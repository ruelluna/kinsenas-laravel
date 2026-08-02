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

function createUserWithoutSavingsPlan(): User
{
    $user = User::factory()->create();
    test()->unlockVaultFor($user);

    return $user;
}

it('redirects income index to plan chooser when no savings plan exists', function () {
    $user = createUserWithoutSavingsPlan();

    $response = $this->actingAs($user)->get(route('savings.income.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertRedirect(route('savings.plan.show', [
        'current_team' => $user->currentTeam->slug,
    ]));
    $response->assertSessionHas('error');
});

it('redirects transfers index to plan chooser when no savings plan exists', function () {
    $user = createUserWithoutSavingsPlan();

    $response = $this->actingAs($user)->get(route('savings.transfers.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertRedirect(route('savings.plan.show', [
        'current_team' => $user->currentTeam->slug,
    ]));
    $response->assertSessionHas('error');
});

it('redirects spending index to plan chooser when no savings plan exists', function () {
    $user = createUserWithoutSavingsPlan();

    $response = $this->actingAs($user)->get(route('savings.spending.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertRedirect(route('savings.plan.show', [
        'current_team' => $user->currentTeam->slug,
    ]));
    $response->assertSessionHas('error');
});

it('redirects reports to plan chooser when no savings plan exists', function () {
    $user = createUserWithoutSavingsPlan();

    $response = $this->actingAs($user)->get(route('savings.reports', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertRedirect(route('savings.plan.show', [
        'current_team' => $user->currentTeam->slug,
    ]));
    $response->assertSessionHas('error');
});
