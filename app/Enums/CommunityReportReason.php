<?php

namespace App\Enums;

enum CommunityReportReason: string
{
    case Spam = 'spam';
    case Harassment = 'harassment';
    case Misinformation = 'misinformation';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Spam => 'Spam',
            self::Harassment => 'Harassment or abuse',
            self::Misinformation => 'Misinformation',
            self::Other => 'Other',
        };
    }
}
