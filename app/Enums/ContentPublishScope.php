<?php

namespace App\Enums;

enum ContentPublishScope: string
{
    case Internal = 'internal';
    case External = 'external';
    case Both = 'both';

    public function label(): string
    {
        return match ($this) {
            self::Internal => 'Internal only',
            self::External => 'External only',
            self::Both => 'Internal & external',
        };
    }

    public function isPublicTeaser(): bool
    {
        return $this === self::External || $this === self::Both;
    }
}
