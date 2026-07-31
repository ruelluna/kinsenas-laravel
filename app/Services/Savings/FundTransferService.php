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
    public function __construct(private FundBalanceService $balanceService) {}

    public function create(
        SavingsPlan $plan,
        string $fromCategoryId,
        string $toCategoryId,
        string $amount,
        string $description,
        string $transferredOn,
        ?User $user = null,
    ): FundTransfer {
        if ($fromCategoryId === $toCategoryId) {
            throw ValidationException::withMessages([
                'to_category_id' => __('Choose a different fund to transfer to.'),
            ]);
        }

        $fromCategory = $this->categoryForPlan($plan, $fromCategoryId, 'from_category_id');
        $toCategory = $this->categoryForPlan($plan, $toCategoryId, 'to_category_id');

        $fromBankId = $fromCategory->bank_id;
        $toBankId = $toCategory->bank_id;
        $sameBank = $fromBankId === $toBankId;

        if ($sameBank && $user !== null) {
            $this->balanceService->assertCanTransfer($plan, $fromCategoryId, $amount);
        }

        $transfer = FundTransfer::query()->create([
            'savings_plan_id' => $plan->id,
            'from_category_id' => $fromCategoryId,
            'to_category_id' => $toCategoryId,
            'from_bank_id' => $fromBankId,
            'to_bank_id' => $toBankId,
            'amount_encrypted' => $amount,
            'description' => $description,
            'transferred_on' => $transferredOn,
            'status' => $sameBank ? TransferStatus::Confirmed : TransferStatus::Pending,
            'confirmed_at' => $sameBank ? now() : null,
            'confirmed_by_user_id' => $sameBank ? $user?->id : null,
        ]);

        return $transfer->fresh(['fromBank.institution', 'toBank.institution', 'fromCategory', 'toCategory']);
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

        $this->balanceService->assertCanTransfer($transfer->plan, $transfer->from_category_id, $amount);

        $transfer->update([
            'status' => TransferStatus::Confirmed,
            'confirmed_at' => now(),
            'confirmed_by_user_id' => $user->id,
        ]);

        return $transfer->fresh(['fromBank.institution', 'toBank.institution', 'fromCategory', 'toCategory']);
    }

    /**
     * @return Collection<int, FundTransfer>
     */
    public function recentForPlan(SavingsPlan $plan, int $limit = 50): Collection
    {
        return FundTransfer::query()
            ->where('savings_plan_id', $plan->id)
            ->with([
                'fromBank.institution',
                'toBank.institution',
                'fromCategory',
                'toCategory',
            ])
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
            ->first();

        if ($category === null) {
            throw ValidationException::withMessages([
                'category_id' => __('The selected fund is not part of your savings plan.'),
            ]);
        }

        if ($category->bank_id === null) {
            return;
        }

        if ($category->bank_id !== $bankId) {
            throw ValidationException::withMessages([
                'bank_id' => __('This bank is not assigned to the selected fund.'),
            ]);
        }
    }

    private function categoryForPlan(SavingsPlan $plan, string $categoryId, string $field): SavingsCategory
    {
        $category = SavingsCategory::query()
            ->where('plan_id', $plan->id)
            ->where('id', $categoryId)
            ->first();

        if ($category === null) {
            throw ValidationException::withMessages([
                $field => __('The selected fund is not part of your savings plan.'),
            ]);
        }

        return $category;
    }
}
