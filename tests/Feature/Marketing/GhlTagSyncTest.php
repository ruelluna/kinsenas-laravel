<?php

use App\Models\User;
use App\Services\Marketing\GhlMarketingService;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
    config(['billing.mode' => 'open_beta']);
});

it('adds and removes beta tags without sending tags on upsert', function () {
    fakeGhlApi();

    $user = User::factory()->create([
        'email' => 'mutate@example.com',
        'name' => 'Mutate User',
    ]);

    app(GhlMarketingService::class)->syncApplicationEvent($user, 'application_approved');

    Http::assertSent(function ($request) {
        return $request->method() === 'POST'
            && $request->url() === 'https://services.leadconnectorhq.com/contacts/upsert'
            && ! array_key_exists('tags', $request->data());
    });

    Http::assertSent(function ($request) {
        $tags = collect($request->data()['tags'] ?? []);

        return $request->method() === 'POST'
            && str_ends_with($request->url(), '/contacts/ct_test_123/tags')
            && $tags->contains('beta-approved');
    });

    Http::assertSent(function ($request) {
        $tags = collect($request->data()['tags'] ?? []);

        return $request->method() === 'DELETE'
            && str_ends_with($request->url(), '/contacts/ct_test_123/tags')
            && $tags->contains('beta-pending');
    });
});

it('does not call GHL when sync is disabled', function () {
    config([
        'services.ghl.enabled' => false,
        'services.ghl.pit' => 'test-pit-token',
        'services.ghl.location_id' => 'loc_test_123',
    ]);

    Http::fake();

    $user = User::factory()->create(['email' => 'disabled@example.com']);

    app(GhlMarketingService::class)->syncUserTags($user, ['registered'], [], ['event' => 'registered']);

    Http::assertNothingSent();
});

it('preserves survey and beta tags as separate add calls', function () {
    fakeGhlApi();

    $user = User::factory()->create([
        'email' => 'both@example.com',
        'name' => 'Both Tags',
    ]);

    app(GhlMarketingService::class)->syncApplicationEvent($user, 'application_submitted');

    $this->postJson(route('survey.responses.store'), [
        'language' => 'en',
        'email' => 'both@example.com',
        'name' => 'Both Tags',
        'result' => 'family-first-planner',
        'completed_at' => now()->toIso8601String(),
        'answers' => [
            'q1' => 'employee',
            'q2' => 'single',
            'q3' => '2-3',
            'q4' => 'send_family',
            'q5' => ['bills'],
            'q6' => 'manual',
            'q7' => 'forgetting_transfers',
            'q9' => 'track_transfers',
            'q10' => 'early_access',
        ],
    ])->assertCreated();

    Http::assertSentCount(4);

    Http::assertSent(function ($request) {
        if ($request->method() !== 'POST' || ! str_ends_with($request->url(), '/contacts/ct_test_123/tags')) {
            return false;
        }

        $tags = collect($request->data()['tags'] ?? []);

        return $tags->contains('beta-pending');
    });

    Http::assertSent(function ($request) {
        if ($request->method() !== 'POST' || ! str_ends_with($request->url(), '/contacts/ct_test_123/tags')) {
            return false;
        }

        $tags = collect($request->data()['tags'] ?? []);

        return $tags->contains('survey-completed');
    });
});
