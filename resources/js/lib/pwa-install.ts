export const PWA_INSTALL_DISMISS_KEY = 'kinsenas.dismiss.pwaInstall.v1';

export const PWA_INSTALLED_KEY = 'kinsenas.pwaInstall.installed.v1';

export const PWA_INSTALL_VISIT_COUNT_KEY = 'kinsenas.pwaInstall.visitCount.v1';

export const PWA_INSTALL_HAS_PLAN_KEY = 'kinsenas.pwaInstall.hasPlan.v1';

export const PWA_ENGAGEMENT_CHANGE_EVENT = 'kinsenas:pwa-engagement-change';

export type BeforeInstallPromptEvent = Event & {
    prompt: () => Promise<void>;
    userChoice: Promise<{ outcome: 'accepted' | 'dismissed' }>;
};

export const PWA_AUTH_AUTO_OPEN_SESSION_KEY =
    'kinsenas.pwaInstall.authAutoOpen.session.v1';

export type InstallGuideVariant = 'ios' | 'chromium' | 'generic';

export function isAuthInstallPage(component: string): boolean {
    return component === 'auth/register' || component === 'auth/login';
}

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

export function isChromiumBrowser(): boolean {
    if (typeof navigator === 'undefined') {
        return false;
    }

    const ua = navigator.userAgent;

    if (/Firefox\//.test(ua) && !/Seamonkey\//.test(ua)) {
        return false;
    }

    return (
        /Chrome|CriOS|Edg|OPR|Brave/.test(ua) ||
        /SamsungBrowser\//.test(ua)
    );
}

export function getInstallGuideVariant(
    hasNativePrompt: boolean,
): InstallGuideVariant {
    if (isIosSafari()) {
        return 'ios';
    }

    if (hasNativePrompt || isChromiumBrowser()) {
        return 'chromium';
    }

    return 'generic';
}

export function isStandaloneDisplay(): boolean {
    if (typeof window === 'undefined') {
        return false;
    }

    if (window.matchMedia('(display-mode: standalone)').matches) {
        return true;
    }

    if (window.matchMedia('(display-mode: minimal-ui)').matches) {
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

export function isPwaInstalled(): boolean {
    if (isStandaloneDisplay()) {
        return true;
    }

    try {
        return window.localStorage.getItem(PWA_INSTALLED_KEY) === '1';
    } catch {
        return false;
    }
}

export function markPwaInstalled(): void {
    try {
        window.localStorage.setItem(PWA_INSTALLED_KEY, '1');
        window.localStorage.setItem(PWA_INSTALL_DISMISS_KEY, '1');
    } catch {
        // Ignore.
    }
}

export function clearPwaInstalledMark(): void {
    try {
        window.localStorage.removeItem(PWA_INSTALLED_KEY);
    } catch {
        // Ignore.
    }
}

export async function syncPwaInstalledFromBrowser(): Promise<boolean> {
    if (isStandaloneDisplay()) {
        markPwaInstalled();

        return true;
    }

    if (!isPwaInstalled()) {
        return false;
    }

    if (!('getInstalledRelatedApps' in navigator)) {
        return true;
    }

    try {
        const related = await navigator.getInstalledRelatedApps();

        if (related.length === 0) {
            clearPwaInstalledMark();

            return false;
        }
    } catch {
        return true;
    }

    return true;
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
