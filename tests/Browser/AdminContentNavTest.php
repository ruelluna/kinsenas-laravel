<?php

use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('navigates content entity tabs from posts list to settings', function () {
    $admin = User::factory()->platformAdmin()->create([
        'email' => 'content-admin@kinsenas.test',
    ]);

    $page = visit('/login');
    browserLogin($page, $admin);

    $page = visit('/admin/content/posts');

    $page->assertSee('Content posts')
        ->click('@content-section-tab-posts-settings')
        ->assertPathIs('/admin/content/posts/settings')
        ->assertSee('Post categories')
        ->assertNoSmoke();
});

it('navigates entity tabs from posts to series', function () {
    $admin = User::factory()->platformAdmin()->create([
        'email' => 'content-admin@kinsenas.test',
    ]);

    $page = visit('/login');
    browserLogin($page, $admin);

    $page = visit('/admin/content/posts');

    $page->click('@content-entity-tab-series')
        ->assertPathIs('/admin/content/series')
        ->assertSee('Content series')
        ->assertNoSmoke();
});
