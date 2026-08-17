<?php

namespace Database\Seeders;

use App\Models\SavingsFormulaTemplate;
use App\Models\User;
use App\Services\Savings\SavingsPlanService;
use Illuminate\Database\Seeder;

class SavingsPlanSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SavingsFormulaTemplateSeeder::class,
        ]);

        $template = SavingsFormulaTemplate::query()
            ->where('slug', 'simple-plan')
            ->firstOrFail();

        $user = User::query()->where('email', 'simple-plan-demo@kinsenas.test')->first();

        if ($user === null) {
            $user = User::factory()->create([
                'name' => 'Simple Plan Demo',
                'email' => 'simple-plan-demo@kinsenas.test',
            ]);
        }

        $planService = app(SavingsPlanService::class);

        if ($planService->forTeam($user->currentTeam, $user) !== null) {
            return;
        }

        $planService->cloneFromTemplate(
            $user->currentTeam,
            $user,
            $template,
            $template->name,
        );
    }
}
