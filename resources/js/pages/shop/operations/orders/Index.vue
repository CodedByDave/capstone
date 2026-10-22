<script setup lang="ts">
import SortableHeader from '@/components/SortableHeader.vue';
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
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
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
    Pencil,
    Plus,
    RotateCcw,
    Search,
    Trash2,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import Vue3EasyDataTable, {
    type Header,
    type ServerOptions,
} from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';
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

type SortColumn =
    | 'order_number'
    | 'customer'
    | 'service'
    | 'status'
    | 'payment_status'
    | 'total'
    | 'created_at';

// ─── Props ─────────────────────────────────────────

const EasyDataTable = Vue3EasyDataTable;

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

const serverOptions = ref<ServerOptions>({
    page: props.orders.current_page,
    rowsPerPage: props.orders.per_page,
    sortBy: String(props.filters.sort_by ?? 'created_at'),
    sortType: props.filters.sort_direction === 'asc' ? 'asc' : 'desc',
});

// Plain strings for the header component; the table library types these more loosely.
const sortBy = computed(() => String(serverOptions.value.sortBy));
const sortType = computed<'asc' | 'desc'>(() =>
    serverOptions.value.sortType === 'asc' ? 'asc' : 'desc',
);

function toggleSort(column: SortColumn) {
    if (serverOptions.value.sortBy === column) {
        serverOptions.value.sortType =
            serverOptions.value.sortType === 'asc' ? 'desc' : 'asc';
        return;
    }

    serverOptions.value.sortBy = column;
    serverOptions.value.sortType = 'asc';
}

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

// ─── Table ─────────────────────────────────────────

const headers = computed<Header[]>(() => [
    { text: 'Order #', value: 'order_number', width: 150 },
    { text: 'Customer', value: 'customer', width: 210 },
    { text: 'Service', value: 'service', width: 180 },
    ...(props.showArchived
        ? []
        : [
              { text: 'Status', value: 'status', width: 150 },
              { text: 'Payment', value: 'payment_status', width: 130 },
          ]),
    { text: 'Total', value: 'total', width: 130 },
    { text: 'Date', value: 'created_at', width: 140 },
    {
        text: 'Actions',
        value: 'actions',
        width: props.showArchived ? 130 : 140,
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
    })[s] ?? Package;

const statusStyle = (s: string) =>
    ({
        pending: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
        in_progress: 'bg-blue-50 text-blue-700 ring-1 ring-blue-200',
        completed: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
    })[s] ?? 'bg-gray-50 text-gray-700 ring-1 ring-gray-200';

const paymentStyle = (s: string) =>
    ({
        unpaid: 'bg-red-50 text-red-700 ring-1 ring-red-200',
        partial: 'bg-orange-50 text-orange-700 ring-1 ring-orange-200',
        paid: 'bg-green-50 text-green-700 ring-1 ring-green-200',
    })[s] ?? 'bg-gray-50 text-gray-700 ring-1 ring-gray-200';

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
            <Card>
                <CardHeader>
                    <div
                        class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
                    >
                        <CardTitle>{{
                            showArchived ? 'Archived Orders' : 'Orders List'
                        }}</CardTitle>
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
                                @click="router.visit(`${base}/create`)"
                            >
                                <Plus class="mr-1 h-4 w-4" /> New Order
                            </Button>
                        </div>
                    </div>
                </CardHeader>

                <CardContent class="space-y-4">
                    <!-- Filters (active view only) -->
                    <div
                        v-if="!showArchived"
                        class="flex flex-col gap-2 sm:flex-row"
                    >
                        <div class="relative flex-1">
                            <Search
                                class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                            />
                            <Input
                                v-model="search"
                                class="pl-8"
                                placeholder="Search by name, order # or phone…"
                            />
                        </div>
                        <select
                            v-model="status"
                            aria-label="Filter by status"
                            class="h-9 rounded-md border border-input bg-background px-3 text-sm focus:ring-2 focus:ring-ring focus:outline-none"
                        >
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
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
                    </div>

                    <!-- Active filter tags -->
                    <div v-if="hasActiveFilters" class="flex flex-wrap gap-2">
                        <span
                            v-if="status"
                            class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary"
                        >
                            Status: {{ formatLabel(status) }}
                            <button
                                class="hover:text-primary/70"
                                aria-label="Remove status filter"
                                @click="status = ''"
                            >
                                <X class="h-3 w-3" />
                            </button>
                        </span>
                        <span
                            v-if="paymentStatus"
                            class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary"
                        >
                            Payment: {{ formatLabel(paymentStatus) }}
                            <button
                                class="hover:text-primary/70"
                                aria-label="Remove payment filter"
                                @click="paymentStatus = ''"
                            >
                                <X class="h-3 w-3" />
                            </button>
                        </span>
                    </div>

                    <!-- Table -->
                    <div class="overflow-hidden rounded-lg border">
                        <EasyDataTable
                            v-model:server-options="serverOptions"
                            class="orders-data-table"
                            :headers="headers"
                            :items="tableItems"
                            :server-items-length="orders.total"
                            :loading="tableLoading"
                            :rows-items="[5, 10, 15, 20, 50, 100]"
                            rows-of-page-separator-message="out of"
                            rows-per-page-message="Rows per page:"
                            buttons-pagination
                            alternating
                            must-sort
                        >
                            <template #header-order_number="{ text }">
                                <SortableHeader
                                    :label="text"
                                    column="order_number"
                                    :sort-by="sortBy"
                                    :sort-type="sortType"
                                    @toggle="toggleSort('order_number')"
                                />
                            </template>
                            <template #header-customer="{ text }">
                                <SortableHeader
                                    :label="text"
                                    column="customer"
                                    :sort-by="sortBy"
                                    :sort-type="sortType"
                                    @toggle="toggleSort('customer')"
                                />
                            </template>
                            <template #header-service="{ text }">
                                <SortableHeader
                                    :label="text"
                                    column="service"
                                    :sort-by="sortBy"
                                    :sort-type="sortType"
                                    @toggle="toggleSort('service')"
                                />
                            </template>
                            <template #header-status="{ text }">
                                <SortableHeader
                                    :label="text"
                                    column="status"
                                    :sort-by="sortBy"
                                    :sort-type="sortType"
                                    @toggle="toggleSort('status')"
                                />
                            </template>
                            <template #header-payment_status="{ text }">
                                <SortableHeader
                                    :label="text"
                                    column="payment_status"
                                    :sort-by="sortBy"
                                    :sort-type="sortType"
                                    @toggle="toggleSort('payment_status')"
                                />
                            </template>
                            <template #header-total="{ text }">
                                <SortableHeader
                                    :label="text"
                                    column="total"
                                    :sort-by="sortBy"
                                    :sort-type="sortType"
                                    @toggle="toggleSort('total')"
                                />
                            </template>
                            <template #header-created_at="{ text }">
                                <SortableHeader
                                    :label="text"
                                    column="created_at"
                                    :sort-by="sortBy"
                                    :sort-type="sortType"
                                    @toggle="toggleSort('created_at')"
                                />
                            </template>

                            <template #item-order_number="order">
                                <button
                                    v-if="!showArchived"
                                    class="font-medium text-blue-600 hover:text-blue-800 hover:underline"
                                    @click="
                                        router.visit(
                                            `${base}/${order.payment_public_id}`,
                                        )
                                    "
                                >
                                    {{ order.order_number }}
                                </button>
                                <span
                                    v-else
                                    class="font-medium text-muted-foreground"
                                >
                                    {{ order.order_number }}
                                </span>
                            </template>

                            <template #item-customer="order">
                                <div class="py-1">
                                    <p class="font-medium">
                                        {{ order.customer_name }}
                                    </p>
                                    <p
                                        v-if="order.customer_phone"
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{ order.customer_phone }}
                                    </p>
                                </div>
                            </template>

                            <template #item-service="order">
                                <span class="text-muted-foreground">{{
                                    order.service
                                }}</span>
                            </template>

                            <template #item-status="order">
                                <span
                                    :class="statusStyle(order.status)"
                                    class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold"
                                >
                                    <component
                                        :is="statusIcon(order.status)"
                                        class="h-3 w-3"
                                    />
                                    {{ formatLabel(order.status) }}
                                </span>
                            </template>

                            <template #item-payment_status="order">
                                <span
                                    :class="paymentStyle(order.payment_status)"
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                                >
                                    {{ formatLabel(order.payment_status) }}
                                </span>
                            </template>

                            <template #item-total="order">
                                <span class="font-medium">{{
                                    formatCurrency(order.total_amount)
                                }}</span>
                            </template>

                            <template #item-created_at="order">
                                <span
                                    class="whitespace-nowrap text-muted-foreground"
                                    >{{ formatDate(order.created_at) }}</span
                                >
                            </template>

                            <template #item-actions="order">
                                <div class="flex gap-0.5">
                                    <!-- Active view actions -->
                                    <template v-if="!showArchived">
                                        <Button
                                            size="icon"
                                            variant="ghost"
                                            aria-label="View order"
                                            @click="
                                                router.visit(
                                                    `${base}/${order.payment_public_id}`,
                                                )
                                            "
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            size="icon"
                                            variant="ghost"
                                            aria-label="Edit order"
                                            @click="
                                                router.visit(
                                                    `${base}/${order.payment_public_id}/edit`,
                                                )
                                            "
                                        >
                                            <Pencil
                                                class="h-4 w-4 text-green-500"
                                            />
                                        </Button>
                                        <Button
                                            size="icon"
                                            variant="ghost"
                                            aria-label="Archive order"
                                            @click="confirmDelete(order)"
                                        >
                                            <Trash2
                                                class="h-4 w-4 text-red-500"
                                            />
                                        </Button>
                                    </template>

                                    <!-- Archived view actions -->
                                    <template v-else>
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            class="gap-1.5 border-emerald-300 text-emerald-700 hover:bg-emerald-50"
                                            @click="restoreOrder(order)"
                                        >
                                            <RotateCcw class="h-3.5 w-3.5" />
                                            Restore
                                        </Button>
                                    </template>
                                </div>
                            </template>

                            <template #empty-message>
                                <div class="py-10 text-center">
                                    <component
                                        :is="showArchived ? Archive : Package"
                                        class="mx-auto mb-2 h-10 w-10 text-muted-foreground opacity-30"
                                    />
                                    <p class="text-muted-foreground">
                                        {{
                                            showArchived
                                                ? 'No archived orders'
                                                : 'No orders found'
                                        }}
                                    </p>
                                    <p
                                        v-if="hasActiveFilters"
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        Try adjusting your filters
                                    </p>
                                </div>
                            </template>
                        </EasyDataTable>
                    </div>
                </CardContent>
            </Card>
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

<style scoped>
.orders-data-table {
    --easy-table-border: 0;
    --easy-table-row-border: 1px solid var(--border);
    --easy-table-header-background-color: var(--muted);
    --easy-table-header-font-color: var(--muted-foreground);
    --easy-table-header-font-size: 12px;
    --easy-table-header-height: 52px;
    --easy-table-header-item-padding: 10px 12px;
    --easy-table-body-row-background-color: var(--background);
    --easy-table-body-even-row-background-color: color-mix(
        in srgb,
        var(--muted) 35%,
        transparent
    );
    --easy-table-body-row-font-color: var(--foreground);
    --easy-table-body-even-row-font-color: var(--foreground);
    --easy-table-body-row-hover-background-color: color-mix(
        in srgb,
        var(--muted) 65%,
        transparent
    );
    --easy-table-body-row-hover-font-color: var(--foreground);
    --easy-table-body-row-height: 64px;
    --easy-table-body-item-padding: 10px 12px;
    --easy-table-message-font-color: var(--muted-foreground);
    --easy-table-footer-background-color: var(--background);
    --easy-table-footer-font-color: var(--muted-foreground);
    --easy-table-footer-font-size: 12px;
    --easy-table-footer-height: 56px;
    --easy-table-footer-padding: 0 16px;
    --easy-table-buttons-pagination-border: 1px solid var(--border);
    width: 100%;
}

:deep(.vue3-easy-data-table__main) {
    background: var(--background);
}

:deep(.vue3-easy-data-table__main table) {
    min-width: 980px;
}
</style>
