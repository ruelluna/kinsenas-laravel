<?php

use App\Models\Bank;
use App\Models\FundSpend;
use App\Models\FundTransfer;
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
        'name' => 'January salary',
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
        'name' => 'March salary',
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
    $bank = Bank::factory()->create(['team_id' => $user->currentTeam->id]);

    FundTransfer::factory()->confirmed()->create([
        'savings_plan_id' => $plan->id,
        'from_category_id' => $everydayCategory->id,
        'to_category_id' => $plan->categories->firstWhere('name', 'Empower Fund')->id,
        'from_bank_id' => $bank->id,
        'to_bank_id' => $bank->id,
        'amount_encrypted' => '4000.00',
    ]);

    $service = app(FundBalanceService::class);
    $balances = $service->balancesForPlan($plan->fresh('categories'));
    $everyday = collect($balances)->firstWhere('name', 'Everyday Fund');

    expect($everyday['allocated'])->toBe('25000.00')
        ->and($everyday['transferred'])->toBe('4000.00')
        ->and($everyday['remaining'])->toBe('21000.00');
});

it('includes assigned categories in bank balance breakdown before any activity', function () {
    $user = User::factory()->create();
    $plan = setupLockedPlan($user, '50000.00');
    $everydayCategory = $plan->categories->firstWhere('name', 'Everyday Fund');
    $empowerCategory = $plan->categories->firstWhere('name', 'Empower Fund');
    $bank = Bank::factory()->create(['team_id' => $user->currentTeam->id]);

    $everydayCategory->update(['bank_id' => $bank->id]);
    $empowerCategory->update(['bank_id' => $bank->id]);

    $service = app(FundBalanceService::class);
    $bankBalances = $service->bankBalancesForTeam($user->currentTeam, $plan->fresh('categories'));

    $bankBalance = collect($bankBalances)->firstWhere('bankId', $bank->id);

    expect($bankBalance)->not->toBeNull()
        ->and($bankBalance['total'])->toBe('27500.00')
        ->and($bankBalance['byCategory'])->toHaveCount(2)
        ->and(collect($bankBalance['byCategory'])->pluck('categoryName')->all())->toBe([
            'Empower Fund',
            'Everyday Fund',
        ])
        ->and(collect($bankBalance['byCategory'])->pluck('total')->all())->toBe([
            '2500.00',
            '25000.00',
        ]);
});

it('defaults everyday fund as the quick spend category', function () {
    $user = User::factory()->create();
    $plan = setupLockedPlan($user);

    $defaultId = app(FundBalanceService::class)->defaultCategoryId($plan);
    $everyday = $plan->categories->firstWhere('name', 'Everyday Fund');

    expect($defaultId)->toBe($everyday->id);
});

it('lists everyday fund first on transfer and spending views for trc plans', function () {
    $user = User::factory()->create();
    $plan = setupLockedPlan($user);
    $service = app(FundBalanceService::class);

    $everyday = $plan->categories->firstWhere('name', 'Everyday Fund');
    $orderedCategories = $service->categoriesWithDefaultFirst($plan);
    $orderedBalances = $service->balancesWithDefaultFirst($plan);

    expect($orderedCategories->first()?->id)->toBe($everyday->id)
        ->and($orderedBalances[0]['categoryId'])->toBe($everyday->id)
        ->and($orderedCategories->first()?->name)->toBe('Everyday Fund');
});
