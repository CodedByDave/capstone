<script setup lang="ts">
import type { AppPageProps } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Bell, ClipboardList, Home, Store, User } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

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

            if (n.type === 'payment_request') {
                toast(n.title, {
                    type: 'info',
                    position: 'top-center',
                    autoClose: false,
                    closeOnClick: false,
                    theme: 'colored',
                    icon: '💳',
                    onClick: () => router.visit('/user/notifications'),
                    toastStyle: { cursor: 'pointer' },
                });
            }
        }
    } catch {
        // silently ignore network errors
    }
}

onMounted(() => {
    pollTimer = setInterval(pollNotifications, 20_000);
});

onUnmounted(() => {
    if (pollTimer) clearInterval(pollTimer);
});

const navItems = [
    { href: '/user/dashboard', label: 'Home', icon: Home },
    { href: '/user/shops', label: 'Shops', icon: Store },
    { href: '/user/orders', label: 'Orders', icon: ClipboardList },
    { href: '/user/profile', label: 'Profile', icon: User },
];

function isActive(href: string) {
    if (href === '/user/dashboard') return url.value === '/user/dashboard';
    return url.value.startsWith(href);
}
</script>

<template>
    <div class="flex min-h-screen flex-col bg-gray-50">
        <!-- ── Top Bar ────────────────────────────────────────────── -->
        <header
            class="sticky top-0 z-40 border-b border-gray-100 bg-white shadow-sm"
        >
            <div
                class="mx-auto flex max-w-lg items-center justify-between px-4 py-3"
            >
                <div class="flex items-center gap-2">
                    <div
                        class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-lg"
                    >
                        <img
                            src="/laundryhub.png"
                            alt="LaundryHub"
                            class="h-8 w-8 object-contain"
                        />
                    </div>
                    <span class="text-sm font-bold text-gray-900"
                        >LaundryHub</span
                    >
                </div>

                <div class="flex items-center gap-2">
                    <slot name="header-action" />

                    <!-- Notification bell -->
                    <Link
                        href="/user/notifications"
                        class="relative flex h-9 w-9 items-center justify-center rounded-xl transition hover:bg-gray-100"
                    >
                        <Bell class="h-5 w-5 text-gray-600" />
                        <span
                            v-if="unread > 0"
                            class="absolute -top-0.5 -right-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] leading-none font-bold text-white"
                        >
                            {{ unread > 9 ? '9+' : unread }}
                        </span>
                    </Link>
                </div>
            </div>
        </header>

        <!-- ── Main Content ───────────────────────────────────────── -->
        <main class="mx-auto w-full max-w-lg flex-1 overflow-y-auto pb-20">
            <slot />
        </main>

        <!-- ── Bottom Navigation ──────────────────────────────────── -->
        <nav
            class="fixed right-0 bottom-0 left-0 z-40 border-t border-gray-100 bg-white shadow-lg"
        >
            <div
                class="mx-auto flex max-w-lg items-center justify-around px-2 py-1"
            >
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    class="flex min-w-0 flex-col items-center gap-0.5 rounded-xl px-3 py-2 transition-colors"
                    :class="
                        isActive(item.href)
                            ? 'text-blue-600'
                            : 'text-gray-400 hover:text-gray-600'
                    "
                >
                    <component
                        :is="item.icon"
                        class="h-5 w-5 shrink-0 transition-transform"
                        :class="isActive(item.href) ? 'scale-110' : ''"
                    />
                    <span class="text-[10px] leading-none font-medium">{{
                        item.label
                    }}</span>
                    <span
                        v-if="isActive(item.href)"
                        class="mt-0.5 h-1 w-1 rounded-full bg-blue-600"
                    />
                </Link>
            </div>
        </nav>
    </div>
</template>
