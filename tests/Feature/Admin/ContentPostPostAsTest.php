<?php

use App\Models\ContentPost;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('stores post as on content posts', function () {
    $admin = User::factory()->platformAdmin()->create();

    $this->actingAs($admin)
        ->post(route('admin.content.posts.store'), [
            'title' => 'Guest column',
            'slug' => 'guest-column',
            'excerpt' => 'A guest perspective on payday planning.',
            'body' => '<p>Guest body</p>',
            'content_type' => 'article',
            'publish_scope' => 'both',
            'status' => 'published',
            'post_as' => 'Maria Santos, CPA',
        ])
        ->assertRedirect();

    expect(ContentPost::query()->where('slug', 'guest-column')->firstOrFail()->post_as)
        ->toBe('Maria Santos, CPA');
});

it('shows post as name on learn post page instead of author', function () {
    $user = User::factory()->create();
    grantTeamSubscriptionAccess($user->currentTeam);

    $author = User::factory()->author()->create(['name' => 'Internal Author']);

    ContentPost::factory()->external()->create([
        'slug' => 'guest-post',
        'author_id' => $author->id,
        'post_as' => 'Kinsenas Editorial',
        'body' => 'Full body',
    ]);

    $this->actingAs($user)
        ->get(route('learn.posts.show', 'guest-post'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('post.postAs', 'Kinsenas Editorial')
            ->where('post.bylineName', 'Kinsenas Editorial')
            ->where('post.authorName', 'Kinsenas Editorial'));
});

it('falls back to author name when post as is empty', function () {
    $user = User::factory()->create();
    grantTeamSubscriptionAccess($user->currentTeam);

    $author = User::factory()->author()->create(['name' => 'Jane Author']);

    ContentPost::factory()->external()->create([
        'slug' => 'author-post',
        'author_id' => $author->id,
        'post_as' => null,
        'body' => 'Full body',
    ]);

    $this->actingAs($user)
        ->get(route('learn.posts.show', 'author-post'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('post.postAs', null)
            ->where('post.bylineName', 'Jane Author'));
});
