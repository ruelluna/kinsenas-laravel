import { usePage } from '@inertiajs/react';
import {
    createContext,
    useCallback,
    useContext,
    useEffect,
    useMemo,
    useState,
    type ReactNode,
} from 'react';
import {
    dismissBanner,
    isBannerDismissed,
} from '@/lib/dismissible-banner';
import {
    isIosSafari,
    isPwaEngagementReady,
    isStandaloneDisplay,
    PWA_ENGAGEMENT_CHANGE_EVENT,
    PWA_INSTALL_DISMISS_KEY,
    recordPwaInstallVisit,
    type BeforeInstallPromptEvent,
} from '@/lib/pwa-install';
import type { SharedData } from '@/types';

type PwaInstallContextValue = {
    canOfferInstall: boolean;
    showBanner: boolean;
    isIosInstall: boolean;
    canNativePrompt: boolean;
    guideOpen: boolean;
    setGuideOpen: (open: boolean) => void;
    dismissBanner: () => void;
    openInstallGuide: () => void;
    promptInstall: () => Promise<void>;
};

const PwaInstallContext = createContext<PwaInstallContextValue | null>(null);

export function PwaInstallProvider({ children }: { children: ReactNode }) {
    const page = usePage<SharedData>();
    const { subscription, vaultLocked } = page.props;
    const hasAccess = subscription?.hasAccess ?? false;

    const [deferredPrompt, setDeferredPrompt] =
        useState<BeforeInstallPromptEvent | null>(null);
    const [guideOpen, setGuideOpen] = useState(false);
    const [engagementReady, setEngagementReady] = useState(false);
    const [bannerDismissed, setBannerDismissed] = useState(false);
    const [standalone, setStandalone] = useState(false);

    const isIosInstall = isIosSafari();

    useEffect(() => {
        setStandalone(isStandaloneDisplay());
        setBannerDismissed(isBannerDismissed(PWA_INSTALL_DISMISS_KEY));
        setEngagementReady(isPwaEngagementReady());
    }, []);

    useEffect(() => {
        const refreshEngagement = () => {
            setEngagementReady(isPwaEngagementReady());
        };

        window.addEventListener(
            PWA_ENGAGEMENT_CHANGE_EVENT,
            refreshEngagement,
        );

        return () => {
            window.removeEventListener(
                PWA_ENGAGEMENT_CHANGE_EVENT,
                refreshEngagement,
            );
        };
    }, []);

    useEffect(() => {
        if (!hasAccess || standalone) {
            return;
        }

        recordPwaInstallVisit();
        setEngagementReady(isPwaEngagementReady());
    }, [hasAccess, standalone]);

    useEffect(() => {
        if (standalone || isIosInstall) {
            return;
        }

        const handleBeforeInstall = (event: Event) => {
            event.preventDefault();
            setDeferredPrompt(event as BeforeInstallPromptEvent);
        };

        window.addEventListener('beforeinstallprompt', handleBeforeInstall);

        return () => {
            window.removeEventListener(
                'beforeinstallprompt',
                handleBeforeInstall,
            );
        };
    }, [standalone, isIosInstall]);

    const canOfferInstall =
        !standalone && hasAccess && !vaultLocked && (isIosInstall || Boolean(deferredPrompt));

    const showBanner =
        canOfferInstall &&
        engagementReady &&
        !bannerDismissed &&
        (isIosInstall || Boolean(deferredPrompt));

    const dismissInstallBanner = useCallback(() => {
        dismissBanner(PWA_INSTALL_DISMISS_KEY);
        setBannerDismissed(true);
    }, []);

    const openInstallGuide = useCallback(() => {
        setGuideOpen(true);
    }, []);

    const promptInstall = useCallback(async () => {
        if (!deferredPrompt) {
            openInstallGuide();

            return;
        }

        await deferredPrompt.prompt();
        await deferredPrompt.userChoice;
        setDeferredPrompt(null);
    }, [deferredPrompt, openInstallGuide]);

    const value = useMemo(
        (): PwaInstallContextValue => ({
            canOfferInstall,
            showBanner,
            isIosInstall,
            canNativePrompt: Boolean(deferredPrompt),
            guideOpen,
            setGuideOpen,
            dismissBanner: dismissInstallBanner,
            openInstallGuide,
            promptInstall,
        }),
        [
            canOfferInstall,
            showBanner,
            isIosInstall,
            deferredPrompt,
            guideOpen,
            dismissInstallBanner,
            openInstallGuide,
            promptInstall,
        ],
    );

    return (
        <PwaInstallContext.Provider value={value}>
            {children}
        </PwaInstallContext.Provider>
    );
}

export function usePwaInstall(): PwaInstallContextValue {
    const context = useContext(PwaInstallContext);

    if (!context) {
        throw new Error('usePwaInstall must be used within PwaInstallProvider');
    }

    return context;
}
