<?php

namespace Database\Factories;

use App\Enums\ContentPostStatus;
use App\Enums\ContentPostType;
use App\Enums\ContentPublishScope;
use App\Models\ContentPost;
use App\Models\ContentSeries;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ContentPost>
 */
class ContentPostFactory extends Factory
{
    protected $model = ContentPost::class;

    public function definition(): array
    {
        $title = fake()->sentence(4);
        $body = fake()->paragraphs(3, true);

        return [
            'content_series_id' => null,
            'episode_number' => null,
            'title' => rtrim($title, '.'),
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'excerpt' => fake()->sentence(12),
            'body' => $body,
            'content_type' => ContentPostType::Article,
            'publish_scope' => ContentPublishScope::Both,
            'status' => ContentPostStatus::Published,
            'video_embed_url' => null,
            'cover_image_url' => null,
            'author_id' => User::factory(),
            'metadata' => null,
            'published_at' => now(),
            'reading_time_minutes' => max(1, (int) ceil(str_word_count($body) / 200)),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => ContentPostStatus::Draft,
            'published_at' => null,
        ]);
    }

    public function internal(): static
    {
        return $this->state(fn () => [
            'publish_scope' => ContentPublishScope::Internal,
        ]);
    }

    public function external(): static
    {
        return $this->state(fn () => [
            'publish_scope' => ContentPublishScope::External,
        ]);
    }

    public function reminder(): static
    {
        return $this->state(fn () => [
            'content_type' => ContentPostType::Reminder,
        ]);
    }

    public function episode(int $number = 1, ?ContentSeries $series = null): static
    {
        return $this->state(fn () => [
            'content_series_id' => $series?->id ?? ContentSeries::factory(),
            'episode_number' => $number,
            'content_type' => ContentPostType::Episode,
        ]);
    }
}
