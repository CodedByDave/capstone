<script setup lang="ts">
import type { AppPageProps } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    Bell,
    ClipboardList,
    Home,
    User,
    WashingMachine,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import 'vue3-toastify/dist/index.css';

// Pages with their own hero (the shop page) hide the top bar.
defineProps<{ hideHeader?: boolean }>();

const page = usePage<AppPageProps>();
const url = computed(() => page.url);

const unread = computed(() => (page.props as any).unreadNotifications ?? 0);

// ─── Payment-request polling ──────────────────────────────────────────────────

const seenIds = ref<Set<string>>(new Set());
let pollTimer: ReturnType<typeof setInterval> | null = null;

async function pollNotifications() {
    // Don't poll if user is already on the notifications page
    if (url.value === '/user/notifications') return;

    try {
        const res = await fetch('/user/notifications/poll', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) return;
        const data = (await res.json()) as {
            count: number;
            unread: { id: string; type: string; title: string; body: string }[];
        };

        for (const n of data.unread) {
            if (seenIds.value.has(n.id)) continue;
            seenIds.value.add(n.id);
        }
    } catch {
        // silently ignore network errors
    }
}

// The customer app is light-only; without this, popups and toasts (rendered outside the page)
// would pick up the browser's dark mode and clash with the light pages.
let restoreDark = false;

onMounted(() => {
    pollTimer = setInterval(pollNotifications, 20_000);

    const root = document.documentElement;
    restoreDark = root.classList.contains('dark');
    root.classList.remove('dark');
    root.style.colorScheme = 'light';
});

onUnmounted(() => {
    if (pollTimer) clearInterval(pollTimer);

    const root = document.documentElement;
    root.style.colorScheme = '';
    if (restoreDark) root.classList.add('dark');
});

const navItems = [
    { href: '/user/dashboard', label: 'Home', icon: Home },
    { href: '/user/shops', label: 'Shops', icon: WashingMachine },
    { href: '/user/orders', label: 'Orders', icon: ClipboardList },
    { href: '/user/profile', label: 'Profile', icon: User },
];

function isActive(href: string) {
    if (href === '/user/dashboard') return url.value === '/user/dashboard';
    return url.value.startsWith(href);
}
</script>

<template>
    <div class="flex min-h-screen flex-col bg-white font-customer">
        <!-- ── Top Bar ────────────────────────────────────────────── -->
        <header
            v-if="!hideHeader"
            class="sticky top-0 z-40 border-b border-slate-100 bg-white"
        >
            <div
                class="mx-auto flex max-w-lg items-center justify-between px-5 py-3.5"
            >
                <div class="flex items-center gap-2">
                    <img
                        src="/laundryhub.png"
                        alt=""
                        class="h-6 w-6 object-contain"
                    />
                    <span
                        class="font-display text-sm font-semibold text-slate-900"
                        >LaundryHub</span
                    >
                </div>

                <div class="flex items-center gap-2">
                    <slot name="header-action" />

                    <!-- Notification bell -->
                    <Link
                        href="/user/notifications"
                        class="relative flex h-10 w-10 items-center justify-center rounded-full transition hover:bg-slate-100"
                        :aria-label="
                            unread > 0
                                ? `Notifications, ${unread} unread`
                                : 'Notifications'
                        "
                    >
                        <Bell class="h-5 w-5 text-slate-800" />
                        <span
                            v-if="unread > 0"
                            class="absolute top-2 right-2 h-2 w-2 rounded-full bg-blue-600 ring-2 ring-white"
                        />
                    </Link>
                </div>
            </div>
        </header>

        <!-- ── Main Content ───────────────────────────────────────── -->
        <main class="mx-auto w-full max-w-lg flex-1 overflow-y-auto pb-32">
            <slot />
        </main>

        <!-- ── Bottom Navigation (same floating bar as the rider app) ── -->
        <nav
            class="fixed inset-x-0 bottom-0 z-40 mx-auto max-w-md px-5 pb-[max(1rem,env(safe-area-inset-bottom))]"
            aria-label="Main"
        >
            <div
                class="grid grid-cols-4 rounded-2xl bg-[#0a2dff] px-2 py-3 shadow-lg"
            >
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    class="flex min-h-11 flex-col items-center justify-center gap-1 text-[11px] font-semibold text-white focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-none"
                    :class="isActive(item.href) ? 'opacity-100' : 'opacity-70'"
                    :aria-current="isActive(item.href) ? 'page' : undefined"
                >
                    <component :is="item.icon" class="h-5 w-5 shrink-0" />
                    {{ item.label }}
                </Link>
            </div>
        </nav>
    </div>
</template>
