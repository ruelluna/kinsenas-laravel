<?php

namespace App\Enums;

enum UserActivityAction: string
{
    case TeamInvitationSent = 'team.invitation.sent';
    case TeamInvitationCancelled = 'team.invitation.cancelled';
    case TeamInvitationAccepted = 'team.invitation.accepted';
    case TeamInvitationDeclined = 'team.invitation.declined';
    case TeamInvitationExpired = 'team.invitation.expired';
    case TeamMemberRoleUpdated = 'team.member.role_updated';
    case TeamMemberRemoved = 'team.member.removed';
    case TeamLeft = 'team.left';
    case TeamCreated = 'team.created';
    case TeamUpdated = 'team.updated';
    case TeamDeleted = 'team.deleted';
    case TeamSwitched = 'team.switched';
    case SavingsBankCreated = 'savings.bank.created';
    case SavingsBankUpdated = 'savings.bank.updated';
    case SavingsPlanCreated = 'savings.plan.created';
    case SavingsPlanUpdated = 'savings.plan.updated';
    case SavingsIncomeRecorded = 'savings.income.recorded';
    case SavingsSpendingRecorded = 'savings.spending.recorded';
    case SavingsTransferCreated = 'savings.transfer.created';
    case VaultUnlocked = 'vault.unlocked';
    case AuthLoggedIn = 'auth.logged_in';
    case AuthLoggedOut = 'auth.logged_out';
    case AuthRegistered = 'auth.registered';
    case ProfileUpdated = 'profile.updated';
    case PasswordUpdated = 'password.updated';
    case NotificationPreferencesUpdated = 'notifications.preferences_updated';
    case BillingPaymentSubmitted = 'billing.payment_submitted';
    case BillingPaymentApproved = 'billing.payment_approved';
    case BillingPaymentRejected = 'billing.payment_rejected';
    case AdminBetaApplicationApproved = 'admin.beta_application.approved';
    case AdminBetaApplicationRejected = 'admin.beta_application.rejected';
    case AdminPlatformUserUpdated = 'admin.platform_user.updated';
    case AdminPlatformUserDeleted = 'admin.platform_user.deleted';

    public function label(): string
    {
        return match ($this) {
            self::TeamInvitationSent => 'Invitation sent',
            self::TeamInvitationCancelled => 'Invitation cancelled',
            self::TeamInvitationAccepted => 'Invitation accepted',
            self::TeamInvitationDeclined => 'Invitation declined',
            self::TeamInvitationExpired => 'Invitation expired',
            self::TeamMemberRoleUpdated => 'Member role updated',
            self::TeamMemberRemoved => 'Member removed',
            self::TeamLeft => 'Left team',
            self::TeamCreated => 'Team created',
            self::TeamUpdated => 'Team updated',
            self::TeamDeleted => 'Team deleted',
            self::TeamSwitched => 'Switched team',
            self::SavingsBankCreated => 'Bank added',
            self::SavingsBankUpdated => 'Bank updated',
            self::SavingsPlanCreated => 'Savings plan created',
            self::SavingsPlanUpdated => 'Savings plan updated',
            self::SavingsIncomeRecorded => 'Income recorded',
            self::SavingsSpendingRecorded => 'Spending recorded',
            self::SavingsTransferCreated => 'Transfer created',
            self::VaultUnlocked => 'Vault unlocked',
            self::AuthLoggedIn => 'Logged in',
            self::AuthLoggedOut => 'Logged out',
            self::AuthRegistered => 'Registered',
            self::ProfileUpdated => 'Profile updated',
            self::PasswordUpdated => 'Password updated',
            self::NotificationPreferencesUpdated => 'Notification preferences updated',
            self::BillingPaymentSubmitted => 'Payment submitted',
            self::BillingPaymentApproved => 'Payment approved',
            self::BillingPaymentRejected => 'Payment rejected',
            self::AdminBetaApplicationApproved => 'Beta application approved',
            self::AdminBetaApplicationRejected => 'Beta application rejected',
            self::AdminPlatformUserUpdated => 'Platform user updated',
            self::AdminPlatformUserDeleted => 'Platform user deleted',
        };
    }
}
