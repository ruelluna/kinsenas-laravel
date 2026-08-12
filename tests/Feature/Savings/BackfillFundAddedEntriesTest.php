<?php

use App\Models\FundAddedEntry;
use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
use App\Models\User;
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

it('backfills fund added entries for categories with existing opening balances', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);

    $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

    test()->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    $plan = SavingsPlan::query()->with('categories')->firstOrFail();
    $everyday = $plan->categories->firstWhere('name', 'Everyday Fund');

    $everyday->update(['opening_balance_encrypted' => '12000.00']);
    FundAddedEntry::query()->delete();

    $migration = include database_path('migrations/2026_08_06_110000_backfill_fund_added_entries_from_opening_balances.php');
    $migration->up();

    expect(FundAddedEntry::query()->count())->toBe(1)
        ->and(FundAddedEntry::query()->first()->amount_encrypted)->toBe('12000.00')
        ->and(FundAddedEntry::query()->first()->category_id)->toBe($everyday->id)
        ->and(FundAddedEntry::query()->first()->category_name)->toBe('Everyday Fund');
});

it('backfill only inserts the gap when some fund added entries already exist', function () {
    [$user, $plan, $everydayCategory] = createUserWithLockedIncome();

    $everydayCategory->update(['opening_balance_encrypted' => '10000.00']);
    FundAddedEntry::query()->delete();

    FundAddedEntry::factory()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $everydayCategory->id,
        'category_name' => $everydayCategory->name,
        'amount_encrypted' => '3000.00',
        'added_on' => '2026-01-10',
    ]);

    $migration = include database_path('migrations/2026_08_06_110000_backfill_fund_added_entries_from_opening_balances.php');
    $migration->up();

    expect(FundAddedEntry::query()->count())->toBe(2)
        ->and(
            FundAddedEntry::query()
                ->get()
                ->sum(fn (FundAddedEntry $entry): float => (float) $entry->amount_encrypted),
        )->toBe(10000.0);
});
