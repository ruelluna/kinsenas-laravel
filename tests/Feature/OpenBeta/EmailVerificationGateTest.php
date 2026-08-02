<?php

use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
    config(['billing.mode' => 'open_beta']);
});

it('redirects unverified users away from the dashboard', function () {
    $user = User::factory()->unverified()->betaApproved()->create(['email' => 'unverified@example.com']);
    $this->unlockVaultFor($user);
    $team = $user->personalTeam();

    $response = $this->actingAs($user)->get(route('dashboard', ['current_team' => $team->slug]));

    $response->assertRedirect(route('verification.notice'));
});

it('allows verified approved users to access the dashboard', function () {
    $user = User::factory()->betaApproved()->create(['email' => 'verified@example.com']);
    $this->unlockVaultFor($user);
    $team = $user->personalTeam();

    $response = $this->actingAs($user)->get(route('dashboard', ['current_team' => $team->slug]));

    $response->assertOk();
});

it('redirects newly registered open beta users to the email verification page', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Beta User',
        'email' => 'beta-user@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice', absolute: false));
});

it('does not mark newly registered users as email verified', function () {
    $this->post(route('register.store'), [
        'name' => 'Unverified User',
        'email' => 'unverified-user@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'unverified-user@example.com')->firstOrFail();

    expect($user->hasVerifiedEmail())->toBeFalse()
        ->and($user->email_verified_at)->toBeNull();
});
