<?php

use App\Enums\NotificationKind;
use App\Models\User;
use App\Services\Notifications\NotificationPreferenceService;
use App\Services\Notifications\PushNotificationDiagnostics;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NotificationChannels\WebPush\WebPushChannel;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('reports missing push subscription in the user checklist', function () {
    $user = User::factory()->create();

    $checklist = app(PushNotificationDiagnostics::class)->checklistForUser($user);

    expect($checklist)->toContain('MISSING: no push_subscriptions row for this device');
});

it('does not include web push when push is disabled even with a subscription', function () {
    $user = User::factory()->create();
    $preferences = $user->notificationPreferences()->firstOrFail();
    $preferences->update([
        'push_enabled' => false,
        'push_team_invitations' => true,
    ]);
    $user->updatePushSubscription(
        'https://push.example.test/subscription/off',
        'key',
        'auth',
        'aes128gcm',
    );

    $channels = app(NotificationPreferenceService::class)
        ->channelsFor($user, NotificationKind::TeamInvitation);

    expect($channels)->not->toContain(WebPushChannel::class);
});

it('includes web push when subscribed and push is enabled', function () {
    $user = User::factory()->create();
    $preferences = $user->notificationPreferences()->firstOrFail();
    $preferences->update([
        'push_enabled' => true,
        'push_team_invitations' => true,
    ]);
    $user->updatePushSubscription(
        'https://push.example.test/subscription/on',
        'key',
        'auth',
        'aes128gcm',
    );

    $channels = app(NotificationPreferenceService::class)
        ->channelsFor($user, NotificationKind::TeamInvitation);

    expect($channels)->toContain(WebPushChannel::class);
});
