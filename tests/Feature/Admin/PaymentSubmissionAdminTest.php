<?php

use App\Enums\BillingInterval;
use App\Enums\PaymentSubmissionStatus;
use App\Enums\SubscriptionStatus;
use App\Models\PaymentSubmission;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPlanPrice;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('approves a pending payment submission and activates subscription', function () {
    $admin = User::factory()->platformAdmin()->create();
    $member = User::factory()->create(['email' => 'member@example.com']);
    $team = $member->personalTeam();

    $plan = SubscriptionPlan::query()->where('slug', 'basic')->firstOrFail();
    $price = SubscriptionPlanPrice::query()
        ->where('plan_id', $plan->id)
        ->where('interval', BillingInterval::Monthly)
        ->firstOrFail();

    $submission = PaymentSubmission::factory()->pending()->create([
        'user_id' => $member->id,
        'team_id' => $team->id,
        'plan_price_id' => $price->id,
    ]);

    $response = $this->actingAs($admin)->post(route('admin.payment-submissions.approve', $submission));

    $response->assertRedirect();

    expect($submission->fresh()->status)->toBe(PaymentSubmissionStatus::Approved)
        ->and($team->subscription->fresh()->status)->toBe(SubscriptionStatus::Active);
});

it('rejects a pending payment submission with notes', function () {
    $admin = User::factory()->platformAdmin()->create();
    $member = User::factory()->create(['email' => 'member@example.com']);
    $team = $member->personalTeam();

    $plan = SubscriptionPlan::query()->where('slug', 'basic')->firstOrFail();
    $price = SubscriptionPlanPrice::query()
        ->where('plan_id', $plan->id)
        ->where('interval', BillingInterval::Yearly)
        ->firstOrFail();

    $submission = PaymentSubmission::factory()->pending()->create([
        'user_id' => $member->id,
        'team_id' => $team->id,
        'plan_price_id' => $price->id,
    ]);

    $response = $this->actingAs($admin)->post(route('admin.payment-submissions.reject', $submission), [
        'notes' => 'Reference number does not match.',
    ]);

    $response->assertRedirect();

    expect($submission->fresh())
        ->status->toBe(PaymentSubmissionStatus::Rejected)
        ->notes->toBe('Reference number does not match.');
});

it('blocks approving a non-pending submission', function () {
    $admin = User::factory()->platformAdmin()->create();
    $member = User::factory()->create(['email' => 'member@example.com']);
    $team = $member->personalTeam();

    $plan = SubscriptionPlan::query()->where('slug', 'basic')->firstOrFail();
    $price = SubscriptionPlanPrice::query()
        ->where('plan_id', $plan->id)
        ->where('interval', BillingInterval::Monthly)
        ->firstOrFail();

    $submission = PaymentSubmission::factory()->approved()->create([
        'user_id' => $member->id,
        'team_id' => $team->id,
        'plan_price_id' => $price->id,
    ]);

    $response = $this->actingAs($admin)->post(route('admin.payment-submissions.approve', $submission));

    $response->assertSessionHasErrors('submission');
});

it('includes proof image url in payment submissions index props', function () {
    $admin = User::factory()->platformAdmin()->create();
    $member = User::factory()->create(['email' => 'member@example.com']);
    $team = $member->personalTeam();

    $plan = SubscriptionPlan::query()->where('slug', 'basic')->firstOrFail();
    $price = SubscriptionPlanPrice::query()
        ->where('plan_id', $plan->id)
        ->where('interval', BillingInterval::Monthly)
        ->firstOrFail();

    PaymentSubmission::factory()->pending()->create([
        'user_id' => $member->id,
        'team_id' => $team->id,
        'plan_price_id' => $price->id,
        'proof_image_path' => 'payment-proofs/test.jpg',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.payment-submissions.index'));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/payment-submissions/index')
            ->has('submissions', 1)
            ->where('submissions.0.proofImageUrl', fn ($url) => str_contains($url, 'payment-proofs/test.jpg'))
        );
});

it('blocks owner from submitting when team has pending submission', function () {
    $member = User::factory()->create(['email' => 'member@example.com']);
    $team = $member->personalTeam();

    $plan = SubscriptionPlan::query()->where('slug', 'basic')->firstOrFail();
    $price = SubscriptionPlanPrice::query()
        ->where('plan_id', $plan->id)
        ->where('interval', BillingInterval::Monthly)
        ->firstOrFail();

    PaymentSubmission::factory()->pending()->create([
        'user_id' => $member->id,
        'team_id' => $team->id,
        'plan_price_id' => $price->id,
    ]);

    $response = $this->actingAs($member)->post(route('billing.pay.store'), [
        'plan_price_id' => $price->id,
        'reference_number' => 'REF-12345',
    ]);

    $response->assertSessionHasErrors('reference_number');
});
