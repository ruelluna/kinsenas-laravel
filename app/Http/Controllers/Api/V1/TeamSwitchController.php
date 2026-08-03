<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Services\Api\SharedPropsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamSwitchController extends Controller
{
    public function __invoke(Request $request, SharedPropsService $sharedProps): JsonResponse
    {
        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'exists:teams,id'],
        ]);

        $team = Team::query()->findOrFail($validated['team_id']);
        $user = $request->user();

        abort_unless($user->belongsToTeam($team), 403);

        $user->forceFill(['current_team_id' => $team->id])->save();
        $user->refresh();

        return response()->json($sharedProps->forUser($user));
    }
}
