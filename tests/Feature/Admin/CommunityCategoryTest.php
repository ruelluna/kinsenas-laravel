<?php

use App\Models\CommunityCategory;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('allows platform admin to create and update community categories', function () {
    $admin = User::factory()->platformAdmin()->create();

    $this->actingAs($admin)
        ->post(route('admin.content.community-categories.store'), [
            'name' => 'Payday wins',
            'slug' => 'payday-wins',
            'description' => 'Stories about stretching a paycheck',
            'status' => 'published',
            'sort_order' => 1,
        ])
        ->assertRedirect();

    $category = CommunityCategory::query()->where('slug', 'payday-wins')->firstOrFail();

    $this->actingAs($admin)
        ->put(route('admin.content.community-categories.update', $category), [
            'name' => 'Payday wins updated',
            'slug' => 'payday-wins',
            'description' => 'Updated description',
            'status' => 'published',
            'sort_order' => 2,
        ])
        ->assertRedirect();

    expect($category->fresh()->name)->toBe('Payday wins updated');
});

it('forbids author from managing community categories', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)
        ->get(route('admin.content.community-categories.index'))
        ->assertForbidden();
});

it('lists categories for platform admin', function () {
    $admin = User::factory()->platformAdmin()->create();
    CommunityCategory::factory()->create(['name' => 'Side hustle stories']);

    $this->actingAs($admin)
        ->get(route('admin.content.community-categories.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/content/community-categories/index')
            ->has('categories', 1));
});
