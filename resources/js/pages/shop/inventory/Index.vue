<script setup lang="ts">
import ExportCsvButton from '@/components/shop/ExportCsvButton.vue';
import {
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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { DataTableOptions } from '@/composables/useDataTable';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Eye,
    Layers,
    Package,
    Pencil,
    Plus,
    RefreshCcw,
    Trash2,
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
    sku: string;
    unit: string;
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

const tableLoading = ref(false);
const options = ref<DataTableOptions>({
    page: props.inventory.current_page,
    rowsPerPage: props.inventory.per_page,
});

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
        stock.value !== 'all',
);

const headers: DataTableHeader[] = [
    { text: 'Item', value: 'name' },
    { text: 'SKU', value: 'sku' },
    { text: 'Category', value: 'category' },
    { text: 'Supplier', value: 'supplier' },
    { text: 'Stock', value: 'quantity' },
    { text: 'Unit price', value: 'unit_price' },
    { text: 'Status', value: 'status' },
    { text: 'Actions', value: 'actions', align: 'center', width: 150 },
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
    const statusChanged = status.value !== 'all';
    search.value = '';
    categoryId.value = 'all';
    supplierId.value = 'all';
    status.value = 'all';
    stock.value = 'all';
    // A status change already reloads through the status watcher.
    if (!statusChanged) applyFilters();
}

// ─── Delete ───────────────────────────────────────────────────────────────────

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
            toast.success('Item deleted successfully.');
        },
        onError: () => toast.error('Failed to delete item.'),
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

function formatPrice(price: string | null) {
    if (!price) return '—';
    return `₱${Number(price).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
}
</script>

<template>
    <Head title="Inventory" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Inventory Management">
        <div class="space-y-6 px-6">
            <!-- Stats row -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Total Items
                            </p>
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-100"
                            >
                                <Package class="h-4 w-4 text-blue-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold">{{ inventory.total }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Low Stock
                            </p>
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100"
                            >
                                <AlertTriangle class="h-4 w-4 text-amber-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-amber-600">
                            {{ lowStock.length }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Categories
                            </p>
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-purple-100"
                            >
                                <Layers class="h-4 w-4 text-purple-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold">
                            {{ categories.length }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Suppliers
                            </p>
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-green-100"
                            >
                                <Truck class="h-4 w-4 text-green-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold">{{ suppliers.length }}</p>
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
                            {{ item.name }} ({{ item.quantity }}
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

            <!-- Table card -->
            <DataTable
                v-model:options="options"
                :headers="headers"
                :items="inventory.data"
                :total="inventory.total"
                :loading="tableLoading"
                noun="inventory items"
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
                <template #toolbar>
                    <div class="space-y-3">
                        <div
                            class="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center"
                        >
                            <h2 class="flex items-center gap-2 font-semibold">
                                <Package
                                    class="h-4 w-4 text-muted-foreground"
                                />
                                Inventory List
                            </h2>
                            <div class="flex flex-wrap gap-2">
                                <ExportCsvButton dataset="inventory" />
                                <ExportCsvButton
                                    dataset="stock-movements"
                                    label="Export stock movements"
                                />
                                <Button
                                    size="sm"
                                    variant="outline"
                                    @click="router.visit(categoryBase)"
                                >
                                    <Layers class="mr-1.5 h-4 w-4" /> Categories
                                </Button>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    class="bg-yellow-500 text-white hover:bg-yellow-600 hover:text-white"
                                    @click="router.visit(supplierBase)"
                                >
                                    <Truck class="mr-1.5 h-4 w-4" /> Suppliers
                                </Button>
                                <Button
                                    v-if="
                                        isOwner ||
                                        can('Inventory Management', 'create')
                                    "
                                    size="sm"
                                    @click="router.visit(`${base}/create`)"
                                >
                                    <Plus class="mr-1.5 h-4 w-4" /> Add Item
                                </Button>
                            </div>
                        </div>

                        <DataTableToolbar
                            v-model:search="search"
                            v-model:status="status"
                            :statuses="statusOptions"
                            search-placeholder="Search name or SKU..."
                            search-label="Search inventory"
                            status-label="Filter by item status"
                            @submit="applyFilters()"
                        >
                            <Select
                                v-model="categoryId"
                                @update:model-value="() => applyFilters()"
                            >
                                <SelectTrigger
                                    class="w-40"
                                    aria-label="Filter by category"
                                    ><SelectValue placeholder="Category"
                                /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all"
                                        >All Categories</SelectItem
                                    >
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
                                    class="w-40"
                                    aria-label="Filter by supplier"
                                    ><SelectValue placeholder="Supplier"
                                /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all"
                                        >All Suppliers</SelectItem
                                    >
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
                                    class="w-32"
                                    aria-label="Filter by stock level"
                                    ><SelectValue placeholder="Stock"
                                /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all"
                                        >All Stock</SelectItem
                                    >
                                    <SelectItem value="low"
                                        >Low Stock</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="resetFilters"
                            >
                                <RefreshCcw class="mr-1.5 h-4 w-4" />
                                Reset
                            </Button>
                        </DataTableToolbar>
                    </div>
                </template>

                <template #item-name="item">
                    <p class="font-medium">{{ item.name }}</p>
                    <p class="text-xs text-muted-foreground">{{ item.unit }}</p>
                </template>

                <template #item-sku="item">
                    <span class="font-mono text-xs text-muted-foreground">
                        {{ item.sku }}
                    </span>
                </template>

                <template #item-category="item">
                    <span class="text-xs text-muted-foreground">
                        {{ item.category?.name ?? '—' }}
                    </span>
                </template>

                <template #item-supplier="item">
                    <span class="text-xs text-muted-foreground">
                        {{ item.supplier?.name ?? '—' }}
                    </span>
                </template>

                <template #item-quantity="item">
                    <div class="flex items-center gap-2">
                        <span class="font-medium">{{ item.quantity }}</span>
                        <StatusPill :tone="stockBadge(item).tone">
                            {{ stockBadge(item).label }}
                        </StatusPill>
                    </div>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        Min: {{ item.min_stock }}
                    </p>
                    <p
                        v-if="reorderSuggestions[item.id]"
                        class="mt-0.5 text-xs font-medium text-amber-700"
                    >
                        {{
                            reorderSuggestions[item.id].suggested_quantity > 0
                                ? `Order ~${reorderSuggestions[item.id].suggested_quantity} ${item.unit}`
                                : 'Reorder soon'
                        }}
                        · ~{{
                            Math.ceil(reorderSuggestions[item.id].days_left)
                        }}d left,
                        {{ reorderSuggestions[item.id].lead_time_days }}d lead
                    </p>
                </template>

                <template #item-unit_price="item">
                    <span class="whitespace-nowrap text-muted-foreground">
                        {{ formatPrice(item.unit_price) }}
                    </span>
                </template>

                <template #item-status="item">
                    <StatusPill
                        :tone="item.status === 'active' ? 'success' : 'neutral'"
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
                                label: 'Delete',
                                icon: Trash2,
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

        <!-- Delete Confirm Dialog -->
        <AlertDialog v-model:open="isDeleteOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Delete Item</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to delete
                        <strong>{{ itemToDelete?.name }}</strong
                        >? This action cannot be undone.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <Button variant="outline" @click="cancelDelete"
                        >Cancel</Button
                    >
                    <Button variant="destructive" @click="confirmDelete"
                        >Delete</Button
                    >
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </ShopLayout>
</template>
