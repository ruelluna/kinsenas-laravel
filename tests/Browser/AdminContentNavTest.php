<?php

use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('navigates content admin tabs from posts to series', function () {
    $admin = User::factory()->platformAdmin()->create([
        'email' => 'content-admin@kinsenas.test',
    ]);

    $page = visit('/login');
    browserLogin($page, $admin);

    $page = visit('/admin/content/posts');

    $page->assertSee('Content posts')
        ->click('@content-admin-tab-series')
        ->assertPathIs('/admin/content/series')
        ->assertSee('Content series')
        ->assertSee('New series')
        ->assertNoSmoke();
});
