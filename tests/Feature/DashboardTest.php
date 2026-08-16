<?php

use App\Enums\TeamRole;
use App\Models\Bank;
use App\Models\FundSpend;
use App\Models\FundTransfer;
use App\Models\Recipient;
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
        ->has('setup.steps', 4)
        ->where('setup.steps.0.key', 'bank')
        ->where('setup.steps.0.complete', false)
        ->where('setup.steps.1.key', 'plan')
        ->where('plan', null)
        ->has('fundBalances', 0),
    );
});

it('dashboard shows fund buckets after plan is chosen', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);
    $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

    $this->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    $response = $this->actingAs($user)->get(dashboardRoute($user));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('setup.hasPlan', true)
        ->where('setup.hasOpeningBalances', false)
        ->has('fundBalances', 3)
        ->where('fundBalances.0.canFund', true)
        ->where('fundBalances.0.remaining', '0.00'),
    );
});

it('dashboard summary includes fund totals after income is added', function () {
    [$user, $plan, $everydayCategory] = createUserWithLockedIncome();

    $response = $this->actingAs($user)->get(dashboardRoute($user));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('dashboard')
        ->where('setup.hasPlan', true)
        ->where('setup.canDrawFromFunds', true)
        ->where('plan.name', $plan->name)
        ->where('summary.defaultFundName', 'Everyday Fund')
        ->where('summary.defaultFundRemaining', '35000.00')
        ->where('summary.otherFundsRemaining', '15000.00')
        ->has('fundBalances', 3)
        ->where('fundBalances.0.name', 'Everyday Fund')
        ->where('fundBalances.0.allocationType', 'percentage')
        ->where('fundBalances.0.percentage', '70.00')
        ->where('fundBalances.0.remaining', '35000.00'),
    );
});

it('dashboard summary uses first bucket as default when everyday fund is renamed', function () {
    $user = User::factory()->create();
    test()->unlockVaultFor($user);
    $template = SavingsFormulaTemplate::query()->where('slug', 'abundant-formula')->firstOrFail();

    $this->actingAs($user)->post(route('savings.plan.from-template', [
        'current_team' => $user->currentTeam->slug,
        'template' => $template->id,
    ]));

    $plan = SavingsPlan::query()->firstOrFail();
    $dailyCategory = $plan->categories()->where('name', 'Everyday Fund')->firstOrFail();
    $dailyCategory->update(['name' => 'Daily Spending']);

    $this->actingAs($user)->post(route('savings.income.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'name' => 'January salary',
        'amount' => '50000.00',
        'period_start' => '2026-01-01',
    ]);

    $response = $this->actingAs($user)->get(dashboardRoute($user));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('dashboard')
        ->where('summary.defaultFundName', 'Daily Spending')
        ->where('summary.defaultFundRemaining', '35000.00')
        ->where('summary.otherFundsRemaining', '15000.00'),
    );
});

it('dashboard fund balances include bank metadata when assigned', function () {
    [$user, $plan, $everydayCategory] = createUserWithLockedIncome();

    $bank = Bank::factory()->create([
        'team_id' => $user->currentTeam->id,
        'name' => 'BPI',
        'account_label' => 'Main',
    ]);

    $everydayCategory->update(['bank_id' => $bank->id]);

    $response = $this->actingAs($user)->get(dashboardRoute($user));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('dashboard')
        ->where('fundBalances.0.bankId', $bank->id)
        ->where('fundBalances.0.bankDisplayName', 'BPI — Main'),
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

it('dashboard includes awaiting reimbursement count', function () {
    [$user, , $everydayCategory] = createUserWithLockedIncome();

    $this->actingAs($user)->post(route('savings.recipients.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'type' => 'person',
        'name' => 'Ana',
    ]);

    $payerId = Recipient::query()->firstOrFail()->id;

    $this->actingAs($user)->post(route('savings.spending.store', [
        'current_team' => $user->currentTeam->slug,
    ]), [
        'category_id' => $everydayCategory->id,
        'amount' => '1000.00',
        'description' => 'Bill for Ana',
        'spent_on' => '2026-01-15',
        'expects_reimbursement' => true,
        'expected_from_recipient_id' => $payerId,
    ]);

    $response = $this->actingAs($user)->get(dashboardRoute($user));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('dashboard')
        ->where('summary.awaitingReimbursementCount', 1)
        ->has('pendingActions.reimbursements', 1),
    );
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
        ->where('setup.steps.0.key', 'bank')
        ->where('setup.steps.0.complete', true),
    );
});

it('dashboard recent activity includes fund additions', function () {
    [$user, $plan, $everydayCategory] = createUserWithLockedIncome();

    $this->actingAs($user)->patch(route('savings.plan.category.opening-balance', [
        'current_team' => $user->currentTeam->slug,
        'category' => $everydayCategory->id,
    ]), [
        'amount' => '2000.00',
    ]);

    $response = $this->actingAs($user)->get(dashboardRoute($user));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('dashboard')
        ->has('recentActivity', 1)
        ->where('recentActivity.0.type', 'fund_addition')
        ->where('recentActivity.0.amount', '2000.00')
        ->where('recentActivity.0.label', $everydayCategory->name),
    );
});
