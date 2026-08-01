<?php

namespace Database\Factories;

use App\Enums\SurveyLanguage;
use App\Enums\SurveyResultSlug;
use App\Models\SurveyResponse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyResponse>
 */
class SurveyResponseFactory extends Factory
{
    protected $model = SurveyResponse::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'language' => fake()->randomElement(SurveyLanguage::cases()),
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->name(),
            'result' => fake()->randomElement(SurveyResultSlug::cases()),
            'answers' => [
                'q1' => 'employee',
                'q2' => 'single',
                'q3' => 'none',
                'q4' => 'pay_bills',
                'q5' => ['bills', 'rent'],
                'q6' => 'sometimes',
                'q7' => 'too_many_bills',
                'q8' => [],
                'q9' => 'split_income',
                'q10' => 'early_access',
            ],
            'completed_at' => now(),
            'ip_address' => fake()->ipv4(),
        ];
    }
}
