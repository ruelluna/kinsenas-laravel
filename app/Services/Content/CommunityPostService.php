<?php

namespace App\Services\Content;

use App\Enums\CommunityPostStatus;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CommunityPostService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $author, array $data): CommunityPost
    {
        $categoryIds = $data['category_ids'] ?? [];
        unset($data['category_ids']);

        $post = CommunityPost::query()->create([
            ...$data,
            'user_id' => $author->id,
            'slug' => $this->uniqueSlug($data['title']),
            'status' => CommunityPostStatus::Pending,
        ]);

        $post->categories()->sync($categoryIds);

        return $post->fresh(['categories', 'author']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(CommunityPost $post, User $author, array $data): CommunityPost
    {
        if (! $post->isOwnedBy($author)) {
            throw new InvalidArgumentException('You can only edit your own posts.');
        }

        if ($post->status !== CommunityPostStatus::Pending) {
            throw new InvalidArgumentException('Only pending posts can be edited.');
        }

        $post->update($data);

        return $post->fresh(['categories', 'author']);
    }

    public function withdraw(CommunityPost $post, User $author): CommunityPost
    {
        if (! $post->isOwnedBy($author)) {
            throw new InvalidArgumentException('You can only withdraw your own posts.');
        }

        if (! in_array($post->status, [CommunityPostStatus::Pending, CommunityPostStatus::Published], true)) {
            throw new InvalidArgumentException('This post cannot be withdrawn.');
        }

        $post->update([
            'status' => CommunityPostStatus::Withdrawn,
            'published_at' => null,
        ]);

        return $post->fresh();
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $suffix = 1;

        while (CommunityPost::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
