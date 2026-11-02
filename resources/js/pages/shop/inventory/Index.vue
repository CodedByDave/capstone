<script setup lang="ts">
import {
    BulkActionButton,
    DataTable,
    DataTableToolbar,
    RowActions,
    StatusPill,
    statusLabel,
    type DataTableHeader,
    type StatusTone,
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
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { DataTableOptions } from '@/composables/useDataTable';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { formatQuantity, packageStock } from '@/lib/inventoryStock';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Archive,
    ChevronDown,
    Download,
    Eye,
    Layers,
    Package,
    Pencil,
    Plus,
    RefreshCcw,
    Truck,
} from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Category {
    id: number;
    name: string;
}
interface Supplier {
    id: number;
    name: string;
}

interface InventoryItem {
    id: number;
    name: string;
    unit: string;
    /** How the item is bought (e.g. "bottle") and how much of `unit` one holds (e.g. 5000). */
    package_unit?: string | null;
    package_size?: number | null;
    quantity: number;
    min_stock: number;
    max_stock: number | null;
    unit_price: string | null;
    selling_price: string | null;
    status: 'active' | 'inactive';
    category: Category | null;
    supplier: Supplier | null;
}

interface ReorderSuggestion {
    days_left: number;
    lead_time_days: number;
    suggested_quantity: number;
    needs_reorder: boolean;
}

interface Paginator {
    data: InventoryItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    inventory: Paginator;
    categories: Category[];
    suppliers: Supplier[];
    lowStock: InventoryItem[];
    reorderSuggestions: Record<number, ReorderSuggestion>;
    filters: Record<string, string>;
}>();

// ─── Permissions & base path ──────────────────────────────────────────────────

const { isOwner, can } = usePermissions();
const base = computed(() =>
    isOwner.value ? '/shop/inventory' : '/staff/inventory',
);
const supplierBase = computed(() =>
    isOwner.value ? '/shop/supplier' : '/staff/supplier',
);
const categoryBase = computed(() =>
    isOwner.value ? '/shop/inventory/category' : '/staff/inventory/category',
);
const exportBase = computed(() =>
    isOwner.value ? '/shop/export' : '/staff/export',
);
const alertsBase = computed(() =>
    isOwner.value ? '/shop/inventory/alerts' : '/staff/inventory/alerts',
);

// ─── Flash toast ──────────────────────────────────────────────────────────────

const page = usePage<AppPageProps>();

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
        case 'warning':
            toast.warning(flashToast.message);
            break;
        default:
            toast(flashToast.message);
    }
});

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: base.value },
];

// ─── Filters ──────────────────────────────────────────────────────────────────

const search = ref(props.filters.search ?? '');
const categoryId = ref(props.filters.category_id ?? 'all');
const supplierId = ref(props.filters.supplier_id ?? 'all');
const status = ref(props.filters.status ?? 'all');
const stock = ref(props.filters.stock ?? 'all');

const DEFAULT_SORT = { sortBy: 'name', sortType: 'asc' } as const;

const tableLoading = ref(false);
const options = ref<DataTableOptions>({
    page: props.inventory.current_page,
    rowsPerPage: props.inventory.per_page,
    sortBy: props.filters.sort_by ?? DEFAULT_SORT.sortBy,
    sortType: props.filters.sort_direction === 'desc' ? 'desc' : 'asc',
});

const isSorted = computed(
    () =>
        options.value.sortBy !== DEFAULT_SORT.sortBy ||
        options.value.sortType !== DEFAULT_SORT.sortType,
);

const statusOptions = [
    { value: 'all', label: 'All' },
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

const hasFilters = computed(
    () =>
        search.value !== '' ||
        categoryId.value !== 'all' ||
        supplierId.value !== 'all' ||
        status.value !== 'all' ||
        stock.value !== 'all' ||
        isSorted.value,
);

const headers: DataTableHeader[] = [
    { text: 'Item', value: 'name', sortable: true },
    { text: 'Category', value: 'category', sortable: true },
    { text: 'Supplier', value: 'supplier', sortable: true },
    { text: 'On hand', value: 'quantity', sortable: true },
    { text: 'Unit price', value: 'unit_price', sortable: true, align: 'right' },
    { text: 'Status', value: 'status', sortable: true },
    { text: 'Actions', value: 'actions', align: 'center', width: 96 },
];

watch(
    () => props.inventory,
    (inventory) => {
        options.value = {
            ...options.value,
            page: inventory.current_page,
            rowsPerPage: inventory.per_page,
        };
    },
);

watch(
    () => options.value.page,
    (page) => {
        if (page !== props.inventory.current_page) applyFilters(page);
    },
);

watch(status, () => applyFilters());

// Sorting runs on the server so it covers every page, not just the one on screen. The sources
// are separate getters: one getter returning a fresh array would count as changed on every
// reload (the page replaces `options`), and each reload would trigger another.
watch([() => options.value.sortBy, () => options.value.sortType], () =>
    applyFilters(),
);

function applyFilters(page = 1) {
    router.get(
        base.value,
        {
            search: search.value || undefined,
            category_id:
                categoryId.value !== 'all' ? categoryId.value : undefined,
            supplier_id:
                supplierId.value !== 'all' ? supplierId.value : undefined,
            status: status.value !== 'all' ? status.value : undefined,
            stock: stock.value !== 'all' ? stock.value : undefined,
            sort_by: isSorted.value ? options.value.sortBy : undefined,
            sort_direction: isSorted.value ? options.value.sortType : undefined,
            page: page > 1 ? page : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onStart: () => {
                tableLoading.value = true;
            },
            onFinish: () => {
                tableLoading.value = false;
            },
        },
    );
}

function resetFilters() {
    // A status or sort change already reloads through its own watcher.
    const reloadsItself = status.value !== 'all' || isSorted.value;
    search.value = '';
    categoryId.value = 'all';
    supplierId.value = 'all';
    status.value = 'all';
    stock.value = 'all';
    options.value = { ...options.value, ...DEFAULT_SORT, page: 1 };
    if (!reloadsItself) applyFilters();
}

// ─── Archive ───────────────────────────────────────────────────────────────────

const itemToDelete = ref<InventoryItem | null>(null);
const isDeleteOpen = ref(false);

function openDeleteDialog(item: InventoryItem) {
    itemToDelete.value = item;
    isDeleteOpen.value = true;
}

function cancelDelete() {
    isDeleteOpen.value = false;
    setTimeout(() => {
        itemToDelete.value = null;
    }, 200);
}

function confirmDelete() {
    if (!itemToDelete.value) return;
    router.delete(`${base.value}/${itemToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            itemToDelete.value = null;
            toast.success('Product archived successfully.');
        },
        onError: () => toast.error('Failed to archive product.'),
    });
    isDeleteOpen.value = false;
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function isLowStock(item: InventoryItem) {
    return item.quantity <= item.min_stock;
}
function isOutOfStock(item: InventoryItem) {
    return item.quantity === 0;
}

function stockBadge(item: InventoryItem): { label: string; tone: StatusTone } {
    if (isOutOfStock(item)) return { label: 'Out of stock', tone: 'danger' };
    if (isLowStock(item)) return { label: 'Low stock', tone: 'warning' };
    return { label: 'In stock', tone: 'success' };
}

/**
 * How full the stock is, for the bar under the quantity. The target is the item's max stock, or
 * four times its reorder point when no max is set; an item with neither shows a full bar.
 */
function stockPercent(item: InventoryItem): number {
    const quantity = Number(item.quantity) || 0;
    if (quantity <= 0) return 0;

    const max = Number(item.max_stock) || 0;
    const reorder = Number(item.min_stock) || 0;
    const target = max > 0 ? max : reorder > 0 ? reorder * 4 : quantity;

    // A sliver stays visible so a nearly empty item is not mistaken for an empty one.
    return Math.min(100, Math.max(4, Math.round((quantity / target) * 100)));
}

function stockBarClass(item: InventoryItem): string {
    if (isOutOfStock(item)) return 'bg-red-500';
    if (isLowStock(item)) return 'bg-amber-500';
    return 'bg-blue-600';
}

const statCards = computed(() => [
    {
        label: 'Total items',
        value: props.inventory.total,
        icon: Package,
    },
    {
        label: 'Low stock',
        value: props.lowStock.length,
        icon: AlertTriangle,
        // Green while nothing needs reordering, amber when something does.
        dot: props.lowStock.length > 0 ? 'bg-amber-500' : 'bg-emerald-500',
        valueClass: props.lowStock.length > 0 ? 'text-amber-600' : '',
    },
    {
        label: 'Categories',
        value: props.categories.length,
        icon: Layers,
    },
    {
        label: 'Suppliers',
        value: props.suppliers.length,
        icon: Truck,
    },
]);

function formatPrice(price: string | null) {
    if (!price) return '—';
    return `₱${Number(price).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
}

const selected = ref<Array<string | number>>([]);
</script>

<template>
    <Head title="Inventory" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Inventory Management">
        <div class="space-y-6 px-6">
            <!-- Page header -->
            <div
                class="flex flex-wrap items-start justify-between gap-x-4 gap-y-3"
            >
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Inventory</h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Track supplies, stock levels and reorder points.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="h-10 gap-2">
                                <Download class="h-4 w-4" aria-hidden="true" />
                                Export
                                <ChevronDown
                                    class="h-4 w-4 text-muted-foreground"
                                    aria-hidden="true"
                                />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="min-w-52">
                            <DropdownMenuItem as-child class="min-h-9">
                                <a :href="`${exportBase}/inventory`" download>
                                    <Download
                                        class="h-4 w-4"
                                        aria-hidden="true"
                                    />
                                    Inventory (CSV)
                                </a>
                            </DropdownMenuItem>
                            <DropdownMenuItem as-child class="min-h-9">
                                <a
                                    :href="`${exportBase}/stock-movements`"
                                    download
                                >
                                    <Download
                                        class="h-4 w-4"
                                        aria-hidden="true"
                                    />
                                    Stock movements (CSV)
                                </a>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="h-10 gap-2">
                                Manage
                                <ChevronDown
                                    class="h-4 w-4 text-muted-foreground"
                                    aria-hidden="true"
                                />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="min-w-44">
                            <DropdownMenuItem
                                class="min-h-9 cursor-pointer"
                                @select="router.visit(categoryBase)"
                            >
                                <Layers class="h-4 w-4" aria-hidden="true" />
                                Categories
                            </DropdownMenuItem>
                            <DropdownMenuItem
                                class="min-h-9 cursor-pointer"
                                @select="router.visit(supplierBase)"
                            >
                                <Truck class="h-4 w-4" aria-hidden="true" />
                                Suppliers
                            </DropdownMenuItem>
                            <DropdownMenuItem
                                v-if="
                                    isOwner ||
                                    can('Inventory Management', 'view')
                                "
                                class="min-h-9 cursor-pointer"
                                @select="router.visit(`${base}/archive`)"
                            >
                                <Archive class="h-4 w-4" aria-hidden="true" />
                                Archive
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <Button
                        v-if="isOwner || can('Inventory Management', 'create')"
                        class="h-10 bg-blue-600 text-white hover:bg-blue-700"
                        @click="router.visit(`${base}/create`)"
                    >
                        <Plus class="mr-1.5 h-4 w-4" aria-hidden="true" />
                        Add item
                    </Button>
                </div>
            </div>

            <!-- Stats row -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <Card
                    v-for="card in statCards"
                    :key="card.label"
                    class="gap-0 py-0"
                >
                    <CardContent class="p-5">
                        <div class="flex items-center justify-between gap-2">
                            <p
                                class="flex items-center gap-2 text-[11px] font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                {{ card.label }}
                                <span
                                    v-if="card.dot"
                                    class="h-1.5 w-1.5 rounded-full"
                                    :class="card.dot"
                                    aria-hidden="true"
                                />
                            </p>
                            <component
                                :is="card.icon"
                                class="h-4 w-4 shrink-0 text-muted-foreground"
                                aria-hidden="true"
                            />
                        </div>
                        <p
                            class="mt-3 text-3xl font-bold tabular-nums"
                            :class="card.valueClass"
                        >
                            {{ card.value }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Low stock banner -->
            <div
                v-if="lowStock.length > 0"
                class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm"
            >
                <AlertTriangle class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" />
                <div class="flex-1">
                    <p class="font-medium text-amber-800">
                        {{ lowStock.length }} item{{
                            lowStock.length > 1 ? 's are' : ' is'
                        }}
                        running low on stock.
                    </p>
                    <div class="mt-1 flex flex-wrap gap-1">
                        <span
                            v-for="item in lowStock.slice(0, 5)"
                            :key="item.id"
                            class="rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-700"
                        >
                            {{ item.name }} ({{ formatQuantity(item.quantity) }}
                            {{ item.unit }})
                        </span>
                        <span
                            v-if="lowStock.length > 5"
                            class="text-xs text-amber-600"
                        >
                            +{{ lowStock.length - 5 }} more
                        </span>
                    </div>
                </div>
                <Button
                    size="sm"
                    variant="outline"
                    class="shrink-0 border-amber-300 text-amber-700 hover:bg-amber-100"
                    @click="router.visit(alertsBase)"
                >
                    View Alerts
                </Button>
            </div>

            <!-- Filters -->
            <DataTableToolbar
                v-model:search="search"
                v-model:status="status"
                :statuses="statusOptions"
                search-placeholder="Search by name"
                search-label="Search inventory"
                status-label="Filter by item status"
                @submit="applyFilters()"
            >
                <Select
                    v-model="categoryId"
                    @update:model-value="() => applyFilters()"
                >
                    <SelectTrigger
                        class="w-44 data-[size=default]:h-11"
                        aria-label="Filter by category"
                        ><SelectValue placeholder="All categories"
                    /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All categories</SelectItem>
                        <SelectItem
                            v-for="c in categories"
                            :key="c.id"
                            :value="String(c.id)"
                            >{{ c.name }}</SelectItem
                        >
                    </SelectContent>
                </Select>
                <Select
                    v-model="supplierId"
                    @update:model-value="() => applyFilters()"
                >
                    <SelectTrigger
                        class="w-44 data-[size=default]:h-11"
                        aria-label="Filter by supplier"
                        ><SelectValue placeholder="All suppliers"
                    /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All suppliers</SelectItem>
                        <SelectItem
                            v-for="s in suppliers"
                            :key="s.id"
                            :value="String(s.id)"
                            >{{ s.name }}</SelectItem
                        >
                    </SelectContent>
                </Select>
                <Select
                    v-model="stock"
                    @update:model-value="() => applyFilters()"
                >
                    <SelectTrigger
                        class="w-36 data-[size=default]:h-11"
                        aria-label="Filter by stock level"
                        ><SelectValue placeholder="All stock"
                    /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All stock</SelectItem>
                        <SelectItem value="low">Low stock</SelectItem>
                    </SelectContent>
                </Select>
                <Button
                    v-if="hasFilters"
                    variant="ghost"
                    class="h-11"
                    @click="resetFilters"
                >
                    <RefreshCcw class="mr-1.5 h-4 w-4" aria-hidden="true" />
                    Reset
                </Button>
            </DataTableToolbar>

            <!-- Table card -->
            <DataTable
                v-model:options="options"
                v-model:selected="selected"
                :headers="headers"
                :items="inventory.data"
                :total="inventory.total"
                :loading="tableLoading"
                noun="inventory items"
                :row-label="(item) => item.name"
                :empty-icon="Package"
                :empty-message="
                    hasFilters
                        ? 'No inventory items match the current filters.'
                        : 'No inventory items found.'
                "
                :resettable="hasFilters"
                :min-width="1080"
                hide-rows-per-page
                @reset="resetFilters"
            >
                <template #bulk-actions>
                    <BulkActionButton
                        v-if="can('Inventory Management', 'archive')"
                        resource="inventory"
                        action="archive"
                        :ids="selected"
                        noun="products"
                        @done="selected = []"
                    />
                </template>

                <template #item-name="item">
                    <p class="font-semibold">{{ item.name }}</p>
                    <p class="text-xs text-muted-foreground">
                        Unit: {{ item.unit }}
                    </p>
                </template>

                <template #item-category="item">
                    <span :class="item.category ? '' : 'text-muted-foreground'">
                        {{ item.category?.name ?? '—' }}
                    </span>
                </template>

                <template #item-supplier="item">
                    <span :class="item.supplier ? '' : 'text-muted-foreground'">
                        {{ item.supplier?.name ?? '—' }}
                    </span>
                </template>

                <template #item-quantity="item">
                    <div class="min-w-56 py-1">
                        <div
                            class="flex flex-wrap items-center gap-x-2 gap-y-1"
                        >
                            <span
                                class="text-[15px] font-semibold whitespace-nowrap tabular-nums"
                            >
                                <template v-if="packageStock(item)">
                                    {{ packageStock(item)!.full }}
                                    <span
                                        v-if="packageStock(item)!.remainder"
                                        class="font-normal text-muted-foreground"
                                        >+
                                        {{
                                            packageStock(item)!.remainder
                                        }}</span
                                    >
                                </template>
                                <template v-else>
                                    {{ formatQuantity(item.quantity) }}
                                    {{ item.unit }}
                                </template>
                            </span>
                            <StatusPill :tone="stockBadge(item).tone">
                                {{ stockBadge(item).label }}
                            </StatusPill>
                        </div>
                        <div
                            class="mt-1.5 h-1 w-40 overflow-hidden rounded-full bg-muted"
                            role="progressbar"
                            aria-label="Stock level compared with the reorder point"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            :aria-valuenow="stockPercent(item)"
                        >
                            <div
                                class="h-full rounded-full"
                                :class="stockBarClass(item)"
                                :style="{ width: `${stockPercent(item)}%` }"
                            />
                        </div>
                        <p
                            v-if="packageStock(item)"
                            class="mt-1.5 text-xs whitespace-nowrap text-muted-foreground"
                        >
                            {{ formatQuantity(item.quantity) }}
                            {{ item.unit }} total ·
                            {{ packageStock(item)!.perPackage }}
                        </p>
                        <p
                            class="text-xs whitespace-nowrap text-muted-foreground"
                            :class="packageStock(item) ? '' : 'mt-1.5'"
                        >
                            Reorder at {{ formatQuantity(item.min_stock) }}
                            {{ item.unit }}
                        </p>
                        <p
                            v-if="reorderSuggestions[item.id]"
                            class="mt-0.5 text-xs font-medium text-amber-700"
                        >
                            {{
                                reorderSuggestions[item.id].suggested_quantity >
                                0
                                    ? `Order ~${reorderSuggestions[item.id].suggested_quantity} ${item.unit}`
                                    : 'Reorder soon'
                            }}
                            · ~{{
                                Math.ceil(
                                    reorderSuggestions[item.id].days_left,
                                )
                            }}d left,
                            {{ reorderSuggestions[item.id].lead_time_days }}d
                            lead
                        </p>
                    </div>
                </template>

                <template #item-unit_price="item">
                    <span class="font-semibold whitespace-nowrap tabular-nums">
                        {{ formatPrice(item.unit_price) }}
                    </span>
                </template>

                <template #item-status="item">
                    <StatusPill
                        :tone="item.status === 'active' ? 'info' : 'neutral'"
                    >
                        {{ statusLabel(item.status) }}
                    </StatusPill>
                </template>

                <template #item-actions="item">
                    <RowActions
                        :label="item.name"
                        :actions="[
                            {
                                label: 'View',
                                icon: Eye,
                                onSelect: () =>
                                    router.visit(`${base}/${item.id}`),
                            },
                            {
                                label: 'Edit',
                                icon: Pencil,
                                show:
                                    isOwner ||
                                    can('Inventory Management', 'update'),
                                onSelect: () =>
                                    router.visit(`${base}/${item.id}/edit`),
                            },
                            {
                                label: 'Archive',
                                icon: Archive,
                                destructive: true,
                                show:
                                    isOwner ||
                                    can('Inventory Management', 'archive'),
                                onSelect: () => openDeleteDialog(item),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>

        <!-- Archive Confirm Dialog -->
        <AlertDialog v-model:open="isDeleteOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Archive Product</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to archive this product?
                        <strong>{{ itemToDelete?.name }}</strong>
                        will be hidden from the inventory list. You can restore
                        it later from the Archive page.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <Button variant="outline" @click="cancelDelete"
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
