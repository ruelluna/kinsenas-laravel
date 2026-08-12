<?php

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\Teams\TeamInvitation as TeamInvitationNotification;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('team invitations can be created', function () {
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
        'role' => TeamRole::Member->value,
    ]);
});

it('invitation email for existing users uses login route', function () {
    $owner = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => $invitedUser->email,
        'invited_by' => $owner->id,
    ]);

    $mail = (new TeamInvitationNotification($invitation))->toMail($invitedUser);

    $this->assertSame(route('login', ['invitation' => $invitation->code]), $mail->actionUrl);
    $this->assertStringContainsString('dashboard', implode(' ', $mail->introLines));
});

it('invitation email for unknown users uses login route', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'unknown@example.com',
        'invited_by' => $owner->id,
    ]);

    $mail = (new TeamInvitationNotification($invitation))->toMail((object) []);

    $this->assertSame(route('login', ['invitation' => $invitation->code]), $mail->actionUrl);
    $this->assertStringContainsString('log in', strtolower(implode(' ', $mail->introLines)));
});

it('team invitations can be created by admins', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($admin, ['role' => TeamRole::Admin->value]);
    prepareTeamForInvites($admin, $team);

    $response = $this
        ->actingAs($admin)
        ->post(route('teams.invitations.store', $team), [
            'email' => 'invited@example.com',
            'role' => TeamRole::Member->value,
        ]);

    $response->assertRedirect(route('teams.edit', $team));
});

it('existing team members cannot be invited', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $member = User::factory()->create(['email' => 'member@example.com']);
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $response = $this
        ->actingAs($owner)
        ->post(route('teams.invitations.store', $team), [
            'email' => 'member@example.com',
            'role' => TeamRole::Member->value,
        ]);

    $response->assertSessionHasErrors('email');
});

it('duplicate invitations cannot be created', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $response = $this
        ->actingAs($owner)
        ->post(route('teams.invitations.store', $team), [
            'email' => 'invited@example.com',
            'role' => TeamRole::Member->value,
        ]);

    $response->assertSessionHasErrors('email');
});

it('team invitations cannot be created by members', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);
    grantTeamSubscriptionAccess($team);
    prepareTeamForInvites($owner, $team);

    $response = $this
        ->actingAs($member)
        ->post(route('teams.invitations.store', $team), [
            'email' => 'invited@example.com',
            'role' => TeamRole::Member->value,
        ]);

    $response->assertForbidden();
});

it('team invitations can be cancelled by owners', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $owner->id,
    ]);

    $response = $this
        ->actingAs($owner)
        ->delete(route('teams.invitations.destroy', [$team, $invitation]));

    $response->assertRedirect(route('teams.edit', $team));

    $this->assertDatabaseMissing('team_invitations', [
        'id' => $invitation->id,
    ]);
});

it('team invitations can be accepted', function () {
    $owner = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'role' => TeamRole::Member,
        'invited_by' => $owner->id,
    ]);

    $response = $this
        ->actingAs($invitedUser)
        ->get(route('invitations.accept', $invitation));

    $response->assertRedirect(route('dashboard'));
    $response->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Invitation accepted.']);

    $this->assertTrue($invitedUser->fresh()->belongsToTeam($team));
    $this->assertNotNull($invitation->fresh()->accepted_at);
});

it('team invitations can be declined by the invited user', function () {
    $owner = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $response = $this
        ->actingAs($invitedUser)
        ->delete(route('invitations.decline', $invitation));

    $response->assertRedirect(route('dashboard'));

    $this->assertDatabaseMissing('team_invitations', [
        'id' => $invitation->id,
    ]);
});

it('team invitations cannot be declined by uninvited user', function () {
    $owner = User::factory()->create();
    $uninvitedUser = User::factory()->create(['email' => 'uninvited@example.com']);
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $response = $this
        ->actingAs($uninvitedUser)
        ->delete(route('invitations.decline', $invitation));

    $response->assertSessionHasErrors('invitation');

    $this->assertDatabaseHas('team_invitations', [
        'id' => $invitation->id,
    ]);
});

it('accepted team invitations cannot be declined', function () {
    $owner = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->accepted()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $response = $this
        ->actingAs($invitedUser)
        ->delete(route('invitations.decline', $invitation));

    $response->assertSessionHasErrors('invitation');

    $this->assertDatabaseHas('team_invitations', [
        'id' => $invitation->id,
    ]);
});

it('team invitations cannot be accepted by uninvited user', function () {
    $owner = User::factory()->create();
    $uninvitedUser = User::factory()->create(['email' => 'uninvited@example.com']);
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $response = $this
        ->actingAs($uninvitedUser)
        ->get(route('invitations.accept', $invitation));

    $response->assertSessionHasErrors('invitation');

    $this->assertFalse($uninvitedUser->fresh()->belongsToTeam($team));
});

it('expired invitations cannot be accepted', function () {
    $owner = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->expired()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $response = $this
        ->actingAs($invitedUser)
        ->get(route('invitations.accept', $invitation));

    $response->assertSessionHasErrors('invitation');

    $this->assertFalse($invitedUser->fresh()->belongsToTeam($team));
});
