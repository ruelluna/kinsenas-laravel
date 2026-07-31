<?php

namespace App\Enums;

enum TransferStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
        };
    }
}
