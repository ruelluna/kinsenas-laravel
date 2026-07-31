import type { FundBalance } from '@/types/savings';

export type DashboardSetupStep = {
    key: string;
    label: string;
    complete: boolean;
    href: string;
};

export type DashboardSetup = {
    hasPlan: boolean;
    hasIncome: boolean;
    hasLockedIncome: boolean;
    hasBank: boolean;
    hasSpending: boolean;
    complete: boolean;
    steps: DashboardSetupStep[];
};

export type DashboardPlan = {
    id: string;
    name: string;
    hasLockedIncome: boolean;
};

export type DashboardLowBalanceFund = {
    categoryId: string;
    name: string;
    percentUsed: number;
};

export type DashboardSummary = {
    totalRemaining: string | null;
    totalInBanks: string | null;
    attentionCount: number;
    pendingTransferCount: number;
    pendingSpendCount: number;
    lowBalanceFunds: DashboardLowBalanceFund[];
};

export type DashboardBankBalance = {
    bankId: string;
    bankName: string;
    logoUrl: string | null;
    total: string;
    byCategory: Array<{
        categoryId: string;
        categoryName: string;
        total: string;
    }>;
};

export type DashboardPendingAction = {
    id: string;
    type: 'transfer' | 'spend';
    amount: string | null;
    description: string | null;
    date: string;
    label: string;
    confirmHref: string;
};

export type DashboardPendingActions = {
    transfers: DashboardPendingAction[];
    spends: DashboardPendingAction[];
};

export type DashboardActivityItem = {
    id: string;
    type: 'transfer' | 'spend';
    amount: string | null;
    description: string | null;
    date: string;
    label: string;
};

export type DashboardFeatures = {
    transfers: boolean;
    reports: boolean;
};

export type DashboardQuickLinks = {
    income: string;
    spending: string;
    transfers: string;
    banks: string;
    plan: string;
    reports: string;
};

export type DashboardPageProps = {
    setup: DashboardSetup;
    plan: DashboardPlan | null;
    summary: DashboardSummary;
    fundBalances: FundBalance[];
    bankBalances: DashboardBankBalance[];
    pendingActions: DashboardPendingActions;
    recentActivity: DashboardActivityItem[];
    features: DashboardFeatures;
    quickLinks: DashboardQuickLinks;
};
