<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import {
    DataTable,
    RowActions,
    StatusPill,
    type DataTableHeader,
} from '@/components/data-table';
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    useDataTable,
    usePaginatorTable,
    type DataTableOptions,
} from '@/composables/useDataTable';
import {
    Archive,
    BadgeDollarSign,
    CheckCircle2,
    Pencil,
    Percent,
    Plus,
    RotateCcw,
    Tag,
    ToggleLeft,
    ToggleRight,
    Trash2,
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
const base = computed(() =>
    isOwner.value ? '/shop/operations/promos' : '/staff/operations/promos',
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
    { text: 'Name', value: 'name' },
    { text: 'Type', value: 'type' },
    { text: 'Value', value: 'value' },
    { text: 'Min. order', value: 'min_order_amount' },
    { text: 'Validity', value: 'validity' },
    { text: 'Orders used', value: 'orders_count', align: 'center' },
    { text: 'Status', value: 'status' },
    { text: 'Actions', value: 'actions', align: 'center', width: 150 },
];

// ─── Delete ───────────────────────────────────────────────────────────────────

const toDelete = ref<Promotion | null>(null);
const isDeleteOpen = ref(false);

function confirmDelete() {
    if (!toDelete.value) return;
    router.delete(`${base.value}/${toDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteOpen.value = false;
            toDelete.value = null;
        },
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
</script>

<template>
    <Head title="Promotions" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Promotions">
        <div class="space-y-6 px-6">
            <!-- Header -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2 class="text-lg font-semibold">Promotions</h2>
                    <p class="text-sm text-muted-foreground">
                        Manage discount promotions applied to customer orders.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        @click="router.visit(`${base}?archived=${!archived}`)"
                    >
                        <Archive class="mr-2 h-4 w-4" />
                        {{ archived ? 'Back to Active' : 'View Archived' }}
                    </Button>
                    <Button
                        v-if="!archived"
                        @click="router.visit(`${base}/create`)"
                    >
                        <Plus class="mr-2 h-4 w-4" /> New Promotion
                    </Button>
                </div>
            </div>

            <!-- Stats -->
            <div v-if="!archived" class="grid grid-cols-2 gap-4 sm:grid-cols-5">
                <Card>
                    <CardContent class="flex items-center gap-3 pt-4 pb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100"
                        >
                            <Tag class="h-5 w-5 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.total }}</p>
                            <p class="text-xs text-muted-foreground">Total</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 pt-4 pb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100"
                        >
                            <CheckCircle2 class="h-5 w-5 text-green-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.active }}</p>
                            <p class="text-xs text-muted-foreground">Active</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 pt-4 pb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100"
                        >
                            <XCircle class="h-5 w-5 text-red-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">
                                {{ stats.inactive }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Inactive
                            </p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 pt-4 pb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-100"
                        >
                            <Percent class="h-5 w-5 text-violet-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">
                                {{ stats.percentage }}
                            </p>
                            <p class="text-xs text-muted-foreground">% Off</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 pt-4 pb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100"
                        >
                            <BadgeDollarSign class="h-5 w-5 text-amber-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.fixed }}</p>
                            <p class="text-xs text-muted-foreground">Fixed ₱</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Table -->
            <DataTable
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
                    <StatusPill
                        v-else
                        :tone="promo.is_active ? 'success' : 'danger'"
                    >
                        {{ promo.is_active ? 'Active' : 'Inactive' }}
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
                                show: !archived,
                                onSelect: () =>
                                    router.patch(`${base}/${promo.id}/toggle`),
                            },
                            {
                                label: 'Edit',
                                icon: Pencil,
                                show: !archived,
                                onSelect: () =>
                                    router.visit(`${base}/${promo.id}/edit`),
                            },
                            {
                                label: 'Archive',
                                icon: Trash2,
                                destructive: true,
                                show: !archived,
                                onSelect: () => {
                                    toDelete = promo;
                                    isDeleteOpen = true;
                                },
                            },
                            {
                                label: 'Restore',
                                icon: RotateCcw,
                                show: archived,
                                onSelect: () =>
                                    router.patch(`${base}/${promo.id}/restore`),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>

        <!-- Delete confirm -->
        <AlertDialog v-model:open="isDeleteOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Archive Promotion</AlertDialogTitle>
                    <AlertDialogDescription>
                        Archive <strong>{{ toDelete?.name }}</strong
                        >? It will no longer be available for new orders.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <Button variant="outline" @click="isDeleteOpen = false"
                        >Cancel</Button
                    >
                    <Button variant="destructive" @click="confirmDelete"
                        >Archive</Button
                    >
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </ShopLayout>
</template>
