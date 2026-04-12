<script setup lang="ts">
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Home, Store, ClipboardList, User, Bell } from 'lucide-vue-next'
import type { AppPageProps } from '@/types'

const page = usePage<AppPageProps>()
const url  = computed(() => page.url)

const unread = computed(() => (page.props as any).unreadNotifications ?? 0)

const navItems = [
    { href: '/user/dashboard',    label: 'Home',    icon: Home },
    { href: '/user/shops',        label: 'Shops',   icon: Store },
    { href: '/user/orders',       label: 'Orders',  icon: ClipboardList },
    { href: '/user/profile',      label: 'Profile', icon: User },
]

function isActive(href: string) {
    if (href === '/user/dashboard') return url.value === '/user/dashboard'
    return url.value.startsWith(href)
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">

        <!-- ── Top Bar ────────────────────────────────────────────── -->
        <header class="sticky top-0 z-40 bg-white border-b border-gray-100 shadow-sm">
            <div class="flex items-center justify-between px-4 py-3 max-w-lg mx-auto">
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-lg overflow-hidden flex items-center justify-center">
                        <img src="/laundryhub.png" alt="LaundryHub" class="h-8 w-8 object-contain" />
                    </div>
                    <span class="font-bold text-gray-900 text-sm">LaundryHub</span>
                </div>

                <div class="flex items-center gap-2">
                    <slot name="header-action" />

                    <!-- Notification bell -->
                    <Link href="/user/notifications" class="relative h-9 w-9 flex items-center justify-center rounded-xl hover:bg-gray-100 transition">
                        <Bell class="h-5 w-5 text-gray-600" />
                        <span
                            v-if="unread > 0"
                            class="absolute -top-0.5 -right-0.5 h-4 min-w-4 px-1 flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold leading-none"
                        >
                            {{ unread > 9 ? '9+' : unread }}
                        </span>
                    </Link>
                </div>
            </div>
        </header>

        <!-- ── Main Content ───────────────────────────────────────── -->
        <main class="flex-1 overflow-y-auto pb-20 max-w-lg mx-auto w-full">
            <slot />
        </main>

        <!-- ── Bottom Navigation ──────────────────────────────────── -->
        <nav class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-gray-100 shadow-lg">
            <div class="flex items-center justify-around max-w-lg mx-auto px-2 py-1">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    class="flex flex-col items-center gap-0.5 px-3 py-2 rounded-xl transition-colors min-w-0"
                    :class="isActive(item.href) ? 'text-blue-600' : 'text-gray-400 hover:text-gray-600'"
                >
                    <component
                        :is="item.icon"
                        class="h-5 w-5 shrink-0 transition-transform"
                        :class="isActive(item.href) ? 'scale-110' : ''"
                    />
                    <span class="text-[10px] font-medium leading-none">{{ item.label }}</span>
                    <span v-if="isActive(item.href)" class="h-1 w-1 rounded-full bg-blue-600 mt-0.5" />
                </Link>
            </div>
        </nav>
    </div>
</template>
