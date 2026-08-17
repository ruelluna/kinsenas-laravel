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

it('allows members to submit a post with multiple community categories', function () {
    $member = User::factory()->create();
    grantTeamSubscriptionAccess($member->currentTeam);

    $wins = CommunityCategory::factory()->create(['slug' => 'payday-wins']);
    $tips = CommunityCategory::factory()->create(['slug' => 'tips-lessons']);

    $this->actingAs($member)
        ->post(route('learn.community.store'), [
            'category_ids' => [$wins->id, $tips->id],
            'title' => 'Split my bonus',
            'excerpt' => 'Saved half for emergencies.',
            'body' => '<p>I moved half to my emergency fund.</p>',
        ])
        ->assertRedirect(route('learn.community.mine'));

    $post = CommunityPost::query()->where('title', 'Split my bonus')->firstOrFail();

    expect($post->categories()->pluck('slug')->sort()->values()->all())
        ->toBe(['payday-wins', 'tips-lessons']);
});

it('filters community feed when post has multiple categories', function () {
    $member = User::factory()->create();
    grantTeamSubscriptionAccess($member->currentTeam);

    $wins = CommunityCategory::factory()->create(['slug' => 'payday-wins']);
    $family = CommunityCategory::factory()->create(['slug' => 'family-household']);
    $tips = CommunityCategory::factory()->create(['slug' => 'tips-only']);

    $multi = CommunityPost::factory()->published()->create(['slug' => 'multi-category-story']);
    $multi->categories()->attach([$wins->id, $family->id]);

    CommunityPost::factory()->published()->create(['slug' => 'tips-only-story'])
        ->categories()
        ->attach($tips->id);

    $this->actingAs($member)
        ->get(route('learn.community.index', ['category' => 'family-household']))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('posts.data', 1)
            ->where('posts.data.0.slug', 'multi-category-story'));
});
