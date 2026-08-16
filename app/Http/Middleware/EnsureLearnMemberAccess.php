<?php

namespace App\Http\Middleware;

use App\Services\Content\LearnAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLearnMemberAccess
{
    public function __construct(private LearnAccessService $learnAccessService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        if (! $this->learnAccessService->userHasFullLearnAccess($user)) {
            abort(403, __('Subscribe to access this feature.'));
        }

        return $next($request);
    }
}
