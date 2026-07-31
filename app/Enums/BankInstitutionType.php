<?php

namespace App\Enums;

enum BankInstitutionType: string
{
    case Bank = 'bank';
    case EWallet = 'e_wallet';

    public function label(): string
    {
        return match ($this) {
            self::Bank => 'Bank',
            self::EWallet => 'E-Wallet',
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
