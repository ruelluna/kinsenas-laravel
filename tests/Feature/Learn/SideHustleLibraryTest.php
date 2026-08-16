<?php

use App\Models\SideHustle;
use App\Models\SideHustleCategory;
use App\Models\User;
use App\Services\Billing\SubscriptionService;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('shows published side hustles to subscribed members with full body', function () {
    $user = User::factory()->create();
    grantTeamSubscriptionAccess($user->currentTeam);

    $category = SideHustleCategory::factory()->create(['slug' => 'online-work']);
    SideHustle::factory()->create([
        'slug' => 'virtual-assistant',
        'body' => '<p>Full hustle guide</p>',
        'side_hustle_category_id' => $category->id,
    ]);

    $this->actingAs($user)
        ->get(route('learn.index', ['filter' => 'side-hustles']))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('learn/index')
            ->where('hasFullAccess', true)
            ->has('hustles.data', 1));

    $this->actingAs($user)
        ->get(route('learn.side-hustles.show', 'virtual-assistant'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('showFullBody', true)
            ->where('hustle.body', '<p>Full hustle guide</p>'));
});

it('shows external teaser only to guests', function () {
    $category = SideHustleCategory::factory()->create();
    SideHustle::factory()->external()->create([
        'slug' => 'guest-hustle',
        'excerpt' => 'Guest teaser',
        'body' => '<p>Hidden body</p>',
        'side_hustle_category_id' => $category->id,
    ]);
    SideHustle::factory()->internal()->create([
        'slug' => 'member-only-hustle',
        'side_hustle_category_id' => $category->id,
    ]);

    $this->get(route('learn.index', ['filter' => 'side-hustles']))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->has('hustles.data', 1));

    $this->get(route('learn.side-hustles.show', 'guest-hustle'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('showFullBody', false)
            ->missing('hustle.body'));

    $this->get(route('learn.side-hustles.show', 'member-only-hustle'))
        ->assertNotFound();
});

it('filters side hustles by category slug', function () {
    $food = SideHustleCategory::factory()->create(['slug' => 'food']);
    $online = SideHustleCategory::factory()->create(['slug' => 'online']);

    SideHustle::factory()->external()->create([
        'slug' => 'street-food',
        'side_hustle_category_id' => $food->id,
    ]);
    SideHustle::factory()->external()->create([
        'slug' => 'va-work',
        'side_hustle_category_id' => $online->id,
    ]);

    $this->get(route('learn.index', ['filter' => 'side-hustles', 'category' => 'food']))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('activeCategory', 'food')
            ->has('hustles.data', 1)
            ->where('hustles.data.0.slug', 'street-food'));
});

it('shows post as byline on side hustle detail', function () {
    $user = User::factory()->create();
    grantTeamSubscriptionAccess($user->currentTeam);

    $category = SideHustleCategory::factory()->create();
    SideHustle::factory()->create([
        'slug' => 'branded-hustle',
        'post_as' => 'Kinsenas Editorial',
        'side_hustle_category_id' => $category->id,
    ]);

    $this->actingAs($user)
        ->get(route('learn.side-hustles.show', 'branded-hustle'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->where('hustle.bylineName', 'Kinsenas Editorial'));
});

it('shows teaser to logged in unsubscribed users', function () {
    $user = User::factory()->create();
    app(SubscriptionService::class)->requirePaidSubscription($user->currentTeam);

    $category = SideHustleCategory::factory()->create();
    SideHustle::factory()->external()->create([
        'slug' => 'upgrade-hustle',
        'excerpt' => 'Visible teaser',
        'body' => '<p>Hidden until subscribed</p>',
        'side_hustle_category_id' => $category->id,
    ]);

    $this->actingAs($user)
        ->get(route('learn.side-hustles.show', 'upgrade-hustle'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('showFullBody', false)
            ->where('hasFullAccess', false));
});
