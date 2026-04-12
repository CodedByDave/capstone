<script setup lang="ts">
import { computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import UserLayout from '@/layouts/user/UserLayout.vue'
import {
    Bell, CheckCircle2, Clock, Package, CreditCard, WashingMachine, Inbox, Truck,
} from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface NotificationData {
    title: string
    body: string
    order_id?: number
    order_number?: string
    type: 'placed' | 'status_changed' | 'payment_updated' | 'delivery_updated'
    status?: string
    payment_status?: string
    delivery_id?: number
    delivery_status?: string
}

interface Notification {
    id: string
    data: NotificationData
    read_at: string | null
    created_at: string
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    notifications: Notification[]
}>()

// ─── Computed ─────────────────────────────────────────────────────────────────

const hasUnread = computed(() => props.notifications.some(n => !n.read_at))

// ─── Helpers ──────────────────────────────────────────────────────────────────

function notifIcon(type: string) {
    if (type === 'placed')           return Package
    if (type === 'status_changed')   return WashingMachine
    if (type === 'payment_updated')  return CreditCard
    if (type === 'delivery_updated') return Truck
    return Bell
}

function notifColor(n: Notification): string {
    const type           = n.data.type
    const status         = n.data.status
    const deliveryStatus = n.data.delivery_status
    if (type === 'placed')                       return 'bg-blue-100 text-blue-600'
    if (type === 'payment_updated')              return 'bg-green-100 text-green-600'
    if (type === 'delivery_updated') {
        if (deliveryStatus === 'delivered')      return 'bg-emerald-100 text-emerald-600'
        if (deliveryStatus === 'picked_up')      return 'bg-amber-100 text-amber-600'
        if (deliveryStatus === 'failed')         return 'bg-red-100 text-red-600'
        return 'bg-indigo-100 text-indigo-600'
    }
    if (status === 'completed')                  return 'bg-emerald-100 text-emerald-600'
    if (status === 'in_progress')                return 'bg-amber-100 text-amber-600'
    return 'bg-gray-100 text-gray-500'
}

function handleNotifClick(n: Notification) {
    if (n.data.order_id) {
        router.visit(`/user/orders/${n.data.order_id}`)
    }
}
</script>

<template>
    <Head title="Notifications" />
    <UserLayout>
        <div class="px-4 pt-4 pb-6">

            <!-- ── Header ──────────────────────────────────────── -->
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <Bell class="h-5 w-5 text-blue-600" />
                    Notifications
                </h1>
            </div>

            <!-- ── Empty state ─────────────────────────────────── -->
            <div v-if="notifications.length === 0" class="flex flex-col items-center py-20 text-gray-400">
                <Inbox class="h-14 w-14 mb-4 opacity-20" />
                <p class="text-base font-semibold text-gray-500">No notifications yet</p>
                <p class="text-sm mt-1 text-center">You'll see order updates here once you place an order.</p>
            </div>

            <!-- ── Notification list ────────────────────────────── -->
            <div v-else class="space-y-2">
                <button
                    v-for="notif in notifications"
                    :key="notif.id"
                    class="w-full text-left flex items-start gap-3 p-4 rounded-2xl border transition-all active:scale-[0.99]"
                    :class="notif.read_at
                        ? 'bg-white border-gray-100 shadow-sm'
                        : 'bg-blue-50 border-blue-200 shadow-sm'"
                    @click="handleNotifClick(notif)"
                >
                    <!-- Icon -->
                    <div :class="`h-10 w-10 rounded-xl flex items-center justify-center shrink-0 ${notifColor(notif)}`">
                        <component :is="notifIcon(notif.data.type)" class="h-5 w-5" />
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-semibold text-gray-800 leading-tight">
                                {{ notif.data.title }}
                            </p>
                            <!-- Unread dot -->
                            <span v-if="!notif.read_at" class="h-2 w-2 rounded-full bg-blue-500 shrink-0 mt-1" />
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">{{ notif.data.body }}</p>
                        <div class="flex items-center gap-1 mt-1.5 text-[11px] text-gray-400">
                            <Clock class="h-3 w-3 shrink-0" />
                            {{ notif.created_at }}
                        </div>
                    </div>
                </button>
            </div>

        </div>
    </UserLayout>
</template>
