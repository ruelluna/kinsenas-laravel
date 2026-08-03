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

it('stores a database notification for registered invitees', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $team = Team::factory()->create();
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $this->actingAs($owner)
        ->post(route('teams.invitations.store', $team), [
            'email' => $invitedUser->email,
            'role' => TeamRole::Member->value,
        ])
        ->assertRedirect(route('teams.edit', $team));

    Notification::assertSentTo($invitedUser, TeamInvitationNotification::class);
});

it('includes database payload for team invitations', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => $owner->email,
        'invited_by' => $owner->id,
    ]);

    $payload = (new TeamInvitationNotification($invitation))->toArray($owner);

    expect($payload['kind'])->toBe('team_invitation')
        ->and($payload['title'])->not->toBeEmpty()
        ->and($payload['actionUrl'])->toBe('/launch');
});
