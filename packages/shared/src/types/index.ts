export type { User, Auth, TeamInvitationContext, RegisterContext } from './auth';
export type {
    Team,
    TeamRole,
    TeamSummary,
    TeamMember,
    TeamInvitation,
    TeamInvitationCreated,
    TeamPermissions,
    TeamRoleOption,
    TeamsIndexResponse,
    TeamShowResponse,
    TeamMutationResponse,
    TeamInvitationAcceptResponse,
    DashboardInvitation,
} from './teams';
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
    CategoryAllocationType,
    DeductionMode,
    FundBalance,
    FundSpend,
    FundTransfer,
    IncomePeriod,
    IncomePeriodShowResponse,
    IncomeBreakdownRow,
    IncomeCustomCategory,
    IncomeDistributionTodo,
    IncomeDistributionTodoProgress,
    IncomeDistributionTodoCompleteResponse,
    SavingsPlan,
    SavingsPlanDetail,
    SavingsCategory,
    SavingsCategoryOpeningBalance,
    SavingsBank,
    SavingsBankOption,
    SavingsRecipient,
    RecipientTypeOption,
    ReportTotals,
    FundGraphData,
    DashboardGraphData,
    FundUtilizationPoint,
    SpendingByFundPoint,
    SpendingOverTimePoint,
    IncomeVsSpendingPoint,
    TopRecipientPoint,
} from './savings';
export type {
    SubscriptionSummary,
    BillingPlan,
    BillingPlanPrice,
    BillingSubscriptionDetail,
    PaymentMethodConfig,
    BillingPageResponse,
    PaymentSubmissionResponse,
} from './billing';
export type {
    NotificationItem,
    NotificationInboxPage,
    NotificationInboxResponse,
    NotificationUnreadCountResponse,
} from './notifications';
export type {
    SettingsProfileUser,
    SettingsProfileResponse,
    SettingsProfileUpdatePayload,
    SettingsProfileUpdateResponse,
    SettingsProfileDeletePayload,
    SettingsPasswordUpdatePayload,
    NotificationPreferences,
    NotificationPreferencesResponse,
    NotificationPreferencesUpdateResponse,
    BetaFeedbackCategoryOption,
    BetaFeedbackCreateResponse,
    BetaFeedbackStorePayload,
    BetaFeedbackStoreResponse,
} from './settings';

export type ApiSharedProps = {
    user: import('./auth').User | null;
    currentTeam: import('./teams').Team | null;
    teams: import('./teams').Team[];
    vaultLocked: boolean;
    subscription: import('./billing').SubscriptionSummary | null;
    billingMode: string;
    openBeta?: {
        isActive: boolean;
        isParticipant: boolean;
    };
};

export type ApiBootstrapProps = ApiSharedProps & {
    emailVerified: boolean;
    canAccessApp: boolean;
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
