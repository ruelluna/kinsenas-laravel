<?php

use App\Enums\BetaAccessCodeType;
use App\Enums\BetaApplicationStatus;
use App\Models\BetaAccessCode;
use App\Models\BetaAccessCodeBatch;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
    config(['billing.mode' => 'open_beta']);
});

it('auto-approves registration when a valid shared beta access code is provided', function () {
    Queue::fake();

    $admin = User::factory()->create(['is_platform_admin' => true]);
    $accessCode = BetaAccessCode::factory()->create([
        'code' => 'KINSENAS-MNL-2026',
        'label' => 'Manila Finance Expo 2026',
        'created_by' => $admin->id,
    ]);

    $this->post(route('register.store'), [
        'name' => 'Event Attendee',
        'email' => 'event-attendee@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'beta_code' => 'kinsenas-mnl-2026',
    ]);

    $user = User::where('email', 'event-attendee@example.com')->firstOrFail();

    expect($user->beta_application_status)->toBe(BetaApplicationStatus::Approved)
        ->and($user->beta_access_code_id)->toBe($accessCode->id)
        ->and($user->beta_approved_by)->toBeNull()
        ->and($accessCode->fresh()->redemptions_count)->toBe(1);
});

it('keeps manual review pending when no beta access code is provided', function () {
    Queue::fake();

    $this->post(route('register.store'), [
        'name' => 'Organic Beta User',
        'email' => 'organic-beta@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'organic-beta@example.com')->firstOrFail();

    expect($user->beta_application_status)->toBe(BetaApplicationStatus::Pending)
        ->and($user->beta_access_code_id)->toBeNull();
});

it('rejects registration when the beta access code is invalid', function () {
    $this->post(route('register.store'), [
        'name' => 'Invalid Code User',
        'email' => 'invalid-code@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'beta_code' => 'NOT-A-REAL-CODE',
    ])->assertSessionHasErrors('beta_code');

    expect(User::where('email', 'invalid-code@example.com')->exists())->toBeFalse();
});

it('rejects registration when the beta access code is expired', function () {
    $admin = User::factory()->create(['is_platform_admin' => true]);

    BetaAccessCode::factory()->expired()->create([
        'code' => 'KINSENAS-OLD-2026',
        'created_by' => $admin->id,
    ]);

    $this->post(route('register.store'), [
        'name' => 'Expired Code User',
        'email' => 'expired-code@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'beta_code' => 'KINSENAS-OLD-2026',
    ])->assertSessionHasErrors('beta_code');
});

it('rejects registration when a single-use beta access code has already been redeemed', function () {
    $admin = User::factory()->create(['is_platform_admin' => true]);

    BetaAccessCode::factory()->singleUse()->maxedOut()->create([
        'code' => 'USED-CODE-1234',
        'created_by' => $admin->id,
    ]);

    $this->post(route('register.store'), [
        'name' => 'Late Attendee',
        'email' => 'late-attendee@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'beta_code' => 'USED-CODE-1234',
    ])->assertSessionHasErrors('beta_code');
});

it('prefills beta access code context from the register query string', function () {
    $admin = User::factory()->create(['is_platform_admin' => true]);

    BetaAccessCode::factory()->create([
        'code' => 'KINSENAS-QR-2026',
        'label' => 'QR Event',
        'created_by' => $admin->id,
    ]);

    $response = $this->get(route('register', ['beta_code' => 'KINSENAS-QR-2026']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('auth/register')
        ->where('betaCode', 'KINSENAS-QR-2026')
        ->where('betaCodeLabel', 'QR Event'),
    );
});

it('blocks dashboard access for code-approved users until email is verified', function () {
    $user = User::factory()->unverified()->betaApproved()->create([
        'email' => 'code-unverified@example.com',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard', [
        'current_team' => $user->personalTeam()->slug,
    ]));

    $response->assertRedirect(route('verification.notice'));
});

it('allows platform admins to create shared beta access codes', function () {
    $admin = User::factory()->create(['is_platform_admin' => true]);

    $response = $this->actingAs($admin)->post(route('admin.beta-access-codes.store'), [
        'code' => 'KINSENAS-ADMIN-2026',
        'label' => 'Admin Created Event',
        'max_uses' => 100,
    ]);

    $response->assertRedirect(route('admin.beta-access-codes.index'));

    $this->assertDatabaseHas('beta_access_codes', [
        'code' => 'KINSENAS-ADMIN-2026',
        'label' => 'Admin Created Event',
        'max_uses' => 100,
    ]);
});

it('allows platform admins to generate single-use beta access code batches', function () {
    $admin = User::factory()->create(['is_platform_admin' => true]);

    $response = $this->actingAs($admin)->post(route('admin.beta-access-codes.batches.store'), [
        'name' => 'Card batch 1',
        'quantity' => 5,
    ]);

    $response->assertRedirect(route('admin.beta-access-codes.index'));

    $batch = BetaAccessCodeBatch::query()->firstOrFail();

    expect($batch->quantity)->toBe(5)
        ->and(BetaAccessCode::query()->where('batch_id', $batch->id)->count())->toBe(5);
});

it('exports a beta access code batch as csv', function () {
    $admin = User::factory()->create(['is_platform_admin' => true]);
    $batch = BetaAccessCodeBatch::factory()->create([
        'created_by' => $admin->id,
        'quantity' => 2,
    ]);

    BetaAccessCode::factory()
        ->count(2)
        ->sequence(
            ['code' => 'AAAA-BBBB'],
            ['code' => 'CCCC-DDDD'],
        )
        ->create([
            'batch_id' => $batch->id,
            'type' => BetaAccessCodeType::SingleUse,
            'max_uses' => 1,
            'created_by' => $admin->id,
        ]);

    $response = $this->actingAs($admin)->get(route('admin.beta-access-codes.batches.export', $batch));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
});
