export type NotificationItem = {
    id: string;
    kind: string;
    title: string;
    body: string;
    actionUrl: string | null;
    meta: Record<string, unknown>;
    readAt: string | null;
    createdAt: string | null;
    isUnread: boolean;
};

export type SharedNotifications = {
    unreadCount: number;
};

export type NotificationPreferences = {
    emailTeamInvitations: boolean;
    emailPendingActions: boolean;
    emailBillingReminders: boolean;
    inAppTeamInvitations: boolean;
    inAppPendingActions: boolean;
    inAppBillingReminders: boolean;
    pushEnabled: boolean;
    pushPendingActions: boolean;
    pushBillingReminders: boolean;
};

export type WebPushConfig = {
    vapidPublicKey: string | null;
};
