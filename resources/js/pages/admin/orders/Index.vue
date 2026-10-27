<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import {
    DataTable,
    DataTableToolbar,
    RowActions,
    StatusPill,
    statusLabel,
    statusTone,
    type DataTableHeader,
} from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import type { DataTableOptions } from '@/composables/useDataTable';
import {
    Download,
    Eye,
    FileText,
    ListOrdered,
    RefreshCcw,
    ShieldCheck,
    ShieldX,
    Upload,
    X,
} from 'lucide-vue-next';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Module {
    id: number;
    name: string;
    price: string;
}

interface Payment {
    id: number;
    payment_method: string;
    amount: string;
    status: string;
    paid_at: string | null;
}

const paymentBadge: Record<string, { label: string; cls: string }> = {
    gcash: { label: 'GCash', cls: 'bg-blue-100 text-blue-700' },
    maya: { label: 'Maya', cls: 'bg-green-100 text-green-700' },
    card: { label: 'Card', cls: 'bg-violet-100 text-violet-700' },
    grab_pay: { label: 'GrabPay', cls: 'bg-emerald-100 text-emerald-700' },
    dob: { label: 'Online Banking', cls: 'bg-amber-100 text-amber-700' },
    billease: { label: 'BillEase', cls: 'bg-orange-100 text-orange-700' },
};

interface OrderItem {
    id: number;
    public_id: string;
    transaction_reference: string;
    shop_name: string;
    owner_name: string;
    email: string;
    phone: string;
    municipality: string;
    barangay: string;
    plan_name: string | null;
    billing_months: number | null;
    total_price: string;
    status: string;
    expires_at: string | null;
    created_at: string;
    kyc_bir: string | null;
    kyc_dti: string | null;
    kyc_mayors: string | null;
    kyc_sanitary: string | null;
    modules: Module[];
    payments: Payment[];
    payment_method: string | null;
}

interface Paginator {
    data: OrderItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    orders: Paginator;
    stats: {
        total: number;
        paid: number;
        rejected: number;
        pending: number;
        expired: number;
        revenue: number;
    };
    filters: Record<string, string | number | null | undefined>;
}>();

// ─── Flash ────────────────────────────────────────────────────────────────────

const page = usePage();

onMounted(() => {
    const flash = page.props.toast as
        | { type: string; message: string }
        | undefined;
    if (!flash) return;
    switch (flash.type) {
        case 'success':
            toast.success(flash.message);
            break;
        case 'error':
            toast.error(flash.message);
            break;
        default:
            toast(flash.message);
    }
});

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Order Management', href: '/admin/orders' },
];

// ─── Filters, sorting and paging ──────────────────────────────────────────────

const search = ref(String(props.filters.search ?? ''));
const status = ref(
    props.filters.status && props.filters.status !== 'all'
        ? String(props.filters.status)
        : '',
);
const plan = ref(
    props.filters.plan && props.filters.plan !== 'all'
        ? String(props.filters.plan)
        : '',
);
const date = ref(String(props.filters.date ?? ''));
const importInput = ref<HTMLInputElement | null>(null);
const importing = ref(false);
const tableLoading = ref(false);

const headers: DataTableHeader[] = [
    {
        text: 'Transaction reference',
        value: 'reference',
        width: 170,
        sortable: true,
    },
    { text: 'Shop', value: 'shop', width: 190, sortable: true },
    { text: 'Plan', value: 'plan', width: 120, sortable: true },
    { text: 'Payment', value: 'payment', width: 120 },
    { text: 'Total', value: 'total', width: 120, sortable: true },
    { text: 'Status', value: 'status', width: 130, sortable: true },
    { text: 'Ordered', value: 'created_at', width: 140, sortable: true },
    { text: 'Actions', value: 'actions', width: 170, align: 'center' },
];

const statusOptions = computed(() => [
    { value: '', label: 'All', count: props.stats.total },
    { value: 'paid', label: 'Paid', count: props.stats.paid },
    { value: 'approved', label: 'Approved' },
    { value: 'pending', label: 'Pending', count: props.stats.pending },
    { value: 'rejected', label: 'Rejected', count: props.stats.rejected },
    { value: 'failed', label: 'Failed' },
]);

function setStatus(value: string) {
    status.value = value;
    filterTable();
}

const serverOptions = ref<DataTableOptions>({
    page: props.orders.current_page,
    rowsPerPage: props.orders.per_page,
    sortBy: String(props.filters.sort_by ?? 'created_at'),
    sortType: props.filters.sort_direction === 'asc' ? 'asc' : 'desc',
});

const tableItems = computed(() =>
    props.orders.data.map((order) => ({
        ...order,
        reference: order.transaction_reference,
        shop: order.shop_name,
        owner: order.owner_name,
        plan: order.plan_name ?? '',
        payment: order.payment_method ?? '',
        total: order.total_price,
        expires: order.expires_at ?? '',
        actions: '',
    })),
);

function query() {
    return {
        search: search.value || undefined,
        status: status.value || undefined,
        plan: plan.value || undefined,
        date: date.value || undefined,
        sort_by: serverOptions.value.sortBy,
        sort_direction: serverOptions.value.sortType,
        page: serverOptions.value.page,
        per_page: serverOptions.value.rowsPerPage,
    };
}

function applyFilters() {
    router.get('/admin/orders', query(), {
        only: ['orders', 'filters'],
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
function filterTable() {
    if (serverOptions.value.page !== 1) {
        serverOptions.value.page = 1;
        return;
    }

    applyFilters();
}

function resetFilters() {
    search.value = '';
    status.value = '';
    plan.value = '';
    date.value = '';

    const optionsChanged =
        serverOptions.value.page !== 1 ||
        serverOptions.value.sortBy !== 'created_at' ||
        serverOptions.value.sortType !== 'desc';

    serverOptions.value.page = 1;
    serverOptions.value.sortBy = 'created_at';
    serverOptions.value.sortType = 'desc';

    if (!optionsChanged) applyFilters();
}

watch(
    [
        () => serverOptions.value.page,
        () => serverOptions.value.rowsPerPage,
        () => serverOptions.value.sortBy,
        () => serverOptions.value.sortType,
    ],
    () => applyFilters(),
);

function exportCsv() {
    const params = new URLSearchParams();
    const filters = {
        search: search.value || undefined,
        sort_by: String(serverOptions.value.sortBy),
        sort_direction: String(serverOptions.value.sortType),
        status: status.value || undefined,
        plan: plan.value || undefined,
        date: date.value || undefined,
    };

    Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined) params.set(key, value);
    });

    window.location.assign(`/admin/orders/export?${params.toString()}`);
}

function chooseCsvFile() {
    importInput.value?.click();
}

function importCsv(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    router.post(
        '/admin/orders/import',
        { file },
        {
            forceFormData: true,
            preserveScroll: true,
            onStart: () => (importing.value = true),
            onSuccess: () => toast.success('Orders imported successfully.'),
            onError: (errors) =>
                toast.error(
                    String(
                        errors.file || 'The orders CSV could not be imported.',
                    ),
                ),
            onFinish: () => {
                importing.value = false;
                input.value = '';
            },
        },
    );
}

// ─── Module Dropdown ──────────────────────────────────────────────────────────

const expandedOrder = ref<string | null>(null);

function toggleModules(orderReference: string) {
    expandedOrder.value =
        expandedOrder.value === orderReference ? null : orderReference;
}

function handleClickOutside() {
    expandedOrder.value = null;
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));

// ─── KYC Modal ────────────────────────────────────────────────────────────────

const kycModal = ref<{ open: boolean; order: OrderItem | null }>({
    open: false,
    order: null,
});

function openKyc(order: OrderItem) {
    kycModal.value = { open: true, order };
}

function closeKyc() {
    kycModal.value = { open: false, order: null };
}

const kycDocs = (order: OrderItem) => [
    { label: 'BIR Certificate', key: 'kyc_bir', path: order.kyc_bir },
    { label: 'DTI Registration', key: 'kyc_dti', path: order.kyc_dti },
    { label: "Mayor's Permit", key: 'kyc_mayors', path: order.kyc_mayors },
    { label: 'Sanitary Permit', key: 'kyc_sanitary', path: order.kyc_sanitary },
];

function kycUrl(path: string) {
    return `/admin/kyc-file?path=${encodeURIComponent(path)}`;
}

function isImage(path: string) {
    return /\.(jpg|jpeg|png)$/i.test(path);
}

// ─── Approve / Reject ─────────────────────────────────────────────────────────

const confirmModal = ref<{
    open: boolean;
    action: 'approve' | 'reject' | null;
    orderPublicId: string | null;
    shopName: string;
    rejectionReason: string;
    reasonError: string;
}>({
    open: false,
    action: null,
    orderPublicId: null,
    shopName: '',
    rejectionReason: '',
    reasonError: '',
});

function openConfirm(action: 'approve' | 'reject', order: OrderItem) {
    if (action === 'approve') {
        router.visit(`/admin/orders/${order.public_id}`);
        return;
    }

    confirmModal.value = {
        open: true,
        action,
        orderPublicId: order.public_id,
        shopName: order.shop_name,
        rejectionReason: '',
        reasonError: '',
    };
}

function closeConfirm() {
    confirmModal.value = {
        open: false,
        action: null,
        orderPublicId: null,
        shopName: '',
        rejectionReason: '',
        reasonError: '',
    };
}

function submitAction() {
    if (!confirmModal.value.orderPublicId || !confirmModal.value.action) return;

    if (confirmModal.value.action === 'reject') {
        if (!confirmModal.value.rejectionReason.trim()) {
            confirmModal.value.reasonError =
                'Please provide a reason for rejection.';
            return;
        }
        confirmModal.value.reasonError = '';
    }

    const url = `/admin/orders/${confirmModal.value.orderPublicId}/${confirmModal.value.action}`;
    const payload =
        confirmModal.value.action === 'reject'
            ? { rejection_reason: confirmModal.value.rejectionReason.trim() }
            : {};

    router.post(url, payload, {
        onSuccess: () => {
            closeConfirm();
            const flash = page.props.toast as
                | { type: string; message: string }
                | undefined;
            if (flash) {
                switch (flash.type) {
                    case 'success':
                        toast.success(flash.message);
                        break;
                    case 'error':
                        toast.error(flash.message);
                        break;
                    default:
                        toast(flash.message);
                }
            }
        },
    });
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatDate(d: string | null) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function formatPrice(p: string | number) {
    return `₱${Number(p).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
}

function isExpired(expiresAt: string | null) {
    if (!expiresAt) return false;
    return new Date(expiresAt) < new Date();
}
</script>

<template>
    <Head title="Order Management" />
    <AdminLayout :breadcrumbs="breadcrumbs" title="Order Management">
        <div class="space-y-6 px-6">
            <!-- Stats -->
            <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
                <Card>
                    <CardContent class="p-4">
                        <p
                            class="mb-3 text-xs font-medium text-muted-foreground"
                        >
                            Total orders
                        </p>
                        <p class="text-2xl font-bold">
                            {{ stats.total.toLocaleString() }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4">
                        <p
                            class="mb-3 text-xs font-medium text-muted-foreground"
                        >
                            Paid orders
                        </p>
                        <p class="text-2xl font-bold">
                            {{ stats.paid.toLocaleString() }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4">
                        <p
                            class="mb-3 text-xs font-medium text-muted-foreground"
                        >
                            Pending orders
                        </p>
                        <p class="text-2xl font-bold">
                            {{ stats.pending.toLocaleString() }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4">
                        <p
                            class="mb-3 text-xs font-medium text-muted-foreground"
                        >
                            Rejected orders
                        </p>
                        <p class="text-2xl font-bold">
                            {{ stats.rejected.toLocaleString() }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4">
                        <p
                            class="mb-3 text-xs font-medium text-muted-foreground"
                        >
                            Revenue
                        </p>
                        <p class="text-2xl font-bold">
                            {{ formatPrice(stats.revenue) }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Orders table -->
            <DataTable
                v-model:options="serverOptions"
                :headers="headers"
                :items="tableItems"
                :total="orders.total"
                :loading="tableLoading"
                item-key="public_id"
                noun="orders"
                :empty-icon="ListOrdered"
                empty-message="No orders found."
                :min-width="1150"
                @reset="resetFilters"
            >
                <template #toolbar>
                    <div class="space-y-3">
                        <DataTableToolbar
                            v-model:search="search"
                            :status="status"
                            :statuses="statusOptions"
                            search-placeholder="Search reference, shop, owner, email..."
                            search-label="Search orders"
                            status-label="Filter by order status"
                            @update:status="setStatus"
                            @submit="filterTable"
                        >
                            <template #actions>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    class="border-slate-300 bg-slate-50 text-slate-700 hover:bg-slate-100 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-300"
                                    @click="resetFilters"
                                >
                                    <RefreshCcw class="mr-1.5 h-4 w-4" />
                                    Reset
                                </Button>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    class="border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 hover:text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300"
                                    @click="exportCsv"
                                >
                                    <Download class="mr-1.5 h-4 w-4" />
                                    Export CSV
                                </Button>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    class="border-blue-300 bg-blue-50 text-blue-700 hover:bg-blue-100 hover:text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300"
                                    :disabled="importing"
                                    @click="chooseCsvFile"
                                >
                                    <Upload class="mr-1.5 h-4 w-4" />
                                    {{
                                        importing
                                            ? 'Importing...'
                                            : 'Import CSV'
                                    }}
                                </Button>
                                <input
                                    ref="importInput"
                                    type="file"
                                    accept=".csv,text/csv"
                                    class="hidden"
                                    @change="importCsv"
                                />
                            </template>
                        </DataTableToolbar>
                        <div class="flex flex-wrap items-center gap-2">
                            <select
                                v-model="plan"
                                class="filter-input"
                                aria-label="Filter by plan"
                                @change="filterTable"
                            >
                                <option value="">All plans</option>
                                <option value="Basic">Basic</option>
                                <option value="Standard">Standard</option>
                                <option value="Premium">Premium</option>
                            </select>
                            <input
                                v-model="date"
                                type="date"
                                class="filter-input"
                                aria-label="Filter by order date"
                                @change="filterTable"
                            />
                        </div>
                    </div>
                </template>

                <template #item-reference="order">
                    <span class="font-mono text-xs text-muted-foreground">
                        {{ order.transaction_reference }}
                    </span>
                </template>

                <template #item-shop="order">
                    <p class="font-medium whitespace-nowrap">
                        {{ order.shop_name }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{ order.municipality }}, {{ order.barangay }}
                    </p>
                </template>

                <template #item-plan="order">
                    <p class="text-xs font-medium">
                        {{ order.plan_name ?? '—' }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{
                            order.billing_months
                                ? `${order.billing_months} mo`
                                : '—'
                        }}
                    </p>
                </template>

                <template #item-payment="order">
                    <span
                        v-if="order.payment_method"
                        class="rounded-full px-2 py-0.5 text-xs font-medium whitespace-nowrap"
                        :class="
                            paymentBadge[order.payment_method]?.cls ??
                            'bg-gray-100 text-gray-500'
                        "
                    >
                        {{
                            paymentBadge[order.payment_method]?.label ??
                            order.payment_method
                        }}
                    </span>
                    <span v-else class="text-xs text-muted-foreground">—</span>
                </template>

                <template #item-total="order">
                    <span class="font-medium whitespace-nowrap">{{
                        formatPrice(order.total_price)
                    }}</span>
                </template>

                <template #item-status="order">
                    <StatusPill :tone="statusTone(order.status)">
                        {{ statusLabel(order.status) }}
                    </StatusPill>
                </template>

                <template #item-created_at="order">
                    <span
                        class="text-xs whitespace-nowrap text-muted-foreground"
                        >{{ formatDate(order.created_at) }}</span
                    >
                </template>

                <!-- Actions -->
                <template #item-actions="order">
                    <RowActions
                        :label="`order ${order.transaction_reference}`"
                        :actions="[
                            {
                                label: 'View order',
                                icon: Eye,
                                onSelect: () =>
                                    router.visit(
                                        `/admin/orders/${order.public_id}`,
                                    ),
                            },
                            {
                                label: 'View KYC documents',
                                icon: FileText,
                                onSelect: () => openKyc(order),
                            },
                            {
                                label: 'Approve',
                                icon: ShieldCheck,
                                show: order.status === 'pending',
                                onSelect: () => openConfirm('approve', order),
                            },
                            {
                                label: 'Reject',
                                icon: ShieldX,
                                destructive: true,
                                show: order.status === 'pending',
                                onSelect: () => openConfirm('reject', order),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>

        <!-- ── KYC Modal ──────────────────────────────────────────────────────── -->
        <Teleport to="body">
            <div
                v-if="kycModal.open"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 backdrop-blur-sm"
                @click.self="closeKyc"
            >
                <div
                    class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl dark:bg-zinc-900"
                >
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-semibold">
                                KYC Documents
                            </h2>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                {{ kycModal.order?.shop_name }} —
                                {{ kycModal.order?.owner_name }}
                            </p>
                        </div>
                        <button
                            type="button"
                            class="flex h-7 w-7 items-center justify-center rounded-full hover:bg-muted"
                            @click="closeKyc"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div
                            v-for="doc in kycDocs(kycModal.order!)"
                            :key="doc.key"
                            class="overflow-hidden rounded-xl border border-border"
                        >
                            <div
                                class="flex items-center justify-between border-b border-border bg-muted/40 px-3 py-2"
                            >
                                <span class="text-xs font-medium">{{
                                    doc.label
                                }}</span>
                                <span
                                    v-if="!doc.path"
                                    class="text-xs text-muted-foreground"
                                    >Not uploaded</span
                                >
                                <a
                                    v-else
                                    :href="kycUrl(doc.path)"
                                    target="_blank"
                                    class="text-xs text-blue-500 hover:underline"
                                >
                                    Open
                                </a>
                            </div>

                            <div
                                class="flex h-40 items-center justify-center bg-muted/20"
                            >
                                <template v-if="!doc.path">
                                    <p class="text-xs text-muted-foreground">
                                        No file
                                    </p>
                                </template>
                                <template v-else-if="isImage(doc.path)">
                                    <img
                                        :src="kycUrl(doc.path)"
                                        class="h-full w-full object-contain"
                                        alt="KYC document"
                                    />
                                </template>
                                <template v-else>
                                    <div
                                        class="flex flex-col items-center gap-2 text-muted-foreground"
                                    >
                                        <FileText
                                            class="h-10 w-10 opacity-30"
                                        />
                                        <span class="text-xs"
                                            >PDF Document</span
                                        >
                                        <a
                                            :href="kycUrl(doc.path)"
                                            target="_blank"
                                            class="text-xs text-blue-500 hover:underline"
                                            >View PDF</a
                                        >
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ── Confirm Modal ──────────────────────────────────────────────────── -->
        <Teleport to="body">
            <div
                v-if="confirmModal.open"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 backdrop-blur-sm"
                @click.self="closeConfirm"
            >
                <div
                    class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl dark:bg-zinc-900"
                >
                    <div class="mb-4 flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                            :class="
                                confirmModal.action === 'approve'
                                    ? 'bg-emerald-100'
                                    : 'bg-red-100'
                            "
                        >
                            <ShieldCheck
                                v-if="confirmModal.action === 'approve'"
                                class="h-5 w-5 text-emerald-600"
                            />
                            <ShieldX v-else class="h-5 w-5 text-red-600" />
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold capitalize">
                                {{ confirmModal.action }} Order
                            </h2>
                            <p class="text-xs text-muted-foreground">
                                {{ confirmModal.shopName }}
                            </p>
                        </div>
                    </div>

                    <p class="mb-4 text-sm text-muted-foreground">
                        <template v-if="confirmModal.action === 'approve'">
                            This will activate the shop and make modules
                            available to the owner.
                        </template>
                        <template v-else>
                            This will reject the order. The shop will not be
                            activated and the owner will be notified with your
                            reason. A refund will be marked for processing.
                        </template>
                    </p>

                    <!-- Rejection reason (only shown when rejecting) -->
                    <div v-if="confirmModal.action === 'reject'" class="mb-5">
                        <label
                            class="mb-1.5 block text-xs font-medium text-foreground"
                        >
                            Reason for rejection
                            <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="confirmModal.rejectionReason"
                            rows="4"
                            placeholder="e.g. Incomplete or invalid KYC documents submitted. Please re-submit with valid BIR and DTI certificates."
                            class="w-full resize-none rounded-lg border border-border bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:ring-2 focus:ring-red-400 focus:outline-none"
                            :class="
                                confirmModal.reasonError ? 'border-red-400' : ''
                            "
                        />
                        <p
                            v-if="confirmModal.reasonError"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ confirmModal.reasonError }}
                        </p>
                        <p class="mt-1.5 text-xs text-muted-foreground">
                            This reason will be emailed to the shop owner along
                            with a refund notice.
                        </p>
                    </div>

                    <div class="flex justify-end gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            @click="closeConfirm"
                            >Cancel</Button
                        >
                        <Button
                            size="sm"
                            :class="
                                confirmModal.action === 'approve'
                                    ? 'bg-emerald-600 text-white hover:bg-emerald-700'
                                    : 'bg-red-600 text-white hover:bg-red-700'
                            "
                            @click="submitAction"
                        >
                            {{
                                confirmModal.action === 'approve'
                                    ? 'Approve'
                                    : 'Reject'
                            }}
                        </Button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>

<style scoped>
.filter-input {
    height: 36px;
    min-width: 150px;
    border: 1px solid var(--border);
    border-radius: 6px;
    background: var(--background);
    padding: 0 10px;
    color: var(--foreground);
    font-size: 13px;
    outline: none;
}

.filter-input:focus-visible {
    border-color: var(--ring);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--ring) 25%, transparent);
}
</style>
