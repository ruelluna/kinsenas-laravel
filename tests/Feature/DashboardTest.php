<?php

use App\Enums\TeamRole;
use App\Models\Bank;
use App\Models\FundSpend;
use App\Models\FundTransfer;
use App\Models\IncomePeriod;
use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
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

function dashboardRoute(User $user): string
{
    return route('dashboard', ['current_team' => $user->currentTeam->slug]);
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

it('guests are redirected to the login page', function () {
    $user = User::factory()->create();

    $response = $this->get(dashboardRoute($user));
    $response->assertRedirect(route('login'));
});

it('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);

    $response = $this
        ->actingAs($user)
        ->get(dashboardRoute($user));

    $response->assertOk();
});

it('dashboard includes pending invitations for the authenticated user', function () {
    $owner = User::factory()->create(['name' => 'Taylor Otwell']);
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $team = Team::factory()->create(['name' => 'Laravel Team']);

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $this->unlockVaultFor($invitedUser);

    $response = $this
        ->actingAs($invitedUser)
        ->get(dashboardRoute($invitedUser));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('dashboard')
        ->has('pendingInvitations', 1)
        ->where('pendingInvitations.0.code', $invitation->code)
        ->where('pendingInvitations.0.inviterName', 'Taylor Otwell')
        ->where('pendingInvitations.0.team.name', 'Laravel Team')
        ->where('pendingInvitations.0.team.slug', $team->slug)
        ->missing('pendingInvitations.0.teamName'),
    );
});

it('dashboard does not include accepted invitations', function () {
    $owner = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    TeamInvitation::factory()->accepted()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $this->unlockVaultFor($invitedUser);

    $response = $this
        ->actingAs($invitedUser)
        ->get(dashboardRoute($invitedUser));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('dashboard')
        ->has('pendingInvitations', 0),
    );
});

it('dashboard excludes expired invitations without deleting them', function () {
    $owner = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->expired()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $this->unlockVaultFor($invitedUser);

    $response = $this
        ->actingAs($invitedUser)
        ->get(dashboardRoute($invitedUser));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('dashboard')
        ->has('pendingInvitations', 0),
    );

    $this->assertDatabaseHas('team_invitations', [
        'id' => $invitation->id,
    ]);
});

it('dashboard does not include or delete other users invitations', function () {
    $owner = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->expired()->create([
        'team_id' => $team->id,
        'email' => 'someone@example.com',
        'invited_by' => $owner->id,
    ]);

    $this->unlockVaultFor($invitedUser);

    $response = $this
        ->actingAs($invitedUser)
        ->get(dashboardRoute($invitedUser));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('dashboard')
        ->has('pendingInvitations', 0),
    );

    $this->assertDatabaseHas('team_invitations', [
        'id' => $invitation->id,
    ]);
});

it('dashboard setup shows incomplete steps for a new team without a plan', function () {
    $user = User::factory()->create();
    $this->unlockVaultFor($user);

    $response = $this->actingAs($user)->get(dashboardRoute($user));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('dashboard')
        ->where('setup.hasPlan', false)
        ->where('setup.complete', false)
        ->has('setup.steps', 5)
        ->where('setup.steps.0.key', 'plan')
        ->where('setup.steps.0.complete', false)
        ->where('plan', null)
        ->has('fundBalances', 0),
    );
});

it('dashboard summary includes fund totals after locked income', function () {
    [$user, $plan, $everydayCategory] = createUserWithLockedIncome();

    $response = $this->actingAs($user)->get(dashboardRoute($user));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('dashboard')
        ->where('setup.hasPlan', true)
        ->where('setup.hasLockedIncome', true)
        ->where('plan.name', $plan->name)
        ->where('summary.totalRemaining', '50000.00')
        ->has('fundBalances', 3)
        ->where('fundBalances.0.name', 'Everyday Fund')
        ->where('fundBalances.0.remaining', '35000.00'),
    );
});

it('dashboard includes pending spend confirmations and recent activity', function () {
    [$user, $plan, $everydayCategory] = createUserWithLockedIncome();

    $bank = Bank::factory()->create([
        'team_id' => $user->currentTeam->id,
    ]);

    $this->actingAs($user)->post(route('savings.spending.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '500.00',
        'description' => 'Groceries',
        'spent_on' => '2026-01-15',
        'bank_id' => $bank->id,
    ]);

    $this->actingAs($user)->post(route('savings.spending.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '250.00',
        'description' => 'Coffee',
        'spent_on' => '2026-01-20',
    ]);

    $response = $this->actingAs($user)->get(dashboardRoute($user));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('dashboard')
        ->where('summary.pendingSpendCount', 1)
        ->where('summary.attentionCount', fn ($count) => $count >= 1)
        ->has('pendingActions.spends', 1)
        ->has('recentActivity', 1)
        ->where('recentActivity.0.description', 'Coffee')
        ->where('setup.hasSpending', true),
    );

    expect(FundSpend::query()->count())->toBe(2);
    expect(FundTransfer::query()->count())->toBe(0);
});

it('dashboard setup marks bank step complete when team has a bank', function () {
    [$user] = createUserWithLockedIncome();

    Bank::factory()->create([
        'team_id' => $user->currentTeam->id,
    ]);

    $response = $this->actingAs($user)->get(dashboardRoute($user));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('dashboard')
        ->where('setup.hasBank', true)
        ->where('setup.steps.3.complete', true),
    );
});
