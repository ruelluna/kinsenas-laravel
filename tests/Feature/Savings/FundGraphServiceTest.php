<?php

use App\Enums\TransferStatus;
use App\Models\FundSpend;
use App\Models\Recipient;
use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
use App\Models\User;
use App\Services\Savings\FundGraphService;
use App\Services\Vault\VaultKeyManager;
use Carbon\Carbon;
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

function setupPlanWithIncome(User $user, string $amount = '50000.00'): SavingsPlan
{
    test()->unlockVaultFor($user);

    $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

    test()->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    test()->actingAs($user)->post(route('savings.income.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'name' => 'January salary',
        'amount' => $amount,
        'period_start' => '2026-01-01',
    ]);

    return SavingsPlan::query()->with('categories')->firstOrFail();
}

it('returns empty graph data when vault is locked', function () {
    $user = User::factory()->create();
    $plan = setupPlanWithIncome($user);

    app(VaultKeyManager::class)->forgetAll();

    $service = app(FundGraphService::class);
    $data = $service->graphDataForPlan($plan);

    expect($data['fund_utilization'])->toBe([])
        ->and($data['spending_by_fund'])->toBe([])
        ->and($data['spending_over_time'])->toBe([])
        ->and($data['income_vs_spending'])->toBe([])
        ->and($data['top_recipients'])->toBe([]);
});

it('builds fund utilization from current balances', function () {
    $user = User::factory()->create();
    $plan = setupPlanWithIncome($user);
    $everyday = $plan->categories->firstWhere('name', 'Everyday Fund');

    FundSpend::factory()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $everyday->id,
        'amount_encrypted' => '10000.00',
        'spent_on' => '2026-01-15',
        'status' => TransferStatus::Confirmed,
    ]);

    $service = app(FundGraphService::class);
    $data = $service->graphDataForPlan($plan);

    $everydayUtil = collect($data['fund_utilization'])->firstWhere('name', 'Everyday Fund');

    expect($everydayUtil)->not->toBeNull()
        ->and($everydayUtil['percent_used'])->toBe(28.6)
        ->and($everydayUtil['remaining'])->toBe('25000.00');
});

it('includes every fund bucket in fund utilization', function () {
    $user = User::factory()->create();
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
        'amount' => '50000.00',
        'period_start' => '2026-01-01',
    ]);

    $plan = SavingsPlan::query()->with('categories')->firstOrFail();
    $service = app(FundGraphService::class);
    $data = $service->graphDataForPlan($plan);

    $expectedNames = $plan->categories->sortBy('sort_order')->pluck('name')->values()->all();
    $utilizationNames = collect($data['fund_utilization'])->pluck('name')->values()->all();

    expect($utilizationNames)->toBe($expectedNames)
        ->and($data['fund_utilization'])->toHaveCount(7);
});

it('groups confirmed spends by fund bucket within date range', function () {
    $user = User::factory()->create();
    $plan = setupPlanWithIncome($user);
    $everyday = $plan->categories->firstWhere('name', 'Everyday Fund');
    $savings = $plan->categories->firstWhere('name', 'Savings');

    FundSpend::factory()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $everyday->id,
        'amount_encrypted' => '1000.00',
        'spent_on' => '2026-01-10',
    ]);

    FundSpend::factory()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $savings->id,
        'amount_encrypted' => '500.00',
        'spent_on' => '2026-01-20',
    ]);

    FundSpend::factory()->pending()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $everyday->id,
        'amount_encrypted' => '999.00',
        'spent_on' => '2026-01-12',
    ]);

    $service = app(FundGraphService::class);
    $from = Carbon::parse('2026-01-01');
    $to = Carbon::parse('2026-01-31');
    $data = $service->graphDataForPlan($plan, $from, $to);

    expect($data['spending_by_fund'])->toHaveCount(2);

    $everydaySpend = collect($data['spending_by_fund'])->firstWhere('name', 'Everyday Fund');
    $savingsSpend = collect($data['spending_by_fund'])->firstWhere('name', 'Savings');

    expect($everydaySpend['total'])->toBe('1000.00')
        ->and($savingsSpend['total'])->toBe('500.00');
});

it('groups spending over time by month', function () {
    $user = User::factory()->create();
    $plan = setupPlanWithIncome($user);
    $everyday = $plan->categories->firstWhere('name', 'Everyday Fund');

    FundSpend::factory()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $everyday->id,
        'amount_encrypted' => '1000.00',
        'spent_on' => '2026-01-10',
    ]);

    FundSpend::factory()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $everyday->id,
        'amount_encrypted' => '2000.00',
        'spent_on' => '2026-02-05',
    ]);

    $service = app(FundGraphService::class);
    $data = $service->graphDataForPlan(
        $plan,
        Carbon::parse('2026-01-01'),
        Carbon::parse('2026-02-28'),
    );

    expect($data['spending_over_time'])->toHaveCount(2)
        ->and($data['spending_over_time'][0]['period'])->toBe('2026-01')
        ->and($data['spending_over_time'][0]['total'])->toBe('1000.00')
        ->and($data['spending_over_time'][1]['period'])->toBe('2026-02')
        ->and($data['spending_over_time'][1]['total'])->toBe('2000.00');
});

it('builds income vs spending per income period', function () {
    $user = User::factory()->create();
    $plan = setupPlanWithIncome($user, '50000.00');
    $everyday = $plan->categories->firstWhere('name', 'Everyday Fund');

    test()->actingAs($user)->post(route('savings.income.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'name' => 'February salary',
        'amount' => '60000.00',
        'period_start' => '2026-02-01',
    ]);

    FundSpend::factory()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $everyday->id,
        'amount_encrypted' => '5000.00',
        'spent_on' => '2026-01-20',
    ]);

    FundSpend::factory()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $everyday->id,
        'amount_encrypted' => '3000.00',
        'spent_on' => '2026-02-10',
    ]);

    $service = app(FundGraphService::class);
    $data = $service->graphDataForPlan(
        $plan,
        Carbon::parse('2026-01-01'),
        Carbon::parse('2026-02-28'),
    );

    expect($data['income_vs_spending'])->toHaveCount(2);

    $january = $data['income_vs_spending'][0];
    $february = $data['income_vs_spending'][1];

    expect($january['period'])->toBe('2026-01-01')
        ->and($january['income'])->toBe('50000.00')
        ->and($january['spending'])->toBe('5000.00')
        ->and($february['period'])->toBe('2026-02-01')
        ->and($february['income'])->toBe('60000.00')
        ->and($february['spending'])->toBe('3000.00');
});

it('returns top recipients limited to eight entries', function () {
    $user = User::factory()->create();
    $plan = setupPlanWithIncome($user);
    $everyday = $plan->categories->firstWhere('name', 'Everyday Fund');

    foreach (range(1, 10) as $index) {
        $recipient = Recipient::query()->create([
            'team_id' => $user->currentTeam->id,
            'type' => 'person',
            'name' => "Recipient {$index}",
        ]);

        FundSpend::factory()->create([
            'savings_plan_id' => $plan->id,
            'category_id' => $everyday->id,
            'recipient_id' => $recipient->id,
            'amount_encrypted' => (string) ($index * 100),
            'spent_on' => '2026-01-15',
        ]);
    }

    $service = app(FundGraphService::class);
    $data = $service->graphDataForPlan(
        $plan,
        Carbon::parse('2026-01-01'),
        Carbon::parse('2026-01-31'),
    );

    expect($data['top_recipients'])->toHaveCount(8)
        ->and($data['top_recipients'][0]['name'])->toBe('Recipient 10')
        ->and($data['top_recipients'][0]['total'])->toBe('1000.00');
});

it('provides dashboard graph subset with three month spending trend', function () {
    $user = User::factory()->create();
    $plan = setupPlanWithIncome($user);
    $everyday = $plan->categories->firstWhere('name', 'Everyday Fund');

    FundSpend::factory()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $everyday->id,
        'amount_encrypted' => '1500.00',
        'spent_on' => now()->subMonths(2)->startOfMonth()->addDays(5)->toDateString(),
    ]);

    $service = app(FundGraphService::class);
    $data = $service->dashboardGraphsForPlan($plan);

    expect($data)->toHaveKeys(['fund_utilization', 'spending_over_time'])
        ->and($data['fund_utilization'])->not->toBeEmpty()
        ->and($data['spending_over_time'])->not->toBeEmpty();
});
