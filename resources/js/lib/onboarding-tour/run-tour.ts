import { router } from '@inertiajs/react';
import { driver, type DriveStep, type Driver } from 'driver.js';
import {
    clearOnboardingTourActive,
    markOnboardingTourCompleted,
    setOnboardingTourActive,
} from '@/lib/onboarding-tour/storage';
import {
    ONBOARDING_TOUR_STEPS,
    stepMatchesPath,
    teamPath,
    tourElementSelector,
    type OnboardingTourStep,
} from '@/lib/onboarding-tour/steps';

let activeDriver: Driver | null = null;
let suppressCompleteOnDestroy = false;

export type RunOnboardingTourOptions = {
    teamId: string | number;
    teamSlug: string;
    startIndex?: number;
    forced?: boolean;
    onFinished?: () => void;
};

function destroyActiveDriver(options?: { suppressComplete?: boolean }): void {
    if (!activeDriver) {
        return;
    }

    suppressCompleteOnDestroy = options?.suppressComplete ?? false;
    activeDriver.destroy();
    activeDriver = null;
    suppressCompleteOnDestroy = false;
}

function finishTour(teamId: string | number, onFinished?: () => void): void {
    markOnboardingTourCompleted(teamId);
    clearOnboardingTourActive();
    destroyActiveDriver({ suppressComplete: true });
    onFinished?.();
}

function goToStepPage(teamSlug: string, step: OnboardingTourStep): void {
    if (!step.navigateTo) {
        return;
    }

    router.visit(teamPath(teamSlug, step.navigateTo), {
        preserveState: false,
        preserveScroll: false,
    });
}

function navigateForStep(
    teamId: string | number,
    teamSlug: string,
    stepIndex: number,
    step: OnboardingTourStep,
    forced: boolean,
): void {
    setOnboardingTourActive({ teamId, resumeIndex: stepIndex, forced });
    destroyActiveDriver({ suppressComplete: true });
    goToStepPage(teamSlug, step);
}

export function isOnboardingTourRunning(): boolean {
    return activeDriver?.isActive() ?? false;
}

export function stopOnboardingTour(): void {
    destroyActiveDriver({ suppressComplete: true });
    clearOnboardingTourActive();
}

export function runOnboardingTour({
    teamId,
    teamSlug,
    startIndex = 0,
    forced = false,
    onFinished,
}: RunOnboardingTourOptions): void {
    if (typeof document === 'undefined') {
        return;
    }

    destroyActiveDriver({ suppressComplete: true });

    const steps = ONBOARDING_TOUR_STEPS;
    const safeIndex = Math.min(Math.max(startIndex, 0), Math.max(steps.length - 1, 0));
    const current = steps[safeIndex];

    if (!current) {
        finishTour(teamId, onFinished);

        return;
    }

    const pathname = window.location.pathname;

    if (!stepMatchesPath(current, pathname) && current.navigateTo) {
        navigateForStep(teamId, teamSlug, safeIndex, current, forced);

        return;
    }

    setOnboardingTourActive({ teamId, resumeIndex: safeIndex, forced });

    const driveSteps: DriveStep[] = steps.map((step) => ({
        element: tourElementSelector(step.tourId),
        popover: {
            title: step.title,
            description: step.description,
            side: 'bottom',
            align: 'start',
        },
    }));

    activeDriver = driver({
        steps: driveSteps,
        animate: true,
        smoothScroll: true,
        allowClose: true,
        overlayOpacity: 0.55,
        stagePadding: 8,
        stageRadius: 8,
        popoverClass: 'kinsenas-driver-popover',
        showProgress: true,
        progressText: '{{current}} of {{total}}',
        nextBtnText: 'Next',
        prevBtnText: 'Back',
        doneBtnText: 'Done',
        skipMissingElement: true,
        waitForElement: 1500,
        onDestroyed: () => {
            activeDriver = null;

            if (suppressCompleteOnDestroy) {
                return;
            }

            // Overlay click / Escape without going through our button handlers.
            markOnboardingTourCompleted(teamId);
            clearOnboardingTourActive();
            onFinished?.();
        },
        onNextClick: (_element, _step, { driver: instance, state }) => {
            const index = state.activeIndex ?? 0;
            const nextIndex = index + 1;
            const nextStep = steps[nextIndex];

            if (!nextStep) {
                finishTour(teamId, onFinished);

                return;
            }

            if (!stepMatchesPath(nextStep, window.location.pathname) && nextStep.navigateTo) {
                navigateForStep(teamId, teamSlug, nextIndex, nextStep, forced);

                return;
            }

            setOnboardingTourActive({ teamId, resumeIndex: nextIndex, forced });
            instance.moveNext();
        },
        onPrevClick: (_element, _step, { driver: instance, state }) => {
            const index = state.activeIndex ?? 0;
            const prevIndex = index - 1;
            const prevStep = steps[prevIndex];

            if (!prevStep) {
                return;
            }

            if (!stepMatchesPath(prevStep, window.location.pathname) && prevStep.navigateTo) {
                navigateForStep(teamId, teamSlug, prevIndex, prevStep, forced);

                return;
            }

            setOnboardingTourActive({ teamId, resumeIndex: prevIndex, forced });
            instance.movePrevious();
        },
        onCloseClick: () => {
            finishTour(teamId, onFinished);
        },
    });

    activeDriver.drive(safeIndex);
}
