<?php

use App\Enums\BillingInterval;
use App\Enums\SubscriptionStatus;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('lists subscribers for platform admin', function () {
    $admin = User::factory()->platformAdmin()->create();
    User::factory()->create(['email' => 'member@example.com']);

    $response = $this->actingAs($admin)->get(route('admin.subscribers.index'));

    $response->assertSuccessful();
});

it('extends a subscriber trial', function () {
    $admin = User::factory()->platformAdmin()->create();
    $member = User::factory()->create(['email' => 'member@example.com']);
    $team = $member->personalTeam();
    $subscription = $team->subscription;
    $originalEndsAt = $subscription->trial_ends_at->copy();

    $response = $this->actingAs($admin)->post(route('admin.subscribers.extend-trial', $team), [
        'days' => 7,
    ]);

    $response->assertRedirect();

    expect($subscription->fresh()->trial_ends_at->greaterThan($originalEndsAt))->toBeTrue();
});

it('cancels a subscriber subscription', function () {
    $admin = User::factory()->platformAdmin()->create();
    $member = User::factory()->create(['email' => 'member@example.com']);
    $team = $member->personalTeam();

    $response = $this->actingAs($admin)->post(route('admin.subscribers.cancel', $team), [
        'reason' => 'Requested by user',
    ]);

    $response->assertRedirect();

    expect($team->subscription->fresh()->status)->toBe(SubscriptionStatus::Cancelled);
});

it('manually activates a subscriber', function () {
    $admin = User::factory()->platformAdmin()->create();
    $member = User::factory()->create(['email' => 'member@example.com']);
    $team = $member->personalTeam();

    $response = $this->actingAs($admin)->post(route('admin.subscribers.activate', $team), [
        'interval' => BillingInterval::Monthly->value,
    ]);

    $response->assertRedirect();

    $subscription = $team->subscription->fresh();

    expect($subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($subscription->current_period_ends_at)->not->toBeNull();
});

it('changes a subscriber plan', function () {
    $admin = User::factory()->platformAdmin()->create();
    $member = User::factory()->create(['email' => 'member@example.com']);
    $team = $member->personalTeam();

    $newPlan = SubscriptionPlan::factory()->create([
        'name' => 'Enterprise',
        'slug' => 'enterprise',
    ]);

    $response = $this->actingAs($admin)->post(route('admin.subscribers.change-plan', $team), [
        'plan_id' => $newPlan->id,
    ]);

    $response->assertRedirect();

    expect($team->subscription->fresh()->plan_id)->toBe($newPlan->id);
});

it('filters subscribers by status', function () {
    $admin = User::factory()->platformAdmin()->create();
    $member = User::factory()->create(['email' => 'member@example.com']);
    $member->personalTeam()->subscription->update(['status' => SubscriptionStatus::Cancelled]);

    $response = $this->actingAs($admin)->get(route('admin.subscribers.index', [
        'status' => SubscriptionStatus::Cancelled->value,
    ]));

    $response->assertSuccessful();
});
