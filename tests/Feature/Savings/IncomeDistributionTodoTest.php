<?php

use App\Enums\IncomeDistributionTodoStatus;
use App\Models\Bank;
use App\Models\IncomeDistributionTodo;
use App\Models\IncomePeriod;
use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
use App\Models\User;
use App\Services\Savings\IncomeDistributionTodoService;
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

function createIncomeDistributionFixture(string $amount = '50000.00', ?User $user = null): array
{
    $user ??= createUserWithPlan();

    test()->actingAs($user)->post(route('savings.income.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'name' => 'January salary',
        'amount' => $amount,
        'period_start' => '2026-01-01',
    ]);

    $plan = SavingsPlan::query()->where('team_id', $user->current_team_id)->firstOrFail();
    $period = IncomePeriod::query()->where('plan_id', $plan->id)->latest()->firstOrFail();

    return [$user, $period];
}

it('creates distribution todos when income is stored', function () {
    [$user, $period] = createIncomeDistributionFixture();

    $plan = SavingsPlan::query()->firstOrFail();
    $everydayCategory = $plan->categories()->where('name', 'Everyday Fund')->firstOrFail();

    expect(IncomeDistributionTodo::query()->where('income_period_id', $period->id)->count())->toBe(3);

    $everydayTodo = IncomeDistributionTodo::query()
        ->where('income_period_id', $period->id)
        ->where('category_id', $everydayCategory->id)
        ->firstOrFail();

    expect($everydayTodo->status)->toBe(IncomeDistributionTodoStatus::Pending)
        ->and($everydayTodo->amount_encrypted)->toBe('35000.00');

    $response = $this->actingAs($user)->get(route('savings.income.show', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('savings/income/show')
        ->has('distributionTodos', 3)
        ->where('distributionTodos.0.categoryName', 'Everyday Fund')
        ->where('distributionTodos.0.amount', '35000.00')
        ->where('distributionTodos.0.status', 'pending')
        ->where('distributionTodoProgress.pendingCount', 3)
        ->where('distributionTodoProgress.complete', false),
    );
});

it('snapshots assigned bank on distribution todos', function () {
    [$user, $period] = createIncomeDistributionFixture();

    $plan = SavingsPlan::query()->firstOrFail();
    $everydayCategory = $plan->categories()->where('name', 'Everyday Fund')->firstOrFail();

    $this->actingAs($user)->post(route('savings.banks.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'name' => 'BPI',
        'account_label' => 'Main',
    ]);

    $bank = Bank::query()->firstOrFail();
    $everydayCategory->update(['bank_id' => $bank->id]);

    app(IncomeDistributionTodoService::class)->syncFromPeriod($period->fresh(['allocations.category.bank']));

    $todo = IncomeDistributionTodo::query()
        ->where('income_period_id', $period->id)
        ->where('category_id', $everydayCategory->id)
        ->firstOrFail();

    expect($todo->bank_id)->toBe($bank->id);
});

it('creates todos for categories without assigned banks', function () {
    [$user, $period] = createIncomeDistributionFixture();

    $todo = IncomeDistributionTodo::query()
        ->where('income_period_id', $period->id)
        ->firstOrFail();

    expect($todo->bank_id)->toBeNull();
});

it('marks a distribution todo complete', function () {
    [$user, $period] = createIncomeDistributionFixture();

    $todo = IncomeDistributionTodo::query()
        ->where('income_period_id', $period->id)
        ->firstOrFail();

    $response = $this->actingAs($user)->post(route('savings.income.todos.complete', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
        'todo' => $todo->id,
    ]));

    $response->assertRedirect();

    $todo->refresh();

    expect($todo->status)->toBe(IncomeDistributionTodoStatus::Completed)
        ->and($todo->completed_by_user_id)->toBe($user->id)
        ->and($todo->completed_at)->not->toBeNull();
});

it('reopens completed todos when custom amounts change the allocation', function () {
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

    [$user, $period] = createIncomeDistributionFixture('50000.00', $user);

    $plan = SavingsPlan::query()->where('team_id', $user->current_team_id)->firstOrFail();
    $collegeCategory = $plan->categories()->where('name', 'College Fund')->firstOrFail();

    $todo = IncomeDistributionTodo::query()
        ->where('income_period_id', $period->id)
        ->where('category_id', $collegeCategory->id)
        ->firstOrFail();

    $this->actingAs($user)->post(route('savings.income.todos.complete', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
        'todo' => $todo->id,
    ]));

    expect($todo->fresh()->status)->toBe(IncomeDistributionTodoStatus::Completed);

    $this->actingAs($user)->put(route('savings.income.custom-amounts', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]), [
        'custom_amounts' => [
            [
                'category_id' => $collegeCategory->id,
                'amount' => '2500.00',
            ],
        ],
    ]);

    $todo->refresh();

    expect($todo->status)->toBe(IncomeDistributionTodoStatus::Pending)
        ->and($todo->amount_encrypted)->toBe('2500.00')
        ->and($todo->completed_at)->toBeNull();
});

it('removes distribution todos when income is deleted', function () {
    [$user, $period] = createIncomeDistributionFixture();

    expect(IncomeDistributionTodo::query()->where('income_period_id', $period->id)->count())->toBe(3);

    $this->actingAs($user)->delete(route('savings.income.destroy', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    expect(IncomeDistributionTodo::query()->where('income_period_id', $period->id)->count())->toBe(0)
        ->and(IncomePeriod::query()->find($period->id))->toBeNull();
});

it('exposes distribution todo progress on the income index', function () {
    [$user, $period] = createIncomeDistributionFixture();

    $response = $this->actingAs($user)->get(route('savings.income.index', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('savings/income/index')
        ->where('periods.0.distributionTodoProgress.pendingCount', 3)
        ->where('periods.0.distributionTodoProgress.totalCount', 3)
        ->where('periods.0.distributionTodoProgress.complete', false),
    );
});

it('forbids completing a todo from another teams income period', function () {
    [$owner, $period] = createIncomeDistributionFixture();

    $todo = IncomeDistributionTodo::query()
        ->where('income_period_id', $period->id)
        ->firstOrFail();

    $otherUser = User::factory()->create();
    $this->unlockVaultFor($otherUser);

    $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

    $this->actingAs($otherUser)->post(route('savings.plan.from-template', [
        'current_team' => $otherUser->currentTeam->slug,
        'template' => $template->id,
    ]));

    $response = $this->actingAs($otherUser)->post(route('savings.income.todos.complete', [
        'current_team' => $otherUser->currentTeam->slug,
        'incomePeriod' => $period->id,
        'todo' => $todo->id,
    ]));

    $response->assertNotFound();
});
