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

it('seeds the trc template with a utility category instead of empower fund', function () {
    $template = SavingsFormulaTemplate::query()->where('slug', 'trc-savings')->firstOrFail();

    expect($template->categories()->where('name', 'Utility')->value('percentage'))
        ->toBe('5.00')
        ->and($template->categories()->where('name', 'Empower Fund')->exists())
        ->toBeFalse();
});

it('creates a plan with a utility category from the trc template', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);

    $template = SavingsFormulaTemplate::query()->where('slug', 'trc-savings')->firstOrFail();

    test()->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    expect(SavingsCategory::query()->where('name', 'Utility')->exists())->toBeTrue()
        ->and(SavingsCategory::query()->where('name', 'Empower Fund')->exists())->toBeFalse();
});

it('backfills legacy empower fund names after migration', function () {
    $template = SavingsFormulaTemplate::query()->where('slug', 'trc-savings')->firstOrFail();
    $templateCategory = $template->categories()->where('name', 'Empower Fund')->first()
        ?? $template->categories()->where('name', 'Utility')->firstOrFail();

    $templateCategory->update(['name' => 'Empower Fund']);

    $user = User::factory()->create();
    test()->unlockVaultFor($user);

    test()->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    $plan = SavingsPlan::query()->firstOrFail();
    $planCategory = $plan->categories()->where('name', 'Empower Fund')->firstOrFail();
    $planCategory->update(['name' => 'Empower Fund']);

    FundAddedEntry::query()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $planCategory->id,
        'category_name' => 'Empower Fund',
        'amount_encrypted' => '1000.00',
        'added_on' => now()->toDateString(),
        'created_by_user_id' => $user->id,
    ]);

    DB::table('migrations')
        ->where('migration', '2026_08_17_000631_rename_empower_fund_to_utility')
        ->delete();

    $this->artisan('migrate', ['--path' => 'database/migrations/2026_08_17_000631_rename_empower_fund_to_utility.php']);

    $templateCategory->refresh();
    $planCategory->refresh();

    expect($templateCategory->name)->toBe('Utility')
        ->and($planCategory->name)->toBe('Utility')
        ->and(FundAddedEntry::query()->value('category_name'))->toBe('Utility');
});
