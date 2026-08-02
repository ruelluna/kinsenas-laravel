<?php

namespace App\Services\Dashboard;

use App\Enums\SubscriptionFeature;
use App\Enums\TransferStatus;
use App\Models\FundSpend;
use App\Models\FundTransfer;
use App\Models\IncomePeriod;
use App\Models\SavingsPlan;
use App\Models\Team;
use App\Models\User;
use App\Services\Billing\SubscriptionService;
use App\Services\Savings\FundBalanceService;
use App\Services\Savings\FundSpendService;
use App\Services\Savings\FundTransferService;
use App\Services\Savings\SavingsPlanService;

class DashboardSummaryService
{
    public function __construct(
        private SavingsPlanService $planService,
        private FundBalanceService $balanceService,
        private FundSpendService $fundSpendService,
        private FundTransferService $fundTransferService,
        private SubscriptionService $subscriptionService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forTeam(Team $team, User $user): array
    {
        $plan = $this->planService->forTeam($team, $user);
        $teamSlug = $team->slug;
        $savingsBase = "/{$teamSlug}/savings";

        $hasPlan = $plan !== null;
        $hasIncome = $plan?->hasIncomePeriod() ?? false;
        $hasLockedIncome = $plan?->hasLockedIncomePeriod() ?? false;
        $hasOpeningBalances = $plan?->hasOpeningBalances() ?? false;
        $hasBank = $team->banks()->exists();
        $hasSpending = $hasPlan && FundSpend::query()
            ->where('savings_plan_id', $plan->id)
            ->where('status', TransferStatus::Confirmed)
            ->exists();

        $latestUnlockedPeriod = $hasPlan
            ? IncomePeriod::query()
                ->where('plan_id', $plan->id)
                ->where('is_locked', false)
                ->latest('period_start')
                ->latest()
                ->first()
            : null;

        $lockIncomeHref = $latestUnlockedPeriod !== null
            ? "{$savingsBase}/income/{$latestUnlockedPeriod->id}"
            : "{$savingsBase}/income";

        $steps = [
            [
                'key' => 'bank',
                'label' => 'Add your banks',
                'complete' => $hasBank,
                'href' => "{$savingsBase}/banks",
            ],
            [
                'key' => 'plan',
                'label' => 'Choose a savings plan',
                'complete' => $hasPlan,
                'href' => "{$savingsBase}/plan",
            ],
            [
                'key' => 'income',
                'label' => 'Add income',
                'complete' => $hasIncome,
                'href' => "{$savingsBase}/income",
            ],
            [
                'key' => 'lock_income',
                'label' => 'Lock income',
                'complete' => $hasLockedIncome,
                'href' => $lockIncomeHref,
            ],
            [
                'key' => 'spending',
                'label' => 'Record spending',
                'complete' => $hasSpending,
                'href' => "{$savingsBase}/spending",
            ],
        ];

        $setupComplete = collect($steps)->every(fn (array $step) => $step['complete']);

        $fundBalances = $plan !== null && $plan->shouldShowFundBalances()
            ? $this->balanceService->balancesWithDefaultFirst($plan)
            : [];

        $bankBalances = $plan !== null && $plan->shouldShowFundBalances()
            ? $this->balanceService->bankBalancesForTeam($team, $plan)
            : [];

        $summary = $this->buildSummary($fundBalances, $bankBalances, $plan);

        $canTransfers = $this->subscriptionService->userHasFeature($user, SubscriptionFeature::Transfers);
        $canReports = $this->subscriptionService->userHasFeature($user, SubscriptionFeature::Reports);

        $pendingActions = $plan !== null
            ? $this->pendingActions($plan, $teamSlug, $canTransfers)
            : ['transfers' => [], 'spends' => []];

        $recentActivity = $plan !== null
            ? $this->recentActivity($plan)
            : [];

        return [
            'setup' => [
                'hasPlan' => $hasPlan,
                'hasIncome' => $hasIncome,
                'hasLockedIncome' => $hasLockedIncome,
                'hasOpeningBalances' => $hasOpeningBalances,
                'hasBank' => $hasBank,
                'hasSpending' => $hasSpending,
                'complete' => $setupComplete,
                'steps' => $steps,
            ],
            'plan' => $plan !== null ? [
                'id' => $plan->id,
                'name' => $plan->name,
                'hasLockedIncome' => $hasLockedIncome,
                'canDrawFromFunds' => $plan->canDrawFromFunds(),
            ] : null,
            'summary' => $summary,
            'fundBalances' => $fundBalances,
            'bankBalances' => $bankBalances,
            'pendingActions' => $pendingActions,
            'recentActivity' => $recentActivity,
            'features' => [
                'transfers' => $canTransfers,
                'reports' => $canReports,
            ],
            'quickLinks' => [
                'income' => "{$savingsBase}/income",
                'spending' => "{$savingsBase}/spending",
                'transfers' => "{$savingsBase}/transfers",
                'banks' => "{$savingsBase}/banks",
                'plan' => "{$savingsBase}/plan",
                'reports' => "{$savingsBase}/reports",
            ],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $fundBalances
     * @param  list<array<string, mixed>>  $bankBalances
     * @return array<string, mixed>
     */
    private function buildSummary(array $fundBalances, array $bankBalances, ?SavingsPlan $plan): array
    {
        $totalRemaining = null;
        $totalInBanks = null;
        $lowBalanceFunds = [];
        $pendingTransferCount = 0;
        $pendingSpendCount = 0;

        if ($fundBalances !== []) {
            $totalRemaining = '0.00';

            foreach ($fundBalances as $balance) {
                $remaining = $balance['remaining'] ?? null;

                if ($remaining !== null) {
                    $totalRemaining = bcadd($totalRemaining, $remaining, 2);
                }

                $percentUsed = $balance['percentUsed'] ?? null;

                if ($percentUsed !== null && $percentUsed >= 90) {
                    $lowBalanceFunds[] = [
                        'categoryId' => $balance['categoryId'],
                        'name' => $balance['name'],
                        'percentUsed' => $percentUsed,
                    ];
                }
            }
        }

        if ($bankBalances !== []) {
            $totalInBanks = '0.00';

            foreach ($bankBalances as $bank) {
                $totalInBanks = bcadd($totalInBanks, $bank['total'], 2);
            }
        }

        if ($plan !== null) {
            $pendingTransferCount = FundTransfer::query()
                ->where('savings_plan_id', $plan->id)
                ->where('status', TransferStatus::Pending)
                ->count();

            $pendingSpendCount = FundSpend::query()
                ->where('savings_plan_id', $plan->id)
                ->where('status', TransferStatus::Pending)
                ->whereNotNull('bank_id')
                ->count();
        }

        $attentionCount = $pendingTransferCount + $pendingSpendCount + count($lowBalanceFunds);

        return [
            'totalRemaining' => $totalRemaining,
            'totalInBanks' => $totalInBanks,
            'attentionCount' => $attentionCount,
            'pendingTransferCount' => $pendingTransferCount,
            'pendingSpendCount' => $pendingSpendCount,
            'lowBalanceFunds' => $lowBalanceFunds,
        ];
    }

    /**
     * @return array{transfers: list<array<string, mixed>>, spends: list<array<string, mixed>>}
     */
    private function pendingActions(SavingsPlan $plan, string $teamSlug, bool $canTransfers): array
    {
        $transfers = $canTransfers
            ? FundTransfer::query()
                ->where('savings_plan_id', $plan->id)
                ->where('status', TransferStatus::Pending)
                ->with(['fromCategory', 'toCategory', 'fromBank', 'toBank'])
                ->latest('transferred_on')
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn (FundTransfer $transfer) => [
                    'id' => $transfer->id,
                    'type' => 'transfer',
                    'amount' => $transfer->amount_encrypted,
                    'description' => $transfer->description,
                    'date' => $transfer->transferred_on->toDateString(),
                    'label' => trim(($transfer->fromCategory?->name ?? '').' → '.($transfer->toCategory?->name ?? '')),
                    'confirmHref' => "/{$teamSlug}/savings/transfers/{$transfer->id}/confirm",
                ])
                ->values()
                ->all()
            : [];

        $spends = FundSpend::query()
            ->where('savings_plan_id', $plan->id)
            ->where('status', TransferStatus::Pending)
            ->whereNotNull('bank_id')
            ->with(['category', 'bank'])
            ->latest('spent_on')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (FundSpend $spend) => [
                'id' => $spend->id,
                'type' => 'spend',
                'amount' => $spend->amount_encrypted,
                'description' => $spend->description,
                'date' => $spend->spent_on->toDateString(),
                'label' => trim(($spend->category?->name ?? '').($spend->bank?->name ? " · {$spend->bank->name}" : '')),
                'confirmHref' => "/{$teamSlug}/savings/spending/{$spend->id}/confirm",
            ])
            ->values()
            ->all();

        return [
            'transfers' => $transfers,
            'spends' => $spends,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentActivity(SavingsPlan $plan, int $limit = 8): array
    {
        $spends = $this->fundSpendService->recentForPlan($plan, 20)
            ->where('status', TransferStatus::Confirmed)
            ->map(fn (FundSpend $spend) => [
                'id' => "spend-{$spend->id}",
                'type' => 'spend',
                'amount' => $spend->amount_encrypted,
                'description' => $spend->description,
                'date' => $spend->spent_on->toDateString(),
                'label' => trim(
                    ($spend->category?->name ?? '')
                    .($spend->bank?->name ? " · {$spend->bank->name}" : '')
                    .($spend->recipient?->name ? " → {$spend->recipient->name}" : ''),
                ),
            ]);

        $transfers = $this->fundTransferService->recentForPlan($plan, 20)
            ->where('status', TransferStatus::Confirmed)
            ->map(fn (FundTransfer $transfer) => [
                'id' => "transfer-{$transfer->id}",
                'type' => 'transfer',
                'amount' => $transfer->amount_encrypted,
                'description' => $transfer->description,
                'date' => $transfer->transferred_on->toDateString(),
                'label' => trim(($transfer->fromCategory?->name ?? '').' → '.($transfer->toCategory?->name ?? '')),
            ]);

        return $spends
            ->concat($transfers)
            ->sortByDesc('date')
            ->take($limit)
            ->values()
            ->all();
    }
}
