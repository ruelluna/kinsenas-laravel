<?php

namespace App\Services\Content;

use App\Enums\ContentPostStatus;
use App\Enums\ContentPostType;
use App\Enums\ContentPublishScope;
use App\Enums\ContentSeriesStatus;
use App\Enums\UserActivityAction;
use App\Models\ContentPost;
use App\Models\ContentSeries;
use App\Models\User;
use App\Services\Audit\UserActivityLogger;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ContentPublishService
{
    public function __construct(private UserActivityLogger $activityLogger) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createPost(array $attributes, User $author): ContentPost
    {
        $categoryIds = $attributes['category_ids'] ?? [];
        unset($attributes['category_ids']);

        $attributes = $this->preparePostAttributes($attributes, $author);

        $post = ContentPost::query()->create($attributes);
        $post->categories()->sync($categoryIds);

        if ($post->status === ContentPostStatus::Published) {
            $this->logPostPublished($post, $author);
        }

        return $post->load('categories');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updatePost(ContentPost $post, array $attributes, User $author): ContentPost
    {
        $wasPublished = $post->status === ContentPostStatus::Published;
        $categoryIds = $attributes['category_ids'] ?? null;
        unset($attributes['category_ids']);

        $attributes = $this->preparePostAttributes($attributes, $author, $post);

        $post->update($attributes);

        if ($categoryIds !== null) {
            $post->categories()->sync($categoryIds);
        }

        $post->refresh()->load('categories');

        if (! $wasPublished && $post->status === ContentPostStatus::Published) {
            $this->logPostPublished($post, $author);
        } else {
            $this->activityLogger->log(
                UserActivityAction::ContentPostUpdated,
                'Updated content post :properties.title',
                $author,
                $post,
                ['title' => $post->title, 'slug' => $post->slug],
            );
        }

        return $post;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createSeries(array $attributes): ContentSeries
    {
        $series = ContentSeries::query()->create($attributes);

        if ($series->status === ContentSeriesStatus::Published) {
            $this->activityLogger->log(
                UserActivityAction::ContentSeriesPublished,
                'Published content series :properties.title',
                auth()->user(),
                $series,
                ['title' => $series->title, 'slug' => $series->slug],
            );
        }

        return $series;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateSeries(ContentSeries $series, array $attributes): ContentSeries
    {
        $wasPublished = $series->status === ContentSeriesStatus::Published;
        $series->update($attributes);
        $series->refresh();

        if (! $wasPublished && $series->status === ContentSeriesStatus::Published) {
            $this->activityLogger->log(
                UserActivityAction::ContentSeriesPublished,
                'Published content series :properties.title',
                auth()->user(),
                $series,
                ['title' => $series->title, 'slug' => $series->slug],
            );
        } else {
            $this->activityLogger->log(
                UserActivityAction::ContentSeriesUpdated,
                'Updated content series :properties.title',
                auth()->user(),
                $series,
                ['title' => $series->title, 'slug' => $series->slug],
            );
        }

        return $series;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function preparePostAttributes(array $attributes, User $author, ?ContentPost $existing = null): array
    {
        if (! empty($attributes['content_series_id'])) {
            $attributes['content_type'] = ContentPostType::Episode->value;
        }

        if (! empty($attributes['body'])) {
            $attributes['reading_time_minutes'] = max(1, (int) ceil(str_word_count(strip_tags((string) $attributes['body'])) / 200));
        }

        if (($attributes['status'] ?? $existing?->status?->value) === ContentPostStatus::Published->value
            && empty($attributes['published_at'])
            && ($existing === null || $existing->published_at === null)) {
            $attributes['published_at'] = now();
        }

        $scope = ContentPublishScope::tryFrom($attributes['publish_scope'] ?? $existing?->publish_scope?->value ?? ContentPublishScope::Internal->value);

        if ($scope?->isPublicTeaser() && empty($attributes['excerpt']) && empty($existing?->excerpt)) {
            throw ValidationException::withMessages([
                'excerpt' => __('An excerpt is required for external or both publish scopes.'),
            ]);
        }

        if ($existing === null || array_key_exists('author_id', $attributes)) {
            $attributes['author_id'] = $this->resolveAuthorId($attributes, $author, $existing);
        }

        if (empty($attributes['slug']) && ! empty($attributes['title'])) {
            $attributes['slug'] = Str::slug($attributes['title']);
        }

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function resolveAuthorId(array $attributes, User $actor, ?ContentPost $existing): int
    {
        if ($actor->canManagePlatform() && filled($attributes['author_id'] ?? null)) {
            return (int) $attributes['author_id'];
        }

        if ($existing !== null && $existing->author_id !== null) {
            return $existing->author_id;
        }

        return $actor->id;
    }

    private function logPostPublished(ContentPost $post, User $author): void
    {
        $this->activityLogger->log(
            UserActivityAction::ContentPostPublished,
            'Published content post :properties.title',
            $author,
            $post,
            ['title' => $post->title, 'slug' => $post->slug],
        );
    }
}
