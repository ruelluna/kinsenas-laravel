export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    isPlatformAdmin?: boolean;
    beta_enrolled_at?: string | null;
    beta_application_status?: string | null;
    beta_approved_at?: string | null;
    beta_launch_discount_eligible?: boolean;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
};

export type Auth = {
    user: User;
};
