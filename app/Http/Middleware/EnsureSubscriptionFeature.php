<?php

namespace App\Http\Middleware;

use App\Enums\SubscriptionFeature;
use App\Services\Billing\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionFeature
{
    public function __construct(private SubscriptionService $subscriptionService)
    {
    }

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        $subscriptionFeature = SubscriptionFeature::tryFrom($feature);

        if ($subscriptionFeature === null) {
            abort(500, "Unknown subscription feature: {$feature}");
        }

        if ($this->subscriptionService->userHasFeature($user, $subscriptionFeature)) {
            return $next($request);
        }

        return redirect()
            ->route('settings.billing')
            ->with('error', __('Your plan does not include this feature. Please upgrade to continue.'));
    }
}
