<?php

namespace App\Console\Commands;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Services\Billing\SubscriptionService;
use Illuminate\Console\Command;

class SyncSubscriptionStatusCommand extends Command
{
    protected $signature = 'billing:sync-subscription-status';

    protected $description = 'Mark expired trials and billing periods as past due';

    public function handle(SubscriptionService $subscriptionService): int
    {
        $updated = 0;

        Subscription::query()
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('status', SubscriptionStatus::Trialing)
                        ->whereNotNull('trial_ends_at')
                        ->where('trial_ends_at', '<', now());
                })->orWhere(function ($query) {
                    $query->where('status', SubscriptionStatus::Active)
                        ->whereNotNull('current_period_ends_at')
                        ->where('current_period_ends_at', '<', now());
                });
            })
            ->chunkById(100, function ($subscriptions) use ($subscriptionService, &$updated) {
                foreach ($subscriptions as $subscription) {
                    if ($subscriptionService->syncExpiredStatus($subscription)) {
                        $updated++;
                    }
                }
            });

        $this->info("Updated {$updated} subscription(s) to past due.");

        return self::SUCCESS;
    }
}
