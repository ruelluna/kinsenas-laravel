import { useCallback, useSyncExternalStore } from 'react';
import {
    dismissBanner,
    isBannerDismissed,
    subscribeBannerDismiss,
} from '@/lib/dismissible-banner';

export function useDismissibleBanner(storageKey: string): {
    dismissed: boolean;
    dismiss: () => void;
} {
    const getSnapshot = useCallback(
        () => isBannerDismissed(storageKey),
        [storageKey],
    );

    const dismissed = useSyncExternalStore(
        subscribeBannerDismiss,
        getSnapshot,
        () => false,
    );

    const dismiss = useCallback(() => {
        dismissBanner(storageKey);
    }, [storageKey]);

    return { dismissed, dismiss };
}
