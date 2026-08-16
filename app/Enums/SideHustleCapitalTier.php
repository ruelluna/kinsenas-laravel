<?php

namespace App\Enums;

enum SideHustleCapitalTier: string
{
    case Low = 'low';
    case Moderate = 'moderate';
    case High = 'high';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low capital',
            self::Moderate => 'Moderate capital',
            self::High => 'High capital',
        };
    }
}
