import { DismissButton } from '@/components/dismiss-button';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { pageContentPaddingX } from '@/components/page-content';
import { usePwaInstall } from '@/contexts/pwa-install-context';
import { cn } from '@/lib/utils';

export function InstallAppBanner() {
    const {
        showBanner,
        isIosInstall,
        canNativePrompt,
        dismissBanner,
        openInstallGuide,
        promptInstall,
    } = usePwaInstall();

    if (!showBanner) {
        return null;
    }

    return (
        <div
            className={cn(
                'border-b border-sidebar-border/50 bg-muted/40',
                pageContentPaddingX,
                'py-3',
            )}
        >
            <Alert className="relative border-primary/20 bg-background pr-12">
                <DismissButton
                    onDismiss={dismissBanner}
                    label="Dismiss install app banner"
                    className="absolute top-2 right-2"
                />
                <AlertTitle>Install Kinsenas</AlertTitle>
                <AlertDescription className="space-y-3">
                    <p>
                        {isIosInstall
                            ? 'Add Kinsenas to your home screen for quick access without the browser bar.'
                            : 'Add Kinsenas to your home screen or desktop for a faster, app-like experience.'}
                    </p>
                    {isIosInstall ? (
                        <Button
                            type="button"
                            size="sm"
                            variant="default"
                            onClick={openInstallGuide}
                        >
                            How to install
                        </Button>
                    ) : canNativePrompt ? (
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
                    ) : null}
                </AlertDescription>
            </Alert>
        </div>
    );
}
