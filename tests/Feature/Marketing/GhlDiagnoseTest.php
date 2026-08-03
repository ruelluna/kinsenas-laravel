<?php

use App\Models\User;
use App\Services\Marketing\GhlMarketingService;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('reports ghl disabled reason', function () {
    config([
        'services.ghl.enabled' => false,
        'services.ghl.pit' => '',
        'services.ghl.location_id' => '',
    ]);

    $this->artisan('ghl:diagnose')
        ->expectsOutputToContain('enabled=false')
        ->assertSuccessful();
});

it('runs upsert smoke test when ghl is enabled', function () {
    fakeGhlApi();

    $this->artisan('ghl:diagnose', ['--upsert' => 'diagnose@example.com'])
        ->expectsOutputToContain('Upsert OK')
        ->assertSuccessful();

    Http::assertSent(function ($request) {
        return $request->method() === 'POST'
            && str_contains($request->url(), '/contacts/upsert')
            && ($request->data()['email'] ?? null) === 'diagnose@example.com';
    });
});

it('logs when sync is skipped due to disabled config', function () {
    config([
        'services.ghl.enabled' => false,
        'services.ghl.pit' => 'test-pit-token',
        'services.ghl.location_id' => 'loc_test_123',
    ]);

    Queue::fake();

    $user = User::factory()->create(['email' => 'log@example.com']);

    Http::fake();
    Log::spy();

    app(GhlMarketingService::class)->syncUserTags($user, ['registered'], [], ['event' => 'registered']);

    Log::shouldHaveReceived('info')
        ->once()
        ->with('GHL sync skipped', Mockery::on(fn (array $context) => ($context['reason'] ?? null) === 'enabled=false'));

    Http::assertNothingSent();
});
