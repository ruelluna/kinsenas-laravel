import { ensureServiceWorkerRegistered } from '@/lib/register-pwa';

export type PushContentEncoding = 'aes128gcm' | 'aesgcm';

function urlBase64ToUint8Array(base64String: string): Uint8Array<ArrayBuffer> {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding)
        .replace(/-/g, '+')
        .replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let index = 0; index < rawData.length; index += 1) {
        outputArray[index] = rawData.charCodeAt(index);
    }

    return outputArray;
}

function csrfToken(): string {
    const token = document.querySelector<HTMLMetaElement>(
        'meta[name="csrf-token"]',
    )?.content;

    if (!token) {
        throw new Error('CSRF token not found.');
    }

    return token;
}

export function resolvePushContentEncoding(
    subscription: PushSubscription,
): PushContentEncoding {
    if (
        subscription.endpoint.includes('mozilla.com') ||
        subscription.endpoint.includes('push.services.mozilla.com')
    ) {
        return 'aesgcm';
    }

    return 'aes128gcm';
}

export async function subscribeToWebPush(vapidPublicKey: string): Promise<void> {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        throw new Error('Push notifications are not supported in this browser.');
    }

    if (!vapidPublicKey) {
        throw new Error('Push notifications are not configured on this server.');
    }

    const permission = await Notification.requestPermission();

    if (permission !== 'granted') {
        throw new Error('Notification permission was not granted.');
    }

    await ensureServiceWorkerRegistered();

    const registration = await navigator.serviceWorker.ready;
    const existingSubscription =
        await registration.pushManager.getSubscription();

    if (existingSubscription) {
        await existingSubscription.unsubscribe();
    }

    const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
    });

    const json = subscription.toJSON();

    if (!json.endpoint || !json.keys?.p256dh || !json.keys.auth) {
        throw new Error('Invalid push subscription payload.');
    }

    const contentEncoding = resolvePushContentEncoding(subscription);

    const response = await fetch('/settings/notifications/push-subscription', {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            endpoint: json.endpoint,
            keys: {
                p256dh: json.keys.p256dh,
                auth: json.keys.auth,
            },
            contentEncoding,
        }),
    });

    if (!response.ok) {
        throw new Error('Unable to save push subscription.');
    }
}

export async function unsubscribeFromWebPush(): Promise<void> {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    const registration = await navigator.serviceWorker.ready;
    const subscription = await registration.pushManager.getSubscription();

    if (!subscription) {
        return;
    }

    const endpoint = subscription.endpoint;

    await subscription.unsubscribe();

    await fetch('/settings/notifications/push-subscription', {
        method: 'DELETE',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify({ endpoint }),
    });
}

export function isWebPushSupported(): boolean {
    return (
        typeof window !== 'undefined' &&
        'serviceWorker' in navigator &&
        'PushManager' in window &&
        'Notification' in window
    );
}
