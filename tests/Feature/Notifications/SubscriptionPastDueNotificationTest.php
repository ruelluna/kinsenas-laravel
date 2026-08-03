<?php

use App\Enums\SubscriptionStatus;
use App\Enums\TeamRole;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\User;
use App\Notifications\Billing\SubscriptionPastDue;
use App\Services\Billing\SubscriptionService;
use App\Services\Notifications\SubscriptionNotificationService;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('notifies billing managers when a subscription becomes past due', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $subscription = Subscription::factory()->create([
        'team_id' => $team->id,
        'status' => SubscriptionStatus::Trialing,
        'trial_ends_at' => now()->subDay(),
    ]);

    app(SubscriptionService::class)->syncExpiredStatus($subscription);

    Notification::assertSentTo($owner, SubscriptionPastDue::class);
});

it('deduplicates past due notifications by subscription id', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $subscription = Subscription::factory()->create([
        'team_id' => $team->id,
        'status' => SubscriptionStatus::PastDue,
    ]);

    app(SubscriptionNotificationService::class)
        ->notifyPastDue($subscription);
    app(SubscriptionNotificationService::class)
        ->notifyPastDue($subscription);

    expect($owner->notifications()->count())->toBe(1);
});
