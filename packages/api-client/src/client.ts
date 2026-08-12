import type {
    ApiBootstrapProps,
    ApiSharedProps,
    BetaFeedbackCreateResponse,
    BetaFeedbackStorePayload,
    BetaFeedbackStoreResponse,
    BillingPageResponse,
    CategoryAllocationType,
    DashboardInvitation,
    DashboardPageProps,
    DeductionMode,
    FundSpend,
    FundTransfer,
    IncomeDistributionTodoCompleteResponse,
    IncomePeriod,
    IncomePeriodShowResponse,
    NotificationInboxResponse,
    NotificationPreferences,
    NotificationPreferencesResponse,
    NotificationPreferencesUpdateResponse,
    NotificationUnreadCountResponse,
    PaymentSubmissionResponse,
    RecipientTypeOption,
    RegisterContext,
    ReportTotals,
    SavingsBankOption,
    SavingsCategoryOpeningBalance,
    SavingsPlanDetail,
    SavingsRecipient,
    SettingsPasswordUpdatePayload,
    SettingsProfileDeletePayload,
    SettingsProfileResponse,
    SettingsProfileUpdatePayload,
    SettingsProfileUpdateResponse,
    TeamInvitationAcceptResponse,
    TeamInvitationCreated,
    TeamMutationResponse,
    TeamRole,
    TeamShowResponse,
    TeamsIndexResponse,
    User,
} from '@kinsenas/shared';

export type ApiClientConfig = {
    baseUrl: string;
    getToken: () => string | null;
    getTeamId?: () => number | null;
};

export class ApiError extends Error {
    constructor(
        message: string,
        public status: number,
        public errors?: Record<string, string[]>,
    ) {
        super(message);
        this.name = 'ApiError';
    }
}

type JsonBody = Record<string, unknown> | unknown[];

type Uploadable = Blob | File;

type SavingsPlanCategoryInput = {
    id?: string;
    name: string;
    allocation_type: CategoryAllocationType;
    percentage?: number | string | null;
    deduction_mode?: DeductionMode | null;
    deduction_value?: number | string | null;
    deduct_from_index?: number | null;
    bank_id?: string | null;
    opening_balance?: number | string | null;
};

export class KinsenasApiClient {
    constructor(private config: ApiClientConfig) {}

    private teamPath(teamId: number, suffix: string): string {
        return `/api/v1/teams/${teamId}${suffix}`;
    }

    private async request<T>(
        path: string,
        options: RequestInit = {},
    ): Promise<T> {
        const token = this.config.getToken();
        const teamId = this.config.getTeamId?.();

        const headers: Record<string, string> = {
            Accept: 'application/json',
            ...(options.body instanceof FormData
                ? {}
                : { 'Content-Type': 'application/json' }),
            ...(options.headers as Record<string, string>),
        };

        if (token) {
            headers.Authorization = `Bearer ${token}`;
        }

        if (teamId) {
            headers['X-Team-Id'] = String(teamId);
        }

        const response = await fetch(`${this.config.baseUrl}${path}`, {
            ...options,
            headers,
        });

        if (!response.ok) {
            const body = (await response.json().catch(() => ({}))) as {
                message?: string;
                errors?: Record<string, string[]>;
            };
            throw new ApiError(
                body.message ?? `Request failed (${response.status})`,
                response.status,
                body.errors,
            );
        }

        if (response.status === 204) {
            return undefined as T;
        }

        return response.json() as Promise<T>;
    }

    private jsonBody(payload: JsonBody): string {
        return JSON.stringify(payload);
    }

    readonly auth = {
        login: (email: string, password: string, deviceName = 'mobile') =>
            this.request<
                | { token: string; user: User }
                | { two_factor: boolean; message: string }
            >('/api/v1/auth/login', {
                method: 'POST',
                body: this.jsonBody({ email, password, device_name: deviceName }),
            }),

        register: (payload: {
            name: string;
            email: string;
            password: string;
            password_confirmation: string;
            marketing_emails_opt_in?: boolean;
            beta_code?: string | null;
            device_name?: string;
        }) =>
            this.request<{ token: string; user: User; recovery_key: string }>(
                '/api/v1/auth/register',
                {
                    method: 'POST',
                    body: this.jsonBody(payload),
                },
            ),

        registerContext: (params?: { invitation?: string; beta_code?: string }) => {
            const search = new URLSearchParams();
            if (params?.invitation) {
                search.set('invitation', params.invitation);
            }
            if (params?.beta_code) {
                search.set('beta_code', params.beta_code);
            }
            const query = search.toString();

            return this.request<RegisterContext>(
                `/api/v1/auth/register-context${query ? `?${query}` : ''}`,
            );
        },

        forgotPassword: (email: string) =>
            this.request<{ message: string }>('/api/v1/auth/forgot-password', {
                method: 'POST',
                body: this.jsonBody({ email }),
            }),

        resetPassword: (payload: {
            token: string;
            email: string;
            password: string;
            password_confirmation: string;
        }) =>
            this.request<{ message: string }>('/api/v1/auth/reset-password', {
                method: 'POST',
                body: this.jsonBody(payload),
            }),

        bootstrap: () => this.request<ApiBootstrapProps>('/api/v1/auth/bootstrap'),

        me: () => this.request<ApiSharedProps>('/api/v1/auth/me'),

        logout: () => this.request<void>('/api/v1/auth/logout', { method: 'POST' }),
    };

    readonly vault = {
        unlock: (payload: { password?: string; recovery_key?: string }) =>
            this.request<{ message: string }>('/api/v1/vault/unlock', {
                method: 'POST',
                body: this.jsonBody(payload),
            }),
    };

    readonly invitations = {
        accept: (invitationCode: string) =>
            this.request<TeamInvitationAcceptResponse>(
                `/api/v1/invitations/${invitationCode}/accept`,
                { method: 'POST' },
            ),

        decline: (invitationCode: string) =>
            this.request<{ message: string }>(
                `/api/v1/invitations/${invitationCode}/decline`,
                { method: 'POST' },
            ),
    };

    readonly notifications = {
        index: () =>
            this.request<NotificationInboxResponse>('/api/v1/notifications'),

        markRead: (notificationId: string) =>
            this.request<NotificationUnreadCountResponse>(
                `/api/v1/notifications/${notificationId}/read`,
                { method: 'PATCH' },
            ),

        markAllRead: () =>
            this.request<NotificationUnreadCountResponse>(
                '/api/v1/notifications/read-all',
                { method: 'POST' },
            ),
    };

    readonly settings = {
        profile: {
            show: () =>
                this.request<SettingsProfileResponse>('/api/v1/settings/profile'),

            update: (payload: SettingsProfileUpdatePayload) =>
                this.request<SettingsProfileUpdateResponse>(
                    '/api/v1/settings/profile',
                    {
                        method: 'PATCH',
                        body: this.jsonBody(payload),
                    },
                ),

            destroy: (payload: SettingsProfileDeletePayload) =>
                this.request<{ message: string }>('/api/v1/settings/profile', {
                    method: 'DELETE',
                    body: this.jsonBody(payload),
                }),
        },

        password: {
            update: (payload: SettingsPasswordUpdatePayload) =>
                this.request<{ message: string }>('/api/v1/settings/password', {
                    method: 'PUT',
                    body: this.jsonBody(payload),
                }),
        },

        notifications: {
            show: () =>
                this.request<NotificationPreferencesResponse>(
                    '/api/v1/settings/notifications',
                ),

            update: (payload: NotificationPreferences & { paydayDayOfMonth?: number | null }) =>
                this.request<NotificationPreferencesUpdateResponse>(
                    '/api/v1/settings/notifications',
                    {
                        method: 'PATCH',
                        body: this.jsonBody(payload),
                    },
                ),
        },

        feedback: {
            create: () =>
                this.request<BetaFeedbackCreateResponse>('/api/v1/settings/feedback'),

            store: (payload: BetaFeedbackStorePayload) =>
                this.request<BetaFeedbackStoreResponse>('/api/v1/settings/feedback', {
                    method: 'POST',
                    body: this.jsonBody(payload),
                }),
        },

        billing: {
            show: () =>
                this.request<BillingPageResponse>('/api/v1/settings/billing'),

            submitPayment: (payload: {
                plan_price_id: string;
                reference_number: string;
                proof_image?: Uploadable | null;
            }) => {
                const form = new FormData();
                form.append('plan_price_id', payload.plan_price_id);
                form.append('reference_number', payload.reference_number);
                if (payload.proof_image) {
                    form.append('proof_image', payload.proof_image);
                }

                return this.request<PaymentSubmissionResponse>(
                    '/api/v1/settings/billing/payments',
                    {
                        method: 'POST',
                        body: form,
                    },
                );
            },
        },
    };

    readonly teams = {
        index: () => this.request<TeamsIndexResponse>('/api/v1/teams'),

        store: (name: string) =>
            this.request<TeamMutationResponse>('/api/v1/teams', {
                method: 'POST',
                body: this.jsonBody({ name }),
            }),

        show: (teamId: number) =>
            this.request<TeamShowResponse>(this.teamPath(teamId, '')),

        update: (teamId: number, name: string) =>
            this.request<TeamMutationResponse>(this.teamPath(teamId, ''), {
                method: 'PATCH',
                body: this.jsonBody({ name }),
            }),

        destroy: (teamId: number) =>
            this.request<{ message: string }>(this.teamPath(teamId, ''), {
                method: 'DELETE',
            }),

        leave: (teamId: number) =>
            this.request<{ message: string }>(this.teamPath(teamId, '/leave'), {
                method: 'DELETE',
            }),

        switch: (teamId: number) =>
            this.request<TeamMutationResponse>(this.teamPath(teamId, '/switch'), {
                method: 'POST',
            }),

        switchCurrent: (teamId: number) =>
            this.request<ApiSharedProps>('/api/v1/teams/switch', {
                method: 'POST',
                body: this.jsonBody({ team_id: teamId }),
            }),

        members: {
            update: (teamId: number, userId: number, role: TeamRole) =>
                this.request<{ message: string }>(
                    this.teamPath(teamId, `/members/${userId}`),
                    {
                        method: 'PATCH',
                        body: this.jsonBody({ role }),
                    },
                ),

            remove: (teamId: number, userId: number) =>
                this.request<{ message: string }>(
                    this.teamPath(teamId, `/members/${userId}`),
                    { method: 'DELETE' },
                ),
        },

        invitations: {
            store: (teamId: number, payload: { email: string; role: TeamRole }) =>
                this.request<{ message: string; invitation: TeamInvitationCreated }>(
                    this.teamPath(teamId, '/invitations'),
                    {
                        method: 'POST',
                        body: this.jsonBody(payload),
                    },
                ),

            destroy: (teamId: number, invitationCode: string) =>
                this.request<{ message: string }>(
                    this.teamPath(teamId, `/invitations/${invitationCode}`),
                    { method: 'DELETE' },
                ),
        },
    };

    readonly dashboard = {
        show: (teamId: number) =>
            this.request<{ data: DashboardPageProps & { pendingInvitations: DashboardInvitation[] } }>(
                this.teamPath(teamId, '/dashboard'),
            ).then((response) => response.data),
    };

    readonly billing = {
        show: (teamId: number) =>
            this.request<BillingPageResponse>(this.teamPath(teamId, '/billing')),
    };

    readonly savings = {
        plan: {
            show: (teamId: number) =>
                this.request<{ data: SavingsPlanDetail | null }>(
                    this.teamPath(teamId, '/savings/plan'),
                ),

            createFromTemplate: (teamId: number, templateId: string) =>
                this.request<{ data: SavingsPlanDetail }>(
                    this.teamPath(teamId, `/savings/plan/from-template/${templateId}`),
                    { method: 'POST' },
                ),

            createCustom: (teamId: number) =>
                this.request<{ data: SavingsPlanDetail }>(
                    this.teamPath(teamId, '/savings/plan/custom'),
                    { method: 'POST' },
                ),

            update: (
                teamId: number,
                payload: {
                    categories: SavingsPlanCategoryInput[];
                    is_shared_with_team?: boolean;
                    allow_editing_spends?: boolean;
                },
            ) =>
                this.request<{ data: SavingsPlanDetail }>(
                    this.teamPath(teamId, '/savings/plan'),
                    {
                        method: 'PUT',
                        body: this.jsonBody(payload),
                    },
                ),

            addOpeningBalance: (
                teamId: number,
                categoryId: string,
                amount: number | string,
            ) =>
                this.request<{ data: SavingsCategoryOpeningBalance }>(
                    this.teamPath(
                        teamId,
                        `/savings/plan/categories/${categoryId}/opening-balance`,
                    ),
                    {
                        method: 'PATCH',
                        body: this.jsonBody({ amount }),
                    },
                ),

            destroy: (teamId: number) =>
                this.request<void>(this.teamPath(teamId, '/savings/plan'), {
                    method: 'DELETE',
                }),
        },

        banks: {
            index: (teamId: number) =>
                this.request<{ data: SavingsBankOption[] }>(
                    this.teamPath(teamId, '/savings/banks'),
                ),

            store: (teamId: number, payload: Record<string, unknown>) =>
                this.request<{ data: SavingsBankOption | SavingsBankOption[] }>(
                    this.teamPath(teamId, '/savings/banks'),
                    {
                        method: 'POST',
                        body: this.jsonBody(payload),
                    },
                ),

            update: (teamId: number, bankId: string, payload: Record<string, unknown>) =>
                this.request<{ data: SavingsBankOption }>(
                    this.teamPath(teamId, `/savings/banks/${bankId}`),
                    {
                        method: 'PUT',
                        body: this.jsonBody(payload),
                    },
                ),

            destroy: (teamId: number, bankId: string) =>
                this.request<void>(this.teamPath(teamId, `/savings/banks/${bankId}`), {
                    method: 'DELETE',
                }),
        },

        recipients: {
            index: (teamId: number) =>
                this.request<{
                    data: SavingsRecipient[];
                    meta: { recipientTypes: RecipientTypeOption[] };
                }>(this.teamPath(teamId, '/savings/recipients')),

            store: (teamId: number, payload: Record<string, unknown>) =>
                this.request<{ data: SavingsRecipient }>(
                    this.teamPath(teamId, '/savings/recipients'),
                    {
                        method: 'POST',
                        body: this.jsonBody(payload),
                    },
                ),

            update: (
                teamId: number,
                recipientId: string,
                payload: Record<string, unknown>,
            ) =>
                this.request<{ data: SavingsRecipient }>(
                    this.teamPath(teamId, `/savings/recipients/${recipientId}`),
                    {
                        method: 'PUT',
                        body: this.jsonBody(payload),
                    },
                ),

            destroy: (teamId: number, recipientId: string) =>
                this.request<void>(
                    this.teamPath(teamId, `/savings/recipients/${recipientId}`),
                    { method: 'DELETE' },
                ),
        },

        income: {
            index: (teamId: number) =>
                this.request<{ data: IncomePeriod[] }>(
                    this.teamPath(teamId, '/savings/income'),
                ),

            show: (teamId: number, incomePeriodId: string) =>
                this.request<IncomePeriodShowResponse>(
                    this.teamPath(teamId, `/savings/income/${incomePeriodId}`),
                ),

            store: (
                teamId: number,
                payload: { name: string; amount: number | string; period_start: string },
            ) =>
                this.request<{ data: IncomePeriod }>(
                    this.teamPath(teamId, '/savings/income'),
                    {
                        method: 'POST',
                        body: this.jsonBody(payload),
                    },
                ),

            updateCustomAmounts: (
                teamId: number,
                incomePeriodId: string,
                customAmounts: Record<string, number | string | null>,
            ) =>
                this.request<{ data: IncomePeriod }>(
                    this.teamPath(
                        teamId,
                        `/savings/income/${incomePeriodId}/custom-amounts`,
                    ),
                    {
                        method: 'PUT',
                        body: this.jsonBody({ custom_amounts: customAmounts }),
                    },
                ),

            completeDistributionTodo: (
                teamId: number,
                incomePeriodId: string,
                todoId: string,
            ) =>
                this.request<{ data: IncomeDistributionTodoCompleteResponse }>(
                    this.teamPath(
                        teamId,
                        `/savings/income/${incomePeriodId}/todos/${todoId}/complete`,
                    ),
                    { method: 'POST' },
                ),

            destroy: (teamId: number, incomePeriodId: string) =>
                this.request<void>(
                    this.teamPath(teamId, `/savings/income/${incomePeriodId}`),
                    { method: 'DELETE' },
                ),
        },

        spending: {
            index: (teamId: number) =>
                this.request<{
                    data: FundSpend[];
                    meta: { plan: { id: string; name: string } };
                }>(this.teamPath(teamId, '/savings/spending')),

            store: (
                teamId: number,
                payload: {
                    category_id: string;
                    amount: number | string;
                    description: string;
                    spent_on: string;
                    bank_id?: string | null;
                    recipient_id?: string | null;
                    expects_reimbursement?: boolean;
                    expected_from_recipient_id?: string | null;
                    receipt_image?: Uploadable | null;
                },
            ) => {
                const form = new FormData();
                form.append('category_id', payload.category_id);
                form.append('amount', String(payload.amount));
                form.append('description', payload.description);
                form.append('spent_on', payload.spent_on);
                if (payload.bank_id) {
                    form.append('bank_id', payload.bank_id);
                }
                if (payload.recipient_id) {
                    form.append('recipient_id', payload.recipient_id);
                }
                if (payload.expects_reimbursement) {
                    form.append('expects_reimbursement', '1');
                }
                if (payload.expected_from_recipient_id) {
                    form.append(
                        'expected_from_recipient_id',
                        payload.expected_from_recipient_id,
                    );
                }
                if (payload.receipt_image) {
                    form.append('receipt_image', payload.receipt_image);
                }

                return this.request<{ data: FundSpend }>(
                    this.teamPath(teamId, '/savings/spending'),
                    { method: 'POST', body: form },
                );
            },

            update: (
                teamId: number,
                fundSpendId: string,
                payload: {
                    category_id: string;
                    amount: number | string;
                    description: string;
                    spent_on: string;
                    recipient_id?: string | null;
                    receipt_image?: Uploadable | null;
                    remove_receipt?: boolean;
                },
            ) => {
                const form = new FormData();
                form.append('category_id', payload.category_id);
                form.append('amount', String(payload.amount));
                form.append('description', payload.description);
                form.append('spent_on', payload.spent_on);
                if (payload.recipient_id) {
                    form.append('recipient_id', payload.recipient_id);
                }
                if (payload.receipt_image) {
                    form.append('receipt_image', payload.receipt_image);
                }
                if (payload.remove_receipt) {
                    form.append('remove_receipt', '1');
                }

                return this.request<{ data: FundSpend }>(
                    this.teamPath(teamId, `/savings/spending/${fundSpendId}`),
                    { method: 'PUT', body: form },
                );
            },

            destroy: (teamId: number, fundSpendId: string) =>
                this.request<void>(
                    this.teamPath(teamId, `/savings/spending/${fundSpendId}`),
                    { method: 'DELETE' },
                ),

            confirm: (teamId: number, fundSpendId: string) =>
                this.request<{ data: FundSpend }>(
                    this.teamPath(teamId, `/savings/spending/${fundSpendId}/confirm`),
                    { method: 'POST' },
                ),

            storeReimbursement: (
                teamId: number,
                fundSpendId: string,
                payload: {
                    amount: number | string;
                    received_on: string;
                    bank_id?: string | null;
                    notes?: string | null;
                },
            ) =>
                this.request<{ data: FundSpend }>(
                    this.teamPath(
                        teamId,
                        `/savings/spending/${fundSpendId}/reimbursements`,
                    ),
                    {
                        method: 'POST',
                        body: JSON.stringify(payload),
                    },
                ),

            closeReimbursement: (teamId: number, fundSpendId: string) =>
                this.request<{ data: FundSpend }>(
                    this.teamPath(
                        teamId,
                        `/savings/spending/${fundSpendId}/close-reimbursement`,
                    ),
                    { method: 'POST' },
                ),
        },

        transfers: {
            index: (teamId: number) =>
                this.request<{ data: FundTransfer[] }>(
                    this.teamPath(teamId, '/savings/transfers'),
                ),

            store: (
                teamId: number,
                payload: {
                    from_category_id: string;
                    to_category_id: string;
                    amount: number | string;
                    description?: string | null;
                    transferred_on: string;
                },
            ) =>
                this.request<{ data: FundTransfer }>(
                    this.teamPath(teamId, '/savings/transfers'),
                    {
                        method: 'POST',
                        body: this.jsonBody(payload),
                    },
                ),

            confirm: (teamId: number, fundTransferId: string) =>
                this.request<{ data: FundTransfer }>(
                    this.teamPath(
                        teamId,
                        `/savings/transfers/${fundTransferId}/confirm`,
                    ),
                    { method: 'POST' },
                ),
        },

        reports: {
            index: (teamId: number) =>
                this.request<{ data: ReportTotals }>(
                    this.teamPath(teamId, '/savings/reports'),
                ),
        },
    };
}

export function createApiClient(config: ApiClientConfig): KinsenasApiClient {
    return new KinsenasApiClient(config);
}
