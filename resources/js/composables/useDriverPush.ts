import { ref } from 'vue';

function xsrf(): string {
    return decodeURIComponent(
        document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '',
    );
}

function urlBase64ToUint8Array(value: string): Uint8Array<ArrayBuffer> {
    const padded = (value + '='.repeat((4 - (value.length % 4)) % 4))
        .replace(/-/g, '+')
        .replace(/_/g, '/');
    const raw = atob(padded);
    const bytes = new Uint8Array(new ArrayBuffer(raw.length));
    for (let i = 0; i < raw.length; i++) bytes[i] = raw.charCodeAt(i);

    return bytes;
}

async function post(url: string, body: unknown): Promise<boolean> {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-XSRF-TOKEN': xsrf(),
        },
        credentials: 'same-origin',
        body: JSON.stringify(body),
    });

    return response.ok;
}

const supported =
    typeof window !== 'undefined' &&
    'serviceWorker' in navigator &&
    'PushManager' in window &&
    'Notification' in window;

const permission = ref<NotificationPermission>(
    supported ? Notification.permission : 'denied',
);
const subscribed = ref(false);

/** Lets a driver receive new-job alerts on their phone even when the app is closed. */
export function useDriverPush() {
    async function registration() {
        return navigator.serviceWorker.ready;
    }

    /** Re-sends the device's subscription so the server always has a current one. */
    async function sync() {
        if (!supported || Notification.permission !== 'granted') return;

        try {
            const existing = await (
                await registration()
            ).pushManager.getSubscription();
            if (existing) {
                subscribed.value = await post(
                    '/driver/push/subscribe',
                    existing.toJSON(),
                );
            }
        } catch {
            // Offline or a hiccup: try again next time the app opens.
        }
    }

    async function enable(): Promise<boolean> {
        if (!supported) return false;

        permission.value = await Notification.requestPermission();
        if (permission.value !== 'granted') return false;

        try {
            const keyResponse = await fetch('/driver/push/key', {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const { public_key } = (await keyResponse.json()) as {
                public_key: string | null;
            };
            if (!public_key) return false;

            const reg = await registration();
            const subscription =
                (await reg.pushManager.getSubscription()) ??
                (await reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(public_key),
                }));

            subscribed.value = await post(
                '/driver/push/subscribe',
                subscription.toJSON(),
            );

            return subscribed.value;
        } catch {
            return false;
        }
    }

    return { supported, permission, subscribed, enable, sync };
}
