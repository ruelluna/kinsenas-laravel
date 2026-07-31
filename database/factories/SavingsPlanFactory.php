<?php

namespace Database\Factories;

use App\Models\SavingsPlan;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavingsPlan>
 */
class SavingsPlanFactory extends Factory
{
    protected $model = SavingsPlan::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'created_by_user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'currency' => 'PHP',
            'is_shared_with_team' => false,
            'allow_editing_spends' => false,
        ];
    }
}
