<?php

namespace App\Enums;

enum BetaFeedbackCategory: string
{
    case Bug = 'bug';
    case Feature = 'feature';
    case General = 'general';

    public function label(): string
    {
        return match ($this) {
            self::Bug => 'Bug report',
            self::Feature => 'Feature idea',
            self::General => 'General feedback',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $category) => ['value' => $category->value, 'label' => $category->label()],
            self::cases(),
        );
    }
}
