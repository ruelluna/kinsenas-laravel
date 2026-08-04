<?php

namespace Database\Factories;

use App\Enums\IncomeDistributionTodoStatus;
use App\Models\IncomeDistributionTodo;
use App\Models\IncomePeriod;
use App\Models\SavingsCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IncomeDistributionTodo>
 */
class IncomeDistributionTodoFactory extends Factory
{
    protected $model = IncomeDistributionTodo::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'income_period_id' => IncomePeriod::factory(),
            'category_id' => SavingsCategory::factory(),
            'bank_id' => null,
            'amount_encrypted' => '1000.00',
            'status' => IncomeDistributionTodoStatus::Pending,
            'completed_at' => null,
            'completed_by_user_id' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IncomeDistributionTodoStatus::Completed,
            'completed_at' => now(),
        ]);
    }
}
