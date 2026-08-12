<?php

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

it('shows allocation percentage after fund bucket titles on the dashboard', function () {
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

    $dashboardUrl = route('dashboard', ['current_team' => $user->currentTeam->slug]);

    $page = visit('/login');
    browserLogin($page, $user);

    $page->navigate($dashboardUrl);

    browserUnlockVaultIfNeeded($page);
    browserDismissOnboardingTour($page, $user->currentTeam->id);

    $page->assertPathContains('/dashboard')
        ->assertSee('Everyday Fund · 70%')
        ->assertNoSmoke();
});
