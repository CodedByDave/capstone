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
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import {
    AlertCircle,
    Archive,
    CheckCircle,
    Clock,
    CreditCard,
    Eye,
    Loader,
    Package,
    PackageCheck,
    Pencil,
    Plus,
    RotateCcw,
    Trash2,
    X,
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
    payment_status: string;
    payment_method: string;
    total_amount: string;
    amount_paid: string;
    created_at: string;
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
const base = computed(() =>
    isOwner.value ? '/shop/operations/orders' : '/staff/operations/orders',
);

// ─── Filters and server options ────────────────────

const search = ref(String(props.filters.search ?? ''));
const status = ref(String(props.filters.status ?? ''));
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
        status: status.value || undefined,
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
        only: ['orders', 'statusSummary', 'filters'],
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
    ...['pending', 'in_progress', 'completed', 'picked_up'].map((value) => ({
        value,
        label: formatLabel(value),
        count: props.statusSummary[value] ?? 0,
    })),
]);

// ─── Table ─────────────────────────────────────────

const headers = computed<DataTableHeader[]>(() => [
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

const changeStatus = (order: Order, next: 'picked_up') => {
    router.patch(
        `${base.value}/${order.payment_public_id}/status`,
        { status: next },
        {
            preserveScroll: true,
            only: ['orders', 'statusSummary', 'flash', 'toast'],
            onError: (errors) =>
                toast.error(
                    errors.status ?? 'The order status could not be changed.',
                ),
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
                class="grid grid-cols-2 gap-4 lg:grid-cols-4"
            >
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
                    v-if="orders.data.length > 0"
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
                                <Button
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
                                    v-if="!showArchived"
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
                                label: 'Mark as picked up',
                                icon: PackageCheck,
                                show: !showArchived && canPickUp(order),
                                onSelect: () =>
                                    changeStatus(order, 'picked_up'),
                            },
                            {
                                label: 'Edit',
                                icon: Pencil,
                                show: !showArchived,
                                onSelect: () =>
                                    router.visit(
                                        `${base}/${order.payment_public_id}/edit`,
                                    ),
                            },
                            {
                                label: 'Archive',
                                icon: Trash2,
                                destructive: true,
                                show: !showArchived,
                                onSelect: () => confirmDelete(order),
                            },
                            {
                                label: 'Restore',
                                icon: RotateCcw,
                                show: showArchived,
                                onSelect: () => restoreOrder(order),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>

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
