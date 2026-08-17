<?php

use App\Enums\CommunityPostStatus;
use App\Models\CommunityPost;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('lists all community posts for platform admin', function () {
    $admin = User::factory()->platformAdmin()->create();
    CommunityPost::factory()->published()->create(['title' => 'Live story']);
    CommunityPost::factory()->pending()->create(['title' => 'Waiting story']);

    $this->actingAs($admin)
        ->get(route('admin.content.community.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/content/community/index')
            ->has('posts.data', 2));
});

it('excludes withdrawn community posts from the admin list', function () {
    $admin = User::factory()->platformAdmin()->create();
    CommunityPost::factory()->published()->create(['title' => 'Still visible']);
    CommunityPost::factory()->create([
        'title' => 'Removed story',
        'status' => CommunityPostStatus::Withdrawn,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.content.community.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/content/community/index')
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Still visible'));
});

it('allows platform admin to remove a published community post', function () {
    $admin = User::factory()->platformAdmin()->create();
    $post = CommunityPost::factory()->published()->create(['slug' => 'remove-me']);

    $this->actingAs($admin)
        ->delete(route('admin.content.community-posts.destroy', $post))
        ->assertRedirect();

    expect($post->fresh()->status)->toBe(CommunityPostStatus::Withdrawn);
});

it('forbids author from listing community posts in admin', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)
        ->get(route('admin.content.community.index'))
        ->assertForbidden();
});
