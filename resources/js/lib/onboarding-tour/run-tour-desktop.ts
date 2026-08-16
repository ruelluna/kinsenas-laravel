import type { DriveStep, Driver } from 'driver.js';
import { driver } from 'driver.js';
import {
    ONBOARDING_TOUR_DESKTOP_STEPS
    
} from '@/lib/onboarding-tour/steps-desktop';
import type {OnboardingTourStep} from '@/lib/onboarding-tour/steps-desktop';
import {
    stepMatchesPath,
    tourElementSelector,
} from '@/lib/onboarding-tour/steps-shared';
import {
    clearOnboardingTourActive,
    markOnboardingTourCompleted,
    setOnboardingTourActive,
} from '@/lib/onboarding-tour/storage';
import {
    destroyActiveDriver,
    isSuppressCompleteOnDestroy,
    setActiveDriver,
} from '@/lib/onboarding-tour/tour-driver-registry';
import { finishTour, navigateForStep } from '@/lib/onboarding-tour/tour-lifecycle';

type CreateDesktopDriverOptions = {
    teamId: string | number;
    teamSlug: string;
    forced: boolean;
    steps: OnboardingTourStep[];
    onFinished?: () => void;
};

export function buildDesktopDriveSteps(
    steps: OnboardingTourStep[],
): DriveStep[] {
    return steps.map((step) => ({
        element: tourElementSelector(step.tourId),
        popover: {
            title: step.title,
            description: step.description,
            side: 'bottom',
            align: 'start',
        },
    }));
}

export function createDesktopDriver({
    teamId,
    teamSlug,
    forced,
    steps,
    onFinished,
}: CreateDesktopDriverOptions): Driver {
    return driver({
        steps: buildDesktopDriveSteps(steps),
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
            setActiveDriver(null);

            if (isSuppressCompleteOnDestroy()) {
                return;
            }

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

            if (
                !stepMatchesPath(nextStep, window.location.pathname) &&
                nextStep.navigateTo
            ) {
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

            if (
                !stepMatchesPath(prevStep, window.location.pathname) &&
                prevStep.navigateTo
            ) {
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
}

export function runDesktopOnboardingTour({
    teamId,
    teamSlug,
    startIndex = 0,
    forced = false,
    onFinished,
}: {
    teamId: string | number;
    teamSlug: string;
    startIndex?: number;
    forced?: boolean;
    onFinished?: () => void;
}): void {
    if (typeof document === 'undefined') {
        return;
    }

    destroyActiveDriver({ suppressComplete: true });

    const steps = ONBOARDING_TOUR_DESKTOP_STEPS;
    const safeIndex = Math.min(
        Math.max(startIndex, 0),
        Math.max(steps.length - 1, 0),
    );
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

    const instance = createDesktopDriver({
        teamId,
        teamSlug,
        forced,
        steps,
        onFinished,
    });

    setActiveDriver(instance);
    instance.drive(safeIndex);
}
