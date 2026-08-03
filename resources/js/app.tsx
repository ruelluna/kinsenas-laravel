import { createInertiaApp } from '@inertiajs/react';
import { KinsenasProvider } from '@kinsenas/ui';
import { toast } from 'sonner';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import SettingsLayout from '@/layouts/settings/layout';
import 'driver.js/dist/driver.css';

const appName = import.meta.env.VITE_APP_NAME || 'Kinsenas';

if (import.meta.env.PROD) {
    void import('virtual:pwa-register').then(({ registerSW }) => {
        registerSW({
            immediate: true,
            onNeedRefresh() {
                toast('Update available', {
                    description: 'A new version of Kinsenas is ready.',
                    action: {
                        label: 'Reload',
                        onClick: () => window.location.reload(),
                    },
                    duration: Infinity,
                });
            },
        });
    });
}

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
                <TooltipProvider delayDuration={0}>
                    {app}
                    <Toaster />
                </TooltipProvider>
            </KinsenasProvider>
        );
    },
    progress: {
        color: '#0D7377',
    },
});

// This will set light / dark mode on load...
initializeTheme();
