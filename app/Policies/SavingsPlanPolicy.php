<?php

namespace App\Policies;

use App\Models\SavingsPlan;
use App\Models\Team;
use App\Models\User;

class SavingsPlanPolicy
{
    public function view(User $user, SavingsPlan $plan, Team $team): bool
    {
        if ($plan->team_id !== $team->id) {
            return false;
        }

        if ($plan->created_by_user_id === $user->id) {
            return true;
        }

        return $plan->is_shared_with_team && $user->belongsToTeam($team);
    }

    public function update(User $user, SavingsPlan $plan, Team $team): bool
    {
        if ($plan->team_id !== $team->id) {
            return false;
        }

        if ($plan->created_by_user_id === $user->id) {
            return true;
        }

        return $plan->is_shared_with_team && $user->hasTeamPermission($team, \App\Enums\TeamPermission::TeamUpdate);
    }
}
