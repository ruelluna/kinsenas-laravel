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
import { useIsMobile } from '@/hooks/use-mobile';
import {
    getInstallGuideVariant,
    isAuthInstallPage,
    isIosSafari,
    isMobileInstallDevice,
    isPwaInstalled,
    markPwaInstalled,
    PWA_INSTALL_DISMISS_KEY,
    syncPwaInstalledFromBrowser,
    type BeforeInstallPromptEvent,
    type InstallGuideVariant,
} from '@/lib/pwa-install';
import type { SharedData } from '@/types';

type PwaInstallContextValue = {
    canOfferInstall: boolean;
    showBanner: boolean;
    showAuthPrompt: boolean;
    isIosInstall: boolean;
    canNativePrompt: boolean;
    installGuideVariant: InstallGuideVariant;
    guideOpen: boolean;
    setGuideOpen: (open: boolean) => void;
    dismissBanner: () => void;
    openInstallGuide: () => void;
    promptInstall: () => Promise<void>;
};

const PwaInstallContext = createContext<PwaInstallContextValue | null>(null);

export function PwaInstallProvider({ children }: { children: ReactNode }) {
    const page = usePage<SharedData>();
    const { auth } = page.props;
    const isLoggedIn = auth.user !== null;
    const onAuthInstallPage = isAuthInstallPage(page.component);
    const isLayoutMobile = useIsMobile();
    const [clientReady, setClientReady] = useState(false);
    const isMobileInstall =
        clientReady && (isLayoutMobile || isMobileInstallDevice());

    const [deferredPrompt, setDeferredPrompt] =
        useState<BeforeInstallPromptEvent | null>(null);
    const [guideOpen, setGuideOpen] = useState(false);
    const [bannerDismissed, setBannerDismissed] = useState(false);
    const [installed, setInstalled] = useState(() => isPwaInstalled());

    const isIosInstall = clientReady && isIosSafari();
    const canNativePrompt = Boolean(deferredPrompt);
    const installGuideVariant = getInstallGuideVariant(canNativePrompt);

    const applyInstalledState = useCallback(() => {
        if (!isPwaInstalled()) {
            return;
        }

        markPwaInstalled();
        setInstalled(true);
        setBannerDismissed(true);
        setGuideOpen(false);
        setDeferredPrompt(null);
    }, []);

    useEffect(() => {
        setClientReady(true);
    }, []);

    useEffect(() => {
        applyInstalledState();
        setBannerDismissed(isBannerDismissed(PWA_INSTALL_DISMISS_KEY));

        void syncPwaInstalledFromBrowser().then((stillInstalled) => {
            setInstalled(stillInstalled);

            if (stillInstalled) {
                setBannerDismissed(true);
                setGuideOpen(false);
            }
        });

        const onAppInstalled = () => {
            applyInstalledState();
        };

        const standaloneQuery = window.matchMedia(
            '(display-mode: standalone), (display-mode: minimal-ui)',
        );
        const onDisplayModeChange = () => {
            applyInstalledState();
        };

        window.addEventListener('appinstalled', onAppInstalled);
        standaloneQuery.addEventListener('change', onDisplayModeChange);

        return () => {
            window.removeEventListener('appinstalled', onAppInstalled);
            standaloneQuery.removeEventListener('change', onDisplayModeChange);
        };
    }, [applyInstalledState]);

    useEffect(() => {
        if (installed || !isMobileInstall) {
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
    }, [installed, isMobileInstall]);

    const canOfferInstall =
        !installed &&
        isMobileInstall &&
        (onAuthInstallPage || isLoggedIn);

    const showInstallPrompt = canOfferInstall && !bannerDismissed;

    const showAuthPrompt = showInstallPrompt && onAuthInstallPage;

    const showBanner = showInstallPrompt && !onAuthInstallPage;

    const dismissInstallBanner = useCallback(() => {
        dismissBanner(PWA_INSTALL_DISMISS_KEY);
        setBannerDismissed(true);
    }, []);

    const openInstallGuide = useCallback(() => {
        if (installed) {
            return;
        }

        setGuideOpen(true);
    }, [installed]);

    const promptInstall = useCallback(async () => {
        if (installed) {
            return;
        }

        if (!deferredPrompt) {
            openInstallGuide();

            return;
        }

        await deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;

        if (outcome === 'accepted') {
            markPwaInstalled();
        }

        applyInstalledState();

        setDeferredPrompt(null);
    }, [installed, deferredPrompt, openInstallGuide, applyInstalledState]);

    const value = useMemo(
        (): PwaInstallContextValue => ({
            canOfferInstall,
            showBanner,
            showAuthPrompt,
            isIosInstall,
            canNativePrompt,
            installGuideVariant,
            guideOpen,
            setGuideOpen,
            dismissBanner: dismissInstallBanner,
            openInstallGuide,
            promptInstall,
        }),
        [
            canOfferInstall,
            showBanner,
            showAuthPrompt,
            isIosInstall,
            canNativePrompt,
            installGuideVariant,
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
