<?php

use App\Models\FundSpend;
use App\Models\IncomePeriod;
use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
use App\Models\User;
use App\Services\Savings\FundBalanceService;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    $this->seed([
        SavingsFormulaTemplateSeeder::class,
        BillingSeeder::class,
    ]);
});

function setupLockedPlan(User $user, string $amount = '100000.00'): SavingsPlan
{
    test()->unlockVaultFor($user);

    $template = SavingsFormulaTemplate::query()->where('slug', 'trc-savings')->firstOrFail();

    test()->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    test()->actingAs($user)->post(route('savings.income.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'amount' => $amount,
        'period_start' => '2026-02-01',
    ]);

    $period = IncomePeriod::query()->firstOrFail();

    test()->actingAs($user)->post(route('savings.income.lock', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    return SavingsPlan::query()->with('categories')->firstOrFail();
}

it('calculates running balances across locked income', function () {
    $user = User::factory()->create();
    $plan = setupLockedPlan($user);

    $service = app(FundBalanceService::class);
    $balances = $service->balancesForPlan($plan);

    $everyday = collect($balances)->firstWhere('name', 'Everyday Fund');
    $empower = collect($balances)->firstWhere('name', 'Empower Fund');

    expect($everyday['allocated'])->toBe('50000.00')
        ->and($everyday['remaining'])->toBe('50000.00')
        ->and($everyday['isDefault'])->toBeTrue()
        ->and($empower['allocated'])->toBe('5000.00');
});

it('aggregates spending across multiple income periods', function () {
    $user = User::factory()->create();
    $plan = setupLockedPlan($user, '50000.00');

    test()->actingAs($user)->post(route('savings.income.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'amount' => '50000.00',
        'period_start' => '2026-03-01',
    ]);

    $secondPeriod = IncomePeriod::query()->orderByDesc('period_start')->firstOrFail();

    test()->actingAs($user)->post(route('savings.income.lock', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $secondPeriod->id,
    ]));

    $everydayCategory = $plan->categories->firstWhere('name', 'Everyday Fund');

    FundSpend::factory()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $everydayCategory->id,
        'amount_encrypted' => '3000.00',
        'description' => 'Groceries',
        'spent_on' => '2026-03-05',
    ]);

    $service = app(FundBalanceService::class);
    $balances = $service->balancesForPlan($plan->fresh('categories'));
    $everyday = collect($balances)->firstWhere('name', 'Everyday Fund');

    expect($everyday['allocated'])->toBe('50000.00')
        ->and($everyday['transferred'])->toBe('0.00')
        ->and($everyday['spent'])->toBe('3000.00')
        ->and($everyday['remaining'])->toBe('47000.00');
});

it('subtracts confirmed transfers from remaining balance', function () {
    $user = User::factory()->create();
    $plan = setupLockedPlan($user, '50000.00');
    $everydayCategory = $plan->categories->firstWhere('name', 'Everyday Fund');
    $bank = \App\Models\Bank::factory()->create(['team_id' => $user->currentTeam->id]);

    \App\Models\FundTransfer::factory()->confirmed()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $everydayCategory->id,
        'bank_id' => $bank->id,
        'amount_encrypted' => '4000.00',
    ]);

    $service = app(FundBalanceService::class);
    $balances = $service->balancesForPlan($plan->fresh('categories'));
    $everyday = collect($balances)->firstWhere('name', 'Everyday Fund');

    expect($everyday['transferred'])->toBe('4000.00')
        ->and($everyday['remaining'])->toBe('46000.00');
});

it('defaults everyday fund as the quick spend category', function () {
    $user = User::factory()->create();
    $plan = setupLockedPlan($user);

    $defaultId = app(FundBalanceService::class)->defaultCategoryId($plan);
    $everyday = $plan->categories->firstWhere('name', 'Everyday Fund');

    expect($defaultId)->toBe($everyday->id);
});
