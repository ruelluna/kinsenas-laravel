<?php

namespace App\Services\Notifications;

use App\Models\FundSpend;
use App\Models\FundTransfer;
use App\Models\SavingsPlan;
use App\Models\Team;
use App\Models\User;
use App\Notifications\Savings\PendingSpendConfirmation;
use App\Notifications\Savings\PendingTransferConfirmation;
use Illuminate\Support\Collection;

class PendingActionNotificationService
{
    public function __construct(private NotificationPreferenceService $preferenceService) {}

    public function notifyForSpend(FundSpend $spend, ?User $except = null): void
    {
        $spend->loadMissing('plan.team');

        if ($spend->plan === null) {
            return;
        }

        $this->notifyTeamMembers(
            $spend->plan,
            new PendingSpendConfirmation($spend),
            $except,
            'spendId',
            (string) $spend->id,
        );
    }

    public function notifyForTransfer(FundTransfer $transfer, ?User $except = null): void
    {
        $transfer->loadMissing('plan.team');

        if ($transfer->plan === null) {
            return;
        }

        $this->notifyTeamMembers(
            $transfer->plan,
            new PendingTransferConfirmation($transfer),
            $except,
            'transferId',
            (string) $transfer->id,
        );
    }

    /**
     * @param  PendingSpendConfirmation|PendingTransferConfirmation  $notification
     */
    private function notifyTeamMembers(
        SavingsPlan $plan,
        object $notification,
        ?User $except,
        string $metaKey,
        string $metaValue,
    ): void {
        $team = $plan->team;

        if ($team === null) {
            return;
        }

        $this->recipientsForTeam($team, $except)->each(function (User $member) use ($notification, $metaKey, $metaValue): void {
            if ($this->hasUnreadNotification($member, $metaKey, $metaValue)) {
                return;
            }

            $member->notify($notification);
        });
    }

    /**
     * @return Collection<int, User>
     */
    private function recipientsForTeam(Team $team, ?User $except): Collection
    {
        return $team->members()
            ->when($except !== null, fn ($query) => $query->where('users.id', '!=', $except->id))
            ->get();
    }

    private function hasUnreadNotification(User $user, string $metaKey, string $metaValue): bool
    {
        return $user->unreadNotifications()
            ->where('data->meta->'.$metaKey, $metaValue)
            ->exists();
    }
}
