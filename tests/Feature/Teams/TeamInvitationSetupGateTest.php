<?php

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        BillingSeeder::class,
        SavingsFormulaTemplateSeeder::class,
    ]);
});

it('blocks invitations until team setup is complete', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    grantTeamSubscriptionAccess($team);

    $response = $this
        ->actingAs($owner)
        ->post(route('teams.invitations.store', $team), [
            'email' => 'invited@example.com',
            'role' => TeamRole::Member->value,
        ]);

    $response->assertSessionHasErrors('setup');
    $this->assertDatabaseMissing('team_invitations', [
        'team_id' => $team->id,
        'email' => 'invited@example.com',
    ]);
});

it('allows invitations when bank plan and income are complete', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    prepareTeamForInvites($owner, $team);

    $response = $this
        ->actingAs($owner)
        ->post(route('teams.invitations.store', $team), [
            'email' => 'invited@example.com',
            'role' => TeamRole::Member->value,
        ]);

    $response->assertRedirect(route('teams.edit', $team));

    $this->assertDatabaseHas('team_invitations', [
        'team_id' => $team->id,
        'email' => 'invited@example.com',
    ]);
});

it('forbids invitations through policy when setup is incomplete', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    grantTeamSubscriptionAccess($team);

    expect($owner->can('inviteMember', $team))->toBeFalse();
});
