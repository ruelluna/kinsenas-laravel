<?php

namespace App\Services\Savings;

use App\Enums\CategoryAllocationType;
use App\Enums\DeductionMode;
use App\Models\SavingsCategory;
use App\Models\SavingsPlan;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CategoryAllocationCalculator
{
    /**
     * @param  array<string, string>|null  $periodDeductions  category_id => amount override for this income period
     * @return array<string, string> category_id => amount
     */
    public function calculate(SavingsPlan $plan, string $income, ?array $periodDeductions = null): array
    {
        $plan->loadMissing(['categories.deductFromCategory']);

        /** @var Collection<int, SavingsCategory> $categories */
        $categories = $plan->categories->sortBy('sort_order')->values();

        $amounts = [];

        foreach ($categories as $category) {
            $amounts[$category->id] = '0.00';
        }

        foreach ($categories as $category) {
            if ($category->allocation_type !== CategoryAllocationType::Percentage) {
                continue;
            }

            $amounts[$category->id] = bcmul(
                $income,
                bcdiv((string) $category->percentage, '100', 6),
                2,
            );
        }

        foreach ($categories as $category) {
            if ($category->allocation_type !== CategoryAllocationType::Deduction) {
                continue;
            }

            $deductionAmount = $this->deductionAmount($category, $income, $periodDeductions);
            $sourceId = $category->deduct_from_category_id;

            if ($sourceId === null || ! isset($amounts[$sourceId])) {
                throw ValidationException::withMessages([
                    'categories' => __('Invalid deduction source for :name.', ['name' => $category->name]),
                ]);
            }

            if (bccomp($deductionAmount, '0', 2) !== 1) {
                continue;
            }

            $amounts[$sourceId] = bcsub($amounts[$sourceId], $deductionAmount, 2);
            $amounts[$category->id] = bcadd($amounts[$category->id], $deductionAmount, 2);

            if (bccomp($amounts[$sourceId], '0', 2) === -1) {
                throw ValidationException::withMessages([
                    'amount' => __('Deduction for :name exceeds available funds in :source.', [
                        'name' => $category->name,
                        'source' => $category->deductFromCategory?->name ?? 'source category',
                    ]),
                ]);
            }
        }

        return $amounts;
    }

    /**
     * @param  array<string, string>|null  $periodDeductions
     * @return list<array{
     *     categoryId: string,
     *     name: string,
     *     allocationType: string,
     *     percentage: string|null,
     *     amount: string,
     *     deductionMode: string|null,
     *     deductionValue: string|null,
     *     deductFromCategoryId: string|null,
     *     deductFromCategoryName: string|null,
     *     deductionNote: string|null
     * }>
     */
    public function breakdown(SavingsPlan $plan, string $income, ?array $periodDeductions = null): array
    {
        $amounts = $this->calculate($plan, $income, $periodDeductions);
        $plan->loadMissing(['categories.deductFromCategory', 'categories.deductionsFromThis']);

        return $plan->categories->sortBy('sort_order')->values()->map(function (SavingsCategory $category) use ($amounts) {
            $deductionNote = null;

            if ($category->isDeduction() && $category->deductFromCategory !== null) {
                $deductionNote = __('from :source', ['source' => $category->deductFromCategory->name]);
            }

            if ($category->isPercentage() && $category->deductionsFromThis->isNotEmpty()) {
                $totalDeducted = '0.00';

                foreach ($category->deductionsFromThis as $deduction) {
                    $totalDeducted = bcadd($totalDeducted, $amounts[$deduction->id] ?? '0.00', 2);
                }

                if (bccomp($totalDeducted, '0', 2) === 1) {
                    $deductionNote = __('− :amount deduction', ['amount' => $totalDeducted]);
                }
            }

            return [
                'categoryId' => $category->id,
                'name' => $category->name,
                'allocationType' => $category->allocation_type->value,
                'percentage' => $category->percentage !== null ? (string) $category->percentage : null,
                'amount' => $amounts[$category->id] ?? '0.00',
                'deductionMode' => $category->deduction_mode?->value,
                'deductionValue' => $category->deduction_value !== null ? (string) $category->deduction_value : null,
                'deductFromCategoryId' => $category->deduct_from_category_id,
                'deductFromCategoryName' => $category->deductFromCategory?->name,
                'deductionNote' => $deductionNote,
            ];
        })->all();
    }

    /**
     * @param  array<string, string>|null  $periodDeductions
     */
    private function deductionAmount(SavingsCategory $category, string $income, ?array $periodDeductions): string
    {
        if ($periodDeductions !== null && array_key_exists($category->id, $periodDeductions)) {
            return $periodDeductions[$category->id];
        }

        if ($category->deduction_value === null) {
            return '0.00';
        }

        return match ($category->deduction_mode) {
            DeductionMode::PercentOfIncome => bcmul(
                $income,
                bcdiv((string) $category->deduction_value, '100', 6),
                2,
            ),
            default => number_format((float) $category->deduction_value, 2, '.', ''),
        };
    }
}
