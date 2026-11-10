<script setup lang="ts">
import InventorySalesOptions from '@/components/shop/InventorySalesOptions.vue';
import ServicePhotoInput from '@/components/shop/ServicePhotoInput.vue';
import UnitConfigurationSteps from '@/components/shop/UnitConfigurationSteps.vue';
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
import { useShopCapabilities } from '@/composables/useShopCapabilities';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { formatQuantity, packageStock } from '@/lib/inventoryStock';
import { usageUnitsFor } from '@/lib/inventoryUnits';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ArrowUpDown, Loader2, Package } from 'lucide-vue-next';
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
const { hasSuppliers } = useShopCapabilities();
const base = computed(() =>
    isOwner.value ? '/shop/inventory' : '/staff/inventory',
);

// Units offered in the dropdown; an older item may carry another one (e.g. "sachet"), which is kept as an option.
const unitOptions = computed(() => {
    const options = [
        { value: 'pcs', label: 'Pieces (pcs)' },
        { value: 'ml', label: 'Milliliters (ml)' },
        { value: 'g', label: 'Grams (g)' },
    ];

    return options.some((o) => o.value === inventory.unit)
        ? options
        : [{ value: inventory.unit, label: inventory.unit }, ...options];
});

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
    // Typed per purchase unit (e.g. per bottle) and saved per unit (per ml) on submit.
    unit_price: (inventory.unit_price !== null &&
    inventory.unit_price !== undefined &&
    (inventory.package_size ?? 0) > 0
        ? Number(
              (Number(inventory.unit_price) * inventory.package_size!).toFixed(
                  2,
              ),
          )
        : (inventory.unit_price ?? '')) as number | string,
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

// How the item is bought from the supplier (e.g. a bottle of 5,000 ml). "Same as usage" means no package.
const purchaseUnitOptions = [
    { value: 'pcs', label: 'Pieces (pcs)' },
    { value: 'bottle', label: 'Bottles' },
    { value: 'sack', label: 'Sacks / Bags' },
    { value: 'box', label: 'Boxes' },
    { value: 'pack', label: 'Packs' },
    { value: 'roll', label: 'Rolls' },
    { value: 'gallon', label: 'Gallons' },
];

// An item saved with another purchase unit keeps it as an option.
const extraPurchaseUnit = computed(() =>
    form.package_unit &&
    !purchaseUnitOptions.some((o) => o.value === form.package_unit)
        ? form.package_unit
        : null,
);

const purchaseOptions = computed(() =>
    extraPurchaseUnit.value
        ? [
              {
                  value: extraPurchaseUnit.value,
                  label: extraPurchaseUnit.value,
              },
              ...purchaseUnitOptions,
          ]
        : purchaseUnitOptions,
);

const purchaseUnit = computed({
    get: () => form.package_unit,
    set: (value: string) => {
        form.package_unit = value;
        form.clearErrors('package_unit', 'package_size');
    },
});
const purchaseSelected = computed(() => form.package_unit !== '');

function purchaseUnitFits(packageUnit: string, unit: string): boolean {
    const allowed = usageUnitsFor(packageUnit);

    return !packageUnit || !allowed || allowed.includes(unit);
}

// The container size is counted in the usage unit, so changing the unit clears it. The purchase
// unit stays when it still fits the new unit (the dropdowns only offer matching pairs).
watch(
    () => form.unit,
    (unit) => {
        if (!purchaseUnitFits(form.package_unit, unit)) {
            form.package_unit = '';
        }
        form.package_size = '';
        form.clearErrors('package_unit', 'package_size');
    },
);

// Stock is changed through Stock adjustment, so the field only displays it.
const stockDisplay = computed({
    get: () => formatQuantity(inventory.quantity),
    set: () => {},
});

const stepErrors = computed(
    () => form.errors as Record<string, string | undefined>,
);

const supplierName = computed(
    () =>
        suppliers.find((s) => String(s.id) === form.supplier_id)?.name ??
        'No supplier',
);

// What one unit costs the shop (the saved cost is already per unit), for the pricing overview.
const packageCount = computed(() => Number(form.package_size) || 0);
const costPerUnit = computed(() =>
    form.unit_price === ''
        ? null
        : packageCount.value > 0
          ? Number(form.unit_price) / packageCount.value
          : Number(form.unit_price),
);

// ─── Side panel preview and save status ────────────────────────────────────

const categoryName = computed(
    () =>
        categories.find((c) => String(c.id) === form.inventory_categories_id)
            ?.name ?? 'No category',
);

const photoPreview = computed(() =>
    form.photo
        ? URL.createObjectURL(form.photo)
        : form.remove_photo
          ? null
          : inventory.photo_url,
);

const previewPrice = computed(() =>
    form.unit_price === ''
        ? '₱ —'
        : `₱${Number(form.unit_price).toLocaleString('en-PH', { minimumFractionDigits: 2 })} / ${form.package_unit || form.unit}`,
);

const saveStatus = computed(() => {
    if (form.recentlySuccessful) return 'Changes saved';

    return form.isDirty ? 'You have unsaved changes' : 'No changes yet';
});

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
        // The cost is typed per purchase unit and saved per unit (₱80 per 5,000 ml = ₱0.016 per ml).
        unit_price:
            data.unit_price === '' || packageCount.value <= 0
                ? data.unit_price
                : Number(
                      (Number(data.unit_price) / packageCount.value).toFixed(8),
                  ),
        _method: 'put',
    })).post(`${base.value}/${inventory.id}`, { forceFormData: true });
}
</script>

<template>
    <Head :title="`Edit — ${inventory.name}`" />
    <ShopLayout title="Edit Inventory Item">
        <div class="mx-auto max-w-6xl space-y-6 px-6 pb-10">
            <!-- Header -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <nav
                        aria-label="Breadcrumb"
                        class="mb-2 flex flex-wrap items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <Link :href="base" class="hover:text-foreground"
                            >Inventory</Link
                        >
                        <span aria-hidden="true">/</span>
                        <Link
                            :href="`${base}/${inventory.id}`"
                            class="hover:text-foreground"
                            >{{ inventory.name }}</Link
                        >
                        <span aria-hidden="true">/</span>
                        <span class="font-medium text-foreground">Edit</span>
                    </nav>
                    <h2 class="text-2xl font-bold tracking-tight">Edit item</h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Update details for "{{ inventory.name }}".
                    </p>
                </div>
                <Button
                    type="button"
                    variant="outline"
                    class="shrink-0"
                    @click="router.visit(base)"
                >
                    <ArrowLeft class="mr-2 h-4 w-4" /> Back to inventory
                </Button>
            </div>

            <form
                class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_300px]"
                @submit.prevent="submit"
            >
                <!-- Left column -->
                <div class="space-y-8">
                    <!-- Basic information -->
                    <section>
                        <h3 class="text-base font-semibold">
                            Basic information
                        </h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            What is this item and how is it counted?
                        </p>
                        <div class="mt-5 grid gap-6 md:grid-cols-[180px_1fr]">
                            <ServicePhotoInput
                                v-model="form.photo"
                                v-model:removed="form.remove_photo"
                                compact
                                :current-url="inventory.photo_url"
                                :error="form.errors.photo"
                            />
                            <div class="grid grid-cols-12 gap-x-5 gap-y-5">
                                <div
                                    class="col-span-12 space-y-1.5 sm:col-span-7"
                                >
                                    <label
                                        for="item-name"
                                        class="text-sm font-medium"
                                        >Item name
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <Input
                                        id="item-name"
                                        v-model="form.name"
                                        placeholder="e.g. Fabric Conditioner"
                                        :class="{
                                            'border-red-500': form.errors.name,
                                        }"
                                    />
                                    <p
                                        v-if="form.errors.name"
                                        class="text-xs text-red-500"
                                    >
                                        {{ form.errors.name }}
                                    </p>
                                </div>
                                <div
                                    class="col-span-12 space-y-1.5 sm:col-span-5"
                                >
                                    <label class="text-sm font-medium"
                                        >Category</label
                                    >
                                    <Select
                                        :model-value="
                                            form.inventory_categories_id || NONE
                                        "
                                        @update:model-value="
                                            (v) =>
                                                (form.inventory_categories_id =
                                                    v === NONE ? '' : String(v))
                                        "
                                    >
                                        <SelectTrigger class="w-full min-w-0"
                                            ><SelectValue
                                                placeholder="Select category"
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
                                <div class="col-span-12 space-y-1.5">
                                    <label
                                        for="item-description"
                                        class="text-sm font-medium"
                                        >Description
                                        <span
                                            class="text-xs font-normal text-muted-foreground"
                                            >(optional)</span
                                        ></label
                                    >
                                    <Textarea
                                        id="item-description"
                                        v-model="form.description"
                                        placeholder="Optional item description..."
                                        rows="3"
                                        maxlength="2000"
                                    />
                                    <p
                                        v-if="form.errors.description"
                                        class="text-xs text-red-500"
                                    >
                                        {{ form.errors.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Unit configuration -->
                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">
                            Unit configuration
                        </h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            The usage unit, how it is bought, and what you pay.
                        </p>
                        <div class="mt-5">
                            <UnitConfigurationSteps
                                v-model:unit="form.unit"
                                v-model:purchase-unit="purchaseUnit"
                                v-model:package-size="form.package_size"
                                v-model:quantity="stockDisplay"
                                v-model:cost="form.unit_price"
                                stock-readonly
                                stock-hint="Change it under Stock adjustment below."
                                :unit-options="unitOptions"
                                :purchase-options="purchaseOptions"
                                :purchase-selected="purchaseSelected"
                                :package-unit="form.package_unit"
                                :entry-label="inventory.unit"
                                :uses-packages="false"
                                :total-stock="inventory.quantity"
                                :cost-label="`Cost per ${form.package_unit || 'purchase unit'} (₱)`"
                                :errors="stepErrors"
                            />
                        </div>
                    </section>

                    <!-- Stock adjustment -->
                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">
                            Stock adjustment
                        </h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            Restock, return or write off stock. Applied right
                            away and kept in the stock history, separate from
                            Save changes.
                        </p>
                        <div
                            class="mt-5 rounded-lg bg-muted/40 px-4 py-3 text-sm"
                        >
                            <span class="text-muted-foreground"
                                >Current stock:</span
                            >
                            <template v-if="packageStock(inventory)">
                                <strong class="ml-1">{{
                                    packageStock(inventory)!.full
                                }}</strong>
                                <span
                                    v-if="packageStock(inventory)!.remainder"
                                    class="text-muted-foreground"
                                >
                                    +
                                    {{
                                        packageStock(inventory)!.remainder
                                    }}</span
                                >
                                <span
                                    class="block text-xs text-muted-foreground"
                                >
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
                        <div class="mt-5 grid grid-cols-12 gap-x-6 gap-y-5">
                            <div class="col-span-12 space-y-1.5 sm:col-span-3">
                                <label class="text-sm font-medium">Type</label>
                                <Select v-model="adjustForm.type">
                                    <SelectTrigger class="w-full min-w-0"
                                        ><SelectValue
                                    /></SelectTrigger>
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
                            <div class="col-span-12 space-y-1.5 sm:col-span-3">
                                <label
                                    for="adjust-quantity"
                                    class="text-sm font-medium"
                                    >Quantity ({{
                                        hasPackage && adjustInPackages
                                            ? inventory.package_unit
                                            : inventory.unit
                                    }})</label
                                >
                                <Input
                                    id="adjust-quantity"
                                    v-model="adjustForm.quantity"
                                    type="number"
                                    min="0"
                                    :step="
                                        hasPackage && adjustInPackages
                                            ? 'any'
                                            : 1
                                    "
                                    :class="{
                                        'border-red-500':
                                            adjustForm.errors.quantity,
                                    }"
                                    @keydown.enter.prevent="submitAdjust"
                                />
                                <button
                                    v-if="hasPackage"
                                    type="button"
                                    class="text-xs text-primary hover:underline"
                                    @click="
                                        adjustInPackages = !adjustInPackages
                                    "
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
                            <div class="col-span-12 space-y-1.5 sm:col-span-3">
                                <label
                                    for="adjust-reference"
                                    class="text-sm font-medium"
                                    >Reference No.
                                    <span
                                        class="text-xs font-normal text-muted-foreground"
                                        >(optional)</span
                                    ></label
                                >
                                <Input
                                    id="adjust-reference"
                                    v-model="adjustForm.reference_number"
                                    placeholder="e.g. PO-001"
                                    @keydown.enter.prevent="submitAdjust"
                                />
                            </div>
                            <div class="col-span-12 space-y-1.5 sm:col-span-3">
                                <label
                                    for="adjust-notes"
                                    class="text-sm font-medium"
                                    >Notes
                                    <span
                                        class="text-xs font-normal text-muted-foreground"
                                        >(optional)</span
                                    ></label
                                >
                                <Input
                                    id="adjust-notes"
                                    v-model="adjustForm.notes"
                                    placeholder="Reason for adjustment..."
                                    @keydown.enter.prevent="submitAdjust"
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
                                    <strong>{{
                                        formatQuantity(stockAfter)
                                    }}</strong>
                                    {{ inventory.unit }}.
                                </template>
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
                    </section>

                    <!-- Sales options -->
                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">Sales options</h3>
                        <p class="mt-0.5 mb-5 text-sm text-muted-foreground">
                            Choose how customers are charged for this item.
                            Internal use is set per service in Operations →
                            Services.
                        </p>
                        <InventorySalesOptions
                            :form="form"
                            :unit="form.unit"
                            :cost-per-unit="costPerUnit"
                            :package-unit="form.package_unit.trim()"
                            :package-size="form.package_size"
                        />
                    </section>

                    <!-- Stock alerts & additional settings -->
                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">
                            Stock alerts &amp; additional settings
                        </h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            When to be alerted, where it comes from, and any
                            notes.
                        </p>
                        <div class="mt-5 grid grid-cols-12 gap-x-5 gap-y-5">
                            <div class="col-span-12 space-y-1.5 sm:col-span-4">
                                <label
                                    for="item-min"
                                    class="text-sm font-medium"
                                    >Reorder level ({{ form.unit }})
                                    <span class="text-red-500">*</span></label
                                >
                                <Input
                                    id="item-min"
                                    v-model="form.min_stock"
                                    type="number"
                                    min="0"
                                    :class="{
                                        'border-red-500': form.errors.min_stock,
                                    }"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Get notified when stock falls to or below
                                    this level.
                                </p>
                                <p
                                    v-if="form.errors.min_stock"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.min_stock }}
                                </p>
                            </div>
                            <div class="col-span-12 space-y-1.5 sm:col-span-4">
                                <label
                                    for="item-max"
                                    class="text-sm font-medium"
                                    >Maximum stock
                                    <span
                                        class="text-xs font-normal text-muted-foreground"
                                        >(optional)</span
                                    ></label
                                >
                                <Input
                                    id="item-max"
                                    v-model="form.max_stock"
                                    type="number"
                                    min="0"
                                    placeholder="No limit"
                                    :class="{
                                        'border-red-500': form.errors.max_stock,
                                    }"
                                />
                                <p
                                    v-if="form.errors.max_stock"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.max_stock }}
                                </p>
                            </div>
                            <div
                                v-if="hasSuppliers"
                                class="col-span-12 space-y-1.5 sm:col-span-4"
                            >
                                <label class="text-sm font-medium"
                                    >Supplier</label
                                >
                                <Select
                                    :model-value="form.supplier_id || NONE"
                                    @update:model-value="
                                        (v) =>
                                            (form.supplier_id =
                                                v === NONE ? '' : String(v))
                                    "
                                >
                                    <SelectTrigger class="w-full min-w-0"
                                        ><SelectValue
                                            placeholder="Select supplier"
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
                            <div class="col-span-12 space-y-1.5 sm:col-span-4">
                                <label class="text-sm font-medium"
                                    >Status
                                    <span class="text-red-500">*</span></label
                                >
                                <Select v-model="form.status">
                                    <SelectTrigger class="w-full min-w-0"
                                        ><SelectValue
                                    /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active"
                                            >Active</SelectItem
                                        >
                                        <SelectItem value="inactive"
                                            >Inactive</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="col-span-12 space-y-1.5 sm:col-span-8">
                                <label
                                    for="item-notes"
                                    class="text-sm font-medium"
                                    >Notes
                                    <span
                                        class="text-xs font-normal text-muted-foreground"
                                        >(optional)</span
                                    ></label
                                >
                                <Textarea
                                    id="item-notes"
                                    v-model="form.notes"
                                    placeholder="e.g. Keep in a cool and dry place."
                                    rows="2"
                                    maxlength="1000"
                                />
                                <p
                                    v-if="form.errors.notes"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.notes }}
                                </p>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Right column -->
                <aside
                    class="rounded-xl border bg-card p-4 shadow-sm lg:sticky lg:top-4"
                >
                    <section>
                        <p
                            class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                        >
                            Item preview
                        </p>
                        <div class="mt-3 overflow-hidden rounded-xl border">
                            <div
                                class="flex h-24 items-center justify-center bg-blue-50 text-blue-500 dark:bg-blue-950/40"
                            >
                                <img
                                    v-if="photoPreview"
                                    :src="photoPreview"
                                    alt=""
                                    class="h-full w-full object-cover"
                                />
                                <Package v-else class="h-8 w-8" />
                            </div>
                            <div class="space-y-1 p-3">
                                <p class="text-xs font-semibold text-blue-600">
                                    {{ categoryName }} � {{ supplierName }}
                                </p>
                                <p
                                    class="truncate text-base font-bold"
                                    :class="
                                        form.name
                                            ? ''
                                            : 'text-muted-foreground/60'
                                    "
                                >
                                    {{ form.name || 'Item name' }}
                                </p>
                                <div
                                    class="flex items-center justify-between border-t pt-2 text-xs"
                                >
                                    <span class="font-bold">{{
                                        previewPrice
                                    }}</span>
                                    <span class="text-muted-foreground">{{
                                        form.status === 'active'
                                            ? 'Active'
                                            : 'Inactive'
                                    }}</span>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    On hand:
                                    {{ formatQuantity(inventory.quantity) }}
                                    {{ inventory.unit }} � Alert at
                                    {{
                                        formatQuantity(
                                            Number(form.min_stock) || 0,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="mt-4 border-t pt-4">
                        <p
                            class="text-xs"
                            :class="
                                form.isDirty
                                    ? 'font-medium text-amber-600 dark:text-amber-400'
                                    : 'text-muted-foreground'
                            "
                            role="status"
                        >
                            {{ saveStatus }}
                        </p>

                        <Button
                            type="submit"
                            class="mt-3 w-full"
                            :disabled="!form.isDirty || form.processing"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            {{ form.processing ? 'Saving…' : 'Save changes' }}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            class="mt-2 w-full"
                            :disabled="form.processing"
                            @click="router.visit(`${base}/${inventory.id}`)"
                        >
                            Cancel
                        </Button>
                    </section>
                </aside>
            </form>
        </div>
    </ShopLayout>
</template>
