<?php

namespace Database\Factories;

use App\Enums\TransferStatus;
use App\Models\Bank;
use App\Models\FundTransfer;
use App\Models\SavingsCategory;
use App\Models\SavingsPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FundTransfer>
 */
class FundTransferFactory extends Factory
{
    protected $model = FundTransfer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'savings_plan_id' => SavingsPlan::factory(),
            'category_id' => SavingsCategory::factory(),
            'bank_id' => Bank::factory(),
            'amount_encrypted' => fake()->randomFloat(2, 100, 5000),
            'description' => fake()->sentence(3),
            'transferred_on' => fake()->date(),
            'status' => TransferStatus::Pending,
            'confirmed_at' => null,
            'confirmed_by_user_id' => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => [
            'status' => TransferStatus::Confirmed,
            'confirmed_at' => now(),
        ]);
    }
}
