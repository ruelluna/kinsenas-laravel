<?php

use App\Enums\PlatformRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        (new RolePermissionSeeder)->run();

        User::query()
            ->where('is_platform_admin', true)
            ->each(fn (User $user) => $user->syncRoles([PlatformRole::PlatformAdmin->value]));

        User::query()
            ->whereDoesntHave('roles')
            ->each(fn (User $user) => $user->assignRole(PlatformRole::User->value));

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_platform_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_platform_admin')->default(false)->after('current_team_id');
        });

        User::query()
            ->role(PlatformRole::PlatformAdmin->value)
            ->update(['is_platform_admin' => true]);
    }
};
