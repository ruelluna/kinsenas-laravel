<?php

namespace App\Http\Controllers\Api\V1\Teams;

use App\Actions\Teams\CreateTeam;
use App\Enums\TeamRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\DeleteTeamRequest;
use App\Http\Requests\Teams\SaveTeamRequest;
use App\Models\Membership;
use App\Models\Team;
use App\Models\User;
use App\Services\Billing\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TeamController extends Controller
{
    public function index(Request $request, SubscriptionService $subscriptionService): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'teams' => $user->teams()
                ->with('subscription')
                ->get()
                ->map(function (Team $team) use ($user, $subscriptionService): array {
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
                ->values(),
        ]);
    }

    public function store(SaveTeamRequest $request, CreateTeam $createTeam): JsonResponse
    {
        Gate::authorize('create', Team::class);

        $team = $createTeam->handle($request->user(), $request->validated('name'));

        return response()->json([
            'message' => __('Team created.'),
            'team' => $this->teamPayload($team),
        ], 201);
    }

    public function show(Request $request, Team $team): JsonResponse
    {
        return response()->json([
            'team' => $this->teamPayload($team),
            'members' => $team->members()->get()->map(function (User $member): array {
                /** @var Membership $membership */
                $membership = $member->getRelation('pivot');

                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'avatar' => $member->avatar,
                    'role' => $membership->role->value,
                    'roleLabel' => $membership->role->label(),
                ];
            })->values(),
            'invitations' => $team->invitations()
                ->whereNull('accepted_at')
                ->get()
                ->map(fn ($invitation): array => [
                    'code' => $invitation->code,
                    'email' => $invitation->email,
                    'role' => $invitation->role->value,
                    'roleLabel' => $invitation->role->label(),
                    'createdAt' => $invitation->created_at->toISOString(),
                ])
                ->values(),
            'permissions' => $request->user()->toTeamPermissions($team),
            'availableRoles' => TeamRole::assignable(),
        ]);
    }

    public function update(SaveTeamRequest $request, Team $team): JsonResponse
    {
        Gate::authorize('update', $team);

        $team = DB::transaction(function () use ($request, $team): Team {
            $team = Team::query()->whereKey($team->id)->lockForUpdate()->firstOrFail();
            $team->update(['name' => $request->validated('name')]);

            return $team;
        });

        return response()->json([
            'message' => __('Team updated.'),
            'team' => $this->teamPayload($team),
        ]);
    }

    public function switch(Request $request, Team $team): JsonResponse
    {
        abort_unless($request->user()->belongsToTeam($team), 403);

        $request->user()->switchTeam($team);

        return response()->json([
            'message' => __('Current team switched.'),
            'team' => $this->teamPayload($team),
        ]);
    }

    public function leave(Request $request, Team $team): JsonResponse
    {
        Gate::authorize('leave', $team);

        $user = $request->user();
        $fallbackTeam = $user->isCurrentTeam($team) ? $user->fallbackTeam($team) : null;

        $team->memberships()->where('user_id', $user->id)->delete();

        if ($fallbackTeam) {
            $user->switchTeam($fallbackTeam);
        }

        return response()->json([
            'message' => __('You left the team ":name"', ['name' => $team->name]),
        ]);
    }

    public function destroy(DeleteTeamRequest $request, Team $team): JsonResponse
    {
        $user = $request->user();
        $fallbackTeam = $user->isCurrentTeam($team) ? $user->fallbackTeam($team) : null;

        DB::transaction(function () use ($user, $team): void {
            User::query()
                ->where('current_team_id', $team->id)
                ->where('id', '!=', $user->id)
                ->each(fn (User $affectedUser) => $affectedUser->switchTeam($affectedUser->personalTeam()));

            $team->invitations()->delete();
            $team->memberships()->delete();
            $team->delete();
        });

        if ($fallbackTeam) {
            $user->switchTeam($fallbackTeam);
        }

        return response()->json([
            'message' => __('Team deleted.'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function teamPayload(Team $team): array
    {
        return [
            'id' => $team->id,
            'name' => $team->name,
            'slug' => $team->slug,
            'isPersonal' => $team->is_personal,
        ];
    }
}
