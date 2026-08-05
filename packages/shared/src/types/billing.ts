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

export type BillingPlanPrice = {
    id: string;
    interval: string;
    intervalLabel: string;
    amount: number;
    currency: string;
};

export type BillingPlan = {
    id: string;
    name: string;
    slug: string;
    trialDays: number;
    prices: BillingPlanPrice[];
};

export type BillingSubscriptionDetail = {
    status: string;
    statusLabel: string;
    trialEndsAt: string | null;
    currentPeriodEndsAt: string | null;
    planName: string | null;
};

export type PaymentMethodConfig = {
    label: string;
    instructions: string;
    qrImageUrl: string | null;
};

export type BillingPageResponse = {
    team: {
        id: number;
        name: string;
        slug: string;
        isPersonal: boolean;
    };
    canManageBilling: boolean;
    subscription: BillingSubscriptionDetail | null;
    plans: BillingPlan[];
    paymentMethod: PaymentMethodConfig | null;
};

export type PaymentSubmissionResponse = {
    message: string;
    paymentSubmission: {
        id: string;
        status: string;
        referenceNumber: string;
        createdAt: string | null;
    };
};
