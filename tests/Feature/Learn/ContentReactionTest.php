<?php

use App\Models\ContentPost;
use App\Models\User;
use App\Services\Billing\SubscriptionService;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('requires learn member access to react', function () {
    $post = ContentPost::factory()->create();
    $user = User::factory()->create();
    app(SubscriptionService::class)->requirePaidSubscription($user->currentTeam);

    $this->actingAs($user)
        ->post(route('learn.posts.react', $post))
        ->assertForbidden();
});

it('toggles helpful reactions for subscribed members', function () {
    $post = ContentPost::factory()->create();
    $user = User::factory()->create();
    grantTeamSubscriptionAccess($user->currentTeam);

    $this->actingAs($user)
        ->postJson(route('learn.posts.react', $post))
        ->assertSuccessful()
        ->assertJson(['reacted' => true, 'count' => 1]);

    $this->actingAs($user)
        ->postJson(route('learn.posts.react', $post))
        ->assertSuccessful()
        ->assertJson(['reacted' => false, 'count' => 0]);
});

it('requires authentication to react', function () {
    $post = ContentPost::factory()->create();

    $this->post(route('learn.posts.react', $post))->assertRedirect();
});
