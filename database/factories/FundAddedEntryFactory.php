<?php

namespace Database\Factories;

use App\Models\FundAddedEntry;
use App\Models\SavingsCategory;
use App\Models\SavingsPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FundAddedEntry>
 */
class FundAddedEntryFactory extends Factory
{
    protected $model = FundAddedEntry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'savings_plan_id' => SavingsPlan::factory(),
            'category_id' => SavingsCategory::factory(),
            'category_name' => fake()->words(2, true),
            'amount_encrypted' => fake()->randomFloat(2, 100, 10000),
            'added_on' => fake()->date(),
            'created_by_user_id' => null,
        ];
    }
}
