<?php

namespace App\Http\Controllers\Api\V1\Teams;

use App\Enums\TeamRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\UpdateTeamMemberRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class TeamMemberController extends Controller
{
    public function update(UpdateTeamMemberRequest $request, Team $team, User $user): JsonResponse
    {
        Gate::authorize('updateMember', $team);

        $newRole = TeamRole::from($request->validated('role'));

        $team->memberships()
            ->where('user_id', $user->id)
            ->firstOrFail()
            ->update(['role' => $newRole]);

        return response()->json([
            'message' => __('Member role updated.'),
        ]);
    }

    public function destroy(Team $team, User $user): JsonResponse
    {
        Gate::authorize('removeMember', $team);
        abort_if($team->owner()?->is($user), 403, __('The team owner cannot be removed.'));

        $team->memberships()->where('user_id', $user->id)->delete();

        if ($user->isCurrentTeam($team)) {
            $user->switchTeam($user->personalTeam());
        }

        return response()->json([
            'message' => __('Member removed.'),
        ]);
    }
}
