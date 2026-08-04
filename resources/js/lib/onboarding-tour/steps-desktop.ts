import {
    ONBOARDING_TOUR_STEP_DEFINITIONS,
    type OnboardingTourStepBase,
} from '@/lib/onboarding-tour/steps-shared';

export type OnboardingTourStep = OnboardingTourStepBase;

export const ONBOARDING_TOUR_DESKTOP_STEPS: OnboardingTourStep[] =
    ONBOARDING_TOUR_STEP_DEFINITIONS;
