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

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const actionUrl = event.notification.data?.actionUrl ?? '/';

    event.waitUntil(
        clients
            .matchAll({ type: 'window', includeUncontrolled: true })
            .then((windowClients) => {
                for (const client of windowClients) {
                    if (client.url.includes(actionUrl) && 'focus' in client) {
                        return client.focus();
                    }
                }

                if (clients.openWindow) {
                    return clients.openWindow(actionUrl);
                }

                return undefined;
            }),
    );
});
