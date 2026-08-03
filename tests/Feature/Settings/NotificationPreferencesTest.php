<?php

use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('shows notification preference settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('settings.notifications.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/notifications')
            ->has('preferences')
            ->has('pushSubscriptionCount'));
});

it('updates notification preferences', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('settings.notifications.update'), [
            'emailTeamInvitations' => false,
            'emailPendingActions' => true,
            'emailBillingReminders' => true,
            'inAppTeamInvitations' => true,
            'inAppPendingActions' => false,
            'inAppBillingReminders' => true,
            'pushEnabled' => false,
            'pushPendingActions' => true,
            'pushBillingReminders' => false,
        ])
        ->assertRedirect(route('settings.notifications.edit'));

    $preferences = $user->notificationPreferences()->firstOrFail();

    expect($preferences->email_team_invitations)->toBeFalse()
        ->and($preferences->in_app_pending_actions)->toBeFalse()
        ->and($preferences->push_billing_reminders)->toBeFalse();
});
