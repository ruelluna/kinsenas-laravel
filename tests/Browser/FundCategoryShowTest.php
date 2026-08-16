<?php

use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
use App\Models\User;
use App\Services\Savings\IncomeCalculationService;
use App\Services\Savings\SavingsPlanService;
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

it('navigates to fund detail when clicking the fund title on the dashboard', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);
    grantTeamSubscriptionAccess($user->currentTeam);

    $template = SavingsFormulaTemplate::query()
        ->where('slug', 'abundant-formula')
        ->firstOrFail();

    $plan = app(SavingsPlanService::class)->cloneFromTemplate(
        $user->currentTeam,
        $user,
        $template,
        $template->name,
    );

    app(IncomeCalculationService::class)->create(
        $plan,
        $user,
        'January salary',
        '50000.00',
        '2026-01-01',
    );

    $everydayCategory = $plan->categories()->firstWhere('name', 'Everyday Fund');

    $dashboardUrl = route('dashboard', ['current_team' => $user->currentTeam->slug]);

    $page = visit('/login');
    browserLogin($page, $user);

    $page->navigate($dashboardUrl);

    browserUnlockVaultIfNeeded($page);
    browserDismissOnboardingTour($page, $user->currentTeam->id);

    $page->click("@fund-card-title-{$everydayCategory->id}")
        ->assertPathContains("/savings/funds/{$everydayCategory->id}")
        ->assertSee('Everyday Fund · 70%')
        ->assertSee('Remaining')
        ->assertSee('Add Existing Fund')
        ->assertNoSmoke();
});

it('loads fund detail page directly with summary sections', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);
    grantTeamSubscriptionAccess($user->currentTeam);

    $template = SavingsFormulaTemplate::query()
        ->where('slug', 'trc-savings')
        ->firstOrFail();

    $this->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    $this->actingAs($user)->post(route('savings.income.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'name' => 'January salary',
        'amount' => '50000.00',
        'period_start' => '2026-02-01',
    ]);

    $plan = SavingsPlan::query()->with('categories')->firstOrFail();
    $everydayCategory = $plan->categories->firstWhere('name', 'Everyday Fund');

    $detailUrl = route('savings.funds.show', [
        'current_team' => $user->currentTeam->slug,
        'category' => $everydayCategory->id,
    ]);

    $page = visit('/login');
    browserLogin($page, $user);

    $page->navigate($detailUrl);

    browserUnlockVaultIfNeeded($page);

    $page->assertSee('Everyday Fund')
        ->assertSee('Income allocations')
        ->assertSee('Starting balance history')
        ->assertSee('Transfers')
        ->assertSee('Spending')
        ->assertNoSmoke();
});
