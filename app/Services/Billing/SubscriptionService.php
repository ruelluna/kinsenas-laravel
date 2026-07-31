<?php

namespace App\Services\Billing;

use App\Enums\BillingInterval;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function startTrial(User $user, ?SubscriptionPlan $plan = null): Subscription
    {
        $plan ??= SubscriptionPlan::query()->firstOrCreate(
            ['slug' => config('billing.default_plan_slug')],
            [
                'name' => 'Basic',
                'trial_days' => 14,
                'features' => [],
                'is_active' => true,
                'sort_order' => 1,
            ],
        );

        return DB::transaction(function () use ($user, $plan) {
            return Subscription::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'plan_id' => $plan->id,
                    'status' => SubscriptionStatus::Trialing,
                    'trial_ends_at' => now()->addDays($plan->trial_days),
                    'current_period_ends_at' => null,
                ],
            );
        });
    }

    public function activate(User $user, BillingInterval $interval): Subscription
    {
        $subscription = $user->subscription()->with('plan.prices')->firstOrFail();
        $months = $interval === BillingInterval::Yearly ? 12 : 1;

        $subscription->update([
            'status' => SubscriptionStatus::Active,
            'trial_ends_at' => null,
            'current_period_ends_at' => now()->addMonths($months),
        ]);

        return $subscription->fresh('plan');
    }

    public function markPastDue(Subscription $subscription): void
    {
        if ($subscription->status === SubscriptionStatus::Trialing && $subscription->trial_ends_at?->isPast()) {
            $subscription->update(['status' => SubscriptionStatus::PastDue]);
        }
    }

    public function userHasAccess(User $user): bool
    {
        $subscription = $user->subscription;

        if ($subscription === null) {
            return false;
        }

        if ($subscription->status === SubscriptionStatus::Trialing && $subscription->trial_ends_at?->isPast()) {
            return false;
        }

        if ($subscription->status === SubscriptionStatus::Active && $subscription->current_period_ends_at?->isPast()) {
            return false;
        }

        return $subscription->allowsSavingsAccess();
    }
}
