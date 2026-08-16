<?php

use App\Models\FundSpend;
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

it('includes graph data on the savings reports page', function () {
    [$user, $plan, $everydayCategory] = createUserWithLockedIncome();
    grantTeamSubscriptionAccess($user->currentTeam);

    FundSpend::factory()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $everydayCategory->id,
        'amount_encrypted' => '1000.00',
        'spent_on' => '2026-01-15',
    ]);

    $response = $this->actingAs($user)->get(route('savings.reports', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('savings/reports')
        ->has('totals.fund_health')
        ->has('graphs.fund_utilization')
        ->has('graphs.spending_by_fund')
        ->has('graphs.spending_over_time')
        ->has('graphs.income_vs_spending')
        ->has('graphs.top_recipients')
        ->has('graphs.range')
    );
});

it('filters report graphs by date range query params', function () {
    [$user, $plan, $everydayCategory] = createUserWithLockedIncome();
    grantTeamSubscriptionAccess($user->currentTeam);

    FundSpend::factory()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $everydayCategory->id,
        'amount_encrypted' => '1000.00',
        'spent_on' => '2026-01-15',
    ]);

    FundSpend::factory()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $everydayCategory->id,
        'amount_encrypted' => '2000.00',
        'spent_on' => '2026-03-15',
    ]);

    $response = $this->actingAs($user)->get(route('savings.reports', [
        'current_team' => $user->currentTeam->slug,
        'from' => '2026-01-01',
        'to' => '2026-01-31',
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('graphs.range.from', '2026-01-01')
        ->where('graphs.range.to', '2026-01-31')
        ->where('graphs.spending_over_time.0.total', '1000.00')
    );
});
