<?php

namespace App\Support;

use App\Models\User;

class UserProfilePhoto
{
    public static function url(?User $user): ?string
    {
        if ($user === null || blank($user->profile_photo_path)) {
            return null;
        }

        return asset('storage/'.$user->profile_photo_path);
    }
}
