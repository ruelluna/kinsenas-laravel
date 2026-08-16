<?php

namespace App\Http\Responses\Concerns;

use App\Enums\BillingMode;
use App\Models\Team;
use App\Services\Billing\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

trait RedirectsToCurrentTeam
{
    protected function redirectPathAfterAuth(Request $request, string $redirect): string
    {
        $user = $request->user();

        if ($user !== null && ! $user->hasVerifiedEmail()) {
            return route('verification.notice');
        }

        return $this->redirectPathForCurrentTeam($request, $redirect);
    }

    protected function redirectPathForCurrentTeam(Request $request, string $redirect): string
    {
        $user = $request->user();

        if ($user !== null
            && ! $user->isPlatformAdmin()
            && ! BillingMode::isOpenBeta()
            && $user->currentTeam !== null
            && ! app(SubscriptionService::class)->teamHasAccess($user->currentTeam)) {
            return route('settings.billing');
        }

        $team = $this->currentTeam($request);

        URL::defaults(['current_team' => $team->slug]);

        return "/{$team->slug}{$redirect}";
    }

    protected function currentTeam(Request $request): Team
    {
        $user = $request->user();

        abort_if(! $user, 403);

        $team = $user->currentTeam ?? $user->personalTeam();

        abort_if(! $team, 403);

        return $team;
    }
}
