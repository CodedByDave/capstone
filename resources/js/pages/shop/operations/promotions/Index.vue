<script setup lang="ts">
import ActionsMenu from '@/components/shop/ActionsMenu.vue';
import PageHeader from '@/components/shop/PageHeader.vue';
import { useUndoToast } from '@/composables/useUndoToast';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import {
    BulkActionButton,
    DataTable,
    KpiTiles,
    RowActions,
    StatusPill,
    type DataTableHeader,
} from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { useCsvImport } from '@/composables/useCsvImport';
import {
    useDataTable,
    usePaginatorTable,
    type DataTableOptions,
} from '@/composables/useDataTable';
import { usePermissions } from '@/composables/usePermissions';
import {
    Archive,
    BadgeDollarSign,
    CheckCircle2,
    Download,
    Pencil,
    Percent,
    Plus,
    RotateCcw,
    Tag,
    ToggleLeft,
    ToggleRight,
    Trash2,
    Upload,
    XCircle,
} from 'lucide-vue-next';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Promotion {
    id: number;
    name: string;
    description: string | null;
    type: 'percentage' | 'fixed';
    value: string;
    min_order_amount: string | null;
    is_active: boolean;
    starts_at: string | null;
    ends_at: string | null;
    orders_count?: number;
    deleted_at: string | null;
}

interface Stats {
    total: number;
    active: number;
    inactive: number;
    percentage: number;
    fixed: number;
}

interface Paginated {
    data: Promotion[];
    current_page: number;
    last_page: number;
    per_page?: number;
    total: number;
    from: number | null;
    to: number | null;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    promotions: Paginated | Promotion[];
    stats: Stats;
    archived: boolean;
}>();

// ─── Setup ────────────────────────────────────────────────────────────────────

const page = usePage<AppPageProps>();
const isOwner = computed(() => page.props.auth.user.role === 'owner');
const { can } = usePermissions();
const canCreate = computed(() => can('Operations', 'create'));
const canManage = computed(() => can('Operations', 'manage'));
const base = computed(() =>
    isOwner.value ? '/shop/operations/promos' : '/staff/operations/promos',
);

const {
    input: csvInput,
    importing,
    choose: chooseCsv,
    onFileChange: onCsvChange,
} = useCsvImport(
    () => `${isOwner.value ? '/shop' : '/staff'}/import/promotions`,
);

onMounted(() => {
    const flashToast = page.props.toast as
        | { type: string; message: string }
        | undefined;
    if (!flashToast) return;
    switch (flashToast.type) {
        case 'success':
            toast.success(flashToast.message);
            break;
        case 'error':
            toast.error(flashToast.message);
            break;
        default:
            toast(flashToast.message);
    }
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Operations', href: '/shop/operations/orders' },
    { title: 'Promotions', href: '/shop/operations/promos' },
];

// ─── Status ───────────────────────────────────────────────────────────────────

// A promotion is only live while it is switched on and inside its date range.
function promoStatus(promo: Promotion): {
    label: string;
    tone: 'success' | 'danger' | 'neutral' | 'info';
} {
    if (!promo.is_active) return { label: 'Inactive', tone: 'danger' };
    const now = new Date();
    const today = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    if (promo.ends_at && promo.ends_at < today)
        return { label: 'Expired', tone: 'neutral' };
    if (promo.starts_at && promo.starts_at > today)
        return { label: 'Scheduled', tone: 'info' };
    return { label: 'Active', tone: 'success' };
}

// ─── Data ─────────────────────────────────────────────────────────────────────

const rows = computed<Promotion[]>(() =>
    Array.isArray(props.promotions) ? props.promotions : props.promotions.data,
);

const paginated = computed(() =>
    Array.isArray(props.promotions) ? null : props.promotions,
);

// ─── Table ────────────────────────────────────────────────────────────────────

// Active promotions are paginated by the backend; the archive is a full list.
const { options: serverOptions, loading } = usePaginatorTable(
    () =>
        paginated.value ?? {
            current_page: 1,
            per_page: Math.max(rows.value.length, 1),
            data: rows.value,
        },
    ['promotions'],
);

const {
    options: clientOptions,
    pageItems: clientItems,
    total: clientTotal,
} = useDataTable(() => (paginated.value ? [] : rows.value), {
    sortBy: 'name',
    // Validity and status are shown from other fields, so sort by those.
    sortValue: (promo, column) => {
        const record = promo as unknown as Record<string, unknown>;

        if (column === 'validity') return record.starts_at;
        if (column === 'status') return record.is_active;
        if (column === 'value' || column === 'min_order_amount') {
            return Number(record[column]);
        }

        return record[column];
    },
});

const tableOptions = computed(() =>
    paginated.value ? serverOptions.value : clientOptions.value,
);
const tableItems = computed(() =>
    paginated.value ? rows.value : clientItems.value,
);
const tableTotal = computed(() =>
    paginated.value ? paginated.value.total : clientTotal.value,
);

function setTableOptions(next: DataTableOptions) {
    if (paginated.value) serverOptions.value = next;
    else clientOptions.value = next;
}

const headers: DataTableHeader[] = [
    { text: 'Name', value: 'name', sortable: true },
    { text: 'Type', value: 'type', sortable: true },
    { text: 'Value', value: 'value', sortable: true },
    { text: 'Min. order', value: 'min_order_amount', sortable: true },
    { text: 'Validity', value: 'validity', sortable: true },
    {
        text: 'Orders used',
        value: 'orders_count',
        sortable: true,
        align: 'center',
    },
    { text: 'Status', value: 'status', sortable: true },
    { text: 'Actions', value: 'actions', align: 'center', width: 150 },
];

// ─── Delete ───────────────────────────────────────────────────────────────────

const { undoable } = useUndoToast();

// Archiving can be undone, so it happens at once and a toast offers Undo for a few seconds.
function archivePromotion(promo: Promotion) {
    router.delete(`${base.value}/${promo.id}`, {
        preserveScroll: true,
        onSuccess: () =>
            undoable(`${promo.name} archived.`, () =>
                router.patch(
                    `${base.value}/${promo.id}/restore`,
                    {},
                    { preserveScroll: true },
                ),
            ),
    });
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatValue(promo: Promotion) {
    return promo.type === 'percentage'
        ? `${parseFloat(promo.value)}%`
        : `₱${parseFloat(promo.value).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
}

function formatDate(d: string | null) {
    if (!d) return '—';
    return new Date(d + 'T00:00:00').toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

const selected = ref<Array<string | number>>([]);
</script>

<template>
    <Head title="Promotions" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Promotions">
        <div class="space-y-6 px-6">
            <!-- Header -->
            <PageHeader
                title="Promotions"
                description="Manage discount promotions applied to customer orders."
            >
                <Button
                    v-if="archived && canManage"
                    variant="back"
                    class="h-11"
                    @click="router.visit(`${base}?archived=false`)"
                >
                    <Archive class="mr-2 h-4 w-4" />
                    Back to Active
                </Button>
                <ActionsMenu
                    v-if="!archived"
                    :items="[
                        {
                            label: 'Export CSV',
                            icon: Download,
                            href: `${isOwner ? '/shop' : '/staff'}/export/promotions`,
                        },
                        {
                            label: importing ? 'Importing...' : 'Import CSV',
                            icon: Upload,
                            onSelect: chooseCsv,
                            disabled: importing,
                            hidden: !canCreate,
                        },
                        {
                            separator: true,
                            label: 'Archived promotions',
                            icon: Archive,
                            onSelect: () =>
                                router.visit(`${base}?archived=true`),
                            hidden: !canManage,
                        },
                    ]"
                />
                <Button
                    v-if="!archived && canCreate"
                    class="h-11"
                    @click="router.visit(`${base}/create`)"
                >
                    <Plus class="mr-2 h-4 w-4" /> New Promotion
                </Button>
                <input
                    ref="csvInput"
                    type="file"
                    accept=".csv,text/csv"
                    class="hidden"
                    @change="onCsvChange"
                />
            </PageHeader>

            <!-- Stats -->
            <KpiTiles
                :tiles="[
                    { label: 'Total', value: stats.total, icon: Tag },
                    {
                        label: 'Active',
                        value: stats.active,
                        icon: CheckCircle2,
                    },
                    { label: 'Inactive', value: stats.inactive, icon: XCircle },
                    { label: '% Off', value: stats.percentage, icon: Percent },
                    {
                        label: 'Fixed ₱',
                        value: stats.fixed,
                        icon: BadgeDollarSign,
                    },
                ]"
            />

            <!-- Table -->
            <DataTable
                v-model:selected="selected"
                :options="tableOptions"
                :headers="headers"
                :items="tableItems"
                :total="tableTotal"
                :loading="loading"
                noun="promotions"
                :empty-icon="Tag"
                :empty-message="
                    archived
                        ? 'No archived promotions.'
                        : 'No promotions yet. Create one to get started.'
                "
                :resettable="false"
                :min-width="1080"
                :hide-rows-per-page="Boolean(paginated)"
                @update:options="setTableOptions"
            >
                <template #bulk-actions>
                    <BulkActionButton
                        v-if="canManage"
                        resource="promotion"
                        :action="archived ? 'restore' : 'archive'"
                        :ids="selected"
                        noun="promotions"
                        @done="selected = []"
                    />
                </template>

                <template #item-name="promo">
                    <p class="font-medium">{{ promo.name }}</p>
                    <p
                        v-if="promo.description"
                        class="max-w-[200px] truncate text-xs text-muted-foreground"
                    >
                        {{ promo.description }}
                    </p>
                </template>

                <template #item-type="promo">
                    <span
                        class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold whitespace-nowrap"
                        :class="
                            promo.type === 'percentage'
                                ? 'bg-violet-100 text-violet-700'
                                : 'bg-amber-100 text-amber-700'
                        "
                    >
                        <Percent
                            v-if="promo.type === 'percentage'"
                            class="h-3 w-3"
                        />
                        <BadgeDollarSign v-else class="h-3 w-3" />
                        {{
                            promo.type === 'percentage'
                                ? 'Percentage'
                                : 'Fixed Amount'
                        }}
                    </span>
                </template>

                <template #item-value="promo">
                    <span class="font-semibold whitespace-nowrap">
                        {{ formatValue(promo) }}
                    </span>
                </template>

                <template #item-min_order_amount="promo">
                    <span
                        class="text-xs whitespace-nowrap text-muted-foreground"
                    >
                        {{
                            promo.min_order_amount
                                ? `₱${parseFloat(promo.min_order_amount).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
                                : '—'
                        }}
                    </span>
                </template>

                <template #item-validity="promo">
                    <span
                        class="text-xs whitespace-nowrap text-muted-foreground"
                    >
                        <template v-if="!promo.starts_at && !promo.ends_at"
                            >No limit</template
                        >
                        <template v-else
                            >{{ formatDate(promo.starts_at) }} –
                            {{ formatDate(promo.ends_at) }}</template
                        >
                    </span>
                </template>

                <template #item-orders_count="promo">
                    <span
                        class="inline-flex h-6 w-10 items-center justify-center rounded bg-blue-100 text-xs font-semibold text-blue-700"
                    >
                        {{ promo.orders_count ?? 0 }}
                    </span>
                </template>

                <template #item-status="promo">
                    <StatusPill v-if="archived" tone="neutral"
                        >Archived</StatusPill
                    >
                    <StatusPill v-else :tone="promoStatus(promo).tone">
                        {{ promoStatus(promo).label }}
                    </StatusPill>
                </template>

                <template #item-actions="promo">
                    <RowActions
                        :label="promo.name"
                        :actions="[
                            {
                                label: promo.is_active
                                    ? 'Deactivate'
                                    : 'Activate',
                                icon: promo.is_active
                                    ? ToggleRight
                                    : ToggleLeft,
                                show: !archived && canManage,
                                onSelect: () =>
                                    router.patch(`${base}/${promo.id}/toggle`),
                            },
                            {
                                label: 'Edit',
                                icon: Pencil,
                                show: !archived && canManage,
                                onSelect: () =>
                                    router.visit(`${base}/${promo.id}/edit`),
                            },
                            {
                                label: 'Archive',
                                icon: Trash2,
                                destructive: true,
                                show: !archived && canManage,
                                onSelect: () => archivePromotion(promo),
                            },
                            {
                                label: 'Restore',
                                icon: RotateCcw,
                                show: archived && canManage,
                                onSelect: () =>
                                    router.patch(`${base}/${promo.id}/restore`),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>
    </ShopLayout>
</template>
