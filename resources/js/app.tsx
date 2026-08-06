import { createInertiaApp } from '@inertiajs/react';
import AppProviders from '@/components/app-providers';
import { initializeTheme } from '@/hooks/use-appearance';
import { registerPwaServiceWorker } from '@/lib/register-pwa';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import SettingsLayout from '@/layouts/settings/layout';
import 'driver.js/dist/driver.css';

const appName = import.meta.env.VITE_APP_NAME || 'Kinsenas';

registerPwaServiceWorker();

initializeTheme();

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
        return <AppProviders>{app}</AppProviders>;
    },
    progress: {
        color: '#1E8B75',
        includeCSS: true,
        showSpinner: false,
        delay: 150,
    },
});
