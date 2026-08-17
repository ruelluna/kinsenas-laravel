<?php

namespace Database\Factories;

use App\Enums\CommunityPostStatus;
use App\Models\CommunityCategory;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CommunityPost>
 */
class CommunityPostFactory extends Factory
{
    protected $model = CommunityPost::class;

    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'excerpt' => fake()->sentence(16),
            'body' => '<p>'.fake()->paragraph(3).'</p>',
            'cover_image_url' => null,
            'status' => CommunityPostStatus::Pending,
            'rejection_reason' => null,
            'published_at' => null,
            'moderated_by' => null,
            'moderated_at' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (CommunityPost $post): void {
            if ($post->categories()->count() === 0) {
                $post->categories()->attach(CommunityCategory::factory()->create());
            }
        });
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => CommunityPostStatus::Published,
            'published_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => CommunityPostStatus::Pending,
            'published_at' => null,
        ]);
    }
}
