<?php

namespace Database\Factories;

use App\Enums\ContentPostStatus;
use App\Models\SideHustleCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SideHustleCategory>
 */
class SideHustleCategoryFactory extends Factory
{
    protected $model = SideHustleCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('##'),
            'description' => fake()->sentence(12),
            'status' => ContentPostStatus::Published,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => ContentPostStatus::Draft,
        ]);
    }
}
