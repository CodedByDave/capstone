import { router } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

type Popup = {
    id: string;
    type: string | null;
    title: string;
    body: string;
    url: string | null;
};

const POLL_MS = 15_000;
const STORAGE_KEY = 'shop-popups-seen';

function loadSeen(): Set<string> {
    try {
        return new Set(
            JSON.parse(sessionStorage.getItem(STORAGE_KEY) ?? '[]') as string[],
        );
    } catch {
        return new Set();
    }
}

function saveSeen(seen: Set<string>) {
    try {
        sessionStorage.setItem(
            STORAGE_KEY,
            JSON.stringify([...seen].slice(-100)),
        );
    } catch {
        // storage unavailable: the popup may repeat after a reload, which is harmless
    }
}

/**
 * Polls for new online orders while the shop owner or staff member is working and shows
 * each one as a clickable popup that opens the order.
 */
export function useShopOrderPopups(pollUrl: () => string) {
    const seen = loadSeen();
    let timer: ReturnType<typeof setInterval> | null = null;

    async function poll() {
        if (document.hidden) return;

        try {
            const res = await fetch(pollUrl(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
            });
            if (!res.ok) return;

            const data = (await res.json()) as { popups: Popup[] };
            const fresh = data.popups.filter((popup) => !seen.has(popup.id));

            fresh.forEach((popup) => {
                seen.add(popup.id);
                toast.info(`${popup.title}: ${popup.body}`, {
                    autoClose: 15_000,
                    position: 'top-right',
                    onClick: () => popup.url && router.visit(popup.url),
                });
            });

            if (fresh.length > 0) {
                saveSeen(seen);
                router.reload({ only: ['unreadNotifications'] });
            }
        } catch {
            // network hiccup: try again on the next tick
        }
    }

    onMounted(() => {
        poll();
        timer = setInterval(poll, POLL_MS);
    });

    onUnmounted(() => {
        if (timer) clearInterval(timer);
    });
}
