import { toast } from 'sonner';

export function registerPwaServiceWorker(): void {
    if (!import.meta.env.PROD || !('serviceWorker' in navigator)) {
        return;
    }

    void import('workbox-window').then(({ Workbox }) => {
        const workbox = new Workbox('/sw.js', {
            scope: '/',
            type: 'classic',
        });

        workbox.addEventListener('waiting', () => {
            toast('Update available', {
                description: 'A new version of Kinsenas is ready.',
                action: {
                    label: 'Reload',
                    onClick: () => {
                        workbox.messageSkipWaiting();
                    },
                },
                duration: Infinity,
            });
        });

        workbox.addEventListener('controlling', () => {
            window.location.reload();
        });

        void workbox.register().catch(() => {
            // SW unavailable — install prompt may still work on some browsers.
        });
    });
}
