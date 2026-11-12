<script setup lang="ts">
import {
    DataTable,
    DataTableToolbar,
    RowActions,
    StatusPill,
    type DataTableHeader,
} from '@/components/data-table';
import InputError from '@/components/InputError.vue';
import PriceListStatusPill, {
    type PriceListStatus,
} from '@/components/supplier/PriceListStatusPill.vue';
import SupplierPurchaseOrders, {
    type PortalPurchaseOrder,
} from '@/components/supplier/SupplierPurchaseOrders.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import type { DataTableOptions } from '@/composables/useDataTable';
import SupplierLayout from '@/layouts/supplier/SupplierLayout.vue';
import { compressPhoto } from '@/lib/compressPhoto';
import { router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import {
    Camera,
    Download,
    FileImage,
    FileSpreadsheet,
    Loader2,
    Package,
    Pencil,
    Plus,
    RefreshCcw,
    ScanText,
    Trash2,
    Upload,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface SupplyItem {
    public_id: string;
    name: string;
    unit: string | null;
    stock_quantity: string;
    unit_price: string;
    updated_at: string;
}

interface MatchResult {
    item: string;
    name: string;
    expected_price: number;
    found_price: number | null;
    result: 'matched' | 'price_mismatch' | 'not_found';
}

interface PriceList {
    public_id: string;
    status: PriceListStatus;
    photo_url: string;
    uploaded_at: string | null;
    checked_at: string | null;
    matched_count: number;
    item_count: number;
    results: MatchResult[];
}

const props = defineProps<{
    supplier: { name: string; shop_name: string | null };
    items: {
        data: SupplyItem[];
        current_page: number;
        per_page: number;
        total: number;
    };
    counts: { total: number; in_stock: number; out_of_stock: number };
    priceLists: PriceList[];
    latestPriceList: PriceList | null;
    filters: { search: string; stock: string; per_page: number };
    purchaseOrders: { orders: PortalPurchaseOrder[]; awaiting: number };
}>();

const BASE = '/supplier-portal';
const DEFAULT_SORT = 'name';

/* ─── KPI cards ─────────────────────────────── */

const statCards = computed(() => [
    { label: 'Supplies listed', value: props.counts.total },
    { label: 'In stock', value: props.counts.in_stock },
    { label: 'Out of stock', value: props.counts.out_of_stock },
]);

/* ─── Supplies table (backend search, filter, sort, paging) ─── */

const search = ref(props.filters.search ?? '');
const stock = ref(props.filters.stock ?? '');
const tableLoading = ref(false);
const query = new URLSearchParams(window.location.search);

const serverOptions = ref<DataTableOptions>({
    page: props.items.current_page,
    rowsPerPage: props.items.per_page,
    sortBy: query.get('sort_by') || DEFAULT_SORT,
    sortType: query.get('sort_direction') === 'desc' ? 'desc' : 'asc',
});

const headers: DataTableHeader[] = [
    { text: 'Supply', value: 'name', sortable: true },
    { text: 'Unit', value: 'unit', sortable: true },
    {
        text: 'In stock',
        value: 'stock_quantity',
        sortable: true,
        align: 'right',
    },
    { text: 'Unit price', value: 'unit_price', sortable: true, align: 'right' },
    { text: 'Stock', value: 'availability' },
    { text: 'Actions', value: 'actions', align: 'center', width: 96 },
];

const stockOptions = computed(() => [
    { value: '', label: 'All', count: props.counts.total },
    { value: 'in_stock', label: 'In stock', count: props.counts.in_stock },
    {
        value: 'out_of_stock',
        label: 'Out of stock',
        count: props.counts.out_of_stock,
    },
]);

function applyFilters() {
    router.get(
        BASE,
        {
            search: search.value || undefined,
            stock: stock.value || undefined,
            sort_by: serverOptions.value.sortBy,
            sort_direction: serverOptions.value.sortType,
            page: serverOptions.value.page,
            per_page: serverOptions.value.rowsPerPage,
        },
        {
            only: ['items', 'counts', 'filters'],
            preserveState: true,
            preserveScroll: true,
            replace: true,
            showProgress: false,
            onStart: () => (tableLoading.value = true),
            onFinish: () => (tableLoading.value = false),
        },
    );
}

// A changed search or filter always starts again from the first page.
function filterTable() {
    if (serverOptions.value.page !== 1) {
        serverOptions.value.page = 1;
        return;
    }
    applyFilters();
}

function setStock(value: string) {
    stock.value = value;
    filterTable();
}

function resetFilters() {
    search.value = '';
    stock.value = '';
    const changed =
        serverOptions.value.page !== 1 ||
        serverOptions.value.sortBy !== DEFAULT_SORT ||
        serverOptions.value.sortType !== 'asc';
    serverOptions.value = {
        ...serverOptions.value,
        page: 1,
        sortBy: DEFAULT_SORT,
        sortType: 'asc',
    };
    if (!changed) applyFilters();
}

watch(
    [
        () => serverOptions.value.rowsPerPage,
        () => serverOptions.value.sortBy,
        () => serverOptions.value.sortType,
    ],
    () => {
        // Sorting or a new page size returns to page 1; the page watch below then reloads.
        if (serverOptions.value.page !== 1) {
            serverOptions.value.page = 1;
            return;
        }
        applyFilters();
    },
);
watch(
    () => serverOptions.value.page,
    () => applyFilters(),
);

const peso = new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
});
const quantity = new Intl.NumberFormat('en-PH', { maximumFractionDigits: 2 });

/* ─── Add / edit supply ─────────────────────── */

const dialogOpen = ref(false);
const editing = ref<SupplyItem | null>(null);
const itemForm = useForm({
    name: '',
    unit: '',
    stock_quantity: '' as string | number,
    unit_price: '' as string | number,
});

function openCreate() {
    editing.value = null;
    itemForm.reset();
    itemForm.clearErrors();
    dialogOpen.value = true;
}

function openEdit(item: SupplyItem) {
    editing.value = item;
    itemForm.clearErrors();
    itemForm.name = item.name;
    itemForm.unit = item.unit ?? '';
    itemForm.stock_quantity = Number(item.stock_quantity);
    itemForm.unit_price = Number(item.unit_price);
    dialogOpen.value = true;
}

function saveItem() {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            dialogOpen.value = false;
            itemForm.reset();
        },
    };

    if (editing.value) {
        itemForm.put(`${BASE}/items/${editing.value.public_id}`, options);
    } else {
        itemForm.post(`${BASE}/items`, options);
    }
}

const removingId = ref<string | null>(null);

function removeItem(item: SupplyItem) {
    if (!window.confirm(`Remove ${item.name} from your supplies?`)) return;

    removingId.value = item.public_id;
    router.delete(`${BASE}/items/${item.public_id}`, {
        preserveScroll: true,
        onFinish: () => (removingId.value = null),
    });
}

/* ─── Import from a CSV or a scanned price list ─── */

interface ImportRow {
    name: string;
    unit: string;
    stock_quantity: number | string;
    unit_price: number | string;
    existing: boolean;
    current_price: number | null;
}

const csvInput = ref<HTMLInputElement | null>(null);
const scanInput = ref<HTMLInputElement | null>(null);
const reading = ref<'csv' | 'photo' | null>(null);
const importError = ref<string | null>(null);
const reviewOpen = ref(false);
const reviewSource = ref<'csv' | 'photo'>('csv');
const reviewRows = ref<ImportRow[]>([]);
const savingImport = ref(false);
const importErrors = ref<Record<string, string>>({});

const newCount = computed(
    () => reviewRows.value.filter((row) => !row.existing).length,
);
const updateCount = computed(
    () => reviewRows.value.filter((row) => row.existing).length,
);

/** Reads the file on the server into rows to review; nothing is saved yet. */
async function readImport(kind: 'csv' | 'photo', event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    input.value = '';
    if (!file) return;

    importError.value = null;
    reading.value = kind;

    try {
        const body = new FormData();
        if (kind === 'csv') {
            body.append('csv_file', file);
        } else {
            // A smaller photo uploads faster and stays inside the OCR service's size limit.
            body.append('photo', await compressPhoto(file));
        }

        const { data } = await axios.post<{ rows: ImportRow[] }>(
            `${BASE}/items/import/preview`,
            body,
        );
        // A blank cell comes back as null; the inputs show it empty.
        reviewRows.value = data.rows.map((row) => ({
            ...row,
            stock_quantity: row.stock_quantity ?? '',
            unit_price: row.unit_price ?? '',
        }));
        reviewSource.value = kind;
        importErrors.value = {};
        reviewOpen.value = true;
    } catch (error) {
        const response = axios.isAxiosError(error) ? error.response : undefined;
        const errors = response?.data?.errors as
            | Record<string, string[]>
            | undefined;
        importError.value =
            (errors && Object.values(errors)[0]?.[0]) ||
            (response?.status === 429
                ? 'Too many uploads in a short time. Wait a minute and try again.'
                : 'The file could not be read. Please try again.');
    } finally {
        reading.value = null;
    }
}

function removeReviewRow(index: number) {
    reviewRows.value.splice(index, 1);
    // Row errors are keyed by position, so they no longer line up.
    importErrors.value = {};
    if (reviewRows.value.length === 0) reviewOpen.value = false;
}

function rowError(index: number, field: string) {
    return importErrors.value[`items.${index}.${field}`];
}

function rowLabel(row: ImportRow, index: number) {
    return row.name || `row ${index + 1}`;
}

function saveImport() {
    router.post(
        `${BASE}/items/import`,
        {
            items: reviewRows.value.map((row) => ({
                name: row.name,
                unit: row.unit,
                stock_quantity: row.stock_quantity,
                unit_price: row.unit_price,
            })),
        },
        {
            preserveScroll: true,
            onStart: () => (savingImport.value = true),
            onFinish: () => (savingImport.value = false),
            onError: (errors) => (importErrors.value = errors),
            onSuccess: () => {
                reviewOpen.value = false;
                reviewRows.value = [];
            },
        },
    );
}

/* ─── Price-list upload and OCR result ─────── */

const photoInput = ref<HTMLInputElement | null>(null);
const preparing = ref(false);
const uploadForm = useForm<{ photo: File | null }>({ photo: null });
const uploading = computed(() => preparing.value || uploadForm.processing);

async function onPhotoChosen(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    input.value = '';
    if (!file) return;

    preparing.value = true;
    try {
        // A smaller photo uploads faster and stays inside the OCR service's size limit.
        uploadForm.photo = await compressPhoto(file);
    } finally {
        preparing.value = false;
    }

    uploadForm.post(`${BASE}/price-lists`, {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => uploadForm.reset(),
    });
}

const rechecking = ref(false);

function recheck(priceList: PriceList) {
    router.post(
        `${BASE}/price-lists/${priceList.public_id}/recheck`,
        {},
        {
            preserveScroll: true,
            onStart: () => (rechecking.value = true),
            onFinish: () => (rechecking.value = false),
        },
    );
}

const resultLabels: Record<MatchResult['result'], string> = {
    matched: 'Matches',
    price_mismatch: 'Different price',
    not_found: 'Not on photo',
};
const resultTones = {
    matched: 'success',
    price_mismatch: 'warning',
    not_found: 'danger',
} as const;

function formatDate(value: string | null) {
    if (!value) return '—';
    return new Date(value).toLocaleString('en-PH', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}
</script>

<template>
    <SupplierLayout
        title="Supplier Dashboard"
        :supplier-name="supplier.name"
        :shop-name="supplier.shop_name"
    >
        <div>
            <h1 class="text-xl font-semibold">Your supplies</h1>
            <p class="text-sm text-muted-foreground">
                Keep your stock and prices current. Add supplies one by one,
                import a CSV, or scan your printed price list.
            </p>
        </div>

        <!-- Orders the shop sent: answered first, before the supply list -->
        <SupplierPurchaseOrders
            :orders="purchaseOrders.orders"
            :awaiting="purchaseOrders.awaiting"
            :shop-name="supplier.shop_name"
        />

        <!-- KPI cards -->
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            <Card v-for="item in statCards" :key="item.label">
                <CardContent class="p-4">
                    <p class="mb-3 text-xs font-medium text-muted-foreground">
                        {{ item.label }}
                    </p>
                    <p class="text-2xl font-bold">{{ item.value }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <p class="mb-3 text-xs font-medium text-muted-foreground">
                        Price list check
                    </p>
                    <PriceListStatusPill :status="latestPriceList?.status" />
                </CardContent>
            </Card>
        </div>

        <!-- Import: hidden file pickers, a hint and any read error -->
        <input
            ref="csvInput"
            type="file"
            accept=".csv,text/csv"
            class="sr-only"
            tabindex="-1"
            aria-label="Choose a CSV file of supplies"
            @change="readImport('csv', $event)"
        />
        <input
            ref="scanInput"
            type="file"
            accept="image/jpeg,image/png,image/webp"
            class="sr-only"
            tabindex="-1"
            aria-label="Choose a photo of your price list"
            @change="readImport('photo', $event)"
        />
        <div
            class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 text-sm text-muted-foreground"
        >
            <p>
                Add many supplies at once: import a CSV, or scan a photo of your
                printed price list. You review every row before it is saved.
            </p>
            <a
                :href="`${BASE}/items/import/template`"
                class="inline-flex min-h-11 items-center gap-1.5 font-medium text-primary underline-offset-4 hover:underline focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
            >
                <Download class="h-4 w-4" aria-hidden="true" />
                CSV template
            </a>
        </div>
        <div
            v-if="importError"
            role="alert"
            class="flex items-start justify-between gap-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"
        >
            <span class="pt-1.5">{{ importError }}</span>
            <button
                type="button"
                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md hover:bg-red-100 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none dark:hover:bg-red-900/40"
                aria-label="Dismiss import error"
                @click="importError = null"
            >
                <X class="h-4 w-4" aria-hidden="true" />
            </button>
        </div>

        <!-- Supplies table -->
        <DataTable
            v-model:options="serverOptions"
            :headers="headers"
            :items="items.data"
            :total="items.total"
            :loading="tableLoading"
            item-key="public_id"
            noun="supplies"
            :empty-icon="Package"
            empty-message="No supplies match. Add the supplies you have in stock."
            :min-width="560"
            :rows-items="[10, 25, 50]"
            :selectable="false"
            @reset="resetFilters"
        >
            <template #toolbar>
                <DataTableToolbar
                    v-model:search="search"
                    :status="stock"
                    :statuses="stockOptions"
                    search-placeholder="Search supply or unit..."
                    search-label="Search supplies"
                    status-label="Filter by stock"
                    @update:status="setStock"
                    @submit="filterTable"
                >
                    <template #actions>
                        <Button
                            size="sm"
                            variant="outline"
                            @click="resetFilters"
                        >
                            <RefreshCcw class="mr-1.5 h-4 w-4" /> Reset
                        </Button>
                        <Button
                            size="sm"
                            variant="outline"
                            :disabled="reading !== null"
                            @click="csvInput?.click()"
                        >
                            <Loader2
                                v-if="reading === 'csv'"
                                class="mr-1.5 h-4 w-4 animate-spin"
                            />
                            <FileSpreadsheet v-else class="mr-1.5 h-4 w-4" />
                            {{
                                reading === 'csv'
                                    ? 'Reading CSV...'
                                    : 'Import CSV'
                            }}
                        </Button>
                        <Button
                            size="sm"
                            variant="outline"
                            :disabled="reading !== null"
                            @click="scanInput?.click()"
                        >
                            <Loader2
                                v-if="reading === 'photo'"
                                class="mr-1.5 h-4 w-4 animate-spin"
                            />
                            <Camera v-else class="mr-1.5 h-4 w-4" />
                            {{
                                reading === 'photo'
                                    ? 'Scanning photo...'
                                    : 'Scan price list'
                            }}
                        </Button>
                        <Button size="sm" @click="openCreate">
                            <Plus class="mr-1.5 h-4 w-4" /> Add supply
                        </Button>
                    </template>
                </DataTableToolbar>
            </template>

            <template #item-name="item">
                <p class="font-semibold">{{ item.name }}</p>
            </template>

            <template #item-unit="item">
                <span class="text-muted-foreground">{{
                    item.unit || '—'
                }}</span>
            </template>

            <template #item-stock_quantity="item">
                <span class="tabular-nums">
                    {{ quantity.format(Number(item.stock_quantity)) }}
                </span>
            </template>

            <template #item-unit_price="item">
                <span class="font-medium tabular-nums">
                    {{ peso.format(Number(item.unit_price)) }}
                </span>
            </template>

            <template #item-availability="item">
                <StatusPill
                    :tone="
                        Number(item.stock_quantity) > 0 ? 'success' : 'danger'
                    "
                >
                    {{
                        Number(item.stock_quantity) > 0
                            ? 'In stock'
                            : 'Out of stock'
                    }}
                </StatusPill>
            </template>

            <template #item-actions="item">
                <RowActions
                    :label="item.name"
                    :actions="[
                        {
                            label: 'Edit',
                            icon: Pencil,
                            onSelect: () => openEdit(item),
                        },
                        {
                            label: 'Remove',
                            icon:
                                removingId === item.public_id
                                    ? Loader2
                                    : Trash2,
                            destructive: true,
                            disabled: removingId === item.public_id,
                            onSelect: () => removeItem(item),
                        },
                    ]"
                />
            </template>
        </DataTable>

        <!-- Price list -->
        <section
            class="rounded-[14px] border bg-background p-4 sm:p-6"
            aria-labelledby="price-list-heading"
        >
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2
                        id="price-list-heading"
                        class="flex items-center gap-2 font-semibold"
                    >
                        <ScanText
                            class="h-4 w-4 text-muted-foreground"
                            aria-hidden="true"
                        />
                        Price list
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        Upload a clear, well-lit photo of your printed price
                        list. OCR reads it and compares each supply's name and
                        price with what you entered.
                    </p>
                </div>
                <div>
                    <input
                        id="price-list-photo"
                        ref="photoInput"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="sr-only"
                        :disabled="uploading"
                        @change="onPhotoChosen"
                    />
                    <Button
                        :disabled="uploading || counts.total === 0"
                        @click="photoInput?.click()"
                    >
                        <Loader2
                            v-if="uploading"
                            class="mr-1.5 h-4 w-4 animate-spin"
                        />
                        <Upload v-else class="mr-1.5 h-4 w-4" />
                        {{ uploading ? 'Checking photo...' : 'Upload photo' }}
                    </Button>
                </div>
            </div>
            <InputError class="mt-2" :message="uploadForm.errors.photo" />
            <p
                v-if="counts.total === 0"
                class="mt-2 text-xs text-muted-foreground"
            >
                Add at least one supply first, so the price list has something
                to be checked against.
            </p>

            <div
                v-if="latestPriceList"
                class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,2fr)]"
            >
                <a
                    :href="latestPriceList.photo_url"
                    target="_blank"
                    rel="noopener"
                    class="block overflow-hidden rounded-lg border bg-muted focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                >
                    <img
                        :src="latestPriceList.photo_url"
                        alt="Latest uploaded price list"
                        class="max-h-80 w-full object-contain"
                    />
                </a>
                <div class="min-w-0 space-y-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <PriceListStatusPill :status="latestPriceList.status" />
                        <span class="text-sm text-muted-foreground">
                            {{ latestPriceList.matched_count }} of
                            {{ latestPriceList.item_count }} supplies match ·
                            checked {{ formatDate(latestPriceList.checked_at) }}
                        </span>
                        <Button
                            size="sm"
                            variant="outline"
                            :disabled="rechecking"
                            @click="recheck(latestPriceList)"
                        >
                            <Loader2
                                v-if="rechecking"
                                class="mr-1.5 h-4 w-4 animate-spin"
                            />
                            <RefreshCcw v-else class="mr-1.5 h-4 w-4" />
                            Re-check
                        </Button>
                    </div>
                    <p
                        v-if="latestPriceList.status === 'unreadable'"
                        class="text-sm text-muted-foreground"
                    >
                        The photo could not be read. Upload a sharper photo
                        taken straight on, with the whole list in frame.
                    </p>
                    <div v-else class="overflow-x-auto rounded-lg border">
                        <table class="w-full min-w-[340px] text-sm">
                            <caption class="sr-only">
                                Price list check per supply
                            </caption>
                            <thead class="bg-muted/50">
                                <tr
                                    class="text-left text-xs font-medium tracking-wide text-muted-foreground uppercase"
                                >
                                    <th scope="col" class="px-3 py-2">
                                        Supply
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-3 py-2 text-right"
                                    >
                                        Your price
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-3 py-2 text-right"
                                    >
                                        On photo
                                    </th>
                                    <th scope="col" class="px-3 py-2">
                                        Result
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="row in latestPriceList.results"
                                    :key="row.item"
                                >
                                    <td class="px-3 py-2 font-medium">
                                        {{ row.name }}
                                    </td>
                                    <td
                                        class="px-3 py-2 text-right tabular-nums"
                                    >
                                        {{ peso.format(row.expected_price) }}
                                    </td>
                                    <td
                                        class="px-3 py-2 text-right tabular-nums"
                                    >
                                        {{
                                            row.found_price === null
                                                ? '—'
                                                : peso.format(row.found_price)
                                        }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <StatusPill
                                            :tone="resultTones[row.result]"
                                        >
                                            {{ resultLabels[row.result] }}
                                        </StatusPill>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div
                v-else
                class="mt-6 flex flex-col items-center gap-2 rounded-lg border border-dashed py-10 text-center text-sm text-muted-foreground"
            >
                <FileImage class="h-8 w-8" aria-hidden="true" />
                No price list uploaded yet.
            </div>

            <div v-if="priceLists.length > 1" class="mt-6">
                <h3 class="mb-2 text-sm font-medium">Earlier uploads</h3>
                <ul class="divide-y rounded-lg border">
                    <li
                        v-for="list in priceLists.slice(1)"
                        :key="list.public_id"
                        class="flex flex-wrap items-center justify-between gap-2 px-3 py-2 text-sm"
                    >
                        <a
                            :href="list.photo_url"
                            target="_blank"
                            rel="noopener"
                            class="flex items-center gap-2 hover:underline"
                        >
                            <FileImage
                                class="h-4 w-4 text-muted-foreground"
                                aria-hidden="true"
                            />
                            {{ formatDate(list.uploaded_at) }}
                        </a>
                        <PriceListStatusPill :status="list.status" />
                    </li>
                </ul>
            </div>
        </section>

        <!-- Review imported supplies before they are saved -->
        <Dialog v-model:open="reviewOpen">
            <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-4xl">
                <DialogHeader>
                    <DialogTitle>
                        Review {{ reviewRows.length }}
                        {{ reviewRows.length === 1 ? 'supply' : 'supplies' }}
                    </DialogTitle>
                    <DialogDescription>
                        {{
                            reviewSource === 'photo'
                                ? 'Read from your photo by OCR. Check each name and price against your list, fill in the stock, and remove any row that is not a product.'
                                : 'Read from your CSV file. Correct anything that looks wrong.'
                        }}
                        Nothing is saved until you click Save.
                    </DialogDescription>
                </DialogHeader>

                <p class="text-sm">
                    <span class="font-semibold">{{ newCount }}</span> new ·
                    <span class="font-semibold">{{ updateCount }}</span>
                    already listed (their unit, stock and price will be updated)
                </p>

                <form @submit.prevent="saveImport">
                    <div class="overflow-x-auto rounded-lg border">
                        <table class="w-full min-w-[680px] text-sm">
                            <caption class="sr-only">
                                Supplies to import
                            </caption>
                            <thead class="bg-muted/50">
                                <tr
                                    class="text-left text-xs font-medium tracking-wide text-muted-foreground uppercase"
                                >
                                    <th scope="col" class="px-3 py-2">
                                        Supply
                                    </th>
                                    <th scope="col" class="w-36 px-3 py-2">
                                        Unit
                                    </th>
                                    <th scope="col" class="w-28 px-3 py-2">
                                        In stock
                                    </th>
                                    <th scope="col" class="w-32 px-3 py-2">
                                        Unit price (₱)
                                    </th>
                                    <th scope="col" class="w-28 px-3 py-2">
                                        Status
                                    </th>
                                    <th scope="col" class="w-12 px-2 py-2">
                                        <span class="sr-only">Remove</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="(row, index) in reviewRows"
                                    :key="index"
                                    class="align-top"
                                >
                                    <td class="px-3 py-2">
                                        <Input
                                            v-model="row.name"
                                            :aria-label="`Name of row ${index + 1}`"
                                            :class="{
                                                'border-red-500': rowError(
                                                    index,
                                                    'name',
                                                ),
                                            }"
                                        />
                                        <InputError
                                            :message="rowError(index, 'name')"
                                        />
                                    </td>
                                    <td class="px-3 py-2">
                                        <Input
                                            v-model="row.unit"
                                            :aria-label="`Unit of ${rowLabel(row, index)}`"
                                            placeholder="e.g. gallon"
                                        />
                                        <InputError
                                            :message="rowError(index, 'unit')"
                                        />
                                    </td>
                                    <td class="px-3 py-2">
                                        <Input
                                            v-model="row.stock_quantity"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            :aria-label="`Stock of ${rowLabel(row, index)}`"
                                            :class="{
                                                'border-red-500': rowError(
                                                    index,
                                                    'stock_quantity',
                                                ),
                                            }"
                                        />
                                        <InputError
                                            :message="
                                                rowError(
                                                    index,
                                                    'stock_quantity',
                                                )
                                            "
                                        />
                                    </td>
                                    <td class="px-3 py-2">
                                        <Input
                                            v-model="row.unit_price"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            :aria-label="`Price of ${rowLabel(row, index)}`"
                                            :class="{
                                                'border-red-500': rowError(
                                                    index,
                                                    'unit_price',
                                                ),
                                            }"
                                        />
                                        <InputError
                                            :message="
                                                rowError(index, 'unit_price')
                                            "
                                        />
                                    </td>
                                    <td class="px-3 py-2 pt-4">
                                        <StatusPill
                                            :tone="
                                                row.existing
                                                    ? 'warning'
                                                    : 'success'
                                            "
                                        >
                                            {{
                                                row.existing ? 'Updates' : 'New'
                                            }}
                                        </StatusPill>
                                        <p
                                            v-if="
                                                row.existing &&
                                                row.current_price !== null
                                            "
                                            class="mt-1 text-xs text-muted-foreground tabular-nums"
                                        >
                                            Now
                                            {{ peso.format(row.current_price) }}
                                        </p>
                                    </td>
                                    <td class="px-2 py-2">
                                        <button
                                            type="button"
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-md text-red-600 hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                            :aria-label="`Remove ${rowLabel(row, index)} from the import`"
                                            @click="removeReviewRow(index)"
                                        >
                                            <Trash2
                                                class="h-4 w-4"
                                                aria-hidden="true"
                                            />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <InputError class="mt-2" :message="importErrors.items" />

                    <DialogFooter class="mt-4">
                        <Button
                            type="button"
                            variant="outline"
                            @click="reviewOpen = false"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            :disabled="savingImport || reviewRows.length === 0"
                        >
                            <Loader2
                                v-if="savingImport"
                                class="mr-1.5 h-4 w-4 animate-spin"
                            />
                            Save {{ reviewRows.length }}
                            {{
                                reviewRows.length === 1 ? 'supply' : 'supplies'
                            }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Add / edit supply dialog -->
        <Dialog v-model:open="dialogOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>
                        {{ editing ? 'Edit supply' : 'Add supply' }}
                    </DialogTitle>
                    <DialogDescription>
                        Use the same name and price as on your printed price
                        list so the photo check can match them.
                    </DialogDescription>
                </DialogHeader>
                <form class="space-y-4" @submit.prevent="saveItem">
                    <div class="space-y-1.5">
                        <label for="supply-name" class="text-sm font-medium"
                            >Name <span class="text-red-500">*</span></label
                        >
                        <Input
                            id="supply-name"
                            v-model="itemForm.name"
                            required
                            placeholder="e.g. Liquid detergent"
                        />
                        <InputError :message="itemForm.errors.name" />
                    </div>
                    <div class="space-y-1.5">
                        <label for="supply-unit" class="text-sm font-medium"
                            >Unit</label
                        >
                        <Input
                            id="supply-unit"
                            v-model="itemForm.unit"
                            placeholder="e.g. gallon, sack, box"
                        />
                        <InputError :message="itemForm.errors.unit" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label
                                for="supply-stock"
                                class="text-sm font-medium"
                                >In stock
                                <span class="text-red-500">*</span></label
                            >
                            <Input
                                id="supply-stock"
                                v-model="itemForm.stock_quantity"
                                type="number"
                                min="0"
                                step="0.01"
                                required
                            />
                            <InputError
                                :message="itemForm.errors.stock_quantity"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="supply-price"
                                class="text-sm font-medium"
                                >Unit price (₱)
                                <span class="text-red-500">*</span></label
                            >
                            <Input
                                id="supply-price"
                                v-model="itemForm.unit_price"
                                type="number"
                                min="0"
                                step="0.01"
                                required
                            />
                            <InputError :message="itemForm.errors.unit_price" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="dialogOpen = false"
                            >Cancel</Button
                        >
                        <Button type="submit" :disabled="itemForm.processing">
                            <Loader2
                                v-if="itemForm.processing"
                                class="mr-1.5 h-4 w-4 animate-spin"
                            />
                            {{ editing ? 'Save changes' : 'Add supply' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </SupplierLayout>
</template>
