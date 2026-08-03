<?php

namespace App\Enums;

enum NotificationKind: string
{
    case TeamInvitation = 'team_invitation';
    case PendingSpendConfirmation = 'pending_spend_confirmation';
    case PendingTransferConfirmation = 'pending_transfer_confirmation';
    case LowFundBalance = 'low_fund_balance';
    case TrialEndingReminder = 'trial_ending_reminder';
    case SubscriptionPastDue = 'subscription_past_due';
    case TeamInvitationAccepted = 'team_invitation_accepted';
    case PendingActionConfirmed = 'pending_action_confirmed';
    case IncomeReminder = 'income_reminder';
    case BetaApproved = 'beta_approved';
    case TestPush = 'test_push';
}
