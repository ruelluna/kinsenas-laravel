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

it('allows platform admin to approve a pending community post', function () {
    $admin = User::factory()->platformAdmin()->create();
    $author = User::factory()->create();
    $post = CommunityPost::factory()->pending()->create([
        'user_id' => $author->id,
        'slug' => 'approve-me',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.content.community-posts.approve', $post))
        ->assertRedirect();

    expect($post->fresh()->status)->toBe(CommunityPostStatus::Published);
});

it('allows platform admin to reject a pending community post', function () {
    $admin = User::factory()->platformAdmin()->create();
    $author = User::factory()->create();
    $post = CommunityPost::factory()->pending()->create(['user_id' => $author->id]);

    $this->actingAs($admin)
        ->post(route('admin.content.community-posts.reject', $post), [
            'rejection_reason' => 'Does not meet guidelines',
        ])
        ->assertRedirect();

    expect($post->fresh()->status)->toBe(CommunityPostStatus::Rejected)
        ->and($post->fresh()->rejection_reason)->toBe('Does not meet guidelines');
});

it('lists pending posts in the moderation queue', function () {
    $admin = User::factory()->platformAdmin()->create();
    CommunityPost::factory()->pending()->create(['title' => 'Awaiting review']);
    CommunityPost::factory()->published()->create();

    $this->actingAs($admin)
        ->get(route('admin.content.community-posts.pending'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/content/community-posts/pending')
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Awaiting review'));
});
