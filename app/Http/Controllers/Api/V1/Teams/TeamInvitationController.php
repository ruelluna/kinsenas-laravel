<?php

namespace App\Http\Controllers\Api\V1\Teams;

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
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;

class TeamInvitationController extends Controller
{
    public function __construct(
        private GhlUserTagService $ghlUserTagService,
        private UserActivityLogger $activityLogger,
    ) {}

    public function store(CreateTeamInvitationRequest $request, Team $team): JsonResponse
    {
        Gate::authorize('inviteMember', $team);

        $invitation = $team->invitations()->create([
            'email' => $request->validated('email'),
            'role' => TeamRole::from($request->validated('role')),
            'invited_by' => $request->user()->id,
            'expires_at' => now()->addDays(3),
        ]);

        Notification::route('mail', $invitation->email)->notify(new TeamInvitationNotification($invitation));

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

        return response()->json([
            'message' => __('Invitation sent.'),
            'invitation' => [
                'code' => $invitation->code,
                'email' => $invitation->email,
                'role' => $invitation->role->value,
                'expiresAt' => $invitation->expires_at?->toISOString(),
            ],
        ], 201);
    }

    public function destroy(Team $team, TeamInvitation $invitation): JsonResponse
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

        return response()->json([
            'message' => __('Invitation cancelled.'),
        ]);
    }

    public function accept(RespondToTeamInvitationRequest $request, TeamInvitation $invitation): JsonResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($user, $invitation): void {
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

        return response()->json([
            'message' => __('Invitation accepted.'),
            'team' => [
                'id' => $invitation->team->id,
                'name' => $invitation->team->name,
                'slug' => $invitation->team->slug,
            ],
        ]);
    }

    public function decline(RespondToTeamInvitationRequest $request, TeamInvitation $invitation): JsonResponse
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

        return response()->json([
            'message' => __('Invitation declined.'),
        ]);
    }
}
