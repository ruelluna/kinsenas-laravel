<?php

use App\Models\User;
use App\Notifications\System\TestPushNotification;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('queues a test push for the current device when subscribed', function () {
    Notification::fake();

    $user = User::factory()->create();
    $user->updatePushSubscription(
        'https://fcm.googleapis.com/fcm/send/example-token',
        'public-key',
        'auth-token',
        'aes128gcm',
    );
    $user->notificationPreferences()->update(['push_enabled' => true]);

    $this->actingAs($user)
        ->post(route('settings.notifications.test-push'))
        ->assertRedirect(route('settings.notifications.edit'));

    Notification::assertSentTo($user, TestPushNotification::class);
});

it('rejects test push when the device is not subscribed', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('settings.notifications.test-push'))
        ->assertRedirect(route('settings.notifications.edit'))
        ->assertSessionHasErrors('push');

    Notification::assertNothingSent();
});

it('requires authentication to send a test push', function () {
    $this->post(route('settings.notifications.test-push'))
        ->assertRedirect(route('login'));
});
