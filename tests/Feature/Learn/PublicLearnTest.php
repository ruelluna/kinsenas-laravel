<?php

use App\Models\ContentPost;
use App\Models\ContentSeries;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows external teasers to guests but hides internal posts', function () {
    ContentPost::factory()->external()->create(['title' => 'Public tip', 'slug' => 'public-tip']);
    ContentPost::factory()->internal()->create(['title' => 'Members only', 'slug' => 'members-only']);

    $this->get(route('learn.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('learn/index')
            ->where('hasFullAccess', false)
            ->has('posts.data', 1)
            ->where('posts.data.0.slug', 'public-tip'));

    $this->get(route('learn.posts.show', 'public-tip'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('showFullBody', false));

    $this->get(route('learn.posts.show', 'members-only'))
        ->assertNotFound();
});

it('returns not found for draft posts', function () {
    ContentPost::factory()->draft()->external()->create(['slug' => 'draft-tip']);

    $this->get(route('learn.posts.show', 'draft-tip'))->assertNotFound();
});

it('hides series without public teaser episodes from guests', function () {
    $series = ContentSeries::factory()->create(['slug' => 'internal-series']);
    ContentPost::factory()->internal()->episode(1, $series)->create();

    $this->get(route('learn.series.show', $series))->assertNotFound();
});

it('shows public series teaser to guests', function () {
    $series = ContentSeries::factory()->create(['slug' => 'public-series']);
    ContentPost::factory()->external()->episode(1, $series)->create(['slug' => 'public-ep-1']);

    $this->get(route('learn.series.show', $series))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->has('episodes', 1));
});

it('includes open graph metadata for public post teasers', function () {
    ContentPost::factory()->external()->create([
        'title' => 'Shareable tip',
        'slug' => 'shareable-tip',
        'excerpt' => 'A teaser for social sharing.',
        'cover_image_url' => 'https://cdn.example.com/cover.jpg',
    ]);

    $this->get(route('learn.posts.show', 'shareable-tip'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('openGraph.title', 'Shareable tip')
            ->where('openGraph.description', 'A teaser for social sharing.')
            ->where('openGraph.image', 'https://cdn.example.com/cover.jpg')
            ->where('openGraph.url', route('learn.posts.show', 'shareable-tip', absolute: true)),
        );
});

it('omits open graph metadata for internal-only posts', function () {
    $this->seed(BillingSeeder::class);

    $user = User::factory()->create();
    grantTeamSubscriptionAccess($user->currentTeam);

    ContentPost::factory()->internal()->create(['slug' => 'internal-only']);

    $this->actingAs($user)
        ->get(route('learn.posts.show', 'internal-only'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->where('openGraph', null));
});
