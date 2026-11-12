<script setup lang="ts">
import {
    DataTable,
    DataTableToolbar,
    RowActions,
    StatusPill,
    type DataTableHeader,
} from '@/components/data-table';
import { Button } from '@/components/ui/button';
import type { DataTableOptions } from '@/composables/useDataTable';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import {
    PO_STATUS,
    peso,
    shortDate,
    type PurchaseOrderStatus,
} from '@/lib/purchaseOrders';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ClipboardList, Eye, Plus } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface OrderRow {
    public_id: string;
    po_number: string;
    status: PurchaseOrderStatus;
    total: string;
    expected_date: string | null;
    created_at: string;
    items_count: number;
    supplier: { public_id: string; name: string } | null;
}

const props = defineProps<{
    orders: {
        data: OrderRow[];
        current_page: number;
        per_page: number;
        total: number;
    };
    counts: Partial<Record<PurchaseOrderStatus, number>>;
    filters: { search: string; status: string; per_page: number };
    base: string;
}>();

const { isOwner, can } = usePermissions();
const canCreate = computed(
    () => isOwner.value || can('Inventory Management', 'create'),
);
const inventoryBase = computed(() =>
    isOwner.value ? '/shop/inventory' : '/staff/inventory',
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: inventoryBase.value },
    { title: 'Purchase Orders', href: props.base },
];

const headers: DataTableHeader[] = [
    { text: 'PO number', value: 'po_number', sortable: true },
    { text: 'Supplier', value: 'supplier', sortable: true },
    { text: 'Items', value: 'items_count' },
    { text: 'Total', value: 'total', sortable: true },
    { text: 'Status', value: 'status', sortable: true },
    { text: 'Delivery date', value: 'expected_date', sortable: true },
    { text: 'Created', value: 'created_at', sortable: true },
    { text: 'Actions', value: 'actions', align: 'center', width: 90 },
];

// ─── Table state (search, status, sort and paging all run on the server) ─────

const searchQuery = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status || 'all');
const loading = ref(false);
const query = new URLSearchParams(window.location.search);
const options = ref<DataTableOptions>({
    page: props.orders.current_page,
    rowsPerPage: props.orders.per_page,
    sortBy: query.get('sort_by') || undefined,
    sortType: query.get('sort_direction') === 'desc' ? 'desc' : 'asc',
});

const allCount = computed(() =>
    Object.values(props.counts).reduce((sum, n) => sum + (n ?? 0), 0),
);
const statusOptions = computed(() => [
    { value: 'all', label: 'All', count: allCount.value },
    ...(Object.keys(PO_STATUS) as PurchaseOrderStatus[]).map((status) => ({
        value: status,
        label: PO_STATUS[status].label,
        count: props.counts[status] ?? 0,
    })),
]);

function tableQuery() {
    return {
        search: searchQuery.value || undefined,
        status: statusFilter.value === 'all' ? undefined : statusFilter.value,
        sort_by: options.value.sortBy,
        sort_direction: options.value.sortBy
            ? options.value.sortType
            : undefined,
        page: options.value.page,
        per_page: options.value.rowsPerPage,
    };
}

function applyFilters() {
    router.get(props.base, tableQuery(), {
        only: ['orders', 'counts', 'filters'],
        preserveState: true,
        preserveScroll: true,
        replace: true,
        showProgress: false,
        onStart: () => (loading.value = true),
        onFinish: () => (loading.value = false),
    });
}

// Any new search, filter or sort starts again from page 1.
function filterTable() {
    if (options.value.page !== 1) {
        options.value.page = 1;
        return;
    }
    applyFilters();
}

function setStatus(value: string) {
    statusFilter.value = value;
    filterTable();
}

function resetFilters() {
    searchQuery.value = '';
    statusFilter.value = 'all';
    const changed =
        options.value.page !== 1 || options.value.sortBy !== undefined;
    options.value = { ...options.value, page: 1, sortBy: undefined };
    if (!changed) applyFilters();
}

watch(
    () => [options.value.sortBy, options.value.sortType],
    () => filterTable(),
);
watch(
    () => [options.value.page, options.value.rowsPerPage],
    () => applyFilters(),
);

const open = (order: OrderRow) =>
    router.visit(`${props.base}/${order.public_id}`);
</script>

<template>
    <Head title="Purchase Orders" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Purchase Orders">
        <div class="space-y-6 px-6">
            <DataTable
                v-model:options="options"
                :headers="headers"
                :items="orders.data"
                item-key="public_id"
                :total="orders.total"
                :loading="loading"
                :selectable="false"
                noun="purchase orders"
                :empty-icon="ClipboardList"
                :min-width="960"
                @reset="resetFilters"
            >
                <template #toolbar>
                    <div class="space-y-3">
                        <div
                            class="flex flex-wrap items-center justify-between gap-2"
                        >
                            <h2 class="flex items-center gap-2 font-semibold">
                                <ClipboardList
                                    class="h-4 w-4 text-muted-foreground"
                                />
                                Purchase Orders
                            </h2>
                            <Button
                                v-if="canCreate"
                                size="sm"
                                @click="router.visit(`${base}/create`)"
                            >
                                <Plus class="mr-1.5 h-4 w-4" /> New order
                            </Button>
                        </div>
                        <DataTableToolbar
                            v-model:search="searchQuery"
                            :status="statusFilter"
                            :statuses="statusOptions"
                            search-placeholder="Search PO number or supplier"
                            search-label="Search purchase orders"
                            status-label="Filter by order status"
                            @update:status="setStatus"
                            @submit="filterTable"
                        />
                    </div>
                </template>

                <template #item-po_number="order">
                    <button
                        type="button"
                        class="text-left font-medium hover:underline focus-visible:underline focus-visible:outline-none"
                        @click="open(order)"
                    >
                        {{ order.po_number }}
                    </button>
                </template>

                <template #item-supplier="order">
                    {{ order.supplier?.name ?? '—' }}
                </template>

                <template #item-items_count="order">
                    <span class="text-muted-foreground">
                        {{ order.items_count }}
                        {{ order.items_count === 1 ? 'item' : 'items' }}
                    </span>
                </template>

                <template #item-total="order">
                    <span class="font-medium tabular-nums">{{
                        peso(order.total)
                    }}</span>
                </template>

                <template #item-status="order">
                    <StatusPill
                        :tone="
                            PO_STATUS[order.status as PurchaseOrderStatus].tone
                        "
                    >
                        {{
                            PO_STATUS[order.status as PurchaseOrderStatus].label
                        }}
                    </StatusPill>
                </template>

                <template #item-expected_date="order">
                    <span class="whitespace-nowrap text-muted-foreground">{{
                        shortDate(order.expected_date)
                    }}</span>
                </template>

                <template #item-created_at="order">
                    <span class="whitespace-nowrap text-muted-foreground">{{
                        shortDate(order.created_at)
                    }}</span>
                </template>

                <template #item-actions="order">
                    <RowActions
                        :label="order.po_number"
                        :actions="[
                            {
                                label: 'View',
                                icon: Eye,
                                onSelect: () => open(order as OrderRow),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>
    </ShopLayout>
</template>
