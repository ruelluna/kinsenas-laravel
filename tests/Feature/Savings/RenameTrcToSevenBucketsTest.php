<?php

use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
use App\Models\SavingsPlanPageGuidance;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Database\Seeders\SavingsPlanPageGuidanceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    $this->seed([
        SavingsFormulaTemplateSeeder::class,
        SavingsPlanPageGuidanceSeeder::class,
        BillingSeeder::class,
    ]);
});

it('seeds the trc template as seven buckets', function () {
    $template = SavingsFormulaTemplate::query()->where('slug', 'trc-savings')->firstOrFail();

    expect($template->name)->toBe('7 Buckets')
        ->and($template->description)->not->toContain('TRC')
        ->and($template->description)->not->toContain('Truly Rich Club');
});

it('creates a plan named seven buckets from the trc template', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);

    $template = SavingsFormulaTemplate::query()->where('slug', 'trc-savings')->firstOrFail();

    test()->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    expect(SavingsPlan::query()->value('name'))->toBe('7 Buckets');
});

it('backfills legacy trc plan and guidance names after migration', function () {
    $template = SavingsFormulaTemplate::query()->where('slug', 'trc-savings')->firstOrFail();
    $template->update([
        'name' => 'TRC — Truly Rich Club',
        'description' => 'TRC stands for Truly Rich Club — a seven-bucket payday split.',
    ]);

    $user = User::factory()->create();
    test()->unlockVaultFor($user);

    test()->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    SavingsPlan::query()->update(['name' => 'TRC — Truly Rich Club']);

    SavingsPlanPageGuidance::query()->update([
        'chooser_intro' => 'We recommend TRC (Truly Rich Club) for members who want discipline.',
    ]);

    DB::table('migrations')
        ->where('migration', '2026_08_12_222205_rename_trc_to_seven_buckets')
        ->delete();

    $this->artisan('migrate', ['--path' => 'database/migrations/2026_08_12_222205_rename_trc_to_seven_buckets.php']);

    $template->refresh();

    expect($template->name)->toBe('7 Buckets')
        ->and($template->description)->not->toContain('TRC')
        ->and(SavingsPlan::query()->value('name'))->toBe('7 Buckets')
        ->and(SavingsPlanPageGuidance::query()->value('chooser_intro'))
        ->not->toContain('TRC')
        ->and(SavingsPlanPageGuidance::query()->value('chooser_intro'))
        ->not->toContain('Truly Rich Club');
});
