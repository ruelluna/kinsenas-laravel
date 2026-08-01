<?php

namespace Database\Factories;

use App\Enums\PaymentSubmissionStatus;
use App\Models\PaymentSubmission;
use App\Models\SubscriptionPlanPrice;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentSubmission>
 */
class PaymentSubmissionFactory extends Factory
{
    protected $model = PaymentSubmission::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'team_id' => Team::factory(),
            'plan_price_id' => SubscriptionPlanPrice::factory(),
            'reference_number' => strtoupper(fake()->bothify('REF-####??')),
            'proof_image_path' => null,
            'status' => PaymentSubmissionStatus::Pending,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'notes' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentSubmissionStatus::Pending,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'notes' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentSubmissionStatus::Approved,
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentSubmissionStatus::Rejected,
            'reviewed_at' => now(),
            'notes' => fake()->sentence(),
        ]);
    }
}
