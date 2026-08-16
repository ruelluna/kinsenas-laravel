<?php

namespace Database\Seeders;

use App\Enums\PlatformPermission;
use App\Enums\PlatformRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PlatformPermission::cases() as $permission) {
            Permission::findOrCreate($permission->value, 'web');
        }

        foreach (PlatformRole::cases() as $platformRole) {
            $role = Role::findOrCreate($platformRole->value, 'web');

            $role->syncPermissions(
                collect($platformRole->permissions())
                    ->map(fn (PlatformPermission $permission) => $permission->value)
                    ->all(),
            );
        }
    }
}
