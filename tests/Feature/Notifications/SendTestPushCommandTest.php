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

it('allows platform admins to send a test push to themselves', function () {
    Notification::fake();

    $admin = User::factory()->platformAdmin()->create([
        'email' => 'admin@example.com',
    ]);

    $this->actingAs($admin)
        ->artisan('notifications:send-test-push')
        ->assertSuccessful();

    Notification::assertSentTo($admin, TestPushNotification::class);
});

it('forbids non-admin users from sending test push', function () {
    $member = User::factory()->create();

    $this->actingAs($member)
        ->artisan('notifications:send-test-push')
        ->assertFailed();
});

it('requires confirmation before sending to all subscribers', function () {
    Notification::fake();

    $admin = User::factory()->platformAdmin()->create();

    $this->actingAs($admin)
        ->artisan('notifications:send-test-push', ['--all' => true])
        ->expectsConfirmation('Send test push to all users with an active push subscription?', 'no')
        ->assertSuccessful();

    Notification::assertNothingSent();
});
