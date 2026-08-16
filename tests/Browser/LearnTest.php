<?php

use App\Models\ContentPost;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('opens a learn post and toggles helpful reaction', function () {
    $member = User::factory()->create([
        'email' => 'learn-member@kinsenas.test',
    ]);
    grantTeamSubscriptionAccess($member->currentTeam);

    $post = ContentPost::factory()->create([
        'slug' => 'browser-helpful-post',
        'title' => 'Browser helpful post',
        'body' => 'Member-only body for browser test.',
        'publish_scope' => 'both',
    ]);

    $page = visit('/login');
    browserLogin($page, $member);

    $page = visit("/learn/posts/{$post->slug}");

    $page->assertSee('Browser helpful post')
        ->assertSee('Member-only body for browser test.')
        ->click('@learn-helpful-button')
        ->assertSee('1 person found this helpful')
        ->assertNoSmoke();
});
