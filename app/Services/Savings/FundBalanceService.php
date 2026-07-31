<?php

namespace App\Services\Savings;

use App\Enums\TransferStatus;
use App\Models\FundSpend;
use App\Models\IncomeAllocation;
use App\Models\IncomePeriod;
use App\Models\SavingsCategory;
use App\Models\SavingsPlan;
use App\Services\Vault\FinancialEncryptionService;
use App\Services\Vault\VaultKeyManager;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class FundBalanceService
{
    public function __construct(
        private VaultKeyManager $vaultKeyManager,
        private FinancialEncryptionService $encryption,
    ) {
    }

    /**
     * @return list<array{
     *     categoryId: string,
     *     name: string,
     *     hint: string|null,
     *     isDefault: bool,
     *     allocated: string|null,
     *     spent: string|null,
     *     remaining: string|null,
     *     percentUsed: float|null
     * }>
     */
    public function balancesForPlan(SavingsPlan $plan): array
    {
        $plan->loadMissing('categories');
        $dek = $this->vaultKeyManager->userDek();
        $allocatedByCategory = $this->allocatedTotalsByCategory($plan, $dek);
        $spentByCategory = $this->spentTotalsByCategory($plan, $dek);
        $defaultCategoryId = $this->defaultCategoryId($plan);

        return $plan->categories
            ->sortBy('sort_order')
            ->values()
            ->map(function (SavingsCategory $category) use ($allocatedByCategory, $spentByCategory, $defaultCategoryId, $dek) {
                $allocated = $allocatedByCategory[$category->id] ?? '0.00';
                $spent = $spentByCategory[$category->id] ?? '0.00';
                $remaining = $dek === null ? null : bcsub($allocated, $spent, 2);
                $percentUsed = null;

                if ($dek !== null && bccomp($allocated, '0', 2) === 1) {
                    $percentUsed = round((float) bcdiv(bcmul($spent, '100', 4), $allocated, 2), 1);
                }

                return [
                    'categoryId' => $category->id,
                    'name' => $category->name,
                    'hint' => $this->hintForCategory($category->name),
                    'isDefault' => $category->id === $defaultCategoryId,
                    'allocated' => $dek === null ? null : $allocated,
                    'spent' => $dek === null ? null : $spent,
                    'remaining' => $remaining,
                    'percentUsed' => $percentUsed,
                ];
            })
            ->all();
    }

    public function remainingForCategory(SavingsPlan $plan, string $categoryId): ?string
    {
        $dek = $this->vaultKeyManager->userDek();

        if ($dek === null) {
            return null;
        }

        $allocated = $this->allocatedTotalsByCategory($plan, $dek)[$categoryId] ?? '0.00';
        $spent = $this->spentTotalsByCategory($plan, $dek)[$categoryId] ?? '0.00';

        return bcsub($allocated, $spent, 2);
    }

    public function assertCanSpend(SavingsPlan $plan, string $categoryId, string $amount): void
    {
        if (! $plan->hasLockedIncomePeriod()) {
            throw ValidationException::withMessages([
                'amount' => __('Lock at least one income period before recording spending.'),
            ]);
        }

        $remaining = $this->remainingForCategory($plan, $categoryId);

        if ($remaining === null) {
            return;
        }

        if (bccomp($amount, $remaining, 2) === 1) {
            $category = $plan->categories()->find($categoryId);
            $categoryName = $category?->name ?? __('this fund');

            throw ValidationException::withMessages([
                'amount' => __('Only :amount remaining in :fund.', [
                    'amount' => '₱'.number_format((float) $remaining, 2),
                    'fund' => $categoryName,
                ]),
            ]);
        }
    }

    public function assertCanUnlockPeriod(IncomePeriod $period): void
    {
        $period->loadMissing(['plan.categories', 'allocations']);
        $plan = $period->plan;
        $dek = $this->vaultKeyManager->userDek();

        if ($dek === null) {
            return;
        }

        $allocatedByCategory = $this->allocatedTotalsByCategory($plan, $dek);
        $spentByCategory = $this->spentTotalsByCategory($plan, $dek);

        foreach ($period->allocations as $allocation) {
            $categoryId = $allocation->category_id;
            $periodAmount = $this->decryptAmount($dek, $allocation->getRawOriginal('amount_encrypted')) ?? '0.00';
            $currentAllocated = $allocatedByCategory[$categoryId] ?? '0.00';
            $allocatedAfterUnlock = bcsub($currentAllocated, $periodAmount, 2);
            $spent = $spentByCategory[$categoryId] ?? '0.00';

            if (bccomp($spent, $allocatedAfterUnlock, 2) === 1) {
                $categoryName = $allocation->category?->name ?? __('a fund');

                throw ValidationException::withMessages([
                    'period' => __('Cannot unlock income — :fund spending exceeds what would remain allocated.', [
                        'fund' => $categoryName,
                    ]),
                ]);
            }
        }
    }

    /**
     * @return array{
     *     by_bank: list<array{bank_id: string, bank_name: string, total: string}>,
     *     by_recipient: list<array{recipient_id: string, recipient_name: string, total: string}>,
     *     fund_health: list<array{
     *         category_id: string,
     *         category_name: string,
     *         allocated: string,
     *         spent: string,
     *         remaining: string,
     *         percent_used: float
     *     }>
     * }
     */
    public function reportTotals(SavingsPlan $plan, Collection $spends): array
    {
        $dek = $this->vaultKeyManager->userDek();

        if ($dek === null) {
            return [
                'by_bank' => [],
                'by_recipient' => [],
                'fund_health' => [],
            ];
        }

        $confirmed = $spends->where('status', TransferStatus::Confirmed);
        $balances = $this->balancesForPlan($plan);

        return [
            'by_bank' => $this->aggregateSpends($confirmed, $dek, 'bank_id', fn (FundSpend $spend) => $spend->bank?->name ?? 'Unknown'),
            'by_recipient' => $this->aggregateSpends($confirmed, $dek, 'recipient_id', fn (FundSpend $spend) => $spend->recipient?->name ?? 'Unknown'),
            'fund_health' => collect($balances)
                ->map(fn (array $balance) => [
                    'category_id' => $balance['categoryId'],
                    'category_name' => $balance['name'],
                    'allocated' => $balance['allocated'] ?? '0.00',
                    'spent' => $balance['spent'] ?? '0.00',
                    'remaining' => $balance['remaining'] ?? '0.00',
                    'percent_used' => $balance['percentUsed'] ?? 0.0,
                ])
                ->values()
                ->all(),
        ];
    }

    public function defaultCategoryId(SavingsPlan $plan): ?string
    {
        $everyday = $plan->categories->firstWhere('name', 'Everyday Fund');

        return $everyday?->id ?? $plan->categories->sortBy('sort_order')->first()?->id;
    }

    /**
     * @return array<string, string>
     */
    private function allocatedTotalsByCategory(SavingsPlan $plan, ?string $dek): array
    {
        if ($dek === null) {
            return [];
        }

        $totals = [];

        $allocations = IncomeAllocation::query()
            ->whereHas('incomePeriod', fn ($query) => $query
                ->where('plan_id', $plan->id)
                ->where('is_locked', true))
            ->get();

        foreach ($allocations as $allocation) {
            $plain = $this->decryptAmount($dek, $allocation->getRawOriginal('amount_encrypted'));

            if ($plain === null) {
                continue;
            }

            $totals[$allocation->category_id] = bcadd($totals[$allocation->category_id] ?? '0.00', $plain, 2);
        }

        return $totals;
    }

    /**
     * @return array<string, string>
     */
    private function spentTotalsByCategory(SavingsPlan $plan, ?string $dek): array
    {
        if ($dek === null) {
            return [];
        }

        $totals = [];

        $spends = FundSpend::query()
            ->where('savings_plan_id', $plan->id)
            ->where('status', TransferStatus::Confirmed)
            ->get();

        foreach ($spends as $spend) {
            $plain = $this->decryptAmount($dek, $spend->getRawOriginal('amount_encrypted'));

            if ($plain === null) {
                continue;
            }

            $totals[$spend->category_id] = bcadd($totals[$spend->category_id] ?? '0.00', $plain, 2);
        }

        return $totals;
    }

    private function decryptAmount(string $dek, mixed $encrypted): ?string
    {
        if (! is_string($encrypted) || $encrypted === '') {
            return null;
        }

        return $this->encryption->tryDecryptForDisplay($dek, $encrypted);
    }

    private function hintForCategory(string $name): ?string
    {
        return match ($name) {
            'Everyday Fund' => __('Daily expenses'),
            'Empower Fund' => __('Invest in yourself — repairs, skills, tools'),
            'Emergency Fund' => __('True emergencies only'),
            default => null,
        };
    }

    /**
     * @param  Collection<int, FundSpend>  $spends
     * @return list<array<string, string>>
     */
    private function aggregateSpends(Collection $spends, string $dek, string $key, callable $labelResolver): array
    {
        return $spends
            ->filter(fn (FundSpend $spend) => $spend->{$key} !== null)
            ->groupBy($key)
            ->map(function (Collection $group, string $id) use ($dek, $key, $labelResolver) {
                $total = '0.00';

                foreach ($group as $spend) {
                    $plain = $this->decryptAmount($dek, $spend->getRawOriginal('amount_encrypted'));

                    if ($plain !== null) {
                        $total = bcadd($total, $plain, 2);
                    }
                }

                /** @var FundSpend $first */
                $first = $group->first();

                return [
                    $key => $id,
                    str_replace('_id', '_name', $key) => $labelResolver($first),
                    'total' => $total,
                ];
            })
            ->values()
            ->all();
    }
}
