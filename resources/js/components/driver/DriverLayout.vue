<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ClipboardList, Home, Map, User } from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, watch } from 'vue';
import { toast } from 'vue3-toastify';

defineProps<{
    title: string;
    /** Hide the bottom navigation, for full-screen detail pages that have their own action bar. */
    hideNav?: boolean;
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

const tabs = [
    { label: 'Home', href: '/driver', icon: Home, match: /^\/driver\/?$/ },
    {
        label: 'Jobs',
        href: '/driver/jobs',
        icon: ClipboardList,
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

onMounted(() => {
    manifestLink = document.querySelector('link[rel="manifest"]');
    previousManifest = manifestLink?.getAttribute('href') ?? null;
    manifestLink?.setAttribute('href', '/driver/manifest.webmanifest');
});

onBeforeUnmount(() => {
    if (manifestLink && previousManifest) {
        manifestLink.setAttribute('href', previousManifest);
    }
});
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen bg-[#f3f4f6] text-slate-900">
        <div class="mx-auto flex min-h-screen max-w-md flex-col bg-[#f3f4f6]">
            <main :class="['flex-1 px-4 pt-4', hideNav ? 'pb-4' : 'pb-24']">
                <slot />
            </main>

            <nav
                v-if="!hideNav"
                class="fixed inset-x-0 bottom-0 z-30 mx-auto grid max-w-md grid-cols-5 border-t bg-white pb-[env(safe-area-inset-bottom)]"
                aria-label="Driver navigation"
            >
                <Link
                    v-for="tab in tabs"
                    :key="tab.href"
                    :href="tab.href"
                    :class="[
                        'flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium',
                        isActive(tab.match)
                            ? 'text-emerald-800'
                            : 'text-slate-500',
                    ]"
                    :aria-current="isActive(tab.match) ? 'page' : undefined"
                >
                    <component :is="tab.icon" class="h-5 w-5" />
                    {{ tab.label }}
                </Link>
            </nav>
        </div>
    </div>
</template>
