<?php

use App\Enums\NotificationKind;
use App\Enums\TeamRole;
use App\Models\Bank;
use App\Models\SavingsPlan;
use App\Models\User;
use App\Notifications\Savings\PendingActionConfirmed;
use App\Services\Notifications\NotificationPreferenceService;
use App\Services\Savings\FundSpendService;
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

    [$creator] = createUserWithLockedIncome();
    $confirmer = User::factory()->create();
    $team = $creator->currentTeam;
    $team->members()->attach($confirmer, ['role' => TeamRole::Member->value]);

    $plan = SavingsPlan::query()->firstOrFail();
    $category = $plan->categories()->firstOrFail();
    $bank = Bank::factory()->create(['team_id' => $team->id]);

    $this->actingAs($creator);
    $this->unlockVaultFor($creator);

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
    $this->unlockVaultFor($confirmer);

    app(FundSpendService::class)->confirm($spend->fresh(), $confirmer);

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
