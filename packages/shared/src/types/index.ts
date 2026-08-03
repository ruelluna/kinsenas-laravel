export type { User, Auth } from './auth';
export type { Team, TeamRole, DashboardInvitation } from './teams';
export type {
    DashboardPageProps,
    DashboardSetup,
    DashboardSummary,
    DashboardPlan,
    DashboardPendingActions,
    DashboardActivityItem,
    DashboardFeatures,
    DashboardQuickLinks,
    DashboardBankBalance,
} from './dashboard';
export type {
    FundBalance,
    FundSpend,
    FundTransfer,
    IncomePeriod,
    SavingsPlan,
    SavingsBank,
    SavingsRecipient,
} from './savings';
export type { SubscriptionSummary } from './billing';

export type ApiSharedProps = {
    user: import('./auth').User | null;
    currentTeam: import('./teams').Team | null;
    teams: import('./teams').Team[];
    vaultLocked: boolean;
    subscription: import('./billing').SubscriptionSummary | null;
    billingMode: string;
};

export type PaginatedResponse<T> = {
    data: T[];
    meta: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
    };
};
