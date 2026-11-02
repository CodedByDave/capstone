<script setup lang="ts">
import InventorySalesOptions from '@/components/shop/InventorySalesOptions.vue';
import ServicePhotoInput from '@/components/shop/ServicePhotoInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { formatQuantity, packageStock } from '@/lib/inventoryStock';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ArrowUpDown, Loader2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

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
    description: string | null;
    photo_url: string | null;
    unit: string;
    package_unit?: string | null;
    package_size?: number | null;
    quantity: number;
    min_stock: number;
    max_stock: number | null;
    unit_price: string | null;
    addon_enabled: boolean;
    addon_name: string | null;
    addon_quantity: number | null;
    addon_price: string | null;
    retail_enabled: boolean;
    retail_name: string | null;
    retail_pack_size: number | null;
    retail_price: string | null;
    notes: string | null;
    status: string;
    inventory_categories_id: number | null;
    supplier_id: number | null;
}

const { inventory, categories, suppliers } = defineProps<{
    inventory: InventoryItem;
    categories: Category[];
    suppliers: Supplier[];
}>();

const { isOwner } = usePermissions();
const base = computed(() =>
    isOwner.value ? '/shop/inventory' : '/staff/inventory',
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: base.value },
    { title: inventory.name, href: `${base.value}/${inventory.id}` },
    { title: 'Edit', href: `${base.value}/${inventory.id}/edit` },
];

// reka-ui's SelectItem cannot use an empty value, so the "none" option uses this instead.
const NONE = '__none__';

const form = useForm({
    name: inventory.name,
    description: inventory.description ?? '',
    photo: null as File | null,
    remove_photo: false,
    unit: inventory.unit,
    package_unit: inventory.package_unit ?? '',
    package_size: inventory.package_size ?? '',
    min_stock: inventory.min_stock,
    max_stock: inventory.max_stock ?? '',
    unit_price: inventory.unit_price ?? '',
    addon_enabled: !!inventory.addon_enabled,
    addon_name: inventory.addon_name ?? '',
    addon_quantity: (inventory.addon_quantity ?? '') as number | string,
    addon_price: (inventory.addon_price ?? '') as number | string,
    retail_enabled: !!inventory.retail_enabled,
    retail_name: inventory.retail_name ?? '',
    retail_pack_size: (inventory.retail_pack_size ?? '') as number | string,
    retail_price: (inventory.retail_price ?? '') as number | string,
    notes: inventory.notes ?? '',
    status: inventory.status,
    inventory_categories_id: inventory.inventory_categories_id
        ? String(inventory.inventory_categories_id)
        : '',
    supplier_id: inventory.supplier_id ? String(inventory.supplier_id) : '',
});

// Opt-in package fields: already on when the item has a package size saved.
const buysInPackages = ref(
    !!inventory.package_unit && (inventory.package_size ?? 0) > 0,
);

function clearPackages() {
    form.package_unit = '';
    form.package_size = '';
    form.clearErrors('package_unit', 'package_size');
}

watch(buysInPackages, (on) => {
    if (!on) clearPackages();
});
watch(
    () => form.unit,
    () => {
        buysInPackages.value = false;
        clearPackages();
    },
);

// What one unit costs the shop (the saved cost is already per unit), for the pricing overview.
const costPerUnit = computed(() =>
    form.unit_price === '' ? null : Number(form.unit_price),
);

// ─── Stock adjustment ──────────────────────────────────────────────────────
// Stock is saved in the item's own unit. An item bought in packages (e.g. bottles of 5,000 ml)
// can be adjusted in packages; the adjustment is applied right away, apart from "Save changes".

const hasPackage = computed(
    () => !!inventory.package_unit && (inventory.package_size ?? 0) > 0,
);
const adjustInPackages = ref(hasPackage.value);

const adjustTypes = [
    { value: 'restock', label: 'Restock', sign: 1 },
    { value: 'return', label: 'Return', sign: 1 },
    { value: 'adjustment', label: 'Adjustment', sign: 1 },
    { value: 'usage', label: 'Usage', sign: -1 },
    { value: 'damage', label: 'Damage', sign: -1 },
];

const adjustForm = useForm({
    type: 'restock',
    quantity: '' as number | string,
    reference_number: '',
    notes: '',
});

// What the typed quantity is, in the item's own unit.
const adjustUnits = computed(() => {
    const quantity = Number(adjustForm.quantity) || 0;
    return hasPackage.value && adjustInPackages.value
        ? Math.round(quantity * inventory.package_size!)
        : quantity;
});
const adjustChange = computed(() => {
    const sign =
        adjustTypes.find((t) => t.value === adjustForm.type)?.sign ?? 1;
    return sign * Math.abs(adjustUnits.value);
});
const stockAfter = computed(() => inventory.quantity + adjustChange.value);

function submitAdjust() {
    adjustForm.clearErrors();

    if (!adjustUnits.value) {
        adjustForm.setError('quantity', 'Quantity cannot be zero.');
        return;
    }

    adjustForm
        .transform((data) => ({ ...data, quantity: adjustUnits.value }))
        .post(`${base.value}/${inventory.id}/adjust`, {
            preserveScroll: true,
            // Keeps anything typed in the rest of the form; the new stock arrives with the page props.
            preserveState: true,
            onSuccess: () => adjustForm.reset(),
        });
}

function submit() {
    form.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(`${base.value}/${inventory.id}`, { forceFormData: true });
}
</script>

<template>
    <Head :title="`Edit — ${inventory.name}`" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Edit Inventory Item">
        <div class="space-y-8 px-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Edit Item</h2>
                    <p class="text-sm text-muted-foreground">
                        Update details for
                        <span class="font-medium text-foreground">{{
                            inventory.name
                        }}</span
                        >.
                    </p>
                </div>
                <Button variant="outline" @click="router.visit(base)">
                    <ArrowLeft class="mr-2 h-4 w-4" /> Back to inventory
                </Button>
            </div>

            <!-- Basic Info -->
            <div>
                <p
                    class="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                >
                    Basic Information
                </p>
                <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                    <div class="col-span-12 space-y-1 sm:col-span-6">
                        <label class="text-sm font-medium"
                            >Item Name
                            <span class="text-red-500">*</span></label
                        >
                        <Input
                            v-model="form.name"
                            :class="{ 'border-red-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="text-xs text-red-500">
                            {{ form.errors.name }}
                        </p>
                    </div>
                    <div class="col-span-12 space-y-1 sm:col-span-3">
                        <label class="text-sm font-medium"
                            >Unit <span class="text-red-500">*</span></label
                        >
                        <Select v-model="form.unit">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="pcs"
                                    >Pieces (pcs)</SelectItem
                                >
                                <SelectItem value="kg"
                                    >Kilograms (kg)</SelectItem
                                >
                                <SelectItem value="g">Grams (g)</SelectItem>
                                <SelectItem value="liters"
                                    >Liters (L)</SelectItem
                                >
                                <SelectItem value="ml"
                                    >Milliliters (ml)</SelectItem
                                >
                                <SelectItem value="bottles">Bottles</SelectItem>
                                <SelectItem value="boxes">Boxes</SelectItem>
                                <SelectItem value="packs">Packs</SelectItem>
                                <SelectItem value="rolls">Rolls</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="col-span-12">
                        <label
                            class="flex cursor-pointer items-center gap-2 text-sm font-medium"
                        >
                            <input
                                v-model="buysInPackages"
                                type="checkbox"
                                class="h-4 w-4 rounded border-input accent-primary"
                            />
                            I buy this in bigger packages
                        </label>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Example: detergent bought as a 5,000 ml gallon but
                            stocked in ml.
                        </p>
                    </div>
                    <div
                        v-if="buysInPackages"
                        class="col-span-12 space-y-1 sm:col-span-4"
                    >
                        <label class="text-sm font-medium"
                            >Bought as
                            <span class="text-xs text-muted-foreground"
                                >(optional)</span
                            ></label
                        >
                        <Input
                            v-model="form.package_unit"
                            placeholder="e.g. bottle, pack, sack"
                        />
                        <p
                            v-if="form.errors.package_unit"
                            class="text-xs text-red-500"
                        >
                            {{ form.errors.package_unit }}
                        </p>
                    </div>
                    <div
                        v-if="buysInPackages"
                        class="col-span-12 space-y-1 sm:col-span-4"
                    >
                        <label class="text-sm font-medium"
                            >Contents per
                            {{ form.package_unit.trim() || 'package' }} ({{
                                form.unit
                            }})</label
                        >
                        <Input
                            v-model="form.package_size"
                            type="number"
                            min="1"
                            placeholder="e.g. 5000"
                        />
                        <p
                            v-if="form.errors.package_size"
                            class="text-xs text-red-500"
                        >
                            {{ form.errors.package_size }}
                        </p>
                    </div>
                    <div class="col-span-12 space-y-1">
                        <label class="text-sm font-medium">Description</label>
                        <Textarea v-model="form.description" rows="2" />
                    </div>
                </div>
            </div>

            <!-- Photo -->
            <div>
                <p
                    class="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                >
                    Photo
                </p>
                <div class="max-w-xs">
                    <ServicePhotoInput
                        v-model="form.photo"
                        v-model:removed="form.remove_photo"
                        compact
                        :current-url="inventory.photo_url"
                        :error="form.errors.photo"
                    />
                </div>
            </div>

            <!-- Stock adjustment -->
            <div>
                <p
                    class="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                >
                    Stock Adjustment
                </p>
                <div
                    class="mb-4 rounded-lg border bg-muted/30 px-4 py-3 text-sm"
                >
                    <span class="text-muted-foreground">Current stock:</span>
                    <template v-if="packageStock(inventory)">
                        <strong class="ml-1">{{
                            packageStock(inventory)!.full
                        }}</strong>
                        <span
                            v-if="packageStock(inventory)!.remainder"
                            class="text-muted-foreground"
                        >
                            + {{ packageStock(inventory)!.remainder }}</span
                        >
                        <span class="block text-xs text-muted-foreground">
                            {{ formatQuantity(inventory.quantity) }}
                            {{ inventory.unit }} total ·
                            {{ packageStock(inventory)!.perPackage }}
                        </span>
                    </template>
                    <strong v-else class="ml-1"
                        >{{ formatQuantity(inventory.quantity) }}
                        {{ inventory.unit }}</strong
                    >
                </div>
                <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                    <div class="col-span-12 space-y-1 sm:col-span-3">
                        <label class="text-sm font-medium">Type</label>
                        <Select v-model="adjustForm.type">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="t in adjustTypes"
                                    :key="t.value"
                                    :value="t.value"
                                    >{{ t.label }}</SelectItem
                                >
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="col-span-12 space-y-1 sm:col-span-3">
                        <label class="text-sm font-medium"
                            >Quantity ({{
                                hasPackage && adjustInPackages
                                    ? inventory.package_unit
                                    : inventory.unit
                            }})</label
                        >
                        <Input
                            v-model="adjustForm.quantity"
                            type="number"
                            min="0"
                            :step="hasPackage && adjustInPackages ? 'any' : 1"
                            :class="{
                                'border-red-500': adjustForm.errors.quantity,
                            }"
                        />
                        <button
                            v-if="hasPackage"
                            type="button"
                            class="text-xs text-primary hover:underline"
                            @click="adjustInPackages = !adjustInPackages"
                        >
                            {{
                                adjustInPackages
                                    ? `Enter in ${inventory.unit} instead`
                                    : `Enter in ${inventory.package_unit} instead`
                            }}
                        </button>
                        <p
                            v-if="adjustForm.errors.quantity"
                            class="text-xs text-red-500"
                        >
                            {{ adjustForm.errors.quantity }}
                        </p>
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
                    <div class="col-span-12 space-y-1 sm:col-span-3">
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
                <div
                    class="mt-4 flex flex-wrap items-center justify-between gap-3"
                >
                    <p
                        class="text-xs"
                        :class="
                            stockAfter < 0
                                ? 'text-red-600'
                                : 'text-muted-foreground'
                        "
                    >
                        <template v-if="adjustUnits">
                            Stock after this:
                            <strong>{{ formatQuantity(stockAfter) }}</strong>
                            {{ inventory.unit }}.
                        </template>
                        Applied right away and recorded in the stock history. It
                        is separate from Save changes.
                    </p>
                    <Button
                        type="button"
                        variant="outline"
                        :disabled="adjustForm.processing"
                        @click="submitAdjust"
                    >
                        <Loader2
                            v-if="adjustForm.processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        <ArrowUpDown v-else class="mr-2 h-4 w-4" />
                        {{
                            adjustForm.processing
                                ? 'Saving...'
                                : 'Save adjustment'
                        }}
                    </Button>
                </div>
            </div>

            <!-- Stock thresholds -->
            <div>
                <p
                    class="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                >
                    Stock Thresholds
                </p>
                <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                    <div class="col-span-12 space-y-1 sm:col-span-3">
                        <label class="text-sm font-medium"
                            >Min Stock
                            <span class="text-red-500">*</span></label
                        >
                        <Input
                            v-model="form.min_stock"
                            type="number"
                            min="0"
                            :class="{ 'border-red-500': form.errors.min_stock }"
                        />
                        <p
                            v-if="form.errors.min_stock"
                            class="text-xs text-red-500"
                        >
                            {{ form.errors.min_stock }}
                        </p>
                    </div>
                    <div class="col-span-12 space-y-1 sm:col-span-3">
                        <label class="text-sm font-medium">Max Stock</label>
                        <Input
                            v-model="form.max_stock"
                            type="number"
                            min="0"
                            placeholder="No limit"
                            :class="{ 'border-red-500': form.errors.max_stock }"
                        />
                        <p
                            v-if="form.errors.max_stock"
                            class="text-xs text-red-500"
                        >
                            {{ form.errors.max_stock }}
                        </p>
                    </div>
                    <div class="col-span-12 space-y-1 sm:col-span-3">
                        <label class="text-sm font-medium"
                            >Status <span class="text-red-500">*</span></label
                        >
                        <Select v-model="form.status">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="inactive"
                                    >Inactive</SelectItem
                                >
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div>
                <p
                    class="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                >
                    Pricing
                </p>
                <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                    <div class="col-span-12 space-y-1 sm:col-span-4">
                        <label class="text-sm font-medium"
                            >Unit Price (₱)</label
                        >
                        <Input
                            v-model="form.unit_price"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="0.00"
                        />
                    </div>
                </div>
            </div>

            <!-- Sales options -->
            <div>
                <p
                    class="mb-1 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                >
                    Sales Options
                </p>
                <p class="mb-4 text-xs text-muted-foreground">
                    Internal use is set per service in Operations → Services.
                </p>
                <InventorySalesOptions
                    :form="form"
                    :unit="form.unit"
                    :cost-per-unit="costPerUnit"
                    :package-unit="form.package_unit.trim()"
                    :package-size="form.package_size"
                />
            </div>

            <!-- Notes -->
            <div>
                <p
                    class="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                >
                    Notes
                </p>
                <Textarea
                    v-model="form.notes"
                    rows="2"
                    maxlength="1000"
                    placeholder="e.g. Keep in a cool and dry place."
                />
                <p v-if="form.errors.notes" class="mt-1 text-xs text-red-500">
                    {{ form.errors.notes }}
                </p>
            </div>

            <!-- Classification -->
            <div>
                <p
                    class="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                >
                    Classification
                </p>
                <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                    <div class="col-span-12 space-y-1 sm:col-span-4">
                        <label class="text-sm font-medium">Category</label>
                        <Select
                            :model-value="form.inventory_categories_id || NONE"
                            @update:model-value="
                                (v) =>
                                    (form.inventory_categories_id =
                                        v === NONE ? '' : String(v))
                            "
                        >
                            <SelectTrigger
                                ><SelectValue placeholder="Select category"
                            /></SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="NONE"
                                    >No Category</SelectItem
                                >
                                <SelectItem
                                    v-for="c in categories"
                                    :key="c.id"
                                    :value="String(c.id)"
                                    >{{ c.name }}</SelectItem
                                >
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="col-span-12 space-y-1 sm:col-span-4">
                        <label class="text-sm font-medium">Supplier</label>
                        <Select
                            :model-value="form.supplier_id || NONE"
                            @update:model-value="
                                (v) =>
                                    (form.supplier_id =
                                        v === NONE ? '' : String(v))
                            "
                        >
                            <SelectTrigger
                                ><SelectValue placeholder="Select supplier"
                            /></SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="NONE"
                                    >No Supplier</SelectItem
                                >
                                <SelectItem
                                    v-for="s in suppliers"
                                    :key="s.id"
                                    :value="String(s.id)"
                                    >{{ s.name }}</SelectItem
                                >
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 border-t pt-4">
                <Button
                    variant="outline"
                    :disabled="form.processing"
                    @click="router.visit(`${base}/${inventory.id}`)"
                    >Cancel</Button
                >
                <Button :disabled="form.processing" @click="submit">
                    <Loader2
                        v-if="form.processing"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </Button>
            </div>
        </div>
    </ShopLayout>
</template>
