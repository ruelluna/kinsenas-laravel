<?php

use App\Enums\FinanceActivityTier;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('includes finance activity fields on platform users index', function () {
    $admin = User::factory()->platformAdmin()->create();
    $member = User::factory()->create(['email' => 'active@example.com']);

    $member->forceFill([
        'finance_activity_score' => 75,
        'finance_activity_tier' => FinanceActivityTier::Active->value,
        'last_finance_activity_at' => now(),
    ])->saveQuietly();

    $response = $this->actingAs($admin)->get(route('admin.platform-users.index', [
        'search' => 'active@example.com',
    ]));

    $response->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/platform-users/index')
            ->has('activityTierOptions')
            ->where('users.data.0.financeActivityScore', 75)
            ->where('users.data.0.financeActivityTier', FinanceActivityTier::Active->value));
});

it('filters platform users by activity tier', function () {
    $admin = User::factory()->platformAdmin()->create();
    $admin->forceFill([
        'finance_activity_score' => 90,
        'finance_activity_tier' => FinanceActivityTier::Activated->value,
    ])->saveQuietly();

    User::factory()->create([
        'email' => 'inactive@example.com',
    ])->forceFill([
        'finance_activity_score' => 10,
        'finance_activity_tier' => FinanceActivityTier::Inactive->value,
    ])->saveQuietly();

    User::factory()->create([
        'email' => 'active@example.com',
    ])->forceFill([
        'finance_activity_score' => 80,
        'finance_activity_tier' => FinanceActivityTier::Activated->value,
    ])->saveQuietly();

    $response = $this->actingAs($admin)->get(route('admin.platform-users.index', [
        'activity_tier' => FinanceActivityTier::Inactive->value,
    ]));

    $response->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('users.data', 1)
            ->where('users.data.0.email', 'inactive@example.com'));
});

it('filters platform users by minimum finance activity score', function () {
    $admin = User::factory()->platformAdmin()->create(['email' => 'admin-min-score@example.com']);
    $admin->forceFill([
        'finance_activity_score' => 90,
        'finance_activity_tier' => FinanceActivityTier::Activated->value,
    ])->saveQuietly();

    User::factory()->create([
        'email' => 'low@example.com',
    ])->forceFill([
        'finance_activity_score' => 40,
        'finance_activity_tier' => FinanceActivityTier::Partial->value,
    ])->saveQuietly();

    User::factory()->create([
        'email' => 'high@example.com',
    ])->forceFill([
        'finance_activity_score' => 65,
        'finance_activity_tier' => FinanceActivityTier::Active->value,
    ])->saveQuietly();

    $response = $this->actingAs($admin)->get(route('admin.platform-users.index', [
        'min_score' => 60,
    ]));

    $response->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('users.data', 2)
            ->where('users.data.0.email', 'admin-min-score@example.com')
            ->where('users.data.1.email', 'high@example.com'));
});

it('includes finance activity fields on subscribers index', function () {
    $admin = User::factory()->platformAdmin()->create();
    $member = User::factory()->create(['email' => 'member@example.com']);
    $team = $member->personalTeam();

    $team->forceFill([
        'finance_activity_score' => 55,
        'finance_activity_tier' => FinanceActivityTier::Partial->value,
        'last_finance_activity_at' => now(),
    ])->saveQuietly();

    $response = $this->actingAs($admin)->get(route('admin.subscribers.index', [
        'activity_tier' => FinanceActivityTier::Partial->value,
    ]));

    $response->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/subscribers/index')
            ->has('activityTierOptions')
            ->where('subscribers.data.0.financeActivityScore', 55)
            ->where('subscribers.data.0.financeActivityTier', FinanceActivityTier::Partial->value));
});

it('filters subscribers by finance activity tier', function () {
    $admin = User::factory()->platformAdmin()->create();
    $admin->personalTeam()->forceFill([
        'finance_activity_score' => 5,
        'finance_activity_tier' => FinanceActivityTier::Inactive->value,
    ])->saveQuietly();

    $inactiveMember = User::factory()->create(['email' => 'inactive-team@example.com']);
    $inactiveMember->personalTeam()->forceFill([
        'finance_activity_score' => 5,
        'finance_activity_tier' => FinanceActivityTier::Inactive->value,
    ])->saveQuietly();

    $activeMember = User::factory()->create(['email' => 'active-team@example.com']);
    $activeMember->personalTeam()->forceFill([
        'finance_activity_score' => 90,
        'finance_activity_tier' => FinanceActivityTier::Activated->value,
    ])->saveQuietly();

    $response = $this->actingAs($admin)->get(route('admin.subscribers.index', [
        'activity_tier' => FinanceActivityTier::Activated->value,
    ]));

    $response->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('subscribers.data', 1)
            ->where('subscribers.data.0.ownerEmail', 'active-team@example.com'));
});

it('updates cached user score when finance activity is recorded', function () {
    $user = User::factory()->create();
    $team = $user->personalTeam();

    $baselineScore = $user->fresh()->finance_activity_score;

    unlockVaultForUser($user);
    prepareTeamForInvites($user, $team);

    expect($user->fresh()->finance_activity_score)->toBeGreaterThan($baselineScore)
        ->and($team->fresh()->finance_activity_score)->toBeGreaterThan($baselineScore);
});
