export type SubscriptionFeatureOption = {
    value: string;
    label: string;
};

export type AdminPlanPrice = {
    id?: string;
    interval: string;
    amount: number;
    currency: string;
    isActive: boolean;
};

export type AdminPlan = {
    id: string;
    name: string;
    slug: string;
    trialDays: number;
    features: string[];
    isActive: boolean;
    sortOrder: number;
    prices: AdminPlanPrice[];
    monthlyAmount?: number;
    yearlyAmount?: number;
    monthlyActive?: boolean;
    yearlyActive?: boolean;
};

export type AdminSubscriber = {
    id: number;
    name: string;
    email: string;
    isPlatformAdmin: boolean;
    subscription: {
        id: string;
        status: string;
        statusLabel: string;
        planName: string | null;
        planId: string;
        trialEndsAt: string | null;
        currentPeriodEndsAt: string | null;
        hasAccess: boolean;
    } | null;
    createdAt: string;
};

export type AdminPaymentSubmission = {
    id: string;
    referenceNumber: string;
    status: string;
    userName: string | null;
    userEmail: string | null;
    planName: string | null;
    interval: string | null;
    amount: number | null;
    proofImageUrl: string | null;
    notes: string | null;
    createdAt: string;
};

export type AdminPlatformUser = {
    id: number;
    name: string;
    email: string;
    isPlatformAdmin: boolean;
    subscriptionStatus: string | null;
    subscriptionStatusLabel: string | null;
};

export type FilterOption = {
    value: string;
    label: string;
};
