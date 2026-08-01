<?php

use App\Models\IncomePeriod;
use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
use App\Models\User;
use Laravel\Fortify\Features;
use Tests\TestCase;

uses(TestCase::class)->in('Feature');
uses(TestCase::class)->in('Unit');

function skipUnlessFortifyHas(string $feature, ?string $message = null): void
{
    if (! Features::enabled($feature)) {
        test()->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
    }
}

function createUserWithLockedIncome(string $amount = '50000.00'): array
{
    $user = User::factory()->create();
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

    $period = IncomePeriod::query()->firstOrFail();

    test()->actingAs($user)->post(route('savings.income.lock', [
        'current_team' => $user->currentTeam->slug,
        'incomePeriod' => $period->id,
    ]));

    $plan = SavingsPlan::query()->firstOrFail();
    $everydayCategory = $plan->categories()->where('name', 'Everyday Fund')->firstOrFail();

    return [$user, $plan, $everydayCategory, $period];
}
