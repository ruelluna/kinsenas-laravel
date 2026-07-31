<?php

use App\Enums\BillingInterval;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('lists subscribers for platform admin', function () {
    $admin = User::factory()->create(['is_platform_admin' => true]);
    User::factory()->create(['email' => 'member@example.com']);

    $response = $this->actingAs($admin)->get(route('admin.subscribers.index'));

    $response->assertSuccessful();
});

it('extends a subscriber trial', function () {
    $admin = User::factory()->create(['is_platform_admin' => true]);
    $member = User::factory()->create(['email' => 'member@example.com']);
    $subscription = $member->subscription;
    $originalEndsAt = $subscription->trial_ends_at->copy();

    $response = $this->actingAs($admin)->post(route('admin.subscribers.extend-trial', $member), [
        'days' => 7,
    ]);

    $response->assertRedirect();

    expect($subscription->fresh()->trial_ends_at->greaterThan($originalEndsAt))->toBeTrue();
});

it('cancels a subscriber subscription', function () {
    $admin = User::factory()->create(['is_platform_admin' => true]);
    $member = User::factory()->create(['email' => 'member@example.com']);

    $response = $this->actingAs($admin)->post(route('admin.subscribers.cancel', $member), [
        'reason' => 'Requested by user',
    ]);

    $response->assertRedirect();

    expect($member->subscription->fresh()->status)->toBe(SubscriptionStatus::Cancelled);
});

it('manually activates a subscriber', function () {
    $admin = User::factory()->create(['is_platform_admin' => true]);
    $member = User::factory()->create(['email' => 'member@example.com']);

    $response = $this->actingAs($admin)->post(route('admin.subscribers.activate', $member), [
        'interval' => BillingInterval::Monthly->value,
    ]);

    $response->assertRedirect();

    $subscription = $member->subscription->fresh();

    expect($subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($subscription->current_period_ends_at)->not->toBeNull();
});

it('changes a subscriber plan', function () {
    $admin = User::factory()->create(['is_platform_admin' => true]);
    $member = User::factory()->create(['email' => 'member@example.com']);

    $newPlan = SubscriptionPlan::factory()->create([
        'name' => 'Enterprise',
        'slug' => 'enterprise',
    ]);

    $response = $this->actingAs($admin)->post(route('admin.subscribers.change-plan', $member), [
        'plan_id' => $newPlan->id,
    ]);

    $response->assertRedirect();

    expect($member->subscription->fresh()->plan_id)->toBe($newPlan->id);
});

it('filters subscribers by status', function () {
    $admin = User::factory()->create(['is_platform_admin' => true]);
    $member = User::factory()->create(['email' => 'member@example.com']);
    $member->subscription->update(['status' => SubscriptionStatus::Cancelled]);

    $response = $this->actingAs($admin)->get(route('admin.subscribers.index', [
        'status' => SubscriptionStatus::Cancelled->value,
    ]));

    $response->assertSuccessful();
});
