<?php

use App\Models\PodcastEpisode;
use App\Models\PodcastShow;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('allows platform admin to create podcast episodes', function () {
    $admin = User::factory()->platformAdmin()->create();
    $show = PodcastShow::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.content.podcast-episodes.store'), [
            'podcast_show_id' => $show->id,
            'episode_number' => 1,
            'title' => 'First payday plan',
            'slug' => 'first-payday-plan',
            'excerpt' => 'How to split your first sweldo.',
            'show_notes' => '<p>Notes</p>',
            'audio_embed_url' => 'https://open.spotify.com/embed/episode/example',
            'duration_minutes' => 25,
            'publish_scope' => 'both',
            'status' => 'published',
        ])
        ->assertRedirect();

    expect(PodcastEpisode::query()->where('slug', 'first-payday-plan')->exists())->toBeTrue();
});

it('enforces unique episode numbers per show', function () {
    $admin = User::factory()->platformAdmin()->create();
    $show = PodcastShow::factory()->create();
    PodcastEpisode::factory()->create([
        'podcast_show_id' => $show->id,
        'episode_number' => 1,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.content.podcast-episodes.store'), [
            'podcast_show_id' => $show->id,
            'episode_number' => 1,
            'title' => 'Duplicate episode',
            'slug' => 'duplicate-episode',
            'excerpt' => 'Duplicate number',
            'publish_scope' => 'both',
            'status' => 'published',
        ])
        ->assertSessionHasErrors('episode_number');
});
