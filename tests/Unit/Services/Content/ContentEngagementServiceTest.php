<?php

use App\Enums\ContentEngagementSource;
use App\Enums\ContentReactionType;
use App\Models\ContentEngagementEvent;
use App\Models\ContentPost;
use App\Models\ContentReaction;
use App\Models\User;
use App\Services\Content\ContentEngagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(ContentEngagementService::class);
});

it('records a member view once per day', function () {
    $post = ContentPost::factory()->create();
    $user = User::factory()->create();

    expect($this->service->recordView($post, ContentEngagementSource::Internal, $user))->toBeTrue()
        ->and($this->service->recordView($post, ContentEngagementSource::Internal, $user))->toBeFalse();

    expect(ContentEngagementEvent::query()->count())->toBe(1);
});

it('dedupes anonymous teaser views by session hash', function () {
    $post = ContentPost::factory()->external()->create();

    expect($this->service->recordView($post, ContentEngagementSource::External, null, 'session-abc'))->toBeTrue()
        ->and($this->service->recordView($post, ContentEngagementSource::External, null, 'session-abc'))->toBeFalse();
});

it('toggles helpful reactions and returns counts', function () {
    $post = ContentPost::factory()->create();
    $user = User::factory()->create();

    $first = $this->service->toggleHelpfulReaction($post, $user);

    expect($first['reacted'])->toBeTrue()
        ->and($first['count'])->toBe(1);

    $second = $this->service->toggleHelpfulReaction($post, $user);

    expect($second['reacted'])->toBeFalse()
        ->and($second['count'])->toBe(0)
        ->and(ContentReaction::query()->count())->toBe(0);
});

it('reports whether a user has reacted', function () {
    $post = ContentPost::factory()->create();
    $user = User::factory()->create();

    expect($this->service->userHasReacted($post, $user))->toBeFalse();

    ContentReaction::factory()->create([
        'content_post_id' => $post->id,
        'user_id' => $user->id,
        'reaction_type' => ContentReactionType::Helpful,
    ]);

    expect($this->service->userHasReacted($post, $user))->toBeTrue();
});
