import * as React from 'react';
import type { ReactNode } from 'react';
import { Text, XStack, YStack } from 'tamagui';
import { Button } from './button';

const DISMISS_KEY = 'kinsenas-pwa-install-dismissed';

type BeforeInstallPromptEvent = Event & {
    prompt: () => Promise<void>;
    userChoice: Promise<{ outcome: 'accepted' | 'dismissed' }>;
};

function isIosSafari(): boolean {
    if (typeof navigator === 'undefined') {
        return false;
    }

    const ua = navigator.userAgent;
    const isIos = /iPad|iPhone|iPod/.test(ua);
    const isStandalone =
        typeof window !== 'undefined' &&
        ('standalone' in window.navigator
            ? (window.navigator as Navigator & { standalone?: boolean })
                  .standalone
            : window.matchMedia('(display-mode: standalone)').matches);

    return isIos && !isStandalone && /Safari/.test(ua) && !/CriOS|FxiOS/.test(ua);
}

function isStandaloneDisplay(): boolean {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia('(display-mode: standalone)').matches;
}

export type InstallAppBannerProps = {
    appName?: string;
    onInstall?: () => void;
};

export function InstallAppBanner({
    appName = 'Kinsenas',
    onInstall,
}: InstallAppBannerProps) {
    const [visible, setVisible] = React.useState(false);
    const [deferredPrompt, setDeferredPrompt] =
        React.useState<BeforeInstallPromptEvent | null>(null);
    const [showIosHint, setShowIosHint] = React.useState(false);

    React.useEffect(() => {
        if (typeof window === 'undefined') {
            return;
        }

        if (isStandaloneDisplay()) {
            return;
        }

        if (localStorage.getItem(DISMISS_KEY) === '1') {
            return;
        }

        if (isIosSafari()) {
            setShowIosHint(true);
            setVisible(true);

            return;
        }

        const handleBeforeInstall = (event: Event) => {
            event.preventDefault();
            setDeferredPrompt(event as BeforeInstallPromptEvent);
            setVisible(true);
        };

        window.addEventListener('beforeinstallprompt', handleBeforeInstall);

        return () => {
            window.removeEventListener(
                'beforeinstallprompt',
                handleBeforeInstall,
            );
        };
    }, []);

    function dismiss(): void {
        localStorage.setItem(DISMISS_KEY, '1');
        setVisible(false);
    }

    async function install(): Promise<void> {
        if (!deferredPrompt) {
            return;
        }

        await deferredPrompt.prompt();
        await deferredPrompt.userChoice;
        setDeferredPrompt(null);
        setVisible(false);
        onInstall?.();
    }

    if (!visible) {
        return null;
    }

    return (
        <YStack
            backgroundColor="$secondary"
            borderBottomWidth={1}
            borderColor="$borderColor"
            paddingHorizontal={16}
            paddingVertical={12}
            gap={8}
        >
            <XStack justifyContent="space-between" alignItems="flex-start" gap={12}>
                <YStack flex={1} gap={4}>
                    <Text fontWeight="600" fontSize={14}>
                        Install {appName}
                    </Text>
                    {showIosHint ? (
                        <Text fontSize={13} color="$mutedForeground">
                            Tap Share, then &quot;Add to Home Screen&quot; for
                            quick access.
                        </Text>
                    ) : (
                        <Text fontSize={13} color="$mutedForeground">
                            Add to your home screen for a faster, app-like
                            experience.
                        </Text>
                    )}
                </YStack>
                <Button variant="ghost" size="sm" onPress={dismiss}>
                    Dismiss
                </Button>
            </XStack>
            {!showIosHint && deferredPrompt && (
                <Button size="sm" onPress={install}>
                    Install app
                </Button>
            )}
        </YStack>
    );
}
