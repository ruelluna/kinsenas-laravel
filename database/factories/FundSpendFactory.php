<?php

namespace Database\Factories;

use App\Enums\TransferStatus;
use App\Models\FundSpend;
use App\Models\SavingsCategory;
use App\Models\SavingsPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FundSpend>
 */
class FundSpendFactory extends Factory
{
    protected $model = FundSpend::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'savings_plan_id' => SavingsPlan::factory(),
            'category_id' => SavingsCategory::factory(),
            'amount_encrypted' => fake()->randomFloat(2, 10, 500),
            'description' => fake()->sentence(3),
            'spent_on' => fake()->date(),
            'bank_id' => null,
            'recipient_id' => null,
            'status' => TransferStatus::Confirmed,
            'confirmed_at' => now(),
            'confirmed_by_user_id' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => TransferStatus::Pending,
            'confirmed_at' => null,
            'confirmed_by_user_id' => null,
        ]);
    }
}
