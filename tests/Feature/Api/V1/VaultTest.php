<?php

use App\Models\User;
use App\Services\Vault\VaultKeyManager;
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

it('unlocks vault with password for token auth', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);
    app(VaultKeyManager::class)->forgetAll();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/vault/unlock', [
        'password' => 'password',
    ]);

    $response->assertSuccessful();
    $response->assertJsonPath('vaultLocked', false);
});

it('keeps vault unlocked on subsequent api requests after token unlock', function () {
    $user = User::factory()->create();
    app(VaultKeyManager::class)->forgetAll();

    Sanctum::actingAs($user);

    $this->postJson('/api/v1/vault/unlock', [
        'password' => 'password',
    ])->assertSuccessful();

    $bootstrap = $this->getJson('/api/v1/auth/bootstrap');

    $bootstrap->assertSuccessful();
    $bootstrap->assertJsonPath('vaultLocked', false);
});

it('returns locked response for protected routes when vault is locked', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/teams/'.$user->currentTeam->id.'/dashboard');

    $response->assertStatus(423);
    $response->assertJsonPath('vault_locked', true);
});
