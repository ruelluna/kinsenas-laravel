<?php

namespace Database\Factories;

use App\Enums\ContentReactionType;
use App\Models\ContentPost;
use App\Models\ContentReaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentReaction>
 */
class ContentReactionFactory extends Factory
{
    protected $model = ContentReaction::class;

    public function definition(): array
    {
        return [
            'content_post_id' => ContentPost::factory(),
            'user_id' => User::factory(),
            'reaction_type' => ContentReactionType::Helpful,
        ];
    }
}
