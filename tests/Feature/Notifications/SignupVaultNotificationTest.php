<?php

use App\Models\User;
use App\Notifications\Admin\NewUserRegistered;
use App\Notifications\Vault\RecoveryKeyIssued;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
    config([
        'signup.admin_notify.to' => 'admin-notify@example.com',
        'signup.admin_notify.cc' => 'admin-cc@example.com',
    ]);
});

it('emails the recovery key to the user after web registration', function () {
    Notification::fake();

    $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'test@example.com')->firstOrFail();

    Notification::assertSentTo($user, RecoveryKeyIssued::class, function (RecoveryKeyIssued $notification) use ($user) {
        $html = $notification->toMail($user)->render()->toHtml();

        return str_contains($html, $notification->recoveryKey)
            && str_contains($html, 'only time')
            && str_contains($html, 'Do not delete');
    });
});

it('notifies admins of a new signup without the recovery key after web registration', function () {
    Notification::fake();

    $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'test@example.com')->firstOrFail();
    $recoveryKey = session('registration.recovery_key');

    Notification::assertSentOnDemand(NewUserRegistered::class, function (NewUserRegistered $notification, array $channels, object $notifiable) use ($user, $recoveryKey) {
        expect($notifiable->routes['mail'] ?? null)->toBe('admin-notify@example.com');

        $mail = $notification->toMail($notifiable);
        $html = $mail->render()->toHtml();

        expect($html)
            ->toContain('Test User')
            ->toContain('test@example.com')
            ->toContain('not included in this email')
            ->not->toContain($recoveryKey);

        expect($mail->cc)->toContain(['admin-cc@example.com', null]);

        return $notification->user->is($user);
    });
});

it('emails the recovery key to the user after api registration', function () {
    Notification::fake();

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Api User',
        'email' => 'api@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'device_name' => 'test',
    ]);

    $response->assertCreated();

    $user = User::where('email', 'api@example.com')->firstOrFail();
    $recoveryKey = $response->json('recovery_key');

    Notification::assertSentTo($user, RecoveryKeyIssued::class, function (RecoveryKeyIssued $notification) use ($recoveryKey) {
        return $notification->recoveryKey === $recoveryKey;
    });
});

it('notifies admins of a new signup without the recovery key after api registration', function () {
    Notification::fake();

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Api User',
        'email' => 'api@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'device_name' => 'test',
    ]);

    $response->assertCreated();

    $user = User::where('email', 'api@example.com')->firstOrFail();
    $recoveryKey = $response->json('recovery_key');

    Notification::assertSentOnDemand(NewUserRegistered::class, function (NewUserRegistered $notification) use ($user, $recoveryKey) {
        $html = $notification->toMail($notification->user)->render()->toHtml();

        return $notification->user->is($user)
            && ! str_contains($html, $recoveryKey);
    });
});
