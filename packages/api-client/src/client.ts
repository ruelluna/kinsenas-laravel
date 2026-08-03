import type {
    ApiSharedProps,
    DashboardPageProps,
    PaginatedResponse,
    FundSpend,
    IncomePeriod,
    FundTransfer,
    SubscriptionSummary,
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

export class KinsenasApiClient {
    constructor(private config: ApiClientConfig) {}

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

    login(email: string, password: string) {
        return this.request<{ token: string; user: User }>('/api/v1/auth/login', {
            method: 'POST',
            body: JSON.stringify({ email, password }),
        });
    }

    logout() {
        return this.request<void>('/api/v1/auth/logout', { method: 'POST' });
    }

    me() {
        return this.request<ApiSharedProps>('/api/v1/auth/me');
    }

    unlockVault(payload: { password?: string; recovery_key?: string }) {
        return this.request<{ message: string }>('/api/v1/vault/unlock', {
            method: 'POST',
            body: JSON.stringify(payload),
        });
    }

    switchTeam(teamId: number) {
        return this.request<ApiSharedProps>('/api/v1/teams/switch', {
            method: 'POST',
            body: JSON.stringify({ team_id: teamId }),
        });
    }

    dashboard(teamId: number) {
        return this.request<DashboardPageProps & { pendingInvitations: unknown[] }>(
            `/api/v1/teams/${teamId}/dashboard`,
        );
    }

    incomeIndex(teamId: number) {
        return this.request<{ data: IncomePeriod[] }>(
            `/api/v1/teams/${teamId}/savings/income`,
        ).then((response) => ({ periods: response.data }));
    }

    spendingIndex(teamId: number) {
        return this.request<{ data: FundSpend[] }>(
            `/api/v1/teams/${teamId}/savings/spending`,
        ).then((response) => ({ spends: response.data }));
    }

    transfersIndex(teamId: number) {
        return this.request<{ data: FundTransfer[] }>(
            `/api/v1/teams/${teamId}/savings/transfers`,
        ).then((response) => ({ transfers: response.data }));
    }

    billing(teamId: number) {
        return this.request<{ subscription: SubscriptionSummary; plans: unknown[] }>(
            `/api/v1/teams/${teamId}/billing`,
        );
    }

    adminSubscribers(page = 1) {
        return this.request<PaginatedResponse<Record<string, unknown>>>(
            `/api/v1/admin/subscribers?page=${page}`,
        );
    }

    adminPlans() {
        return this.request<{ plans: Record<string, unknown>[] }>(
            '/api/v1/admin/plans',
        );
    }

    adminBetaApplications(page = 1) {
        return this.request<PaginatedResponse<Record<string, unknown>>>(
            `/api/v1/admin/beta-applications?page=${page}`,
        );
    }

    adminPlatformUsers(page = 1) {
        return this.request<PaginatedResponse<Record<string, unknown>>>(
            `/api/v1/admin/platform-users?page=${page}`,
        );
    }
}

export function createApiClient(config: ApiClientConfig): KinsenasApiClient {
    return new KinsenasApiClient(config);
}
