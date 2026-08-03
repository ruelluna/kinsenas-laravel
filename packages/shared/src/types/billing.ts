export type SubscriptionSummary = {
    teamId: number;
    teamName: string;
    teamSlug: string;
    status: string;
    statusLabel: string;
    planName: string | null;
    trialDaysRemaining: number | null;
    hasAccess: boolean;
};
