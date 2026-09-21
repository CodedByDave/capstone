<script setup lang="ts">
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
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    Archive,
    ArchiveRestore,
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

interface ShopItem {
    id: number;
    public_id: string;
    shop_name: string;
    branch_name: string | null;
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
const confirmAction = ref<'archive' | 'restore' | 'delete'>('archive');

const headers: Header[] = [
    { text: '', value: 'selection', width: 48 },
    { text: 'Shop', value: 'shop_name', width: 210, sortable: true },
    { text: 'Owner', value: 'owner', width: 205 },
    { text: 'Health', value: 'health_status', width: 145 },
    { text: 'Compliance', value: 'compliance_status', width: 165 },
    { text: 'Subscription', value: 'subscription', width: 190 },
    {
        text: 'Last activity',
        value: 'last_activity_at',
        width: 160,
        sortable: true,
    },
    { text: 'Status', value: 'status', width: 145, sortable: true },
    { text: 'Actions', value: 'actions', width: 180 },
];

const serverOptions = ref<ServerOptions>({
    page: props.shops.current_page,
    rowsPerPage: props.shops.per_page,
    sortBy: props.filters.sort_by ?? 'created_at',
    sortType: props.filters.sort_direction === 'asc' ? 'asc' : 'desc',
});

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
    { label: 'Pending approval', value: props.stats.pending },
    { label: 'Compliance issues', value: props.stats.compliance_issues },
    { label: 'Needs attention', value: props.stats.needs_attention },
]);

const secondaryStats = computed(() => [
    { label: 'Registered today', value: props.stats.today },
    { label: 'Archived', value: props.stats.archived },
    { label: 'Disabled', value: props.stats.disabled },
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

function toggleAll() {
    selectedIds.value = allSelected.value
        ? []
        : props.shops.data.map((shop) => shop.id);
}

function toggleOne(id: number) {
    selectedIds.value.includes(id)
        ? (selectedIds.value = selectedIds.value.filter(
              (selectedId) => selectedId !== id,
          ))
        : selectedIds.value.push(id);
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

function badgeClass(type: string) {
    const classes: Record<string, string> = {
        active: 'bg-emerald-100 text-emerald-700',
        pending: 'bg-amber-100 text-amber-700',
        disabled: 'bg-slate-200 text-slate-700',
        healthy: 'bg-emerald-100 text-emerald-700',
        attention: 'bg-amber-100 text-amber-700',
        critical: 'bg-red-100 text-red-700',
        compliant: 'bg-emerald-100 text-emerald-700',
        expiring: 'bg-amber-100 text-amber-700',
        expired: 'bg-red-100 text-red-700',
        incomplete: 'bg-slate-100 text-slate-600',
    };

    return classes[type] ?? 'bg-slate-100 text-slate-600';
}
</script>

<template>
    <Head title="Shop Management" />
    <AdminLayout :breadcrumbs="breadcrumbs" title="Shop Management">
        <div class="space-y-6 px-6">
            <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
                <Card v-for="item in statCards" :key="item.label">
                    <CardContent class="p-4">
                        <p
                            class="mb-3 text-xs font-medium text-muted-foreground"
                        >
                            {{ item.label }}
                        </p>
                        <p class="text-2xl font-bold">{{ item.value }}</p>
                    </CardContent>
                </Card>
            </div>

            <div class="-mt-3 space-y-3">
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
                    class="grid grid-cols-2 gap-2 rounded-lg border bg-muted/20 p-3 sm:grid-cols-4 lg:grid-cols-7"
                >
                    <div
                        v-for="item in secondaryStats"
                        :key="item.label"
                        class="rounded-md bg-background px-3 py-2"
                    >
                        <p class="text-xs text-muted-foreground">
                            {{ item.label }}
                        </p>
                        <p class="mt-1 text-lg font-semibold">
                            {{ item.value }}
                        </p>
                    </div>
                </div>
            </div>

            <CardContent class="space-y-4">
                <div class="flex flex-wrap items-center gap-2 pt-6">
                    <div class="relative min-w-48 flex-1">
                        <Search
                            class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search shop, owner, email..."
                            class="pl-8"
                            @keyup.enter="filterTable"
                        />
                    </div>

                    <template v-if="selectedIds.length > 0">
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
                                variant="outline"
                                class="border-red-300 bg-red-50 text-red-700 hover:bg-red-100 hover:text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300"
                                @click="bulkAction('archive')"
                            >
                                <Archive class="mr-1.5 h-4 w-4" />
                                Archive
                            </Button>
                        </template>
                    </template>

                    <Button
                        size="sm"
                        variant="outline"
                        class="border-red-300 bg-red-50 text-red-700 hover:bg-red-100 hover:text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/40"
                        @click="toggleArchiveView"
                    >
                        <ArchiveRestore class="mr-1.5 h-4 w-4" />
                        {{ archived ? 'Active shops' : 'Archive' }}
                    </Button>

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
                </div>

                <div class="overflow-hidden rounded-lg border">
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
                        alternating
                        must-sort
                    >
                        <template #header-selection>
                            <input
                                type="checkbox"
                                aria-label="Select all shops on this page"
                                :checked="allSelected"
                                class="rounded"
                                @change="toggleAll"
                            />
                        </template>

                        <template #header-health_status="{ text }">
                            <div class="column-filter">
                                <span>{{ text }}</span>
                                <select
                                    v-model="activity"
                                    class="column-filter-input"
                                    aria-label="Filter by shop activity"
                                    @click.stop
                                    @change="filterTable"
                                >
                                    <option value="">All activity</option>
                                    <option value="inactive">
                                        Inactive 30+ days
                                    </option>
                                </select>
                            </div>
                        </template>

                        <template #header-compliance_status="{ text }">
                            <div class="column-filter">
                                <span>{{ text }}</span>
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
                                <span>{{ text }}</span>
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

                        <template #header-status="{ text }">
                            <div class="column-filter">
                                <span>{{ text }}</span>
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
                            />
                        </template>

                        <template #item-shop_name="shop">
                            <div>
                                <p class="font-medium whitespace-nowrap">
                                    {{ shop.shop_name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{
                                        shop.branch_name ||
                                        `${shop.barangay}, ${shop.municipality}`
                                    }}
                                </p>
                            </div>
                        </template>

                        <template #item-owner="shop">
                            <div>
                                <p class="whitespace-nowrap">
                                    {{ shop.owner?.name || 'No owner' }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ shop.owner?.email }}
                                </p>
                            </div>
                        </template>

                        <template #item-health_status="shop">
                            <div>
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                                    :class="badgeClass(shop.health_status)"
                                >
                                    {{ shop.health_status }}
                                </span>
                                <p
                                    v-if="shop.is_inactive"
                                    class="mt-1 text-xs text-amber-600"
                                >
                                    Inactive
                                </p>
                            </div>
                        </template>

                        <template #item-compliance_status="shop">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                                :class="badgeClass(shop.compliance_status)"
                            >
                                {{ shop.compliance_status }}
                            </span>
                        </template>

                        <template #item-subscription="shop">
                            <div>
                                <p class="whitespace-nowrap">
                                    {{ shop.subscription }}
                                </p>
                                <p
                                    class="text-xs whitespace-nowrap"
                                    :class="
                                        shop.is_expired
                                            ? 'text-red-600'
                                            : 'text-muted-foreground'
                                    "
                                >
                                    {{ formatDate(shop.expires_at) }}
                                </p>
                            </div>
                        </template>

                        <template #item-last_activity_at="shop">
                            <span
                                class="text-xs whitespace-nowrap"
                                :class="
                                    shop.is_inactive
                                        ? 'text-amber-700'
                                        : 'text-muted-foreground'
                                "
                            >
                                {{ formatDate(shop.last_activity_at) }}
                            </span>
                        </template>

                        <template #item-status="shop">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                                :class="badgeClass(shop.status)"
                            >
                                {{ shop.status }}
                            </span>
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
                                        <Pencil
                                            class="h-4 w-4 text-slate-500"
                                        />
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
                                        <ShieldOff
                                            class="h-4 w-4 text-amber-600"
                                        />
                                    </Button>
                                    <Button
                                        size="icon"
                                        variant="ghost"
                                        aria-label="Archive shop"
                                        @click="openConfirm(shop, 'archive')"
                                    >
                                        <Trash2
                                            class="h-4 w-4 text-amber-500"
                                        />
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
                                    <Button
                                        size="icon"
                                        variant="ghost"
                                        aria-label="Delete shop permanently"
                                        @click="openConfirm(shop, 'delete')"
                                    >
                                        <Trash2 class="h-4 w-4 text-red-600" />
                                    </Button>
                                </template>
                            </div>
                        </template>

                        <template #empty-message>
                            <div
                                class="py-10 text-center text-sm text-muted-foreground"
                            >
                                No shops match the selected filters.
                            </div>
                        </template>
                    </EasyDataTable>
                </div>
            </CardContent>
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
                            confirmAction === 'delete'
                                ? 'This permanently removes the archived shop and cannot be undone.'
                                : `Are you sure you want to ${confirmAction} ${confirmTarget?.shop_name}?`
                        }}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        :class="
                            confirmAction === 'delete' ||
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
