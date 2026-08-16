<?php

use App\Models\BetaFeedback;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
    config(['billing.mode' => 'open_beta']);
});

it('defaults empty category to general when submitting feedback', function () {
    $user = User::factory()->betaApproved()->create(['email' => 'default-category@example.com']);

    $response = $this->actingAs($user)->post(route('settings.feedback.store'), [
        'message' => 'The dashboard layout is clear.',
        'category' => '',
    ]);

    $response->assertRedirect(route('settings.feedback'));

    $this->assertDatabaseHas('beta_feedbacks', [
        'user_id' => $user->id,
        'message' => 'The dashboard layout is clear.',
        'category' => 'general',
    ]);
});

it('defaults missing category to general when submitting feedback', function () {
    $user = User::factory()->betaApproved()->create(['email' => 'missing-category@example.com']);

    $response = $this->actingAs($user)->post(route('settings.feedback.store'), [
        'message' => 'Would love CSV export.',
    ]);

    $response->assertRedirect(route('settings.feedback'));

    $this->assertDatabaseHas('beta_feedbacks', [
        'user_id' => $user->id,
        'message' => 'Would love CSV export.',
        'category' => 'general',
    ]);
});

it('syncs beta feedback tags to GHL when category is omitted', function () {
    fakeGhlApi();

    $user = User::factory()->betaApproved()->create([
        'email' => 'ghl-feedback@example.com',
        'name' => 'GHL Feedback User',
    ]);

    $this->actingAs($user)->post(route('settings.feedback.store'), [
        'message' => 'Sync this feedback to GHL.',
    ])->assertRedirect(route('settings.feedback'));

    Http::assertSent(function ($request) {
        if ($request->method() !== 'POST' || ! str_ends_with($request->url(), '/contacts/ct_test_123/tags')) {
            return false;
        }

        $tags = collect($request->data()['tags'] ?? []);

        return $tags->contains('beta-feedback') && $tags->contains('beta-feedback-general');
    });
});

it('stores beta feedback from approved verified users', function () {
    $user = User::factory()->betaApproved()->create(['email' => 'feedback@example.com']);

    $response = $this->actingAs($user)->post(route('settings.feedback.store'), [
        'message' => 'The transfers page is very helpful.',
        'category' => 'general',
    ]);

    $response->assertRedirect(route('settings.feedback'));

    $this->assertDatabaseHas('beta_feedbacks', [
        'user_id' => $user->id,
        'message' => 'The transfers page is very helpful.',
        'category' => 'general',
    ]);
});

it('shows submitted feedback in the admin inbox', function () {
    $admin = User::factory()->create(['email' => 'admin@example.com', 'is_platform_admin' => true]);
    $user = User::factory()->betaApproved()->create(['email' => 'member@example.com']);

    BetaFeedback::factory()->create([
        'user_id' => $user->id,
        'team_id' => $user->personalTeam()->id,
        'message' => 'Please add export to CSV.',
        'category' => 'feature',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.beta-feedback.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/beta-feedback/index')
        ->has('feedbacks', 1)
        ->where('feedbacks.0.message', 'Please add export to CSV.')
        ->where('feedbacks.0.userEmail', 'member@example.com'),
    );
});

it('rejects feedback when not in open beta mode', function () {
    config(['billing.mode' => 'live']);
    $user = User::factory()->create(['email' => 'live-feedback@example.com']);

    $response = $this->actingAs($user)->post(route('settings.feedback.store'), [
        'message' => 'Should not save.',
    ]);

    $response->assertForbidden();
});

it('blocks non-participants from submitting feedback during open beta', function () {
    $user = User::factory()->create(['email' => 'non-participant-feedback@example.com']);

    $response = $this->actingAs($user)->post(route('settings.feedback.store'), [
        'message' => 'Should not save.',
    ]);

    $response->assertForbidden();
});
