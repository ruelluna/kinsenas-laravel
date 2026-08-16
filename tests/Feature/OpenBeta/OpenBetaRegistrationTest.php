<?php

use App\Enums\SubscriptionStatus;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
    config(['billing.mode' => 'open_beta']);
});

it('registration screen shows open beta offer instead of trial offer', function () {
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

it('enrolls beta participants on registration', function () {
    Queue::fake();

    $this->post(route('register.store'), [
        'name' => 'Open Beta User',
        'email' => 'open-beta@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'open-beta@example.com')->firstOrFail();

    expect($user->beta_enrolled_at)->not->toBeNull()
        ->and($user->beta_launch_discount_eligible)->toBeTrue()
        ->and($user->personalTeam()->subscription->status)->toBe(SubscriptionStatus::OpenBeta)
        ->and($user->marketing_emails_opt_in)->toBeFalse();
});

it('stores marketing email opt-in on open beta registration', function () {
    Queue::fake();

    $this->post(route('register.store'), [
        'name' => 'Beta Opt In',
        'email' => 'beta-opt-in@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'marketing_emails_opt_in' => '1',
    ]);

    $user = User::where('email', 'beta-opt-in@example.com')->firstOrFail();

    expect($user->marketing_emails_opt_in)->toBeTrue()
        ->and($user->marketing_emails_opted_in_at)->not->toBeNull();
});

it('redirects unverified beta participants to email verification after login', function () {
    $user = User::factory()->unverified()->betaParticipant()->create(['email' => 'pending-verify@example.com']);

    $response = $this->post(route('login.store'), [
        'email' => 'pending-verify@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('verification.notice', absolute: false));
});

it('upserts a GHL contact when a beta participant registers', function () {
    fakeGhlApi();

    $this->post(route('register.store'), [
        'name' => 'Ghl Beta User',
        'email' => 'ghl-beta@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    Http::assertSent(function ($request) {
        $data = $request->data();

        return $request->method() === 'POST'
            && $request->url() === 'https://services.leadconnectorhq.com/contacts/upsert'
            && $request->hasHeader('Authorization', 'Bearer test-pit-token')
            && $request->hasHeader('Version', '2021-07-28')
            && ($data['locationId'] ?? null) === 'loc_test_123'
            && ($data['email'] ?? null) === 'ghl-beta@example.com'
            && ($data['firstName'] ?? null) === 'Ghl'
            && ($data['lastName'] ?? null) === 'Beta User'
            && ! array_key_exists('tags', $data)
            && ! array_key_exists('customFields', $data);
    });

    Http::assertSent(function ($request) {
        $data = $request->data();
        $tags = collect($data['tags'] ?? []);

        return $request->method() === 'POST'
            && str_ends_with($request->url(), '/contacts/ct_test_123/tags')
            && $tags->contains('kinsenas-beta');
    });

    Http::assertSent(function ($request) {
        $data = $request->data();
        $tags = collect($data['tags'] ?? []);

        return $request->method() === 'POST'
            && str_ends_with($request->url(), '/contacts/ct_test_123/tags')
            && $tags->contains('kinsenas-user')
            && $tags->contains('registered');
    });
});

it('does not call GHL when enabled but PIT is missing', function () {
    config([
        'services.ghl.enabled' => true,
        'services.ghl.pit' => '',
        'services.ghl.location_id' => 'loc_test_123',
    ]);

    Http::fake();

    $this->post(route('register.store'), [
        'name' => 'No Pit User',
        'email' => 'no-pit@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    Http::assertNothingSent();
});
