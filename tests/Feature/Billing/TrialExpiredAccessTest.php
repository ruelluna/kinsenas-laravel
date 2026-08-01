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
});

function expiredTrialMember(): User
{
    $member = User::factory()->create(['email' => 'expired@example.com']);
    $personalTeam = $member->personalTeam();

    $personalTeam->subscription->update([
        'trial_ends_at' => now()->subDay(),
    ]);

    return $member->fresh(['currentTeam.subscription.plan']);
}

it('redirects expired trial users from dashboard to billing', function () {
    $member = expiredTrialMember();
    $team = $member->currentTeam;

    $response = $this->actingAs($member)->get(route('dashboard', $team));

    $response->assertRedirect(route('settings.billing'));
});

it('allows expired trial users to access profile and teams list', function () {
    $member = expiredTrialMember();

    $this->actingAs($member)->get(route('profile.edit'))->assertOk();
    $this->actingAs($member)->get(route('teams.index'))->assertOk();
});

it('redirects expired trial users from vault unlock to billing', function () {
    $member = expiredTrialMember();

    $response = $this->actingAs($member)->get(route('vault.unlock'));

    $response->assertRedirect(route('settings.billing'));
});

it('allows expired trial users to access billing pages', function () {
    $member = expiredTrialMember();

    $this->actingAs($member)
        ->get(route('settings.billing'))
        ->assertOk();

    $this->actingAs($member)
        ->get(route('billing.pay'))
        ->assertOk();
});

it('redirects expired trial users to billing after login', function () {
    $member = expiredTrialMember();

    $response = $this->post(route('login'), [
        'email' => $member->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('settings.billing'));
});

it('allows access to an active team when personal team trial expired', function () {
    $owner = expiredTrialMember();

    $sharedTeam = app(CreateTeam::class)->handle($owner, 'Family Budget', isPersonal: false);
    $sharedTeam->subscription->update([
        'status' => SubscriptionStatus::Active,
        'trial_ends_at' => null,
        'current_period_ends_at' => now()->addMonth(),
    ]);

    $owner->switchTeam($sharedTeam);
    $this->unlockVaultFor($owner);

    $this->actingAs($owner)
        ->get(route('dashboard', $sharedTeam))
        ->assertOk();
});
