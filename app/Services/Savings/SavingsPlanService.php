<?php

namespace App\Services\Savings;

use App\Enums\CategoryAllocationType;
use App\Enums\DeductionMode;
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
            ->with(['categories.deductFromCategory'])
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

    /**
     * @param  list<array{
     *     id?: string|null,
     *     name: string,
     *     allocation_type: string,
     *     percentage?: float|int|string|null,
     *     deduction_mode?: string|null,
     *     deduction_value?: float|int|string|null,
     *     deduct_from_index?: int|null
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
                    'sort_order' => $index,
                ]);
            }

            $this->resolveDeductionSources($categories, $created);

            return $plan->fresh(['categories.deductFromCategory']);
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
                'categories' => __('Cannot update categories for an empty plan.'),
            ]);
        }

        $submittedIds = collect($categories)
            ->pluck('id')
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->values();

        foreach ($existing->keys() as $existingId) {
            if (! $submittedIds->contains($existingId)) {
                throw ValidationException::withMessages([
                    'categories' => __('Cannot remove categories after income has been entered.'),
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
                        "categories.{$index}.id" => __('Unknown category.'),
                    ]);
                }

                $this->assertCategoryUnchanged($existingCategory, $category, $index, $categories);
                $indexedCategories[$index] = $existingCategory;

                continue;
            }

            if (($category['allocation_type'] ?? '') === CategoryAllocationType::Percentage->value) {
                throw ValidationException::withMessages([
                    "categories.{$index}.allocation_type" => __('Cannot add percentage categories after income has been entered.'),
                ]);
            }

            $this->validateNewCustomCategory($category, $index, $categories);
        }

        return DB::transaction(function () use ($plan, $categories, $existing, &$indexedCategories) {
            $nextSortOrder = (int) $existing->max('sort_order') + 1;

            foreach ($categories as $index => $category) {
                if (! empty($category['id'])) {
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
                    'sort_order' => $nextSortOrder,
                ]);

                $nextSortOrder++;
            }

            $this->resolveDeductionSources($categories, $indexedCategories);

            return $plan->fresh(['categories.deductFromCategory']);
        });
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

    /**
     * @param  list<array<string, mixed>>  $categories
     */
    private function assertCategoryUnchanged(
        SavingsCategory $existing,
        array $category,
        int $index,
        array $categories,
    ): void {
        $allocationType = CategoryAllocationType::from($category['allocation_type']);

        if ($existing->allocation_type !== $allocationType) {
            throw ValidationException::withMessages([
                "categories.{$index}.allocation_type" => __('Existing categories cannot be changed after income has been entered.'),
            ]);
        }

        if ($existing->name !== $category['name']) {
            throw ValidationException::withMessages([
                "categories.{$index}.name" => __('Existing categories cannot be changed after income has been entered.'),
            ]);
        }

        if ($allocationType === CategoryAllocationType::Percentage) {
            if ($this->normalizeDecimal($existing->percentage) !== $this->normalizeDecimal($category['percentage'] ?? null)) {
                throw ValidationException::withMessages([
                    "categories.{$index}.percentage" => __('Percentages cannot be changed after income has been entered.'),
                ]);
            }

            return;
        }

        $existingMode = $existing->deduction_mode?->value;
        $submittedMode = $category['deduction_mode'] ?? null;

        if ($existingMode !== $submittedMode) {
            throw ValidationException::withMessages([
                "categories.{$index}.deduction_mode" => __('Existing custom categories cannot be changed after income has been entered.'),
            ]);
        }

        if ($this->normalizeDecimal($existing->deduction_value) !== $this->normalizeDecimal($category['deduction_value'] ?? null)) {
            throw ValidationException::withMessages([
                "categories.{$index}.deduction_value" => __('Existing custom categories cannot be changed after income has been entered.'),
            ]);
        }

        $sourceCategoryId = $this->resolveSourceCategoryId($category, $index, $categories);

        if ($existing->deduct_from_category_id !== $sourceCategoryId) {
            throw ValidationException::withMessages([
                "categories.{$index}.deduct_from_index" => __('Existing custom categories cannot be changed after income has been entered.'),
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $categories
     */
    private function validateNewCustomCategory(array $category, int $index, array $categories): void
    {
        if (($category['allocation_type'] ?? '') !== CategoryAllocationType::Deduction->value) {
            return;
        }

        $sourceIndex = $category['deduct_from_index'] ?? null;

        if ($sourceIndex === null) {
            throw ValidationException::withMessages([
                "categories.{$index}.deduct_from_index" => __('Select a source category for this custom category.'),
            ]);
        }

        $source = $categories[$sourceIndex] ?? null;

        if ($source === null || ($source['allocation_type'] ?? '') !== CategoryAllocationType::Percentage->value) {
            throw ValidationException::withMessages([
                "categories.{$index}.deduct_from_index" => __('Custom categories must deduct from a percentage category.'),
            ]);
        }

        if ($sourceIndex === $index) {
            throw ValidationException::withMessages([
                "categories.{$index}.deduct_from_index" => __('A category cannot deduct from itself.'),
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $categories
     */
    private function resolveSourceCategoryId(array $category, int $index, array $categories): ?string
    {
        $sourceIndex = $category['deduct_from_index'] ?? null;

        if ($sourceIndex === null) {
            return null;
        }

        $source = $categories[$sourceIndex] ?? null;

        if ($source === null) {
            throw ValidationException::withMessages([
                "categories.{$index}.deduct_from_index" => __('Select a source category for this custom category.'),
            ]);
        }

        $sourceId = $source['id'] ?? null;

        if ($sourceId === null || $sourceId === '') {
            throw ValidationException::withMessages([
                "categories.{$index}.deduct_from_index" => __('Custom categories must deduct from a percentage category.'),
            ]);
        }

        return $sourceId;
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
                'categories' => __('Category percentages must total exactly 100%.'),
            ]);
        }

        foreach ($categories as $index => $category) {
            if (($category['allocation_type'] ?? '') !== CategoryAllocationType::Deduction->value) {
                continue;
            }

            $this->validateNewCustomCategory($category, $index, $categories);
        }
    }

    private function normalizeDecimal(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }

    public function updateShareSetting(SavingsPlan $plan, bool $isShared): SavingsPlan
    {
        $plan->update(['is_shared_with_team' => $isShared]);

        return $plan->fresh(['categories.deductFromCategory']);
    }
}
