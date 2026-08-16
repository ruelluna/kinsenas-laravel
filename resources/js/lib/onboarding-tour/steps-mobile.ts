import {
    ONBOARDING_TOUR_STEP_DEFINITIONS
    
} from '@/lib/onboarding-tour/steps-shared';
import type {OnboardingTourStepBase} from '@/lib/onboarding-tour/steps-shared';

export type OnboardingTourMobileStep = OnboardingTourStepBase & {
    openMoreSheet?: boolean;
    popoverSide?: 'top' | 'bottom';
    fallbackNavigateTo?: string;
};

const MOBILE_STEP_OVERRIDES: Record<
    string,
    Pick<
        OnboardingTourMobileStep,
        'openMoreSheet' | 'popoverSide' | 'fallbackNavigateTo'
    >
> = {
    'nav-banks': {
        openMoreSheet: true,
        popoverSide: 'bottom',
        fallbackNavigateTo: 'savings/banks',
    },
    'nav-plan': {
        openMoreSheet: true,
        popoverSide: 'bottom',
        fallbackNavigateTo: 'savings/plan',
    },
    'nav-income': {
        popoverSide: 'top',
    },
};

export const ONBOARDING_TOUR_MOBILE_STEPS: OnboardingTourMobileStep[] =
    ONBOARDING_TOUR_STEP_DEFINITIONS.map((step) => ({
        ...step,
        popoverSide: 'bottom' as const,
        ...MOBILE_STEP_OVERRIDES[step.id],
    }));
