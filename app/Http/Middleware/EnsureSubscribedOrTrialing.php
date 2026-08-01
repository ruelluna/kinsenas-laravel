<?php

namespace App\Http\Middleware;

use App\Models\Team;
use App\Models\User;
use App\Services\Billing\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscribedOrTrialing
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        if ($user->isPlatformAdmin()) {
            return $next($request);
        }

        if ($request->routeIs(
            'settings.billing*',
            'billing.*',
            'logout',
            'profile.*',
            'security.*',
            'appearance.*',
            'teams.index',
            'teams.switch',
        )) {
            return $next($request);
        }

        $team = $this->resolveTeam($request, $user);

        if ($team === null) {
            return $next($request);
        }

        if ($this->subscriptionService->teamHasAccess($team)) {
            return $next($request);
        }

        return redirect()
            ->route('settings.billing')
            ->with('error', __(':team requires an active subscription. Please subscribe to continue.', [
                'team' => $team->name,
            ]));
    }

    private function resolveTeam(Request $request, User $user): ?Team
    {
        $routeTeam = $request->route('current_team') ?? $request->route('team');

        if (is_string($routeTeam)) {
            return Team::query()->where('slug', $routeTeam)->first();
        }

        if ($routeTeam instanceof Team) {
            return $routeTeam;
        }

        return $user->currentTeam;
    }
}
