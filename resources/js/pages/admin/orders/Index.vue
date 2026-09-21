<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    BadgeDollarSign,
    ChevronDown,
    Clock,
    Eye,
    FileText,
    ListOrdered,
    RefreshCcw,
    Search,
    ShieldCheck,
    ShieldX,
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
    links: { url: string | null; label: string; active: boolean }[];
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    orders: Paginator;
    stats: {
        total: number;
        paid: number;
        approved: number;
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
const status = ref(props.filters.status ?? 'all');
const plan = ref(props.filters.plan ?? 'all');
const date = ref(props.filters.date ?? '');

function applyFilters() {
    router.get(
        '/admin/orders',
        {
            search: search.value || undefined,
            status: status.value !== 'all' ? status.value : undefined,
            plan: plan.value !== 'all' ? plan.value : undefined,
            date: date.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}

function resetFilters() {
    search.value = '';
    status.value = 'all';
    plan.value = 'all';
    date.value = '';
    router.get('/admin/orders', {}, { preserveState: true, replace: true });
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
            <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Total
                            </p>
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100"
                            >
                                <ListOrdered class="h-4 w-4 text-blue-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold">
                            {{ stats.total.toLocaleString() }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Approved
                            </p>
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100"
                            >
                                <ShieldCheck class="h-4 w-4 text-emerald-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-emerald-600">
                            {{ stats.approved.toLocaleString() }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Pending
                            </p>
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100"
                            >
                                <Clock class="h-4 w-4 text-amber-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-amber-600">
                            {{ stats.pending.toLocaleString() }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Rejected
                            </p>
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-100"
                            >
                                <ShieldX class="h-4 w-4 text-red-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-red-600">
                            {{ stats.rejected.toLocaleString() }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Revenue
                            </p>
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-100"
                            >
                                <BadgeDollarSign
                                    class="h-4 w-4 text-green-600"
                                />
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-green-600">
                            {{ formatPrice(stats.revenue) }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Table card -->
            <Card>
                <CardHeader class="pb-3">
                    <div
                        class="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center"
                    >
                        <CardTitle class="flex items-center gap-2">
                            <ListOrdered
                                class="h-4 w-4 text-muted-foreground"
                            />
                            All Orders
                        </CardTitle>
                        <Button size="sm" variant="ghost" @click="resetFilters">
                            <RefreshCcw class="mr-1.5 h-4 w-4" /> Reset
                        </Button>
                    </div>
                </CardHeader>

                <CardContent class="space-y-4">
                    <!-- Filters -->
                    <div class="flex flex-wrap gap-2">
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

                        <Select
                            v-model="status"
                            @update:model-value="applyFilters"
                        >
                            <SelectTrigger class="w-36">
                                <SelectValue placeholder="Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Status</SelectItem>
                                <SelectItem value="paid">Paid</SelectItem>
                                <SelectItem value="approved"
                                    >Approved</SelectItem
                                >
                                <SelectItem value="rejected"
                                    >Rejected</SelectItem
                                >
                                <SelectItem value="pending">Pending</SelectItem>
                            </SelectContent>
                        </Select>

                        <Select
                            v-model="plan"
                            @update:model-value="applyFilters"
                        >
                            <SelectTrigger class="w-36">
                                <SelectValue placeholder="Plan" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Plans</SelectItem>
                                <SelectItem value="Basic">Basic</SelectItem>
                                <SelectItem value="Standard"
                                    >Standard</SelectItem
                                >
                                <SelectItem value="Premium">Premium</SelectItem>
                            </SelectContent>
                        </Select>

                        <Input
                            v-model="date"
                            type="date"
                            class="w-40"
                            @change="applyFilters"
                        />
                    </div>

                    <!-- Table -->
                    <div class="overflow-visible rounded-lg border">
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="border-b bg-muted/40 text-xs text-muted-foreground"
                                >
                                    <th class="px-4 py-3 text-left font-medium">
                                        Transaction Reference
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Shop
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Owner
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Plan
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Modules
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Payment
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Total
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Status
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Expires
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Ordered
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
                                    class="border-b transition-colors last:border-0 hover:bg-muted/20"
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

                                            <!-- Approve (only for paid orders) -->
                                            <Button
                                                v-if="order.status === 'paid'"
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

                                            <!-- Reject (only for paid orders) -->
                                            <Button
                                                v-if="order.status === 'paid'"
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
                    <div
                        v-if="orders.last_page > 1"
                        class="flex items-center justify-between pt-2"
                    >
                        <p class="text-xs text-muted-foreground">
                            Showing {{ orders.data.length }} of
                            {{ orders.total }} orders
                        </p>
                        <div class="flex gap-1">
                            <Button
                                v-for="link in orders.links"
                                :key="link.label"
                                size="sm"
                                :variant="link.active ? 'default' : 'outline'"
                                :disabled="!link.url"
                                class="h-7 min-w-7 text-xs"
                                @click="link.url && router.visit(link.url)"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>
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
