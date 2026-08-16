<?php

namespace App\Services\Content;

use App\Enums\ContentEngagementEventType;
use App\Enums\ContentEngagementSource;
use App\Enums\ContentPostStatus;
use App\Enums\ContentReactionType;
use App\Models\ContentEngagementEvent;
use App\Models\ContentPost;
use App\Models\ContentReaction;
use App\Models\User;
use Illuminate\Support\Carbon;

class ContentEngagementService
{
    public function recordView(
        ContentPost $post,
        ContentEngagementSource $source,
        ?User $user = null,
        ?string $sessionHash = null,
    ): bool {
        if ($post->status !== ContentPostStatus::Published) {
            return false;
        }

        $startOfDay = Carbon::today();

        if ($user !== null) {
            $exists = ContentEngagementEvent::query()
                ->where('content_post_id', $post->id)
                ->where('user_id', $user->id)
                ->where('event_type', ContentEngagementEventType::Viewed)
                ->where('created_at', '>=', $startOfDay)
                ->exists();

            if ($exists) {
                return false;
            }
        } elseif ($sessionHash !== null) {
            $exists = ContentEngagementEvent::query()
                ->where('content_post_id', $post->id)
                ->whereNull('user_id')
                ->where('event_type', ContentEngagementEventType::Viewed)
                ->where('created_at', '>=', $startOfDay)
                ->where('metadata->session_hash', $sessionHash)
                ->exists();

            if ($exists) {
                return false;
            }
        }

        ContentEngagementEvent::query()->create([
            'content_post_id' => $post->id,
            'user_id' => $user?->id,
            'event_type' => ContentEngagementEventType::Viewed,
            'source' => $source,
            'metadata' => $sessionHash !== null ? ['session_hash' => $sessionHash] : null,
        ]);

        return true;
    }

    /**
     * @return array{reacted: bool, count: int}
     */
    public function toggleHelpfulReaction(ContentPost $post, User $user): array
    {
        $existing = ContentReaction::query()
            ->where('content_post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing !== null) {
            $existing->delete();

            return [
                'reacted' => false,
                'count' => $this->helpfulCount($post),
            ];
        }

        ContentReaction::query()->create([
            'content_post_id' => $post->id,
            'user_id' => $user->id,
            'reaction_type' => ContentReactionType::Helpful,
        ]);

        return [
            'reacted' => true,
            'count' => $this->helpfulCount($post),
        ];
    }

    public function helpfulCount(ContentPost $post): int
    {
        return ContentReaction::query()
            ->where('content_post_id', $post->id)
            ->where('reaction_type', ContentReactionType::Helpful)
            ->count();
    }

    public function userHasReacted(ContentPost $post, ?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return ContentReaction::query()
            ->where('content_post_id', $post->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * @return list<string>
     */
    public function viewedPostIdsForUser(User $user): array
    {
        return ContentEngagementEvent::query()
            ->where('user_id', $user->id)
            ->where('event_type', ContentEngagementEventType::Viewed)
            ->pluck('content_post_id')
            ->unique()
            ->values()
            ->all();
    }
}
