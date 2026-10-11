<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
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
    Search,
    Trash2,
    Truck,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
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

function applyFilters() {
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
        },
        { preserveState: true, replace: true },
    );
}

function resetFilters() {
    search.value = '';
    categoryId.value = 'all';
    supplierId.value = 'all';
    status.value = 'all';
    stock.value = 'all';
    router.get(base.value, {}, { preserveState: true, replace: true });
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

function stockBadge(item: InventoryItem) {
    if (isOutOfStock(item))
        return { label: 'Out of stock', class: 'bg-red-100 text-red-700' };
    if (isLowStock(item))
        return { label: 'Low stock', class: 'bg-amber-100 text-amber-700' };
    return { label: 'In stock', class: 'bg-green-100 text-green-700' };
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
            <Card>
                <CardHeader class="pb-3">
                    <div
                        class="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center"
                    >
                        <CardTitle class="flex items-center gap-2">
                            <Package class="h-4 w-4 text-muted-foreground" />
                            Inventory List
                        </CardTitle>
                        <div class="flex gap-2">
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
                                placeholder="Search name or SKU..."
                                class="pl-8"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <Select
                            v-model="categoryId"
                            @update:model-value="applyFilters"
                        >
                            <SelectTrigger class="w-40"
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
                            @update:model-value="applyFilters"
                        >
                            <SelectTrigger class="w-40"
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
                            v-model="status"
                            @update:model-value="applyFilters"
                        >
                            <SelectTrigger class="w-32"
                                ><SelectValue placeholder="Status"
                            /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Status</SelectItem>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="inactive"
                                    >Inactive</SelectItem
                                >
                            </SelectContent>
                        </Select>
                        <Select
                            v-model="stock"
                            @update:model-value="applyFilters"
                        >
                            <SelectTrigger class="w-32"
                                ><SelectValue placeholder="Stock"
                            /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Stock</SelectItem>
                                <SelectItem value="low">Low Stock</SelectItem>
                            </SelectContent>
                        </Select>
                        <Button variant="ghost" size="sm" @click="resetFilters"
                            >Reset</Button
                        >
                    </div>

                    <!-- Table -->
                    <div class="overflow-hidden rounded-lg border">
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="border-b bg-muted/40 text-xs text-muted-foreground"
                                >
                                    <th class="px-4 py-3 text-left font-medium">
                                        Item
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        SKU
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Category
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Supplier
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Stock
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Unit Price
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Status
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
                                    v-for="item in inventory.data"
                                    :key="item.id"
                                    class="border-b transition-colors last:border-0 hover:bg-muted/20"
                                >
                                    <td class="px-4 py-3">
                                        <p class="font-medium">
                                            {{ item.name }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ item.unit }}
                                        </p>
                                    </td>
                                    <td
                                        class="px-4 py-3 font-mono text-xs text-muted-foreground"
                                    >
                                        {{ item.sku }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-xs text-muted-foreground"
                                    >
                                        {{ item.category?.name ?? '—' }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-xs text-muted-foreground"
                                    >
                                        {{ item.supplier?.name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium">{{
                                                item.quantity
                                            }}</span>
                                            <span
                                                class="rounded-full px-1.5 py-0.5 text-xs font-medium"
                                                :class="stockBadge(item).class"
                                            >
                                                {{ stockBadge(item).label }}
                                            </span>
                                        </div>
                                        <p
                                            class="mt-0.5 text-xs text-muted-foreground"
                                        >
                                            Min: {{ item.min_stock }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ formatPrice(item.unit_price) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="rounded-full px-2 py-0.5 text-xs font-medium"
                                            :class="
                                                item.status === 'active'
                                                    ? 'bg-green-100 text-green-700'
                                                    : 'bg-gray-100 text-gray-500'
                                            "
                                        >
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div
                                            class="flex items-center justify-center gap-1"
                                        >
                                            <Button
                                                size="icon"
                                                variant="ghost"
                                                @click="
                                                    router.visit(
                                                        `${base}/${item.id}`,
                                                    )
                                                "
                                            >
                                                <Eye
                                                    class="h-4 w-4 text-blue-500"
                                                />
                                            </Button>
                                            <Button
                                                v-if="
                                                    isOwner ||
                                                    can(
                                                        'Inventory Management',
                                                        'update',
                                                    )
                                                "
                                                size="icon"
                                                variant="ghost"
                                                @click="
                                                    router.visit(
                                                        `${base}/${item.id}/edit`,
                                                    )
                                                "
                                            >
                                                <Pencil
                                                    class="h-4 w-4 text-green-500"
                                                />
                                            </Button>
                                            <Button
                                                v-if="
                                                    isOwner ||
                                                    can(
                                                        'Inventory Management',
                                                        'archive',
                                                    )
                                                "
                                                size="icon"
                                                variant="ghost"
                                                @click="openDeleteDialog(item)"
                                            >
                                                <Trash2
                                                    class="h-4 w-4 text-red-400"
                                                />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="inventory.data.length === 0">
                                    <td
                                        colspan="8"
                                        class="px-4 py-12 text-center text-sm text-muted-foreground"
                                    >
                                        <Package
                                            class="mx-auto mb-2 h-10 w-10 opacity-20"
                                        />
                                        No inventory items found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="inventory.last_page > 1"
                        class="flex items-center justify-between pt-2"
                    >
                        <p class="text-xs text-muted-foreground">
                            Showing {{ inventory.data.length }} of
                            {{ inventory.total }} items
                        </p>
                        <div class="flex gap-1">
                            <Button
                                v-for="link in inventory.links"
                                :key="link.label"
                                size="sm"
                                :variant="link.active ? 'default' : 'outline'"
                                :disabled="!link.url"
                                class="h-7 min-w-7 text-xs"
                                @click="link.url && router.visit(link.url)"
                            >
                                <span v-html="link.label" />
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
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
