<?php

use App\Models\ContentPost;
use App\Models\ContentSeries;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('allows platform admin to create posts with excerpt validation', function () {
    $admin = User::factory()->platformAdmin()->create();

    $this->actingAs($admin)
        ->post(route('admin.content.posts.store'), [
            'title' => 'External tip',
            'slug' => 'external-tip',
            'body' => 'Full body copy',
            'content_type' => 'article',
            'publish_scope' => 'external',
            'status' => 'published',
        ])
        ->assertSessionHasErrors('excerpt');

    $this->actingAs($admin)
        ->post(route('admin.content.posts.store'), [
            'title' => 'External tip',
            'slug' => 'external-tip',
            'excerpt' => 'Teaser text',
            'body' => 'Full body copy',
            'content_type' => 'article',
            'publish_scope' => 'external',
            'status' => 'published',
        ])
        ->assertRedirect();

    expect(ContentPost::query()->where('slug', 'external-tip')->exists())->toBeTrue();
});

it('forces episode type when series is selected', function () {
    $admin = User::factory()->platformAdmin()->create();
    $series = ContentSeries::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.content.posts.store'), [
            'content_series_id' => $series->id,
            'episode_number' => 1,
            'title' => 'Episode one',
            'slug' => 'episode-one',
            'excerpt' => 'Teaser',
            'body' => 'Episode body',
            'content_type' => 'article',
            'publish_scope' => 'both',
            'status' => 'published',
        ])
        ->assertRedirect();

    expect(ContentPost::query()->where('slug', 'episode-one')->first()->content_type->value)->toBe('episode');
});

it('forbids non admin from managing posts', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.content.posts.index'))
        ->assertForbidden();
});

it('allows author to create posts assigned to themselves', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)
        ->post(route('admin.content.posts.store'), [
            'title' => 'Author post',
            'slug' => 'author-post',
            'excerpt' => 'Teaser text',
            'body' => 'Full body copy',
            'content_type' => 'article',
            'publish_scope' => 'both',
            'status' => 'draft',
        ])
        ->assertRedirect();

    $post = ContentPost::query()->where('slug', 'author-post')->firstOrFail();

    expect($post->author_id)->toBe($author->id);
});

it('lists only own posts for authors', function () {
    $author = User::factory()->author()->create();
    $otherAuthor = User::factory()->author()->create(['email' => 'other-author@example.com']);

    ContentPost::factory()->create([
        'author_id' => $author->id,
        'title' => 'My post',
        'slug' => 'my-post',
    ]);
    ContentPost::factory()->create([
        'author_id' => $otherAuthor->id,
        'title' => 'Other post',
        'slug' => 'other-post',
    ]);

    $this->actingAs($author)
        ->get(route('admin.content.posts.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/content/posts/index')
            ->has('posts.data', 1)
            ->where('posts.data.0.slug', 'my-post')
            ->where('posts.data.0.authorName', $author->name));
});

it('forbids author from editing another authors post', function () {
    $author = User::factory()->author()->create();
    $otherAuthor = User::factory()->author()->create(['email' => 'other-author@example.com']);
    $post = ContentPost::factory()->create(['author_id' => $otherAuthor->id]);

    $this->actingAs($author)
        ->get(route('admin.content.posts.edit', $post))
        ->assertForbidden();

    $this->actingAs($author)
        ->put(route('admin.content.posts.update', $post), [
            'title' => 'Hijacked',
            'slug' => $post->slug,
            'excerpt' => 'Teaser',
            'body' => 'Body',
            'content_type' => 'article',
            'publish_scope' => 'both',
            'status' => 'draft',
        ])
        ->assertForbidden();
});

it('allows platform admin to assign author on create', function () {
    $admin = User::factory()->platformAdmin()->create();
    $author = User::factory()->author()->create(['email' => 'writer@example.com']);

    $this->actingAs($admin)
        ->post(route('admin.content.posts.store'), [
            'author_id' => $author->id,
            'title' => 'Assigned post',
            'slug' => 'assigned-post',
            'excerpt' => 'Teaser text',
            'body' => 'Full body copy',
            'content_type' => 'article',
            'publish_scope' => 'both',
            'status' => 'draft',
        ])
        ->assertRedirect();

    expect(ContentPost::query()->where('slug', 'assigned-post')->firstOrFail()->author_id)
        ->toBe($author->id);
});

it('allows platform admin to edit any post', function () {
    $admin = User::factory()->platformAdmin()->create();
    $author = User::factory()->author()->create(['email' => 'writer@example.com']);
    $post = ContentPost::factory()->create(['author_id' => $author->id]);

    $this->actingAs($admin)
        ->put(route('admin.content.posts.update', $post), [
            'title' => 'Admin updated',
            'slug' => $post->slug,
            'excerpt' => 'Teaser',
            'body' => 'Updated body',
            'content_type' => 'article',
            'publish_scope' => 'both',
            'status' => 'draft',
        ])
        ->assertRedirect();

    expect($post->fresh()->title)->toBe('Admin updated');
});

it('accepts html body when creating and updating posts', function () {
    $admin = User::factory()->platformAdmin()->create();
    $htmlBody = '<p>Intro</p><h2>Section</h2><p>Details with <strong>emphasis</strong>.</p><img src="/storage/content/images/example.png" alt="Chart">';

    $this->actingAs($admin)
        ->post(route('admin.content.posts.store'), [
            'title' => 'HTML article',
            'slug' => 'html-article',
            'excerpt' => 'Teaser text',
            'body' => $htmlBody,
            'content_type' => 'article',
            'publish_scope' => 'both',
            'status' => 'published',
        ])
        ->assertRedirect();

    $post = ContentPost::query()->where('slug', 'html-article')->first();
    expect($post)->not->toBeNull()
        ->and($post->body)->toBe($htmlBody);

    $updatedBody = '<p>Updated intro</p>';

    $this->actingAs($admin)
        ->put(route('admin.content.posts.update', $post), [
            'title' => 'HTML article',
            'slug' => 'html-article',
            'excerpt' => 'Teaser text',
            'body' => $updatedBody,
            'content_type' => 'article',
            'publish_scope' => 'both',
            'status' => 'published',
        ])
        ->assertRedirect();

    expect($post->fresh()->body)->toBe($updatedBody);
});
