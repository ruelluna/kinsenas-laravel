<?php

namespace App\Http\Middleware;

use App\Enums\SubscriptionStatus;
use App\Models\Team;
use App\Models\User;
use App\Services\Billing\BetaApplicationService;
use App\Services\Billing\SubscriptionService;
use App\Services\Vault\VaultKeyManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $subscriptionService = app(SubscriptionService::class);
        $betaApplicationService = app(BetaApplicationService::class);

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'billingMode' => config('billing.mode'),
            'openBeta' => fn () => $betaApplicationService->sharedProps($user),
            'auth' => [
                'user' => $user ? [
                    ...$user->toArray(),
                    'isPlatformAdmin' => $user->isPlatformAdmin(),
                ] : null,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'currentTeam' => fn () => $user?->currentTeam ? $user->toUserTeam($user->currentTeam) : null,
            'teams' => fn () => $user?->toUserTeams(includeCurrent: true) ?? [],
            'canCreateTeam' => fn () => $user ? Gate::allows('create', Team::class) : false,
            'vaultLocked' => fn () => $user !== null && $user->vault !== null && ! app(VaultKeyManager::class)->hasUserDek(),
            'subscription' => fn () => $this->sharedSubscription($user, $subscriptionService),
            'registrationRecoveryKey' => fn () => session('registration.recovery_key'),
            'flash' => fn () => [
                'error' => $request->session()->get('error'),
            ],
            'notifications' => fn () => $user ? [
                'unreadCount' => $user->unreadNotifications()->count(),
            ] : null,
            'webPush' => fn () => [
                'vapidPublicKey' => config('webpush.vapid.public_key'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function sharedSubscription(?User $user, SubscriptionService $subscriptionService): ?array
    {
        if ($user === null) {
            return null;
        }

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
            'isPersonalTeam' => $team->is_personal,
            'canManageBilling' => $user->canManageBilling($team),
            'status' => $subscription?->status->value,
            'statusLabel' => $subscription?->status?->label(),
            'trialEndsAt' => $subscription?->trial_ends_at?->toISOString(),
            'hasAccess' => $subscriptionService->teamHasAccess($team),
            'daysRemaining' => $daysRemaining,
        ];
    }
}
