<script setup lang="ts">
import {
    DataTable,
    RowActions,
    StatusPill,
    statusLabel,
    type DataTableHeader,
    type StatusTone,
} from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { usePaginatorTable } from '@/composables/useDataTable';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import {
    AlertTriangle,
    BellOff,
    CheckCircle2,
    Eye,
    Loader2,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface AlertItem {
    id: number;
    quantity_at_alert: number;
    min_stock_at_alert: number;
    status: 'unread' | 'read' | 'resolved' | 'dismissed';
    resolved_at: string | null;
    created_at: string;
    inventory: {
        id: number;
        name: string;
        sku: string;
        unit: string;
        quantity: number;
    } | null;
}

interface Paginator {
    data: AlertItem[];
    current_page: number;
    last_page: number;
    per_page?: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    alerts: Paginator;
    unreadCount: number;
}>();

const page = usePage<AppPageProps>();
const isOwner = computed(() => page.props.auth.user.role === 'owner');
const alertsBase = computed(() =>
    isOwner.value ? '/shop/inventory/alerts' : '/staff/inventory/alerts',
);
const inventoryBase = computed(() =>
    isOwner.value ? '/shop/inventory' : '/staff/inventory',
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: inventoryBase.value },
    { title: 'Stock Alerts', href: alertsBase.value },
];

const alertList = ref<AlertItem[]>([...props.alerts.data]);
const updatingId = ref<number | null>(null);
const markingAll = ref(false);

watch(
    () => props.alerts.data,
    (data) => {
        alertList.value = [...data];
    },
);

const { options, loading } = usePaginatorTable(
    () => props.alerts,
    ['alerts', 'unreadCount'],
);

const headers: DataTableHeader[] = [
    { text: 'Item', value: 'item' },
    { text: 'Stock at alert', value: 'quantity_at_alert' },
    { text: 'Current stock', value: 'current' },
    { text: 'Status', value: 'status' },
    { text: 'Triggered', value: 'created_at' },
    { text: 'Actions', value: 'actions', align: 'center', width: 140 },
];

async function updateAlert(
    id: number,
    status: 'read' | 'resolved' | 'dismissed',
) {
    updatingId.value = id;
    try {
        await axios.patch(`${alertsBase.value}/${id}`, { status });
        const alert = alertList.value.find((a) => a.id === id);
        if (alert) alert.status = status;
    } catch {
        /* silent */
    } finally {
        updatingId.value = null;
    }
}

async function markAllRead() {
    markingAll.value = true;
    try {
        await axios.post(`${alertsBase.value}/mark-all-read`);
        alertList.value.forEach((a) => {
            if (a.status === 'unread') a.status = 'read';
        });
    } catch {
        /* silent */
    } finally {
        markingAll.value = false;
    }
}

function formatDate(dt: string) {
    return new Date(dt).toLocaleString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

const statusTones: Record<string, StatusTone> = {
    unread: 'danger',
    read: 'neutral',
    resolved: 'success',
    dismissed: 'neutral',
};
</script>

<template>
    <Head title="Stock Alerts" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Low Stock Alerts">
        <div class="space-y-6 px-6">
            <!-- Summary -->
            <div
                class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3"
            >
                <AlertTriangle class="h-5 w-5 shrink-0 text-amber-500" />
                <div class="flex-1">
                    <p class="text-sm font-medium text-amber-800">
                        {{ unreadCount }} unread alert{{
                            unreadCount !== 1 ? 's' : ''
                        }}
                    </p>
                </div>
                <Button
                    v-if="unreadCount > 0"
                    size="sm"
                    variant="outline"
                    class="border-amber-300 text-amber-700 hover:bg-amber-100"
                    :disabled="markingAll"
                    @click="markAllRead"
                >
                    <Loader2
                        v-if="markingAll"
                        class="mr-1.5 h-3.5 w-3.5 animate-spin"
                    />
                    Mark all as read
                </Button>
            </div>

            <DataTable
                v-model:options="options"
                :headers="headers"
                :items="alertList"
                :total="alerts.total"
                :loading="loading"
                noun="alerts"
                :empty-icon="CheckCircle2"
                empty-message="No alerts. All stock levels are healthy!"
                :resettable="false"
                :row-class="
                    (alert) =>
                        alert.status === 'dismissed' ? 'opacity-50' : ''
                "
                :min-width="860"
                hide-rows-per-page
            >
                <template #toolbar>
                    <h2 class="flex items-center gap-2 font-semibold">
                        <AlertTriangle class="h-4 w-4 text-amber-500" />
                        Alert History
                    </h2>
                </template>

                <template #item-item="alert">
                    <p class="font-medium">
                        {{ alert.inventory?.name ?? 'Deleted item' }}
                    </p>
                    <p class="font-mono text-xs text-muted-foreground">
                        {{ alert.inventory?.sku }}
                    </p>
                </template>

                <template #item-quantity_at_alert="alert">
                    <span class="whitespace-nowrap">
                        <span class="font-medium text-red-600">{{
                            alert.quantity_at_alert
                        }}</span>
                        <span class="ml-1 text-xs text-muted-foreground"
                            >/ min {{ alert.min_stock_at_alert }}</span
                        >
                    </span>
                </template>

                <template #item-current="alert">
                    <span
                        v-if="alert.inventory"
                        class="whitespace-nowrap"
                        :class="
                            alert.inventory.quantity <= alert.min_stock_at_alert
                                ? 'font-medium text-red-600'
                                : 'font-medium text-green-600'
                        "
                    >
                        {{ alert.inventory.quantity }}
                        {{ alert.inventory.unit }}
                    </span>
                    <span v-else class="text-muted-foreground">—</span>
                </template>

                <template #item-status="alert">
                    <StatusPill :tone="statusTones[alert.status]">
                        {{ statusLabel(alert.status) }}
                    </StatusPill>
                </template>

                <template #item-created_at="alert">
                    <span
                        class="text-xs whitespace-nowrap text-muted-foreground"
                    >
                        {{ formatDate(alert.created_at) }}
                    </span>
                </template>

                <template #item-actions="alert">
                    <RowActions
                        :label="alert.inventory?.name ?? 'deleted item'"
                        :actions="[
                            {
                                label: 'View item',
                                icon: Eye,
                                show: Boolean(alert.inventory),
                                onSelect: () =>
                                    router.visit(
                                        `${inventoryBase}/${alert.inventory?.id}`,
                                    ),
                            },
                            {
                                label: 'Mark resolved',
                                icon:
                                    updatingId === alert.id
                                        ? Loader2
                                        : CheckCircle2,
                                disabled: updatingId === alert.id,
                                show: ['unread', 'read'].includes(alert.status),
                                onSelect: () =>
                                    updateAlert(alert.id, 'resolved'),
                            },
                            {
                                label: 'Dismiss',
                                icon: BellOff,
                                disabled: updatingId === alert.id,
                                show: alert.status !== 'dismissed',
                                onSelect: () =>
                                    updateAlert(alert.id, 'dismissed'),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>
    </ShopLayout>
</template>
