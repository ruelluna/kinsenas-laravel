<?php

use App\Actions\Teams\CreateTeam;
use App\Enums\SubscriptionStatus;
use App\Enums\TeamRole;
use App\Models\SubscriptionPlanPrice;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
    Storage::fake('public');
});

it('creates a shared team with past due subscription and no trial', function () {
    $owner = User::factory()->create(['email' => 'owner@example.com']);

    $sharedTeam = app(CreateTeam::class)->handle($owner, 'Side Business', isPersonal: false);

    expect($sharedTeam->subscription)->not->toBeNull()
        ->and($sharedTeam->subscription->status)->toBe(SubscriptionStatus::PastDue)
        ->and($sharedTeam->subscription->trial_ends_at)->toBeNull();
});

it('allows team owner to submit payment for current team', function () {
    $owner = User::factory()->create(['email' => 'owner-pay@example.com']);
    $sharedTeam = app(CreateTeam::class)->handle($owner, 'Shared Team', isPersonal: false);
    $planPrice = SubscriptionPlanPrice::query()->firstOrFail();

    $response = $this->actingAs($owner)->post(route('billing.pay.store'), [
        'plan_price_id' => $planPrice->id,
        'reference_number' => 'REF-123456',
        'proof_image' => UploadedFile::fake()->image('proof.jpg'),
    ]);

    $response->assertRedirect(route('settings.billing'));

    $this->assertDatabaseHas('payment_submissions', [
        'user_id' => $owner->id,
        'team_id' => $sharedTeam->id,
        'reference_number' => 'REF-123456',
    ]);
});

it('forbids non-owner team members from submitting payment', function () {
    $owner = User::factory()->create(['email' => 'owner2@example.com']);
    $member = User::factory()->create(['email' => 'member@example.com']);
    $sharedTeam = app(CreateTeam::class)->handle($owner, 'Shared Team Two', isPersonal: false);

    $sharedTeam->members()->attach($member, ['role' => TeamRole::Member->value]);
    $member->switchTeam($sharedTeam);

    $planPrice = SubscriptionPlanPrice::query()->firstOrFail();

    $response = $this->actingAs($member)->post(route('billing.pay.store'), [
        'plan_price_id' => $planPrice->id,
        'reference_number' => 'REF-MEMBER',
        'proof_image' => UploadedFile::fake()->image('proof.jpg'),
    ]);

    $response->assertForbidden();
});

it('redirects owner to billing after creating unpaid shared team', function () {
    $owner = User::factory()->create(['email' => 'creator@example.com']);

    $response = $this->actingAs($owner)->post(route('teams.store'), [
        'name' => 'New Shared Team',
    ]);

    $response->assertRedirect(route('settings.billing'));
});
