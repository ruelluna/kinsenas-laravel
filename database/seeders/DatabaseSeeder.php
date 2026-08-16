<?php

namespace Database\Seeders;

use App\Enums\PlatformRole;
use App\Models\SavingsFormulaTemplate;
use App\Models\User;
use App\Services\Savings\SavingsPlanService;
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

        // $user = User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // $template = SavingsFormulaTemplate::query()->where('slug', 'trc-savings')->first();

        // if ($template !== null) {
        //     app(SavingsPlanService::class)->cloneFromTemplate(
        //         $user->currentTeam,
        //         $user,
        //         $template,
        //         'My TRC Plan',
        //     );
        // }

        $admin = User::factory()->create([
            'name' => 'Platform Admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
        ]);
        $admin->syncPlatformRole(PlatformRole::PlatformAdmin);

        $this->call(ContentSeeder::class);
        $this->call(LearnLibrarySeeder::class);
    }
}
