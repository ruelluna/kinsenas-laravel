<?php

namespace App\Enums;

enum DeductionMode: string
{
    case Fixed = 'fixed';
    case PercentOfIncome = 'percent_of_income';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => 'Fixed amount',
            self::PercentOfIncome => 'Percent of income',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $mode) => ['value' => $mode->value, 'label' => $mode->label()],
            self::cases(),
        );
    }
}
