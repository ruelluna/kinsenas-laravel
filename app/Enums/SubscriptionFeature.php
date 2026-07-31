<?php

namespace App\Enums;

enum SubscriptionFeature: string
{
    case SavingsPlan = 'savings_plan';
    case Transfers = 'transfers';
    case Reports = 'reports';

    public function label(): string
    {
        return match ($this) {
            self::SavingsPlan => 'Savings Plan',
            self::Transfers => 'Transfers',
            self::Reports => 'Reports',
        };
    }
}
