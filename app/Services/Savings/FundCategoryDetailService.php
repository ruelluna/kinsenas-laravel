<?php

namespace App\Services\Savings;

use App\Models\FundAddedEntry;
use App\Models\FundSpend;
use App\Models\FundTransfer;
use App\Models\IncomeAllocation;
use App\Models\SavingsCategory;
use App\Models\SavingsPlan;
use Illuminate\Support\Collection;

class FundCategoryDetailService
{
    public function __construct(
        private FundBalanceService $balanceService,
        private FundSpendReimbursementService $reimbursementService,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function balanceForCategory(SavingsPlan $plan, string $categoryId): ?array
    {
        $balances = $this->balanceService->balancesForPlan($plan);

        return collect($balances)->firstWhere('categoryId', $categoryId);
    }

    /**
     * @return list<array{
     *     id: string,
     *     amount: string|null,
     *     periodId: string,
     *     periodName: string,
     *     periodStart: string
     * }>
     */
    public function allocationsForCategory(SavingsPlan $plan, string $categoryId, int $limit = 50): array
    {
        return IncomeAllocation::query()
            ->where('category_id', $categoryId)
            ->whereHas('incomePeriod', fn ($query) => $query->where('plan_id', $plan->id))
            ->with('incomePeriod')
            ->join('income_periods', 'income_allocations.income_period_id', '=', 'income_periods.id')
            ->orderByDesc('income_periods.period_start')
            ->select('income_allocations.*')
            ->limit($limit)
            ->get()
            ->map(fn (IncomeAllocation $allocation) => [
                'id' => $allocation->id,
                'amount' => $allocation->amount_encrypted,
                'periodId' => $allocation->income_period_id,
                'periodName' => $allocation->incomePeriod?->name ?? '',
                'periodStart' => $allocation->incomePeriod?->period_start?->toDateString() ?? '',
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{
     *     id: string,
     *     amount: string|null,
     *     addedOn: string
     * }>
     */
    public function fundAddedEntriesForCategory(SavingsPlan $plan, string $categoryId, int $limit = 50): array
    {
        return FundAddedEntry::query()
            ->where('savings_plan_id', $plan->id)
            ->where('category_id', $categoryId)
            ->orderByDesc('added_on')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (FundAddedEntry $entry) => [
                'id' => $entry->id,
                'amount' => $entry->amount_encrypted,
                'addedOn' => $entry->added_on->toDateString(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function transfersForCategory(SavingsPlan $plan, string $categoryId, int $limit = 50): array
    {
        $transfers = FundTransfer::query()
            ->where('savings_plan_id', $plan->id)
            ->where(function ($query) use ($categoryId) {
                $query->where('from_category_id', $categoryId)
                    ->orWhere('to_category_id', $categoryId);
            })
            ->with([
                'fromBank.institution',
                'toBank.institution',
                'fromCategory',
                'toCategory',
            ])
            ->orderByDesc('transferred_on')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return $transfers
            ->map(fn (FundTransfer $transfer) => $this->transferPayload(
                $transfer,
                $transfer->from_category_id === $categoryId ? 'out' : 'in',
            ))
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function spendsForCategory(SavingsPlan $plan, string $categoryId, int $limit = 50): array
    {
        return FundSpend::query()
            ->where('savings_plan_id', $plan->id)
            ->where('category_id', $categoryId)
            ->with(['bank', 'recipient', 'category', 'expectedFromRecipient', 'reimbursements.bank'])
            ->orderByDesc('spent_on')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (FundSpend $spend) => $this->spendPayload($spend))
            ->values()
            ->all();
    }

    /**
     * @return list<SavingsCategory>
     */
    public function categoryOptions(SavingsPlan $plan): Collection
    {
        return $this->balanceService->categoriesWithDefaultFirst($plan);
    }

    /**
     * @return array<string, mixed>
     */
    private function transferPayload(FundTransfer $transfer, string $direction): array
    {
        return [
            'id' => $transfer->id,
            'amount' => $transfer->amount_encrypted,
            'description' => $transfer->description,
            'status' => $transfer->status->value,
            'transferredOn' => $transfer->transferred_on->toDateString(),
            'direction' => $direction,
            'fromCategoryName' => $transfer->fromCategory?->name,
            'toCategoryName' => $transfer->toCategory?->name,
            'fromCategoryId' => $transfer->from_category_id,
            'toCategoryId' => $transfer->to_category_id,
            'fromBankName' => $transfer->fromBank?->name,
            'toBankName' => $transfer->toBank?->name,
            'fromBankLogoUrl' => $transfer->fromBank?->institution?->logo_url,
            'toBankLogoUrl' => $transfer->toBank?->institution?->logo_url,
            'crossesBanks' => $transfer->crossesBanks(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function spendPayload(FundSpend $spend): array
    {
        $totals = $this->reimbursementService->totalsForSpend($spend);

        return [
            'id' => $spend->id,
            'amount' => $spend->amount_encrypted,
            'description' => $spend->description,
            'status' => $spend->status->value,
            'spentOn' => $spend->spent_on->toDateString(),
            'bankName' => $spend->bank?->name,
            'recipientName' => $spend->recipient?->name,
            'categoryName' => $spend->category?->name,
            'categoryId' => $spend->category_id,
            'recipientId' => $spend->recipient_id,
            'receiptImageUrl' => $spend->receiptImageUrl(),
            'expectsReimbursement' => $spend->expects_reimbursement,
            'expectedFromRecipientId' => $spend->expected_from_recipient_id,
            'expectedFromRecipientName' => $spend->expectedFromRecipient?->name,
            'reimbursementStatus' => $totals['status']->value,
            'reimbursedAmount' => $totals['received'],
            'remainingOwed' => $totals['remaining'],
            'reimbursements' => $spend->reimbursements->map(fn ($reimbursement) => [
                'id' => $reimbursement->id,
                'amount' => $reimbursement->amount_encrypted,
                'receivedOn' => $reimbursement->received_on->toDateString(),
                'bankName' => $reimbursement->bank?->name,
                'notes' => $reimbursement->notes,
            ])->values()->all(),
        ];
    }
}
