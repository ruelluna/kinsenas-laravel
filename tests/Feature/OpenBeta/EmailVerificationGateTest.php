<?php

use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('allows unverified users to access the dashboard', function () {
    $user = User::factory()->unverified()->betaApproved()->create(['email' => 'unverified@example.com']);
    $this->unlockVaultFor($user);
    $team = $user->personalTeam();

    $response = $this->actingAs($user)->get(route('dashboard', ['current_team' => $team->slug]));

    $response->assertOk();
});

it('allows verified approved users to access the dashboard', function () {
    $user = User::factory()->betaApproved()->create(['email' => 'verified@example.com']);
    $this->unlockVaultFor($user);
    $team = $user->personalTeam();

    $response = $this->actingAs($user)->get(route('dashboard', ['current_team' => $team->slug]));

    $response->assertOk();
});

it('redirects newly registered open beta users to the beta pending page', function () {
    config(['billing.mode' => 'open_beta']);

    $response = $this->post(route('register.store'), [
        'name' => 'Beta User',
        'email' => 'beta-user@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('beta.pending', absolute: false));
});

it('marks newly registered users as email verified', function () {
    $this->post(route('register.store'), [
        'name' => 'Verified User',
        'email' => 'verified-user@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'verified-user@example.com')->firstOrFail();

    expect($user->hasVerifiedEmail())->toBeTrue();
});
