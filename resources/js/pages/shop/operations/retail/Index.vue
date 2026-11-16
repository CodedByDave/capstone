<script setup lang="ts">
import {
    DataTable,
    DataTableToolbar,
    IdentityCell,
    KpiTiles,
    RowActions,
    StatusPill,
    statusTone,
    type DataTableHeader,
} from '@/components/data-table';
import PageHeader from '@/components/shop/PageHeader.vue';
import { Button } from '@/components/ui/button';
import type { DataTableOptions } from '@/composables/useDataTable';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import {
    CheckCircle,
    Clock,
    Eye,
    Globe,
    Loader,
    PackageCheck,
    PackageOpen,
    Plus,
    Receipt,
    ShoppingBag,
    Store,
    Wallet,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

// ─── Types ─────────────────────────────────────────

interface Sale {
    payment_public_id: string;
    order_number: string;
    customer_name: string;
    customer_phone: string | null;
    /** 'shop': recorded at the counter. 'online': placed by a customer in the app. */
    source: 'shop' | 'online';
    status: string;
    payment_status: string;
    total_amount: number;
    items: string[];
    created_at: string;
}

interface Paginator {
    data: Sale[];
    current_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    sales: Paginator;
    summary: {
        sales_today: number;
        revenue_today: number;
        open_online: number;
        by_status: Record<string, number>;
    };
    filters: Record<string, string | number | null | undefined>;
}>();

// ─── Toast ─────────────────────────────────────────

const page = usePage<AppPageProps>();

watch(
    () => page.props.toast as any,
    (t) => {
        if (!t) return;
        if (t.type === 'success') toast.success(t.message);
        if (t.type === 'error') toast.error(t.message);
    },
    { immediate: true },
);

// ─── Permissions and URLs ──────────────────────────

const isOwner = computed(() => page.props.auth.user.role === 'owner');
const { can } = usePermissions();
const canCreate = computed(() => can('Operations', 'create'));
const canManage = computed(() => can('Operations', 'manage'));

const retailBase = computed(() =>
    isOwner.value ? '/shop/operations/retail' : '/staff/operations/retail',
);
const orderBase = computed(() =>
    isOwner.value ? '/shop/operations/orders' : '/staff/operations/orders',
);

// ─── Filters and server options ────────────────────

const search = ref(String(props.filters.search ?? ''));
const status = ref(String(props.filters.status ?? ''));
const source = ref(String(props.filters.source ?? ''));
const tableLoading = ref(false);

const serverOptions = ref<DataTableOptions>({
    page: props.sales.current_page,
    rowsPerPage: props.sales.per_page,
    sortBy: String(props.filters.sort_by ?? 'created_at'),
    sortType: props.filters.sort_direction === 'asc' ? 'asc' : 'desc',
});

function applyFilters() {
    router.get(
        retailBase.value,
        {
            search: search.value || undefined,
            status: status.value || undefined,
            source: source.value || undefined,
            sort_by: serverOptions.value.sortBy,
            sort_direction: serverOptions.value.sortType,
            page: serverOptions.value.page,
            per_page: serverOptions.value.rowsPerPage,
        },
        {
            only: ['sales', 'summary', 'filters'],
            preserveState: true,
            preserveScroll: true,
            replace: true,
            showProgress: false,
            onStart: () => {
                tableLoading.value = true;
            },
            onFinish: () => {
                tableLoading.value = false;
            },
        },
    );
}

// A changed filter always starts again from the first page.
const filterTable = debounce(() => {
    if (serverOptions.value.page !== 1) {
        serverOptions.value.page = 1;
        return;
    }

    applyFilters();
}, 300);

watch([search, status, source], filterTable);

watch(
    [
        () => serverOptions.value.page,
        () => serverOptions.value.rowsPerPage,
        () => serverOptions.value.sortBy,
        () => serverOptions.value.sortType,
    ],
    () => applyFilters(),
);

const hasActiveFilters = computed(
    () => !!(search.value || status.value || source.value),
);

function resetTable() {
    search.value = '';
    status.value = '';
    source.value = '';
    serverOptions.value = {
        ...serverOptions.value,
        page: 1,
        sortBy: 'created_at',
        sortType: 'desc',
    };
}

const totalSales = computed(() =>
    Object.values(props.summary.by_status).reduce((a, b) => a + b, 0),
);

const statusOptions = computed(() => [
    { value: '', label: 'All', count: totalSales.value },
    ...['pending', 'in_progress', 'completed', 'picked_up', 'cancelled'].map(
        (value) => ({
            value,
            label: formatLabel(value),
            count: props.summary.by_status[value] ?? 0,
        }),
    ),
]);

// ─── Table ─────────────────────────────────────────

const headers: DataTableHeader[] = [
    { text: 'Sale #', value: 'order_number', sortable: true },
    { text: 'Customer', value: 'customer', sortable: true },
    { text: 'Products', value: 'items' },
    { text: 'Source', value: 'source' },
    { text: 'Status', value: 'status', sortable: true },
    { text: 'Payment', value: 'payment_status', sortable: true },
    { text: 'Total', value: 'total', sortable: true },
    { text: 'Date', value: 'created_at', sortable: true },
    { text: 'Actions', value: 'actions', width: 100, align: 'center' },
];

const tableItems = computed(() =>
    props.sales.data.map((sale) => ({
        ...sale,
        customer: sale.customer_name,
        total: sale.total_amount,
        actions: '',
    })),
);

// ─── Helpers ───────────────────────────────────────

const formatCurrency = (value: string | number) =>
    `₱${Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;

const formatDate = (date: string) =>
    new Date(date).toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });

const formatLabel = (s: string) =>
    s.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());

const statusIcon = (s: string) =>
    ({
        pending: Clock,
        in_progress: Loader,
        completed: CheckCircle,
        picked_up: PackageCheck,
    })[s] ?? PackageOpen;

// A collected sale is finished and gone, so it gets the quiet grey tone.
const saleStatusTone = (s: string) =>
    s === 'picked_up' ? 'neutral' : statusTone(s);

// ─── Status actions (online product orders) ────────

// An online order is put together, marked ready, then collected. Counter sales are saved collected.
const canMarkReady = (sale: Sale) =>
    sale.source === 'online' && ['pending', 'in_progress'].includes(sale.status);
const canMarkCollected = (sale: Sale) =>
    sale.source === 'online' && sale.status === 'completed';

function changeStatus(sale: Sale, next: 'completed' | 'picked_up') {
    router.patch(
        `${orderBase.value}/${sale.payment_public_id}/status`,
        { status: next },
        {
            preserveScroll: true,
            onError: (errors) =>
                toast.error(
                    Object.values(errors)[0] ?? 'Could not update the order.',
                ),
        },
    );
}
</script>

<template>
    <Head title="Retail Products" />

    <ShopLayout title="Retail Products">
        <div class="space-y-6 px-6">
            <PageHeader
                title="Retail Products"
                description="Sell products without a laundry order, at the counter or from customers' online orders."
            >
                <Button
                    v-if="canCreate"
                    class="h-11"
                    @click="router.visit(`${retailBase}/create`)"
                >
                    <Plus class="mr-2 h-4 w-4" aria-hidden="true" />
                    Record Sale
                </Button>
            </PageHeader>

            <KpiTiles
                :tiles="[
                    {
                        label: 'Sales Today',
                        value: summary.sales_today,
                        detail: 'Counter and online',
                        icon: ShoppingBag,
                    },
                    {
                        label: 'Revenue Today',
                        value: formatCurrency(summary.revenue_today),
                        detail: 'Including tax',
                        icon: Wallet,
                    },
                    {
                        label: 'Online Orders Open',
                        value: summary.open_online,
                        detail: 'Click to filter',
                        icon: Globe,
                        active: source === 'online',
                        onSelect: () =>
                            (source = source === 'online' ? '' : 'online'),
                    },
                    {
                        label: 'All Retail Sales',
                        value: totalSales,
                        detail: 'All statuses',
                        icon: Receipt,
                    },
                ]"
            />

            <DataTable
                v-model:options="serverOptions"
                :headers="headers"
                :items="tableItems"
                :total="sales.total"
                :loading="tableLoading"
                :selectable="false"
                item-key="payment_public_id"
                noun="retail sales"
                :empty-icon="ShoppingBag"
                :empty-message="
                    hasActiveFilters
                        ? 'No sales found. Try adjusting your filters.'
                        : 'No retail sales yet. Record one when a customer buys a product.'
                "
                :resettable="hasActiveFilters"
                @reset="resetTable"
            >
                <template #toolbar>
                    <DataTableToolbar
                        v-model:search="search"
                        v-model:status="status"
                        :statuses="statusOptions"
                        status-label="Filter by status"
                        search-placeholder="Search by name, sale # or phone…"
                        search-label="Search retail sales"
                    >
                        <select
                            v-model="source"
                            aria-label="Filter by source"
                            class="h-11 rounded-lg border border-input bg-background px-3 text-sm focus:ring-2 focus:ring-ring focus:outline-none"
                        >
                            <option value="">All Sources</option>
                            <option value="shop">Counter</option>
                            <option value="online">Online</option>
                        </select>
                        <Button
                            v-if="hasActiveFilters"
                            variant="ghost"
                            class="h-11 gap-1"
                            @click="resetTable"
                        >
                            <X class="h-3.5 w-3.5" /> Clear
                        </Button>
                    </DataTableToolbar>
                </template>

                <template #item-order_number="sale">
                    <button
                        type="button"
                        class="font-medium text-blue-600 hover:text-blue-800 hover:underline"
                        @click="
                            router.visit(
                                `${orderBase}/${sale.payment_public_id}`,
                            )
                        "
                    >
                        {{ sale.order_number }}
                    </button>
                </template>

                <template #item-customer="sale">
                    <IdentityCell
                        :name="sale.customer_name"
                        :secondary="sale.customer_phone"
                    />
                </template>

                <template #item-items="sale">
                    <span
                        class="block max-w-[240px] truncate text-muted-foreground"
                        :title="sale.items.join(', ')"
                    >
                        {{ sale.items.join(', ') || '—' }}
                    </span>
                </template>

                <template #item-source="sale">
                    <StatusPill
                        :tone="sale.source === 'online' ? 'info' : 'neutral'"
                        :icon="sale.source === 'online' ? Globe : Store"
                    >
                        {{ sale.source === 'online' ? 'Online' : 'Counter' }}
                    </StatusPill>
                </template>

                <template #item-status="sale">
                    <StatusPill
                        :tone="saleStatusTone(sale.status)"
                        :icon="statusIcon(sale.status)"
                    >
                        {{ formatLabel(sale.status) }}
                    </StatusPill>
                </template>

                <template #item-payment_status="sale">
                    <StatusPill :tone="statusTone(sale.payment_status)">
                        {{ formatLabel(sale.payment_status) }}
                    </StatusPill>
                </template>

                <template #item-total="sale">
                    <span class="font-medium whitespace-nowrap">{{
                        formatCurrency(sale.total_amount)
                    }}</span>
                </template>

                <template #item-created_at="sale">
                    <span class="whitespace-nowrap text-muted-foreground">{{
                        formatDate(sale.created_at)
                    }}</span>
                </template>

                <template #item-actions="sale">
                    <RowActions
                        :label="`sale ${sale.order_number}`"
                        :actions="[
                            {
                                label: 'View',
                                icon: Eye,
                                onSelect: () =>
                                    router.visit(
                                        `${orderBase}/${sale.payment_public_id}`,
                                    ),
                            },
                            {
                                label: 'Mark as ready',
                                icon: CheckCircle,
                                show: canManage && canMarkReady(sale),
                                onSelect: () =>
                                    changeStatus(sale, 'completed'),
                            },
                            {
                                label: 'Mark as collected',
                                icon: PackageCheck,
                                show: canManage && canMarkCollected(sale),
                                onSelect: () =>
                                    changeStatus(sale, 'picked_up'),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>
    </ShopLayout>
</template>
