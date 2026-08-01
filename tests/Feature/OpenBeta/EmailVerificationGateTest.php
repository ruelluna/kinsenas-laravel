<?php

use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('blocks unverified users from the dashboard', function () {
    $user = User::factory()->unverified()->create(['email' => 'unverified@example.com']);
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

it('redirects newly registered users to email verification', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Beta User',
        'email' => 'beta-user@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice'));
});
