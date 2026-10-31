<script setup lang="ts">
import {
    DataTable,
    DataTableToolbar,
    StatusPill,
    statusLabel,
    type DataTableHeader,
    type StatusTone,
} from '@/components/data-table';
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
import { useDataTable } from '@/composables/useDataTable';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    AlertTriangle,
    ArrowLeft,
    ArrowUpDown,
    Loader2,
    Pencil,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Movement {
    id: number;
    type: string;
    quantity: number;
    quantity_before: number;
    quantity_after: number;
    reference_number: string | null;
    notes: string | null;
    created_at: string;
    user: { name: string } | null;
}

interface InventoryItem {
    id: number;
    name: string;
    description: string | null;
    unit: string;
    quantity: number;
    min_stock: number;
    max_stock: number | null;
    unit_price: string | null;
    selling_price: string | null;
    status: 'active' | 'inactive';
    category: { id: number; name: string } | null;
    supplier: { id: number; name: string; phone: string | null } | null;
    movements: Movement[];
}

const { inventory } = defineProps<{ inventory: InventoryItem }>();

const { isOwner, can } = usePermissions();
const base = computed(() =>
    isOwner.value ? '/shop/inventory' : '/staff/inventory',
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: base.value },
    { title: inventory.name, href: `${base.value}/${inventory.id}` },
];

// ─── Stock Adjust ─────────────────────────────────────────────────────────────

const showAdjust = ref(false);
const adjustError = ref<string | null>(null);
const adjusting = ref(false);

const adjustForm = ref({
    type: 'restock',
    quantity: 1,
    reference_number: '',
    notes: '',
});

async function submitAdjust() {
    if (!adjustForm.value.quantity || adjustForm.value.quantity === 0) {
        adjustError.value = 'Quantity cannot be zero.';
        return;
    }

    adjusting.value = true;
    adjustError.value = null;

    try {
        await axios.post(
            `${base.value}/${inventory.id}/adjust`,
            adjustForm.value,
        );
        router.reload({ only: ['inventory'] });
        showAdjust.value = false;
        adjustForm.value = {
            type: 'restock',
            quantity: 1,
            reference_number: '',
            notes: '',
        };
    } catch (err: any) {
        adjustError.value =
            err.response?.data?.errors?.quantity?.[0] ??
            err.response?.data?.message ??
            'Failed to adjust stock.';
    } finally {
        adjusting.value = false;
    }
}

function formatPrice(price: string | null) {
    if (!price) return '—';
    return `₱${Number(price).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
}

function formatDateTime(dt: string) {
    return new Date(dt).toLocaleString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

const movementTypeTone: Record<string, StatusTone> = {
    restock: 'success',
    usage: 'info',
    adjustment: 'neutral',
    return: 'info',
    damage: 'danger',
};

const movementHeaders: DataTableHeader[] = [
    { text: 'Type', value: 'type', sortable: true },
    { text: 'Quantity', value: 'quantity', sortable: true },
    { text: 'Before', value: 'quantity_before' },
    { text: 'After', value: 'quantity_after' },
    { text: 'Reference', value: 'reference_number' },
    { text: 'By', value: 'user' },
    { text: 'Date', value: 'created_at', sortable: true },
];

const {
    status: movementType,
    options: movementOptions,
    pageItems: movementPage,
    total: movementTotal,
    statusCounts: movementCounts,
    isFiltered: movementFiltered,
    reset: resetMovements,
} = useDataTable(() => inventory.movements, {
    status: (movement) => movement.type,
    sortBy: 'created_at',
    sortType: 'desc',
});

const movementTypeOptions = computed(() => [
    { value: '', label: 'All', count: inventory.movements.length },
    ...Object.keys(movementCounts.value).map((type) => ({
        value: type,
        label: statusLabel(type),
        count: movementCounts.value[type],
    })),
]);
</script>

<template>
    <Head :title="inventory.name" />
    <ShopLayout :breadcrumbs="breadcrumbs" :title="inventory.name">
        <div class="space-y-6 px-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <Button variant="outline" @click="router.visit(base)">
                    <ArrowLeft class="mr-2 h-4 w-4" /> Back
                </Button>
                <div class="flex gap-2">
                    <Button
                        v-if="isOwner || can('Inventory Management', 'update')"
                        variant="outline"
                        @click="showAdjust = !showAdjust"
                    >
                        <ArrowUpDown class="mr-2 h-4 w-4" /> Adjust Stock
                    </Button>
                    <Button
                        v-if="isOwner || can('Inventory Management', 'update')"
                        @click="router.visit(`${base}/${inventory.id}/edit`)"
                    >
                        <Pencil class="mr-2 h-4 w-4" /> Edit
                    </Button>
                </div>
            </div>

            <!-- Low stock warning -->
            <div
                v-if="inventory.quantity <= inventory.min_stock"
                class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"
            >
                <AlertTriangle class="h-4 w-4 shrink-0" />
                <span>
                    Stock is {{ inventory.quantity === 0 ? 'out' : 'low' }}.
                    Current: <strong>{{ inventory.quantity }}</strong>
                    {{ inventory.unit }} (min: {{ inventory.min_stock }})
                </span>
            </div>

            <!-- Adjust stock panel -->
            <Card v-if="showAdjust" class="border-blue-200 bg-blue-50/30">
                <CardHeader class="pb-3">
                    <div class="flex items-center justify-between">
                        <CardTitle class="flex items-center gap-2 text-sm">
                            <ArrowUpDown class="h-4 w-4" /> Adjust Stock
                        </CardTitle>
                        <Button
                            size="icon"
                            variant="ghost"
                            class="h-7 w-7"
                            @click="showAdjust = false"
                        >
                            <X class="h-4 w-4" />
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div
                        v-if="adjustError"
                        class="mb-3 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-600"
                    >
                        {{ adjustError }}
                    </div>
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 space-y-1 sm:col-span-3">
                            <label class="text-sm font-medium">Type</label>
                            <Select v-model="adjustForm.type">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="restock"
                                        >Restock</SelectItem
                                    >
                                    <SelectItem value="usage">Usage</SelectItem>
                                    <SelectItem value="adjustment"
                                        >Adjustment</SelectItem
                                    >
                                    <SelectItem value="return"
                                        >Return</SelectItem
                                    >
                                    <SelectItem value="damage"
                                        >Damage</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="col-span-12 space-y-1 sm:col-span-2">
                            <label class="text-sm font-medium">Quantity</label>
                            <Input
                                v-model="adjustForm.quantity"
                                type="number"
                                min="1"
                            />
                        </div>
                        <div class="col-span-12 space-y-1 sm:col-span-3">
                            <label class="text-sm font-medium"
                                >Reference No.
                                <span class="text-xs text-muted-foreground"
                                    >(optional)</span
                                ></label
                            >
                            <Input
                                v-model="adjustForm.reference_number"
                                placeholder="e.g. PO-001"
                            />
                        </div>
                        <div class="col-span-12 space-y-1 sm:col-span-4">
                            <label class="text-sm font-medium"
                                >Notes
                                <span class="text-xs text-muted-foreground"
                                    >(optional)</span
                                ></label
                            >
                            <Input
                                v-model="adjustForm.notes"
                                placeholder="Reason for adjustment..."
                            />
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <Button variant="outline" @click="showAdjust = false"
                            >Cancel</Button
                        >
                        <Button :disabled="adjusting" @click="submitAdjust">
                            <Loader2
                                v-if="adjusting"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            {{ adjusting ? 'Saving...' : 'Save Adjustment' }}
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <!-- Details -->
                <Card class="md:col-span-2">
                    <CardHeader><CardTitle>Item Details</CardTitle></CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-2 gap-6 text-sm">
                            <div class="space-y-1">
                                <p
                                    class="text-xs font-semibold text-muted-foreground uppercase"
                                >
                                    Name
                                </p>
                                <p class="font-medium">{{ inventory.name }}</p>
                            </div>
                            <div class="space-y-1">
                                <p
                                    class="text-xs font-semibold text-muted-foreground uppercase"
                                >
                                    Unit
                                </p>
                                <p>{{ inventory.unit }}</p>
                            </div>
                            <div class="space-y-1">
                                <p
                                    class="text-xs font-semibold text-muted-foreground uppercase"
                                >
                                    Status
                                </p>
                                <span
                                    class="inline-block rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="
                                        inventory.status === 'active'
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-gray-100 text-gray-500'
                                    "
                                >
                                    {{ inventory.status }}
                                </span>
                            </div>
                            <div class="space-y-1">
                                <p
                                    class="text-xs font-semibold text-muted-foreground uppercase"
                                >
                                    Category
                                </p>
                                <p>{{ inventory.category?.name ?? '—' }}</p>
                            </div>
                            <div class="space-y-1">
                                <p
                                    class="text-xs font-semibold text-muted-foreground uppercase"
                                >
                                    Supplier
                                </p>
                                <p>{{ inventory.supplier?.name ?? '—' }}</p>
                            </div>
                            <div class="space-y-1">
                                <p
                                    class="text-xs font-semibold text-muted-foreground uppercase"
                                >
                                    Unit Price
                                </p>
                                <p>{{ formatPrice(inventory.unit_price) }}</p>
                            </div>
                            <div class="space-y-1">
                                <p
                                    class="text-xs font-semibold text-muted-foreground uppercase"
                                >
                                    Selling Price
                                </p>
                                <p>
                                    {{ formatPrice(inventory.selling_price) }}
                                </p>
                            </div>
                            <div
                                v-if="inventory.description"
                                class="col-span-2 space-y-1"
                            >
                                <p
                                    class="text-xs font-semibold text-muted-foreground uppercase"
                                >
                                    Description
                                </p>
                                <p class="text-muted-foreground">
                                    {{ inventory.description }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Stock summary -->
                <div class="space-y-4">
                    <Card>
                        <CardContent class="pt-5 text-center">
                            <p
                                class="text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                            >
                                Current Stock
                            </p>
                            <p
                                class="mt-2 text-5xl font-bold"
                                :class="
                                    inventory.quantity === 0
                                        ? 'text-red-600'
                                        : inventory.quantity <=
                                            inventory.min_stock
                                          ? 'text-amber-600'
                                          : 'text-green-600'
                                "
                            >
                                {{ inventory.quantity }}
                            </p>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ inventory.unit }}
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="space-y-3 pt-5 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground"
                                    >Min Stock</span
                                >
                                <span class="font-medium">{{
                                    inventory.min_stock
                                }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground"
                                    >Max Stock</span
                                >
                                <span class="font-medium">{{
                                    inventory.max_stock ?? '—'
                                }}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Movement history -->
            <DataTable
                v-model:options="movementOptions"
                :headers="movementHeaders"
                :items="movementPage"
                :total="movementTotal"
                noun="stock movements"
                :empty-icon="ArrowUpDown"
                :empty-message="
                    movementFiltered
                        ? 'No stock movements match the selected type.'
                        : 'No stock movements recorded yet.'
                "
                :resettable="movementFiltered"
                :min-width="820"
                compact
                @reset="resetMovements"
            >
                <template #toolbar>
                    <div class="space-y-3">
                        <h2 class="font-semibold">Stock Movement History</h2>
                        <DataTableToolbar
                            v-model:status="movementType"
                            :statuses="movementTypeOptions"
                            status-label="Filter by movement type"
                            hide-search
                        />
                    </div>
                </template>

                <template #item-type="m">
                    <StatusPill :tone="movementTypeTone[m.type] ?? 'neutral'">
                        {{ statusLabel(m.type) }}
                    </StatusPill>
                </template>

                <template #item-quantity="m">
                    <span
                        class="font-medium"
                        :class="
                            m.quantity > 0 ? 'text-green-600' : 'text-red-600'
                        "
                    >
                        {{ m.quantity > 0 ? '+' : '' }}{{ m.quantity }}
                    </span>
                </template>

                <template #item-quantity_before="m">
                    <span class="text-muted-foreground">{{
                        m.quantity_before
                    }}</span>
                </template>

                <template #item-quantity_after="m">
                    <span class="text-muted-foreground">{{
                        m.quantity_after
                    }}</span>
                </template>

                <template #item-reference_number="m">
                    <span class="font-mono text-xs text-muted-foreground">
                        {{ m.reference_number ?? '—' }}
                    </span>
                </template>

                <template #item-user="m">
                    <span class="text-xs text-muted-foreground">
                        {{ m.user?.name ?? '—' }}
                    </span>
                </template>

                <template #item-created_at="m">
                    <span
                        class="text-xs whitespace-nowrap text-muted-foreground"
                    >
                        {{ formatDateTime(m.created_at) }}
                    </span>
                </template>
            </DataTable>
        </div>
    </ShopLayout>
</template>
