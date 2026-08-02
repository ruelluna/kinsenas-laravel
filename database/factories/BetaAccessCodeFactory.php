<?php

namespace Database\Factories;

use App\Enums\BetaAccessCodeType;
use App\Models\BetaAccessCode;
use App\Models\BetaAccessCodeBatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BetaAccessCode>
 */
class BetaAccessCodeFactory extends Factory
{
    protected $model = BetaAccessCode::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'batch_id' => null,
            'code' => 'KINSENAS-'.Str::upper(fake()->bothify('??##')),
            'label' => fake()->words(3, true),
            'type' => BetaAccessCodeType::EventShared,
            'max_uses' => null,
            'redemptions_count' => 0,
            'expires_at' => null,
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }

    public function singleUse(): static
    {
        return $this->state(fn () => [
            'type' => BetaAccessCodeType::SingleUse,
            'code' => Str::upper(fake()->bothify('????-????')),
            'max_uses' => 1,
            'batch_id' => BetaAccessCodeBatch::factory()->state([
                'type' => BetaAccessCodeType::SingleUse,
            ]),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }

    public function maxedOut(): static
    {
        return $this->state(fn () => [
            'max_uses' => 1,
            'redemptions_count' => 1,
        ]);
    }
}
