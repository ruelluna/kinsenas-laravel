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
    getInstallGuideVariant,
    isAuthInstallPage,
    isIosSafari,
    isStandaloneDisplay,
    PWA_INSTALL_DISMISS_KEY,
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

    const [deferredPrompt, setDeferredPrompt] =
        useState<BeforeInstallPromptEvent | null>(null);
    const [guideOpen, setGuideOpen] = useState(false);
    const [bannerDismissed, setBannerDismissed] = useState(false);
    const [standalone, setStandalone] = useState(false);

    const isIosInstall = isIosSafari();
    const canNativePrompt = Boolean(deferredPrompt);
    const installGuideVariant = getInstallGuideVariant(canNativePrompt);

    useEffect(() => {
        setStandalone(isStandaloneDisplay());
        setBannerDismissed(isBannerDismissed(PWA_INSTALL_DISMISS_KEY));
    }, []);

    useEffect(() => {
        if (standalone) {
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
    }, [standalone]);

    const canOfferInstall =
        !standalone && (onAuthInstallPage || isLoggedIn);

    const showInstallPrompt = canOfferInstall && !bannerDismissed;

    const showAuthPrompt = showInstallPrompt && onAuthInstallPage;

    const showBanner = showInstallPrompt && !onAuthInstallPage;

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
