<?php

namespace Database\Factories;

use App\Enums\ContentSeriesStatus;
use App\Models\ContentSeries;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ContentSeries>
 */
class ContentSeriesFactory extends Factory
{
    protected $model = ContentSeries::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => rtrim($title, '.'),
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'description' => fake()->paragraph(),
            'cover_image_url' => null,
            'status' => ContentSeriesStatus::Published,
            'published_at' => now(),
            'sort_order' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => ContentSeriesStatus::Draft,
            'published_at' => null,
        ]);
    }
}
