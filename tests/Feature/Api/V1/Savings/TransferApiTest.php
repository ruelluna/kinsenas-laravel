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

it('records a same-bank transfer', function () {
    $user = User::factory()->create();
    grantTeamSubscriptionAccess($user->currentTeam);
    $this->unlockVaultFor($user);
    $plan = SavingsPlan::factory()->create(['team_id' => $user->currentTeam->id, 'created_by_user_id' => $user->id]);
    $fromCategory = SavingsCategory::factory()->withOpeningBalance('1000.00')->create(['plan_id' => $plan->id, 'percentage' => 50]);
    $toCategory = SavingsCategory::factory()->create(['plan_id' => $plan->id, 'percentage' => 50, 'sort_order' => 1]);
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/teams/{$user->currentTeam->id}/savings/transfers", [
        'from_category_id' => $fromCategory->id,
        'to_category_id' => $toCategory->id,
        'amount' => '100.00',
        'description' => 'Mobile transfer',
        'transferred_on' => '2026-08-01',
    ]);

    $response->assertCreated()->assertJsonPath('data.description', 'Mobile transfer');
    $this->assertDatabaseHas('fund_transfers', ['savings_plan_id' => $plan->id, 'description' => 'Mobile transfer']);
});
