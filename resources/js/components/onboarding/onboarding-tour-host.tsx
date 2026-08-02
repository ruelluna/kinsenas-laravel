import { usePage } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import {
    isOnboardingTourRunning,
    runOnboardingTour,
} from '@/lib/onboarding-tour/run-tour';
import {
    consumeOnboardingTourAutoStart,
    getOnboardingTourActive,
    isOnboardingTourCompleted,
} from '@/lib/onboarding-tour/storage';
import type { SharedData } from '@/types';

/**
 * Resumes or auto-starts the banks-first Driver.js tour across Inertia visits.
 */
export default function OnboardingTourHost() {
    const page = usePage<SharedData>();
    const { currentTeam, subscription } = page.props;
    const teamId = currentTeam?.id;
    const teamSlug = currentTeam?.slug;
    const hasAccess = subscription?.hasAccess ?? true;
    const bootstrapping = useRef(false);

    useEffect(() => {
        if (!teamId || !teamSlug || !hasAccess) {
            return;
        }

        if (isOnboardingTourRunning() || bootstrapping.current) {
            return;
        }

        const active = getOnboardingTourActive();

        if (active && active.teamId === String(teamId)) {
            bootstrapping.current = true;

            const timer = window.setTimeout(() => {
                runOnboardingTour({
                    teamId,
                    teamSlug,
                    startIndex: active.resumeIndex,
                    forced: active.forced,
                });
                bootstrapping.current = false;
            }, 350);

            return () => {
                window.clearTimeout(timer);
                bootstrapping.current = false;
            };
        }

        if (
            consumeOnboardingTourAutoStart(teamId) &&
            !isOnboardingTourCompleted(teamId) &&
            page.url.includes('/dashboard')
        ) {
            bootstrapping.current = true;

            const timer = window.setTimeout(() => {
                runOnboardingTour({
                    teamId,
                    teamSlug,
                    startIndex: 0,
                    forced: false,
                });
                bootstrapping.current = false;
            }, 500);

            return () => {
                window.clearTimeout(timer);
                bootstrapping.current = false;
            };
        }

        return undefined;
    }, [teamId, teamSlug, hasAccess, page.url]);

    return null;
}

export function startOnboardingTour(options: {
    teamId: string | number;
    teamSlug: string;
    forced?: boolean;
}): void {
    runOnboardingTour({
        teamId: options.teamId,
        teamSlug: options.teamSlug,
        startIndex: 0,
        forced: options.forced ?? true,
    });
}
