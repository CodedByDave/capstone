<script setup lang="ts">
import UserLayout from '@/layouts/user/UserLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import {
    Bell,
    CreditCard,
    Inbox,
    Package,
    Scale,
    Truck,
    WashingMachine,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

// ─── Types ────────────────────────────────────────────────────────────────────

interface NotificationData {
    title: string;
    body: string;
    order_id?: number;
    payment_public_id?: string;
    order_number?: string;
    type: 'placed' | 'status_changed' | 'payment_updated' | 'delivery_updated';
    status?: string;
    payment_status?: string;
    amount?: string;
    delivery_id?: number;
    delivery_status?: string;
}

interface Notification {
    id: string;
    data: NotificationData;
    read_at: string | null;
    created_at: string;
    created_at_iso: string;
}

type TabKey = 'all' | 'unread' | 'orders' | 'payments' | 'delivery';

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    notifications: Notification[];
}>();

// Ids read in this visit, so the unread dot clears before the server answers.
const locallyRead = ref<Set<string>>(new Set());

function isUnread(n: Notification) {
    return !n.read_at && !locallyRead.value.has(n.id);
}

// ─── Tabs ─────────────────────────────────────────────────────────────────────

const activeTab = ref<TabKey>('all');

const matchers: Record<TabKey, (n: Notification) => boolean> = {
    all: () => true,
    unread: (n) => isUnread(n),
    orders: (n) => n.data.type === 'placed' || n.data.type === 'status_changed',
    payments: (n) => n.data.type === 'payment_updated',
    delivery: (n) => n.data.type === 'delivery_updated',
};

const tabs = computed(() =>
    (
        [
            ['all', 'All'],
            ['unread', 'Unread'],
            ['orders', 'Orders'],
            ['payments', 'Payments'],
            ['delivery', 'Delivery'],
        ] as [TabKey, string][]
    ).map(([key, label]) => ({
        key,
        label,
        count: props.notifications.filter(matchers[key]).length,
    })),
);

const unreadCount = computed(
    () => props.notifications.filter((n) => isUnread(n)).length,
);

const visible = computed(() =>
    props.notifications.filter(matchers[activeTab.value]),
);

// ─── Grouping ─────────────────────────────────────────────────────────────────

function dayKey(iso: string) {
    const d = new Date(iso);
    return new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime();
}

function groupLabel(iso: string) {
    const today = dayKey(new Date().toISOString());
    const day = dayKey(iso);
    const dayMs = 24 * 60 * 60 * 1000;
    if (day >= today) return 'Today';
    if (day >= today - dayMs) return 'Yesterday';
    return 'Earlier';
}

const groups = computed(() => {
    const order: string[] = [];
    const map = new Map<string, Notification[]>();
    for (const n of visible.value) {
        const label = groupLabel(n.created_at_iso);
        if (!map.has(label)) {
            map.set(label, []);
            order.push(label);
        }
        map.get(label)!.push(n);
    }
    return order.map((label) => ({ label, items: map.get(label)! }));
});

// ─── Helpers ──────────────────────────────────────────────────────────────────

function notifIcon(n: Notification) {
    const { type, status } = n.data;
    if (type === 'placed') return Package;
    if (type === 'payment_updated') return CreditCard;
    if (type === 'delivery_updated') return Truck;
    if (type === 'status_changed') {
        return /weigh/i.test(n.data.title) ? Scale : WashingMachine;
    }
    return status ? WashingMachine : Bell;
}

function open(n: Notification) {
    const destination = n.data.payment_public_id
        ? `/user/orders/${n.data.payment_public_id}`
        : n.data.order_id
          ? '/user/orders'
          : null;

    if (!isUnread(n)) {
        if (destination) router.visit(destination);
        return;
    }

    locallyRead.value.add(n.id);
    router.post(
        `/user/notifications/${n.id}/read`,
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                if (destination) router.visit(destination);
            },
        },
    );
}

function markAllRead() {
    for (const n of props.notifications) locallyRead.value.add(n.id);
    router.post('/user/notifications/read-all', {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Notifications" />
    <UserLayout>
        <div class="px-5 pt-6">
            <!-- ── Header ──────────────────────────────────────── -->
            <div class="flex items-end justify-between gap-3">
                <div>
                    <h1
                        class="font-display text-[28px] leading-9 font-bold text-slate-900"
                    >
                        Notifications
                    </h1>
                    <p class="mt-0.5 text-xs text-slate-500">
                        {{ unreadCount }} unread
                    </p>
                </div>
                <button
                    v-if="unreadCount > 0"
                    type="button"
                    class="min-h-11 text-xs font-semibold text-blue-600 hover:underline"
                    @click="markAllRead"
                >
                    Mark all read
                </button>
            </div>

            <!-- ── Tabs ────────────────────────────────────────── -->
            <div
                class="-mx-5 mt-4 overflow-x-auto border-b border-slate-100 px-5 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                role="tablist"
                aria-label="Filter notifications"
            >
                <div class="flex min-w-max gap-5">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        type="button"
                        role="tab"
                        :aria-selected="activeTab === tab.key"
                        class="relative min-h-11 pb-1 text-sm font-medium whitespace-nowrap transition-colors"
                        :class="
                            activeTab === tab.key
                                ? 'text-slate-900'
                                : 'text-slate-500 hover:text-slate-700'
                        "
                        @click="activeTab = tab.key"
                    >
                        {{ tab.label }}
                        <span
                            class="ml-1 text-xs"
                            :class="
                                activeTab === tab.key
                                    ? 'text-blue-600'
                                    : 'text-slate-400'
                            "
                            >{{ tab.count }}</span
                        >
                        <span
                            v-if="activeTab === tab.key"
                            class="absolute inset-x-0 -bottom-px h-0.5 rounded-full bg-blue-600"
                        />
                    </button>
                </div>
            </div>
        </div>

        <!-- ── Empty state ─────────────────────────────────────── -->
        <div
            v-if="visible.length === 0"
            class="flex flex-col items-center px-5 py-20 text-center"
        >
            <Inbox class="mb-3 h-10 w-10 text-slate-300" aria-hidden="true" />
            <p class="font-display text-sm font-semibold text-slate-900">
                {{
                    notifications.length === 0
                        ? 'No notifications yet'
                        : 'Nothing in this tab'
                }}
            </p>
            <p class="mt-1 max-w-60 text-xs leading-5 text-slate-500">
                {{
                    notifications.length === 0
                        ? "You'll see order updates here once you place an order."
                        : 'Try another tab to see your other updates.'
                }}
            </p>
        </div>

        <!-- ── Notification list ───────────────────────────────── -->
        <div v-else>
            <section v-for="group in groups" :key="group.label">
                <h2
                    class="px-5 pt-5 pb-2 text-[11px] font-medium tracking-[0.12em] text-slate-500 uppercase"
                >
                    {{ group.label }}
                </h2>
                <ul class="divide-y divide-slate-100">
                    <li v-for="notif in group.items" :key="notif.id">
                        <button
                            type="button"
                            class="flex w-full items-start gap-3 px-5 py-4 text-left transition hover:bg-slate-50"
                            @click="open(notif)"
                        >
                            <span
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                                :class="
                                    isUnread(notif)
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'bg-slate-50 text-slate-500'
                                "
                            >
                                <component
                                    :is="notifIcon(notif)"
                                    class="h-[18px] w-[18px]"
                                />
                            </span>

                            <span class="min-w-0 flex-1">
                                <span
                                    class="block text-sm leading-5 text-slate-900"
                                    :class="
                                        isUnread(notif)
                                            ? 'font-semibold'
                                            : 'font-medium'
                                    "
                                >
                                    {{ notif.data.title }}
                                </span>
                                <span
                                    class="mt-0.5 block text-[13px] leading-5 text-slate-600"
                                >
                                    {{ notif.data.body }}
                                </span>
                                <span class="mt-1 block text-xs text-slate-400">
                                    {{ notif.created_at }}
                                </span>
                            </span>

                            <span
                                v-if="isUnread(notif)"
                                class="mt-2 h-2 w-2 shrink-0 rounded-full bg-blue-600"
                                aria-label="Unread"
                            />
                        </button>
                    </li>
                </ul>
            </section>
        </div>
    </UserLayout>
</template>
