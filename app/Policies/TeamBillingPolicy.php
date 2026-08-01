<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamBillingPolicy
{
    public function manageBilling(User $user, Team $team): bool
    {
        if (! $user->belongsToTeam($team)) {
            return false;
        }

        return $user->canManageBilling($team);
    }
}
