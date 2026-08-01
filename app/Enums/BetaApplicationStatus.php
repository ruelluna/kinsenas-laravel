<?php

namespace App\Enums;

enum BetaApplicationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function filterOptions(): array
    {
        return [
            ['value' => '', 'label' => 'All'],
            ...array_map(
                fn (self $status) => ['value' => $status->value, 'label' => $status->label()],
                self::cases(),
            ),
        ];
    }
}
