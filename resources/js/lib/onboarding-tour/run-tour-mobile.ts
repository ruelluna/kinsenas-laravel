import type { DriveStep, Driver } from 'driver.js';
import { driver } from 'driver.js';
import {
    closeMobileMoreSheet,
    openMobileMoreSheet,
} from '@/lib/mobile-more-sheet-bridge';
import {
    ONBOARDING_TOUR_MOBILE_STEPS
    
} from '@/lib/onboarding-tour/steps-mobile';
import type {OnboardingTourMobileStep} from '@/lib/onboarding-tour/steps-mobile';
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

const MOBILE_WAIT_FOR_ELEMENT_MS = 2500;
const MORE_SHEET_OPEN_DELAY_MS = 300;
const ELEMENT_POLL_INTERVAL_MS = 50;

type CreateMobileDriverOptions = {
    teamId: string | number;
    teamSlug: string;
    forced: boolean;
    steps: OnboardingTourMobileStep[];
    onFinished?: () => void;
};

function delay(ms: number): Promise<void> {
    return new Promise((resolve) => {
        window.setTimeout(resolve, ms);
    });
}

export async function waitForTourElement(
    selector: string,
    timeoutMs: number,
): Promise<boolean> {
    const startedAt = Date.now();

    while (Date.now() - startedAt < timeoutMs) {
        const element = document.querySelector(selector);

        if (element) {
            return true;
        }

        await delay(ELEMENT_POLL_INTERVAL_MS);
    }

    return Boolean(document.querySelector(selector));
}

export async function prepareMobileStep(
    step: OnboardingTourMobileStep,
): Promise<'ready' | 'fallback'> {
    if (step.openMoreSheet) {
        openMobileMoreSheet();
        await delay(MORE_SHEET_OPEN_DELAY_MS);
    } else {
        closeMobileMoreSheet();
    }

    const selector = tourElementSelector(step.tourId);
    const found = await waitForTourElement(
        selector,
        MOBILE_WAIT_FOR_ELEMENT_MS,
    );

    if (!found && step.fallbackNavigateTo) {
        return 'fallback';
    }

    return 'ready';
}

function buildMobileDriveSteps(
    steps: OnboardingTourMobileStep[],
): DriveStep[] {
    return steps.map((step) => ({
        element: tourElementSelector(step.tourId),
        popover: {
            title: step.title,
            description: step.description,
            side: step.popoverSide ?? 'bottom',
            align: 'start',
        },
    }));
}

function createMobileDriver({
    teamId,
    teamSlug,
    forced,
    steps,
    onFinished,
}: CreateMobileDriverOptions): Driver {
    return driver({
        steps: buildMobileDriveSteps(steps),
        animate: true,
        smoothScroll: true,
        allowClose: true,
        overlayOpacity: 0.55,
        stagePadding: 8,
        stageRadius: 8,
        popoverClass: 'kinsenas-driver-popover kinsenas-driver-popover-mobile',
        showProgress: true,
        progressText: '{{current}} of {{total}}',
        nextBtnText: 'Next',
        prevBtnText: 'Back',
        doneBtnText: 'Done',
        skipMissingElement: true,
        waitForElement: MOBILE_WAIT_FOR_ELEMENT_MS,
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
            void handleMobileStepTransition({
                teamId,
                teamSlug,
                forced,
                steps,
                onFinished,
                instance,
                currentIndex: state.activeIndex ?? 0,
                direction: 'next',
            });
        },
        onPrevClick: (_element, _step, { driver: instance, state }) => {
            void handleMobileStepTransition({
                teamId,
                teamSlug,
                forced,
                steps,
                onFinished,
                instance,
                currentIndex: state.activeIndex ?? 0,
                direction: 'prev',
            });
        },
        onCloseClick: () => {
            finishTour(teamId, onFinished);
        },
    });
}

async function handleMobileStepTransition({
    teamId,
    teamSlug,
    forced,
    steps,
    onFinished,
    instance,
    currentIndex,
    direction,
}: {
    teamId: string | number;
    teamSlug: string;
    forced: boolean;
    steps: OnboardingTourMobileStep[];
    onFinished?: () => void;
    instance: Driver;
    currentIndex: number;
    direction: 'next' | 'prev';
}): Promise<void> {
    const targetIndex =
        direction === 'next' ? currentIndex + 1 : currentIndex - 1;
    const targetStep = steps[targetIndex];

    if (!targetStep) {
        if (direction === 'next') {
            finishTour(teamId, onFinished);
        }

        return;
    }

    if (
        !stepMatchesPath(targetStep, window.location.pathname) &&
        targetStep.navigateTo
    ) {
        closeMobileMoreSheet();
        navigateForStep(teamId, teamSlug, targetIndex, targetStep, forced);

        return;
    }

    const preparation = await prepareMobileStep(targetStep);

    if (preparation === 'fallback') {
        if (direction === 'next') {
            const fallbackStep = steps[targetIndex + 1];

            if (fallbackStep?.navigateTo) {
                closeMobileMoreSheet();
                navigateForStep(
                    teamId,
                    teamSlug,
                    targetIndex + 1,
                    fallbackStep,
                    forced,
                );
            }

            return;
        }

        setOnboardingTourActive({ teamId, resumeIndex: targetIndex, forced });
        instance.movePrevious();

        return;
    }

    setOnboardingTourActive({ teamId, resumeIndex: targetIndex, forced });

    if (direction === 'next') {
        instance.moveNext();
    } else {
        instance.movePrevious();
    }
}

async function startMobileTourAtIndex({
    teamId,
    teamSlug,
    forced,
    steps,
    safeIndex,
    onFinished,
}: {
    teamId: string | number;
    teamSlug: string;
    forced: boolean;
    steps: OnboardingTourMobileStep[];
    safeIndex: number;
    onFinished?: () => void;
}): Promise<void> {
    const current = steps[safeIndex];

    if (!current) {
        finishTour(teamId, onFinished);

        return;
    }

    const preparation = await prepareMobileStep(current);

    if (preparation === 'fallback') {
        const fallbackStep = steps[safeIndex + 1];

        if (fallbackStep?.navigateTo) {
            closeMobileMoreSheet();
            navigateForStep(
                teamId,
                teamSlug,
                safeIndex + 1,
                fallbackStep,
                forced,
            );
        }

        return;
    }

    setOnboardingTourActive({ teamId, resumeIndex: safeIndex, forced });

    const instance = createMobileDriver({
        teamId,
        teamSlug,
        forced,
        steps,
        onFinished,
    });

    setActiveDriver(instance);
    instance.drive(safeIndex);
}

export function runMobileOnboardingTour({
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

    const steps = ONBOARDING_TOUR_MOBILE_STEPS;
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
        closeMobileMoreSheet();
        navigateForStep(teamId, teamSlug, safeIndex, current, forced);

        return;
    }

    void startMobileTourAtIndex({
        teamId,
        teamSlug,
        forced,
        steps,
        safeIndex,
        onFinished,
    });
}
