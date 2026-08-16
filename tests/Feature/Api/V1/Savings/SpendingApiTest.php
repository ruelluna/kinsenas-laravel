<?php

use App\Enums\RecipientType;
use App\Models\Recipient;
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

    $this->postJson('/api/v1/vault/unlock', [
        'password' => 'password',
    ])->assertSuccessful();

    $response = $this->postJson("/api/v1/teams/{$user->currentTeam->id}/savings/spending", [
        'category_id' => $category->id,
        'amount' => '100.00',
        'description' => 'Mobile groceries',
        'spent_on' => '2026-08-01',
    ]);

    $response->assertCreated()->assertJsonPath('data.description', 'Mobile groceries');
    $this->assertDatabaseHas('fund_spends', ['savings_plan_id' => $plan->id, 'description' => 'Mobile groceries']);
});

it('records payback via api', function () {
    $user = User::factory()->create();
    grantTeamSubscriptionAccess($user->currentTeam);
    $this->unlockVaultFor($user);
    $plan = SavingsPlan::factory()->create(['team_id' => $user->currentTeam->id, 'created_by_user_id' => $user->id]);
    $category = SavingsCategory::factory()->withOpeningBalance('5000.00')->create(['plan_id' => $plan->id, 'percentage' => 100]);
    $recipient = Recipient::query()->create([
        'team_id' => $user->currentTeam->id,
        'type' => RecipientType::Person,
        'name' => 'Ana',
    ]);

    Sanctum::actingAs($user);

    $this->postJson('/api/v1/vault/unlock', [
        'password' => 'password',
    ])->assertSuccessful();

    $spendResponse = $this->postJson("/api/v1/teams/{$user->currentTeam->id}/savings/spending", [
        'category_id' => $category->id,
        'amount' => '1000.00',
        'description' => 'Bill for Ana',
        'spent_on' => '2026-08-01',
        'expects_reimbursement' => true,
        'expected_from_recipient_id' => $recipient->id,
    ]);

    $spendResponse->assertCreated();
    $spendId = $spendResponse->json('data.id');

    $paybackResponse = $this->postJson("/api/v1/teams/{$user->currentTeam->id}/savings/spending/{$spendId}/reimbursements", [
        'amount' => '600.00',
        'received_on' => '2026-08-05',
    ]);

    $paybackResponse->assertOk()
        ->assertJsonPath('data.reimbursementStatus', 'partial')
        ->assertJsonPath('data.reimbursedAmount', '600.00')
        ->assertJsonPath('data.remainingOwed', '400.00');
});
