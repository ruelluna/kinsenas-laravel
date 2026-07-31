<?php

namespace Database\Factories;

use App\Models\Bank;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bank>
 */
class BankFactory extends Factory
{
    protected $model = Bank::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'name' => fake()->randomElement(['BDO', 'BPI', 'GCash', 'Maya']),
            'account_label' => fake()->optional()->randomElement(['Savings', 'Payroll', 'Main']),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
