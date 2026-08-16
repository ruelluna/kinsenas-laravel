<?php

namespace Database\Factories;

use App\Enums\ContentPostStatus;
use App\Enums\ContentPublishScope;
use App\Models\PodcastEpisode;
use App\Models\PodcastShow;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PodcastEpisode>
 */
class PodcastEpisodeFactory extends Factory
{
    protected $model = PodcastEpisode::class;

    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'podcast_show_id' => PodcastShow::factory(),
            'episode_number' => fake()->unique()->numberBetween(1, 100),
            'title' => rtrim($title, '.'),
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'excerpt' => fake()->sentence(12),
            'show_notes' => '<p>'.fake()->paragraph(2).'</p>',
            'audio_embed_url' => 'https://open.spotify.com/embed/episode/example',
            'duration_minutes' => fake()->numberBetween(15, 60),
            'publish_scope' => ContentPublishScope::Both,
            'status' => ContentPostStatus::Published,
            'published_at' => now(),
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
}
