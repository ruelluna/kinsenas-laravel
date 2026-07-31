<?php

namespace Database\Factories;

use App\Enums\BillingInterval;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPlanPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionPlanPrice>
 */
class SubscriptionPlanPriceFactory extends Factory
{
    protected $model = SubscriptionPlanPrice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plan_id' => SubscriptionPlan::factory(),
            'interval' => BillingInterval::Monthly,
            'amount' => 29900,
            'currency' => 'PHP',
            'is_active' => true,
        ];
    }

    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => [
            'interval' => BillingInterval::Yearly,
            'amount' => 299000,
        ]);
    }
}
