<?php

namespace Database\Factories;

use App\Enums\CategoryAllocationType;
use App\Models\SavingsCategory;
use App\Models\SavingsPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavingsCategory>
 */
class SavingsCategoryFactory extends Factory
{
    protected $model = SavingsCategory::class;

    public function definition(): array
    {
        return [
            'plan_id' => SavingsPlan::factory(),
            'name' => fake()->words(2, true),
            'allocation_type' => CategoryAllocationType::Percentage,
            'percentage' => fake()->randomFloat(2, 5, 30),
            'sort_order' => 0,
        ];
    }

    public function deduction(): static
    {
        return $this->state(fn () => [
            'allocation_type' => CategoryAllocationType::Deduction,
            'percentage' => null,
        ]);
    }
}
