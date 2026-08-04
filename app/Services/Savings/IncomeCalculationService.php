<?php

namespace App\Services\Savings;

use App\Models\IncomeAllocation;
use App\Models\IncomePeriod;
use App\Models\IncomePeriodDeduction;
use App\Models\SavingsCategory;
use App\Models\SavingsPlan;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IncomeCalculationService
{
    public function __construct(
        private CategoryAllocationCalculator $calculator,
        private FundBalanceService $fundBalanceService,
        private IncomeDistributionTodoService $distributionTodoService,
    ) {}

    public function create(SavingsPlan $plan, User $user, string $name, string $amount, string $periodStart): IncomePeriod
    {
        return DB::transaction(function () use ($plan, $user, $name, $amount, $periodStart) {
            $period = IncomePeriod::query()->create([
                'plan_id' => $plan->id,
                'name' => $name,
                'amount_encrypted' => $amount,
                'period_start' => $periodStart,
            ]);

            $period->load(['plan.categories.deductFromCategory', 'periodDeductions']);
            $this->persistAllocations($period, $user);

            return $period->fresh(['plan.categories.deductFromCategory', 'allocations.category']);
        });
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function preview(SavingsPlan $plan, string $amount): array
    {
        return $this->calculator->breakdown($plan, $amount);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function breakdownForPeriod(IncomePeriod $period): array
    {
        $period->loadMissing(['allocations.category.deductFromCategory', 'plan.categories.deductFromCategory']);

        if ($period->allocations->isNotEmpty()) {
            $allocationsByCategory = $period->allocations->keyBy('category_id');

            return $period->allocations
                ->sortBy(fn (IncomeAllocation $allocation) => $allocation->category?->sort_order ?? 0)
                ->map(function (IncomeAllocation $allocation) use ($allocationsByCategory) {
                    $category = $allocation->category;

                    return [
                        'categoryId' => $allocation->category_id,
                        'name' => $category?->name ?? '',
                        'allocationType' => $category?->allocation_type->value ?? 'percentage',
                        'percentage' => $category?->percentage !== null ? (string) $category->percentage : null,
                        'amount' => $allocation->amount_encrypted,
                        'deductionMode' => $category?->deduction_mode?->value,
                        'deductionValue' => $category?->deduction_value !== null ? (string) $category->deduction_value : null,
                        'deductFromCategoryId' => $category?->deduct_from_category_id,
                        'deductFromCategoryName' => $category?->deductFromCategory?->name,
                        'deductionNote' => $this->deductionNoteForLocked($category, $allocation, $allocationsByCategory),
                    ];
                })
                ->values()
                ->all();
        }

        $amount = $period->amount_encrypted;

        if ($amount === null) {
            return [];
        }

        return $this->calculator->breakdown($period->plan, $amount, $this->periodDeductionOverrides($period));
    }

    /**
     * @return list<array{
     *     categoryId: string,
     *     name: string,
     *     deductFromCategoryName: string|null,
     *     planDefaultAmount: string|null,
     *     periodAmount: string|null,
     *     hasPeriodOverride: bool
     * }>
     */
    public function customCategoriesForPeriod(IncomePeriod $period): array
    {
        $period->loadMissing(['plan.categories.deductFromCategory', 'periodDeductions']);

        $overrides = $period->periodDeductions->keyBy('category_id');

        return $period->plan->categories
            ->filter(fn (SavingsCategory $category) => $category->isDeduction())
            ->sortBy('sort_order')
            ->values()
            ->map(function (SavingsCategory $category) use ($overrides) {
                $override = $overrides->get($category->id);

                return [
                    'categoryId' => $category->id,
                    'name' => $category->name,
                    'deductFromCategoryName' => $category->deductFromCategory?->name,
                    'planDefaultAmount' => $this->planDefaultDeductionAmount($category),
                    'periodAmount' => $override?->amount_encrypted,
                    'hasPeriodOverride' => $override !== null,
                ];
            })
            ->all();
    }

    /**
     * @param  list<array{category_id: string, amount: float|int|string|null}>  $customAmounts
     */
    public function syncCustomAmounts(IncomePeriod $period, User $user, array $customAmounts): IncomePeriod
    {
        $period->loadMissing('plan.categories');

        $customCategoryIds = $period->plan->categories
            ->filter(fn (SavingsCategory $category) => $category->isDeduction())
            ->pluck('id')
            ->all();

        return DB::transaction(function () use ($period, $user, $customAmounts, $customCategoryIds) {
            foreach ($customAmounts as $row) {
                $categoryId = $row['category_id'];

                if (! in_array($categoryId, $customCategoryIds, true)) {
                    continue;
                }

                $amount = $row['amount'] ?? null;
                $normalizedAmount = ($amount === null || $amount === '')
                    ? '0.00'
                    : number_format((float) $amount, 2, '.', '');

                IncomePeriodDeduction::query()->updateOrCreate(
                    [
                        'income_period_id' => $period->id,
                        'category_id' => $categoryId,
                    ],
                    [
                        'amount_encrypted' => $normalizedAmount,
                    ],
                );
            }

            $period->load(['plan.categories.deductFromCategory', 'periodDeductions']);
            $this->persistAllocations($period, $user);

            return $period->fresh(['plan.categories.deductFromCategory', 'periodDeductions', 'allocations.category']);
        });
    }

    public function delete(IncomePeriod $period): void
    {
        $this->fundBalanceService->assertCanRemovePeriod($period);

        $period->delete();
    }

    public function allocateUnlockedPeriod(IncomePeriod $period, User $user): IncomePeriod
    {
        if ($period->is_locked && $period->allocations()->exists()) {
            return $period->load('allocations.category');
        }

        return DB::transaction(function () use ($period, $user) {
            $period->load(['plan.categories.deductFromCategory', 'periodDeductions']);
            $this->persistAllocations($period, $user);

            return $period->fresh(['allocations.category', 'plan.categories']);
        });
    }

    private function persistAllocations(IncomePeriod $period, User $user): void
    {
        $period->loadMissing(['plan.categories.deductFromCategory', 'periodDeductions']);
        $amount = $period->amount_encrypted;

        if ($amount === null) {
            throw ValidationException::withMessages([
                'amount' => __('Income amount is required before allocating to funds.'),
            ]);
        }

        $allocations = $this->calculator->calculate(
            $period->plan,
            $amount,
            $this->periodDeductionOverrides($period),
        );

        $period->allocations()->delete();

        foreach ($period->plan->categories as $category) {
            IncomeAllocation::query()->create([
                'income_period_id' => $period->id,
                'category_id' => $category->id,
                'amount_encrypted' => $allocations[$category->id] ?? '0.00',
            ]);
        }

        $period->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by_user_id' => $user->id,
        ]);

        $period->load(['allocations.category.bank']);
        $this->distributionTodoService->syncFromPeriod($period);
    }

    /**
     * @param  Collection<string, IncomeAllocation>  $allocationsByCategory
     */
    private function deductionNoteForLocked(
        ?SavingsCategory $category,
        IncomeAllocation $allocation,
        Collection $allocationsByCategory,
    ): ?string {
        if ($category === null) {
            return null;
        }

        if ($category->isDeduction() && $category->deductFromCategory !== null) {
            return __('from :source', ['source' => $category->deductFromCategory->name]);
        }

        if ($category->isPercentage()) {
            $category->loadMissing('deductionsFromThis');
            $totalDeducted = '0.00';

            foreach ($category->deductionsFromThis as $deduction) {
                $deductionAllocation = $allocationsByCategory->get($deduction->id);
                $deductionAmount = $deductionAllocation?->amount_encrypted ?? '0.00';
                $totalDeducted = bcadd($totalDeducted, $deductionAmount, 2);
            }

            if (bccomp($totalDeducted, '0', 2) === 1) {
                return __('− :amount deduction', ['amount' => $totalDeducted]);
            }
        }

        return null;
    }

    /**
     * @return array<string, string>|null
     */
    private function periodDeductionOverrides(IncomePeriod $period): ?array
    {
        $period->loadMissing('periodDeductions');

        if ($period->periodDeductions->isEmpty()) {
            return null;
        }

        return $period->periodDeductions
            ->mapWithKeys(fn (IncomePeriodDeduction $deduction) => [
                $deduction->category_id => $deduction->amount_encrypted ?? '0.00',
            ])
            ->all();
    }

    private function planDefaultDeductionAmount(SavingsCategory $category): ?string
    {
        if ($category->deduction_value === null) {
            return null;
        }

        return number_format((float) $category->deduction_value, 2, '.', '');
    }
}
