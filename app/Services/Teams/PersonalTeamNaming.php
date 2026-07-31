<?php

namespace App\Services\Teams;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Str;

class PersonalTeamNaming
{
    public function nameFor(User $user): string
    {
        return Str::limit("{$user->name}'s finances", 255, '');
    }

    public function slugBaseFor(User $user): string
    {
        $base = Str::slug($user->name);

        if ($base === '') {
            $base = Str::slug(Str::before($user->email, '@'));
        }

        return $base !== '' ? $base : 'workspace';
    }

    public function slugFor(User $user, ?int $excludeTeamId = null): string
    {
        return Team::uniqueSlugFor($this->slugBaseFor($user), $excludeTeamId);
    }
}
