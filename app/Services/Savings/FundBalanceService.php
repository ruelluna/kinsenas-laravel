<?php

namespace App\Services\Savings;

use App\Enums\TransferStatus;
use App\Models\FundSpend;
use App\Models\FundTransfer;
use App\Models\IncomeAllocation;
use App\Models\IncomePeriod;
use App\Models\SavingsCategory;
use App\Models\SavingsPlan;
use App\Models\Team;
use App\Services\Vault\FinancialEncryptionService;
use App\Services\Vault\VaultKeyManager;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class FundBalanceService
{
    public function __construct(
        private VaultKeyManager $vaultKeyManager,
        private FinancialEncryptionService $encryption,
    ) {}

    /**
     * @return list<array{
     *     categoryId: string,
     *     name: string,
     *     hint: string|null,
     *     isDefault: bool,
     *     allocated: string|null,
     *     transferred: string|null,
     *     received: string|null,
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
        $transferredOutByCategory = $this->transferredOutTotalsByCategory($plan, $dek);
        $receivedInByCategory = $this->receivedInTotalsByCategory($plan, $dek);
        $spentByCategory = $this->spentTotalsByCategory($plan, $dek);
        $defaultCategoryId = $this->defaultCategoryId($plan);

        return $plan->categories
            ->sortBy('sort_order')
            ->values()
            ->map(function (SavingsCategory $category) use (
                $allocatedByCategory,
                $transferredOutByCategory,
                $receivedInByCategory,
                $spentByCategory,
                $defaultCategoryId,
                $dek,
            ) {
                $allocated = $allocatedByCategory[$category->id] ?? '0.00';
                $transferredOut = $transferredOutByCategory[$category->id] ?? '0.00';
                $receivedIn = $receivedInByCategory[$category->id] ?? '0.00';
                $spent = $spentByCategory[$category->id] ?? '0.00';
                $remaining = $dek === null
                    ? null
                    : bcsub(
                        bcadd(bcsub($allocated, $transferredOut, 2), $receivedIn, 2),
                        $spent,
                        2,
                    );
                $percentUsed = null;

                if ($dek !== null && bccomp($allocated, '0', 2) === 1) {
                    $drawn = bcsub(bcadd($transferredOut, $spent, 2), $receivedIn, 2);
                    $percentUsed = round((float) bcdiv(bcmul($drawn, '100', 4), $allocated, 2), 1);
                }

                return [
                    'categoryId' => $category->id,
                    'name' => $category->name,
                    'hint' => $this->hintForCategory($category->name),
                    'isDefault' => $category->id === $defaultCategoryId,
                    'allocated' => $dek === null ? null : $allocated,
                    'transferred' => $dek === null ? null : $transferredOut,
                    'received' => $dek === null ? null : $receivedIn,
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
        $transferredOut = $this->transferredOutTotalsByCategory($plan, $dek)[$categoryId] ?? '0.00';
        $receivedIn = $this->receivedInTotalsByCategory($plan, $dek)[$categoryId] ?? '0.00';
        $spent = $this->spentTotalsByCategory($plan, $dek)[$categoryId] ?? '0.00';

        return bcsub(
            bcadd(bcsub($allocated, $transferredOut, 2), $receivedIn, 2),
            $spent,
            2,
        );
    }

    public function assertCanSpend(SavingsPlan $plan, string $categoryId, string $amount): void
    {
        $this->assertCanDrawFromCategory($plan, $categoryId, $amount, 'spending');
    }

    public function assertCanUpdateSpend(
        SavingsPlan $plan,
        FundSpend $spend,
        string $newCategoryId,
        string $newAmount,
    ): void {
        if (! $plan->allow_editing_spends) {
            throw ValidationException::withMessages([
                'amount' => __('Spending edits are disabled for this plan.'),
            ]);
        }

        if ($spend->status !== TransferStatus::Confirmed) {
            return;
        }

        $oldAmount = $spend->amount_encrypted;

        if ($oldAmount === null) {
            return;
        }

        if ($spend->category_id === $newCategoryId) {
            $remaining = $this->remainingForCategory($plan, $newCategoryId);

            if ($remaining === null) {
                return;
            }

            $available = bcadd($remaining, $oldAmount, 2);

            if (bccomp($newAmount, $available, 2) === 1) {
                $category = $plan->categories()->find($newCategoryId);
                $categoryName = $category?->name ?? __('this fund');

                throw ValidationException::withMessages([
                    'amount' => __('Only :amount remaining in :fund.', [
                        'amount' => $available,
                        'fund' => $categoryName,
                    ]),
                ]);
            }

            return;
        }

        $this->assertCanSpend($plan, $newCategoryId, $newAmount);
    }

    public function assertCanDeleteSpend(SavingsPlan $plan): void
    {
        if (! $plan->allow_editing_spends) {
            throw ValidationException::withMessages([
                'amount' => __('Spending edits are disabled for this plan.'),
            ]);
        }
    }

    public function assertCanTransfer(SavingsPlan $plan, string $categoryId, string $amount): void
    {
        $this->assertCanDrawFromCategory($plan, $categoryId, $amount, 'transfer');
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
        $transferredOutByCategory = $this->transferredOutTotalsByCategory($plan, $dek);
        $receivedInByCategory = $this->receivedInTotalsByCategory($plan, $dek);
        $spentByCategory = $this->spentTotalsByCategory($plan, $dek);

        foreach ($period->allocations as $allocation) {
            $categoryId = $allocation->category_id;
            $periodAmount = $this->decryptAmount($dek, $allocation->getRawOriginal('amount_encrypted')) ?? '0.00';
            $currentAllocated = $allocatedByCategory[$categoryId] ?? '0.00';
            $allocatedAfterUnlock = bcsub($currentAllocated, $periodAmount, 2);
            $transferredOut = $transferredOutByCategory[$categoryId] ?? '0.00';
            $receivedIn = $receivedInByCategory[$categoryId] ?? '0.00';
            $spent = $spentByCategory[$categoryId] ?? '0.00';
            $drawn = bcsub(bcadd($transferredOut, $spent, 2), $receivedIn, 2);

            if (bccomp($drawn, $allocatedAfterUnlock, 2) === 1) {
                $categoryName = $allocation->category?->name ?? __('a fund');

                throw ValidationException::withMessages([
                    'period' => __('Cannot unlock income — :fund transfers and spending exceed what would remain allocated.', [
                        'fund' => $categoryName,
                    ]),
                ]);
            }
        }
    }

    /**
     * @return list<array{
     *     bankId: string,
     *     bankName: string,
     *     logoUrl: string|null,
     *     total: string,
     *     byCategory: list<array{categoryId: string, categoryName: string, total: string}>
     * }>
     */
    public function bankBalancesForTeam(Team $team, SavingsPlan $plan): array
    {
        $dek = $this->vaultKeyManager->userDek();

        if ($dek === null) {
            return [];
        }

        $plan->loadMissing('categories');

        $banks = $team->banks()
            ->where('is_active', true)
            ->with('institution')
            ->orderBy('sort_order')
            ->get();

        $transfers = FundTransfer::query()
            ->where('savings_plan_id', $plan->id)
            ->where('status', TransferStatus::Confirmed)
            ->with(['fromCategory', 'toCategory', 'fromBank', 'toBank'])
            ->get();

        $spends = FundSpend::query()
            ->where('savings_plan_id', $plan->id)
            ->where('status', TransferStatus::Confirmed)
            ->whereNotNull('bank_id')
            ->with(['category', 'bank'])
            ->get();

        return $banks->map(function ($bank) use ($dek, $transfers, $spends, $plan) {
            $byCategory = [];
            $total = '0.00';

            $bankSpends = $spends->where('bank_id', $bank->id);

            $assignedCategories = $plan->categories
                ->where('bank_id', $bank->id)
                ->sortBy('sort_order')
                ->values();

            $activityCategoryIds = $transfers->flatMap(fn (FundTransfer $transfer) => [
                $transfer->from_bank_id === $bank->id ? $transfer->from_category_id : null,
                $transfer->to_bank_id === $bank->id ? $transfer->to_category_id : null,
            ])
                ->merge($bankSpends->pluck('category_id'))
                ->filter()
                ->unique()
                ->reject(fn (string $categoryId) => $assignedCategories->contains('id', $categoryId));

            foreach ($assignedCategories as $category) {
                $categoryTotal = $this->remainingForCategory($plan, $category->id) ?? '0.00';

                $byCategory[] = [
                    'categoryId' => $category->id,
                    'categoryName' => $category->name,
                    'total' => $categoryTotal,
                ];

                $total = bcadd($total, $categoryTotal, 2);
            }

            foreach ($activityCategoryIds as $categoryId) {
                $categoryName = $transfers->firstWhere('from_category_id', $categoryId)?->fromCategory?->name
                    ?? $transfers->firstWhere('to_category_id', $categoryId)?->toCategory?->name
                    ?? $bankSpends->firstWhere('category_id', $categoryId)?->category?->name
                    ?? __('Unknown');

                $categoryTotal = $this->netBalanceForBankCategory($dek, $transfers, $bankSpends, $bank->id, $categoryId);

                $byCategory[] = [
                    'categoryId' => $categoryId,
                    'categoryName' => $categoryName,
                    'total' => $categoryTotal,
                ];

                $total = bcadd($total, $categoryTotal, 2);
            }

            return [
                'bankId' => $bank->id,
                'bankName' => $bank->name,
                'logoUrl' => $bank->institution?->logo_url,
                'total' => $total,
                'byCategory' => $byCategory,
            ];
        })->all();
    }

    /**
     * @return array{
     *     by_bank: list<array{
     *         bank_id: string,
     *         bank_name: string,
     *         logo_url: string|null,
     *         total: string,
     *         by_category: list<array{category_id: string, category_name: string, total: string}>
     *     }>,
     *     by_recipient: list<array{recipient_id: string, recipient_name: string, total: string}>,
     *     fund_health: list<array{
     *         category_id: string,
     *         category_name: string,
     *         allocated: string,
     *         transferred: string,
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
        $team = $plan->team;

        return [
            'by_bank' => collect($this->bankBalancesForTeam($team, $plan))
                ->map(fn (array $bank) => [
                    'bank_id' => $bank['bankId'],
                    'bank_name' => $bank['bankName'],
                    'logo_url' => $bank['logoUrl'],
                    'total' => $bank['total'],
                    'by_category' => collect($bank['byCategory'])
                        ->map(fn (array $row) => [
                            'category_id' => $row['categoryId'],
                            'category_name' => $row['categoryName'],
                            'total' => $row['total'],
                        ])
                        ->values()
                        ->all(),
                ])
                ->values()
                ->all(),
            'by_recipient' => $this->aggregateSpends($confirmed, $dek, 'recipient_id', fn (FundSpend $spend) => $spend->recipient?->name ?? 'Unknown'),
            'fund_health' => collect($balances)
                ->map(fn (array $balance) => [
                    'category_id' => $balance['categoryId'],
                    'category_name' => $balance['name'],
                    'allocated' => $balance['allocated'] ?? '0.00',
                    'transferred' => $balance['transferred'] ?? '0.00',
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
        $plan->loadMissing('categories');

        $everyday = $plan->categories->firstWhere('name', 'Everyday Fund');

        return $everyday?->id ?? $plan->categories->sortBy('sort_order')->first()?->id;
    }

    /**
     * @return list<array{
     *     categoryId: string,
     *     name: string,
     *     hint: string|null,
     *     isDefault: bool,
     *     allocated: string|null,
     *     transferred: string|null,
     *     received: string|null,
     *     spent: string|null,
     *     remaining: string|null,
     *     percentUsed: float|null
     * }>
     */
    public function balancesWithDefaultFirst(SavingsPlan $plan): array
    {
        return $this->orderRowsWithDefaultFirst(
            $this->balancesForPlan($plan),
            $this->defaultCategoryId($plan),
            fn (array $row) => $row['categoryId'],
        );
    }

    /**
     * @return Collection<int, SavingsCategory>
     */
    public function categoriesWithDefaultFirst(SavingsPlan $plan): Collection
    {
        $plan->loadMissing('categories');
        $defaultId = $this->defaultCategoryId($plan);
        $sorted = $plan->categories->sortBy('sort_order')->values();

        if ($defaultId === null) {
            return $sorted;
        }

        $default = $sorted->firstWhere('id', $defaultId);

        if ($default === null) {
            return $sorted;
        }

        return collect([$default])->merge($sorted->where('id', '!=', $defaultId))->values();
    }

    /**
     * @template T
     *
     * @param  list<T>  $rows
     * @param  callable(T): string  $categoryIdResolver
     * @return list<T>
     */
    private function orderRowsWithDefaultFirst(array $rows, ?string $defaultId, callable $categoryIdResolver): array
    {
        if ($defaultId === null || $rows === []) {
            return $rows;
        }

        $defaultIndex = null;

        foreach ($rows as $index => $row) {
            if ($categoryIdResolver($row) === $defaultId) {
                $defaultIndex = $index;

                break;
            }
        }

        if ($defaultIndex === null || $defaultIndex === 0) {
            return $rows;
        }

        $default = $rows[$defaultIndex];
        $rest = array_values(array_filter(
            $rows,
            fn ($row) => $categoryIdResolver($row) !== $defaultId,
        ));

        return array_merge([$default], $rest);
    }

    /**
     * @return array<string, string|null>
     */
    public function categoryBankMap(SavingsPlan $plan): array
    {
        $plan->loadMissing('categories.bank');

        $map = [];

        foreach ($plan->categories as $category) {
            $map[$category->id] = $category->bank_id;
        }

        return $map;
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
    private function transferredOutTotalsByCategory(SavingsPlan $plan, ?string $dek): array
    {
        if ($dek === null) {
            return [];
        }

        $totals = [];

        $transfers = FundTransfer::query()
            ->where('savings_plan_id', $plan->id)
            ->where('status', TransferStatus::Confirmed)
            ->get();

        foreach ($transfers as $transfer) {
            $plain = $this->decryptAmount($dek, $transfer->getRawOriginal('amount_encrypted'));

            if ($plain === null) {
                continue;
            }

            $totals[$transfer->from_category_id] = bcadd($totals[$transfer->from_category_id] ?? '0.00', $plain, 2);
        }

        return $totals;
    }

    /**
     * @return array<string, string>
     */
    private function receivedInTotalsByCategory(SavingsPlan $plan, ?string $dek): array
    {
        if ($dek === null) {
            return [];
        }

        $totals = [];

        $transfers = FundTransfer::query()
            ->where('savings_plan_id', $plan->id)
            ->where('status', TransferStatus::Confirmed)
            ->get();

        foreach ($transfers as $transfer) {
            $plain = $this->decryptAmount($dek, $transfer->getRawOriginal('amount_encrypted'));

            if ($plain === null) {
                continue;
            }

            $totals[$transfer->to_category_id] = bcadd($totals[$transfer->to_category_id] ?? '0.00', $plain, 2);
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

    private function assertCanDrawFromCategory(
        SavingsPlan $plan,
        string $categoryId,
        string $amount,
        string $action,
    ): void {
        if (! $plan->hasLockedIncomePeriod()) {
            throw ValidationException::withMessages([
                'amount' => __('Lock at least one income period before recording :action.', [
                    'action' => $action,
                ]),
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

    /**
     * @param  Collection<int, FundTransfer>  $transfers
     * @param  Collection<int, FundSpend>  $bankSpends
     */
    private function netBalanceForBankCategory(
        string $dek,
        Collection $transfers,
        Collection $bankSpends,
        string $bankId,
        string $categoryId,
    ): string {
        $categoryTotal = '0.00';

        foreach ($transfers as $transfer) {
            $plain = $this->decryptAmount($dek, $transfer->getRawOriginal('amount_encrypted'));

            if ($plain === null) {
                continue;
            }

            if ($transfer->to_category_id === $categoryId && $transfer->to_bank_id === $bankId) {
                $categoryTotal = bcadd($categoryTotal, $plain, 2);
            }

            if ($transfer->from_category_id === $categoryId && $transfer->from_bank_id === $bankId) {
                $categoryTotal = bcsub($categoryTotal, $plain, 2);
            }
        }

        foreach ($bankSpends->where('category_id', $categoryId) as $spend) {
            $plain = $this->decryptAmount($dek, $spend->getRawOriginal('amount_encrypted'));

            if ($plain !== null) {
                $categoryTotal = bcsub($categoryTotal, $plain, 2);
            }
        }

        return $categoryTotal;
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
