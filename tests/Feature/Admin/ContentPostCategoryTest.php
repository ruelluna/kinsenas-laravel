<?php

use App\Models\ContentPost;
use App\Models\ContentPostCategory;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('allows platform admin to create and update content post categories', function () {
    $admin = User::factory()->platformAdmin()->create();

    $this->actingAs($admin)
        ->post(route('admin.content.post-categories.store'), [
            'name' => 'Payday & income',
            'slug' => 'payday-income',
            'description' => 'Sweldo rituals and locking income',
            'status' => 'published',
            'sort_order' => 1,
        ])
        ->assertRedirect();

    $category = ContentPostCategory::query()->where('slug', 'payday-income')->firstOrFail();

    $this->actingAs($admin)
        ->put(route('admin.content.post-categories.update', $category), [
            'name' => 'Payday and income',
            'slug' => 'payday-income',
            'description' => 'Updated',
            'status' => 'published',
            'sort_order' => 2,
        ])
        ->assertRedirect();

    expect($category->fresh()->name)->toBe('Payday and income');
});

it('allows assigning multiple categories when creating a content post', function () {
    $admin = User::factory()->platformAdmin()->create();
    $payday = ContentPostCategory::factory()->create(['slug' => 'payday-income']);
    $kinsenas = ContentPostCategory::factory()->create(['slug' => 'using-kinsenas']);

    $this->actingAs($admin)
        ->post(route('admin.content.posts.store'), [
            'title' => 'Lock your sweldo',
            'slug' => 'lock-your-sweldo',
            'body' => '<p>Lock income after payday.</p>',
            'content_type' => 'article',
            'publish_scope' => 'internal',
            'status' => 'published',
            'category_ids' => [$payday->id, $kinsenas->id],
        ])
        ->assertRedirect();

    $post = ContentPost::query()->where('slug', 'lock-your-sweldo')->firstOrFail();

    expect($post->categories()->pluck('slug')->sort()->values()->all())
        ->toBe(['payday-income', 'using-kinsenas']);
});

it('forbids author from managing content post categories', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)
        ->get(route('admin.content.post-categories.index'))
        ->assertForbidden();
});
