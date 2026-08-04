import { createInertiaApp } from '@inertiajs/react';
import { KinsenasProvider } from '@kinsenas/ui';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { NavigationLoadingProvider } from '@/contexts/navigation-loading-context';
import { initializeTheme } from '@/hooks/use-appearance';
import { registerPwaServiceWorker } from '@/lib/register-pwa';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import SettingsLayout from '@/layouts/settings/layout';
import 'driver.js/dist/driver.css';

const appName = import.meta.env.VITE_APP_NAME || 'Kinsenas';

registerPwaServiceWorker();

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'welcome':
            case name.startsWith('marketing/'):
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
            case name.startsWith('teams/'):
                return [AppLayout, SettingsLayout];
            case name.startsWith('billing/'):
                return AppLayout;
            default:
                return AppLayout;
        }
    },
    strictMode: true,
    withApp(app) {
        return (
            <KinsenasProvider>
                <NavigationLoadingProvider>
                    <TooltipProvider delayDuration={0}>
                        {app}
                        <Toaster />
                    </TooltipProvider>
                </NavigationLoadingProvider>
            </KinsenasProvider>
        );
    },
    progress: {
        color: '#0D7377',
        includeCSS: true,
        showSpinner: false,
        delay: 150,
    },
});

// This will set light / dark mode on load...
initializeTheme();
