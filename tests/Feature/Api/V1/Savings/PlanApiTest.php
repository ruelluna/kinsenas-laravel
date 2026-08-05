<?php

use App\Models\SavingsFormulaTemplate;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    $this->seed([BillingSeeder::class, SavingsFormulaTemplateSeeder::class]);
});

it('creates a savings plan from a template', function () {
    $user = User::factory()->create();
    grantTeamSubscriptionAccess($user->currentTeam);
    $this->unlockVaultFor($user);
    Sanctum::actingAs($user);
    $template = SavingsFormulaTemplate::query()->firstOrFail();

    $response = $this->postJson("/api/v1/teams/{$user->currentTeam->id}/savings/plan/from-template/{$template->id}");

    $response->assertCreated()->assertJsonPath('data.name', $template->name);
    $this->assertDatabaseHas('savings_plans', ['team_id' => $user->currentTeam->id]);
});
