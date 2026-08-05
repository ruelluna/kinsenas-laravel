<?php

use App\Models\SavingsPlan;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(fn () => $this->seed(BillingSeeder::class));

it('returns savings report totals for the scoped team', function () {
    $user = User::factory()->create();
    grantTeamSubscriptionAccess($user->currentTeam);
    $this->unlockVaultFor($user);
    SavingsPlan::factory()->create(['team_id' => $user->currentTeam->id, 'created_by_user_id' => $user->id]);
    Sanctum::actingAs($user);

    $response = $this->getJson("/api/v1/teams/{$user->currentTeam->id}/savings/reports");

    $response->assertSuccessful()->assertJsonStructure(['data']);
});
