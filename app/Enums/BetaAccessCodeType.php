<?php

namespace App\Enums;

enum BetaAccessCodeType: string
{
    case EventShared = 'event_shared';
    case SingleUse = 'single_use';

    public function label(): string
    {
        return match ($this) {
            self::EventShared => 'Event code',
            self::SingleUse => 'Single-use code',
        };
    }
}
