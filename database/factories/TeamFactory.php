<?php

namespace Database\Factories;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterCreating(function (Team $team): void {
            if ($team->subscription()->exists()) {
                return;
            }

            $plan = SubscriptionPlan::query()->first();

            if ($plan === null) {
                return;
            }

            Subscription::query()->updateOrCreate(
                ['team_id' => $team->id],
                [
                    'plan_id' => $plan->id,
                    'status' => SubscriptionStatus::Active,
                    'trial_ends_at' => null,
                    'current_period_ends_at' => now()->addMonth(),
                ],
            );
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'is_personal' => false,
        ];
    }

    /**
     * Indicate that the team is a personal team.
     */
    public function personal(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_personal' => true,
        ]);
    }

    /**
     * Indicate that the team has been deleted.
     */
    public function trashed(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}
