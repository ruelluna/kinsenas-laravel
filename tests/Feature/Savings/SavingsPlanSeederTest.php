<?php

use App\Models\SavingsCategory;
use App\Models\SavingsPlan;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('seeds a demo savings plan from the simple plan template', function () {
    $this->seed(SavingsPlanSeeder::class);

    $user = User::query()->where('email', 'simple-plan-demo@kinsenas.test')->firstOrFail();
    $plan = SavingsPlan::query()->where('team_id', $user->currentTeam->id)->firstOrFail();

    expect($plan->name)->toBe('The Simple Plan')
        ->and(SavingsCategory::query()->where('plan_id', $plan->id)->pluck('name')->all())
        ->toBe(['Everyday Fund', 'Savings']);
});

it('does not duplicate the demo plan when the seeder runs twice', function () {
    $this->seed(SavingsPlanSeeder::class);
    $this->seed(SavingsPlanSeeder::class);

    expect(SavingsPlan::query()->count())->toBe(1)
        ->and(User::query()->where('email', 'simple-plan-demo@kinsenas.test')->count())->toBe(1);
});
