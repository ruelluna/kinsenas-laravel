<?php

namespace App\Services\Dashboard;

use App\Enums\ReimbursementStatus;
use App\Enums\SubscriptionFeature;
use App\Enums\TransferStatus;
use App\Models\FundAddedEntry;
use App\Models\FundSpend;
use App\Models\FundTransfer;
use App\Models\SavingsPlan;
use App\Models\Team;
use App\Models\User;
use App\Services\Billing\SubscriptionService;
use App\Services\Savings\FundBalanceService;
use App\Services\Savings\FundSpendReimbursementService;
use App\Services\Savings\FundSpendService;
use App\Services\Savings\FundTransferService;
use App\Services\Savings\SavingsPlanService;
use App\Services\Teams\TeamSetupService;

class DashboardSummaryService
{
    public function __construct(
        private SavingsPlanService $planService,
        private FundBalanceService $balanceService,
        private FundSpendService $fundSpendService,
        private FundTransferService $fundTransferService,
        private FundSpendReimbursementService $reimbursementService,
        private SubscriptionService $subscriptionService,
        private TeamSetupService $teamSetupService,
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
        $canDrawFromFunds = $plan?->canDrawFromFunds() ?? false;
        $hasOpeningBalances = $plan?->hasOpeningBalances() ?? false;
        $hasBank = $team->banks()->exists();
        $hasSpending = $hasPlan && FundSpend::query()
            ->where('savings_plan_id', $plan->id)
            ->where('status', TransferStatus::Confirmed)
            ->exists();

        $steps = $this->teamSetupService->dashboardSetupSteps($team, $user, $hasSpending);

        $setupComplete = collect($steps)->every(fn (array $step) => $step['complete']);

        $fundBalances = $plan !== null
            ? $this->balanceService->balancesWithDefaultFirst($plan)
            : [];

        $bankBalances = $plan !== null && ($plan->canDrawFromFunds() || $plan->hasOpeningBalances())
            ? $this->balanceService->bankBalancesForTeam($team, $plan)
            : [];

        $summary = $this->buildSummary($fundBalances, $plan);

        $canTransfers = $this->subscriptionService->userHasFeature($user, SubscriptionFeature::Transfers);
        $canReports = $this->subscriptionService->userHasFeature($user, SubscriptionFeature::Reports);

        $pendingActions = $plan !== null
            ? $this->pendingActions($plan, $teamSlug, $canTransfers)
            : ['transfers' => [], 'spends' => [], 'reimbursements' => []];

        $recentActivity = $plan !== null
            ? $this->recentActivity($plan)
            : [];

        $quickSpend = $plan !== null && $canDrawFromFunds
            ? [
                'defaultCategoryId' => $this->balanceService->defaultCategoryId($plan),
                'categories' => $this->balanceService->categoriesWithDefaultFirst($plan)
                    ->map(fn ($category) => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'bankId' => $category->bank_id,
                    ])
                    ->values()
                    ->all(),
                'recipients' => $team->recipients()->get(['id', 'name'])->all(),
            ]
            : null;

        return [
            'setup' => [
                'hasPlan' => $hasPlan,
                'hasIncome' => $hasIncome,
                'canDrawFromFunds' => $canDrawFromFunds,
                'hasOpeningBalances' => $hasOpeningBalances,
                'hasBank' => $hasBank,
                'hasSpending' => $hasSpending,
                'complete' => $setupComplete,
                'steps' => $steps,
            ],
            'plan' => $plan !== null ? [
                'id' => $plan->id,
                'name' => $plan->name,
                'canDrawFromFunds' => $plan->canDrawFromFunds(),
                'hasIncome' => $hasIncome,
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
            'quickSpend' => $quickSpend,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $fundBalances
     * @return array<string, mixed>
     */
    private function buildSummary(array $fundBalances, ?SavingsPlan $plan): array
    {
        $defaultFundName = null;
        $defaultFundRemaining = null;
        $otherFundsRemaining = null;
        $lowBalanceFunds = [];
        $pendingTransferCount = 0;
        $pendingSpendCount = 0;
        $awaitingReimbursementCount = 0;

        if ($fundBalances !== []) {
            $otherFundsRemaining = '0.00';

            foreach ($fundBalances as $balance) {
                $remaining = $balance['remaining'] ?? null;

                if ($balance['isDefault'] ?? false) {
                    $defaultFundName = $balance['name'];
                    $defaultFundRemaining = $remaining;
                } elseif ($remaining !== null) {
                    $otherFundsRemaining = bcadd($otherFundsRemaining, $remaining, 2);
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

            $awaitingReimbursementCount = FundSpend::query()
                ->where('savings_plan_id', $plan->id)
                ->where('expects_reimbursement', true)
                ->whereNull('reimbursement_closed_at')
                ->where('status', TransferStatus::Confirmed)
                ->with('reimbursements')
                ->get()
                ->filter(function (FundSpend $spend) {
                    $status = $this->reimbursementService->totalsForSpend($spend)['status'];

                    return in_array($status, [ReimbursementStatus::Awaiting, ReimbursementStatus::Partial], true);
                })
                ->count();
        }

        $attentionCount = $pendingTransferCount + $pendingSpendCount + $awaitingReimbursementCount + count($lowBalanceFunds);

        return [
            'defaultFundName' => $defaultFundName,
            'defaultFundRemaining' => $defaultFundRemaining,
            'otherFundsRemaining' => $otherFundsRemaining,
            'attentionCount' => $attentionCount,
            'pendingTransferCount' => $pendingTransferCount,
            'pendingSpendCount' => $pendingSpendCount,
            'awaitingReimbursementCount' => $awaitingReimbursementCount,
            'lowBalanceFunds' => $lowBalanceFunds,
        ];
    }

    /**
     * @return array{transfers: list<array<string, mixed>>, spends: list<array<string, mixed>>, reimbursements: list<array<string, mixed>>}
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

        $reimbursements = FundSpend::query()
            ->where('savings_plan_id', $plan->id)
            ->where('expects_reimbursement', true)
            ->whereNull('reimbursement_closed_at')
            ->where('status', TransferStatus::Confirmed)
            ->with(['category', 'expectedFromRecipient', 'reimbursements'])
            ->latest('spent_on')
            ->latest()
            ->limit(10)
            ->get()
            ->filter(function (FundSpend $spend) {
                $status = $this->reimbursementService->totalsForSpend($spend)['status'];

                return in_array($status, [ReimbursementStatus::Awaiting, ReimbursementStatus::Partial], true);
            })
            ->map(function (FundSpend $spend) use ($teamSlug) {
                $totals = $this->reimbursementService->totalsForSpend($spend);

                return [
                    'id' => $spend->id,
                    'type' => 'reimbursement',
                    'amount' => $totals['remaining'],
                    'description' => $spend->description,
                    'date' => $spend->spent_on->toDateString(),
                    'label' => trim(
                        ($spend->category?->name ?? '')
                        .($spend->expectedFromRecipient?->name ? " · from {$spend->expectedFromRecipient->name}" : ''),
                    ),
                    'confirmHref' => "/{$teamSlug}/savings/spending",
                ];
            })
            ->values()
            ->all();

        return [
            'transfers' => $transfers,
            'spends' => $spends,
            'reimbursements' => $reimbursements,
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

        $fundAdditions = FundAddedEntry::query()
            ->where('savings_plan_id', $plan->id)
            ->with('category')
            ->latest('added_on')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (FundAddedEntry $entry) => [
                'id' => "fund-addition-{$entry->id}",
                'type' => 'fund_addition',
                'amount' => $entry->amount_encrypted,
                'description' => null,
                'date' => $entry->added_on->toDateString(),
                'label' => $entry->category?->name ?? $entry->category_name,
            ]);

        return $spends
            ->concat($transfers)
            ->concat($fundAdditions)
            ->sortByDesc('date')
            ->take($limit)
            ->values()
            ->all();
    }
}
