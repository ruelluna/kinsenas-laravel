<?php

use App\Models\SideHustle;
use App\Models\SideHustleCategory;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('allows platform admin to create and update side hustles', function () {
    $admin = User::factory()->platformAdmin()->create();
    $category = SideHustleCategory::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.content.side-hustles.store'), [
            'side_hustle_category_id' => $category->id,
            'title' => 'Street food cart',
            'slug' => 'street-food-cart',
            'excerpt' => 'Start small with a mobile cart.',
            'body' => '<p>Guide body</p>',
            'difficulty' => 'beginner',
            'capital_tier' => 'low',
            'startup_capital_min' => 5000,
            'startup_capital_max' => 15000,
            'time_commitment_hours_min' => 10,
            'time_commitment_hours_max' => 20,
            'skills' => 'Cooking, Customer service',
            'equipment' => 'Cart, Cooler',
            'publish_scope' => 'both',
            'status' => 'published',
            'sort_order' => 1,
        ])
        ->assertRedirect();

    $hustle = SideHustle::query()->where('slug', 'street-food-cart')->firstOrFail();

    expect($hustle->skills)->toBe(['Cooking', 'Customer service']);

    $this->actingAs($admin)
        ->put(route('admin.content.side-hustles.update', $hustle), [
            'side_hustle_category_id' => $category->id,
            'title' => 'Street food cart updated',
            'slug' => 'street-food-cart',
            'excerpt' => 'Updated excerpt',
            'body' => '<p>Updated body</p>',
            'difficulty' => 'beginner',
            'capital_tier' => 'low',
            'startup_capital_min' => 5000,
            'startup_capital_max' => 15000,
            'time_commitment_hours_min' => 10,
            'time_commitment_hours_max' => 20,
            'skills' => 'Cooking',
            'equipment' => 'Cart',
            'publish_scope' => 'both',
            'status' => 'published',
            'sort_order' => 1,
        ])
        ->assertRedirect();

    expect($hustle->fresh()->title)->toBe('Street food cart updated');
});

it('allows author to manage side hustles', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)
        ->get(route('admin.content.side-hustles.index'))
        ->assertSuccessful();
});

it('requires excerpt for external publish scope', function () {
    $admin = User::factory()->platformAdmin()->create();
    $category = SideHustleCategory::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.content.side-hustles.store'), [
            'side_hustle_category_id' => $category->id,
            'title' => 'Virtual assistant',
            'slug' => 'virtual-assistant',
            'body' => '<p>Guide</p>',
            'difficulty' => 'intermediate',
            'capital_tier' => 'low',
            'publish_scope' => 'external',
            'status' => 'published',
        ])
        ->assertSessionHasErrors('excerpt');
});
