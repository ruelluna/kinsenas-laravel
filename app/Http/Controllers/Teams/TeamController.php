<?php

namespace App\Http\Controllers\Teams;

use App\Actions\Teams\CreateTeam;
use App\Enums\TeamPermission;
use App\Enums\TeamRole;
use App\Enums\UserActivityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\DeleteTeamRequest;
use App\Http\Requests\Teams\SaveTeamRequest;
use App\Models\Membership;
use App\Models\Team;
use App\Models\User;
use App\Services\Audit\UserActivityLogger;
use App\Services\Billing\SubscriptionService;
use App\Services\Teams\TeamSetupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class TeamController extends Controller
{
    public function __construct(
        private TeamSetupService $teamSetupService,
        private UserActivityLogger $activityLogger,
    ) {}

    /**
     * Display a listing of the user's teams.
     */
    public function index(Request $request, SubscriptionService $subscriptionService): Response
    {
        $user = $request->user();

        $teams = $user->teams()
            ->with('subscription')
            ->get()
            ->map(function (Team $team) use ($user, $subscriptionService) {
                $userTeam = $user->toUserTeam($team);

                return [
                    'id' => $userTeam->id,
                    'name' => $userTeam->name,
                    'slug' => $userTeam->slug,
                    'isPersonal' => $userTeam->isPersonal,
                    'role' => $userTeam->role,
                    'roleLabel' => $userTeam->roleLabel,
                    'isCurrent' => $userTeam->isCurrent,
                    'subscriptionStatusLabel' => $team->subscription?->status?->label(),
                    'hasSubscriptionAccess' => $subscriptionService->teamHasAccess($team),
                ];
            })
            ->values();

        return Inertia::render('teams/index', [
            'teams' => $teams,
        ]);
    }

    /**
     * Store a newly created team.
     */
    public function store(SaveTeamRequest $request, CreateTeam $createTeam, SubscriptionService $subscriptionService): RedirectResponse
    {
        Gate::authorize('create', Team::class);

        $team = $createTeam->handle($request->user(), $request->validated('name'));

        $this->activityLogger->log(
            UserActivityAction::TeamCreated,
            'Created team :properties.team_name',
            $request->user(),
            $team,
            ['team_name' => $team->name],
            $team,
        );

        if (! $subscriptionService->teamHasAccess($team)) {
            Inertia::flash('toast', [
                'type' => 'info',
                'message' => __('Subscribe to activate this team workspace.'),
            ]);

            return to_route('settings.billing');
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Team created.')]);

        return to_route('teams.edit', ['team' => $team->slug]);
    }

    /**
     * Show the team edit page.
     */
    public function edit(Request $request, Team $team): Response
    {
        $user = $request->user();
        $canInviteByRole = $user->hasTeamPermission($team, TeamPermission::CreateInvitation);
        $canViewActivity = $canInviteByRole || $user->hasTeamPermission($team, TeamPermission::UpdateTeam);

        return Inertia::render('teams/edit', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
                'isPersonal' => $team->is_personal,
            ],
            'members' => $team->members()->get()->map(function (User $member) {
                /** @var Membership $membership */
                $membership = $member->getRelation('pivot');

                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'avatar' => $member->profilePhotoUrl(),
                    'role' => $membership->role->value,
                    'role_label' => $membership->role->label(),
                ];
            }),
            'invitations' => $team->invitations()
                ->whereNull('accepted_at')
                ->get()
                ->map(fn ($invitation) => [
                    'code' => $invitation->code,
                    'email' => $invitation->email,
                    'role' => $invitation->role->value,
                    'role_label' => $invitation->role->label(),
                    'created_at' => $invitation->created_at->toISOString(),
                ]),
            'permissions' => $user->toTeamPermissions($team),
            'canInviteByRole' => $canInviteByRole,
            'inviteReadiness' => $this->teamSetupService->readinessForInvites($team, $user)->toArray(),
            'teamActivity' => $canViewActivity
                ? $this->recentTeamActivity($team)
                : [],
            'availableRoles' => TeamRole::assignable(),
        ]);
    }

    /**
     * @return list<array{id: int, description: string, event: string|null, causer_name: string|null, created_at: string}>
     */
    private function recentTeamActivity(Team $team): array
    {
        return Activity::query()
            ->where('log_name', 'kinsenas')
            ->where('properties->team_id', $team->id)
            ->with('causer')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($activity) => [
                'id' => $activity->id,
                'description' => $activity->description,
                'event' => $activity->event,
                'causer_name' => $activity->causer?->name,
                'created_at' => $activity->created_at?->toISOString() ?? now()->toISOString(),
            ])
            ->all();
    }

    /**
     * Update the specified team.
     */
    public function update(SaveTeamRequest $request, Team $team): RedirectResponse
    {
        Gate::authorize('update', $team);

        $previousName = $team->name;

        $team = DB::transaction(function () use ($request, $team) {
            $team = Team::whereKey($team->id)->lockForUpdate()->firstOrFail();

            $team->update(['name' => $request->validated('name')]);

            return $team;
        });

        $this->activityLogger->log(
            UserActivityAction::TeamUpdated,
            'Updated team name to :properties.team_name',
            $request->user(),
            $team,
            [
                'previous_name' => $previousName,
                'team_name' => $team->name,
            ],
            $team,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Team updated.')]);

        return to_route('teams.edit', ['team' => $team->slug]);
    }

    /**
     * Switch the user's current team.
     */
    public function switch(Request $request, Team $team): RedirectResponse
    {
        abort_unless($request->user()->belongsToTeam($team), 403);

        $request->user()->switchTeam($team);

        $this->activityLogger->log(
            UserActivityAction::TeamSwitched,
            'Switched to team :properties.team_name',
            $request->user(),
            $team,
            ['team_name' => $team->name],
            $team,
        );

        return back();
    }

    /**
     * Leave the specified team.
     */
    public function leave(Request $request, Team $team): RedirectResponse
    {
        Gate::authorize('leave', $team);

        $user = $request->user();

        $fallbackTeam = $user->isCurrentTeam($team)
            ? $user->fallbackTeam($team)
            : null;

        $this->activityLogger->log(
            UserActivityAction::TeamLeft,
            'Left team :properties.team_name',
            $user,
            $team,
            ['team_name' => $team->name],
            $team,
        );

        $team->memberships()
            ->where('user_id', $user->id)
            ->delete();

        if ($fallbackTeam) {
            $user->switchTeam($fallbackTeam);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('You left the team ":name"', ['name' => $team->name])]);

        return to_route('teams.index');
    }

    /**
     * Delete the specified team.
     */
    public function destroy(DeleteTeamRequest $request, Team $team): RedirectResponse
    {
        $user = $request->user();
        $fallbackTeam = $user->isCurrentTeam($team)
            ? $user->fallbackTeam($team)
            : null;

        DB::transaction(function () use ($user, $team) {
            User::where('current_team_id', $team->id)
                ->where('id', '!=', $user->id)
                ->each(fn (User $affectedUser) => $affectedUser->switchTeam($affectedUser->personalTeam()));

            $team->invitations()->delete();
            $team->memberships()->delete();
            $team->delete();
        });

        $this->activityLogger->log(
            UserActivityAction::TeamDeleted,
            'Deleted team :properties.team_name',
            $user,
            properties: ['team_name' => $team->name],
            team: $team,
        );

        if ($fallbackTeam) {
            $user->switchTeam($fallbackTeam);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Team deleted.')]);

        return to_route('teams.index');
    }
}
