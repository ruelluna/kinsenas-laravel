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

it('records a confirmed spend from an opening balance', function () {
    $user = User::factory()->create();
    grantTeamSubscriptionAccess($user->currentTeam);
    $this->unlockVaultFor($user);
    $plan = SavingsPlan::factory()->create(['team_id' => $user->currentTeam->id, 'created_by_user_id' => $user->id]);
    $category = SavingsCategory::factory()->withOpeningBalance()->create(['plan_id' => $plan->id, 'percentage' => 100]);
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/teams/{$user->currentTeam->id}/savings/spending", [
        'category_id' => $category->id,
        'amount' => '100.00',
        'description' => 'Mobile groceries',
        'spent_on' => '2026-08-01',
    ]);

    $response->assertCreated()->assertJsonPath('data.description', 'Mobile groceries');
    $this->assertDatabaseHas('fund_spends', ['savings_plan_id' => $plan->id, 'description' => 'Mobile groceries']);
});
