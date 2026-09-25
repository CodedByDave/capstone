<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

<<<<<<< HEAD
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import {
    ListOrdered, BadgeDollarSign, Clock, CheckCircle2,
    AlertTriangle, Search, RefreshCcw, Eye,
} from 'lucide-vue-next'
=======
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
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)

// ─── Types ────────────────────────────────────────────────────────────────────

interface Module {
    id: number
    name: string
    price: string
}

interface Payment {
    id: number
    payment_method: string
    amount: string
    status: string
    paid_at: string | null
}

interface OrderItem {
    id: number
    shop_name: string
    owner_name: string
    email: string
    phone: string
    municipality: string
    barangay: string
    subscription_plan: string | null
    total_price: string
    status: string
    expires_at: string | null
    created_at: string
    modules: Module[]
    payments: Payment[]
}

interface Paginator {
<<<<<<< HEAD
    data: OrderItem[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    links: { url: string | null; label: string; active: boolean }[]
=======
    data: OrderItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
<<<<<<< HEAD
    orders:  Paginator
    stats:   {
        total: number
        paid: number
        pending: number
        expired: number
        revenue: number
    }
    filters: Record<string, string>
}>()
=======
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
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)

// ─── Flash ────────────────────────────────────────────────────────────────────

const page = usePage()

onMounted(() => {
    const flash = page.props.toast as { type: string; message: string } | undefined
    if (!flash) return
    switch (flash.type) {
        case 'success': toast.success(flash.message); break
        case 'error':   toast.error(flash.message);   break
        default:        toast(flash.message)
    }
})

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard',        href: '/admin/dashboard' },
    { title: 'Order Management', href: '/admin/orders' },
]

// ─── Filters ──────────────────────────────────────────────────────────────────

<<<<<<< HEAD
const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? 'all')
const plan   = ref(props.filters.plan   ?? 'all')
const date   = ref(props.filters.date   ?? '')

function applyFilters() {
    router.get('/admin/orders', {
        search: search.value || undefined,
        status: status.value !== 'all' ? status.value : undefined,
        plan:   plan.value   !== 'all' ? plan.value   : undefined,
        date:   date.value   || undefined,
    }, { preserveState: true, replace: true })
}

function resetFilters() {
    search.value = ''
    status.value = 'all'
    plan.value   = 'all'
    date.value   = ''
    router.get('/admin/orders', {}, { preserveState: true, replace: true })
=======
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
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatDate(d: string | null) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('en-PH', {
        year: 'numeric', month: 'short', day: 'numeric',
    })
}

function formatPrice(p: string | number) {
    return `₱${Number(p).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
}

function isExpired(expiresAt: string | null) {
    if (!expiresAt) return false
    return new Date(expiresAt) < new Date()
}

const planStyles: Record<string, { label: string; cls: string }> = {
    monthly:       { label: 'Monthly',     cls: 'bg-blue-100 text-blue-700'     },
    annually:      { label: 'Annually',    cls: 'bg-amber-100 text-amber-700'   },
}

function getPlanBadge(p: string | null) {
    if (!p) return { label: 'None', cls: 'bg-gray-100 text-gray-400' }
    return planStyles[p] ?? { label: p, cls: 'bg-gray-100 text-gray-500' }
}

const statusBadge: Record<string, string> = {
    paid:    'bg-green-100 text-green-700',
    pending: 'bg-amber-100 text-amber-700',
    failed:  'bg-red-100 text-red-600',
}
</script>

<template>
    <Head title="Order Management" />
    <AdminLayout :breadcrumbs="breadcrumbs" title="Order Management">
        <div class="px-6 space-y-6">

            <!-- Stats -->
<<<<<<< HEAD
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Total Orders</p>
                            <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <ListOrdered class="h-4 w-4 text-blue-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold">{{ stats.total.toLocaleString() }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Paid</p>
                            <div class="h-8 w-8 rounded-lg bg-green-100 flex items-center justify-center">
                                <CheckCircle2 class="h-4 w-4 text-green-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-green-600">{{ stats.paid.toLocaleString() }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Pending</p>
                            <div class="h-8 w-8 rounded-lg bg-amber-100 flex items-center justify-center">
                                <Clock class="h-4 w-4 text-amber-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-amber-600">{{ stats.pending.toLocaleString() }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Expired</p>
                            <div class="h-8 w-8 rounded-lg bg-red-100 flex items-center justify-center">
                                <AlertTriangle class="h-4 w-4 text-red-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-red-600">{{ stats.expired.toLocaleString() }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Total Revenue</p>
                            <div class="h-8 w-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                                <BadgeDollarSign class="h-4 w-4 text-emerald-600" />
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-emerald-600">{{ formatPrice(stats.revenue) }}</p>
=======
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
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
                    </CardContent>
                </Card>
            </div>

<<<<<<< HEAD
            <!-- Table card -->
            <Card>
                <CardHeader class="pb-3">
                    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
                        <CardTitle class="flex items-center gap-2">
                            <ListOrdered class="h-4 w-4 text-muted-foreground" />
                            All Orders
                        </CardTitle>
                        <Button size="sm" variant="ghost" @click="resetFilters">
                            <RefreshCcw class="h-4 w-4 mr-1.5" /> Reset
                        </Button>
                    </div>
                </CardHeader>

                <CardContent class="space-y-4">
                    <!-- Filters -->
                    <div class="flex flex-wrap gap-2">
                        <div class="relative flex-1 min-w-48">
                            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input
                                v-model="search"
                                placeholder="Search shop, owner, email..."
                                class="pl-8"
                                @keyup.enter="applyFilters"
                            />
                        </div>

                        <Select v-model="status" @update:model-value="applyFilters">
                            <SelectTrigger class="w-36">
                                <SelectValue placeholder="Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Status</SelectItem>
                                <SelectItem value="paid">Paid</SelectItem>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="failed">Failed</SelectItem>
                            </SelectContent>
                        </Select>

                        <Select v-model="plan" @update:model-value="applyFilters">
                            <SelectTrigger class="w-36">
                                <SelectValue placeholder="Plan" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Plans</SelectItem>
                                <SelectItem value="monthly">Monthly</SelectItem>
                                <SelectItem value="annually">Annually</SelectItem>
                            </SelectContent>
                        </Select>

=======
            <!-- Orders table -->
            <CardContent class="space-y-4">
                <div class="flex flex-wrap items-center gap-2 pt-6">
                    <div class="relative min-w-48 flex-1">
                        <Search
                            class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                        />
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
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

<<<<<<< HEAD
                    <!-- Table -->
                    <div class="rounded-lg border overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                                    <th class="text-left px-4 py-3 font-medium">#</th>
                                    <th class="text-left px-4 py-3 font-medium">Shop</th>
                                    <th class="text-left px-4 py-3 font-medium">Owner</th>
                                    <th class="text-left px-4 py-3 font-medium">Plan</th>
                                    <th class="text-left px-4 py-3 font-medium">Modules</th>
                                    <th class="text-left px-4 py-3 font-medium">Total</th>
                                    <th class="text-left px-4 py-3 font-medium">Status</th>
                                    <th class="text-left px-4 py-3 font-medium">Expires</th>
                                    <th class="text-left px-4 py-3 font-medium">Ordered</th>
                                    <th class="text-center px-4 py-3 font-medium">Actions</th>
=======
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
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
                                </tr>
                            </thead>
                            <tbody>
                                <tr
<<<<<<< HEAD
                                    v-for="order in orders.data" :key="order.id"
                                    class="border-b last:border-0 hover:bg-muted/20 transition-colors"
=======
                                    v-for="order in orders.data"
                                    :key="order.public_id"
                                    class="h-16 border-b transition-colors last:border-0 even:bg-muted/35 hover:bg-muted/65"
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
                                >
                                    <td class="px-4 py-3 font-mono text-xs text-muted-foreground">
                                        #{{ order.id }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium whitespace-nowrap">{{ order.shop_name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ order.municipality }}, {{ order.barangay }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium whitespace-nowrap">{{ order.owner_name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ order.email }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full font-medium"
                                            :class="getPlanBadge(order.subscription_plan).cls"
                                        >
                                            {{ getPlanBadge(order.subscription_plan).label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            <span
                                                v-for="mod in order.modules" :key="mod.id"
                                                class="text-xs px-1.5 py-0.5 rounded bg-muted text-muted-foreground"
                                            >
                                                {{ mod.name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-medium whitespace-nowrap">
                                        {{ formatPrice(order.total_price) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                                            :class="statusBadge[order.status] ?? 'bg-gray-100 text-gray-500'"
                                        >
                                            {{ order.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span
                                            v-if="order.expires_at"
                                            class="text-xs font-medium"
                                            :class="isExpired(order.expires_at)
                                                ? 'text-red-500'
                                                : 'text-muted-foreground'"
                                        >
                                            {{ formatDate(order.expires_at) }}
                                            <span v-if="isExpired(order.expires_at)" class="block">Expired</span>
                                        </span>
                                        <span v-else class="text-xs text-muted-foreground">—</span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground whitespace-nowrap">
                                        {{ formatDate(order.created_at) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <Button
                                            size="icon" variant="ghost"
                                            @click="router.visit(`/admin/orders/${order.id}`)"
                                        >
<<<<<<< HEAD
                                            <Eye class="h-4 w-4 text-blue-500" />
                                        </Button>
=======
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
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
                                    </td>
                                </tr>

                                <tr v-if="orders.data.length === 0">
                                    <td colspan="10" class="px-4 py-12 text-center text-sm text-muted-foreground">
                                        <ListOrdered class="h-10 w-10 mx-auto mb-2 opacity-20" />
                                        No orders found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
<<<<<<< HEAD
                    <div v-if="orders.last_page > 1" class="flex items-center justify-between pt-2">
                        <p class="text-xs text-muted-foreground">
                            Showing {{ orders.data.length }} of {{ orders.total }} orders
                        </p>
                        <div class="flex gap-1">
                            <Button
                                v-for="link in orders.links" :key="link.label"
                                size="sm"
                                :variant="link.active ? 'default' : 'outline'"
                                :disabled="!link.url"
                                class="h-7 min-w-7 text-xs"
                                @click="link.url && router.visit(link.url)"
                                v-html="link.label"
                            />
=======
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
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
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
<<<<<<< HEAD
                </CardContent>
            </Card>

=======
                </div>
            </CardContent>
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
        </div>
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
