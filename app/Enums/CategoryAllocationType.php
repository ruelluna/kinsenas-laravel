<?php

namespace App\Enums;

enum CategoryAllocationType: string
{
    case Percentage = 'percentage';
    case Deduction = 'deduction';

    public function label(): string
    {
        return match ($this) {
            self::Percentage => 'Percentage',
            self::Deduction => 'Deduction',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $type) => ['value' => $type->value, 'label' => $type->label()],
            self::cases(),
        );
    }
}
