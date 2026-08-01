<?php

namespace App\Enums;

enum BillingMode: string
{
    case Live = 'live';
    case OpenBeta = 'open_beta';

    public static function current(): self
    {
        return self::tryFrom(config('billing.mode')) ?? self::Live;
    }

    public static function isOpenBeta(): bool
    {
        return self::current() === self::OpenBeta;
    }

    public function label(): string
    {
        return match ($this) {
            self::Live => 'Live',
            self::OpenBeta => 'Open beta',
        };
    }
}
