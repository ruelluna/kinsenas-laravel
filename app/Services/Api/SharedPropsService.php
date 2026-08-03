<?php

namespace App\Services\Api;

use App\Data\UserTeam;
use App\Enums\SubscriptionStatus;
use App\Models\Team;
use App\Models\User;
use App\Services\Billing\BetaApplicationService;
use App\Services\Billing\SubscriptionService;
use App\Services\Vault\VaultKeyManager;
use Illuminate\Support\Facades\Gate;

class SharedPropsService
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private BetaApplicationService $betaApplicationService,
        private VaultKeyManager $vaultKeyManager,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        return [
            'user' => [
                ...$user->toArray(),
                'isPlatformAdmin' => $user->isPlatformAdmin(),
                'two_factor_enabled' => $user->two_factor_secret !== null,
            ],
            'currentTeam' => $user->currentTeam
                ? $this->mapTeam($user->toUserTeam($user->currentTeam))
                : null,
            'teams' => $user->toUserTeams(includeCurrent: true)
                ->map(fn (UserTeam $team) => $this->mapTeam($team))
                ->values()
                ->all(),
            'canCreateTeam' => Gate::allows('create', Team::class),
            'vaultLocked' => $user->vault !== null && ! $this->vaultKeyManager->hasUserDek(),
            'subscription' => $this->subscription($user),
            'billingMode' => config('billing.mode'),
            'openBeta' => $this->betaApplicationService->sharedProps($user),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function subscription(User $user): ?array
    {
        $team = $user->currentTeam?->loadMissing('subscription.plan');

        if ($team === null) {
            return null;
        }

        $subscription = $team->subscription;
        $daysRemaining = null;

        if ($subscription !== null
            && $subscription->status === SubscriptionStatus::Trialing
            && $subscription->trial_ends_at !== null) {
            $daysRemaining = max(0, (int) now()->diffInDays($subscription->trial_ends_at, false));
        }

        return [
            'teamId' => $team->id,
            'teamName' => $team->name,
            'teamSlug' => $team->slug,
            'status' => $subscription?->status->value,
            'statusLabel' => $subscription?->status?->label(),
            'planName' => $subscription?->plan?->name,
            'trialDaysRemaining' => $daysRemaining,
            'hasAccess' => $this->subscriptionService->teamHasAccess($team),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapTeam(UserTeam $team): array
    {
        return [
            'id' => $team->id,
            'name' => $team->name,
            'slug' => $team->slug,
            'isPersonal' => $team->isPersonal,
            'role' => $team->role,
            'roleLabel' => $team->roleLabel,
            'isCurrent' => $team->isCurrent,
        ];
    }
}
