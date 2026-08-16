<?php

namespace App\Services\Savings;

use App\Enums\TransferStatus;
use App\Models\FundSpend;
use App\Models\IncomePeriod;
use App\Models\SavingsCategory;
use App\Models\SavingsPlan;
use App\Services\Vault\FinancialEncryptionService;
use App\Services\Vault\VaultKeyManager;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class FundGraphService
{
    private const int TOP_RECIPIENTS_LIMIT = 8;

    private const int DASHBOARD_TREND_MONTHS = 3;

    public function __construct(
        private VaultKeyManager $vaultKeyManager,
        private FinancialEncryptionService $encryption,
        private FundBalanceService $fundBalanceService,
    ) {}

    /**
     * @return array{
     *     range: array{from: string|null, to: string|null},
     *     fund_utilization: list<array{category_id: string, name: string, percent_used: float, remaining: string}>,
     *     spending_by_fund: list<array{category_id: string, name: string, total: string}>,
     *     spending_over_time: list<array{period: string, total: string}>,
     *     income_vs_spending: list<array{period: string, income: string, spending: string}>,
     *     top_recipients: list<array{recipient_id: string, name: string, total: string}>
     * }
     */
    public function graphDataForPlan(SavingsPlan $plan, ?CarbonInterface $from = null, ?CarbonInterface $to = null): array
    {
        [$from, $to] = $this->resolveRange($from, $to);
        $dek = $this->vaultKeyManager->userDek();

        if ($dek === null) {
            return $this->emptyGraphData($from, $to);
        }

        $plan->loadMissing('categories');
        $confirmedSpends = $this->confirmedSpendsInRange($plan, $from, $to);

        return [
            'range' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'fund_utilization' => $this->fundUtilization($plan),
            'spending_by_fund' => $this->spendingByFund($confirmedSpends, $plan, $dek),
            'spending_over_time' => $this->spendingOverTime($confirmedSpends, $dek),
            'income_vs_spending' => $this->incomeVsSpending($plan, $from, $to, $dek),
            'top_recipients' => $this->topRecipients($confirmedSpends, $dek),
        ];
    }

    /**
     * @return array{
     *     fund_utilization: list<array{category_id: string, name: string, percent_used: float, remaining: string}>,
     *     spending_over_time: list<array{period: string, total: string}>
     * }
     */
    public function dashboardGraphsForPlan(SavingsPlan $plan): array
    {
        $dek = $this->vaultKeyManager->userDek();

        if ($dek === null) {
            return [
                'fund_utilization' => [],
                'spending_over_time' => [],
            ];
        }

        $to = now()->endOfMonth();
        $from = now()->subMonths(self::DASHBOARD_TREND_MONTHS - 1)->startOfMonth();
        $confirmedSpends = $this->confirmedSpendsInRange($plan, $from, $to);

        return [
            'fund_utilization' => $this->fundUtilization($plan),
            'spending_over_time' => $this->spendingOverTime($confirmedSpends, $dek),
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveRange(?CarbonInterface $from, ?CarbonInterface $to): array
    {
        $resolvedTo = ($to ?? now())->copy()->endOfDay();
        $resolvedFrom = ($from ?? now()->subMonths(6)->startOfMonth())->copy()->startOfDay();

        if ($resolvedFrom->gt($resolvedTo)) {
            [$resolvedFrom, $resolvedTo] = [$resolvedTo->copy()->startOfDay(), $resolvedFrom->copy()->endOfDay()];
        }

        return [$resolvedFrom, $resolvedTo];
    }

    /**
     * @return array{
     *     range: array{from: string|null, to: string|null},
     *     fund_utilization: list<empty>,
     *     spending_by_fund: list<empty>,
     *     spending_over_time: list<empty>,
     *     income_vs_spending: list<empty>,
     *     top_recipients: list<empty>
     * }
     */
    private function emptyGraphData(CarbonInterface $from, CarbonInterface $to): array
    {
        return [
            'range' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'fund_utilization' => [],
            'spending_by_fund' => [],
            'spending_over_time' => [],
            'income_vs_spending' => [],
            'top_recipients' => [],
        ];
    }

    /**
     * @return list<array{category_id: string, name: string, percent_used: float, remaining: string}>
     */
    private function fundUtilization(SavingsPlan $plan): array
    {
        return collect($this->fundBalanceService->balancesForPlan($plan))
            ->map(fn (array $balance) => [
                'category_id' => $balance['categoryId'],
                'name' => $balance['name'],
                'percent_used' => $balance['percentUsed'] ?? 0.0,
                'remaining' => $balance['remaining'] ?? '0.00',
            ])
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, FundSpend>
     */
    private function confirmedSpendsInRange(SavingsPlan $plan, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return FundSpend::query()
            ->where('savings_plan_id', $plan->id)
            ->where('status', TransferStatus::Confirmed)
            ->whereDate('spent_on', '>=', $from->toDateString())
            ->whereDate('spent_on', '<=', $to->toDateString())
            ->with(['category', 'recipient'])
            ->get();
    }

    /**
     * @param  Collection<int, FundSpend>  $spends
     * @return list<array{category_id: string, name: string, total: string}>
     */
    private function spendingByFund(Collection $spends, SavingsPlan $plan, string $dek): array
    {
        $categoryNames = $plan->categories->keyBy('id');

        return $spends
            ->groupBy('category_id')
            ->map(function (Collection $group, string $categoryId) use ($dek, $categoryNames) {
                /** @var SavingsCategory|null $category */
                $category = $categoryNames->get($categoryId);

                return [
                    'category_id' => $categoryId,
                    'name' => $category?->name ?? __('Unknown fund'),
                    'total' => $this->sumSpends($group, $dek),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, FundSpend>  $spends
     * @return list<array{period: string, total: string}>
     */
    private function spendingOverTime(Collection $spends, string $dek): array
    {
        return $spends
            ->groupBy(fn (FundSpend $spend) => Carbon::parse($spend->spent_on)->format('Y-m'))
            ->map(fn (Collection $group, string $period) => [
                'period' => $period,
                'total' => $this->sumSpends($group, $dek),
            ])
            ->sortBy('period')
            ->values()
            ->all();
    }

    /**
     * @return list<array{period: string, income: string, spending: string}>
     */
    private function incomeVsSpending(SavingsPlan $plan, CarbonInterface $from, CarbonInterface $to, string $dek): array
    {
        $periods = IncomePeriod::query()
            ->where('plan_id', $plan->id)
            ->whereDate('period_start', '>=', $from->toDateString())
            ->whereDate('period_start', '<=', $to->toDateString())
            ->orderBy('period_start')
            ->get();

        if ($periods->isEmpty()) {
            return [];
        }

        $confirmedSpends = FundSpend::query()
            ->where('savings_plan_id', $plan->id)
            ->where('status', TransferStatus::Confirmed)
            ->get();

        $rows = [];

        foreach ($periods as $index => $period) {
            $periodStart = Carbon::parse($period->period_start)->startOfDay();
            $nextPeriod = $periods->get($index + 1);
            $periodEnd = $nextPeriod !== null
                ? Carbon::parse($nextPeriod->period_start)->subDay()->endOfDay()
                : $to->copy()->endOfDay();

            $spending = $confirmedSpends
                ->filter(function (FundSpend $spend) use ($periodStart, $periodEnd) {
                    $spentOn = Carbon::parse($spend->spent_on);

                    return $spentOn->gte($periodStart) && $spentOn->lte($periodEnd);
                });

            $income = $this->decryptAmount($dek, $period->getRawOriginal('amount_encrypted')) ?? '0.00';

            $rows[] = [
                'period' => $period->period_start->toDateString(),
                'income' => $income,
                'spending' => $this->sumSpends($spending, $dek),
            ];
        }

        return $rows;
    }

    /**
     * @param  Collection<int, FundSpend>  $spends
     * @return list<array{recipient_id: string, name: string, total: string}>
     */
    private function topRecipients(Collection $spends, string $dek): array
    {
        return $spends
            ->filter(fn (FundSpend $spend) => $spend->recipient_id !== null)
            ->groupBy('recipient_id')
            ->map(function (Collection $group, string $recipientId) use ($dek) {
                /** @var FundSpend $first */
                $first = $group->first();

                return [
                    'recipient_id' => $recipientId,
                    'name' => $first->recipient?->name ?? __('Unknown recipient'),
                    'total' => $this->sumSpends($group, $dek),
                ];
            })
            ->sortByDesc(fn (array $row) => (float) $row['total'])
            ->take(self::TOP_RECIPIENTS_LIMIT)
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, FundSpend>  $spends
     */
    private function sumSpends(Collection $spends, string $dek): string
    {
        $total = '0.00';

        foreach ($spends as $spend) {
            $plain = $this->decryptAmount($dek, $spend->getRawOriginal('amount_encrypted'));

            if ($plain !== null) {
                $total = bcadd($total, $plain, 2);
            }
        }

        return $total;
    }

    private function decryptAmount(string $dek, mixed $encrypted): ?string
    {
        if (! is_string($encrypted) || $encrypted === '') {
            return null;
        }

        return $this->encryption->tryDecryptForDisplay($dek, $encrypted);
    }
}
