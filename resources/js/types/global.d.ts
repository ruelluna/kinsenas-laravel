import type { Auth } from '@/types/auth';
import type { SharedSubscription } from '@/types/billing';
import type { Team } from '@/types/teams';

declare module 'react' {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            currentTeam: Team | null;
            teams: Team[];
            canCreateTeam: boolean;
            subscription: SharedSubscription | null;
            vaultLocked: boolean;
            registrationRecoveryKey?: string | null;
            [key: string]: unknown;
        };
    }
}
