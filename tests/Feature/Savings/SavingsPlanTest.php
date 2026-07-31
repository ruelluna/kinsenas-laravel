<?php

namespace Tests\Feature\Savings;

use App\Models\SavingsFormulaTemplate;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\UnlocksVault;
use Tests\TestCase;

class SavingsPlanTest extends TestCase
{
    use RefreshDatabase, UnlocksVault;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            SavingsFormulaTemplateSeeder::class,
            BillingSeeder::class,
        ]);
    }

    public function test_user_can_create_savings_plan_from_template(): void
    {
        $user = User::factory()->create();
        $this->unlockVaultFor($user);
        $template = SavingsFormulaTemplate::query()->where('slug', 'trc-savings')->firstOrFail();

        $response = $this
            ->actingAs($user)
            ->post(route('savings.plan.from-template', [
                'current_team' => $user->currentTeam->slug,
                'template' => $template->id,
            ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('savings_plans', [
            'team_id' => $user->currentTeam->id,
            'created_by_user_id' => $user->id,
        ]);
        $this->assertDatabaseCount('savings_categories', 7);
    }

    public function test_category_percentages_must_total_one_hundred(): void
    {
        $user = User::factory()->create();
        $this->unlockVaultFor($user);
        $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

        $this->actingAs($user)->post(route('savings.plan.from-template', [
            'current_team' => $user->currentTeam->slug,
            'template' => $template->id,
        ]));

        $response = $this->actingAs($user)->put(route('savings.plan.update', [
            'current_team' => $user->currentTeam->slug,
        ]), [
            'categories' => [
                ['name' => 'A', 'percentage' => 50],
                ['name' => 'B', 'percentage' => 40],
            ],
        ]);

        $response->assertSessionHasErrors('categories');
    }

    public function test_income_amount_is_encrypted_in_database(): void
    {
        $user = User::factory()->create();
        $this->unlockVaultFor($user);
        $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

        $this->actingAs($user)->post(route('savings.plan.from-template', [
            'current_team' => $user->currentTeam->slug,
            'template' => $template->id,
        ]));

        $this->actingAs($user)->post(route('savings.income.store', [
            'current_team' => $user->currentTeam->slug,
        ]), [
            'amount' => '50000.00',
            'period_start' => '2026-01-01',
        ]);

        $raw = \DB::table('income_periods')->value('amount_encrypted');

        $this->assertIsString($raw);
        $this->assertStringNotContainsString('50000', $raw);
    }
}
