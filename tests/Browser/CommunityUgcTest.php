<?php

use App\Enums\CommunityPostStatus;
use App\Models\CommunityCategory;
use App\Models\CommunityPost;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('approves a pending community post from the admin queue', function () {
    $admin = User::factory()->platformAdmin()->create([
        'email' => 'community-admin@kinsenas.test',
    ]);
    $author = User::factory()->create();
    $category = CommunityCategory::factory()->create();

    $post = CommunityPost::factory()->pending()->create([
        'user_id' => $author->id,
        'title' => 'Browser moderation story',
        'slug' => 'browser-moderation-story',
    ]);
    $post->categories()->sync([$category->id]);

    $page = visit('/login');
    browserLogin($page, $admin);

    $page = visit('/admin/content/community/settings#moderation');

    $page->assertSee('Browser moderation story')
        ->click('@community-approve-button')
        ->assertNoSmoke();

    expect($post->fresh()->status)->toBe(CommunityPostStatus::Published);
});

it('shows published community posts to subscribed members', function () {
    $member = User::factory()->create([
        'email' => 'community-member@kinsenas.test',
    ]);
    grantTeamSubscriptionAccess($member->currentTeam);

    $post = CommunityPost::factory()->published()->create([
        'title' => 'Visible community story',
        'slug' => 'visible-community-story',
    ]);

    $page = visit('/login');
    browserLogin($page, $member);

    $page = visit('/learn/community');

    $page->assertSee('Visible community story')
        ->assertNoSmoke();
});
