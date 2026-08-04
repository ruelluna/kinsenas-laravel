import { closeMobileMoreSheet } from '@/lib/mobile-more-sheet-bridge';
import { isMobileViewport } from '@/lib/onboarding-tour/is-mobile-viewport';
import { runDesktopOnboardingTour } from '@/lib/onboarding-tour/run-tour-desktop';
import { runMobileOnboardingTour } from '@/lib/onboarding-tour/run-tour-mobile';
import { clearOnboardingTourActive } from '@/lib/onboarding-tour/storage';
import {
    destroyActiveDriver,
    isOnboardingTourRunning,
} from '@/lib/onboarding-tour/tour-driver-registry';

export type RunOnboardingTourOptions = {
    teamId: string | number;
    teamSlug: string;
    startIndex?: number;
    forced?: boolean;
    onFinished?: () => void;
};

export { isOnboardingTourRunning };

export function stopOnboardingTour(): void {
    destroyActiveDriver({ suppressComplete: true });
    clearOnboardingTourActive();

    if (isMobileViewport()) {
        closeMobileMoreSheet();
    }
}

export function runOnboardingTour(options: RunOnboardingTourOptions): void {
    if (isMobileViewport()) {
        runMobileOnboardingTour(options);
    } else {
        runDesktopOnboardingTour(options);
    }
}
