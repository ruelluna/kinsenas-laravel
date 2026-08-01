export type SubscriptionFeatureOption = {
    value: string;
    label: string;
};

export type TrialOfferPrice = {
    id: string;
    interval: string;
    intervalLabel: string;
    amount: number;
    currency: string;
};

export type TrialOffer = {
    id: string;
    name: string;
    slug: string;
    trialDays: number;
    prices: TrialOfferPrice[];
};

export type OpenBetaOffer = {
    launchDiscountPercent: number;
};

export type OpenBetaInfo = {
    isActive: boolean;
    launchDiscountPercent: number;
    applicationStatus: string | null;
    applicationStatusLabel: string | null;
    isParticipant: boolean;
    isApproved: boolean;
    isPending: boolean;
    launchDiscountEligible: boolean;
    appliedAt: string | null;
    approvedAt: string | null;
};

export type AdminBetaApplication = {
    id: number;
    name: string;
    email: string;
    status: string;
    statusLabel: string;
    emailVerified: boolean;
    appliedAt: string | null;
    approvedAt: string | null;
};

export type SharedSubscription = {
    teamId: number;
    teamName: string;
    teamSlug: string;
    isPersonalTeam: boolean;
    canManageBilling: boolean;
    status: string | null;
    statusLabel: string | null;
    trialEndsAt: string | null;
    currentPeriodEndsAt?: string | null;
    hasAccess: boolean;
    daysRemaining: number | null;
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
    slug: string;
    name: string;
    isPersonal: boolean;
    ownerName: string | null;
    ownerEmail: string | null;
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

export type AdminBetaFeedback = {
    id: string;
    message: string;
    category: string | null;
    categoryLabel: string | null;
    userName: string | null;
    userEmail: string | null;
    teamName: string | null;
    createdAt: string;
};

export type SharedData = {
    name: string;
    billingMode: string;
    openBeta: OpenBetaInfo;
    auth: import('@/types/auth').Auth;
    sidebarOpen: boolean;
    currentTeam: import('@/types/teams').Team | null;
    teams: import('@/types/teams').Team[];
    subscription: SharedSubscription | null;
    vaultLocked: boolean;
    registrationRecoveryKey?: string | null;
};
