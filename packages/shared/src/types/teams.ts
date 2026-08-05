export type TeamRole = 'owner' | 'admin' | 'member';

export type Team = {
    id: number;
    name: string;
    slug: string;
    isPersonal: boolean;
    role?: TeamRole;
    roleLabel?: string;
    isCurrent?: boolean;
    subscriptionStatusLabel?: string | null;
    hasSubscriptionAccess?: boolean;
};

export type DashboardInvitation = {
    code: string;
    inviterName: string;
    team: {
        name: string;
        slug: string;
    };
};

export type TeamSummary = {
    id: number;
    name: string;
    slug: string;
    isPersonal: boolean;
};

export type TeamMember = {
    id: number;
    name: string;
    email: string;
    avatar?: string | null;
    role: TeamRole;
    roleLabel: string;
};

export type TeamInvitation = {
    code: string;
    email: string;
    role: TeamRole;
    roleLabel: string;
    createdAt: string;
};

export type TeamInvitationCreated = {
    code: string;
    email: string;
    role: TeamRole;
    expiresAt: string | null;
};

export type TeamPermissions = {
    canUpdateTeam: boolean;
    canDeleteTeam: boolean;
    canAddMember: boolean;
    canUpdateMember: boolean;
    canRemoveMember: boolean;
    canCreateInvitation: boolean;
    canCancelInvitation: boolean;
};

export type TeamRoleOption = {
    value: TeamRole;
    label: string;
};

export type TeamsIndexResponse = {
    teams: Team[];
};

export type TeamShowResponse = {
    team: TeamSummary;
    members: TeamMember[];
    invitations: TeamInvitation[];
    permissions: TeamPermissions;
    availableRoles: TeamRoleOption[];
};

export type TeamMutationResponse = {
    message: string;
    team: TeamSummary;
};

export type TeamInvitationAcceptResponse = {
    message: string;
    team: TeamSummary;
};
