<?php

use App\Models\BetaFeedback;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
    config(['billing.mode' => 'open_beta']);
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

it('blocks pending beta applicants from submitting feedback', function () {
    $user = User::factory()->betaPending()->create(['email' => 'pending-feedback@example.com']);

    $response = $this->actingAs($user)->post(route('settings.feedback.store'), [
        'message' => 'Should not save.',
    ]);

    $response->assertRedirect(route('beta.pending'));
});
