<?php

namespace App\Services\Savings;

use App\Enums\ReimbursementStatus;
use App\Models\FundSpend;
use App\Models\FundSpendReimbursement;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class FundSpendReimbursementService
{
    public function __construct(
        private FundBalanceService $balanceService,
    ) {}

    /**
     * @return array{received: string, remaining: string, status: ReimbursementStatus}
     */
    public function totalsForSpend(FundSpend $spend): array
    {
        $spend->loadMissing('reimbursements');

        if (! $spend->expects_reimbursement) {
            return [
                'received' => '0.00',
                'remaining' => '0.00',
                'status' => ReimbursementStatus::None,
            ];
        }

        if ($spend->reimbursement_closed_at !== null) {
            $received = $this->sumReimbursements($spend);

            return [
                'received' => $received,
                'remaining' => '0.00',
                'status' => ReimbursementStatus::Closed,
            ];
        }

        $spendAmount = $spend->amount_encrypted;

        if ($spendAmount === null) {
            return [
                'received' => '0.00',
                'remaining' => '0.00',
                'status' => ReimbursementStatus::Awaiting,
            ];
        }

        $received = $this->sumReimbursements($spend);
        $remaining = bcsub($spendAmount, $received, 2);

        if (bccomp($remaining, '0', 2) !== 1) {
            return [
                'received' => $received,
                'remaining' => '0.00',
                'status' => ReimbursementStatus::Resolved,
            ];
        }

        if (bccomp($received, '0', 2) === 1) {
            return [
                'received' => $received,
                'remaining' => $remaining,
                'status' => ReimbursementStatus::Partial,
            ];
        }

        return [
            'received' => $received,
            'remaining' => $remaining,
            'status' => ReimbursementStatus::Awaiting,
        ];
    }

    public function record(
        FundSpend $spend,
        string $amount,
        string $receivedOn,
        ?string $bankId,
        ?string $notes,
        User $user,
    ): FundSpendReimbursement {
        $this->assertCanRecordReimbursement($spend, $amount);

        $spend->loadMissing('plan.team');

        if ($bankId !== null) {
            abort_if(
                ! $spend->plan->team->banks()->where('id', $bankId)->exists(),
                404,
            );
        }

        return FundSpendReimbursement::query()->create([
            'fund_spend_id' => $spend->id,
            'savings_plan_id' => $spend->savings_plan_id,
            'category_id' => $spend->category_id,
            'amount_encrypted' => $amount,
            'received_on' => $receivedOn,
            'bank_id' => $bankId,
            'notes' => $notes,
            'created_by_user_id' => $user->id,
        ]);
    }

    public function closeExpectation(FundSpend $spend): FundSpend
    {
        if (! $spend->expects_reimbursement) {
            throw ValidationException::withMessages([
                'fund_spend' => __('This spending is not expecting payback.'),
            ]);
        }

        $totals = $this->totalsForSpend($spend);

        if ($totals['status'] === ReimbursementStatus::Resolved) {
            throw ValidationException::withMessages([
                'fund_spend' => __('This spending is already fully repaid.'),
            ]);
        }

        if ($totals['status'] === ReimbursementStatus::Closed) {
            return $spend;
        }

        $spend->update(['reimbursement_closed_at' => now()]);

        return $spend->fresh(['reimbursements', 'expectedFromRecipient', 'category', 'bank', 'recipient']);
    }

    public function assertCanRecordReimbursement(FundSpend $spend, string $amount): void
    {
        if (! $spend->expects_reimbursement) {
            throw ValidationException::withMessages([
                'amount' => __('This spending is not expecting payback.'),
            ]);
        }

        if ($spend->reimbursement_closed_at !== null) {
            throw ValidationException::withMessages([
                'amount' => __('Payback is no longer expected for this spending.'),
            ]);
        }

        $totals = $this->totalsForSpend($spend);

        if ($totals['status'] === ReimbursementStatus::Resolved) {
            throw ValidationException::withMessages([
                'amount' => __('This spending is already fully repaid.'),
            ]);
        }

        $spendAmount = $spend->amount_encrypted;

        if ($spendAmount === null) {
            throw ValidationException::withMessages([
                'amount' => __('Unlock your vault to record payback.'),
            ]);
        }

        if (bccomp($amount, '0.01', 2) === -1) {
            throw ValidationException::withMessages([
                'amount' => __('Enter a valid payback amount.'),
            ]);
        }

        if (bccomp($amount, $totals['remaining'], 2) === 1) {
            throw ValidationException::withMessages([
                'amount' => __('Payback amount cannot exceed :remaining owed.', [
                    'remaining' => $totals['remaining'],
                ]),
            ]);
        }
    }

    private function sumReimbursements(FundSpend $spend): string
    {
        $total = '0.00';

        foreach ($spend->reimbursements as $reimbursement) {
            $amount = $reimbursement->amount_encrypted;

            if ($amount === null) {
                continue;
            }

            $total = bcadd($total, $amount, 2);
        }

        return $total;
    }
}
