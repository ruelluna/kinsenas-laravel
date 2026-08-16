<?php

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        BillingSeeder::class,
        SavingsFormulaTemplateSeeder::class,
    ]);
});

it('disables invite button when team setup is incomplete', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    grantTeamSubscriptionAccess($team);

    $page = visit('/login');
    browserLogin($page, $owner);

    $page->navigate(route('teams.edit', $team));

    $page->assertSee('Team members')
        ->assertPresent('@invite-setup-blocked')
        ->assertNoSmoke();
});

it('shows enabled invite button when team setup is complete', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    prepareTeamForInvites($owner, $team);

    $page = visit('/login');
    browserLogin($page, $owner);

    $page->navigate(route('teams.edit', $team));

    $page->assertPresent('@invite-member-button')
        ->assertNotPresent('@invite-setup-blocked')
        ->assertNoSmoke();
});
