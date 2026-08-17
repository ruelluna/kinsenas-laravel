<?php

use App\Models\PodcastShow;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('allows platform admin to create and update podcast shows', function () {
    $admin = User::factory()->platformAdmin()->create();

    $this->actingAs($admin)
        ->post(route('admin.content.podcast-shows.store'), [
            'title' => 'Sweldo Stories',
            'slug' => 'sweldo-stories',
            'description' => 'Real stories from Filipino earners.',
            'status' => 'published',
            'sort_order' => 1,
        ])
        ->assertRedirect();

    $show = PodcastShow::query()->where('slug', 'sweldo-stories')->firstOrFail();

    $this->actingAs($admin)
        ->put(route('admin.content.podcast-shows.update', $show), [
            'title' => 'Sweldo Stories updated',
            'slug' => 'sweldo-stories',
            'description' => 'Updated',
            'status' => 'published',
            'sort_order' => 2,
        ])
        ->assertRedirect();

    expect($show->fresh()->title)->toBe('Sweldo Stories updated');
});

it('forbids author from managing podcast shows', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)
        ->get(route('admin.content.podcasts.index'))
        ->assertForbidden();
});
