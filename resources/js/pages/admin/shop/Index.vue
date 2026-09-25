<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import { ref, onMounted } from 'vue'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import {
    AlertDialog, AlertDialogContent, AlertDialogHeader,
    AlertDialogTitle, AlertDialogDescription, AlertDialogFooter,
    AlertDialogCancel, AlertDialogAction,
} from '@/components/ui/alert-dialog'
import {
<<<<<<< HEAD
    Store, User, CheckCircle, ShieldOff, ShieldCheck,
    Eye, Pencil, Trash2, Search, RefreshCcw,
} from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface ShopOwner {
    id: number
    name: string
    email: string
    phone: string | null
}
=======
    Archive,
    ArchiveRestore,
    ArrowDown,
    ArrowUp,
    ArrowUpDown,
    Download,
    Eye,
    Pencil,
    RefreshCcw,
    RotateCcw,
    Search,
    ShieldCheck,
    ShieldOff,
    Trash2,
} from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import Vue3EasyDataTable, {
    type Header,
    type ServerOptions,
} from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)

interface ShopItem {
    id: number
    shop_name: string
    branch_name: string | null
    phone: string
    municipality: string
    barangay: string
    status: string
    disable_reason: string | null
    created_at: string
    owner: ShopOwner | null
    subscription_plan: string | null
    expires_at: string | null
    is_expired: boolean
    is_expiring_soon: boolean
}

interface Paginator {
    data: ShopItem[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    links: { url: string | null; label: string; active: boolean }[]
}

// ─── Props ────────────────────────────────────────────────────────────────────

const { shops, stats, filters } = defineProps<{
    shops:   Paginator
    stats:   { today: number; total: number; active: number }
    filters: Record<string, string>
}>()

// ─── Flash ────────────────────────────────────────────────────────────────────

<<<<<<< HEAD
const page = usePage()
=======
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const plan = ref(props.filters.plan ?? '');
const compliance = ref(props.filters.compliance ?? '');
const activity = ref(props.filters.activity ?? '');
const subscription = ref(props.filters.subscription ?? '');
const tableLoading = ref(false);
const showMoreStats = ref(false);
const archived = computed(() => Boolean(props.filters.trashed));
const selectedIds = ref<number[]>([]);
const disableDialog = ref(false);
const disableReason = ref('');
const disableTarget = ref<ShopItem | null>(null);
const confirmDialog = ref(false);
const confirmTarget = ref<ShopItem | null>(null);
const confirmAction = ref<'archive' | 'restore' | 'delete'>('archive');

type ShopSortColumn =
    | 'shop_name'
    | 'owner'
    | 'health_status'
    | 'compliance_status'
    | 'subscription'
    | 'last_activity_at'
    | 'status';

const headers: Header[] = [
    { text: '', value: 'selection', width: 48 },
    { text: 'Shop', value: 'shop_name', width: 210 },
    { text: 'Owner', value: 'owner', width: 205 },
    { text: 'Health', value: 'health_status', width: 145 },
    { text: 'Compliance', value: 'compliance_status', width: 165 },
    { text: 'Subscription', value: 'subscription', width: 190 },
    {
        text: 'Last activity',
        value: 'last_activity_at',
        width: 160,
    },
    { text: 'Status', value: 'status', width: 145 },
    { text: 'Actions', value: 'actions', width: 180 },
];

const serverOptions = ref<ServerOptions>({
    page: props.shops.current_page,
    rowsPerPage: props.shops.per_page,
    sortBy: props.filters.sort_by ?? 'created_at',
    sortType: props.filters.sort_direction === 'asc' ? 'asc' : 'desc',
});

function toggleSort(column: ShopSortColumn) {
    if (serverOptions.value.sortBy === column) {
        serverOptions.value.sortType =
            serverOptions.value.sortType === 'asc' ? 'desc' : 'asc';
        return;
    }

    serverOptions.value.sortBy = column;
    serverOptions.value.sortType = 'asc';
}

function sortIcon(column: ShopSortColumn) {
    if (serverOptions.value.sortBy !== column) return ArrowUpDown;

    return serverOptions.value.sortType === 'asc' ? ArrowUp : ArrowDown;
}

const allSelected = computed(
    () =>
        props.shops.data.length > 0 &&
        props.shops.data.every((shop) => selectedIds.value.includes(shop.id)),
);

const tableItems = computed(() =>
    props.shops.data.map((shop) => ({
        ...shop,
        subscription: shop.subscription_plan ?? 'No plan',
        actions: '',
    })),
);

const statCards = computed(() => [
    { label: 'Total shops', value: props.stats.total },
    { label: 'Active shops', value: props.stats.active },
    { label: 'Disabled shops', value: props.stats.disabled },
    { label: 'Compliance issues', value: props.stats.compliance_issues },
    { label: 'Needs attention', value: props.stats.needs_attention },
]);

const secondaryStats = computed(() => [
    { label: 'Registered today', value: props.stats.today },
    { label: 'Archived', value: props.stats.archived },
    { label: 'Expired permits', value: props.stats.expired_permits },
    { label: 'Expiring permits', value: props.stats.expiring_permits },
    { label: 'Inactive 30+ days', value: props.stats.inactive },
    { label: 'Expired plans', value: props.stats.expired_subscriptions },
]);
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)

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
    { title: 'Dashboard',       href: '/admin/dashboard' },
    { title: 'Shop Management', href: '/admin/shop' },
]

// ─── Filters ──────────────────────────────────────────────────────────────────

const search       = ref(filters.search ?? '')
const statusFilter = ref(filters.status ?? 'all')
const planFilter   = ref(filters.plan   ?? 'all')

function applyFilters() {
    router.get('/admin/shop', {
        search: search.value       || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
        plan:   planFilter.value   !== 'all' ? planFilter.value   : undefined,
    }, { preserveState: true, replace: true })
}

function resetFilters() {
    search.value       = ''
    statusFilter.value = 'all'
    planFilter.value   = 'all'
    router.get('/admin/shop', {}, { preserveState: true, replace: true })
}

// ─── Disable dialog ───────────────────────────────────────────────────────────

const disableDialogOpen = ref(false)
const selectedShop      = ref<ShopItem | null>(null)
const disableReason     = ref('')

function openDisableDialog(shop: ShopItem) {
    selectedShop.value      = shop
    disableReason.value     = ''
    disableDialogOpen.value = true
}

<<<<<<< HEAD
function confirmDisable() {
    if (!selectedShop.value) return
    router.post(`/admin/shop/${selectedShop.value.id}/disable`, { reason: disableReason.value }, {
=======
function exportCsv() {
    const params = new URLSearchParams();
    Object.entries(query()).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
            params.set(key, String(value));
        }
    });
    window.location.assign(`/admin/shop/export?${params.toString()}`);
}

function toggleAll() {
    selectedIds.value = allSelected.value
        ? []
        : props.shops.data.map((shop) => shop.id);
}

function toggleOne(id: number) {
    if (selectedIds.value.includes(id)) {
        selectedIds.value = selectedIds.value.filter(
            (selectedId) => selectedId !== id,
        );
        return;
    }

    selectedIds.value.push(id);
}

function openDisable(shop: ShopItem | null = null) {
    disableTarget.value = shop;
    disableReason.value = '';
    disableDialog.value = true;
}

function disableShops() {
    const url = disableTarget.value
        ? `/admin/shop/${disableTarget.value.public_id}/disable`
        : '/admin/shop/bulk-action';
    const data = disableTarget.value
        ? { reason: disableReason.value }
        : {
              ids: selectedIds.value,
              action: 'disable',
              reason: disableReason.value,
          };

    router.post(
        url,
        data,
        actionOptions(() => {
            disableDialog.value = false;
        }),
    );
}

function bulkAction(action: 'archive' | 'enable' | 'restore') {
    if (!selectedIds.value.length) return;
    router.post(
        '/admin/shop/bulk-action',
        { ids: selectedIds.value, action },
        actionOptions(),
    );
}

function enableShop(shop: ShopItem) {
    router.post(`/admin/shop/${shop.public_id}/enable`, {}, actionOptions());
}

function openConfirm(shop: ShopItem, action: 'archive' | 'restore' | 'delete') {
    confirmTarget.value = shop;
    confirmAction.value = action;
    confirmDialog.value = true;
}

function runConfirmedAction() {
    if (!confirmTarget.value) return;
    const shop = confirmTarget.value;

    if (confirmAction.value === 'restore') {
        router.post(
            `/admin/shop/archive/${shop.public_id}/restore`,
            {},
            actionOptions(() => {
                confirmDialog.value = false;
            }),
        );
        return;
    }

    const url =
        confirmAction.value === 'delete'
            ? `/admin/shop/archive/${shop.public_id}`
            : `/admin/shop/${shop.public_id}`;
    router.delete(
        url,
        actionOptions(() => {
            confirmDialog.value = false;
        }),
    );
}

function actionOptions(after?: () => void) {
    return {
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
        preserveScroll: true,
        onSuccess: () => {
            disableDialogOpen.value = false
            selectedShop.value      = null
            disableReason.value     = ''
            toast.success('Shop has been disabled.')
        },
        onError: () => toast.error('Failed to disable shop.'),
    })
}

function enableShop(id: number) {
    router.post(`/admin/shop/${id}/enable`, {}, {
        preserveScroll: true,
        onSuccess: () => toast.success('Shop has been enabled.'),
        onError:   () => toast.error('Failed to enable shop.'),
    })
}

// ─── Archive ──────────────────────────────────────────────────────────────────

const archiveId   = ref<number | null>(null)
const archiveName = ref('')
const archiveOpen = ref(false)

function openArchive(shop: ShopItem) {
    archiveId.value   = shop.id
    archiveName.value = shop.shop_name
    archiveOpen.value = true
}

function cancelArchive() {
    archiveOpen.value = false
    setTimeout(() => { archiveId.value = null; archiveName.value = '' }, 200)
}

function confirmArchive() {
    if (!archiveId.value) return
    router.delete(`/admin/shop/${archiveId.value}`, {
        preserveScroll: true,
        onSuccess: () => { toast.success('Shop archived.'); archiveOpen.value = false },
        onError:   () => toast.error('Failed to archive shop.'),
    })
}

// ─── Plan badge ───────────────────────────────────────────────────────────────

const planStyles: Record<string, { label: string; cls: string }> = {
    monthly:       { label: 'Monthly',     cls: 'bg-blue-100 text-blue-700'     },
    quarterly:     { label: 'Quarterly',   cls: 'bg-purple-100 text-purple-700' },
    semi_annually: { label: 'Semi-Annual', cls: 'bg-green-100 text-green-700'   },
    annually:      { label: 'Annually',    cls: 'bg-amber-100 text-amber-700'   },
}

function getPlanBadge(plan: string | null): { label: string; cls: string } {
    if (!plan) return { label: 'No Plan', cls: 'bg-gray-100 text-gray-400' }
    return planStyles[plan] ?? { label: plan, cls: 'bg-gray-100 text-gray-500' }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatDate(date: string | null) {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('en-PH', {
        year: 'numeric', month: 'short', day: 'numeric',
    })
}
</script>

<template>
    <Head title="Shop Management" />
    <AdminLayout :breadcrumbs="breadcrumbs" title="Shop Management">
        <div class="px-6 space-y-6">

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Registered Today</p>
                            <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <Store class="h-4 w-4 text-blue-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-blue-600">{{ stats.today }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Total Shops</p>
                            <div class="h-8 w-8 rounded-lg bg-purple-100 flex items-center justify-center">
                                <User class="h-4 w-4 text-purple-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-purple-600">{{ stats.total }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Active Shops</p>
                            <div class="h-8 w-8 rounded-lg bg-green-100 flex items-center justify-center">
                                <CheckCircle class="h-4 w-4 text-green-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-green-600">{{ stats.active }}</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Table card -->
            <Card>
                <CardHeader class="pb-3">
                    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
                        <CardTitle class="flex items-center gap-2">
                            <Store class="h-4 w-4 text-muted-foreground" />
                            Shop List
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

<<<<<<< HEAD
                        <Select v-model="statusFilter" @update:model-value="applyFilters">
                            <SelectTrigger class="w-36">
                                <SelectValue placeholder="Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Status</SelectItem>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="inactive">Inactive</SelectItem>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="disabled">Disabled</SelectItem>
                            </SelectContent>
                        </Select>

                        <Select v-model="planFilter" @update:model-value="applyFilters">
                            <SelectTrigger class="w-36">
                                <SelectValue placeholder="Plan" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Plans</SelectItem>
                                <SelectItem value="monthly">Monthly</SelectItem>
                                <SelectItem value="quarterly">Quarterly</SelectItem>
                                <SelectItem value="semi_annually">Semi-Annual</SelectItem>
                                <SelectItem value="annually">Annually</SelectItem>
                                <SelectItem value="none">No Plan</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Table -->
                    <div class="rounded-lg border overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                                    <th class="text-left px-4 py-3 font-medium">Shop</th>
                                    <th class="text-left px-4 py-3 font-medium">Owner</th>
                                    <th class="text-left px-4 py-3 font-medium">Phone</th>
                                    <th class="text-left px-4 py-3 font-medium">Location</th>
                                    <th class="text-left px-4 py-3 font-medium">Plan</th>
                                    <th class="text-left px-4 py-3 font-medium">Expires</th>
                                    <th class="text-left px-4 py-3 font-medium">Status</th>
                                    <th class="text-center px-4 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="shop in shops.data" :key="shop.id"
                                    class="border-b last:border-0 hover:bg-muted/20 transition-colors"
                                    :class="{ 'bg-red-50/50 dark:bg-red-900/10': shop.status === 'disabled' }"
=======
                        <template #header-shop_name="{ text }">
                            <button
                                type="button"
                                class="sortable-column"
                                :aria-label="`Sort shops ${serverOptions.sortBy === 'shop_name' && serverOptions.sortType === 'asc' ? 'descending' : 'ascending'}`"
                                @click="toggleSort('shop_name')"
                            >
                                <span>{{ text }}</span>
                                <component
                                    :is="sortIcon('shop_name')"
                                    class="h-3.5 w-3.5"
                                    :class="{
                                        'opacity-60':
                                            serverOptions.sortBy !==
                                            'shop_name',
                                    }"
                                />
                            </button>
                        </template>

                        <template #header-owner="{ text }">
                            <button
                                type="button"
                                class="sortable-column"
                                :aria-label="`Sort owners ${serverOptions.sortBy === 'owner' && serverOptions.sortType === 'asc' ? 'descending' : 'ascending'}`"
                                @click="toggleSort('owner')"
                            >
                                <span>{{ text }}</span>
                                <component
                                    :is="sortIcon('owner')"
                                    class="h-3.5 w-3.5"
                                    :class="{
                                        'opacity-60':
                                            serverOptions.sortBy !== 'owner',
                                    }"
                                />
                            </button>
                        </template>

                        <template #header-health_status="{ text }">
                            <div class="column-filter">
                                <button
                                    type="button"
                                    class="sortable-column"
                                    :aria-label="`Sort health ${serverOptions.sortBy === 'health_status' && serverOptions.sortType === 'asc' ? 'descending' : 'ascending'}`"
                                    @click="toggleSort('health_status')"
                                >
                                    <span>{{ text }}</span>
                                    <component
                                        :is="sortIcon('health_status')"
                                        class="h-3.5 w-3.5"
                                        :class="{
                                            'opacity-60':
                                                serverOptions.sortBy !==
                                                'health_status',
                                        }"
                                    />
                                </button>
                                <select
                                    v-model="activity"
                                    class="column-filter-input"
                                    aria-label="Filter by shop activity"
                                    @click.stop
                                    @change="filterTable"
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
                                >
                                    <td class="px-4 py-3">
                                        <p class="font-medium whitespace-nowrap">{{ shop.shop_name }}</p>
                                        <p v-if="shop.branch_name" class="text-xs text-muted-foreground">{{ shop.branch_name }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium whitespace-nowrap">{{ shop.owner?.name ?? '—' }}</p>
                                        <p class="text-xs text-muted-foreground">{{ shop.owner?.email ?? '' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground whitespace-nowrap">
                                        {{ shop.phone }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground">
                                        <p>{{ shop.municipality }}</p>
                                        <p class="text-muted-foreground/70">{{ shop.barangay }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full font-medium"
                                            :class="getPlanBadge(shop.subscription_plan).cls"
                                        >
                                            {{ getPlanBadge(shop.subscription_plan).label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span
                                            v-if="shop.expires_at"
                                            class="text-xs font-medium"
                                            :class="{
                                                'text-red-500':          shop.is_expired,
                                                'text-amber-500':        shop.is_expiring_soon && !shop.is_expired,
                                                'text-muted-foreground': !shop.is_expired && !shop.is_expiring_soon,
                                            }"
                                        >
                                            {{ formatDate(shop.expires_at) }}
                                            <span v-if="shop.is_expired" class="block text-xs">Expired</span>
                                            <span v-else-if="shop.is_expiring_soon" class="block text-xs">Expiring soon</span>
                                        </span>
                                        <span v-else class="text-xs text-muted-foreground">—</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                                            :class="{
                                                'bg-green-100 text-green-700':   shop.status === 'active',
                                                'bg-red-100 text-red-600':       shop.status === 'inactive',
                                                'bg-yellow-100 text-yellow-700': shop.status === 'pending',
                                                'bg-gray-100 text-gray-500':     shop.status === 'disabled',
                                            }"
                                        >
                                            {{ shop.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-1">
                                            <Button size="icon" variant="ghost" @click="router.visit(`/admin/shop/${shop.id}`)">
                                                <Eye class="h-4 w-4 text-blue-500" />
                                            </Button>
                                            <Button size="icon" variant="ghost" @click="router.visit(`/admin/shop/${shop.id}/edit`)">
                                                <Pencil class="h-4 w-4 text-green-500" />
                                            </Button>
                                            <Button size="icon" variant="ghost" @click="openArchive(shop)">
                                                <Trash2 class="h-4 w-4 text-amber-500" />
                                            </Button>
                                            <Button
                                                v-if="shop.status !== 'disabled'"
                                                size="icon" variant="ghost"
                                                title="Disable Shop"
                                                @click="openDisableDialog(shop)"
                                            >
                                                <ShieldOff class="h-4 w-4 text-orange-500" />
                                            </Button>
                                            <Button
                                                v-else
                                                size="icon" variant="ghost"
                                                title="Enable Shop"
                                                @click="enableShop(shop.id)"
                                            >
                                                <ShieldCheck class="h-4 w-4 text-green-600" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>

<<<<<<< HEAD
                                <tr v-if="shops.data.length === 0">
                                    <td colspan="8" class="px-4 py-12 text-center text-sm text-muted-foreground">
                                        <Store class="h-10 w-10 mx-auto mb-2 opacity-20" />
                                        No shops found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="shops.last_page > 1" class="flex items-center justify-between pt-2">
                        <p class="text-xs text-muted-foreground">
                            Showing {{ shops.data.length }} of {{ shops.total }} shops
                        </p>
                        <div class="flex gap-1">
                            <Button
                                v-for="link in shops.links" :key="link.label"
                                size="sm"
                                :variant="link.active ? 'default' : 'outline'"
                                :disabled="!link.url"
                                class="h-7 min-w-7 text-xs"
                                @click="link.url && router.visit(link.url)"
                                v-html="link.label"
=======
                        <template #header-compliance_status="{ text }">
                            <div class="column-filter">
                                <button
                                    type="button"
                                    class="sortable-column"
                                    :aria-label="`Sort compliance ${serverOptions.sortBy === 'compliance_status' && serverOptions.sortType === 'asc' ? 'descending' : 'ascending'}`"
                                    @click="toggleSort('compliance_status')"
                                >
                                    <span>{{ text }}</span>
                                    <component
                                        :is="sortIcon('compliance_status')"
                                        class="h-3.5 w-3.5"
                                        :class="{
                                            'opacity-60':
                                                serverOptions.sortBy !==
                                                'compliance_status',
                                        }"
                                    />
                                </button>
                                <select
                                    v-model="compliance"
                                    class="column-filter-input"
                                    aria-label="Filter by compliance"
                                    @click.stop
                                    @change="filterTable"
                                >
                                    <option value="">All compliance</option>
                                    <option value="compliant">Compliant</option>
                                    <option value="expiring">
                                        Expiring soon
                                    </option>
                                    <option value="expired">Expired</option>
                                    <option value="incomplete">
                                        Incomplete
                                    </option>
                                </select>
                            </div>
                        </template>

                        <template #header-subscription="{ text }">
                            <div class="column-filter">
                                <button
                                    type="button"
                                    class="sortable-column"
                                    :aria-label="`Sort subscriptions ${serverOptions.sortBy === 'subscription' && serverOptions.sortType === 'asc' ? 'descending' : 'ascending'}`"
                                    @click="toggleSort('subscription')"
                                >
                                    <span>{{ text }}</span>
                                    <component
                                        :is="sortIcon('subscription')"
                                        class="h-3.5 w-3.5"
                                        :class="{
                                            'opacity-60':
                                                serverOptions.sortBy !==
                                                'subscription',
                                        }"
                                    />
                                </button>
                                <div class="flex gap-1">
                                    <select
                                        v-model="plan"
                                        class="column-filter-input"
                                        aria-label="Filter by plan"
                                        @click.stop
                                        @change="filterTable"
                                    >
                                        <option value="">All plans</option>
                                        <option value="Basic">Basic</option>
                                        <option value="Standard">
                                            Standard
                                        </option>
                                        <option value="Premium">Premium</option>
                                        <option value="none">No plan</option>
                                    </select>
                                    <select
                                        v-model="subscription"
                                        class="column-filter-input"
                                        aria-label="Filter by subscription expiry"
                                        @click.stop
                                        @change="filterTable"
                                    >
                                        <option value="">Any expiry</option>
                                        <option value="expiring">
                                            Expiring
                                        </option>
                                        <option value="expired">Expired</option>
                                    </select>
                                </div>
                            </div>
                        </template>

                        <template #header-last_activity_at="{ text }">
                            <button
                                type="button"
                                class="sortable-column"
                                :aria-label="`Sort last activity ${serverOptions.sortBy === 'last_activity_at' && serverOptions.sortType === 'asc' ? 'descending' : 'ascending'}`"
                                @click="toggleSort('last_activity_at')"
                            >
                                <span>{{ text }}</span>
                                <component
                                    :is="sortIcon('last_activity_at')"
                                    class="h-3.5 w-3.5"
                                    :class="{
                                        'opacity-60':
                                            serverOptions.sortBy !==
                                            'last_activity_at',
                                    }"
                                />
                            </button>
                        </template>

                        <template #header-status="{ text }">
                            <div class="column-filter">
                                <button
                                    type="button"
                                    class="sortable-column"
                                    :aria-label="`Sort status ${serverOptions.sortBy === 'status' && serverOptions.sortType === 'asc' ? 'descending' : 'ascending'}`"
                                    @click="toggleSort('status')"
                                >
                                    <span>{{ text }}</span>
                                    <component
                                        :is="sortIcon('status')"
                                        class="h-3.5 w-3.5"
                                        :class="{
                                            'opacity-60':
                                                serverOptions.sortBy !==
                                                'status',
                                        }"
                                    />
                                </button>
                                <select
                                    v-model="status"
                                    class="column-filter-input"
                                    aria-label="Filter by shop status"
                                    @click.stop
                                    @change="filterTable"
                                >
                                    <option value="">All statuses</option>
                                    <option value="active">Active</option>
                                    <option value="pending">Pending</option>
                                    <option value="disabled">Disabled</option>
                                </select>
                            </div>
                        </template>

                        <template #item-selection="shop">
                            <input
                                type="checkbox"
                                :checked="selectedIds.includes(shop.id)"
                                :aria-label="`Select ${shop.shop_name}`"
                                class="rounded"
                                @change="toggleOne(shop.id)"
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>

        </div>

        <!-- Archive confirm -->
        <AlertDialog v-model:open="archiveOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Archive Shop</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to archive
                        <strong>{{ archiveName }}</strong>?
                        This can be undone later.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <Button variant="outline" @click="cancelArchive">Cancel</Button>
                    <Button variant="destructive" @click="confirmArchive">Archive</Button>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <!-- Disable dialog -->
        <AlertDialog :open="disableDialogOpen" @update:open="disableDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle class="flex items-center gap-2 text-orange-600">
                        <ShieldOff class="h-5 w-5" />
                        Disable Shop
                    </AlertDialogTitle>
                    <AlertDialogDescription>
                        You are about to disable <strong>{{ selectedShop?.shop_name }}</strong>.
                        The owner will lose access. Please provide a reason.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <div class="mt-2 space-y-1">
                    <label class="text-sm font-medium">Reason for disabling</label>
                    <Textarea
                        v-model="disableReason"
                        placeholder="e.g. Violation of terms of service..."
                        rows="3"
                    />
                </div>
                <AlertDialogFooter class="mt-4">
                    <AlertDialogCancel @click="disableDialogOpen = false">Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        class="bg-orange-600 text-white hover:bg-orange-700"
                        :disabled="!disableReason.trim()"
                        @click="confirmDisable"
                    >
                        Disable Shop
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

    </AdminLayout>
</template>
<<<<<<< HEAD
=======

<style scoped>
.shop-data-table {
    --easy-table-border: 0;
    --easy-table-row-border: 1px solid var(--border);
    --easy-table-header-background-color: var(--muted);
    --easy-table-header-font-color: var(--muted-foreground);
    --easy-table-header-font-size: 12px;
    --easy-table-header-height: 92px;
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

:deep(.vue3-easy-data-table__main) {
    background: var(--background);
}

:deep(.vue3-easy-data-table__main table) {
    min-width: 1470px;
}
</style>
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
