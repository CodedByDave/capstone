<script setup lang="ts">
import UserLayout from '@/layouts/user/UserLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import {
    Bell,
    ChevronDown,
    ChevronUp,
    Clock,
    CreditCard,
    Inbox,
    Package,
    Truck,
    WashingMachine,
} from 'lucide-vue-next';
import { ref } from 'vue';

// ─── Types ────────────────────────────────────────────────────────────────────

interface NotificationData {
    title: string;
    body: string;
    order_id?: number;
    order_number?: string;
    type:
        | 'placed'
        | 'status_changed'
        | 'payment_updated'
        | 'payment_request'
        | 'delivery_updated';
    status?: string;
    payment_status?: string;
    amount?: string;
    qr_url?: string | null;
    delivery_id?: number;
    delivery_status?: string;
}

interface Notification {
    id: string;
    data: NotificationData;
    read_at: string | null;
    created_at: string;
}

// ─── Props ────────────────────────────────────────────────────────────────────

defineProps<{
    notifications: Notification[];
}>();

// ─── Expanded QR state ────────────────────────────────────────────────────────

const expandedQr = ref<Set<string>>(new Set());

function toggleQr(id: string) {
    if (expandedQr.value.has(id)) {
        expandedQr.value.delete(id);
    } else {
        expandedQr.value.add(id);
    }
    // trigger reactivity
    expandedQr.value = new Set(expandedQr.value);
}

// ─── Computed ─────────────────────────────────────────────────────────────────

// ─── Helpers ──────────────────────────────────────────────────────────────────

function notifIcon(type: string) {
    if (type === 'placed') return Package;
    if (type === 'status_changed') return WashingMachine;
    if (type === 'payment_updated') return CreditCard;
    if (type === 'payment_request') return CreditCard;
    if (type === 'delivery_updated') return Truck;
    return Bell;
}

function notifColor(n: Notification): string {
    const type = n.data.type;
    const status = n.data.status;
    const deliveryStatus = n.data.delivery_status;
    if (type === 'placed') return 'bg-blue-100 text-blue-600';
    if (type === 'payment_updated') return 'bg-green-100 text-green-600';
    if (type === 'payment_request') return 'bg-violet-100 text-violet-600';
    if (type === 'delivery_updated') {
        if (deliveryStatus === 'delivered')
            return 'bg-emerald-100 text-emerald-600';
        if (deliveryStatus === 'picked_up')
            return 'bg-amber-100 text-amber-600';
        if (deliveryStatus === 'failed') return 'bg-red-100 text-red-600';
        return 'bg-indigo-100 text-indigo-600';
    }
    if (status === 'completed') return 'bg-emerald-100 text-emerald-600';
    if (status === 'in_progress') return 'bg-amber-100 text-amber-600';
    return 'bg-gray-100 text-gray-500';
}

function handleNotifClick(n: Notification) {
    if (n.data.type === 'payment_request') {
        toggleQr(n.id);
        return;
    }
    if (n.data.order_id) {
        router.visit(`/user/orders/${n.data.order_id}`);
    }
}
</script>

<template>
    <Head title="Notifications" />
    <UserLayout>
        <div class="px-4 pt-4 pb-6">
            <!-- ── Header ──────────────────────────────────────── -->
            <div class="mb-4 flex items-center justify-between">
                <h1
                    class="flex items-center gap-2 text-lg font-bold text-gray-900"
                >
                    <Bell class="h-5 w-5 text-blue-600" />
                    Notifications
                </h1>
            </div>

            <!-- ── Empty state ─────────────────────────────────── -->
            <div
                v-if="notifications.length === 0"
                class="flex flex-col items-center py-20 text-gray-400"
            >
                <Inbox class="mb-4 h-14 w-14 opacity-20" />
                <p class="text-base font-semibold text-gray-500">
                    No notifications yet
                </p>
                <p class="mt-1 text-center text-sm">
                    You'll see order updates here once you place an order.
                </p>
            </div>

            <!-- ── Notification list ────────────────────────────── -->
            <div v-else class="space-y-2">
                <button
                    v-for="notif in notifications"
                    :key="notif.id"
                    class="flex w-full items-start gap-3 rounded-2xl border p-4 text-left transition-all active:scale-[0.99]"
                    :class="
                        notif.read_at
                            ? 'border-gray-100 bg-white shadow-sm'
                            : 'border-blue-200 bg-blue-50 shadow-sm'
                    "
                    @click="handleNotifClick(notif)"
                >
                    <!-- Icon -->
                    <div
                        :class="`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${notifColor(notif)}`"
                    >
                        <component
                            :is="notifIcon(notif.data.type)"
                            class="h-5 w-5"
                        />
                    </div>

                    <!-- Content -->
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <p
                                class="text-sm leading-tight font-semibold text-gray-800"
                            >
                                {{ notif.data.title }}
                            </p>
                            <!-- Unread dot -->
                            <span
                                v-if="!notif.read_at"
                                class="mt-1 h-2 w-2 shrink-0 rounded-full bg-blue-500"
                            />
                        </div>
                        <p class="mt-0.5 text-xs leading-relaxed text-gray-500">
                            {{ notif.data.body }}
                        </p>

                        <!-- Payment request: tap to expand QR -->
                        <template v-if="notif.data.type === 'payment_request'">
                            <!-- Collapsed hint -->
                            <div
                                v-if="!expandedQr.has(notif.id)"
                                class="mt-2 flex items-center gap-1.5 text-xs font-medium text-violet-600"
                            >
                                <CreditCard class="h-3.5 w-3.5 shrink-0" />
                                Tap to view payment QR
                                <ChevronDown class="ml-auto h-3.5 w-3.5" />
                            </div>

                            <!-- Expanded QR -->
                            <div
                                v-else
                                class="mt-2 flex flex-col items-center gap-2 rounded-xl border border-violet-200 bg-violet-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold text-violet-700"
                                >
                                    Scan to pay
                                </p>
                                <img
                                    v-if="notif.data.qr_url"
                                    :src="notif.data.qr_url"
                                    alt="Payment QR"
                                    class="h-48 w-48 rounded-xl border border-violet-200 bg-white object-contain p-2"
                                />
                                <div
                                    v-else
                                    class="text-center text-xs text-amber-700"
                                >
                                    QR not available. Contact the shop directly.
                                </div>
                                <p class="text-base font-bold text-violet-900">
                                    ₱{{
                                        Number(
                                            notif.data.amount,
                                        ).toLocaleString('en-PH', {
                                            minimumFractionDigits: 2,
                                        })
                                    }}
                                </p>
                                <p class="text-[11px] text-violet-500">
                                    Open your GCash / Maya app and scan
                                </p>
                                <div
                                    class="mt-1 flex items-center gap-1 text-xs text-violet-500"
                                >
                                    <ChevronUp class="h-3.5 w-3.5" /> Tap to
                                    collapse
                                </div>
                            </div>
                        </template>

                        <div
                            class="mt-1.5 flex items-center gap-1 text-[11px] text-gray-400"
                        >
                            <Clock class="h-3 w-3 shrink-0" />
                            {{ notif.created_at }}
                        </div>
                    </div>
                </button>
            </div>
        </div>
    </UserLayout>
</template>
