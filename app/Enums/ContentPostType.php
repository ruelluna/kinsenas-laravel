<?php

namespace App\Enums;

enum ContentPostType: string
{
    case Article = 'article';
    case Reminder = 'reminder';
    case Share = 'share';
    case Episode = 'episode';

    public function label(): string
    {
        return match ($this) {
            self::Article => 'Article',
            self::Reminder => 'Reminder',
            self::Share => 'Share',
            self::Episode => 'Episode',
        };
    }
}
