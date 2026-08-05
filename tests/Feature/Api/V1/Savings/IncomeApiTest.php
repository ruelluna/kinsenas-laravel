<?php

use App\Models\SavingsCategory;
use App\Models\SavingsPlan;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(fn () => $this->seed(BillingSeeder::class));

it('records and allocates an income period', function () {
    $user = User::factory()->create();
    grantTeamSubscriptionAccess($user->currentTeam);
    $this->unlockVaultFor($user);
    $plan = SavingsPlan::factory()->create(['team_id' => $user->currentTeam->id, 'created_by_user_id' => $user->id]);
    SavingsCategory::factory()->create(['plan_id' => $plan->id, 'percentage' => 100]);
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/teams/{$user->currentTeam->id}/savings/income", [
        'name' => 'August salary',
        'amount' => '50000.00',
        'period_start' => '2026-08-01',
    ]);

    $response->assertCreated()->assertJsonPath('data.label', 'August salary');
    $this->assertDatabaseHas('income_periods', ['plan_id' => $plan->id, 'name' => 'August salary']);
});
