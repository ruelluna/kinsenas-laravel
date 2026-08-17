<?php

use App\Enums\CommunityPostStatus;
use App\Models\CommunityPost;
use App\Models\User;
use App\Services\Content\CommunityModerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('approves a pending community post', function () {
    $admin = User::factory()->platformAdmin()->create();
    $author = User::factory()->create();
    $post = CommunityPost::factory()->pending()->create(['user_id' => $author->id]);

    $result = app(CommunityModerationService::class)->approve($post, $admin);

    expect($result->status)->toBe(CommunityPostStatus::Published)
        ->and($result->published_at)->not->toBeNull()
        ->and($result->moderated_by)->toBe($admin->id);
});

it('rejects a pending community post with a reason', function () {
    $admin = User::factory()->platformAdmin()->create();
    $author = User::factory()->create();
    $post = CommunityPost::factory()->pending()->create(['user_id' => $author->id]);

    $result = app(CommunityModerationService::class)->reject($post, $admin, 'Off-topic content');

    expect($result->status)->toBe(CommunityPostStatus::Rejected)
        ->and($result->rejection_reason)->toBe('Off-topic content');
});

it('prevents authors from moderating their own posts', function () {
    $author = User::factory()->platformAdmin()->create();
    $post = CommunityPost::factory()->pending()->create(['user_id' => $author->id]);

    app(CommunityModerationService::class)->approve($post, $author);
})->throws(InvalidArgumentException::class);
