<script setup lang="ts">
import {
    IdentityCell,
    StatusPill,
    type StatusTone,
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
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    Archive,
    ArchiveRestore,
    ArrowDown,
    ArrowUp,
    ArrowUpDown,
    ChevronDown,
    Download,
    Eye,
    Pencil,
    RefreshCcw,
    RotateCcw,
    Search,
    ShieldCheck,
    ShieldOff,
    Store,
    Trash2,
    Upload,
} from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import Vue3EasyDataTable, {
    type Header,
    type ServerOptions,
} from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

interface ShopItem {
    id: number;
    public_id: string;
    shop_name: string;
    phone: string;
    municipality: string;
    barangay: string;
    status: string;
    disable_reason: string | null;
    created_at: string;
    deleted_at: string | null;
    last_activity_at: string | null;
    owner: { id: number; name: string; email: string } | null;
    subscription_plan: string | null;
    expires_at: string | null;
    is_expired: boolean;
    is_expiring_soon: boolean;
    compliance_status: 'expired' | 'expiring' | 'incomplete' | 'compliant';
    health_status: 'critical' | 'attention' | 'healthy';
    is_inactive: boolean;
}

interface Paginator {
    data: ShopItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Stats {
    today: number;
    total: number;
    active: number;
    pending: number;
    disabled: number;
    archived: number;
    expired_permits: number;
    expiring_permits: number;
    compliance_issues: number;
    inactive: number;
    expired_subscriptions: number;
    needs_attention: number;
}

const EasyDataTable = Vue3EasyDataTable;
const props = defineProps<{
    shops: Paginator;
    stats: Stats;
    filters: Record<string, string>;
}>();

const page = usePage();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Shop Management', href: '/admin/shop' },
];

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
const confirmAction = ref<'archive' | 'restore'>('archive');

type ShopSortColumn = 'shop_name' | 'owner' | 'subscription' | 'status';

// Only what is needed to scan the list. Health, compliance, permits and last
// activity live on the shop's View and Edit pages.
const headers: Header[] = [
    { text: '', value: 'selection', width: 48 },
    { text: 'Shop', value: 'shop_name', width: 260 },
    { text: 'Owner', value: 'owner', width: 240 },
    { text: 'Subscription', value: 'subscription', width: 200 },
    { text: 'Status', value: 'status', width: 150 },
    { text: 'Actions', value: 'actions', width: 190 },
];

const sortableColumns = headers.filter((header) =>
    ['shop_name', 'owner', 'subscription', 'status'].includes(header.value),
) as { text: string; value: ShopSortColumn }[];

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

const statusOptions = computed(() => [
    { value: '', label: 'All', count: props.stats.total },
    { value: 'active', label: 'Active', count: props.stats.active },
    { value: 'pending', label: 'Pending', count: props.stats.pending },
    { value: 'disabled', label: 'Disabled', count: props.stats.disabled },
]);

const statusTone: Record<string, StatusTone> = {
    active: 'success',
    pending: 'warning',
    disabled: 'neutral',
};

function setStatus(value: string) {
    status.value = value;
    filterTable();
}

const secondaryStats = computed(() => [
    { label: 'Registered today', value: props.stats.today },
    { label: 'Archived', value: props.stats.archived },
    { label: 'Expired permits', value: props.stats.expired_permits },
    { label: 'Expiring permits', value: props.stats.expiring_permits },
    { label: 'Inactive 30+ days', value: props.stats.inactive },
    { label: 'Expired plans', value: props.stats.expired_subscriptions },
]);

onMounted(() => {
    const flash = page.props.toast as
        | { type: string; message: string }
        | undefined;
    if (!flash) return;

    if (flash.type === 'success') toast.success(flash.message);
    else if (flash.type === 'error') toast.error(flash.message);
    else toast(flash.message);
});

function query() {
    return {
        search: search.value || undefined,
        status: status.value || undefined,
        plan: plan.value || undefined,
        compliance: compliance.value || undefined,
        activity: activity.value || undefined,
        subscription: subscription.value || undefined,
        trashed: archived.value ? '1' : undefined,
        sort_by: serverOptions.value.sortBy,
        sort_direction: serverOptions.value.sortType,
        page: serverOptions.value.page,
        per_page: serverOptions.value.rowsPerPage,
    };
}

function applyFilters() {
    selectedIds.value = [];
    router.get('/admin/shop', query(), {
        only: ['shops', 'filters'],
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
    compliance.value = '';
    activity.value = '';
    subscription.value = '';

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

function toggleArchiveView() {
    selectedIds.value = [];
    router.get('/admin/shop', archived.value ? {} : { trashed: '1' }, {
        preserveState: true,
    });
}

function exportCsv() {
    const params = new URLSearchParams();
    Object.entries(query()).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
            params.set(key, String(value));
        }
    });
    window.location.assign(`/admin/shop/export?${params.toString()}`);
}

const importing = ref(false);
const importInput = ref<HTMLInputElement | null>(null);

function chooseCsvFile() {
    importInput.value?.click();
}

function importCsv(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    router.post(
        '/admin/shop/import',
        { file },
        {
            forceFormData: true,
            preserveScroll: true,
            onStart: () => (importing.value = true),
            onSuccess: (responsePage) => {
                const flash = responsePage.props.toast as
                    | { type: string; message: string }
                    | undefined;
                toast.success(flash?.message ?? 'Shop CSV imported.');
            },
            onError: (errors) =>
                toast.error(
                    typeof errors.file === 'string'
                        ? errors.file
                        : 'Unable to import the CSV file.',
                ),
            onFinish: () => {
                importing.value = false;
                input.value = '';
            },
        },
    );
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

function openConfirm(shop: ShopItem, action: 'archive' | 'restore') {
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

    router.delete(
        `/admin/shop/${shop.public_id}`,
        actionOptions(() => {
            confirmDialog.value = false;
        }),
    );
}

function actionOptions(after?: () => void) {
    return {
        preserveScroll: true,
        onSuccess: () => {
            selectedIds.value = [];
            after?.();
        },
        onError: () => toast.error('The action could not be completed.'),
    };
}

function formatDate(date: string | null) {
    if (!date) return 'Not recorded';
    return new Date(date).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}
</script>

<template>
    <Head title="Shop Management" />
    <AdminLayout :breadcrumbs="breadcrumbs" title="Shop Management">
        <div class="mx-auto w-full max-w-[1280px] space-y-6 px-6">
            <!-- Header -->
            <header class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        Shop management
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Every laundry shop on the platform, with its owner, plan
                        and status. Open a shop for its full details.
                    </p>
                </div>

                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button
                            variant="success-outline"
                            class="h-11 gap-2"
                            :disabled="importing"
                        >
                            <ArrowUpDown class="h-4 w-4" aria-hidden="true" />
                            {{ importing ? 'Importing...' : 'Import / export' }}
                            <ChevronDown class="h-4 w-4" aria-hidden="true" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-48">
                        <DropdownMenuItem @select="exportCsv">
                            <Download class="mr-2 h-4 w-4" />
                            Export CSV
                        </DropdownMenuItem>
                        <DropdownMenuItem @select="chooseCsvFile">
                            <Upload class="mr-2 h-4 w-4" />
                            Import CSV
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @select="toggleArchiveView">
                            <ArchiveRestore class="mr-2 h-4 w-4" />
                            {{ archived ? 'Active shops' : 'Archived shops' }}
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
                <input
                    ref="importInput"
                    type="file"
                    accept=".csv,text/csv"
                    class="hidden"
                    @change="importCsv"
                />
            </header>

            <!-- Stats -->
            <div class="space-y-3">
                <div
                    class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-5"
                >
                    <div
                        v-for="item in statCards"
                        :key="item.label"
                        class="rounded-2xl border bg-card p-5"
                    >
                        <p
                            class="text-[11px] font-semibold tracking-[0.12em] text-muted-foreground uppercase"
                        >
                            {{ item.label }}
                        </p>
                        <p class="mt-3 text-4xl font-bold tabular-nums">
                            {{ item.value.toLocaleString() }}
                        </p>
                    </div>
                </div>

                <div class="flex justify-end">
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="showMoreStats = !showMoreStats"
                    >
                        {{
                            showMoreStats
                                ? 'Hide statistics'
                                : 'More statistics'
                        }}
                    </Button>
                </div>
                <div
                    v-if="showMoreStats"
                    class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6"
                >
                    <div
                        v-for="item in secondaryStats"
                        :key="item.label"
                        class="rounded-xl border bg-card px-4 py-3"
                    >
                        <p class="text-xs text-muted-foreground">
                            {{ item.label }}
                        </p>
                        <p class="mt-1 text-lg font-semibold tabular-nums">
                            {{ item.value.toLocaleString() }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="flex flex-wrap items-center gap-3">
                <div
                    role="group"
                    aria-label="Filter by shop status"
                    class="inline-flex flex-wrap items-center gap-1 rounded-lg bg-muted p-1"
                >
                    <button
                        v-for="option in statusOptions"
                        :key="option.value"
                        type="button"
                        class="inline-flex h-9 items-center gap-1.5 rounded-md px-3 text-sm font-medium transition-colors outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
                        :class="
                            status === option.value
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-foreground/80 hover:text-foreground'
                        "
                        :aria-pressed="status === option.value"
                        @click="setStatus(option.value)"
                    >
                        {{ option.label }}
                        <span
                            class="text-xs font-normal text-muted-foreground tabular-nums"
                            >{{ option.count }}</span
                        >
                    </button>
                </div>

                <select
                    v-model="plan"
                    class="toolbar-select"
                    aria-label="Filter by plan"
                    @change="filterTable"
                >
                    <option value="">All plans</option>
                    <option value="Basic">Basic</option>
                    <option value="Standard">Standard</option>
                    <option value="Premium">Premium</option>
                    <option value="none">No plan</option>
                </select>
                <select
                    v-model="subscription"
                    class="toolbar-select"
                    aria-label="Filter by subscription expiry"
                    @change="filterTable"
                >
                    <option value="">Any expiry</option>
                    <option value="expiring">Expiring</option>
                    <option value="expired">Expired</option>
                </select>
                <select
                    v-model="compliance"
                    class="toolbar-select"
                    aria-label="Filter by compliance"
                    @change="filterTable"
                >
                    <option value="">All compliance</option>
                    <option value="compliant">Compliant</option>
                    <option value="expiring">Expiring soon</option>
                    <option value="expired">Expired</option>
                    <option value="incomplete">Incomplete</option>
                </select>
                <select
                    v-model="activity"
                    class="toolbar-select"
                    aria-label="Filter by shop activity"
                    @change="filterTable"
                >
                    <option value="">All activity</option>
                    <option value="inactive">Inactive 30+ days</option>
                </select>

                <div class="relative min-w-56 flex-1">
                    <Search
                        class="pointer-events-none absolute top-3.5 left-3 h-4 w-4 text-muted-foreground"
                        aria-hidden="true"
                    />
                    <Input
                        v-model="search"
                        placeholder="Search shop, owner, email..."
                        aria-label="Search shops"
                        class="h-11 rounded-lg pl-9"
                        @keyup.enter="filterTable"
                    />
                </div>
            </div>

            <!-- Bulk actions -->
            <div
                v-if="selectedIds.length > 0"
                class="flex flex-wrap items-center gap-2 rounded-xl border bg-card px-4 py-3"
            >
                <span class="mr-2 text-sm font-medium"
                    >{{ selectedIds.length }} selected</span
                >
                <Button
                    v-if="archived"
                    size="sm"
                    variant="outline"
                    class="border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 hover:text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300"
                    @click="bulkAction('restore')"
                >
                    <RotateCcw class="mr-1.5 h-4 w-4" />
                    Restore ({{ selectedIds.length }})
                </Button>
                <template v-else>
                    <Button
                        size="sm"
                        variant="outline"
                        class="border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 hover:text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300"
                        @click="bulkAction('enable')"
                    >
                        <ShieldCheck class="mr-1.5 h-4 w-4" />
                        Enable ({{ selectedIds.length }})
                    </Button>
                    <Button
                        size="sm"
                        variant="outline"
                        class="border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100 hover:text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300"
                        @click="openDisable()"
                    >
                        <ShieldOff class="mr-1.5 h-4 w-4" />
                        Disable
                    </Button>
                    <Button
                        size="sm"
                        variant="warning-outline"
                        @click="bulkAction('archive')"
                    >
                        <Archive class="mr-1.5 h-4 w-4" />
                        Archive
                    </Button>
                </template>
                <Button size="sm" variant="ghost" @click="selectedIds = []">
                    Clear
                </Button>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-2xl border bg-card">
                <EasyDataTable
                    v-model:server-options="serverOptions"
                    class="shop-data-table"
                    :headers="headers"
                    :items="tableItems"
                    :server-items-length="shops.total"
                    :loading="tableLoading"
                    :rows-items="[5, 10, 15, 20, 50, 100]"
                    rows-of-page-separator-message="out of"
                    rows-per-page-message="Rows per page:"
                    buttons-pagination
                    must-sort
                >
                    <template #header-selection>
                        <input
                            type="checkbox"
                            aria-label="Select all shops on this page"
                            :checked="allSelected"
                            class="h-4 w-4 rounded"
                            @change="toggleAll"
                        />
                    </template>

                    <template
                        v-for="column in sortableColumns"
                        :key="column.value"
                        #[`header-${column.value}`]="{ text }"
                    >
                        <button
                            type="button"
                            class="sortable-column"
                            :aria-label="`Sort by ${text.toLowerCase()} ${serverOptions.sortBy === column.value && serverOptions.sortType === 'asc' ? 'descending' : 'ascending'}`"
                            @click="toggleSort(column.value)"
                        >
                            <span>{{ text }}</span>
                            <component
                                :is="sortIcon(column.value)"
                                class="h-3.5 w-3.5"
                                :class="
                                    serverOptions.sortBy === column.value
                                        ? 'text-primary'
                                        : 'opacity-60'
                                "
                            />
                        </button>
                    </template>

                    <template #item-selection="shop">
                        <input
                            type="checkbox"
                            :checked="selectedIds.includes(shop.id)"
                            :aria-label="`Select ${shop.shop_name}`"
                            class="h-4 w-4 rounded"
                            @change="toggleOne(shop.id)"
                        />
                    </template>

                    <template #item-shop_name="shop">
                        <IdentityCell
                            :name="shop.shop_name"
                            :secondary="`${shop.barangay}, ${shop.municipality}`"
                        />
                    </template>

                    <template #item-owner="shop">
                        <div>
                            <p class="text-sm whitespace-nowrap">
                                {{ shop.owner?.name || 'No owner' }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ shop.owner?.email }}
                            </p>
                        </div>
                    </template>

                    <template #item-subscription="shop">
                        <div>
                            <p class="text-sm whitespace-nowrap">
                                {{ shop.subscription }}
                            </p>
                            <p
                                class="text-xs whitespace-nowrap"
                                :class="
                                    shop.is_expired
                                        ? 'text-red-500'
                                        : 'text-muted-foreground'
                                "
                            >
                                {{ formatDate(shop.expires_at) }}
                            </p>
                        </div>
                    </template>

                    <template #item-status="shop">
                        <StatusPill
                            :tone="statusTone[shop.status] ?? 'neutral'"
                        >
                            <span class="capitalize">{{ shop.status }}</span>
                        </StatusPill>
                    </template>

                    <template #item-actions="shop">
                        <div class="flex items-center justify-center gap-1">
                            <template v-if="!archived">
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    aria-label="View shop"
                                    @click="
                                        router.visit(
                                            `/admin/shop/${shop.public_id}`,
                                        )
                                    "
                                >
                                    <Eye class="h-4 w-4 text-blue-500" />
                                </Button>
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    aria-label="Edit shop"
                                    @click="
                                        router.visit(
                                            `/admin/shop/${shop.public_id}/edit`,
                                        )
                                    "
                                >
                                    <Pencil class="h-4 w-4 text-slate-500" />
                                </Button>
                                <Button
                                    v-if="shop.status === 'disabled'"
                                    size="icon"
                                    variant="ghost"
                                    aria-label="Enable shop"
                                    @click="enableShop(shop)"
                                >
                                    <ShieldCheck
                                        class="h-4 w-4 text-emerald-600"
                                    />
                                </Button>
                                <Button
                                    v-else
                                    size="icon"
                                    variant="ghost"
                                    aria-label="Disable shop"
                                    @click="openDisable(shop)"
                                >
                                    <ShieldOff class="h-4 w-4 text-amber-600" />
                                </Button>
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    aria-label="Archive shop"
                                    @click="openConfirm(shop, 'archive')"
                                >
                                    <Trash2 class="h-4 w-4 text-amber-500" />
                                </Button>
                            </template>
                            <template v-else>
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    aria-label="Restore shop"
                                    @click="openConfirm(shop, 'restore')"
                                >
                                    <RotateCcw
                                        class="h-4 w-4 text-emerald-600"
                                    />
                                </Button>
                            </template>
                        </div>
                    </template>

                    <template #empty-message>
                        <div
                            class="flex flex-col items-center gap-3 py-10 text-center text-sm text-muted-foreground"
                        >
                            <Store class="h-8 w-8" aria-hidden="true" />
                            <p>No shops match the selected filters.</p>
                            <Button
                                size="sm"
                                variant="outline"
                                @click="resetFilters"
                            >
                                <RefreshCcw class="mr-1.5 h-4 w-4" />
                                Reset filters
                            </Button>
                        </div>
                    </template>
                </EasyDataTable>
            </div>
        </div>

        <AlertDialog
            :open="disableDialog"
            @update:open="disableDialog = $event"
        >
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>
                        Disable
                        {{
                            disableTarget?.shop_name ||
                            `${selectedIds.length} selected shops`
                        }}
                    </AlertDialogTitle>
                    <AlertDialogDescription>
                        Access will be suspended and this event will be recorded
                        in Audit & Logs.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <Textarea
                    v-model="disableReason"
                    placeholder="Reason for disabling"
                    rows="3"
                />
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        :disabled="!disableReason.trim()"
                        class="bg-amber-600 hover:bg-amber-700"
                        @click="disableShops"
                    >
                        Disable
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <AlertDialog
            :open="confirmDialog"
            @update:open="confirmDialog = $event"
        >
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle class="capitalize">
                        {{ confirmAction }} shop
                    </AlertDialogTitle>
                    <AlertDialogDescription>
                        {{
                            confirmAction === 'archive'
                                ? `Archive ${confirmTarget?.shop_name}? It moves to the archive with all its data, and you can restore it at any time.`
                                : `Are you sure you want to restore ${confirmTarget?.shop_name}?`
                        }}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        :class="
                            confirmAction === 'archive'
                                ? 'bg-red-600 hover:bg-red-700'
                                : ''
                        "
                        @click="runConfirmedAction"
                    >
                        Continue
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AdminLayout>
</template>

<style scoped>
.shop-data-table {
    --easy-table-border: 0;
    --easy-table-row-border: 1px solid var(--border);
    --easy-table-header-background-color: transparent;
    --easy-table-header-font-color: var(--muted-foreground);
    --easy-table-header-font-size: 11px;
    --easy-table-header-height: 52px;
    --easy-table-header-item-padding: 10px 16px;
    --easy-table-body-row-background-color: transparent;
    --easy-table-body-even-row-background-color: transparent;
    --easy-table-body-row-font-color: var(--foreground);
    --easy-table-body-even-row-font-color: var(--foreground);
    --easy-table-body-row-hover-background-color: color-mix(
        in srgb,
        var(--muted) 55%,
        transparent
    );
    --easy-table-body-row-hover-font-color: var(--foreground);
    --easy-table-body-row-height: 68px;
    --easy-table-body-item-padding: 10px 16px;
    --easy-table-message-font-color: var(--muted-foreground);
    --easy-table-footer-background-color: transparent;
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
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.sortable-column:hover {
    color: var(--foreground);
}

.toolbar-select {
    height: 44px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--background);
    padding: 0 10px;
    color: var(--foreground);
    font-size: 13px;
    outline: none;
}

.toolbar-select:focus-visible {
    border-color: var(--ring);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--ring) 25%, transparent);
}

:deep(.vue3-easy-data-table__main) {
    background: transparent;
}

:deep(.vue3-easy-data-table__main table) {
    width: 100%;
    min-width: 0;
}

:deep(.vue3-easy-data-table__main thead th),
:deep(.vue3-easy-data-table__main tbody td) {
    min-width: 0 !important;
}
</style>
