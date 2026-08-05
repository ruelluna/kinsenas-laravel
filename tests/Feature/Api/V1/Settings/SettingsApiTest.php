<?php

use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(BillingSeeder::class);
});

it('returns and updates the authenticated profile', function (): void {
    $user = User::factory()->betaApproved()->create([
        'name' => 'Original Member',
        'email' => 'original@example.com',
    ]);
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/settings/profile')
        ->assertSuccessful()
        ->assertJsonPath('user.email', 'original@example.com');

    $this->patchJson('/api/v1/settings/profile', [
        'name' => 'Updated Member',
        'email' => 'updated@example.com',
    ])
        ->assertSuccessful()
        ->assertJsonPath('user.name', 'Updated Member')
        ->assertJsonPath('user.email', 'updated@example.com');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Member',
        'email' => 'updated@example.com',
    ]);
});

it('returns and updates notification preferences', function (): void {
    $user = User::factory()->betaApproved()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/settings/notifications')
        ->assertSuccessful()
        ->assertJsonStructure(['preferences', 'paydayDayOfMonth', 'pushSubscriptionCount', 'vapidPublicKey']);

    $this->patchJson('/api/v1/settings/notifications', [
        'emailTeamInvitations' => true,
        'emailPendingActions' => false,
        'emailBillingReminders' => true,
        'inAppTeamInvitations' => true,
        'inAppPendingActions' => true,
        'inAppBillingReminders' => true,
        'pushEnabled' => false,
        'pushTeamInvitations' => false,
        'pushPendingActions' => false,
        'pushLowFundBalance' => false,
        'pushBillingReminders' => false,
        'pushTeamActivity' => false,
        'pushIncomeReminders' => false,
        'pushActionUpdates' => false,
        'paydayDayOfMonth' => 15,
    ])
        ->assertSuccessful()
        ->assertJsonPath('preferences.emailTeamInvitations', true)
        ->assertJsonPath('paydayDayOfMonth', 15);

    $this->assertDatabaseHas('user_notification_preferences', [
        'user_id' => $user->id,
        'email_team_invitations' => true,
    ]);
});

it('returns billing details for the current team', function (): void {
    $user = User::factory()->betaApproved()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/settings/billing')
        ->assertSuccessful()
        ->assertJsonPath('team.id', $user->current_team_id)
        ->assertJsonStructure(['plans', 'paymentMethod']);
});
