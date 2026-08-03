<?php

namespace App\Services\Billing;

use App\Enums\BillingInterval;
use App\Enums\BillingMode;
use App\Enums\SubscriptionFeature;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Team;
use App\Models\User;
use App\Services\Marketing\GhlUserTagService;
use App\Support\Marketing\GhlTagCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    public function __construct(private GhlUserTagService $ghlUserTagService) {}

    public function startTrial(Team $team, ?SubscriptionPlan $plan = null): Subscription
    {
        $plan ??= $this->defaultPlan();

        return DB::transaction(function () use ($team, $plan) {
            $subscription = Subscription::query()->updateOrCreate(
                ['team_id' => $team->id],
                [
                    'plan_id' => $plan->id,
                    'status' => SubscriptionStatus::Trialing,
                    'trial_ends_at' => now()->addDays($plan->trial_days),
                    'current_period_ends_at' => null,
                ],
            );

            $this->syncTrialStartedTag($team);

            return $subscription;
        });
    }

    public function startOpenBeta(Team $team, ?SubscriptionPlan $plan = null): Subscription
    {
        $plan ??= $this->defaultPlan();

        return DB::transaction(function () use ($team, $plan) {
            return Subscription::query()->updateOrCreate(
                ['team_id' => $team->id],
                [
                    'plan_id' => $plan->id,
                    'status' => SubscriptionStatus::OpenBeta,
                    'trial_ends_at' => null,
                    'current_period_ends_at' => null,
                ],
            );
        });
    }

    public function requirePaidSubscription(Team $team, ?SubscriptionPlan $plan = null): Subscription
    {
        $plan ??= $this->defaultPlan();

        return DB::transaction(function () use ($team, $plan) {
            return Subscription::query()->updateOrCreate(
                ['team_id' => $team->id],
                [
                    'plan_id' => $plan->id,
                    'status' => SubscriptionStatus::PastDue,
                    'trial_ends_at' => null,
                    'current_period_ends_at' => null,
                ],
            );
        });
    }

    public function activate(Team $team, BillingInterval $interval): Subscription
    {
        $subscription = $team->subscription()->with('plan.prices')->firstOrFail();
        $months = $interval === BillingInterval::Yearly ? 12 : 1;

        $subscription->update([
            'status' => SubscriptionStatus::Active,
            'trial_ends_at' => null,
            'current_period_ends_at' => now()->addMonths($months),
        ]);

        return $subscription->fresh('plan');
    }

    public function activateManually(Team $team, BillingInterval $interval, ?SubscriptionPlan $plan = null): Subscription
    {
        return DB::transaction(function () use ($team, $interval, $plan) {
            $subscription = $team->subscription()->firstOrFail();

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
                'team_id' => $team->id,
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
                'team_id' => $subscription->team_id,
                'days' => $days,
                'trial_ends_at' => $subscription->trial_ends_at?->toIso8601String(),
            ]);

            return $subscription->fresh('plan');
        });
    }

    public function cancel(Subscription $subscription, ?string $reason = null, bool $syncMarketingTags = true): Subscription
    {
        return DB::transaction(function () use ($subscription, $reason, $syncMarketingTags) {
            $subscription->update([
                'status' => SubscriptionStatus::Cancelled,
                'current_period_ends_at' => null,
            ]);

            Log::info('Subscription cancelled', [
                'subscription_id' => $subscription->id,
                'team_id' => $subscription->team_id,
                'reason' => $reason,
            ]);

            if ($syncMarketingTags) {
                $this->syncSubscriptionCancelledTag($subscription->team);
            }

            return $subscription->fresh('plan');
        });
    }

    public function changePlan(Subscription $subscription, SubscriptionPlan $plan): Subscription
    {
        return DB::transaction(function () use ($subscription, $plan) {
            $subscription->update(['plan_id' => $plan->id]);

            Log::info('Subscription plan changed', [
                'subscription_id' => $subscription->id,
                'team_id' => $subscription->team_id,
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
                'team_id' => $subscription->team_id,
            ]);

            return true;
        }

        if ($subscription->status === SubscriptionStatus::Active
            && $subscription->current_period_ends_at?->isPast()) {
            $subscription->update(['status' => SubscriptionStatus::PastDue]);

            Log::info('Subscription marked past due (period expired)', [
                'subscription_id' => $subscription->id,
                'team_id' => $subscription->team_id,
            ]);

            return true;
        }

        return false;
    }

    public function teamHasAccess(Team $team): bool
    {
        if (BillingMode::isOpenBeta()) {
            return true;
        }

        $subscription = $team->subscription;

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

    public function userHasAccess(User $user, ?Team $team = null): bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }

        $team ??= $user->currentTeam;

        if ($team === null) {
            return false;
        }

        return $this->teamHasAccess($team);
    }

    public function userCanManageBilling(User $user, Team $team): bool
    {
        return $user->canManageBilling($team);
    }

    public function userHasFeature(User $user, SubscriptionFeature $feature, ?Team $team = null): bool
    {
        if ($user->isPlatformAdmin() || BillingMode::isOpenBeta()) {
            return true;
        }

        $team ??= $user->currentTeam;

        if ($team === null || ! $this->teamHasAccess($team)) {
            return false;
        }

        $subscription = $team->subscription?->loadMissing('plan');

        if ($subscription?->plan === null) {
            return false;
        }

        $features = $subscription->plan->features ?? [];

        return in_array($feature->value, $features, true);
    }

    private function defaultPlan(): SubscriptionPlan
    {
        $plan = SubscriptionPlan::query()->firstOrCreate(
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
            return SubscriptionPlan::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->firstOrFail();
        }

        return $plan;
    }

    private function syncTrialStartedTag(Team $team): void
    {
        $owner = $team->owner();

        if (! $owner instanceof User) {
            return;
        }

        $this->ghlUserTagService->dispatch(
            $owner,
            [GhlTagCatalog::TRIAL_ACTIVE],
            [],
            ['event' => 'trial_started', 'team_id' => $team->id],
        );
    }

    private function syncSubscriptionCancelledTag(Team $team): void
    {
        $owner = $team->owner();

        if (! $owner instanceof User) {
            return;
        }

        $this->ghlUserTagService->dispatch(
            $owner,
            [GhlTagCatalog::SUBSCRIPTION_CANCELLED],
            [GhlTagCatalog::SUBSCRIPTION_ACTIVE, GhlTagCatalog::TRIAL_ACTIVE],
            ['event' => 'subscription_cancelled', 'team_id' => $team->id],
        );
    }
}
