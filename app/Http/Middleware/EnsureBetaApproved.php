<?php

namespace App\Http\Middleware;

use App\Enums\BetaApplicationStatus;
use App\Services\Billing\BetaApplicationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBetaApproved
{
    public function __construct(private BetaApplicationService $betaApplicationService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        if ($this->betaApplicationService->hasAppAccess($user)) {
            return $next($request);
        }

        if ($request->routeIs('beta.pending', 'beta.rejected', 'verification.*', 'logout')) {
            return $next($request);
        }

        if ($user->beta_application_status === BetaApplicationStatus::Rejected) {
            return redirect()->route('beta.rejected');
        }

        return redirect()->route('beta.pending');
    }
}
