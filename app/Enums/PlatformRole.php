<?php

namespace App\Enums;

enum PlatformRole: string
{
    case PlatformAdmin = 'platform-admin';
    case Author = 'author';
    case User = 'user';

    public function label(): string
    {
        return match ($this) {
            self::PlatformAdmin => 'Platform Admin',
            self::Author => 'Author',
            self::User => 'User',
        };
    }

    /**
     * @return array<PlatformPermission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::PlatformAdmin => PlatformPermission::cases(),
            self::Author => [
                PlatformPermission::ManageContent,
            ],
            self::User => [],
        };
    }

    /**
     * @return array<array{value: string, label: string}>
     */
    public static function assignable(): array
    {
        return collect(self::cases())
            ->map(fn (self $role) => [
                'value' => $role->value,
                'label' => $role->label(),
            ])
            ->values()
            ->all();
    }
}
