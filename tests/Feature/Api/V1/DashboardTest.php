<?php

use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    $this->seed([
        SavingsFormulaTemplateSeeder::class,
        BillingSeeder::class,
    ]);
});

it('returns dashboard data for team member', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/vault/unlock', [
        'password' => 'password',
    ])->assertSuccessful();

    $response = $this->getJson('/api/v1/teams/'.$user->currentTeam->id.'/dashboard');

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'data' => [
            'setup',
            'summary',
            'fundBalances',
            'pendingInvitations',
        ],
    ]);
});

it('includes allocation metadata on fund balances after income is locked', function () {
    [$user] = createUserWithLockedIncome();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/vault/unlock', [
        'password' => 'password',
    ])->assertSuccessful();

    $response = $this->getJson('/api/v1/teams/'.$user->currentTeam->id.'/dashboard');

    $response->assertSuccessful();
    $response->assertJsonPath('data.fundBalances.0.name', 'Everyday Fund');
    $response->assertJsonPath('data.fundBalances.0.allocationType', 'percentage');
    $response->assertJsonPath('data.fundBalances.0.percentage', '70.00');
    $response->assertJsonPath('data.summary.defaultFundName', 'Everyday Fund');
    $response->assertJsonPath('data.summary.defaultFundRemaining', '35000.00');
    $response->assertJsonPath('data.summary.otherFundsRemaining', '15000.00');
});

it('forbids dashboard for non member team', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $this->unlockVaultFor($user);
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/teams/'.$otherUser->currentTeam->id.'/dashboard');

    $response->assertForbidden();
});

it('switches current team', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $team = $user->ownedTeams()->first();

    $response = $this->postJson('/api/v1/teams/switch', [
        'team_id' => $team->id,
    ]);

    $response->assertSuccessful();
    $response->assertJsonPath('currentTeam.id', $team->id);
});
