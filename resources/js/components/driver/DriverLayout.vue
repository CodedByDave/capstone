<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Home, Map, MapPin, User } from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, watch } from 'vue';
import { toast } from 'vue3-toastify';

defineProps<{
    title: string;
    /** Hide the bottom navigation, for full-screen detail pages that have their own action bar. */
    hideNav?: boolean;
    /** Remove the page padding, for full-bleed pages such as the map. */
    flush?: boolean;
}>();

interface ToastMessage {
    type: 'success' | 'error' | 'warning' | 'info';
    message: string;
}

const page = usePage();

watch(
    () => page.props.toast as ToastMessage | undefined,
    (notification) => {
        if (!notification?.message) return;
        (toast[notification.type] ?? toast)(notification.message);
    },
    { immediate: true },
);

// A new run assigned to this driver pops up as soon as the app next checks, wherever they are in it.
interface DriverAlert {
    id: string;
    title: string;
    body: string;
    url: string | null;
}

const shownAlerts = new Set<string>();
let alertTimer: ReturnType<typeof setInterval> | undefined;

async function checkAlerts() {
    if (document.hidden) return;

    try {
        const response = await fetch('/driver/alerts', {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });
        if (!response.ok) return;

        const { alerts } = (await response.json()) as { alerts: DriverAlert[] };

        for (const alert of alerts) {
            if (shownAlerts.has(alert.id)) continue;
            shownAlerts.add(alert.id);

            navigator.vibrate?.([200, 100, 200]);
            toast.info(`${alert.title}. ${alert.body}`, {
                autoClose: 10000,
                onClick: () => alert.url && router.visit(alert.url),
            });
            // Shown once; the order itself stays on the dispatch list.
            await fetch(`/driver/alerts/${alert.id}/read`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-XSRF-TOKEN': decodeURIComponent(
                        document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '',
                    ),
                },
                credentials: 'same-origin',
            });
            router.reload({ only: ['job', 'open', 'queue', 'next_stop'] });
        }
    } catch {
        // Offline or a hiccup: try again on the next tick.
    }
}

const tabs = [
    { label: 'Home', href: '/driver', icon: Home, match: /^\/driver\/?$/ },
    {
        label: 'Dispatch',
        href: '/driver/jobs',
        icon: MapPin,
        match: /^\/driver\/jobs/,
    },
    { label: 'Map', href: '/driver/map', icon: Map, match: /^\/driver\/map/ },
    {
        label: 'Profile',
        href: '/driver/profile',
        icon: User,
        match: /^\/driver\/profile/,
    },
];

function isActive(match: RegExp): boolean {
    return match.test(page.url.split('?')[0]);
}

// The driver app installs on its own: point the page at the driver manifest while it is open.
let manifestLink: HTMLLinkElement | null = null;
let previousManifest: string | null = null;

// The shop can change the driver's schedule while the app is open, so their status and attendance
// card refresh on their own instead of waiting for a manual reload.
let statusTimer: ReturnType<typeof setInterval> | undefined;

function refreshStatus() {
    if (document.hidden) return;
    router.reload({ only: ['driver', 'attendance'] });
}

onMounted(() => {
    checkAlerts();
    alertTimer = setInterval(checkAlerts, 15000);
    statusTimer = setInterval(refreshStatus, 30000);
    document.addEventListener('visibilitychange', refreshStatus);
    manifestLink = document.querySelector('link[rel="manifest"]');
    previousManifest = manifestLink?.getAttribute('href') ?? null;
    manifestLink?.setAttribute('href', '/driver/manifest.webmanifest');
});

onBeforeUnmount(() => {
    clearInterval(alertTimer);
    clearInterval(statusTimer);
    document.removeEventListener('visibilitychange', refreshStatus);
    if (manifestLink && previousManifest) {
        manifestLink.setAttribute('href', previousManifest);
    }
});
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen bg-white text-slate-900">
        <div class="mx-auto flex min-h-screen max-w-md flex-col bg-white">
            <main
                :class="[
                    'flex-1',
                    flush ? '' : 'px-5 pt-8',
                    hideNav ? 'pb-4' : 'pb-28',
                ]"
            >
                <slot />
            </main>

            <nav
                v-if="!hideNav"
                class="fixed inset-x-0 bottom-0 z-30 mx-auto max-w-md px-5 pb-[max(1rem,env(safe-area-inset-bottom))]"
                aria-label="Driver navigation"
            >
                <div
                    class="grid grid-cols-4 rounded-2xl bg-[#0a2dff] px-2 py-3 shadow-lg"
                >
                    <Link
                        v-for="tab in tabs"
                        :key="tab.href"
                        :href="tab.href"
                        :class="[
                            'flex flex-col items-center gap-1 text-[11px] font-semibold text-white',
                            isActive(tab.match) ? 'opacity-100' : 'opacity-70',
                        ]"
                        :aria-current="isActive(tab.match) ? 'page' : undefined"
                    >
                        <component :is="tab.icon" class="h-5 w-5" />
                        {{ tab.label }}
                    </Link>
                </div>
            </nav>
        </div>
    </div>
</template>
