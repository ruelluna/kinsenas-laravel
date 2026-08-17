<?php

use App\Enums\FinanceActivityTier;
use App\Models\FundSpend;
use App\Models\FundTransfer;
use App\Models\IncomeAllocation;
use App\Models\IncomePeriod;
use App\Models\SavingsCategory;
use App\Models\SavingsPlan;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\DemoAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('seeds a demo account with the seven buckets plan and six months of history', function () {
    $this->seed(DemoAccountSeeder::class);

    $user = User::query()->where('email', 'demo@kinsenas.test')->firstOrFail();
    $plan = SavingsPlan::query()->where('team_id', $user->currentTeam->id)->firstOrFail();

    expect($plan->name)->toBe('7 Buckets')
        ->and(SavingsCategory::query()->where('plan_id', $plan->id)->count())->toBe(7)
        ->and(IncomePeriod::query()->where('plan_id', $plan->id)->count())->toBe(6)
        ->and(IncomePeriod::query()->where('plan_id', $plan->id)->where('is_locked', true)->count())->toBe(6)
        ->and(IncomeAllocation::query()->whereHas('incomePeriod', fn ($q) => $q->where('plan_id', $plan->id))->count())->toBe(42)
        ->and(FundSpend::query()->where('savings_plan_id', $plan->id)->count())->toBeGreaterThan(0)
        ->and(FundTransfer::query()->where('savings_plan_id', $plan->id)->count())->toBeGreaterThan(0);

    $user->refresh();

    expect($user->finance_activity_tier)->toBeIn([
        FinanceActivityTier::Active,
        FinanceActivityTier::Activated,
    ]);
});

it('does not duplicate demo data when the seeder runs twice', function () {
    $this->seed(DemoAccountSeeder::class);
    $this->seed(DemoAccountSeeder::class);

    $user = User::query()->where('email', 'demo@kinsenas.test')->firstOrFail();
    $plan = SavingsPlan::query()->where('team_id', $user->currentTeam->id)->firstOrFail();

    expect(User::query()->where('email', 'demo@kinsenas.test')->count())->toBe(1)
        ->and(SavingsPlan::query()->where('team_id', $user->currentTeam->id)->count())->toBe(1)
        ->and(IncomePeriod::query()->where('plan_id', $plan->id)->count())->toBe(6);
});
