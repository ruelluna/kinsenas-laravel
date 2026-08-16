<?php

namespace App\Enums;

enum PlatformPermission: string
{
    case ManagePlatform = 'admin.manage-platform';
    case ManageContent = 'admin.manage-content';

    public function label(): string
    {
        return match ($this) {
            self::ManagePlatform => 'Manage platform',
            self::ManageContent => 'Manage content',
        };
    }
}
