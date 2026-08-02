const COMPLETED_PREFIX = 'kinsenas.onboardingTour.v1.';
const ACTIVE_KEY = 'kinsenas.onboardingTour.active.v1';
const AUTO_START_KEY = 'kinsenas.onboardingTour.autoStart.v1';

export type OnboardingTourTeamId = string | number;

export type OnboardingTourCompletion = {
    completedAt: string;
};

export type OnboardingTourActiveState = {
    teamId: string;
    resumeIndex: number;
    forced: boolean;
};

function normalizeTeamId(teamId: OnboardingTourTeamId): string {
    return String(teamId);
}

function completedKey(teamId: OnboardingTourTeamId): string {
    return `${COMPLETED_PREFIX}${normalizeTeamId(teamId)}`;
}

export function isOnboardingTourCompleted(
    teamId: OnboardingTourTeamId,
): boolean {
    try {
        return window.localStorage.getItem(completedKey(teamId)) !== null;
    } catch {
        return false;
    }
}

export function markOnboardingTourCompleted(
    teamId: OnboardingTourTeamId,
): void {
    const payload: OnboardingTourCompletion = {
        completedAt: new Date().toISOString(),
    };

    try {
        window.localStorage.setItem(
            completedKey(teamId),
            JSON.stringify(payload),
        );
    } catch {
        // Ignore quota / private mode failures.
    }

    clearOnboardingTourActive();
}

export function clearOnboardingTourCompleted(
    teamId: OnboardingTourTeamId,
): void {
    try {
        window.localStorage.removeItem(completedKey(teamId));
    } catch {
        // Ignore.
    }
}

export function getOnboardingTourActive(): OnboardingTourActiveState | null {
    try {
        const raw = window.sessionStorage.getItem(ACTIVE_KEY);

        if (!raw) {
            return null;
        }

        const parsed = JSON.parse(raw) as OnboardingTourActiveState;

        if (!parsed.teamId || typeof parsed.resumeIndex !== 'number') {
            return null;
        }

        return parsed;
    } catch {
        return null;
    }
}

export function setOnboardingTourActive(state: {
    teamId: OnboardingTourTeamId;
    resumeIndex: number;
    forced: boolean;
}): void {
    try {
        const payload: OnboardingTourActiveState = {
            teamId: normalizeTeamId(state.teamId),
            resumeIndex: state.resumeIndex,
            forced: state.forced,
        };
        window.sessionStorage.setItem(ACTIVE_KEY, JSON.stringify(payload));
    } catch {
        // Ignore.
    }
}

export function clearOnboardingTourActive(): void {
    try {
        window.sessionStorage.removeItem(ACTIVE_KEY);
    } catch {
        // Ignore.
    }
}

const autoStartedTeams = new Set<string>();

export function requestOnboardingTourAutoStart(
    teamId: OnboardingTourTeamId,
): void {
    const id = normalizeTeamId(teamId);

    if (isOnboardingTourCompleted(id) || autoStartedTeams.has(id)) {
        return;
    }

    try {
        window.sessionStorage.setItem(AUTO_START_KEY, id);
    } catch {
        // Ignore.
    }
}

export function consumeOnboardingTourAutoStart(
    teamId: OnboardingTourTeamId,
): boolean {
    const id = normalizeTeamId(teamId);

    try {
        const requested = window.sessionStorage.getItem(AUTO_START_KEY);

        if (requested !== id || autoStartedTeams.has(id)) {
            return false;
        }

        window.sessionStorage.removeItem(AUTO_START_KEY);

        if (isOnboardingTourCompleted(id)) {
            return false;
        }

        autoStartedTeams.add(id);

        return true;
    } catch {
        return false;
    }
}

export function resetOnboardingTourAutoStartGuard(
    teamId: OnboardingTourTeamId,
): void {
    autoStartedTeams.delete(normalizeTeamId(teamId));
}
