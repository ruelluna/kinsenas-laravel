<?php

use App\Models\ContentSeries;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('allows platform admin to create and update series', function () {
    $admin = User::factory()->create(['is_platform_admin' => true]);

    $this->actingAs($admin)
        ->post(route('admin.content.series.store'), [
            'title' => 'Savings stories',
            'slug' => 'savings-stories',
            'description' => 'Member education',
            'status' => 'published',
            'sort_order' => 1,
        ])
        ->assertRedirect();

    $series = ContentSeries::query()->where('slug', 'savings-stories')->firstOrFail();

    $this->actingAs($admin)
        ->put(route('admin.content.series.update', $series), [
            'title' => 'Savings stories updated',
            'slug' => 'savings-stories',
            'description' => 'Updated',
            'status' => 'published',
            'sort_order' => 2,
        ])
        ->assertRedirect();

    expect($series->fresh()->title)->toBe('Savings stories updated');
});

it('forbids non admin from managing series', function () {
    $user = User::factory()->create(['is_platform_admin' => false]);

    $this->actingAs($user)
        ->get(route('admin.content.series.index'))
        ->assertForbidden();
});

it('lists series for platform admin', function () {
    $admin = User::factory()->create(['is_platform_admin' => true]);
    ContentSeries::factory()->create(['title' => 'Listed series']);

    $this->actingAs($admin)
        ->get(route('admin.content.series.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/content/series/index')
            ->has('series', 1));
});
