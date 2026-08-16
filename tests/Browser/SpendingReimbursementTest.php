<?php

use App\Enums\RecipientType;
use App\Models\Recipient;
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

it('records spending with expecting payback and shows resolved after payback', function () {
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

    $plan->update(['allow_editing_spends' => true]);

    app(IncomeCalculationService::class)->create(
        $plan,
        $user,
        'January salary',
        '50000.00',
        '2026-01-01',
    );

    Recipient::query()->create([
        'team_id' => $user->currentTeam->id,
        'type' => RecipientType::Person,
        'name' => 'Ana',
    ]);

    $teamSlug = $user->currentTeam->slug;
    $spendingUrl = "/{$teamSlug}/savings/spending";

    $page = visit('/login');

    browserLogin($page, $user);

    $page->navigate($spendingUrl);

    browserUnlockVaultIfNeeded($page);
    browserDismissOnboardingTour($page, $user->currentTeam->id);

    $page->assertPathContains('/savings/spending')
        ->assertSee('Recent activity')
        ->click('@add-spending-button')
        ->assertSee('Record spending')
        ->assertScript(
            'document.querySelector("[data-test=\\"spending-fund-select\\"]").selectedOptions[0].textContent.trim()',
            'Everyday Fund — ₱35,000.00 remaining',
        )
        ->fill('@spending-amount', '1000')
        ->fill('@spending-description', 'Bill for Ana')
        ->click('@expects-reimbursement')
        ->assertVisible('@expected-from-recipient')
        ->select('@expected-from-recipient', 'Ana')
        ->click('@record-spending-submit')
        ->assertDontSee('Record spending')
        ->assertSee('Bill for Ana')
        ->click('@edit-spending-button')
        ->assertSee('Edit spending')
        ->assertScript(
            'document.querySelector("[data-test=\\"edit-spending-fund-select\\"]").selectedOptions[0].textContent.trim()',
            'Everyday Fund — ₱34,000.00 remaining',
        )
        ->click('@edit-spending-cancel')
        ->assertSee('payback from Ana')
        ->assertVisible('@reimbursement-badge-awaiting')
        ->click('@record-payback-button')
        ->assertSee('Remaining owed')
        ->fill('@payback-amount', '1000')
        ->click('@record-payback-submit')
        ->assertVisible('@reimbursement-badge-resolved')
        ->assertNoSmoke();
});
