<?php

use App\Enums\SubscriptionFeature;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('allows platform admin to create a subscription plan', function () {
    $admin = User::factory()->platformAdmin()->create();

    $response = $this->actingAs($admin)->post(route('admin.plans.store'), [
        'name' => 'Pro',
        'slug' => 'pro',
        'trial_days' => 7,
        'features' => [SubscriptionFeature::SavingsPlan->value, SubscriptionFeature::Reports->value],
        'sort_order' => 2,
        'is_active' => true,
        'prices' => [
            'monthly' => ['amount' => 49900, 'is_active' => true],
            'yearly' => ['amount' => 499000, 'is_active' => true],
        ],
    ]);

    $response->assertRedirect(route('admin.plans.index'));

    $plan = SubscriptionPlan::query()->where('slug', 'pro')->first();

    expect($plan)->not->toBeNull()
        ->and($plan->features)->toContain(SubscriptionFeature::Reports->value)
        ->and($plan->prices)->toHaveCount(2);
});

it('forbids non-admin from creating a subscription plan', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('admin.plans.store'), [
        'name' => 'Pro',
        'slug' => 'pro',
        'trial_days' => 7,
        'sort_order' => 2,
        'prices' => [
            'monthly' => ['amount' => 49900],
            'yearly' => ['amount' => 499000],
        ],
    ]);

    $response->assertForbidden();
});

it('allows platform admin to update a subscription plan', function () {
    $admin = User::factory()->platformAdmin()->create();
    $plan = SubscriptionPlan::query()->where('slug', 'basic')->firstOrFail();

    $response = $this->actingAs($admin)->put(route('admin.plans.update', $plan), [
        'name' => 'Basic Plus',
        'slug' => 'basic',
        'trial_days' => 21,
        'features' => [SubscriptionFeature::SavingsPlan->value],
        'sort_order' => 1,
        'is_active' => true,
        'prices' => [
            'monthly' => ['amount' => 39900, 'is_active' => true],
            'yearly' => ['amount' => 399000, 'is_active' => true],
        ],
    ]);

    $response->assertRedirect(route('admin.plans.index'));

    expect($plan->fresh())
        ->name->toBe('Basic Plus')
        ->trial_days->toBe(21);
});
