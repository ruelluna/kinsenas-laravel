<?php

use App\Enums\SubscriptionFeature;
use App\Enums\SubscriptionStatus;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Billing\SubscriptionService;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('grants platform admins access regardless of subscription', function () {
    $admin = User::factory()->platformAdmin()->create();
    $admin->personalTeam()->subscription->update([
        'status' => SubscriptionStatus::Cancelled,
    ]);

    expect(app(SubscriptionService::class)->userHasAccess($admin))->toBeTrue();
});

it('checks subscription features for members', function () {
    $member = User::factory()->create(['email' => 'member@example.com']);
    $plan = SubscriptionPlan::query()->where('slug', 'basic')->firstOrFail();

    $plan->update([
        'features' => [SubscriptionFeature::SavingsPlan->value],
    ]);

    $member->refresh();

    $service = app(SubscriptionService::class);

    expect($service->userHasFeature($member, SubscriptionFeature::SavingsPlan))->toBeTrue()
        ->and($service->userHasFeature($member, SubscriptionFeature::Reports))->toBeFalse();
});
