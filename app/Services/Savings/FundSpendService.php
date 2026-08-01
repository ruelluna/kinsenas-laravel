<?php

namespace App\Services\Savings;

use App\Enums\TransferStatus;
use App\Models\FundSpend;
use App\Models\SavingsPlan;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class FundSpendService
{
    public function __construct(private FundBalanceService $balanceService) {}

    public function create(
        SavingsPlan $plan,
        string $categoryId,
        string $amount,
        string $description,
        string $spentOn,
        ?string $bankId = null,
        ?string $recipientId = null,
        ?User $user = null,
        ?string $receiptImagePath = null,
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
            'receipt_image_path' => $receiptImagePath,
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
            throw ValidationException::withMessages([
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

    public function update(
        FundSpend $spend,
        SavingsPlan $plan,
        string $categoryId,
        string $amount,
        string $description,
        string $spentOn,
        ?string $recipientId = null,
        ?string $receiptImagePath = null,
        bool $removeReceipt = false,
    ): FundSpend {
        $spend->loadMissing('plan');

        $this->balanceService->assertCanUpdateSpend($plan, $spend, $categoryId, $amount);

        $updates = [
            'category_id' => $categoryId,
            'amount_encrypted' => $amount,
            'description' => $description,
            'spent_on' => $spentOn,
            'recipient_id' => $recipientId,
        ];

        if ($receiptImagePath !== null) {
            if ($spend->receipt_image_path !== null) {
                Storage::disk('public')->delete($spend->receipt_image_path);
            }

            $updates['receipt_image_path'] = $receiptImagePath;
        } elseif ($removeReceipt && $spend->receipt_image_path !== null) {
            Storage::disk('public')->delete($spend->receipt_image_path);
            $updates['receipt_image_path'] = null;
        }

        $spend->update($updates);

        return $spend->fresh(['bank', 'recipient', 'category']);
    }

    public function delete(FundSpend $spend, SavingsPlan $plan): void
    {
        $this->balanceService->assertCanDeleteSpend($plan);

        if ($spend->receipt_image_path !== null) {
            Storage::disk('public')->delete($spend->receipt_image_path);
        }

        $spend->delete();
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
