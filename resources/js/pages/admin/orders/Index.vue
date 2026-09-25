<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    ArrowDown,
    ArrowUp,
    ArrowUpDown,
    ChevronDown,
    Download,
    Eye,
    FileText,
    ListOrdered,
    RefreshCcw,
    Search,
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
    from: number | null;
    to: number | null;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
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
    filters: Record<string, string>;
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

// ─── Filters ──────────────────────────────────────────────────────────────────

const search = ref(props.filters.search ?? '');
const sortBy = ref(props.filters.sort_by ?? '');
const sortDirection = ref(
    props.filters.sort_direction === 'desc' ? 'desc' : 'asc',
);
const status = ref(
    props.filters.status && props.filters.status !== 'all'
        ? props.filters.status
        : '',
);
const plan = ref(
    props.filters.plan && props.filters.plan !== 'all'
        ? props.filters.plan
        : '',
);
const date = ref(props.filters.date ?? '');
const rowsPerPage = ref(props.orders.per_page);
const importInput = ref<HTMLInputElement | null>(null);
const importing = ref(false);
const showRowsSelector = ref(false);

function applyFilters() {
    router.get(
        '/admin/orders',
        {
            search: search.value || undefined,
            sort_by: sortBy.value || undefined,
            sort_direction: sortBy.value ? sortDirection.value : undefined,
            status: status.value || undefined,
            plan: plan.value || undefined,
            date: date.value || undefined,
            per_page: rowsPerPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function resetFilters() {
    search.value = '';
    sortBy.value = '';
    sortDirection.value = 'asc';
    status.value = '';
    plan.value = '';
    date.value = '';
    router.get(
        '/admin/orders',
        { per_page: rowsPerPage.value },
        { preserveState: true, replace: true },
    );
}

function toggleSort(column: 'shop' | 'owner' | 'total' | 'expires') {
    if (sortBy.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortDirection.value = 'asc';
    }

    applyFilters();
}

function selectRowsPerPage(size: number) {
    rowsPerPage.value = size;
    showRowsSelector.value = false;
    applyFilters();
}

function visitPage(url: string | null) {
    if (!url) return;

    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
    });
}

function exportCsv() {
    const params = new URLSearchParams();
    const filters = {
        search: search.value || undefined,
        sort_by: sortBy.value || undefined,
        sort_direction: sortBy.value ? sortDirection.value : undefined,
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
    showRowsSelector.value = false;
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

const statusBadge: Record<string, string> = {
    paid: 'bg-green-100 text-green-700',
    approved: 'bg-emerald-100 text-emerald-700',
    rejected: 'bg-red-100 text-red-600',
    pending: 'bg-amber-100 text-amber-700',
    failed: 'bg-red-100 text-red-600',
};
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
            <CardContent class="space-y-4">
                <div class="flex flex-wrap items-center gap-2 pt-6">
                    <div class="relative min-w-48 flex-1">
                        <Search
                            class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search reference, shop, owner, email..."
                            class="pl-8"
                            @keyup.enter="applyFilters"
                        />
                    </div>
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
                        {{ importing ? 'Importing...' : 'Import CSV' }}
                    </Button>
                    <input
                        ref="importInput"
                        type="file"
                        accept=".csv,text/csv"
                        class="hidden"
                        @change="importCsv"
                    />
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-lg border">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1470px] text-sm">
                            <thead>
                                <tr
                                    class="h-[92px] border-b bg-muted text-xs text-muted-foreground"
                                >
                                    <th class="px-4 py-3 text-left font-medium">
                                        Transaction Reference
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        <button
                                            type="button"
                                            class="sortable-column"
                                            :aria-label="`Sort shops ${sortBy === 'shop' && sortDirection === 'asc' ? 'descending' : 'ascending'}`"
                                            @click="toggleSort('shop')"
                                        >
                                            <span>Shop</span>
                                            <ArrowUp
                                                v-if="
                                                    sortBy === 'shop' &&
                                                    sortDirection === 'asc'
                                                "
                                                class="h-3.5 w-3.5"
                                            />
                                            <ArrowDown
                                                v-else-if="sortBy === 'shop'"
                                                class="h-3.5 w-3.5"
                                            />
                                            <ArrowUpDown
                                                v-else
                                                class="h-3.5 w-3.5 opacity-60"
                                            />
                                        </button>
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        <button
                                            type="button"
                                            class="sortable-column"
                                            :aria-label="`Sort owners ${sortBy === 'owner' && sortDirection === 'asc' ? 'descending' : 'ascending'}`"
                                            @click="toggleSort('owner')"
                                        >
                                            <span>Owner</span>
                                            <ArrowUp
                                                v-if="
                                                    sortBy === 'owner' &&
                                                    sortDirection === 'asc'
                                                "
                                                class="h-3.5 w-3.5"
                                            />
                                            <ArrowDown
                                                v-else-if="sortBy === 'owner'"
                                                class="h-3.5 w-3.5"
                                            />
                                            <ArrowUpDown
                                                v-else
                                                class="h-3.5 w-3.5 opacity-60"
                                            />
                                        </button>
                                    </th>
                                    <th class="px-3 py-2 text-left font-medium">
                                        <div class="column-filter">
                                            <span>Plan</span>
                                            <select
                                                v-model="plan"
                                                class="column-filter-input"
                                                aria-label="Filter by plan"
                                                @change="applyFilters"
                                            >
                                                <option value="">
                                                    All plans
                                                </option>
                                                <option value="Basic">
                                                    Basic
                                                </option>
                                                <option value="Standard">
                                                    Standard
                                                </option>
                                                <option value="Premium">
                                                    Premium
                                                </option>
                                            </select>
                                        </div>
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Modules
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Payment
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        <button
                                            type="button"
                                            class="sortable-column"
                                            :aria-label="`Sort totals ${sortBy === 'total' && sortDirection === 'asc' ? 'descending' : 'ascending'}`"
                                            @click="toggleSort('total')"
                                        >
                                            <span>Total</span>
                                            <ArrowUp
                                                v-if="
                                                    sortBy === 'total' &&
                                                    sortDirection === 'asc'
                                                "
                                                class="h-3.5 w-3.5"
                                            />
                                            <ArrowDown
                                                v-else-if="sortBy === 'total'"
                                                class="h-3.5 w-3.5"
                                            />
                                            <ArrowUpDown
                                                v-else
                                                class="h-3.5 w-3.5 opacity-60"
                                            />
                                        </button>
                                    </th>
                                    <th class="px-3 py-2 text-left font-medium">
                                        <div class="column-filter">
                                            <span>Status</span>
                                            <select
                                                v-model="status"
                                                class="column-filter-input"
                                                aria-label="Filter by order status"
                                                @change="applyFilters"
                                            >
                                                <option value="">
                                                    All statuses
                                                </option>
                                                <option value="paid">
                                                    Paid
                                                </option>
                                                <option value="approved">
                                                    Approved
                                                </option>
                                                <option value="pending">
                                                    Pending
                                                </option>
                                                <option value="rejected">
                                                    Rejected
                                                </option>
                                                <option value="failed">
                                                    Failed
                                                </option>
                                            </select>
                                        </div>
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        <button
                                            type="button"
                                            class="sortable-column"
                                            :aria-label="`Sort expiration dates ${sortBy === 'expires' && sortDirection === 'asc' ? 'descending' : 'ascending'}`"
                                            @click="toggleSort('expires')"
                                        >
                                            <span>Expires</span>
                                            <ArrowUp
                                                v-if="
                                                    sortBy === 'expires' &&
                                                    sortDirection === 'asc'
                                                "
                                                class="h-3.5 w-3.5"
                                            />
                                            <ArrowDown
                                                v-else-if="sortBy === 'expires'"
                                                class="h-3.5 w-3.5"
                                            />
                                            <ArrowUpDown
                                                v-else
                                                class="h-3.5 w-3.5 opacity-60"
                                            />
                                        </button>
                                    </th>
                                    <th class="px-3 py-2 text-left font-medium">
                                        <div class="column-filter">
                                            <span>Ordered</span>
                                            <input
                                                v-model="date"
                                                type="date"
                                                class="column-filter-input"
                                                aria-label="Filter by order date"
                                                @change="applyFilters"
                                            />
                                        </div>
                                    </th>
                                    <th
                                        class="px-4 py-3 text-center font-medium"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="order in orders.data"
                                    :key="order.public_id"
                                    class="h-16 border-b transition-colors last:border-0 even:bg-muted/35 hover:bg-muted/65"
                                >
                                    <td
                                        class="px-4 py-3 font-mono text-xs text-muted-foreground"
                                    >
                                        {{ order.transaction_reference }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <p
                                            class="font-medium whitespace-nowrap"
                                        >
                                            {{ order.shop_name }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ order.municipality }},
                                            {{ order.barangay }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p
                                            class="font-medium whitespace-nowrap"
                                        >
                                            {{ order.owner_name }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ order.email }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="text-xs font-medium">
                                            {{ order.plan_name ?? '—' }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{
                                                order.billing_months
                                                    ? `${order.billing_months} mo`
                                                    : '—'
                                            }}
                                        </p>
                                    </td>

                                    <!-- Modules dropdown -->
                                    <td class="px-4 py-3">
                                        <div class="relative" @click.stop>
                                            <button
                                                type="button"
                                                class="flex items-center gap-1.5 rounded-md bg-muted px-2.5 py-1.5 text-xs font-medium whitespace-nowrap transition-colors hover:bg-muted/70"
                                                @click="
                                                    toggleModules(
                                                        order.public_id,
                                                    )
                                                "
                                            >
                                                {{ order.modules.length }}
                                                module{{
                                                    order.modules.length !== 1
                                                        ? 's'
                                                        : ''
                                                }}
                                                <ChevronDown
                                                    class="h-3 w-3 transition-transform duration-200"
                                                    :class="
                                                        expandedOrder ===
                                                        order.public_id
                                                            ? 'rotate-180'
                                                            : ''
                                                    "
                                                />
                                            </button>
                                            <div
                                                v-if="
                                                    expandedOrder ===
                                                    order.public_id
                                                "
                                                class="absolute left-0 z-20 mt-1 min-w-44 rounded-lg border border-border bg-card py-1 shadow-lg"
                                            >
                                                <div
                                                    v-for="mod in order.modules"
                                                    :key="mod.id"
                                                    class="flex items-center justify-between gap-6 px-3 py-1.5 text-xs hover:bg-muted/50"
                                                >
                                                    <span class="font-medium">{{
                                                        mod.name
                                                    }}</span>
                                                </div>
                                                <div
                                                    v-if="
                                                        order.modules.length ===
                                                        0
                                                    "
                                                    class="px-3 py-2 text-xs text-muted-foreground"
                                                >
                                                    No modules
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <span
                                            v-if="order.payment_method"
                                            class="rounded-full px-2 py-0.5 text-xs font-medium"
                                            :class="
                                                paymentBadge[
                                                    order.payment_method
                                                ]?.cls ??
                                                'bg-gray-100 text-gray-500'
                                            "
                                        >
                                            {{
                                                paymentBadge[
                                                    order.payment_method
                                                ]?.label ?? order.payment_method
                                            }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-xs text-muted-foreground"
                                            >—</span
                                        >
                                    </td>

                                    <td
                                        class="px-4 py-3 font-medium whitespace-nowrap"
                                    >
                                        {{ formatPrice(order.total_price) }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <span
                                            class="rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                                            :class="
                                                statusBadge[order.status] ??
                                                'bg-gray-100 text-gray-500'
                                            "
                                        >
                                            {{ order.status }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span
                                            v-if="order.expires_at"
                                            class="text-xs font-medium"
                                            :class="
                                                isExpired(order.expires_at)
                                                    ? 'text-red-500'
                                                    : 'text-muted-foreground'
                                            "
                                        >
                                            {{ formatDate(order.expires_at) }}
                                            <span
                                                v-if="
                                                    isExpired(order.expires_at)
                                                "
                                                class="block"
                                                >Expired</span
                                            >
                                        </span>
                                        <span
                                            v-else
                                            class="text-xs text-muted-foreground"
                                            >—</span
                                        >
                                    </td>

                                    <td
                                        class="px-4 py-3 text-xs whitespace-nowrap text-muted-foreground"
                                    >
                                        {{ formatDate(order.created_at) }}
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-3">
                                        <div
                                            class="flex items-center justify-center gap-1"
                                        >
                                            <!-- View order detail -->
                                            <Button
                                                size="icon"
                                                variant="ghost"
                                                @click="
                                                    router.visit(
                                                        `/admin/orders/${order.public_id}`,
                                                    )
                                                "
                                            >
                                                <Eye
                                                    class="h-4 w-4 text-blue-500"
                                                />
                                            </Button>

                                            <!-- View KYC files -->
                                            <Button
                                                size="icon"
                                                variant="ghost"
                                                title="View KYC Documents"
                                                @click="openKyc(order)"
                                            >
                                                <FileText
                                                    class="h-4 w-4 text-violet-500"
                                                />
                                            </Button>

                                            <!-- Approve (only while awaiting admin review) -->
                                            <Button
                                                v-if="
                                                    order.status === 'pending'
                                                "
                                                size="icon"
                                                variant="ghost"
                                                title="Approve"
                                                @click="
                                                    openConfirm(
                                                        'approve',
                                                        order,
                                                    )
                                                "
                                            >
                                                <ShieldCheck
                                                    class="h-4 w-4 text-emerald-500"
                                                />
                                            </Button>

                                            <!-- Reject (only while awaiting admin review) -->
                                            <Button
                                                v-if="
                                                    order.status === 'pending'
                                                "
                                                size="icon"
                                                variant="ghost"
                                                title="Reject"
                                                @click="
                                                    openConfirm('reject', order)
                                                "
                                            >
                                                <ShieldX
                                                    class="h-4 w-4 text-red-500"
                                                />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="orders.data.length === 0">
                                    <td
                                        colspan="11"
                                        class="px-4 py-12 text-center text-sm text-muted-foreground"
                                    >
                                        <ListOrdered
                                            class="mx-auto mb-2 h-10 w-10 opacity-20"
                                        />
                                        No orders found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="order-table-footer">
                        <div class="pagination__rows-per-page">
                            Rows per page:
                            <div class="order-rows-selector" @click.stop>
                                <button
                                    type="button"
                                    class="rows-input__wrapper"
                                    aria-label="Select rows per page"
                                    @click="
                                        showRowsSelector = !showRowsSelector
                                    "
                                >
                                    <span>{{ rowsPerPage }}</span>
                                    <span class="triangle" />
                                </button>
                                <ul
                                    class="select-items"
                                    :class="{ show: showRowsSelector }"
                                >
                                    <li
                                        v-for="size in [5, 10, 15, 20, 50, 100]"
                                        :key="size"
                                        :class="{
                                            selected: size === rowsPerPage,
                                        }"
                                        @click="selectRowsPerPage(size)"
                                    >
                                        {{ size }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="pagination__items-index">
                            {{ orders.from ?? 0 }}–{{ orders.to ?? 0 }} out of
                            {{ orders.total }}
                        </div>
                        <button
                            type="button"
                            class="previous-page__click-button"
                            :class="{ 'first-page': !orders.links[0]?.url }"
                            aria-label="Previous page"
                            :disabled="!orders.links[0]?.url"
                            @click="visitPage(orders.links[0]?.url ?? null)"
                        >
                            <span class="arrow arrow-right" />
                        </button>
                        <div class="buttons-pagination">
                            <button
                                v-for="link in orders.links.slice(1, -1)"
                                :key="link.label"
                                type="button"
                                class="item"
                                :class="{
                                    button: link.url !== null,
                                    active: link.active,
                                    omission: link.url === null,
                                }"
                                :disabled="!link.url || link.active"
                                @click="visitPage(link.url)"
                            >
                                {{ link.label }}
                            </button>
                        </div>
                        <button
                            type="button"
                            class="next-page__click-button"
                            :class="{
                                'last-page':
                                    !orders.links[orders.links.length - 1]?.url,
                            }"
                            aria-label="Next page"
                            :disabled="
                                !orders.links[orders.links.length - 1]?.url
                            "
                            @click="
                                visitPage(
                                    orders.links[orders.links.length - 1]
                                        ?.url ?? null,
                                )
                            "
                        >
                            <span class="arrow arrow-left" />
                        </button>
                    </div>
                </div>
            </CardContent>
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
.sortable-column {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: inherit;
    font: inherit;
}

.sortable-column:hover {
    color: var(--foreground);
}

.column-filter {
    display: flex;
    min-width: 0;
    flex-direction: column;
    gap: 6px;
    text-align: left;
}

.column-filter-input {
    height: 30px;
    width: 100%;
    min-width: 82px;
    border: 1px solid var(--border);
    border-radius: 6px;
    background: var(--background);
    padding: 0 7px;
    color: var(--foreground);
    font-size: 11px;
    font-weight: 400;
    outline: none;
}

.column-filter-input:focus {
    border-color: var(--ring);
    box-shadow: 0 0 0 2px color-mix(in srgb, var(--ring) 20%, transparent);
}

.order-table-footer {
    box-sizing: border-box;
    display: flex;
    height: 56px;
    width: 100%;
    align-items: center;
    justify-content: flex-end;
    border-top: 1px solid var(--border);
    background: var(--background);
    padding: 0 16px;
    color: var(--muted-foreground);
    font-size: 12px;
}

.pagination__rows-per-page {
    display: flex;
    align-items: center;
}

.pagination__items-index {
    margin: 0 20px 0 10px;
}

.order-rows-selector {
    position: relative;
    display: inline-block;
    min-width: 45px;
    margin: 0 10px;
}

.rows-input__wrapper {
    display: flex;
    height: 20px;
    width: 100%;
    cursor: pointer;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid var(--muted-foreground);
    padding: 0 5px;
}

.triangle {
    display: inline-block;
    width: 0;
    height: 0;
    border-top: 6px solid var(--muted-foreground);
    border-right: 6px solid transparent;
    border-left: 6px solid transparent;
}

.select-items {
    position: absolute;
    bottom: 20px;
    left: 0;
    z-index: 30;
    display: none;
    width: 100%;
    margin: 0;
    padding: 0;
    list-style: none;
    text-align: left;
    box-shadow:
        0 5px 5px -3px rgb(0 0 0 / 20%),
        0 8px 10px 1px rgb(0 0 0 / 14%),
        0 3px 14px 2px rgb(0 0 0 / 12%);
}

.select-items.show {
    display: block;
}

.select-items li {
    cursor: pointer;
    background: var(--background);
    padding: 5px;
}

.select-items li.selected {
    background: #42b883;
    color: white;
}

.buttons-pagination {
    display: flex;
    box-sizing: border-box;
    padding: 0;
    border-radius: 4px;
}

.buttons-pagination .item {
    box-sizing: border-box;
    min-width: 21.6px;
    cursor: pointer;
    border-top: 1px solid var(--border);
    border-right: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    line-height: 21.6px;
    text-align: center;
}

.buttons-pagination .item:first-of-type {
    border-left: 1px solid var(--border);
    border-radius: 4px 0 0 4px;
}

.buttons-pagination .item:last-of-type {
    border-radius: 0 4px 4px 0;
}

.buttons-pagination .item.active {
    border-color: #42b883;
    background: #42b883;
    color: white;
}

.buttons-pagination .item:disabled:not(.active) {
    cursor: default;
}

.previous-page__click-button,
.next-page__click-button {
    margin: 0 5px;
    cursor: pointer;
}

.previous-page__click-button .arrow,
.next-page__click-button .arrow {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-top: 2px solid var(--foreground);
    border-left: 2px solid var(--foreground);
}

.previous-page__click-button .arrow-right {
    transform: rotate(135deg);
}

.next-page__click-button .arrow-left {
    transform: rotate(-45deg);
}

.previous-page__click-button.first-page,
.next-page__click-button.last-page {
    cursor: not-allowed;
}

.previous-page__click-button.first-page .arrow,
.next-page__click-button.last-page .arrow {
    border-color: #e0e0e0;
}
</style>
