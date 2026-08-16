<?php

namespace Database\Factories;

use App\Enums\ContentPostStatus;
use App\Enums\ContentPublishScope;
use App\Enums\SideHustleCapitalTier;
use App\Enums\SideHustleDifficulty;
use App\Models\SideHustle;
use App\Models\SideHustleCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SideHustle>
 */
class SideHustleFactory extends Factory
{
    protected $model = SideHustle::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'side_hustle_category_id' => SideHustleCategory::factory(),
            'title' => rtrim($title, '.'),
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'excerpt' => fake()->sentence(14),
            'body' => '<p>'.fake()->paragraph(3).'</p>',
            'cover_image_url' => null,
            'difficulty' => SideHustleDifficulty::Beginner,
            'capital_tier' => SideHustleCapitalTier::Low,
            'startup_capital_min' => 500,
            'startup_capital_max' => 5000,
            'time_commitment_hours_min' => 5,
            'time_commitment_hours_max' => 15,
            'skills' => ['Customer service', 'Basic math'],
            'equipment' => ['Mobile phone', 'Small table'],
            'publish_scope' => ContentPublishScope::Both,
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
