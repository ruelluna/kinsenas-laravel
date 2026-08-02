<?php

namespace App\Http\Middleware;

use App\Models\Team;
use App\Services\Savings\SavingsPlanService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSavingsPlan
{
    public function __construct(private SavingsPlanService $planService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        $team = $request->route('current_team');

        if (! $team instanceof Team) {
            abort(500, 'EnsureSavingsPlan requires a current_team route parameter.');
        }

        $plan = $this->planService->forTeam($team, $user);

        if ($plan === null) {
            return redirect()
                ->route('savings.plan.show', ['current_team' => $team->slug])
                ->with('error', __('Choose a savings plan before continuing.'));
        }

        return $next($request);
    }
}
