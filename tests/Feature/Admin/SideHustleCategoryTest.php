<?php

use App\Models\SideHustleCategory;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('allows platform admin to create and update side hustle categories', function () {
    $admin = User::factory()->platformAdmin()->create();

    $this->actingAs($admin)
        ->post(route('admin.content.side-hustle-categories.store'), [
            'name' => 'Food & beverage',
            'slug' => 'food-beverage',
            'description' => 'Street food and small food businesses',
            'status' => 'published',
            'sort_order' => 1,
        ])
        ->assertRedirect();

    $category = SideHustleCategory::query()->where('slug', 'food-beverage')->firstOrFail();

    $this->actingAs($admin)
        ->put(route('admin.content.side-hustle-categories.update', $category), [
            'name' => 'Food and beverage',
            'slug' => 'food-beverage',
            'description' => 'Updated',
            'status' => 'published',
            'sort_order' => 2,
        ])
        ->assertRedirect();

    expect($category->fresh()->name)->toBe('Food and beverage');
});

it('forbids author from managing side hustle categories', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)
        ->get(route('admin.content.side-hustle-categories.index'))
        ->assertForbidden();
});

it('lists categories for platform admin in side hustles settings', function () {
    $admin = User::factory()->platformAdmin()->create();
    SideHustleCategory::factory()->create(['name' => 'Online work']);

    $this->actingAs($admin)
        ->get(route('admin.content.side-hustles.settings'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/content/side-hustles/settings')
            ->has('categories', 1));
});
