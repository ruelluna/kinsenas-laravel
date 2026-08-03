<?php

namespace App\Http\Middleware;

use App\Models\Team;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiTeamScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        $team = $request->route('team');

        if ($team instanceof Team) {
            abort_unless($user->belongsToTeam($team), 403);
            $request->attributes->set('current_team', $team);

            return $next($request);
        }

        $teamId = $team ?? $request->header('X-Team-Id') ?? $user->current_team_id;

        if ($teamId === null) {
            return response()->json(['message' => __('Team context is required.')], 422);
        }

        $resolvedTeam = Team::query()->find($teamId);

        if ($resolvedTeam === null || ! $user->belongsToTeam($resolvedTeam)) {
            abort(403);
        }

        $request->attributes->set('current_team', $resolvedTeam);
        $request->route()?->setParameter('team', $resolvedTeam);

        return $next($request);
    }
}
