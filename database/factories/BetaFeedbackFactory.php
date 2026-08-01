<?php

namespace Database\Factories;

use App\Enums\BetaFeedbackCategory;
use App\Models\BetaFeedback;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BetaFeedback>
 */
class BetaFeedbackFactory extends Factory
{
    protected $model = BetaFeedback::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'team_id' => Team::factory(),
            'message' => fake()->paragraph(),
            'category' => fake()->randomElement(BetaFeedbackCategory::cases()),
        ];
    }
}
