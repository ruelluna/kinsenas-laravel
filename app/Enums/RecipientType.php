<?php

namespace App\Enums;

enum RecipientType: string
{
    case Person = 'person';
    case Organization = 'organization';
    case Church = 'church';
    case Community = 'community';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Person => 'Person',
            self::Organization => 'Organization',
            self::Church => 'Church',
            self::Community => 'Community',
            self::Other => 'Other',
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
