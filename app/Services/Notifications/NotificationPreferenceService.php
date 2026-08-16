<?php

namespace App\Services\Notifications;

use App\Enums\NotificationKind;
use App\Models\User;
use App\Models\UserNotificationPreference;
use NotificationChannels\WebPush\WebPushChannel;

class NotificationPreferenceService
{
    public function forUser(User $user): UserNotificationPreference
    {
        return $user->notificationPreferences()
            ->firstOrCreate([], UserNotificationPreference::defaultAttributes());
    }

    /**
     * @return list<string>
     */
    public function channelsFor(User $user, NotificationKind $kind): array
    {
        $preferences = $this->forUser($user);
        $channels = [];

        if ($this->wantsDatabase($preferences, $kind)) {
            $channels[] = 'database';
        }

        if ($this->wantsMail($preferences, $kind)) {
            $channels[] = 'mail';
        }

        if ($this->wantsWebPush($user, $preferences, $kind)) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function wantsDatabase(UserNotificationPreference $preferences, NotificationKind $kind): bool
    {
        return match ($kind) {
            NotificationKind::TeamInvitation,
            NotificationKind::TeamInvitationAccepted => $preferences->in_app_team_invitations,
            NotificationKind::PendingSpendConfirmation,
            NotificationKind::PendingTransferConfirmation,
            NotificationKind::PendingActionConfirmed => $preferences->in_app_pending_actions,
            NotificationKind::LowFundBalance,
            NotificationKind::TrialEndingReminder,
            NotificationKind::SubscriptionPastDue => $preferences->in_app_billing_reminders,
            NotificationKind::IncomeReminder => $preferences->in_app_pending_actions,
            NotificationKind::TestPush => true,
        };
    }

    public function wantsMail(UserNotificationPreference $preferences, NotificationKind $kind): bool
    {
        return match ($kind) {
            NotificationKind::TeamInvitation => $preferences->email_team_invitations,
            NotificationKind::PendingSpendConfirmation,
            NotificationKind::PendingTransferConfirmation => $preferences->email_pending_actions,
            NotificationKind::LowFundBalance,
            NotificationKind::TeamInvitationAccepted,
            NotificationKind::PendingActionConfirmed,
            NotificationKind::IncomeReminder,
            NotificationKind::TestPush => false,
            NotificationKind::TrialEndingReminder,
            NotificationKind::SubscriptionPastDue => $preferences->email_billing_reminders,
        };
    }

    public function wantsWebPush(User $user, UserNotificationPreference $preferences, NotificationKind $kind): bool
    {
        if (! $preferences->push_enabled || $user->pushSubscriptions()->doesntExist()) {
            return false;
        }

        return match ($kind) {
            NotificationKind::TeamInvitation => $preferences->push_team_invitations,
            NotificationKind::PendingSpendConfirmation,
            NotificationKind::PendingTransferConfirmation => $preferences->push_pending_actions,
            NotificationKind::LowFundBalance => $preferences->push_low_fund_balance,
            NotificationKind::TrialEndingReminder,
            NotificationKind::SubscriptionPastDue => $preferences->push_billing_reminders,
            NotificationKind::TeamInvitationAccepted => $preferences->push_team_activity,
            NotificationKind::PendingActionConfirmed => $preferences->push_action_updates,
            NotificationKind::IncomeReminder => $preferences->push_income_reminders,
            NotificationKind::TestPush => true,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function toSharedArray(UserNotificationPreference $preferences): array
    {
        return [
            'emailTeamInvitations' => $preferences->email_team_invitations,
            'emailPendingActions' => $preferences->email_pending_actions,
            'emailBillingReminders' => $preferences->email_billing_reminders,
            'inAppTeamInvitations' => $preferences->in_app_team_invitations,
            'inAppPendingActions' => $preferences->in_app_pending_actions,
            'inAppBillingReminders' => $preferences->in_app_billing_reminders,
            'pushEnabled' => $preferences->push_enabled,
            'pushTeamInvitations' => $preferences->push_team_invitations,
            'pushPendingActions' => $preferences->push_pending_actions,
            'pushLowFundBalance' => $preferences->push_low_fund_balance,
            'pushBillingReminders' => $preferences->push_billing_reminders,
            'pushTeamActivity' => $preferences->push_team_activity,
            'pushIncomeReminders' => $preferences->push_income_reminders,
            'pushActionUpdates' => $preferences->push_action_updates,
        ];
    }
}
