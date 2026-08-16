<?php

namespace Database\Factories;

use App\Enums\ContentPostStatus;
use App\Models\PodcastShow;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PodcastShow>
 */
class PodcastShowFactory extends Factory
{
    protected $model = PodcastShow::class;

    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'title' => ucwords($title),
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('##'),
            'description' => fake()->sentence(16),
            'cover_image_url' => null,
            'status' => ContentPostStatus::Published,
            'published_at' => now(),
            'sort_order' => 1,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => ContentPostStatus::Draft,
            'published_at' => null,
        ]);
    }
}
