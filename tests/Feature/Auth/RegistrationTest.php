<?php

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

it('registration screen includes team invitation context', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Laravel Team']);
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $response = $this->get(route('register', ['invitation' => $invitation->code]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('auth/register')
        ->where('teamInvitation.code', $invitation->code)
        ->where('teamInvitation.teamName', 'Laravel Team'),
    );
});

it('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();

    $user = User::where('email', 'test@example.com')->first();
    $response->assertRedirect(route('dashboard'));
});

it('registration creates a user named default workspace', function () {
    $this->post(route('register.store'), [
        'name' => 'Ruel Luna',
        'email' => 'ruel@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'ruel@example.com')->firstOrFail();
    $team = $user->personalTeam();

    expect($team)->not->toBeNull()
        ->and($team->name)->toBe("Ruel Luna's finances")
        ->and($team->slug)->toBe('ruel-luna')
        ->and($team->is_personal)->toBeTrue();
});

it('registration assigns distinct slugs when display names collide', function () {
    $this->post(route('register.store'), [
        'name' => 'Juan Dela Cruz',
        'email' => 'juan1@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->post(route('register.store'), [
        'name' => 'Juan Dela Cruz',
        'email' => 'juan2@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $firstTeam = User::where('email', 'juan1@example.com')->firstOrFail()->personalTeam();
    $secondTeam = User::where('email', 'juan2@example.com')->firstOrFail()->personalTeam();

    expect($firstTeam->slug)->toBe('juan-dela-cruz')
        ->and($secondTeam->slug)->toBe('juan-dela-cruz-1');
});
