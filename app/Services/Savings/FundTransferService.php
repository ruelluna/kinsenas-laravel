<?php

namespace App\Services\Savings;

use App\Enums\TransferStatus;
use App\Models\FundTransfer;
use App\Models\SavingsCategory;
use App\Models\SavingsPlan;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class FundTransferService
{
    public function __construct(private FundBalanceService $balanceService)
    {
    }

    public function create(
        SavingsPlan $plan,
        string $categoryId,
        string $bankId,
        string $amount,
        string $description,
        string $transferredOn,
        ?User $user = null,
    ): FundTransfer {
        $this->assertBankAllowedForCategory($plan, $categoryId, $bankId);

        return FundTransfer::query()->create([
            'savings_plan_id' => $plan->id,
            'category_id' => $categoryId,
            'bank_id' => $bankId,
            'amount_encrypted' => $amount,
            'description' => $description,
            'transferred_on' => $transferredOn,
            'status' => TransferStatus::Pending,
            'confirmed_at' => null,
            'confirmed_by_user_id' => null,
        ]);
    }

    public function confirm(FundTransfer $transfer, User $user): FundTransfer
    {
        if ($transfer->status === TransferStatus::Confirmed) {
            return $transfer;
        }

        $transfer->loadMissing('plan');

        $amount = $transfer->amount_encrypted;

        if ($amount === null) {
            throw ValidationException::withMessages([
                'amount' => __('Unlock your vault to confirm this transfer.'),
            ]);
        }

        $this->balanceService->assertCanTransfer($transfer->plan, $transfer->category_id, $amount);

        $transfer->update([
            'status' => TransferStatus::Confirmed,
            'confirmed_at' => now(),
            'confirmed_by_user_id' => $user->id,
        ]);

        return $transfer->fresh(['bank', 'category']);
    }

    /**
     * @return Collection<int, FundTransfer>
     */
    public function recentForPlan(SavingsPlan $plan, int $limit = 50): Collection
    {
        return FundTransfer::query()
            ->where('savings_plan_id', $plan->id)
            ->with(['bank.institution', 'category'])
            ->latest('transferred_on')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function assertBankAllowedForCategory(SavingsPlan $plan, string $categoryId, ?string $bankId): void
    {
        if ($bankId === null) {
            return;
        }

        $category = SavingsCategory::query()
            ->where('plan_id', $plan->id)
            ->where('id', $categoryId)
            ->with('banks')
            ->first();

        if ($category === null) {
            throw ValidationException::withMessages([
                'category_id' => __('The selected fund is not part of your savings plan.'),
            ]);
        }

        if ($category->banks->isEmpty()) {
            return;
        }

        if (! $category->banks->contains('id', $bankId)) {
            throw ValidationException::withMessages([
                'bank_id' => __('This bank is not assigned to the selected fund.'),
            ]);
        }
    }
}
