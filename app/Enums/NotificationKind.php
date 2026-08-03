<?php

namespace App\Enums;

enum NotificationKind: string
{
    case TeamInvitation = 'team_invitation';
    case PendingSpendConfirmation = 'pending_spend_confirmation';
    case PendingTransferConfirmation = 'pending_transfer_confirmation';
    case LowFundBalance = 'low_fund_balance';
    case TrialEndingReminder = 'trial_ending_reminder';
}
