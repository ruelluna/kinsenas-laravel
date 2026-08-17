<?php

use App\Models\CommunityCategory;
use App\Models\CommunityPost;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('requires authentication for the community feed', function () {
    $this->get(route('learn.community.index'))->assertRedirect(route('login'));
});

it('shows published community posts to subscribed members', function () {
    $member = User::factory()->create();
    grantTeamSubscriptionAccess($member->currentTeam);

    $post = CommunityPost::factory()->published()->create([
        'title' => 'Visible story',
        'slug' => 'visible-story',
    ]);

    $this->actingAs($member)
        ->get(route('learn.community.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('learn/community/index')
            ->has('posts.data', 1)
            ->where('posts.data.0.slug', 'visible-story'));
});

it('hides pending posts from other members on the feed', function () {
    $member = User::factory()->create();
    grantTeamSubscriptionAccess($member->currentTeam);
    $other = User::factory()->create();

    CommunityPost::factory()->pending()->create([
        'user_id' => $other->id,
        'slug' => 'secret-pending',
    ]);

    $this->actingAs($member)
        ->get(route('learn.community.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->has('posts.data', 0));
});

it('filters community feed by category slug', function () {
    $member = User::factory()->create();
    grantTeamSubscriptionAccess($member->currentTeam);

    $food = CommunityCategory::factory()->create(['slug' => 'wins']);
    $tips = CommunityCategory::factory()->create(['slug' => 'tips']);

    CommunityPost::factory()->published()->create([
        'community_category_id' => $food->id,
        'slug' => 'food-story',
    ]);
    CommunityPost::factory()->published()->create([
        'community_category_id' => $tips->id,
        'slug' => 'tip-story',
    ]);

    $this->actingAs($member)
        ->get(route('learn.community.index', ['category' => 'wins']))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('activeCategory', 'wins')
            ->has('posts.data', 1)
            ->where('posts.data.0.slug', 'food-story'));
});
