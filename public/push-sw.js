// Web Push handlers, loaded into the PWA service worker (see `workbox.importScripts` in vite.config.ts).
self.addEventListener('push', (event) => {
    let payload = {};
    try {
        payload = event.data ? event.data.json() : {};
    } catch {
        payload = { title: 'LaundryHub', body: event.data ? event.data.text() : '' };
    }

    const { title = 'LaundryHub', ...options } = payload;

    event.waitUntil(
        self.clients
            .matchAll({ type: 'window', includeUncontrolled: true })
            .then((clients) => {
                // The open app already pops the job up itself; only notify when it is not on screen.
                if (clients.some((client) => client.visibilityState === 'visible')) {
                    return undefined;
                }

                return self.registration.showNotification(title, options);
            }),
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const path = (event.notification.data && event.notification.data.url) || '/driver';
    const target = new URL(path, self.location.origin).href;

    event.waitUntil(
        self.clients
            .matchAll({ type: 'window', includeUncontrolled: true })
            .then((clients) => {
                for (const client of clients) {
                    if (client.url.startsWith(self.location.origin) && 'focus' in client) {
                        return client.navigate(target).then((c) => (c || client).focus());
                    }
                }

                return self.clients.openWindow(target);
            }),
    );
});
