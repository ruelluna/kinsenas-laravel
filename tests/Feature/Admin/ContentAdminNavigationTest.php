<?php

use App\Models\PodcastShow;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('allows platform admin to access entity list settings and stats routes', function () {
    $admin = User::factory()->platformAdmin()->create();

    $routes = [
        ['admin.content.posts.index', 'admin/content/posts/index'],
        ['admin.content.posts.settings', 'admin/content/posts/settings'],
        ['admin.content.posts.stats', 'admin/content/posts/stats'],
        ['admin.content.series.index', 'admin/content/series/index'],
        ['admin.content.series.settings', 'admin/content/series/settings'],
        ['admin.content.series.stats', 'admin/content/series/stats'],
        ['admin.content.podcasts.index', 'admin/content/podcast-shows/index'],
        ['admin.content.podcasts.settings', 'admin/content/podcasts/settings'],
        ['admin.content.podcasts.stats', 'admin/content/podcasts/stats'],
        ['admin.content.side-hustles.index', 'admin/content/side-hustles/index'],
        ['admin.content.side-hustles.settings', 'admin/content/side-hustles/settings'],
        ['admin.content.side-hustles.stats', 'admin/content/side-hustles/stats'],
        ['admin.content.community.index', 'admin/content/community/index'],
        ['admin.content.community.settings', 'admin/content/community/settings'],
        ['admin.content.community.stats', 'admin/content/community/stats'],
    ];

    foreach ($routes as [$routeName, $component]) {
        $this->actingAs($admin)
            ->get(route($routeName))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component($component));
    }
});

it('redirects legacy content admin URLs to new entity paths', function () {
    $admin = User::factory()->platformAdmin()->create();

    $this->actingAs($admin)
        ->get('/admin/content/stats')
        ->assertRedirect(route('admin.content.posts.stats'));

    $this->actingAs($admin)
        ->get('/admin/content/post-categories')
        ->assertRedirect(route('admin.content.posts.settings'));

    $this->actingAs($admin)
        ->get('/admin/content/community-posts')
        ->assertRedirect(route('admin.content.community.index'));

    $this->actingAs($admin)
        ->get('/admin/content/podcast-shows')
        ->assertRedirect(route('admin.content.podcasts.index'));

    $this->actingAs($admin)
        ->get('/admin/content/podcast-episodes')
        ->assertRedirect(route('admin.content.podcasts.index'));
});

it('forbids author from entity settings and stats', function () {
    $author = User::factory()->author()->create();

    $forbiddenRoutes = [
        'admin.content.posts.settings',
        'admin.content.posts.stats',
        'admin.content.series.settings',
        'admin.content.community.index',
        'admin.content.community.settings',
    ];

    foreach ($forbiddenRoutes as $routeName) {
        $this->actingAs($author)
            ->get(route($routeName))
            ->assertForbidden();
    }
});

it('allows author to access posts and side hustles lists only', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)
        ->get(route('admin.content.posts.index'))
        ->assertOk();

    $this->actingAs($author)
        ->get(route('admin.content.side-hustles.index'))
        ->assertOk();

    $this->actingAs($author)
        ->get(route('admin.content.series.index'))
        ->assertForbidden();
});

it('pre-selects podcast show when creating nested episode', function () {
    $admin = User::factory()->platformAdmin()->create();
    $show = PodcastShow::factory()->create(['slug' => 'kinsenas-talk']);

    $this->actingAs($admin)
        ->get(route('admin.content.podcasts.episodes.create', $show))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/content/podcast-episodes/create')
            ->where('selectedShowId', $show->id)
            ->has('storeUrl'));

    $this->actingAs($admin)
        ->post(route('admin.content.podcasts.episodes.store', $show), [
            'episode_number' => 1,
            'title' => 'Pilot episode',
            'slug' => 'pilot-episode',
            'publish_scope' => 'internal',
            'status' => 'draft',
        ])
        ->assertRedirect();

    expect($show->fresh()->episodes()->where('slug', 'pilot-episode')->exists())->toBeTrue();
});
