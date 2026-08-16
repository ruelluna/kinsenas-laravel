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
