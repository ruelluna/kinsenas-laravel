export type SettingsProfileUser = {
    id: number;
    name: string;
    email: string;
    emailVerifiedAt: string | null;
};

export type SettingsProfileResponse = {
    user: SettingsProfileUser;
    mustVerifyEmail: boolean;
};

export type SettingsProfileUpdatePayload = {
    name: string;
    email: string;
};

export type SettingsProfileUpdateResponse = {
    message: string;
    user: SettingsProfileUser;
};

export type SettingsProfileDeletePayload = {
    password: string;
};

export type SettingsPasswordUpdatePayload = {
    current_password: string;
    password: string;
    password_confirmation: string;
};

export type NotificationPreferences = {
    emailTeamInvitations: boolean;
    emailPendingActions: boolean;
    emailBillingReminders: boolean;
    inAppTeamInvitations: boolean;
    inAppPendingActions: boolean;
    inAppBillingReminders: boolean;
    pushEnabled: boolean;
    pushTeamInvitations: boolean;
    pushPendingActions: boolean;
    pushLowFundBalance: boolean;
    pushBillingReminders: boolean;
    pushTeamActivity: boolean;
    pushIncomeReminders: boolean;
    pushActionUpdates: boolean;
};

export type NotificationPreferencesResponse = {
    preferences: NotificationPreferences;
    paydayDayOfMonth: number | null;
    pushSubscriptionCount: number;
    vapidPublicKey: string | null;
};

export type NotificationPreferencesUpdateResponse = {
    message: string;
    preferences: NotificationPreferences;
    paydayDayOfMonth: number | null;
};

export type BetaFeedbackCategoryOption = {
    value: string;
    label: string;
};

export type BetaFeedbackCreateResponse = {
    categories: BetaFeedbackCategoryOption[];
};

export type BetaFeedbackStorePayload = {
    message: string;
    category: string;
};

export type BetaFeedbackStoreResponse = {
    message: string;
    feedback: {
        id: string;
        category: string | null;
        createdAt: string | null;
    };
};
