<?php

namespace Database\Factories;

use App\Enums\BankSpaceRole;
use App\Models\Bank;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Bank>
 */
class BankFactory extends Factory
{
    protected $model = Bank::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'name' => fake()->randomElement(['BDO', 'BPI', 'GCash', 'Maya']),
            'account_label' => fake()->optional()->randomElement(['Savings', 'Payroll', 'Main']),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function mainSpace(): static
    {
        return $this->state(fn (): array => [
            'space_role' => BankSpaceRole::Main,
            'account_label' => 'Main account',
        ]);
    }

    public function savingsSpace(?string $label = null): static
    {
        return $this->state(fn (): array => [
            'space_role' => BankSpaceRole::SavingsSpace,
            'account_label' => $label ?? fake()->randomElement(['Vacation', 'Emergency', 'GoSave 1']),
        ]);
    }

    public function grouped(): static
    {
        $groupId = (string) Str::uuid7();

        return $this->state(fn (): array => [
            'bank_account_group_id' => $groupId,
        ]);
    }
}
