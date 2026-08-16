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
    $admin = User::factory()->create(['is_platform_admin' => true]);

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
    $admin = User::factory()->create(['is_platform_admin' => true]);
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
    $user = User::factory()->create(['is_platform_admin' => false]);

    $this->actingAs($user)
        ->get(route('admin.content.posts.index'))
        ->assertForbidden();
});
