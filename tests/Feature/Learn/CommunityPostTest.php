<?php

use App\Enums\CommunityPostStatus;
use App\Models\CommunityCategory;
use App\Models\CommunityPost;
use App\Models\User;
use App\Services\Billing\SubscriptionService;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('allows subscribed members to submit a community post for review', function () {
    $member = User::factory()->create();
    grantTeamSubscriptionAccess($member->currentTeam);
    $category = CommunityCategory::factory()->create();

    $this->actingAs($member)
        ->post(route('learn.community.store'), [
            'community_category_id' => $category->id,
            'title' => 'My payday win',
            'excerpt' => 'How I saved my 13th month pay.',
            'body' => '<p>I split it across funds.</p>',
        ])
        ->assertRedirect(route('learn.community.mine'));

    $post = CommunityPost::query()->where('title', 'My payday win')->firstOrFail();

    expect($post->status)->toBe(CommunityPostStatus::Pending)
        ->and($post->user_id)->toBe($member->id);
});

it('forbids unsubscribed members from submitting community posts', function () {
    $member = User::factory()->create();
    app(SubscriptionService::class)->requirePaidSubscription($member->currentTeam);
    $category = CommunityCategory::factory()->create();

    $this->actingAs($member)
        ->post(route('learn.community.store'), [
            'community_category_id' => $category->id,
            'title' => 'Blocked story',
            'body' => '<p>Nope</p>',
        ])
        ->assertForbidden();
});

it('shows the author their pending post on mine', function () {
    $member = User::factory()->create();
    grantTeamSubscriptionAccess($member->currentTeam);
    $post = CommunityPost::factory()->pending()->create(['user_id' => $member->id]);

    $this->actingAs($member)
        ->get(route('learn.community.mine'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('learn/community/mine')
            ->has('posts.data', 1)
            ->where('posts.data.0.slug', $post->slug)
            ->where('posts.data.0.status', 'pending'));
});
