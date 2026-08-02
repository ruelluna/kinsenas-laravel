<?php

namespace Database\Factories;

use App\Enums\BetaAccessCodeType;
use App\Models\BetaAccessCodeBatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BetaAccessCodeBatch>
 */
class BetaAccessCodeBatchFactory extends Factory
{
    protected $model = BetaAccessCodeBatch::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'type' => BetaAccessCodeType::SingleUse,
            'quantity' => 10,
            'created_by' => User::factory(),
        ];
    }

    public function eventShared(): static
    {
        return $this->state(fn () => [
            'type' => BetaAccessCodeType::EventShared,
            'quantity' => 1,
        ]);
    }
}
