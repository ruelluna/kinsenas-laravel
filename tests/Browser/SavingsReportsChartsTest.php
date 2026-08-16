<?php

use App\Models\FundSpend;
use App\Models\SavingsFormulaTemplate;
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

it('renders savings report charts without javascript errors', function () {
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

    $everydayCategory = $plan->categories()->where('name', 'Everyday Fund')->firstOrFail();

    FundSpend::factory()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $everydayCategory->id,
        'amount_encrypted' => '1500.00',
        'spent_on' => '2026-01-15',
    ]);

    $reportsUrl = route('savings.reports', [
        'current_team' => $user->currentTeam->slug,
        'from' => '2026-01-01',
        'to' => '2026-01-31',
    ]);

    $page = visit('/login');
    browserLogin($page, $user);

    $page->navigate($reportsUrl);

    browserUnlockVaultIfNeeded($page);
    browserDismissOnboardingTour($page, $user->currentTeam->id);

    $page->assertPathContains('/savings/reports')
        ->assertSee('Reports')
        ->assertSee('Fund utilization')
        ->assertSee('Spending trend')
        ->assertSee('Payday in vs out')
        ->assertPresent('[data-test="reports-date-filter"]')
        ->assertPresent('[data-test="fund-utilization-chart"]')
        ->assertPresent('[data-test="spending-trend-chart"]')
        ->assertNoSmoke();
});
