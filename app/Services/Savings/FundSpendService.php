<?php

namespace App\Services\Savings;

use App\Enums\TransferStatus;
use App\Models\FundSpend;
use App\Models\SavingsPlan;
use App\Models\User;
use Illuminate\Support\Collection;

class FundSpendService
{
    public function __construct(private FundBalanceService $balanceService)
    {
    }

    public function create(
        SavingsPlan $plan,
        string $categoryId,
        string $amount,
        string $description,
        string $spentOn,
        ?string $bankId = null,
        ?string $recipientId = null,
        ?User $user = null,
    ): FundSpend {
        $needsConfirmation = $bankId !== null;

        if (! $needsConfirmation) {
            $this->balanceService->assertCanSpend($plan, $categoryId, $amount);
        }
        $now = now();

        return FundSpend::query()->create([
            'savings_plan_id' => $plan->id,
            'category_id' => $categoryId,
            'amount_encrypted' => $amount,
            'description' => $description,
            'spent_on' => $spentOn,
            'bank_id' => $bankId,
            'recipient_id' => $recipientId,
            'status' => $needsConfirmation ? TransferStatus::Pending : TransferStatus::Confirmed,
            'confirmed_at' => $needsConfirmation ? null : $now,
            'confirmed_by_user_id' => $needsConfirmation ? null : $user?->id,
        ]);
    }

    public function confirm(FundSpend $spend, User $user): FundSpend
    {
        if ($spend->status === TransferStatus::Confirmed) {
            return $spend;
        }

        $spend->loadMissing('plan');

        $amount = $spend->amount_encrypted;

        if ($amount === null) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'amount' => __('Unlock your vault to confirm spending.'),
            ]);
        }

        $this->balanceService->assertCanSpend($spend->plan, $spend->category_id, $amount);

        $spend->update([
            'status' => TransferStatus::Confirmed,
            'confirmed_at' => now(),
            'confirmed_by_user_id' => $user->id,
        ]);

        return $spend->fresh(['bank', 'recipient', 'category']);
    }

    /**
     * @return Collection<int, FundSpend>
     */
    public function recentForPlan(SavingsPlan $plan, int $limit = 50): Collection
    {
        return FundSpend::query()
            ->where('savings_plan_id', $plan->id)
            ->with(['bank', 'recipient', 'category'])
            ->latest('spent_on')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
