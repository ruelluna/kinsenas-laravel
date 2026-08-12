<?php

namespace Database\Factories;

use App\Models\FundSpend;
use App\Models\FundSpendReimbursement;
use App\Models\SavingsCategory;
use App\Models\SavingsPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FundSpendReimbursement>
 */
class FundSpendReimbursementFactory extends Factory
{
    protected $model = FundSpendReimbursement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fund_spend_id' => FundSpend::factory(),
            'savings_plan_id' => SavingsPlan::factory(),
            'category_id' => SavingsCategory::factory(),
            'amount_encrypted' => fake()->randomFloat(2, 10, 500),
            'received_on' => fake()->date(),
            'bank_id' => null,
            'notes' => null,
            'created_by_user_id' => null,
        ];
    }
}
