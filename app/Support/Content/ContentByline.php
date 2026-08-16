<?php

namespace App\Support\Content;

use App\Models\User;

class ContentByline
{
    public static function forPost(?string $postAs, ?User $author): string
    {
        if (filled($postAs)) {
            return trim($postAs);
        }

        return $author?->name ?? 'Kinsenas Team';
    }

    public static function forOptionalPostAs(?string $postAs): ?string
    {
        return filled($postAs) ? trim($postAs) : null;
    }
}
