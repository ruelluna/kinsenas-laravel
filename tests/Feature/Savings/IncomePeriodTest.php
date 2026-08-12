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
    ])->assertRedirect();

    return IncomePeriod::query()
        ->where('name', $name)
        ->whereDate('period_start', $periodStart)
        ->latest()
        ->firstOrFail();
}

function createUserWithPlanAndIncome(string $amount = '50000.00', string $periodStart = '2026-01-01'): array
{
    $user = createUserWithPlan();

    $period = createIncomePeriodFor($user, $amount, $periodStart);

    return [$user, $period];
}

it('auto-allocates fund buckets when income is stored', function () {
    [$user, $period] = createUserWithPlanAndIncome();

    expect($period->fresh()->is_locked)->toBeTrue()
        ->and($period->allocations)->toHaveCount(3);

    $response = $this->actingAs($user)->get(route('savings.income.show', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('savings/income/show')
        ->where('period.id', $period->id)
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
        ->has('fundSummary'),
    );
});

it('includes spent and remaining summary on income index after spending', function () {
    [$user, $period] = createUserWithPlanAndIncome();

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
        ->has('fundSummary.categoryFundsAdded')
        ->where("fundSummary.categorySpent.{$everydayCategory->id}", '5000.00')
        ->where("fundSummary.categoryRemaining.{$everydayCategory->id}", '30000.00'),
    );
});

it('income index includes total funds added per category', function () {
    [$user, $plan, $everydayCategory] = createUserWithLockedIncome();

    $this->actingAs($user)->patch(route('savings.plan.category.opening-balance', [
        'current_team' => $user->currentTeam->slug,
        'category' => $everydayCategory->id,
    ]), [
        'amount' => '7500.00',
    ]);

    $response = $this->actingAs($user)->get(route('savings.income.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('savings/income/index')
        ->where("fundSummary.categoryFundsAdded.{$everydayCategory->id}", '7500.00'),
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

it('deletes an income period when no dependent draws exist', function () {
    [$user, $period] = createUserWithPlanAndIncome();

    $response = $this->actingAs($user)->delete(route('savings.income.destroy', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    $response->assertRedirect(route('savings.income.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    expect(IncomePeriod::query()->find($period->id))->toBeNull();
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

it('allocates income with fixed deduction applied to source category', function () {
    $user = createUserWithPlan();
    savePlanWithDeduction($user);
    $period = createIncomePeriodFor($user, '50000.00');

    $showResponse = $this->actingAs($user)->get(route('savings.income.show', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    $showResponse->assertOk();
    $showResponse->assertInertia(fn (Assert $page) => $page
        ->where('breakdown.0.name', 'Everyday Fund')
        ->where('breakdown.0.amount', '24000.00')
        ->where('breakdown.3.name', 'College Fund')
        ->where('breakdown.3.amount', '1000.00')
        ->where('breakdown.3.allocationType', 'deduction'),
    );
});

it('fails to store income when deduction exceeds source allocation', function () {
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

    $response = $this->actingAs($user)->post(route('savings.income.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'name' => 'January salary',
        'amount' => '50000.00',
        'period_start' => '2026-01-01',
    ]);

    $response->assertSessionHasErrors('amount');
});

it('allocates income with percent of income deduction', function () {
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

it('allows deleting income when opening balance covers spending after removal', function () {
    [$user, , $everydayCategory, $period] = createUserWithLockedIncome();

    $everydayCategory->update(['opening_balance_encrypted' => '20000.00']);

    $this->actingAs($user)->post(route('savings.spending.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '15000.00',
        'description' => 'Rent',
        'spent_on' => '2026-01-05',
    ]);

    $response = $this->actingAs($user)->delete(route('savings.income.destroy', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    $response->assertRedirect(route('savings.income.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    expect(IncomePeriod::query()->find($period->id))->toBeNull();
});

it('allows deleting one income period when another covers remaining spending', function () {
    [$user, $plan, $everydayCategory, $firstPeriod] = createUserWithLockedIncome();

    $secondPeriod = createIncomePeriodFor(
        $user,
        '50000.00',
        '2026-02-01',
        'February salary',
    );

    expect($secondPeriod->id)->not->toBe($firstPeriod->id);

    $this->actingAs($user)->post(route('savings.spending.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '10000.00',
        'description' => 'Groceries',
        'spent_on' => '2026-01-15',
    ]);

    $response = $this->actingAs($user)->delete(route('savings.income.destroy', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $firstPeriod->id,
    ]));

    $response->assertRedirect(route('savings.income.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    expect(IncomePeriod::query()->find($firstPeriod->id))->toBeNull()
        ->and(IncomePeriod::query()->find($secondPeriod->id))->not->toBeNull();
});

it('exposes deleteBlockReason on income show and index when delete is blocked', function () {
    [$user, , $everydayCategory, $period] = createUserWithLockedIncome();

    $this->actingAs($user)->post(route('savings.spending.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '10000.00',
        'description' => 'Rent',
        'spent_on' => '2026-01-05',
    ]);

    $showResponse = $this->actingAs($user)->get(route('savings.income.show', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    $showResponse->assertOk();
    $showResponse->assertInertia(fn (Assert $page) => $page
        ->where('deleteBlockReason', fn (?string $reason) => $reason !== null
            && str_contains($reason, 'Everyday Fund')),
    );

    $indexResponse = $this->actingAs($user)->get(route('savings.income.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $indexResponse->assertOk();
    $indexResponse->assertInertia(fn (Assert $page) => $page
        ->where('periods.0.deleteBlockReason', fn (?string $reason) => $reason !== null
            && str_contains($reason, 'Everyday Fund')),
    );
});
