<?php

namespace App\Support\Notifications;

use App\Models\Team;

class NotificationActionUrl
{
    public const LAUNCH = '/launch';

    public static function teamDashboard(?Team $team): string
    {
        return $team !== null ? "/{$team->slug}/dashboard" : self::LAUNCH;
    }
}
