<?php

use App\Enums\NotificationKind;
use App\Enums\TeamRole;
use App\Models\Bank;
use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsPlan;
use App\Models\User;
use App\Notifications\Savings\PendingActionConfirmed;
use App\Services\Notifications\NotificationPreferenceService;
use App\Services\Savings\FundSpendService;
use App\Services\Vault\VaultKeyManager;
use Database\Seeders\BillingSeeder;
use Database\Seeders\SavingsFormulaTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use Tests\Concerns\UnlocksVault;

uses(RefreshDatabase::class, UnlocksVault::class);

beforeEach(function () {
    $this->seed([
        SavingsFormulaTemplateSeeder::class,
        BillingSeeder::class,
    ]);
});

it('includes web push channel for team invitations when enabled', function () {
    $user = User::factory()->create();
    $preferences = $user->notificationPreferences()->firstOrFail();
    $preferences->update([
        'push_enabled' => true,
        'push_team_invitations' => true,
    ]);
    $user->updatePushSubscription('https://push.example.test/team-invite', 'key', 'auth');

    $channels = app(NotificationPreferenceService::class)
        ->channelsFor($user, NotificationKind::TeamInvitation);

    expect($channels)->toContain(WebPushChannel::class);
});

it('notifies the creator when a pending spend is confirmed by someone else', function () {
    Notification::fake();

    $creator = User::factory()->create();
    $this->unlockVaultFor($creator);
    $team = $creator->currentTeam;

    $template = SavingsFormulaTemplate::query()
        ->where('slug', 'abundant-formula')
        ->firstOrFail();

    $this->actingAs($creator)->post(route('savings.plan.from-template', [
        'current_team' => $team->slug,
        'template' => $template->id,
    ]));

    $plan = SavingsPlan::query()->firstOrFail();
    $plan->update(['is_shared_with_team' => true]);

    $teamDek = app(VaultKeyManager::class)->userDek();
    app(VaultKeyManager::class)->storeTeamDek($team, $teamDek);

    $this->actingAs($creator)->post(route('savings.income.store', [
        'current_team' => $team->slug,
    ]), [
        'name' => 'January salary',
        'amount' => '50000.00',
        'period_start' => '2026-01-01',
    ]);

    $confirmer = User::factory()->create();
    $team->members()->attach($confirmer, ['role' => TeamRole::Member->value]);

    $category = $plan->categories()->firstOrFail();
    $bank = Bank::factory()->create(['team_id' => $team->id]);

    $this->actingAs($creator);
    $this->unlockVaultFor($creator);
    app(VaultKeyManager::class)->storeTeamDek($team, $teamDek);

    $spend = app(FundSpendService::class)->create(
        $plan,
        $category->id,
        '100.00',
        'Test spend',
        now()->toDateString(),
        $bank->id,
        null,
        $creator,
    );

    $this->actingAs($confirmer);
    app(VaultKeyManager::class)->storeUserDek($teamDek);
    app(VaultKeyManager::class)->storeTeamDek($team, $teamDek);

    app(FundSpendService::class)->confirm($spend->fresh(['plan.team']), $confirmer);

    Notification::assertSentTo($creator, PendingActionConfirmed::class);
    Notification::assertNotSentTo($confirmer, PendingActionConfirmed::class);
});

it('excludes web push for team invitations when the toggle is off', function () {
    $user = User::factory()->create();
    $preferences = $user->notificationPreferences()->firstOrFail();
    $preferences->update([
        'push_enabled' => true,
        'push_team_invitations' => false,
    ]);
    $user->updatePushSubscription('https://push.example.test/team-invite-off', 'key', 'auth');

    $channels = app(NotificationPreferenceService::class)
        ->channelsFor($user, NotificationKind::TeamInvitation);

    expect($channels)->not->toContain(WebPushChannel::class);
});
