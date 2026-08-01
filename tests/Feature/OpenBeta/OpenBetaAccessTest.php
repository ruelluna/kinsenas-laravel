<?php

use App\Actions\Teams\CreateTeam;
use App\Enums\SubscriptionStatus;
use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
    config(['billing.mode' => 'open_beta']);
});

it('grants access to teams with past due subscriptions during open beta when approved', function () {
    $user = User::factory()->betaApproved()->create(['email' => 'beta-access@example.com']);
    $this->unlockVaultFor($user);
    $sharedTeam = app(CreateTeam::class)->handle($user, 'Beta Shared Team', isPersonal: false);

    expect($sharedTeam->subscription->status)->toBe(SubscriptionStatus::OpenBeta);

    $response = $this->actingAs($user)->get(route('dashboard', ['current_team' => $sharedTeam->slug]));

    $response->assertOk();
});

it('redirects pending beta applicants from the dashboard', function () {
    $user = User::factory()->betaPending()->create(['email' => 'pending@example.com']);
    $team = $user->personalTeam();

    $response = $this->actingAs($user)->get(route('dashboard', ['current_team' => $team->slug]));

    $response->assertRedirect(route('beta.pending'));
});

it('creates shared teams with open beta subscription instead of past due', function () {
    $owner = User::factory()->betaApproved()->create(['email' => 'shared-beta@example.com']);

    $sharedTeam = app(CreateTeam::class)->handle($owner, 'Side Business', isPersonal: false);

    expect($sharedTeam->subscription->status)->toBe(SubscriptionStatus::OpenBeta)
        ->and($sharedTeam->subscription->trial_ends_at)->toBeNull();
});

it('blocks payment submissions during open beta', function () {
    $owner = User::factory()->betaApproved()->create(['email' => 'owner-beta@example.com']);
    app(CreateTeam::class)->handle($owner, 'Beta Team', isPersonal: false);

    $response = $this->actingAs($owner)->get(route('billing.pay'));

    $response->assertRedirect(route('settings.billing'));
    $response->assertSessionHas('error');
});

it('does not lock approved users out when switching to a past due team during open beta', function () {
    $user = User::factory()->betaApproved()->create(['email' => 'switch-beta@example.com']);
    $this->unlockVaultFor($user);
    $personalTeam = $user->personalTeam();
    $sharedTeam = Team::factory()->create(['name' => 'Locked Team']);
    $sharedTeam->members()->attach($user, ['role' => TeamRole::Owner->value]);
    $sharedTeam->subscription()->create([
        'plan_id' => $personalTeam->subscription->plan_id,
        'status' => SubscriptionStatus::PastDue,
        'trial_ends_at' => null,
        'current_period_ends_at' => null,
    ]);

    $user->switchTeam($sharedTeam);

    $response = $this->actingAs($user)->get(route('dashboard', ['current_team' => $sharedTeam->slug]));

    $response->assertOk();
});
