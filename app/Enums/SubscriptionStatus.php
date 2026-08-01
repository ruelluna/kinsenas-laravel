<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Trialing = 'trialing';
    case Active = 'active';
    case PastDue = 'past_due';
    case Cancelled = 'cancelled';
    case OpenBeta = 'open_beta';

    public function label(): string
    {
        return match ($this) {
            self::Trialing => 'Trialing',
            self::Active => 'Active',
            self::PastDue => 'Past Due',
            self::Cancelled => 'Cancelled',
            self::OpenBeta => 'Open beta',
        };
    }

    public function allowsSavingsAccess(): bool
    {
        return match ($this) {
            self::Trialing, self::Active, self::OpenBeta => true,
            self::PastDue, self::Cancelled => false,
        };
    }
}
