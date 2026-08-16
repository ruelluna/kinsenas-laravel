<?php

use App\Enums\ContentEngagementEventType;
use App\Enums\ContentEngagementSource;
use App\Models\ContentEngagementEvent;
use App\Models\ContentPost;
use App\Models\ContentReaction;
use App\Models\User;
use App\Services\Content\ContentStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('summarizes views and reactions for a date window', function () {
    $post = ContentPost::factory()->create();
    $user = User::factory()->create();

    ContentEngagementEvent::factory()->create([
        'content_post_id' => $post->id,
        'user_id' => $user->id,
        'event_type' => ContentEngagementEventType::Viewed,
        'source' => ContentEngagementSource::Internal,
        'created_at' => now()->subDays(2),
    ]);

    ContentEngagementEvent::factory()->create([
        'content_post_id' => $post->id,
        'user_id' => null,
        'event_type' => ContentEngagementEventType::Viewed,
        'source' => ContentEngagementSource::External,
        'created_at' => now()->subDays(40),
    ]);

    ContentReaction::factory()->create([
        'content_post_id' => $post->id,
        'user_id' => $user->id,
    ]);

    $service = app(ContentStatsService::class);

    expect($service->summary(7))->toMatchArray([
        'totalViews' => 1,
        'uniqueViewers' => 1,
        'totalReactions' => 1,
    ]);

    expect($service->summary(null)['totalViews'])->toBe(2);
});

it('ranks top posts by views', function () {
    $popular = ContentPost::factory()->create(['title' => 'Popular']);
    $quiet = ContentPost::factory()->create(['title' => 'Quiet']);

    ContentEngagementEvent::factory()->count(3)->create([
        'content_post_id' => $popular->id,
        'event_type' => ContentEngagementEventType::Viewed,
    ]);

    ContentEngagementEvent::factory()->create([
        'content_post_id' => $quiet->id,
        'event_type' => ContentEngagementEventType::Viewed,
    ]);

    $top = app(ContentStatsService::class)->topPosts(5);

    expect($top->first()['post']->id)->toBe($popular->id)
        ->and($top->first()['views'])->toBe(3);
});
