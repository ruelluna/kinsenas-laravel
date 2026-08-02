export type OnboardingTourStep = {
    id: string;
    /** Matches `[data-tour="…"]` */
    tourId: string;
    /**
     * Path fragment that must appear in the URL (e.g. `/savings/banks`).
     * `null` = any authenticated app page with the sidebar.
     */
    pathIncludes: string | null;
    /** Team-relative path to visit before this step (e.g. `savings/banks`). */
    navigateTo?: string;
    title: string;
    description: string;
};

export const ONBOARDING_TOUR_STEPS: OnboardingTourStep[] = [
    {
        id: 'setup',
        tourId: 'setup-checklist',
        pathIncludes: '/dashboard',
        navigateTo: 'dashboard',
        title: 'Welcome to Kinsenas',
        description:
            'Follow this short checklist. Start with your banks, then pick a savings plan and track income.',
    },
    {
        id: 'nav-banks',
        tourId: 'nav-banks',
        pathIncludes: null,
        title: 'Banks come first',
        description:
            'Open Banks and add every account you use. You’ll assign fund buckets to these accounts next.',
    },
    {
        id: 'banks-intro',
        tourId: 'banks-intro',
        pathIncludes: '/savings/banks',
        navigateTo: 'savings/banks',
        title: 'References only',
        description:
            'Kinsenas does not move money. These banks are your map — you still transfer in your real banking apps so balances stay in sync.',
    },
    {
        id: 'add-bank',
        tourId: 'add-bank',
        pathIncludes: '/savings/banks',
        title: 'Add your accounts',
        description:
            'Add all banks and GoSave spaces you use. You can come back anytime to add more.',
    },
    {
        id: 'nav-plan',
        tourId: 'nav-plan',
        pathIncludes: null,
        title: 'Then choose a plan',
        description:
            'After your banks are listed, pick a savings formula and assign each fund bucket to an account.',
    },
    {
        id: 'plan-main',
        tourId: 'plan-main',
        pathIncludes: '/savings/plan',
        navigateTo: 'savings/plan',
        title: 'Your savings formula',
        description:
            'Compare formulas, then assign each fund bucket to a bank so you know where money should live.',
    },
    {
        id: 'nav-income',
        tourId: 'nav-income',
        pathIncludes: null,
        title: 'Add income next',
        description:
            'Enter payday income so allocations appear. Use Transfers when you move money between real accounts.',
    },
];

export function tourElementSelector(tourId: string): string {
    return `[data-tour="${tourId}"]`;
}

export function teamPath(teamSlug: string, relativePath: string): string {
    const trimmed = relativePath.replace(/^\/+/, '');

    return `/${teamSlug}/${trimmed}`;
}

export function stepMatchesPath(
    step: OnboardingTourStep,
    pathname: string,
): boolean {
    if (step.pathIncludes === null) {
        return true;
    }

    return pathname.includes(step.pathIncludes);
}
