<?php

namespace App\Actions\Teams;

use App\Enums\BillingMode;
use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use App\Services\Billing\SubscriptionService;
use App\Services\Marketing\GhlUserTagService;
use App\Services\Teams\PersonalTeamNaming;
use App\Support\Marketing\GhlTagCatalog;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class CreateTeam
{
    public function __construct(
        private PersonalTeamNaming $personalTeamNaming,
        private SubscriptionService $subscriptionService,
        private GhlUserTagService $ghlUserTagService,
    ) {}

    /**
     * Create a new team and add the user as owner.
     */
    public function handle(User $user, ?string $name = null, bool $isPersonal = false): Team
    {
        if (! $isPersonal && ! config('teams.allow_additional_owned_teams') && $user->ownedTeams()->exists()) {
            throw new AuthorizationException(__('Additional teams are not available yet. You can invite members to your existing team instead.'));
        }

        return DB::transaction(function () use ($user, $name, $isPersonal) {
            $attributes = ['is_personal' => $isPersonal];

            if ($isPersonal) {
                $attributes['name'] = $this->personalTeamNaming->nameFor($user);
                $attributes['slug'] = $this->personalTeamNaming->slugFor($user);
            } else {
                $attributes['name'] = $name;
            }

            $team = Team::create($attributes);

            $team->memberships()->create([
                'user_id' => $user->id,
                'role' => TeamRole::Owner,
            ]);

            $user->switchTeam($team);

            if (BillingMode::isOpenBeta()) {
                $this->subscriptionService->startOpenBeta($team);
            } elseif ($isPersonal) {
                $this->subscriptionService->startTrial($team);
            } else {
                $this->subscriptionService->requirePaidSubscription($team);
            }

            if (! $isPersonal) {
                $this->ghlUserTagService->dispatch(
                    $user,
                    [GhlTagCatalog::TEAM_CREATED],
                    [],
                    ['event' => 'team_created', 'team_id' => $team->id],
                );
            }

            return $team;
        });
    }
}
