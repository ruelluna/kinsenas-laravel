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

it('lists published podcast shows', function () {
    PodcastShow::factory()->create(['title' => 'Sweldo Stories', 'slug' => 'sweldo-stories']);
    PodcastShow::factory()->draft()->create(['slug' => 'draft-show']);

    $this->get(route('learn.podcasts.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('shows', 1)
            ->where('shows.0.slug', 'sweldo-stories'));
});

it('shows episodes for a podcast show to subscribed members', function () {
    $user = User::factory()->create();
    grantTeamSubscriptionAccess($user->currentTeam);

    $show = PodcastShow::factory()->create(['slug' => 'money-talk']);
    PodcastEpisode::factory()->create([
        'podcast_show_id' => $show->id,
        'slug' => 'episode-one',
        'episode_number' => 1,
        'show_notes' => '<p>Full notes</p>',
    ]);

    $this->actingAs($user)
        ->get(route('learn.podcasts.show', $show))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->has('episodes', 1));

    $this->actingAs($user)
        ->get(route('learn.podcasts.episodes.show', [$show, 'episode-one']))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('showFullBody', true)
            ->where('episode.showNotes', '<p>Full notes</p>'));
});

it('hides internal episodes from guests', function () {
    $show = PodcastShow::factory()->create(['slug' => 'member-podcast']);
    PodcastEpisode::factory()->internal()->create([
        'podcast_show_id' => $show->id,
        'slug' => 'internal-episode',
    ]);

    $this->get(route('learn.podcasts.show', $show))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->has('episodes', 0));
});
