<?php

namespace App\Services\Notifications;

use App\Models\FundSpend;
use App\Models\FundTransfer;
use App\Models\User;
use App\Notifications\Savings\PendingActionConfirmed;

class PendingActionConfirmedNotificationService
{
    public function notifyForSpend(FundSpend $spend, User $confirmer): void
    {
        $spend->loadMissing('plan');

        $creator = $spend->created_by_user_id !== null
            ? User::query()->find($spend->created_by_user_id)
            : null;

        if ($creator === null || $creator->is($confirmer)) {
            return;
        }

        if ($this->hasConfirmationNotification($creator, 'spendId', (string) $spend->id)) {
            return;
        }

        $creator->notify(new PendingActionConfirmed($spend, 'spend'));
    }

    public function notifyForTransfer(FundTransfer $transfer, User $confirmer): void
    {
        $transfer->loadMissing('plan');

        $creator = $transfer->created_by_user_id !== null
            ? User::query()->find($transfer->created_by_user_id)
            : null;

        if ($creator === null || $creator->is($confirmer)) {
            return;
        }

        if ($this->hasConfirmationNotification($creator, 'transferId', (string) $transfer->id)) {
            return;
        }

        $creator->notify(new PendingActionConfirmed($transfer, 'transfer'));
    }

    private function hasConfirmationNotification(User $user, string $metaKey, string $metaValue): bool
    {
        return $user->notifications()
            ->where('data->meta->'.$metaKey, $metaValue)
            ->where('data->kind', 'pending_action_confirmed')
            ->exists();
    }
}
