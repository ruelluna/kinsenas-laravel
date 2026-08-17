<?php

namespace Database\Seeders;

use App\Enums\PlatformRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            SavingsFormulaTemplateSeeder::class,
            SavingsPlanPageGuidanceSeeder::class,
            BillingSeeder::class,
            PhilippineBankSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $admin = User::factory()->create([
            'name' => 'Platform Admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
        ]);
        $admin->syncPlatformRole(PlatformRole::PlatformAdmin);

        $this->call(ContentPostCategorySeeder::class);
        $this->call(ContentSeeder::class);
        $this->call(LearnLibrarySeeder::class);
        $this->call(CommunitySeeder::class);
        $this->call(DemoAccountSeeder::class);
    }
}
