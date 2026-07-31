<?php

use App\Enums\SubscriptionStatus;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('marks expired trialing subscriptions as past due', function () {
    $member = User::factory()->create(['email' => 'member@example.com']);
    $subscription = $member->subscription;

    $subscription->update([
        'status' => SubscriptionStatus::Trialing,
        'trial_ends_at' => now()->subDay(),
    ]);

    $this->artisan('billing:sync-subscription-status')->assertSuccessful();

    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::PastDue);
});

it('marks expired active subscriptions as past due', function () {
    $member = User::factory()->create(['email' => 'member@example.com']);
    $subscription = $member->subscription;

    $subscription->update([
        'status' => SubscriptionStatus::Active,
        'trial_ends_at' => null,
        'current_period_ends_at' => now()->subDay(),
    ]);

    $this->artisan('billing:sync-subscription-status')->assertSuccessful();

    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::PastDue);
});

it('leaves valid subscriptions unchanged', function () {
    $member = User::factory()->create(['email' => 'member@example.com']);
    $subscription = $member->subscription;

    $subscription->update([
        'status' => SubscriptionStatus::Trialing,
        'trial_ends_at' => now()->addDays(5),
    ]);

    $this->artisan('billing:sync-subscription-status')->assertSuccessful();

    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::Trialing);
});
