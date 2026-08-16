<?php

namespace App\Support\Content;

use App\Models\User;
use App\Support\UserProfilePhoto;

class ContentByline
{
    public static function forPost(?string $postAs, ?User $author): string
    {
        if (filled($postAs)) {
            return trim($postAs);
        }

        return $author?->name ?? 'Kinsenas Team';
    }

    public static function authorAvatarUrl(?User $author): ?string
    {
        return UserProfilePhoto::url($author);
    }

    public static function forOptionalPostAs(?string $postAs): ?string
    {
        return filled($postAs) ? trim($postAs) : null;
    }
}
