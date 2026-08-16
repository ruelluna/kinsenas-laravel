<?php

namespace App\Enums;

enum ReimbursementStatus: string
{
    case None = 'none';
    case Awaiting = 'awaiting';
    case Partial = 'partial';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::None => __('None'),
            self::Awaiting => __('Awaiting payback'),
            self::Partial => __('Partially repaid'),
            self::Resolved => __('Paid back'),
            self::Closed => __('Closed'),
        };
    }
}
