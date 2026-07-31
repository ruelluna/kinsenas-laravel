<?php

namespace App\Services\Billing;

use App\Enums\BillingInterval;
use App\Enums\SubscriptionFeature;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        if (! $plan->is_active) {
            $plan = SubscriptionPlan::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->firstOrFail();
        }

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

    public function activateManually(User $user, BillingInterval $interval, ?SubscriptionPlan $plan = null): Subscription
    {
        return DB::transaction(function () use ($user, $interval, $plan) {
            $subscription = $user->subscription()->firstOrFail();

            if ($plan !== null) {
                $subscription->update(['plan_id' => $plan->id]);
            }

            $months = $interval === BillingInterval::Yearly ? 12 : 1;

            $subscription->update([
                'status' => SubscriptionStatus::Active,
                'trial_ends_at' => null,
                'current_period_ends_at' => now()->addMonths($months),
            ]);

            Log::info('Subscription manually activated', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'interval' => $interval->value,
                'plan_id' => $subscription->plan_id,
            ]);

            return $subscription->fresh('plan');
        });
    }

    public function extendTrial(Subscription $subscription, int $days): Subscription
    {
        return DB::transaction(function () use ($subscription, $days) {
            $baseDate = $subscription->trial_ends_at?->isFuture()
                ? $subscription->trial_ends_at
                : now();

            $subscription->update([
                'status' => SubscriptionStatus::Trialing,
                'trial_ends_at' => $baseDate->copy()->addDays($days),
                'current_period_ends_at' => null,
            ]);

            Log::info('Subscription trial extended', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'days' => $days,
                'trial_ends_at' => $subscription->trial_ends_at?->toIso8601String(),
            ]);

            return $subscription->fresh('plan');
        });
    }

    public function cancel(Subscription $subscription, ?string $reason = null): Subscription
    {
        return DB::transaction(function () use ($subscription, $reason) {
            $subscription->update([
                'status' => SubscriptionStatus::Cancelled,
                'current_period_ends_at' => null,
            ]);

            Log::info('Subscription cancelled', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'reason' => $reason,
            ]);

            return $subscription->fresh('plan');
        });
    }

    public function changePlan(Subscription $subscription, SubscriptionPlan $plan): Subscription
    {
        return DB::transaction(function () use ($subscription, $plan) {
            $subscription->update(['plan_id' => $plan->id]);

            Log::info('Subscription plan changed', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'plan_id' => $plan->id,
            ]);

            return $subscription->fresh('plan');
        });
    }

    public function markPastDue(Subscription $subscription): void
    {
        $this->syncExpiredStatus($subscription);
    }

    public function syncExpiredStatus(Subscription $subscription): bool
    {
        if ($subscription->status === SubscriptionStatus::Trialing
            && $subscription->trial_ends_at?->isPast()) {
            $subscription->update(['status' => SubscriptionStatus::PastDue]);

            Log::info('Subscription marked past due (trial expired)', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
            ]);

            return true;
        }

        if ($subscription->status === SubscriptionStatus::Active
            && $subscription->current_period_ends_at?->isPast()) {
            $subscription->update(['status' => SubscriptionStatus::PastDue]);

            Log::info('Subscription marked past due (period expired)', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
            ]);

            return true;
        }

        return false;
    }

    public function userHasAccess(User $user): bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }

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

    public function userHasFeature(User $user, SubscriptionFeature $feature): bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }

        if (! $this->userHasAccess($user)) {
            return false;
        }

        $subscription = $user->subscription?->loadMissing('plan');

        if ($subscription?->plan === null) {
            return false;
        }

        $features = $subscription->plan->features ?? [];

        return in_array($feature->value, $features, true);
    }
}
