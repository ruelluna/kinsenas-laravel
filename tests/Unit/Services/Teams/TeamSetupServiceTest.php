<?php

use App\Enums\TeamRole;
use App\Models\Bank;
use App\Models\IncomePeriod;
use App\Models\SavingsPlan;
use App\Models\Team;
use App\Models\User;
use App\Services\Savings\SavingsPlanService;
use App\Services\Teams\TeamSetupService;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        BillingSeeder::class,
        SavingsFormulaTemplateSeeder::class,
    ]);
});

it('is not ready when bank plan and income are missing', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $readiness = app(TeamSetupService::class)->readinessForInvites($team, $user);

    expect($readiness->ready)->toBeFalse()
        ->and($readiness->steps)->toHaveCount(3)
        ->and(collect($readiness->steps)->pluck('complete')->all())->toBe([false, false, false]);
});

it('is ready when bank plan and income exist', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    prepareTeamForInvites($user, $team);

    $readiness = app(TeamSetupService::class)->readinessForInvites($team, $user);

    expect($readiness->ready)->toBeTrue()
        ->and(collect($readiness->steps)->every(fn (array $step) => $step['complete']))->toBeTrue();
});

it('includes spending in dashboard setup steps only', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    unlockVaultForUser($user);

    Bank::factory()->create(['team_id' => $team->id]);

    $plan = SavingsPlan::factory()->create([
        'team_id' => $team->id,
        'created_by_user_id' => $user->id,
        'is_shared_with_team' => true,
    ]);

    IncomePeriod::query()->create([
        'plan_id' => $plan->id,
        'name' => 'January salary',
        'amount_encrypted' => '50000.00',
        'period_start' => now()->startOfMonth()->toDateString(),
    ]);

    $steps = app(TeamSetupService::class)->dashboardSetupSteps($team, $user, false);

    expect($steps)->toHaveCount(4)
        ->and(end($steps)['key'])->toBe('spending')
        ->and(end($steps)['complete'])->toBeFalse();
});

it('uses the acting users visible savings plan', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    Bank::factory()->create(['team_id' => $team->id]);

    SavingsPlan::factory()->create([
        'team_id' => $team->id,
        'created_by_user_id' => User::factory()->create()->id,
        'is_shared_with_team' => false,
    ]);

    expect(app(SavingsPlanService::class)->forTeam($team, $user))->toBeNull()
        ->and(app(TeamSetupService::class)->isReadyForInvites($team, $user))->toBeFalse();
});
