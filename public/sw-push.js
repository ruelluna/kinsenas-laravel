self.addEventListener('push', (event) => {
    if (!event.data) {
        return;
    }

    let payload = {};

    try {
        payload = event.data.json();
    } catch {
        payload = {
            title: 'Kinsenas',
            body: event.data.text(),
        };
    }

    const title = payload.title ?? 'Kinsenas';
    const options = {
        body: payload.body ?? '',
        data: payload.data ?? {},
        icon: '/icons/icon-192.png',
        badge: '/icons/icon-192.png',
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

function resolveNotificationUrl(path) {
    if (path == null || path === '') {
        return new URL('/launch', self.location.origin).href;
    }

    try {
        const url = new URL(path, self.location.origin);

        if (url.pathname === '/dashboard') {
            url.pathname = '/launch';
        }

        return url.href;
    } catch {
        return new URL('/launch', self.location.origin).href;
    }
}

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = resolveNotificationUrl(
        event.notification.data?.actionUrl ?? '/launch',
    );

    event.waitUntil(
        clients
            .matchAll({ type: 'window', includeUncontrolled: true })
            .then((windowClients) => {
                for (const client of windowClients) {
                    if (client.url.startsWith(targetUrl) && 'focus' in client) {
                        return client.focus();
                    }
                }

                if (clients.openWindow) {
                    return clients.openWindow(targetUrl);
                }

                return undefined;
            }),
    );
});
