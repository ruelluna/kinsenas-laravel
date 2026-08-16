<?php

use App\Models\FundAddedEntry;
use App\Models\SavingsCategory;
use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    $this->seed([
        SavingsFormulaTemplateSeeder::class,
        BillingSeeder::class,
    ]);
});

it('seeds the trc template with a savings category instead of emancipation fund', function () {
    $template = SavingsFormulaTemplate::query()->where('slug', 'trc-savings')->firstOrFail();

    expect($template->categories()->where('name', 'Savings')->value('percentage'))
        ->toBe('20.00')
        ->and($template->categories()->where('name', 'Emancipation Fund')->exists())
        ->toBeFalse()
        ->and($template->best_for)->not->toContain('Emancipation');
});

it('creates a plan with a savings category from the trc template', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);

    $template = SavingsFormulaTemplate::query()->where('slug', 'trc-savings')->firstOrFail();

    test()->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    expect(SavingsCategory::query()->where('name', 'Savings')->exists())->toBeTrue()
        ->and(SavingsCategory::query()->where('name', 'Emancipation Fund')->exists())->toBeFalse();
});

it('backfills legacy emancipation fund names after migration', function () {
    $template = SavingsFormulaTemplate::query()->where('slug', 'trc-savings')->firstOrFail();
    $templateCategory = $template->categories()->where('name', 'Emancipation Fund')->first()
        ?? $template->categories()->where('name', 'Savings')->firstOrFail();

    $templateCategory->update(['name' => 'Emancipation Fund']);
    $template->update([
        'best_for' => 'Kinsenas recommends this formula: 20% for long-term freedom (Emancipation).',
    ]);

    $user = User::factory()->create();
    test()->unlockVaultFor($user);

    test()->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    $plan = SavingsPlan::query()->firstOrFail();
    $planCategory = $plan->categories()->where('name', 'Emancipation Fund')->firstOrFail();
    $planCategory->update(['name' => 'Emancipation Fund']);

    FundAddedEntry::query()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $planCategory->id,
        'category_name' => 'Emancipation Fund',
        'amount_encrypted' => '1000.00',
        'added_on' => now()->toDateString(),
        'created_by_user_id' => $user->id,
    ]);

    DB::table('migrations')
        ->where('migration', '2026_08_17_030100_rename_emancipation_fund_to_savings')
        ->delete();

    $this->artisan('migrate', ['--path' => 'database/migrations/2026_08_17_030100_rename_emancipation_fund_to_savings.php']);

    $templateCategory->refresh();
    $planCategory->refresh();

    expect($templateCategory->name)->toBe('Savings')
        ->and($planCategory->name)->toBe('Savings')
        ->and(FundAddedEntry::query()->value('category_name'))->toBe('Savings')
        ->and($template->fresh()->best_for)->not->toContain('Emancipation');
});
