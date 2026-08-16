<?php

use App\Models\ContentPost;
use App\Models\ContentSeries;
use App\Models\User;
use App\Services\Billing\SubscriptionService;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('shows full library to subscribed members', function () {
    $user = User::factory()->create();
    grantTeamSubscriptionAccess($user->currentTeam);

    ContentPost::factory()->internal()->create(['slug' => 'internal-tip']);
    ContentPost::factory()->external()->create(['slug' => 'external-tip']);

    $this->actingAs($user)
        ->get(route('learn.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('hasFullAccess', true)
            ->has('posts.data', 2));
});

it('shows full body for subscribed members', function () {
    $user = User::factory()->create();
    grantTeamSubscriptionAccess($user->currentTeam);

    ContentPost::factory()->external()->create([
        'slug' => 'member-read',
        'body' => 'Secret member body',
    ]);

    $this->actingAs($user)
        ->get(route('learn.posts.show', 'member-read'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('showFullBody', true)
            ->where('post.body', 'Secret member body'));
});

it('shows teaser only for logged in unsubscribed users', function () {
    $user = User::factory()->create();
    app(SubscriptionService::class)->requirePaidSubscription($user->currentTeam);

    ContentPost::factory()->external()->create([
        'slug' => 'upgrade-teaser',
        'body' => 'Hidden until subscribed',
        'excerpt' => 'Visible teaser',
    ]);

    $this->actingAs($user)
        ->get(route('learn.posts.show', 'upgrade-teaser'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('showFullBody', false)
            ->where('hasFullAccess', false)
            ->missing('post.body'));
});

it('orders series episodes by episode number', function () {
    $user = User::factory()->create();
    grantTeamSubscriptionAccess($user->currentTeam);

    $series = ContentSeries::factory()->create(['slug' => 'ordered-series']);
    ContentPost::factory()->episode(2, $series)->create(['slug' => 'ep-2', 'title' => 'Second']);
    ContentPost::factory()->episode(1, $series)->create(['slug' => 'ep-1', 'title' => 'First']);

    $this->actingAs($user)
        ->get(route('learn.series.show', $series))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('episodes.0.slug', 'ep-1')
            ->where('episodes.1.slug', 'ep-2'));
});

it('allows platform admin full learn access without subscription', function () {
    $admin = User::factory()->platformAdmin()->create();
    app(SubscriptionService::class)->requirePaidSubscription($admin->currentTeam);

    ContentPost::factory()->internal()->create([
        'slug' => 'admin-read',
        'body' => 'Admin can read',
    ]);

    $this->actingAs($admin)
        ->get(route('learn.posts.show', 'admin-read'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->where('showFullBody', true));
});
