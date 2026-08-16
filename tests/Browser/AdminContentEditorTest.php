<?php

use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('shows the tiptap editor on the create post page', function () {
    $admin = User::factory()->platformAdmin()->create([
        'email' => 'editor-admin@kinsenas.test',
    ]);

    $page = visit('/login');
    browserLogin($page, $admin);

    $page = visit('/admin/content/posts/create');

    $page->assertSee('New post')
        ->assertPresent('@content-body-editor')
        ->assertNoSmoke();
});
