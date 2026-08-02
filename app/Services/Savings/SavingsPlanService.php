<?php

namespace App\Services\Savings;

use App\Enums\CategoryAllocationType;
use App\Enums\DeductionMode;
use App\Models\Bank;
use App\Models\SavingsCategory;
use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SavingsPlanService
{
    public function forTeam(Team $team, User $user): ?SavingsPlan
    {
        return SavingsPlan::query()
            ->where('team_id', $team->id)
            ->where(function ($query) use ($user) {
                $query->where('created_by_user_id', $user->id)
                    ->orWhere('is_shared_with_team', true);
            })
            ->with(['categories.deductFromCategory', 'categories.bank'])
            ->first();
    }

    public function cloneFromTemplate(Team $team, User $user, SavingsFormulaTemplate $template, string $name): SavingsPlan
    {
        if ($this->forTeam($team, $user) !== null) {
            throw ValidationException::withMessages([
                'plan' => __('A savings plan already exists for this team.'),
            ]);
        }

        return DB::transaction(function () use ($team, $user, $template, $name) {
            $template->load('categories');

            $plan = SavingsPlan::query()->create([
                'team_id' => $team->id,
                'created_by_user_id' => $user->id,
                'name' => $name,
                'currency' => 'PHP',
                'is_shared_with_team' => false,
            ]);

            foreach ($template->categories as $index => $category) {
                SavingsCategory::query()->create([
                    'plan_id' => $plan->id,
                    'name' => $category->name,
                    'allocation_type' => CategoryAllocationType::Percentage,
                    'percentage' => $category->percentage,
                    'sort_order' => $index,
                ]);
            }

            return $plan->load(['categories.deductFromCategory']);
        });
    }

    public function createCustom(Team $team, User $user): SavingsPlan
    {
        if ($this->forTeam($team, $user) !== null) {
            throw ValidationException::withMessages([
                'plan' => __('A savings plan already exists for this team.'),
            ]);
        }

        return SavingsPlan::query()->create([
            'team_id' => $team->id,
            'created_by_user_id' => $user->id,
            'name' => __('Custom savings plan'),
            'currency' => 'PHP',
            'is_shared_with_team' => false,
        ])->load(['categories.deductFromCategory']);
    }

    /**
     * @param  list<array{
     *     id?: string|null,
     *     name: string,
     *     allocation_type: string,
     *     percentage?: float|int|string|null,
     *     deduction_mode?: string|null,
     *     deduction_value?: float|int|string|null,
     *     deduct_from_index?: int|null,
     *     opening_balance?: float|int|string|null
     * }>  $categories
     */
    public function updateCategories(SavingsPlan $plan, array $categories): SavingsPlan
    {
        if ($plan->hasIncomePeriod()) {
            return $this->mergeCategoriesAfterIncome($plan, $categories);
        }

        $this->validateCategories($categories);

        return $this->replaceAllCategories($plan, $categories);
    }

    /**
     * @param  list<array<string, mixed>>  $categories
     */
    private function replaceAllCategories(SavingsPlan $plan, array $categories): SavingsPlan
    {
        return DB::transaction(function () use ($plan, $categories) {
            $plan->categories()->delete();

            $created = [];

            foreach ($categories as $index => $category) {
                $allocationType = CategoryAllocationType::from($category['allocation_type']);

                $created[$index] = SavingsCategory::query()->create([
                    'plan_id' => $plan->id,
                    'name' => $category['name'],
                    'allocation_type' => $allocationType,
                    'percentage' => $allocationType === CategoryAllocationType::Percentage
                        ? $category['percentage']
                        : null,
                    'deduction_mode' => $allocationType === CategoryAllocationType::Deduction
                        && ! empty($category['deduction_mode'])
                        ? DeductionMode::from($category['deduction_mode'])
                        : null,
                    'deduction_value' => $allocationType === CategoryAllocationType::Deduction
                        && isset($category['deduction_value'])
                        && $category['deduction_value'] !== ''
                        ? $category['deduction_value']
                        : null,
                    'opening_balance_encrypted' => $allocationType === CategoryAllocationType::Percentage
                        ? $this->normalizeOpeningBalance($category['opening_balance'] ?? null)
                        : null,
                    'sort_order' => $index,
                ]);
            }

            $this->resolveDeductionSources($categories, $created);
            $this->syncCategoryBanks($plan, $categories, $created);
            $this->syncOpeningBalances($plan, $categories, $created);

            return $plan->fresh(['categories.deductFromCategory', 'categories.bank']);
        });
    }

    /**
     * @param  list<array<string, mixed>>  $categories
     */
    private function mergeCategoriesAfterIncome(SavingsPlan $plan, array $categories): SavingsPlan
    {
        $existing = $plan->categories()->orderBy('sort_order')->get()->keyBy('id');

        if ($existing->isEmpty()) {
            throw ValidationException::withMessages([
                'categories' => __('Cannot update fund buckets for an empty plan.'),
            ]);
        }

        $submittedIds = collect($categories)
            ->pluck('id')
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->values();

        foreach ($existing as $existingCategory) {
            if ($existingCategory->isPercentage() && ! $submittedIds->contains($existingCategory->id)) {
                throw ValidationException::withMessages([
                    'categories' => __('Percentage fund buckets cannot be removed after income has been entered.'),
                ]);
            }
        }

        /** @var array<int, SavingsCategory> $indexedCategories */
        $indexedCategories = [];

        foreach ($categories as $index => $category) {
            $categoryId = $category['id'] ?? null;

            if ($categoryId !== null && $categoryId !== '') {
                $existingCategory = $existing->get($categoryId);

                if ($existingCategory === null) {
                    throw ValidationException::withMessages([
                        "categories.{$index}.id" => __('Unknown fund bucket.'),
                    ]);
                }

                if ($existingCategory->isPercentage()) {
                    $this->assertPercentageUnchanged($existingCategory, $category, $index);
                    $indexedCategories[$index] = $existingCategory;

                    continue;
                }

                if (($category['allocation_type'] ?? '') !== CategoryAllocationType::Deduction->value) {
                    throw ValidationException::withMessages([
                        "categories.{$index}.allocation_type" => __('Custom fund buckets cannot become percentage fund buckets.'),
                    ]);
                }

                $this->validateCustomCategory($category, $index, $categories);

                continue;
            }

            if (($category['allocation_type'] ?? '') === CategoryAllocationType::Percentage->value) {
                throw ValidationException::withMessages([
                    "categories.{$index}.allocation_type" => __('Cannot add percentage fund buckets after income has been entered.'),
                ]);
            }

            $this->validateCustomCategory($category, $index, $categories);
        }

        return DB::transaction(function () use ($plan, $categories, $existing, $submittedIds, &$indexedCategories) {
            foreach ($existing as $existingCategory) {
                if ($existingCategory->isDeduction() && ! $submittedIds->contains($existingCategory->id)) {
                    $existingCategory->delete();
                }
            }

            foreach ($categories as $index => $category) {
                $categoryId = $category['id'] ?? null;

                if ($categoryId !== null && $categoryId !== '') {
                    $existingCategory = $existing->get($categoryId);

                    if ($existingCategory === null || $existingCategory->isPercentage()) {
                        continue;
                    }

                    $existingCategory->update([
                        'name' => $category['name'],
                        'deduction_mode' => ! empty($category['deduction_mode'])
                            ? DeductionMode::from($category['deduction_mode'])
                            : null,
                        'deduction_value' => isset($category['deduction_value']) && $category['deduction_value'] !== ''
                            ? $category['deduction_value']
                            : null,
                        'sort_order' => $index,
                    ]);

                    $indexedCategories[$index] = $existingCategory->fresh();

                    continue;
                }

                $indexedCategories[$index] = SavingsCategory::query()->create([
                    'plan_id' => $plan->id,
                    'name' => $category['name'],
                    'allocation_type' => CategoryAllocationType::Deduction,
                    'deduction_mode' => ! empty($category['deduction_mode'])
                        ? DeductionMode::from($category['deduction_mode'])
                        : null,
                    'deduction_value' => isset($category['deduction_value']) && $category['deduction_value'] !== ''
                        ? $category['deduction_value']
                        : null,
                    'sort_order' => $index,
                ]);
            }

            foreach ($categories as $index => $category) {
                if (($category['allocation_type'] ?? '') === CategoryAllocationType::Percentage->value) {
                    $categoryId = $category['id'] ?? null;

                    if ($categoryId !== null && isset($indexedCategories[$index])) {
                        $indexedCategories[$index]->update(['sort_order' => $index]);
                    }
                }
            }

            $this->resolveDeductionSources($categories, $indexedCategories);
            $this->syncCategoryBanks($plan, $categories, $indexedCategories);

            return $plan->fresh(['categories.deductFromCategory', 'categories.bank']);
        });
    }

    /**
     * @param  list<array<string, mixed>>  $categories
     * @param  array<int, SavingsCategory>  $indexedCategories
     */
    private function syncOpeningBalances(SavingsPlan $plan, array $categories, array $indexedCategories): void
    {
        if ($plan->hasIncomePeriod()) {
            return;
        }

        foreach ($categories as $index => $category) {
            if (! isset($indexedCategories[$index])) {
                continue;
            }

            if (CategoryAllocationType::from($category['allocation_type']) !== CategoryAllocationType::Percentage) {
                continue;
            }

            $indexedCategories[$index]->update([
                'opening_balance_encrypted' => $this->normalizeOpeningBalance($category['opening_balance'] ?? null),
            ]);
        }
    }

    private function normalizeOpeningBalance(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = number_format((float) $value, 2, '.', '');

        if (bccomp($normalized, '0', 2) !== 1) {
            return null;
        }

        return $normalized;
    }

    /**
     * @param  list<array<string, mixed>>  $categories
     * @param  array<int, SavingsCategory>  $created
     */
    private function resolveDeductionSources(array $categories, array $created): void
    {
        foreach ($categories as $index => $category) {
            if (CategoryAllocationType::from($category['allocation_type']) !== CategoryAllocationType::Deduction) {
                continue;
            }

            $sourceIndex = $category['deduct_from_index'] ?? null;

            if ($sourceIndex === null || ! isset($created[$sourceIndex])) {
                continue;
            }

            $created[$index]->update([
                'deduct_from_category_id' => $created[$sourceIndex]->id,
            ]);
        }
    }

    private function assertPercentageUnchanged(
        SavingsCategory $existing,
        array $category,
        int $index,
    ): void {
        if (($category['allocation_type'] ?? '') !== CategoryAllocationType::Percentage->value) {
            throw ValidationException::withMessages([
                "categories.{$index}.allocation_type" => __('Percentage fund buckets cannot be changed after income has been entered.'),
            ]);
        }

        if ($existing->name !== $category['name']) {
            throw ValidationException::withMessages([
                "categories.{$index}.name" => __('Percentage fund bucket names cannot be changed after income has been entered.'),
            ]);
        }

        if ($this->normalizeDecimal($existing->percentage) !== $this->normalizeDecimal($category['percentage'] ?? null)) {
            throw ValidationException::withMessages([
                "categories.{$index}.percentage" => __('Percentages cannot be changed after income has been entered.'),
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $categories
     */
    private function validateCustomCategory(array $category, int $index, array $categories): void
    {
        if (($category['allocation_type'] ?? '') !== CategoryAllocationType::Deduction->value) {
            return;
        }

        $sourceIndex = $category['deduct_from_index'] ?? null;

        if ($sourceIndex === null) {
            throw ValidationException::withMessages([
                "categories.{$index}.deduct_from_index" => __('Select a source fund bucket for this custom fund bucket.'),
            ]);
        }

        $source = $categories[$sourceIndex] ?? null;

        if ($source === null || ($source['allocation_type'] ?? '') !== CategoryAllocationType::Percentage->value) {
            throw ValidationException::withMessages([
                "categories.{$index}.deduct_from_index" => __('Custom fund buckets must deduct from a percentage fund bucket.'),
            ]);
        }

        if ($sourceIndex === $index) {
            throw ValidationException::withMessages([
                "categories.{$index}.deduct_from_index" => __('A fund bucket cannot deduct from itself.'),
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $categories
     */
    private function validateCategories(array $categories): void
    {
        $percentageTotal = collect($categories)
            ->filter(fn (array $category) => ($category['allocation_type'] ?? '') === CategoryAllocationType::Percentage->value)
            ->sum(fn (array $category) => (float) $category['percentage']);

        if (round($percentageTotal, 2) !== 100.0) {
            throw ValidationException::withMessages([
                'categories' => __('Fund bucket percentages must total exactly 100%.'),
            ]);
        }

        foreach ($categories as $index => $category) {
            if (($category['allocation_type'] ?? '') !== CategoryAllocationType::Deduction->value) {
                continue;
            }

            $this->validateCustomCategory($category, $index, $categories);
        }
    }

    private function normalizeDecimal(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }

    /**
     * @param  list<array<string, mixed>>  $categories
     * @param  array<int, SavingsCategory>  $indexedCategories
     */
    private function syncCategoryBanks(SavingsPlan $plan, array $categories, array $indexedCategories): void
    {
        $teamBankIds = Bank::query()
            ->where('team_id', $plan->team_id)
            ->pluck('id');

        foreach ($categories as $index => $category) {
            if (! isset($indexedCategories[$index])) {
                continue;
            }

            $bankId = $category['bank_id'] ?? null;
            $bankId = is_string($bankId) && $bankId !== '' ? $bankId : null;

            if ($bankId !== null && ! $teamBankIds->contains($bankId)) {
                $bankId = null;
            }

            $indexedCategories[$index]->update(['bank_id' => $bankId]);
        }
    }

    public function updateShareSetting(SavingsPlan $plan, bool $isShared): SavingsPlan
    {
        $plan->update(['is_shared_with_team' => $isShared]);

        return $plan->fresh(['categories.deductFromCategory']);
    }

    public function updateSpendingEditSetting(SavingsPlan $plan, bool $allowEditing): SavingsPlan
    {
        $plan->update(['allow_editing_spends' => $allowEditing]);

        return $plan->fresh(['categories.deductFromCategory']);
    }
}
