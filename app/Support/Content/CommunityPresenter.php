<?php

namespace App\Support\Content;

use App\Models\CommunityCategory;
use App\Models\CommunityPost;
use App\Support\UserProfilePhoto;

class CommunityPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function categorySummary(CommunityCategory $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'status' => $category->status->value,
            'sortOrder' => $category->sort_order,
        ];
    }

    public static function categoryAdmin(CommunityCategory $category): array
    {
        return [
            ...self::categorySummary($category),
            'postsCount' => $category->posts_count ?? $category->posts()->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function postSummary(CommunityPost $post, bool $includeBody = false): array
    {
        $author = $post->author;

        $data = [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'coverImageUrl' => $post->cover_image_url,
            'status' => $post->status->value,
            'statusLabel' => $post->status->label(),
            'rejectionReason' => $post->rejection_reason,
            'publishedAt' => $post->published_at?->toIso8601String(),
            'createdAt' => $post->created_at?->toIso8601String(),
            'authorName' => $author?->name ?? 'Member',
            'authorAvatarUrl' => UserProfilePhoto::url($author),
            'categories' => $post->relationLoaded('categories')
                ? $post->categories->map(fn (CommunityCategory $category) => self::categorySummary($category))->values()->all()
                : [],
            'isOwnPost' => false,
        ];

        if ($includeBody) {
            $data['body'] = $post->body;
        }

        return $data;
    }
}
