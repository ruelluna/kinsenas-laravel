<?php

namespace App\Http\Controllers\Teams;

use App\Enums\TeamRole;
use App\Enums\UserActivityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\UpdateTeamMemberRequest;
use App\Models\Membership;
use App\Models\Team;
use App\Models\User;
use App\Services\Audit\UserActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TeamMemberController extends Controller
{
    public function __construct(private UserActivityLogger $activityLogger) {}

    /**
     * Update the specified team member's role.
     */
    public function update(UpdateTeamMemberRequest $request, Team $team, User $user): RedirectResponse
    {
        Gate::authorize('updateMember', $team);

        $newRole = TeamRole::from($request->validated('role'));

        /** @var Membership $membership */
        $membership = $team->memberships()
            ->where('user_id', $user->id)
            ->firstOrFail();

        $previousRole = $membership->role;

        $membership->update(['role' => $newRole]);

        $this->activityLogger->log(
            UserActivityAction::TeamMemberRoleUpdated,
            'Updated :properties.member_name role to :properties.role_label',
            $request->user(),
            $membership,
            [
                'member_id' => $user->id,
                'member_name' => $user->name,
                'member_email' => $user->email,
                'previous_role' => $previousRole->value,
                'previous_role_label' => $previousRole->label(),
                'role' => $newRole->value,
                'role_label' => $newRole->label(),
            ],
            $team,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Member role updated.')]);

        return to_route('teams.edit', ['team' => $team->slug]);
    }

    /**
     * Remove the specified team member.
     */
    public function destroy(Team $team, User $user): RedirectResponse
    {
        Gate::authorize('removeMember', $team);

        abort_if($team->owner()?->is($user), 403, __('The team owner cannot be removed.'));

        $this->activityLogger->log(
            UserActivityAction::TeamMemberRemoved,
            'Removed :properties.member_name from the team',
            request()->user(),
            subject: null,
            properties: [
                'member_id' => $user->id,
                'member_name' => $user->name,
                'member_email' => $user->email,
            ],
            team: $team,
        );

        $team->memberships()
            ->where('user_id', $user->id)
            ->delete();

        if ($user->isCurrentTeam($team)) {
            $user->switchTeam($user->personalTeam());
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Member removed.')]);

        return to_route('teams.edit', ['team' => $team->slug]);
    }
}
