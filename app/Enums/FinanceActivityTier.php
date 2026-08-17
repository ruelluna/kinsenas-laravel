<?php

namespace App\Enums;

enum FinanceActivityTier: string
{
    case Inactive = 'inactive';
    case Partial = 'partial';
    case Active = 'active';
    case Activated = 'activated';

    public function label(): string
    {
        return match ($this) {
            self::Inactive => 'Inactive',
            self::Partial => 'Partial',
            self::Active => 'Active',
            self::Activated => 'Activated',
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function filterOptions(): array
    {
        return [
            ['value' => '', 'label' => 'All activity tiers'],
            ...array_map(
                fn (self $tier) => ['value' => $tier->value, 'label' => $tier->label()],
                self::cases(),
            ),
        ];
    }
}
