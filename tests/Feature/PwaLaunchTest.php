<?php

use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    $this->seed([
        SavingsFormulaTemplateSeeder::class,
        BillingSeeder::class,
    ]);
});

it('redirects guests to login', function () {
    $this->get(route('pwa.launch'))
        ->assertRedirect(route('login'));
});

it('redirects authenticated members to their team dashboard', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);

    $this->actingAs($user)
        ->get(route('pwa.launch'))
        ->assertRedirect(route('dashboard', ['current_team' => $user->currentTeam->slug]));
});

it('redirects authenticated users without a current team to teams settings', function () {
    $user = User::factory()->create();
    $user->update(['current_team_id' => null]);

    $this->actingAs($user->fresh())
        ->get(route('pwa.launch'))
        ->assertRedirect(route('teams.index'));
});
