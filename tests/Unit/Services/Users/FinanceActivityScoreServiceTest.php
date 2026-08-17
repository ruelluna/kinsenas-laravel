<?php

use App\Enums\FinanceActivityTier;
use App\Enums\TransferStatus;
use App\Models\Bank;
use App\Models\FundSpend;
use App\Models\FundTransfer;
use App\Models\IncomePeriod;
use App\Models\SavingsCategory;
use App\Models\SavingsPlan;
use App\Models\Team;
use App\Models\User;
use App\Services\Users\FinanceActivityScoreService;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        BillingSeeder::class,
        SavingsFormulaTemplateSeeder::class,
    ]);

    $this->service = app(FinanceActivityScoreService::class);
});

it('scores zero for a team with no finance activity', function () {
    $team = Team::factory()->create();

    $snapshot = $this->service->scoreForTeam($team);

    expect($snapshot->score)->toBe(0)
        ->and($snapshot->tier)->toBe(FinanceActivityTier::Inactive)
        ->and($snapshot->lastFinanceActivityAt)->toBeNull();
});

it('awards fifty setup points when all milestones are complete', function () {
    Carbon::setTestNow('2026-08-17 12:00:00');

    $user = User::factory()->create();
    $team = $user->personalTeam();

    unlockVaultForUser($user);

    Bank::factory()->create(['team_id' => $team->id]);

    $plan = SavingsPlan::factory()->create([
        'team_id' => $team->id,
        'created_by_user_id' => $user->id,
        'is_shared_with_team' => true,
    ]);

    IncomePeriod::query()->create([
        'plan_id' => $plan->id,
        'name' => 'August salary',
        'amount_encrypted' => '50000.00',
        'period_start' => now()->startOfMonth()->toDateString(),
        'is_locked' => true,
        'locked_at' => now(),
        'locked_by_user_id' => $user->id,
    ]);

    $category = SavingsCategory::factory()->create(['plan_id' => $plan->id]);

    FundTransfer::factory()->confirmed()->create([
        'savings_plan_id' => $plan->id,
        'from_category_id' => $category->id,
        'to_category_id' => $category->id,
        'created_by_user_id' => $user->id,
        'confirmed_by_user_id' => $user->id,
    ]);

    FundSpend::factory()->create([
        'savings_plan_id' => $plan->id,
        'category_id' => $category->id,
        'status' => TransferStatus::Confirmed,
        'confirmed_at' => now(),
        'created_by_user_id' => $user->id,
        'confirmed_by_user_id' => $user->id,
    ]);

    $snapshot = $this->service->scoreForTeam($team);

    expect($snapshot->breakdown['setup'])->toBe(50)
        ->and($snapshot->breakdown['recency'])->toBe(25)
        ->and($snapshot->breakdown['frequency'])->toBe(19)
        ->and($snapshot->score)->toBeGreaterThanOrEqual(80)
        ->and($snapshot->tier)->toBe(FinanceActivityTier::Activated);
});

it('assigns activated tier when income is locked even with a low score', function () {
    $user = User::factory()->create();
    $team = $user->personalTeam();

    Carbon::setTestNow('2026-02-17 12:00:00');

    unlockVaultForUser($user);

    $plan = SavingsPlan::factory()->create([
        'team_id' => $team->id,
        'created_by_user_id' => $user->id,
        'is_shared_with_team' => true,
    ]);

    IncomePeriod::query()->create([
        'plan_id' => $plan->id,
        'name' => 'Old salary',
        'amount_encrypted' => '50000.00',
        'period_start' => now()->startOfMonth()->toDateString(),
        'is_locked' => true,
        'locked_at' => now(),
        'locked_by_user_id' => $user->id,
    ]);

    Carbon::setTestNow('2026-08-17 12:00:00');

    $snapshot = $this->service->scoreForTeam($team);

    expect($snapshot->score)->toBeLessThanOrEqual(35)
        ->and($snapshot->tier)->toBe(FinanceActivityTier::Activated);
});

it('maps score ranges to activity tiers', function (int $setup, int $recency, int $frequency, FinanceActivityTier $expectedTier) {
    $tier = FinanceActivityScoreService::resolveTier(
        score: (int) round(($setup * 0.5) + ($recency * 0.25) + ($frequency * 0.25)),
        incomeLocked: false,
    );

    expect($tier)->toBe($expectedTier);
})->with([
    'inactive' => [0, 0, 0, FinanceActivityTier::Inactive],
    'partial lower bound' => [60, 0, 0, FinanceActivityTier::Partial],
    'active lower bound' => [100, 40, 40, FinanceActivityTier::Active],
    'activated by score' => [100, 100, 100, FinanceActivityTier::Activated],
]);

it('decays recency when the last finance action is older than thirty days', function () {
    Carbon::setTestNow('2026-08-17 12:00:00');

    $team = Team::factory()->create();

    Bank::factory()->create([
        'team_id' => $team->id,
        'created_at' => now()->subDays(45),
        'updated_at' => now()->subDays(45),
    ]);

    $snapshot = $this->service->scoreForTeam($team);

    expect($snapshot->breakdown['recency'])->toBe(6)
        ->and($snapshot->breakdown['setup'])->toBe(5);
});

it('persists refreshed scores on users and teams', function () {
    Carbon::setTestNow('2026-08-17 12:00:00');

    $user = User::factory()->create();
    $team = $user->personalTeam();

    Bank::factory()->create(['team_id' => $team->id]);

    $this->service->refreshTeam($team);
    $this->service->refreshUser($user);

    expect($team->fresh()->finance_activity_score)->toBeGreaterThan(0)
        ->and($user->fresh()->finance_activity_score)->toBeGreaterThan(0)
        ->and($team->fresh()->finance_activity_tier)->not->toBeNull()
        ->and($user->fresh()->last_finance_activity_at)->not->toBeNull();
});
