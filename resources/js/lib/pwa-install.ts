export const PWA_INSTALL_DISMISS_KEY = 'kinsenas.dismiss.pwaInstall.v1';

export const PWA_INSTALL_VISIT_COUNT_KEY = 'kinsenas.pwaInstall.visitCount.v1';

export const PWA_INSTALL_HAS_PLAN_KEY = 'kinsenas.pwaInstall.hasPlan.v1';

export const PWA_ENGAGEMENT_CHANGE_EVENT = 'kinsenas:pwa-engagement-change';

export type BeforeInstallPromptEvent = Event & {
    prompt: () => Promise<void>;
    userChoice: Promise<{ outcome: 'accepted' | 'dismissed' }>;
};

export function isIosSafari(): boolean {
    if (typeof navigator === 'undefined') {
        return false;
    }

    const ua = navigator.userAgent;
    const isIos = /iPad|iPhone|iPod/.test(ua);

    return (
        isIos &&
        !isStandaloneDisplay() &&
        /Safari/.test(ua) &&
        !/CriOS|FxiOS/.test(ua)
    );
}

export function isStandaloneDisplay(): boolean {
    if (typeof window === 'undefined') {
        return false;
    }

    if (window.matchMedia('(display-mode: standalone)').matches) {
        return true;
    }

    return (
        'standalone' in window.navigator &&
        Boolean(
            (window.navigator as Navigator & { standalone?: boolean })
                .standalone,
        )
    );
}

export function recordPwaInstallVisit(): number {
    try {
        const next =
            Number.parseInt(
                window.localStorage.getItem(PWA_INSTALL_VISIT_COUNT_KEY) ??
                    '0',
                10,
            ) + 1;

        window.localStorage.setItem(
            PWA_INSTALL_VISIT_COUNT_KEY,
            String(next),
        );

        return next;
    } catch {
        return 1;
    }
}

export function markPwaEngagementFromPlan(): void {
    try {
        window.localStorage.setItem(PWA_INSTALL_HAS_PLAN_KEY, '1');
        window.dispatchEvent(new Event(PWA_ENGAGEMENT_CHANGE_EVENT));
    } catch {
        // Ignore.
    }
}

export function isPwaEngagementReady(): boolean {
    try {
        const visitCount = Number.parseInt(
            window.localStorage.getItem(PWA_INSTALL_VISIT_COUNT_KEY) ?? '0',
            10,
        );

        if (visitCount >= 2) {
            return true;
        }

        return window.localStorage.getItem(PWA_INSTALL_HAS_PLAN_KEY) === '1';
    } catch {
        return false;
    }
}
