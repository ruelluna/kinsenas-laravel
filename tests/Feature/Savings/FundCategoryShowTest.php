<?php

use App\Models\Bank;
use App\Models\FundAddedEntry;
use App\Models\FundSpend;
use App\Models\FundTransfer;
use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
use App\Models\User;
use App\Services\Savings\FundBalanceService;
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

function createFundCategoryShowFixture(string $amount = '50000.00'): array
{
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
        'amount' => $amount,
        'period_start' => '2026-02-01',
    ]);

    $plan = SavingsPlan::query()->with('categories')->firstOrFail();
    $everydayCategory = $plan->categories->firstWhere('name', 'Everyday Fund');
    $utilityCategory = $plan->categories->firstWhere('name', 'Utility');

    return [$user, $plan, $everydayCategory, $utilityCategory];
}

it('redirects to plan chooser when no savings plan exists', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);

    $response = $this->actingAs($user)->get(route('savings.funds.show', [
        'current_team' => $user->currentTeam->slug,
        'category' => '00000000-0000-7000-8000-000000000001',
    ]));

    $response->assertRedirect(route('savings.plan.show', [
        'current_team' => $user->currentTeam->slug,
    ]));
    $response->assertSessionHas('error');
});

it('returns 404 when category does not belong to the team plan', function () {
    [$user, , $everydayCategory] = createFundCategoryShowFixture();

    $otherUser = User::factory()->create();
    test()->unlockVaultFor($otherUser);

    $template = SavingsFormulaTemplate::query()->where('slug', 'trc-savings')->firstOrFail();

    test()->actingAs($otherUser)->post(route('savings.plan.from-template', [
        'current_team' => $otherUser->currentTeam->slug,
        'template' => $template->id,
    ]));

    $response = $this->actingAs($otherUser)->get(route('savings.funds.show', [
        'current_team' => $otherUser->currentTeam->slug,
        'category' => $everydayCategory->id,
    ]));

    $response->assertNotFound();
});

it('shows fund category detail with balances matching FundBalanceService', function () {
    [$user, $plan, $everydayCategory] = createFundCategoryShowFixture('50000.00');

    FundAddedEntry::factory()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $everydayCategory->id,
        'category_name' => 'Everyday Fund',
        'amount_encrypted' => '2000.00',
        'added_on' => '2026-01-15',
    ]);

    $everydayCategory->update(['opening_balance_encrypted' => '2000.00']);

    FundSpend::factory()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $everydayCategory->id,
        'amount_encrypted' => '1500.00',
        'description' => 'Groceries',
        'spent_on' => '2026-02-10',
    ]);

    $service = app(FundBalanceService::class);
    $expectedBalance = collect($service->balancesForPlan($plan->fresh('categories')))
        ->firstWhere('categoryId', $everydayCategory->id);

    $response = $this->actingAs($user)->get(route('savings.funds.show', [
        'current_team' => $user->currentTeam->slug,
        'category' => $everydayCategory->id,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('savings/funds/show')
        ->where('fundBalance.categoryId', $everydayCategory->id)
        ->where('fundBalance.name', 'Everyday Fund')
        ->where('fundBalance.percentage', '50.00')
        ->where('fundBalance.openingBalance', $expectedBalance['openingBalance'])
        ->where('fundBalance.allocated', $expectedBalance['allocated'])
        ->where('fundBalance.transferred', $expectedBalance['transferred'])
        ->where('fundBalance.received', $expectedBalance['received'])
        ->where('fundBalance.spent', $expectedBalance['spent'])
        ->where('fundBalance.remaining', $expectedBalance['remaining'])
        ->has('fundAddedEntries', 1)
        ->where('fundAddedEntries.0.amount', '2000.00')
        ->has('allocations', 1)
        ->where('allocations.0.amount', '25000.00')
        ->where('allocations.0.periodName', 'January salary')
        ->has('spends', 1)
        ->where('spends.0.amount', '1500.00')
        ->where('spends.0.description', 'Groceries')
        ->where('plan.canDrawFromFunds', true)
    );
});

it('includes incoming and outgoing transfers for the category', function () {
    [$user, $plan, $everydayCategory, $utilityCategory] = createFundCategoryShowFixture('50000.00');
    $bank = Bank::factory()->create(['team_id' => $user->currentTeam->id]);

    FundTransfer::factory()->confirmed()->create([
        'savings_plan_id' => $plan->id,
        'from_category_id' => $everydayCategory->id,
        'to_category_id' => $utilityCategory->id,
        'from_bank_id' => $bank->id,
        'to_bank_id' => $bank->id,
        'amount_encrypted' => '3000.00',
        'description' => 'To utility',
        'transferred_on' => '2026-02-12',
    ]);

    FundTransfer::factory()->confirmed()->create([
        'savings_plan_id' => $plan->id,
        'from_category_id' => $utilityCategory->id,
        'to_category_id' => $everydayCategory->id,
        'from_bank_id' => $bank->id,
        'to_bank_id' => $bank->id,
        'amount_encrypted' => '1000.00',
        'description' => 'From utility',
        'transferred_on' => '2026-02-13',
    ]);

    $response = $this->actingAs($user)->get(route('savings.funds.show', [
        'current_team' => $user->currentTeam->slug,
        'category' => $everydayCategory->id,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('savings/funds/show')
        ->has('transfers', 2)
        ->where('transfers.0.direction', 'in')
        ->where('transfers.0.amount', '1000.00')
        ->where('transfers.1.direction', 'out')
        ->where('transfers.1.amount', '3000.00')
    );
});

it('passes modal props for quick actions', function () {
    [$user, , $everydayCategory] = createFundCategoryShowFixture();

    $response = $this->actingAs($user)->get(route('savings.funds.show', [
        'current_team' => $user->currentTeam->slug,
        'category' => $everydayCategory->id,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('savings/funds/show')
        ->where('defaultCategoryId', $everydayCategory->id)
        ->has('categories', fn (Assert $categories) => $categories
            ->where('0.id', $everydayCategory->id)
            ->etc()
        )
        ->has('categoryBankMap')
        ->has('recipients')
    );
});
