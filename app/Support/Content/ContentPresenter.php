<?php

namespace App\Support\Content;

use App\Models\ContentPost;
use App\Models\ContentSeries;

class ContentPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function postSummary(ContentPost $post, bool $includeBody = false): array
    {
        $data = [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'contentType' => $post->content_type->value,
            'contentTypeLabel' => $post->content_type->label(),
            'publishScope' => $post->publish_scope->value,
            'status' => $post->status->value,
            'coverImageUrl' => $post->cover_image_url,
            'videoEmbedUrl' => $post->video_embed_url,
            'readingTimeMinutes' => $post->reading_time_minutes,
            'publishedAt' => $post->published_at?->toIso8601String(),
            'series' => $post->series ? self::seriesSummary($post->series) : null,
            'episodeNumber' => $post->episode_number,
            'postAs' => $post->post_as,
            'bylineName' => ContentByline::forPost($post->post_as, $post->author),
            'authorName' => ContentByline::forPost($post->post_as, $post->author),
        ];

        if ($includeBody) {
            $data['body'] = $post->body;
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public static function seriesSummary(ContentSeries $series): array
    {
        return [
            'id' => $series->id,
            'title' => $series->title,
            'slug' => $series->slug,
            'description' => $series->description,
            'coverImageUrl' => $series->cover_image_url,
            'status' => $series->status->value,
            'publishedAt' => $series->published_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function postAdmin(ContentPost $post): array
    {
        return [
            ...self::postSummary($post, includeBody: true),
            'contentSeriesId' => $post->content_series_id,
            'authorId' => $post->author_id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function seriesAdmin(ContentSeries $series): array
    {
        return [
            ...self::seriesSummary($series),
            'sortOrder' => $series->sort_order,
            'postsCount' => $series->posts()->count(),
        ];
    }
}
