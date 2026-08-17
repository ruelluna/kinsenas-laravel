<?php

namespace App\Services\Content;

use App\Enums\CommunityPostStatus;
use App\Models\CommunityPost;
use App\Models\User;
use InvalidArgumentException;

class CommunityModerationService
{
    public function approve(CommunityPost $post, User $moderator): CommunityPost
    {
        $this->ensureCanModerate($post, $moderator);

        $post->update([
            'status' => CommunityPostStatus::Published,
            'published_at' => now(),
            'moderated_by' => $moderator->id,
            'moderated_at' => now(),
            'rejection_reason' => null,
        ]);

        return $post->fresh(['category', 'author']);
    }

    public function reject(CommunityPost $post, User $moderator, string $reason): CommunityPost
    {
        $this->ensureCanModerate($post, $moderator);

        $post->update([
            'status' => CommunityPostStatus::Rejected,
            'rejection_reason' => trim($reason),
            'moderated_by' => $moderator->id,
            'moderated_at' => now(),
            'published_at' => null,
        ]);

        return $post->fresh(['category', 'author']);
    }

    private function ensureCanModerate(CommunityPost $post, User $moderator): void
    {
        if ($post->status !== CommunityPostStatus::Pending) {
            throw new InvalidArgumentException('Only pending posts can be moderated.');
        }

        if ($post->isOwnedBy($moderator)) {
            throw new InvalidArgumentException('Authors cannot moderate their own posts.');
        }

        if (! $moderator->canManagePlatform()) {
            throw new InvalidArgumentException('Only platform admins can moderate community posts.');
        }
    }
}
