import { router } from '@inertiajs/react';
import type { OnboardingTourStepBase } from '@/lib/onboarding-tour/steps-shared';
import { stepMatchesPath, teamPath } from '@/lib/onboarding-tour/steps-shared';
import {
    clearOnboardingTourActive,
    markOnboardingTourCompleted,
    setOnboardingTourActive,
} from '@/lib/onboarding-tour/storage';
import { destroyActiveDriver } from '@/lib/onboarding-tour/tour-driver-registry';

export function finishTour(
    teamId: string | number,
    onFinished?: () => void,
): void {
    markOnboardingTourCompleted(teamId);
    clearOnboardingTourActive();
    destroyActiveDriver({ suppressComplete: true });
    onFinished?.();
}

export function goToStepPage(
    teamSlug: string,
    step: Pick<OnboardingTourStepBase, 'navigateTo'>,
): void {
    if (!step.navigateTo) {
        return;
    }

    router.visit(teamPath(teamSlug, step.navigateTo), {
        preserveState: false,
        preserveScroll: false,
    });
}

export function navigateForStep(
    teamId: string | number,
    teamSlug: string,
    stepIndex: number,
    step: OnboardingTourStepBase,
    forced: boolean,
): void {
    setOnboardingTourActive({ teamId, resumeIndex: stepIndex, forced });
    destroyActiveDriver({ suppressComplete: true });
    goToStepPage(teamSlug, step);
}

export function shouldNavigateForStep(
    step: OnboardingTourStepBase,
    pathname: string,
): boolean {
    return !stepMatchesPath(step, pathname) && Boolean(step.navigateTo);
}
