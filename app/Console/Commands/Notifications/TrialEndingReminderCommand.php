<?php

namespace App\Console\Commands\Notifications;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\Billing\TrialEndingReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class TrialEndingReminderCommand extends Command
{
    protected $signature = 'notifications:trial-ending-reminder';

    protected $description = 'Notify billing managers when a trial is ending within three days';

    public function handle(): int
    {
        $notified = 0;
        $thresholds = [3, 1];

        foreach ($thresholds as $daysRemaining) {
            $windowStart = now()->addDays($daysRemaining)->startOfDay();
            $windowEnd = now()->addDays($daysRemaining)->endOfDay();

            Subscription::query()
                ->where('status', SubscriptionStatus::Trialing)
                ->whereNotNull('trial_ends_at')
                ->whereBetween('trial_ends_at', [$windowStart, $windowEnd])
                ->with('team')
                ->each(function (Subscription $subscription) use ($daysRemaining, &$notified): void {
                    $team = $subscription->team;

                    if ($team === null) {
                        return;
                    }

                    $team->members()
                        ->get()
                        ->filter(fn (User $member) => $member->canManageBilling($team))
                        ->each(function (User $member) use ($subscription, $daysRemaining, &$notified): void {
                            if ($this->hasUnreadTrialReminder($member, (string) $subscription->id, $daysRemaining)) {
                                return;
                            }

                            $member->notify(new TrialEndingReminder($subscription, $daysRemaining));
                            $notified++;
                        });
                });
        }

        $this->info("Sent {$notified} trial ending reminder(s).");

        return self::SUCCESS;
    }

    private function hasUnreadTrialReminder(User $user, string $subscriptionId, int $daysRemaining): bool
    {
        return $user->notifications()
            ->where('data->meta->subscriptionId', $subscriptionId)
            ->where('data->meta->daysRemaining', $daysRemaining)
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->exists();
    }
}
