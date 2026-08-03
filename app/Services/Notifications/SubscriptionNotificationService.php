<?php

namespace App\Services\Notifications;

use App\Models\Subscription;
use App\Models\User;
use App\Notifications\Billing\SubscriptionPastDue;

class SubscriptionNotificationService
{
    public function notifyPastDue(Subscription $subscription): void
    {
        $subscription->loadMissing('team');

        $team = $subscription->team;

        if ($team === null) {
            return;
        }

        $team->members()
            ->get()
            ->filter(fn (User $member) => $member->canManageBilling($team))
            ->each(function (User $member) use ($subscription): void {
                if ($this->hasPastDueNotification($member, (string) $subscription->id)) {
                    return;
                }

                $member->notify(new SubscriptionPastDue($subscription));
            });
    }

    private function hasPastDueNotification(User $user, string $subscriptionId): bool
    {
        return $user->notifications()
            ->where('data->meta->subscriptionId', $subscriptionId)
            ->where('data->kind', 'subscription_past_due')
            ->exists();
    }
}
