<?php

use App\Models\ContentPost;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('includes author profile photo on learn post byline', function () {
    $member = User::factory()->create();
    grantTeamSubscriptionAccess($member->currentTeam);

    $author = User::factory()->author()->create([
        'name' => 'Jane Author',
        'profile_photo_path' => 'avatars/jane.jpg',
    ]);

    ContentPost::factory()->external()->create([
        'slug' => 'photo-byline-post',
        'author_id' => $author->id,
        'post_as' => null,
        'body' => 'Full body',
    ]);

    $this->actingAs($member)
        ->get(route('learn.posts.show', 'photo-byline-post'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('post.bylineName', 'Jane Author')
            ->where('post.bylineAvatarUrl', asset('storage/avatars/jane.jpg')));
});

it('still includes author photo when post as name is set', function () {
    $member = User::factory()->create();
    grantTeamSubscriptionAccess($member->currentTeam);

    $author = User::factory()->author()->create([
        'profile_photo_path' => 'avatars/editor.jpg',
    ]);

    ContentPost::factory()->external()->create([
        'slug' => 'alias-post',
        'author_id' => $author->id,
        'post_as' => 'Kinsenas Editorial',
        'body' => 'Full body',
    ]);

    $this->actingAs($member)
        ->get(route('learn.posts.show', 'alias-post'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('post.bylineName', 'Kinsenas Editorial')
            ->where('post.bylineAvatarUrl', asset('storage/avatars/editor.jpg')));
});
