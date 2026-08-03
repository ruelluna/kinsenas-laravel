import { toast } from 'sonner';

let serviceWorkerRegistrationPromise: Promise<ServiceWorkerRegistration> | null =
    null;

function createWorkboxRegistration(): Promise<ServiceWorkerRegistration> {
    return import('workbox-window').then(({ Workbox }) => {
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

        return workbox.register();
    });
}

export function registerPwaServiceWorker(): void {
    if (!import.meta.env.PROD || !('serviceWorker' in navigator)) {
        return;
    }

    void ensureServiceWorkerRegistered().catch(() => {
        // SW unavailable — install prompt may still work on some browsers.
    });
}

export async function ensureServiceWorkerRegistered(): Promise<ServiceWorkerRegistration> {
    if (!('serviceWorker' in navigator)) {
        throw new Error('Service workers are not supported in this browser.');
    }

    const canRegisterInDev =
        import.meta.env.DEV && import.meta.env.VITE_PWA_DEV === 'true';

    if (!import.meta.env.PROD && !canRegisterInDev) {
        throw new Error(
            'Push requires a production build. Run npm run build or set VITE_PWA_DEV=true for local testing.',
        );
    }

    if (!serviceWorkerRegistrationPromise) {
        serviceWorkerRegistrationPromise = createWorkboxRegistration();
    }

    return serviceWorkerRegistrationPromise;
}
