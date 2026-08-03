const DISMISS_CHANGE_EVENT = 'kinsenas:dismiss-change';

export const OPEN_BETA_BANNER_DISMISS_KEY =
    'kinsenas.dismiss.openBetaBanner.v1';

export { PWA_INSTALL_DISMISS_KEY } from '@/lib/pwa-install';

export function setupChecklistDismissKey(teamId: string): string {
    return `kinsenas.dismiss.setupChecklist.v1.${teamId}`;
}

export function isBannerDismissed(storageKey: string): boolean {
    if (typeof window === 'undefined') {
        return false;
    }

    try {
        return window.localStorage.getItem(storageKey) === '1';
    } catch {
        return false;
    }
}

export function dismissBanner(storageKey: string): void {
    try {
        window.localStorage.setItem(storageKey, '1');
        window.dispatchEvent(
            new CustomEvent(DISMISS_CHANGE_EVENT, { detail: { storageKey } }),
        );
    } catch {
        // Ignore quota / private mode failures.
    }
}

export function subscribeBannerDismiss(onStoreChange: () => void): () => void {
    if (typeof window === 'undefined') {
        return () => {};
    }

    const handler = () => {
        onStoreChange();
    };

    window.addEventListener(DISMISS_CHANGE_EVENT, handler);

    return () => {
        window.removeEventListener(DISMISS_CHANGE_EVENT, handler);
    };
}
