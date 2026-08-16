<?php

namespace App\Http\Controllers\Teams;

use App\Enums\TeamRole;
use App\Enums\UserActivityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\CreateTeamInvitationRequest;
use App\Http\Requests\Teams\RespondToTeamInvitationRequest;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\Teams\TeamInvitation as TeamInvitationNotification;
use App\Notifications\Teams\TeamInvitationAccepted;
use App\Services\Audit\UserActivityLogger;
use App\Services\Marketing\GhlUserTagService;
use App\Support\Marketing\GhlTagCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;

class TeamInvitationController extends Controller
{
    public function __construct(
        private GhlUserTagService $ghlUserTagService,
        private UserActivityLogger $activityLogger,
    ) {}

    /**
     * Store a newly created invitation.
     */
    public function store(CreateTeamInvitationRequest $request, Team $team): RedirectResponse
    {
        Gate::authorize('inviteMember', $team);

        $invitation = $team->invitations()->create([
            'email' => $request->validated('email'),
            'role' => TeamRole::from($request->validated('role')),
            'invited_by' => $request->user()->id,
            'expires_at' => now()->addDays(3),
        ]);

        Notification::route('mail', $invitation->email)
            ->notify(new TeamInvitationNotification($invitation));

        User::query()
            ->where('email', $invitation->email)
            ->first()
            ?->notify(new TeamInvitationNotification($invitation));

        $this->ghlUserTagService->dispatch(
            $request->user(),
            [GhlTagCatalog::TEAM_INVITE_SENT],
            [],
            ['event' => 'team_invite_sent', 'team_id' => $team->id],
        );

        $this->activityLogger->log(
            UserActivityAction::TeamInvitationSent,
            'Sent team invitation to :properties.email',
            $request->user(),
            $invitation,
            [
                'email' => $invitation->email,
                'role' => $invitation->role->value,
                'role_label' => $invitation->role->label(),
                'invitation_code' => $invitation->code,
            ],
            $team,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Invitation sent.')]);

        return to_route('teams.edit', ['team' => $team->slug]);
    }

    /**
     * Cancel the specified invitation.
     */
    public function destroy(Team $team, TeamInvitation $invitation): RedirectResponse
    {
        abort_unless($invitation->team_id === $team->id, 404);

        Gate::authorize('cancelInvitation', $team);

        $this->activityLogger->log(
            UserActivityAction::TeamInvitationCancelled,
            'Cancelled team invitation to :properties.email',
            request()->user(),
            $invitation,
            [
                'email' => $invitation->email,
                'role' => $invitation->role->value,
                'role_label' => $invitation->role->label(),
                'invitation_code' => $invitation->code,
            ],
            $team,
        );

        $invitation->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Invitation cancelled.')]);

        return to_route('teams.edit', ['team' => $team->slug]);
    }

    /**
     * Accept the invitation.
     */
    public function accept(RespondToTeamInvitationRequest $request, TeamInvitation $invitation): RedirectResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($user, $invitation) {
            $team = $invitation->team;

            $team->memberships()->firstOrCreate(
                ['user_id' => $user->id],
                ['role' => $invitation->role],
            );

            $invitation->update(['accepted_at' => now()]);

            $user->switchTeam($team);
        });

        $invitation->loadMissing('inviter');

        $this->activityLogger->log(
            UserActivityAction::TeamInvitationAccepted,
            'Accepted team invitation for :properties.team_name',
            $user,
            $invitation,
            [
                'email' => $invitation->email,
                'role' => $invitation->role->value,
                'role_label' => $invitation->role->label(),
                'team_name' => $invitation->team->name,
            ],
            $invitation->team,
        );

        if ($invitation->inviter !== null && ! $invitation->inviter->is($user)) {
            $invitation->inviter->notify(new TeamInvitationAccepted($invitation, $user));
        }

        $this->ghlUserTagService->dispatch(
            $user,
            [GhlTagCatalog::TEAM_MEMBER],
            [],
            ['event' => 'team_invite_accepted', 'team_id' => $invitation->team_id],
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Invitation accepted.')]);

        return to_route('dashboard');
    }

    /**
     * Decline the invitation.
     */
    public function decline(RespondToTeamInvitationRequest $request, TeamInvitation $invitation): RedirectResponse
    {
        $user = $request->user();

        $this->activityLogger->log(
            UserActivityAction::TeamInvitationDeclined,
            'Declined team invitation for :properties.team_name',
            $user,
            $invitation,
            [
                'email' => $invitation->email,
                'role' => $invitation->role->value,
                'role_label' => $invitation->role->label(),
                'team_name' => $invitation->team->name,
            ],
            $invitation->team,
        );

        $invitation->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Invitation declined.')]);

        return to_route('dashboard');
    }
}
