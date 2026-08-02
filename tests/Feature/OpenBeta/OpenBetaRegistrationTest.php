<?php

use App\Enums\BetaApplicationStatus;
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

it('redirects pending applicants to the beta pending page after login', function () {
    $user = User::factory()->betaPending()->create(['email' => 'pending-verify@example.com']);

    $response = $this->post(route('login.store'), [
        'email' => 'pending-verify@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('beta.pending', absolute: false));
});

it('grants launch discount eligibility after admin approval', function () {
    $admin = User::factory()->create(['email' => 'admin@example.com', 'is_platform_admin' => true]);
    $user = User::factory()->betaPending()->create(['email' => 'discount@example.com']);

    $this->actingAs($admin)->post(route('admin.beta-applications.approve', $user));

    expect($user->fresh()->beta_application_status)->toBe(BetaApplicationStatus::Approved)
        ->and($user->fresh()->beta_launch_discount_eligible)->toBeTrue();
});

it('upserts a GHL contact when beta application is submitted', function () {
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
            && $tags->contains('kinsenas-beta')
            && $tags->contains('beta-pending');
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

it('upserts a GHL contact with approved tags when admin approves', function () {
    fakeGhlApi();

    $admin = User::factory()->create(['email' => 'admin-ghl@example.com', 'is_platform_admin' => true]);
    $applicant = User::factory()->betaPending()->create([
        'name' => 'Approve Me',
        'email' => 'approve-ghl@example.com',
    ]);

    $this->actingAs($admin)->post(route('admin.beta-applications.approve', $applicant));

    Http::assertSent(function ($request) {
        $data = $request->data();

        return $request->method() === 'POST'
            && $request->url() === 'https://services.leadconnectorhq.com/contacts/upsert'
            && ($data['email'] ?? null) === 'approve-ghl@example.com'
            && ! array_key_exists('tags', $data);
    });

    Http::assertSent(function ($request) {
        $data = $request->data();
        $tags = collect($data['tags'] ?? []);

        return $request->method() === 'POST'
            && str_ends_with($request->url(), '/contacts/ct_test_123/tags')
            && $tags->contains('kinsenas-beta')
            && $tags->contains('beta-approved');
    });

    Http::assertSent(function ($request) {
        $data = $request->data();
        $tags = collect($data['tags'] ?? []);

        return $request->method() === 'DELETE'
            && str_ends_with($request->url(), '/contacts/ct_test_123/tags')
            && $tags->contains('beta-pending')
            && $tags->contains('beta-rejected');
    });
});

it('allows admin to approve a pending beta application', function () {
    $admin = User::factory()->create(['email' => 'admin-approve@example.com', 'is_platform_admin' => true]);
    $applicant = User::factory()->betaPending()->create(['email' => 'applicant@example.com']);

    $response = $this->actingAs($admin)->post(route('admin.beta-applications.approve', $applicant));

    $response->assertRedirect();
    expect($applicant->fresh()->beta_application_status)->toBe(BetaApplicationStatus::Approved);
});
