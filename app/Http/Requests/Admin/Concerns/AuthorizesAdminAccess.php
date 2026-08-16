<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Enums\PlatformPermission;

trait AuthorizesAdminAccess
{
    protected function authorizePlatformManagement(): bool
    {
        return $this->user()?->can(PlatformPermission::ManagePlatform->value) ?? false;
    }

    protected function authorizeContentManagement(): bool
    {
        return $this->user()?->can(PlatformPermission::ManageContent->value) ?? false;
    }
}
