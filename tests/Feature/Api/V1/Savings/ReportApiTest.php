<?php

use App\Models\FundSpend;
use App\Models\SavingsPlan;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(fn () => $this->seed([BillingSeeder::class, SavingsFormulaTemplateSeeder::class]));

it('returns savings report totals for the scoped team', function () {
    $user = User::factory()->create();
    grantTeamSubscriptionAccess($user->currentTeam);
    $this->unlockVaultFor($user);
    SavingsPlan::factory()->create(['team_id' => $user->currentTeam->id, 'created_by_user_id' => $user->id]);
    Sanctum::actingAs($user);

    $response = $this->getJson("/api/v1/teams/{$user->currentTeam->id}/savings/reports");

    $response->assertSuccessful()->assertJsonStructure([
        'data',
        'graphs' => [
            'range',
            'fund_utilization',
            'spending_by_fund',
            'spending_over_time',
            'income_vs_spending',
            'top_recipients',
        ],
    ]);
});

it('filters report graphs by date range query params', function () {
    [$user, $plan, $everydayCategory] = createUserWithLockedIncome();
    grantTeamSubscriptionAccess($user->currentTeam);
    Sanctum::actingAs($user);

    FundSpend::factory()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $everydayCategory->id,
        'amount_encrypted' => '1000.00',
        'spent_on' => '2026-01-15',
    ]);

    FundSpend::factory()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $everydayCategory->id,
        'amount_encrypted' => '2000.00',
        'spent_on' => '2026-03-15',
    ]);

    $response = $this->getJson("/api/v1/teams/{$user->currentTeam->id}/savings/reports?from=2026-01-01&to=2026-01-31");

    $response->assertSuccessful()
        ->assertJsonPath('graphs.range.from', '2026-01-01')
        ->assertJsonPath('graphs.range.to', '2026-01-31')
        ->assertJsonPath('graphs.spending_over_time.0.total', '1000.00');
});
