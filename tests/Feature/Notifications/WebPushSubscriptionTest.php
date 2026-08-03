<?php

use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('stores a push subscription for the authenticated user', function () {
    $user = User::factory()->create();

    $endpoint = 'https://push.example.test/subscription/abc';

    $this->actingAs($user)
        ->postJson(route('settings.notifications.push-subscription.store'), [
            'endpoint' => $endpoint,
            'keys' => [
                'p256dh' => 'test-public-key',
                'auth' => 'test-auth-token',
            ],
            'contentEncoding' => 'aes128gcm',
        ])
        ->assertOk()
        ->assertJsonPath('pushEnabled', true);

    $this->assertDatabaseHas('push_subscriptions', [
        'endpoint' => $endpoint,
        'subscribable_type' => User::class,
        'subscribable_id' => $user->id,
        'content_encoding' => 'aes128gcm',
    ]);

    expect($user->notificationPreferences()->first()->push_enabled)->toBeTrue();
});

it('defaults content encoding to aes128gcm when omitted', function () {
    $user = User::factory()->create();

    $endpoint = 'https://fcm.googleapis.com/fcm/send/example-token';

    $this->actingAs($user)
        ->postJson(route('settings.notifications.push-subscription.store'), [
            'endpoint' => $endpoint,
            'keys' => [
                'p256dh' => 'test-public-key',
                'auth' => 'test-auth-token',
            ],
        ])
        ->assertOk();

    $this->assertDatabaseHas('push_subscriptions', [
        'endpoint' => $endpoint,
        'content_encoding' => 'aes128gcm',
    ]);
});

it('deletes a push subscription by endpoint', function () {
    $user = User::factory()->create();
    $endpoint = 'https://push.example.test/subscription/def';

    $user->updatePushSubscription($endpoint, 'public-key', 'auth-token', 'aes128gcm');
    $user->notificationPreferences()->update(['push_enabled' => true]);

    $this->actingAs($user)
        ->deleteJson(route('settings.notifications.push-subscription.destroy'), [
            'endpoint' => $endpoint,
        ])
        ->assertOk()
        ->assertJsonPath('pushSubscriptionCount', 0);

    $this->assertDatabaseMissing('push_subscriptions', [
        'endpoint' => $endpoint,
    ]);

    expect($user->notificationPreferences()->first()->push_enabled)->toBeFalse();
});
