<?php

namespace Database\Factories;

use App\Enums\BankInstitutionType;
use App\Models\BankInstitution;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BankInstitution>
 */
class BankInstitutionFactory extends Factory
{
    protected $model = BankInstitution::class;

    public function definition(): array
    {
        $name = fake()->unique()->company().' Bank';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'type' => BankInstitutionType::Bank,
            'logo_path' => null,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function eWallet(): static
    {
        return $this->state(fn () => [
            'type' => BankInstitutionType::EWallet,
        ]);
    }
}
