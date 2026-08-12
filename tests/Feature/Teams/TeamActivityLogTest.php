<?php

use App\Enums\TeamRole;
use App\Enums\UserActivityAction;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        BillingSeeder::class,
        SavingsFormulaTemplateSeeder::class,
    ]);
});

it('logs invitation send cancel accept and decline actions', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $team = Team::factory()->create();
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    prepareTeamForInvites($owner, $team);

    $this->actingAs($owner)->post(route('teams.invitations.store', $team), [
        'email' => 'invited@example.com',
        'role' => TeamRole::Member->value,
    ])->assertRedirect();

    expect(Activity::query()->where('event', UserActivityAction::TeamInvitationSent->value)->exists())->toBeTrue();

    $invitation = TeamInvitation::query()->where('email', 'invited@example.com')->firstOrFail();

    $this->actingAs($owner)->delete(route('teams.invitations.destroy', [$team, $invitation]))
        ->assertRedirect();

    expect(Activity::query()->where('event', UserActivityAction::TeamInvitationCancelled->value)->exists())->toBeTrue();

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'role' => TeamRole::Member,
        'invited_by' => $owner->id,
    ]);

    $this->actingAs($invitedUser)->get(route('invitations.accept', $invitation))
        ->assertRedirect(route('dashboard'));

    expect(Activity::query()->where('event', UserActivityAction::TeamInvitationAccepted->value)->exists())->toBeTrue();

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $this->actingAs($invitedUser)->delete(route('invitations.decline', $invitation))
        ->assertRedirect(route('dashboard'));

    expect(Activity::query()->where('event', UserActivityAction::TeamInvitationDeclined->value)->exists())->toBeTrue();
});

it('logs member role updates and removals without financial data', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create(['email' => 'member@example.com']);
    $team = Team::factory()->create();
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);
    grantTeamSubscriptionAccess($team);

    $this->actingAs($owner)->patch(route('teams.members.update', [$team, $member]), [
        'role' => TeamRole::Admin->value,
    ])->assertRedirect();

    $roleLog = Activity::query()->where('event', UserActivityAction::TeamMemberRoleUpdated->value)->firstOrFail();

    expect(data_get($roleLog->properties, 'amount'))->toBeNull()
        ->and(data_get($roleLog->properties, 'role_label'))->toBe('Admin');

    $this->actingAs($owner)->delete(route('teams.members.destroy', [$team, $member]))
        ->assertRedirect();

    expect(Activity::query()->where('event', UserActivityAction::TeamMemberRemoved->value)->exists())->toBeTrue();
});

it('logs leave team actions', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['is_personal' => false]);
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);
    grantTeamSubscriptionAccess($team);

    $this->actingAs($member)->delete(route('teams.leave', $team))
        ->assertRedirect(route('teams.index'));

    expect(Activity::query()->where('event', UserActivityAction::TeamLeft->value)->exists())->toBeTrue();
});
