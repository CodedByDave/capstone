<script setup lang="ts">
import {
    DataTable,
    DataTableToolbar,
    IdentityCell,
    RowActions,
    StatusPill,
    statusTone,
    type DataTableHeader,
} from '@/components/data-table';
import CompleteOrderDialog from '@/components/shop/CompleteOrderDialog.vue';
import ExportCsvButton from '@/components/shop/ExportCsvButton.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import type { DataTableOptions } from '@/composables/useDataTable';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import {
    AlertCircle,
    Archive,
    CheckCheck,
    CheckCircle,
    Clock,
    CreditCard,
    Eye,
    ListOrdered,
    Loader,
    Package,
    PackageCheck,
    Pencil,
    Plus,
    RotateCcw,
    Trash2,
    Undo2,
    X,
    Zap,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

// ─── Types ─────────────────────────────────────────

interface Order {
    id: number;
    payment_public_id: string;
    order_number: string;
    customer_name: string;
    customer_phone: string | null;
    status: string;
    deliver_to_customer?: boolean;
    pricing_model?: 'per_kg' | 'per_bundle' | 'per_piece';
    actual_weight_kg?: string | null;
    payment_status: string;
    payment_method: string;
    total_amount: string;
    amount_paid: string;
    created_at: string;
    is_rush: boolean;
    rush_fee: string;
    // 1 = the next order to work on; null once the order is no longer waiting.
    queue_position: number | null;
    service?: { service_name: string };
}

interface Paginator {
    data: Order[];
    current_page: number;
    per_page: number;
    total: number;
}

// ─── Props ─────────────────────────────────────────

const props = defineProps<{
    orders: Paginator;
    statusSummary: Record<string, number>;
    newOrdersCount: number;
    queueSummary: { waiting: number; rush: number };
    rushFee: number;
    filters: Record<string, string | number | boolean | null | undefined>;
    showArchived: boolean;
}>();

// ─── Toast ─────────────────────────────────────────

const page = usePage<AppPageProps>();

watch(
    () => page.props.toast as any,
    (t) => {
        if (!t) return;
        if (t.type === 'success') toast.success(t.message);
        if (t.type === 'error') toast.error(t.message);
        if (t.type === 'warning') toast.warning(t.message);
        if (t.type === 'info') toast.info(t.message);
    },
    { immediate: true },
);

// ─── Base URL ──────────────────────────────────────

const isOwner = computed(() => page.props.auth.user.role === 'owner');
const { can } = usePermissions();
const canCreate = computed(() => can('Operations', 'create'));
const canManage = computed(() => can('Operations', 'manage'));
const base = computed(() =>
    isOwner.value ? '/shop/operations/orders' : '/staff/operations/orders',
);

// ─── Filters and server options ────────────────────

const search = ref(String(props.filters.search ?? ''));
// "new" is a date-based view (orders placed today), not an order status.
const status = ref(
    props.filters.new_only ? 'new' : String(props.filters.status ?? ''),
);
const paymentStatus = ref(String(props.filters.payment_status ?? ''));
const tableLoading = ref(false);

const serverOptions = ref<DataTableOptions>({
    page: props.orders.current_page,
    rowsPerPage: props.orders.per_page,
    sortBy: String(props.filters.sort_by ?? 'created_at'),
    sortType: props.filters.sort_direction === 'asc' ? 'asc' : 'desc',
});

function query() {
    return {
        search: search.value || undefined,
        status:
            status.value && status.value !== 'new' ? status.value : undefined,
        new_only: status.value === 'new' ? 1 : undefined,
        payment_status: paymentStatus.value || undefined,
        archived: props.showArchived ? 1 : undefined,
        sort_by: serverOptions.value.sortBy,
        sort_direction: serverOptions.value.sortType,
        page: serverOptions.value.page,
        per_page: serverOptions.value.rowsPerPage,
    };
}

function applyFilters() {
    router.get(base.value, query(), {
        only: [
            'orders',
            'statusSummary',
            'newOrdersCount',
            'queueSummary',
            'filters',
        ],
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
    });
}

// A changed filter always starts again from the first page.
const filterTable = debounce(() => {
    if (serverOptions.value.page !== 1) {
        serverOptions.value.page = 1;
        return;
    }

    applyFilters();
}, 300);

watch([search, status, paymentStatus], filterTable);

// The queue is shown in queue order, so staff see which laundry to do next.
watch(status, (value) => {
    const sorted = serverOptions.value.sortBy;

    if (value === 'queue' && sorted === 'created_at') {
        serverOptions.value = {
            ...serverOptions.value,
            sortBy: 'queue',
            sortType: 'asc',
        };
    } else if (value !== 'queue' && sorted === 'queue') {
        serverOptions.value = {
            ...serverOptions.value,
            sortBy: 'created_at',
            sortType: 'desc',
        };
    }
});

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
    () =>
        !props.showArchived &&
        (search.value || status.value || paymentStatus.value),
);

const clearFilters = () => {
    search.value = '';
    status.value = '';
    paymentStatus.value = '';
};

// Clears search, filters, and sort; the watchers reload the first page.
const resetTable = () => {
    clearFilters();
    serverOptions.value = {
        ...serverOptions.value,
        page: 1,
        sortBy: 'created_at',
        sortType: 'desc',
    };
};

const statusOptions = computed(() => [
    { value: '', label: 'All', count: totalOrders.value },
    { value: 'new', label: 'New (today)', count: props.newOrdersCount },
    { value: 'queue', label: 'In queue', count: props.queueSummary.waiting },
    ...['pending', 'in_progress', 'completed', 'picked_up'].map((value) => ({
        value,
        label: formatLabel(value),
        count: props.statusSummary[value] ?? 0,
    })),
]);

// ─── Table ─────────────────────────────────────────

const headers = computed<DataTableHeader[]>(() => [
    ...(props.showArchived
        ? []
        : [{ text: 'Queue', value: 'queue', width: 130, sortable: true }]),
    { text: 'Order #', value: 'order_number', width: 150, sortable: true },
    { text: 'Customer', value: 'customer', width: 220, sortable: true },
    { text: 'Service', value: 'service', width: 180, sortable: true },
    ...(props.showArchived
        ? []
        : [
              { text: 'Status', value: 'status', width: 150, sortable: true },
              {
                  text: 'Payment',
                  value: 'payment_status',
                  width: 130,
                  sortable: true,
              },
          ]),
    { text: 'Total', value: 'total', width: 130, sortable: true },
    { text: 'Date', value: 'created_at', width: 140, sortable: true },
    {
        text: 'Actions',
        value: 'actions',
        width: props.showArchived ? 130 : 140,
        align: 'center',
    },
]);

const tableItems = computed(() =>
    props.orders.data.map((order) => ({
        ...order,
        customer: order.customer_name,
        service: order.service?.service_name ?? '—',
        total: order.total_amount,
        queue: order.queue_position ?? 0,
        actions: '',
    })),
);

// ─── KPIs ──────────────────────────────────────────

const totalOrders = computed(() =>
    Object.values(props.statusSummary).reduce((a, b) => a + b, 0),
);

const totalRevenue = computed(() =>
    props.orders.data.reduce((sum, o) => sum + Number(o.total_amount), 0),
);

const totalCollected = computed(() =>
    props.orders.data.reduce((sum, o) => sum + Number(o.amount_paid), 0),
);

const outstandingBalance = computed(
    () => totalRevenue.value - totalCollected.value,
);

const unpaidCount = computed(
    () => props.orders.data.filter((o) => o.payment_status === 'unpaid').length,
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
    })[s] ?? Package;

// A picked up order is finished and gone, so it gets the quiet grey tone.
const orderStatusTone = (s: string) =>
    s === 'picked_up' ? 'neutral' : statusTone(s);

// ─── Status actions ────────────────────────────────

// Orders are completed by the shop's auto-complete automation, so only the hand-over is a manual action.
// Delivered orders are handed over by a rider, so only collected orders can be picked up.
const canPickUp = (order: Order) =>
    order.status === 'completed' && !order.deliver_to_customer;

// An order still being worked on can be completed by hand, e.g. when it is done early or the
// automation has not caught up. It needs a final price, so an unweighed order goes to Edit first.
const canComplete = (order: Order) => order.status === 'in_progress';
// A completion made by mistake can be taken back until the order is picked up.
const canRevert = (order: Order) => order.status === 'completed';

const orderToComplete = ref<Order | null>(null);
const showCompleteDialog = ref(false);
const completeDialogMode = ref<'complete' | 'revert'>('complete');

const startRevert = (order: Order) => {
    orderToComplete.value = order;
    completeDialogMode.value = 'revert';
    showCompleteDialog.value = true;
};

const startComplete = (order: Order) => {
    const priced =
        order.pricing_model === 'per_piece' ||
        (order.actual_weight_kg !== null &&
            order.actual_weight_kg !== undefined);

    if (!priced) {
        toast.info('Enter the actual weight first, then complete the order.');
        router.visit(`${base.value}/${order.payment_public_id}/edit`);
        return;
    }

    orderToComplete.value = order;
    completeDialogMode.value = 'complete';
    showCompleteDialog.value = true;
};

const completeOrder = () => {
    if (orderToComplete.value)
        changeStatus(
            orderToComplete.value,
            completeDialogMode.value === 'complete'
                ? 'completed'
                : 'in_progress',
        );
    orderToComplete.value = null;
};

const changeStatus = (
    order: Order,
    next: 'picked_up' | 'completed' | 'in_progress',
) => {
    router.patch(
        `${base.value}/${order.payment_public_id}/status`,
        { status: next },
        {
            preserveScroll: true,
            only: [
                'orders',
                'statusSummary',
                'newOrdersCount',
                'queueSummary',
                'flash',
                'toast',
            ],
            onError: (errors) =>
                toast.error(
                    errors.status ?? 'The order status could not be changed.',
                ),
        },
    );
};

// ─── Rush ──────────────────────────────────────────

// Only an order still in the queue can be rushed. Taking rush off is blocked once it has a payment.
const inQueue = (order: Order) => order.queue_position !== null;
// Rush is decided before the customer pays, so both actions are only offered on unpaid orders.
const unpaid = (order: Order) => Number(order.amount_paid) === 0;
const canRush = (order: Order) =>
    inQueue(order) && unpaid(order) && !order.is_rush && props.rushFee > 0;
const canUnrush = (order: Order) =>
    inQueue(order) && unpaid(order) && order.is_rush;

const orderToRush = ref<Order | null>(null);
const rushMode = ref<'rush' | 'unrush'>('rush');
const showRushDialog = ref(false);

const startRush = (order: Order, mode: 'rush' | 'unrush') => {
    orderToRush.value = order;
    rushMode.value = mode;
    showRushDialog.value = true;
};

const confirmRush = () => {
    const order = orderToRush.value;
    showRushDialog.value = false;
    orderToRush.value = null;
    if (!order) return;

    router.patch(
        `${base.value}/${order.payment_public_id}/rush`,
        { rush: rushMode.value === 'rush' },
        {
            preserveScroll: true,
            only: [
                'orders',
                'statusSummary',
                'queueSummary',
                'flash',
                'toast',
                'errors',
            ],
            onError: (errors) =>
                toast.error(errors.rush ?? 'The order could not be updated.'),
        },
    );
};

// ─── Toggle Archive View ───────────────────────────

const toggleArchived = () => {
    router.get(
        base.value,
        { archived: props.showArchived ? undefined : true },
        {
            preserveState: false,
            replace: true,
        },
    );
};

// ─── Restore ───────────────────────────────────────

const restoreOrder = (order: Order) => {
    router.patch(`${base.value}/${order.payment_public_id}/restore`);
};

const restoreAll = () => {
    router.post(`${base.value}/restore-all`);
};

// ─── Delete Dialog ─────────────────────────────────

const orderToDelete = ref<Order | null>(null);
const showDeleteDialog = ref(false);

const confirmDelete = (order: Order) => {
    orderToDelete.value = order;
    showDeleteDialog.value = true;
};

const handleDelete = () => {
    if (!orderToDelete.value) return;
    router.delete(`${base.value}/${orderToDelete.value.payment_public_id}`);
    showDeleteDialog.value = false;
    orderToDelete.value = null;
};
</script>

<template>
    <Head :title="showArchived ? 'Archived Orders' : 'Orders'" />

    <ShopLayout :title="showArchived ? 'Archived Orders' : 'Orders'">
        <div class="space-y-6 px-6">
            <!-- ── KPI Cards (active view only) ──────────── -->
            <div
                v-if="!showArchived"
                class="grid grid-cols-2 gap-4 lg:grid-cols-5"
            >
                <Card
                    class="cursor-pointer transition hover:ring-2 hover:ring-blue-200"
                    :class="{ 'ring-2 ring-blue-400': status === 'queue' }"
                    @click="status = status === 'queue' ? '' : 'queue'"
                >
                    <CardContent class="pt-5 pb-4">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                In Queue
                            </p>
                            <ListOrdered class="h-4 w-4 text-blue-500" />
                        </div>
                        <p class="text-2xl font-bold">
                            {{ queueSummary.waiting }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ queueSummary.rush }} rush · Click to filter
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="pt-5 pb-4">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                Total Orders
                            </p>
                            <Package class="h-4 w-4 text-blue-500" />
                        </div>
                        <p class="text-2xl font-bold">{{ totalOrders }}</p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            All statuses
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="pt-5 pb-4">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                Collected
                            </p>
                            <CheckCircle class="h-4 w-4 text-green-500" />
                        </div>
                        <p class="text-2xl font-bold">
                            {{ formatCurrency(totalCollected) }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{
                                totalRevenue
                                    ? (
                                          (totalCollected / totalRevenue) *
                                          100
                                      ).toFixed(0)
                                    : 0
                            }}% of revenue
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="pt-5 pb-4">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                Outstanding
                            </p>
                            <AlertCircle class="h-4 w-4 text-orange-500" />
                        </div>
                        <p
                            class="text-2xl font-bold"
                            :class="
                                outstandingBalance > 0 ? 'text-orange-600' : ''
                            "
                        >
                            {{ formatCurrency(outstandingBalance) }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Uncollected balance
                        </p>
                    </CardContent>
                </Card>

                <Card
                    class="cursor-pointer transition hover:ring-2 hover:ring-red-200"
                    :class="{
                        'ring-2 ring-red-400': paymentStatus === 'unpaid',
                    }"
                    @click="
                        paymentStatus =
                            paymentStatus === 'unpaid' ? '' : 'unpaid'
                    "
                >
                    <CardContent class="pt-5 pb-4">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                Unpaid Orders
                            </p>
                            <CreditCard class="h-4 w-4 text-red-500" />
                        </div>
                        <p
                            class="text-2xl font-bold"
                            :class="unpaidCount > 0 ? 'text-red-600' : ''"
                        >
                            {{ unpaidCount }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Click to filter
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- ── Archived Banner ───────────────────────── -->
            <div
                v-if="showArchived"
                class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3"
            >
                <Archive class="h-5 w-5 shrink-0 text-amber-600" />
                <div class="flex-1">
                    <p class="text-sm font-semibold text-amber-800">
                        Viewing Archived Orders
                    </p>
                    <p class="text-xs text-amber-600">
                        These orders have been soft-deleted. You can restore
                        them individually or all at once.
                    </p>
                </div>
                <Button
                    v-if="orders.data.length > 0 && canManage"
                    size="sm"
                    variant="outline"
                    class="gap-1.5 border-amber-300 text-amber-700 hover:bg-amber-100"
                    @click="restoreAll"
                >
                    <RotateCcw class="h-3.5 w-3.5" /> Restore All
                </Button>
            </div>

            <!-- ── Table Card ─────────────────────────────── -->
            <DataTable
                v-model:options="serverOptions"
                :headers="headers"
                :items="tableItems"
                :total="orders.total"
                :loading="tableLoading"
                noun="orders"
                :empty-icon="showArchived ? Archive : Package"
                :empty-message="
                    showArchived
                        ? 'No archived orders'
                        : hasActiveFilters
                          ? 'No orders found. Try adjusting your filters.'
                          : 'No orders found'
                "
                :resettable="!showArchived"
                :min-width="980"
                @reset="resetTable"
            >
                <template #toolbar>
                    <div class="space-y-3">
                        <div
                            class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
                        >
                            <h2 class="font-semibold">
                                {{
                                    showArchived
                                        ? 'Archived Orders'
                                        : 'Orders List'
                                }}
                            </h2>
                            <div class="flex gap-2">
                                <ExportCsvButton
                                    v-if="!showArchived"
                                    dataset="orders"
                                />
                                <Button
                                    v-if="canManage"
                                    variant="outline"
                                    size="sm"
                                    class="gap-1.5"
                                    :class="
                                        showArchived
                                            ? 'border-amber-400 bg-amber-50 text-amber-700'
                                            : ''
                                    "
                                    @click="toggleArchived"
                                >
                                    <Archive class="h-4 w-4" />
                                    {{
                                        showArchived
                                            ? 'Back to Active'
                                            : 'View Archives'
                                    }}
                                </Button>
                                <Button
                                    v-if="!showArchived && canCreate"
                                    size="sm"
                                    @click="router.visit(`${base}/create`)"
                                >
                                    <Plus class="mr-1 h-4 w-4" /> New Order
                                </Button>
                            </div>
                        </div>

                        <!-- Filters (active view only) -->
                        <DataTableToolbar
                            v-if="!showArchived"
                            v-model:search="search"
                            v-model:status="status"
                            :statuses="statusOptions"
                            search-placeholder="Search by name, order # or phone…"
                            search-label="Search orders"
                            status-label="Filter by order status"
                        >
                            <select
                                v-model="paymentStatus"
                                aria-label="Filter by payment"
                                class="h-9 rounded-md border border-input bg-background px-3 text-sm focus:ring-2 focus:ring-ring focus:outline-none"
                            >
                                <option value="">All Payments</option>
                                <option value="unpaid">Unpaid</option>
                                <option value="partial">Partial</option>
                                <option value="paid">Paid</option>
                            </select>
                            <Button
                                v-if="hasActiveFilters"
                                variant="ghost"
                                size="sm"
                                class="h-9 gap-1"
                                @click="clearFilters"
                            >
                                <X class="h-3.5 w-3.5" /> Clear
                            </Button>
                        </DataTableToolbar>
                    </div>
                </template>

                <template #item-queue="order">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span
                            v-if="order.queue_position"
                            class="inline-flex min-w-9 items-center justify-center rounded-md bg-blue-50 px-2 py-1 text-sm font-bold text-blue-700 dark:bg-blue-500/15 dark:text-blue-300"
                            :title="
                                order.queue_position === 1
                                    ? 'Work on this order next'
                                    : `${order.queue_position - 1} order(s) ahead of this one`
                            "
                            :aria-label="`Number ${order.queue_position} in the queue`"
                        >
                            #{{ order.queue_position }}
                        </span>
                        <StatusPill
                            v-if="order.is_rush"
                            tone="warning"
                            :icon="Zap"
                        >
                            Rush
                        </StatusPill>
                        <span
                            v-if="!order.queue_position && !order.is_rush"
                            class="text-muted-foreground"
                            >—</span
                        >
                    </div>
                </template>

                <template #item-order_number="order">
                    <button
                        v-if="!showArchived"
                        type="button"
                        class="font-medium text-blue-600 hover:text-blue-800 hover:underline"
                        @click="
                            router.visit(`${base}/${order.payment_public_id}`)
                        "
                    >
                        {{ order.order_number }}
                    </button>
                    <span v-else class="font-medium text-muted-foreground">
                        {{ order.order_number }}
                    </span>
                </template>

                <template #item-customer="order">
                    <IdentityCell
                        :name="order.customer_name"
                        :secondary="order.customer_phone"
                    />
                </template>

                <template #item-service="order">
                    <span class="text-muted-foreground">{{
                        order.service
                    }}</span>
                </template>

                <template #item-status="order">
                    <StatusPill
                        :tone="orderStatusTone(order.status)"
                        :icon="statusIcon(order.status)"
                    >
                        {{ formatLabel(order.status) }}
                    </StatusPill>
                </template>

                <template #item-payment_status="order">
                    <StatusPill :tone="statusTone(order.payment_status)">
                        {{ formatLabel(order.payment_status) }}
                    </StatusPill>
                </template>

                <template #item-total="order">
                    <span class="font-medium whitespace-nowrap">{{
                        formatCurrency(order.total_amount)
                    }}</span>
                </template>

                <template #item-created_at="order">
                    <span class="whitespace-nowrap text-muted-foreground">{{
                        formatDate(order.created_at)
                    }}</span>
                </template>

                <template #item-actions="order">
                    <RowActions
                        :label="`order ${order.order_number}`"
                        :actions="[
                            {
                                label: 'View',
                                icon: Eye,
                                show: !showArchived,
                                onSelect: () =>
                                    router.visit(
                                        `${base}/${order.payment_public_id}`,
                                    ),
                            },
                            {
                                label: 'Mark as rush',
                                icon: Zap,
                                show:
                                    !showArchived &&
                                    canManage &&
                                    canRush(order),
                                onSelect: () => startRush(order, 'rush'),
                            },
                            {
                                label: 'Remove rush',
                                icon: Undo2,
                                show:
                                    !showArchived &&
                                    canManage &&
                                    canUnrush(order),
                                onSelect: () => startRush(order, 'unrush'),
                            },
                            {
                                label: 'Mark as completed',
                                icon: CheckCheck,
                                show:
                                    !showArchived &&
                                    canManage &&
                                    canComplete(order),
                                onSelect: () => startComplete(order),
                            },
                            {
                                label: 'Revert to in progress',
                                icon: Undo2,
                                show:
                                    !showArchived &&
                                    canManage &&
                                    canRevert(order),
                                onSelect: () => startRevert(order),
                            },
                            {
                                label: 'Mark as picked up',
                                icon: PackageCheck,
                                show:
                                    !showArchived &&
                                    canManage &&
                                    canPickUp(order),
                                onSelect: () =>
                                    changeStatus(order, 'picked_up'),
                            },
                            {
                                label: 'Edit',
                                icon: Pencil,
                                show: !showArchived && canManage,
                                onSelect: () =>
                                    router.visit(
                                        `${base}/${order.payment_public_id}/edit`,
                                    ),
                            },
                            {
                                label: 'Archive',
                                icon: Trash2,
                                destructive: true,
                                show: !showArchived && canManage,
                                onSelect: () => confirmDelete(order),
                            },
                            {
                                label: 'Restore',
                                icon: RotateCcw,
                                show: showArchived && canManage,
                                onSelect: () => restoreOrder(order),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>

        <CompleteOrderDialog
            v-model:open="showCompleteDialog"
            :mode="completeDialogMode"
            :order-number="orderToComplete?.order_number"
            :customer-name="orderToComplete?.customer_name"
            @confirm="completeOrder"
        />

        <!-- ── Rush Alert Dialog ──────────────────────── -->
        <AlertDialog
            :open="showRushDialog"
            @update:open="showRushDialog = $event"
        >
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>
                        {{
                            rushMode === 'rush'
                                ? 'Rush this order?'
                                : 'Remove rush?'
                        }}
                    </AlertDialogTitle>
                    <AlertDialogDescription>
                        <template v-if="rushMode === 'rush'">
                            Order
                            <span class="font-semibold text-foreground">{{
                                orderToRush?.order_number
                            }}</span>
                            moves to the front of the queue and a rush fee of
                            <span class="font-semibold text-foreground">{{
                                formatCurrency(rushFee)
                            }}</span>
                            is added to the total. Collect it with the rest of
                            the payment.
                        </template>
                        <template v-else>
                            Order
                            <span class="font-semibold text-foreground">{{
                                orderToRush?.order_number
                            }}</span>
                            goes back to its normal place in the queue and the
                            rush fee of
                            <span class="font-semibold text-foreground">{{
                                formatCurrency(orderToRush?.rush_fee ?? 0)
                            }}</span>
                            is taken off the total.
                        </template>
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="showRushDialog = false"
                        >Cancel</AlertDialogCancel
                    >
                    <AlertDialogAction @click="confirmRush">
                        {{ rushMode === 'rush' ? 'Rush order' : 'Remove rush' }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <!-- ── Delete Alert Dialog ────────────────────── -->
        <AlertDialog
            :open="showDeleteDialog"
            @update:open="showDeleteDialog = $event"
        >
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Archive Order</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to archive order
                        <span class="font-semibold text-foreground">{{
                            orderToDelete?.order_number
                        }}</span>
                        for
                        <span class="font-semibold text-foreground">{{
                            orderToDelete?.customer_name
                        }}</span
                        >? This will restore any deducted inventory and the
                        order will be moved to archive.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="showDeleteDialog = false"
                        >Cancel</AlertDialogCancel
                    >
                    <AlertDialogAction
                        class="bg-red-600 text-white hover:bg-red-700"
                        @click="handleDelete"
                    >
                        Archive Order
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </ShopLayout>
</template>
