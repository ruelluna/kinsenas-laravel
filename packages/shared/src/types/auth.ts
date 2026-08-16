export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    isPlatformAdmin?: boolean;
    isAuthor?: boolean;
    canManagePlatform?: boolean;
    canManageContent?: boolean;
    canManageAllContent?: boolean;
    platformRole?: string;
    beta_enrolled_at?: string | null;
    beta_launch_discount_eligible?: boolean;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
};

export type Auth = {
    user: User;
};

export type TeamInvitationContext = {
    code: string;
    teamName: string;
};

export type RegisterContext = {
    passwordRules: string;
    teamInvitation: TeamInvitationContext | null;
    trialOffer: unknown | null;
    openBetaOffer: unknown | null;
};
