<?php

use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(fn () => $this->seed(BillingSeeder::class));

it('creates a bank account for the scoped team', function () {
    $user = User::factory()->create();
    grantTeamSubscriptionAccess($user->currentTeam);
    $this->unlockVaultFor($user);
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/teams/{$user->currentTeam->id}/savings/banks", [
        'name' => 'Mobile Savings',
        'account_label' => 'Main',
    ]);

    $response->assertCreated()->assertJsonPath('data.name', 'Mobile Savings');
    $this->assertDatabaseHas('banks', ['team_id' => $user->currentTeam->id, 'name' => 'Mobile Savings']);
});
