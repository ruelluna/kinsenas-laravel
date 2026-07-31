<?php

namespace App\Http\Middleware;

use App\Services\Billing\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscribedOrTrialing
{
    public function __construct(private SubscriptionService $subscriptionService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        if ($this->subscriptionService->userHasAccess($user)) {
            return $next($request);
        }

        if ($request->routeIs('settings.billing*', 'billing.*')) {
            return $next($request);
        }

        return redirect()
            ->route('settings.billing')
            ->with('error', __('Your trial has ended. Please subscribe to continue.'));
    }
}
