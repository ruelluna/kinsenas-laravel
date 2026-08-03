import { useEffect } from 'react';
import { DismissButton } from '@/components/dismiss-button';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { usePwaInstall } from '@/contexts/pwa-install-context';
import { PWA_AUTH_AUTO_OPEN_SESSION_KEY } from '@/lib/pwa-install';

export function InstallAppAuthPrompt() {
    const {
        showAuthPrompt,
        isIosInstall,
        canNativePrompt,
        dismissBanner,
        openInstallGuide,
        promptInstall,
    } = usePwaInstall();

    useEffect(() => {
        if (!showAuthPrompt) {
            return;
        }

        const timer = window.setTimeout(() => {
            try {
                if (sessionStorage.getItem(PWA_AUTH_AUTO_OPEN_SESSION_KEY)) {
                    return;
                }

                sessionStorage.setItem(PWA_AUTH_AUTO_OPEN_SESSION_KEY, '1');
            } catch {
                return;
            }

            if (isIosInstall || !canNativePrompt) {
                openInstallGuide();
            }
        }, 2000);

        return () => {
            window.clearTimeout(timer);
        };
    }, [
        showAuthPrompt,
        isIosInstall,
        canNativePrompt,
        openInstallGuide,
    ]);

    if (!showAuthPrompt) {
        return null;
    }

    return (
        <Alert className="relative border-primary/20 bg-primary/5 pr-12">
            <DismissButton
                onDismiss={dismissBanner}
                label="Dismiss install app prompt"
                className="absolute top-2 right-2"
            />
            <AlertTitle>Install Kinsenas</AlertTitle>
            <AlertDescription className="space-y-3">
                <p>
                    {isIosInstall
                        ? 'Add Kinsenas to your home screen for quick access without the browser bar.'
                        : 'Install Kinsenas on this device for a faster, app-like experience.'}
                </p>
                {isIosInstall || !canNativePrompt ? (
                    <Button
                        type="button"
                        size="sm"
                        variant="default"
                        onClick={openInstallGuide}
                    >
                        How to install
                    </Button>
                ) : (
                    <Button
                        type="button"
                        size="sm"
                        variant="default"
                        onClick={() => {
                            void promptInstall();
                        }}
                    >
                        Install app
                    </Button>
                )}
            </AlertDescription>
        </Alert>
    );
}
