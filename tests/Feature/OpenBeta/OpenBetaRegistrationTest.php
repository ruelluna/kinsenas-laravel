<?php

use App\Enums\BetaApplicationStatus;
use App\Enums\SubscriptionStatus;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
    config(['billing.mode' => 'open_beta']);
});

it('registration screen shows open beta application offer instead of trial offer', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('auth/register')
        ->where('trialOffer', null)
        ->has('openBetaOffer', fn (Assert $offer) => $offer
            ->where('launchDiscountPercent', 20),
        ),
    );
});

it('creates a pending beta application on registration', function () {
    Queue::fake();

    $this->post(route('register.store'), [
        'name' => 'Open Beta User',
        'email' => 'open-beta@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'open-beta@example.com')->firstOrFail();

    expect($user->beta_enrolled_at)->not->toBeNull()
        ->and($user->beta_application_status)->toBe(BetaApplicationStatus::Pending)
        ->and($user->personalTeam()->subscription->status)->toBe(SubscriptionStatus::OpenBeta);
});

it('redirects verified pending applicants to the beta pending page', function () {
    $user = User::factory()->betaPending()->create(['email' => 'pending-verify@example.com']);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $this->actingAs($user)->get($verificationUrl)
        ->assertRedirect(route('beta.pending', absolute: false).'?verified=1');
});

it('grants launch discount eligibility after admin approval and email verification', function () {
    $admin = User::factory()->create(['email' => 'admin@example.com', 'is_platform_admin' => true]);
    $user = User::factory()->unverified()->betaPending()->create(['email' => 'discount@example.com']);

    $this->actingAs($admin)->post(route('admin.beta-applications.approve', $user));

    expect($user->fresh()->beta_application_status)->toBe(BetaApplicationStatus::Approved);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->beta_launch_discount_eligible)->toBeTrue();
});

it('dispatches GHL webhook when beta application is submitted', function () {
    config([
        'services.ghl.enabled' => true,
        'services.ghl.webhook_application_url' => 'https://hooks.example.test/ghl/application',
    ]);

    Http::fake([
        'hooks.example.test/*' => Http::response(['ok' => true], 200),
    ]);

    $this->post(route('register.store'), [
        'name' => 'Webhook User',
        'email' => 'webhook@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    Http::assertSent(fn ($request) => $request->url() === 'https://hooks.example.test/ghl/application'
        && $request['event'] === 'application_submitted'
        && $request['user']['email'] === 'webhook@example.com');
});

it('allows admin to approve a pending beta application', function () {
    $admin = User::factory()->create(['email' => 'admin-approve@example.com', 'is_platform_admin' => true]);
    $applicant = User::factory()->betaPending()->create(['email' => 'applicant@example.com']);

    $response = $this->actingAs($admin)->post(route('admin.beta-applications.approve', $applicant));

    $response->assertRedirect();
    expect($applicant->fresh()->beta_application_status)->toBe(BetaApplicationStatus::Approved);
});
