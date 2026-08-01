<?php

use App\Models\IncomePeriod;
use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    $this->seed([
        SavingsFormulaTemplateSeeder::class,
        BillingSeeder::class,
    ]);
});

function createUserWithPlan(string $templateSlug = 'abundant-formula'): User
{
    $user = User::factory()->create();
    test()->unlockVaultFor($user);

    $template = SavingsFormulaTemplate::query()->where('slug', $templateSlug)->firstOrFail();

    test()->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    return $user;
}

function createIncomePeriodFor(
    User $user,
    string $amount = '50000.00',
    string $periodStart = '2026-01-01',
    string $name = 'January salary',
): IncomePeriod {
    test()->actingAs($user)->post(route('savings.income.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'name' => $name,
        'amount' => $amount,
        'period_start' => $periodStart,
    ]);

    return IncomePeriod::query()->firstOrFail();
}

function createUserWithPlanAndIncome(string $amount = '50000.00', string $periodStart = '2026-01-01'): array
{
    $user = createUserWithPlan();

    $period = createIncomePeriodFor($user, $amount, $periodStart);

    return [$user, $period];
}

it('shows preview breakdown for an unlocked income period', function () {
    [$user, $period] = createUserWithPlanAndIncome();

    $response = $this->actingAs($user)->get(route('savings.income.show', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('savings/income/show')
        ->where('period.id', $period->id)
        ->where('period.isLocked', false)
        ->has('breakdown', 3)
        ->where('breakdown.0.name', 'Everyday Fund')
        ->where('breakdown.0.percentage', '70.00')
        ->where('breakdown.0.amount', '35000.00')
        ->where('breakdown.1.name', 'Savings')
        ->where('breakdown.1.amount', '10000.00')
        ->where('breakdown.2.name', 'Tithe')
        ->where('breakdown.2.amount', '5000.00'),
    );
});

it('shows persisted allocations for a locked income period', function () {
    [$user, $period] = createUserWithPlanAndIncome();

    $this->actingAs($user)->post(route('savings.income.lock', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    $period->refresh();

    $response = $this->actingAs($user)->get(route('savings.income.show', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('savings/income/show')
        ->where('period.isLocked', true)
        ->has('breakdown', 3)
        ->where('breakdown.0.amount', '35000.00'),
    );

    expect($period->allocations)->toHaveCount(3);
});

it('returns not found when viewing another teams income period', function () {
    [$owner, $period] = createUserWithPlanAndIncome();

    $otherUser = User::factory()->create();
    $this->unlockVaultFor($otherUser);

    $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

    $this->actingAs($otherUser)->post(route('savings.plan.from-template', [
        'current_team' => $otherUser->currentTeam->slug,
        'template' => $template->id,
    ]));

    $response = $this->actingAs($otherUser)->get(route('savings.income.show', [
        'current_team' => $otherUser->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    $response->assertNotFound();
});

it('does not include raw allocations on the income index payload', function () {
    [$user, $period] = createUserWithPlanAndIncome();

    $response = $this->actingAs($user)->get(route('savings.income.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('savings/income/index')
        ->has('planCategories', 3)
        ->where('planCategories.0.name', 'Everyday Fund')
        ->where('planCategories.0.percentage', '70.00')
        ->has('periods', 1)
        ->where('periods.0.id', $period->id)
        ->where('periods.0.name', 'January salary')
        ->where('periods.0.categoryAmounts', fn ($amounts) => count($amounts) === 3)
        ->missing('periods.0.allocations')
        ->where('fundSummary', null),
    );
});

it('includes spent and remaining summary on income index when income is locked', function () {
    [$user, $period] = createUserWithPlanAndIncome();

    $this->actingAs($user)->post(route('savings.income.lock', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    $plan = SavingsPlan::query()->firstOrFail();
    $everydayCategory = $plan->categories()->where('name', 'Everyday Fund')->firstOrFail();

    $this->actingAs($user)->post(route('savings.spending.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '5000.00',
        'description' => 'Groceries',
        'spent_on' => '2026-01-15',
    ]);

    $response = $this->actingAs($user)->get(route('savings.income.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('savings/income/index')
        ->has('fundSummary.categorySpent')
        ->has('fundSummary.categoryRemaining')
        ->where("fundSummary.categorySpent.{$everydayCategory->id}", '5000.00')
        ->where("fundSummary.categoryRemaining.{$everydayCategory->id}", '30000.00'),
    );
});

it('requires a name when storing income', function () {
    $user = createUserWithPlan();

    $response = $this->actingAs($user)->post(route('savings.income.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'amount' => '50000.00',
        'period_start' => '2026-01-01',
    ]);

    $response->assertSessionHasErrors('name');
});

function savePlanWithDeduction(User $user): void
{
    test()->actingAs($user)->put(route('savings.plan.update', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'categories' => [
            ['name' => 'Everyday Fund', 'allocation_type' => 'percentage', 'percentage' => 50],
            ['name' => 'Savings', 'allocation_type' => 'percentage', 'percentage' => 30],
            ['name' => 'Tithe', 'allocation_type' => 'percentage', 'percentage' => 20],
            [
                'name' => 'College Fund',
                'allocation_type' => 'deduction',
                'deduction_mode' => 'fixed',
                'deduction_value' => 1000,
                'deduct_from_index' => 0,
            ],
        ],
    ]);
}

it('locks income with fixed deduction applied to source category', function () {
    $user = createUserWithPlan();
    savePlanWithDeduction($user);
    $period = createIncomePeriodFor($user, '50000.00');

    $response = $this->actingAs($user)->post(route('savings.income.lock', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    $response->assertRedirect();

    $showResponse = $this->actingAs($user)->get(route('savings.income.show', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    $showResponse->assertOk();
    $showResponse->assertInertia(fn (Assert $page) => $page
        ->where('period.isLocked', true)
        ->where('breakdown.0.name', 'Everyday Fund')
        ->where('breakdown.0.amount', '24000.00')
        ->where('breakdown.3.name', 'College Fund')
        ->where('breakdown.3.amount', '1000.00')
        ->where('breakdown.3.allocationType', 'deduction'),
    );
});

it('fails to lock when deduction exceeds source allocation', function () {
    $user = createUserWithPlan();

    test()->actingAs($user)->put(route('savings.plan.update', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'categories' => [
            ['name' => 'Everyday Fund', 'allocation_type' => 'percentage', 'percentage' => 50],
            ['name' => 'Savings', 'allocation_type' => 'percentage', 'percentage' => 50],
            [
                'name' => 'College Fund',
                'allocation_type' => 'deduction',
                'deduction_mode' => 'fixed',
                'deduction_value' => 30000,
                'deduct_from_index' => 0,
            ],
        ],
    ]);

    $period = createIncomePeriodFor($user, '50000.00');

    $response = $this->actingAs($user)->post(route('savings.income.lock', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    $response->assertSessionHasErrors('amount');
});

it('locks income with percent of income deduction', function () {
    $user = createUserWithPlan();

    test()->actingAs($user)->put(route('savings.plan.update', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'categories' => [
            ['name' => 'Everyday Fund', 'allocation_type' => 'percentage', 'percentage' => 50],
            ['name' => 'Savings', 'allocation_type' => 'percentage', 'percentage' => 50],
            [
                'name' => 'College Fund',
                'allocation_type' => 'deduction',
                'deduction_mode' => 'percent_of_income',
                'deduction_value' => 2,
                'deduct_from_index' => 0,
            ],
        ],
    ]);

    $period = createIncomePeriodFor($user, '50000.00');

    $response = $this->actingAs($user)->post(route('savings.income.lock', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    $response->assertRedirect();

    $showResponse = $this->actingAs($user)->get(route('savings.income.show', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    $showResponse->assertInertia(fn (Assert $page) => $page
        ->where('breakdown.0.amount', '24000.00')
        ->where('breakdown.2.amount', '1000.00'),
    );
});

it('allows clearing a custom amount for an income period while keeping the category', function () {
    $user = createUserWithPlan();

    test()->actingAs($user)->put(route('savings.plan.update', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'categories' => [
            ['name' => 'Everyday Fund', 'allocation_type' => 'percentage', 'percentage' => 50],
            ['name' => 'Savings', 'allocation_type' => 'percentage', 'percentage' => 50],
            [
                'name' => 'College Fund',
                'allocation_type' => 'deduction',
                'deduction_mode' => 'fixed',
                'deduction_value' => 1000,
                'deduct_from_index' => 0,
            ],
        ],
    ]);

    $period = createIncomePeriodFor($user, '50000.00');

    $plan = SavingsPlan::query()->firstOrFail();
    $collegeCategory = $plan->categories()->where('name', 'College Fund')->firstOrFail();

    $this->actingAs($user)->put(route('savings.income.custom-amounts', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]), [
        'custom_amounts' => [
            ['category_id' => $collegeCategory->id, 'amount' => null],
        ],
    ]);

    $showResponse = $this->actingAs($user)->get(route('savings.income.show', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    $showResponse->assertInertia(fn (Assert $page) => $page
        ->where('breakdown.0.amount', '25000.00')
        ->where('breakdown.2.amount', '0.00')
        ->has('customCategories', 1)
        ->where('customCategories.0.name', 'College Fund'),
    );
});
