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
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    Loader2,
    Package,
    Save,
} from 'lucide-vue-next';
import { computed } from 'vue';

interface Category {
    id: number;
    name: string;
}
interface Supplier {
    id: number;
    name: string;
}

const { categories, suppliers } = defineProps<{
    categories: Category[];
    suppliers: Supplier[];
}>();

const { isOwner } = usePermissions();
const base = computed(() =>
    isOwner.value ? '/shop/inventory' : '/staff/inventory',
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: base.value },
    { title: 'Add Item', href: `${base.value}/create` },
];

const form = useForm({
    name: '',
    description: '',
    photo: null as File | null,
    unit: 'pcs',
    package_unit: '',
    package_size: '',
    quantity: 0,
    min_stock: 5,
    max_stock: '',
    unit_price: '',
    addon_enabled: false,
    addon_name: '',
    addon_quantity: '' as number | string,
    addon_price: '' as number | string,
    retail_enabled: false,
    retail_name: '',
    retail_pack_size: '' as number | string,
    retail_price: '' as number | string,
    notes: '',
    status: 'active',
    inventory_categories_id: '',
    supplier_id: '',
});

const unitOptions = [
    { value: 'pcs', label: 'Pieces (pcs)' },
    { value: 'kg', label: 'Kilograms (kg)' },
    { value: 'g', label: 'Grams (g)' },
    { value: 'liters', label: 'Liters (L)' },
    { value: 'ml', label: 'Milliliters (ml)' },
    { value: 'bottles', label: 'Bottles' },
    { value: 'boxes', label: 'Boxes' },
    { value: 'packs', label: 'Packs' },
    { value: 'rolls', label: 'Rolls' },
];

const peso = (value: string | number) =>
    `₱${Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;

const categoryName = computed(
    () =>
        categories.find((c) => String(c.id) === form.inventory_categories_id)
            ?.name ?? 'No category',
);
const supplierName = computed(
    () =>
        suppliers.find((s) => String(s.id) === form.supplier_id)?.name ??
        'No supplier',
);

const saveStatus = computed(() =>
    form.isDirty ? 'You have unsaved changes' : 'No changes yet',
);

// When the item is bought in packages (e.g. bottles of 5,000 ml), the stock
// fields are typed in packages and saved in the item's own unit.
const purchaseUnitOptions = [
    { value: 'none', label: 'Same as consumption unit' },
    { value: 'bottle', label: 'Bottle' },
    { value: 'gallon', label: 'Gallon' },
    { value: 'sack', label: 'Sack' },
    { value: 'pack', label: 'Pack' },
    { value: 'box', label: 'Box' },
    { value: 'carton', label: 'Carton' },
    { value: 'drum', label: 'Drum' },
    { value: 'roll', label: 'Roll' },
];

const purchaseUnit = computed({
    get: () => form.package_unit || 'none',
    set: (value: string) => {
        form.package_unit = value === 'none' ? '' : value;
        if (value === 'none') form.package_size = '';
        form.clearErrors('package_unit', 'package_size');
    },
});
const purchaseSelected = computed(() => form.package_unit !== '');
const usesPackages = computed(
    () => purchaseSelected.value && Number(form.package_size) > 0,
);
const packageCount = computed(() => Number(form.package_size) || 0);
const inUnit = (packages: number | string) =>
    Math.round(Number(packages || 0) * packageCount.value);
// What one unit costs the shop, for the pricing overview.
const costPerUnit = computed(() =>
    form.unit_price === ''
        ? null
        : usesPackages.value
          ? Number(form.unit_price) / packageCount.value
          : Number(form.unit_price),
);
const entryLabel = computed(() =>
    usesPackages.value ? form.package_unit.trim() : form.unit,
);

function submit() {
    form.transform((raw) => {
        const data = {
            ...raw,
        };

        return usesPackages.value
            ? {
                  ...data,
                  quantity: inUnit(data.quantity),
                  min_stock: inUnit(data.min_stock),
                  max_stock:
                      data.max_stock === '' ? '' : inUnit(data.max_stock),
                  // The cost is typed per package and saved per unit (₱80 per 5,000 ml = ₱0.016 per ml).
                  unit_price:
                      data.unit_price === ''
                          ? ''
                          : Number(
                                (
                                    Number(data.unit_price) / packageCount.value
                                ).toFixed(4),
                            ),
              }
            : { ...data, package_unit: '', package_size: '' };
    }).post(base.value, {
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Head title="Add Inventory Item" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Add Inventory Item">
        <div class="mx-auto max-w-6xl space-y-6 px-6 pb-10">
            <!-- Header -->
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <span
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40"
                    >
                        <Package class="h-6 w-6" aria-hidden="true" />
                    </span>
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight">
                            Add Inventory Item
                        </h2>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            Configure your supply with flexible usage options,
                            units, and pricing.
                        </p>
                    </div>
                </div>
                <Button
                    type="button"
                    variant="outline"
                    class="shrink-0"
                    @click="router.visit(base)"
                >
                    <ArrowLeft class="mr-2 h-4 w-4" aria-hidden="true" /> Back
                    to inventory
                </Button>
            </div>

            <form
                class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_300px]"
                @submit.prevent="submit"
            >
                <div class="space-y-6">
                    <!-- 1. Basic information -->
                    <section class="rounded-xl border bg-card p-5">
                        <div class="flex items-start gap-3">
                            <span
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white"
                                >1</span
                            >
                            <div>
                                <h3 class="text-base font-semibold">
                                    Basic Information
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    What is this item and how is it counted?
                                </p>
                            </div>
                        </div>
                        <div class="mt-5 grid gap-6 md:grid-cols-[180px_1fr]">
                            <ServicePhotoInput
                                v-model="form.photo"
                                compact
                                :error="form.errors.photo"
                            />
                            <div class="grid grid-cols-12 gap-x-5 gap-y-4">
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
                                        v-model="form.inventory_categories_id"
                                    >
                                        <SelectTrigger
                                            ><SelectValue
                                                placeholder="Select category"
                                        /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none"
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
                                            class="text-xs text-muted-foreground"
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

                    <!-- 2. Unit configuration -->
                    <section class="rounded-xl border bg-card p-5">
                        <div class="flex items-start gap-3">
                            <span
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white"
                                >2</span
                            >
                            <div>
                                <h3 class="text-base font-semibold">
                                    Unit Configuration
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    Set the usage unit, how it is bought, and
                                    the starting stock.
                                </p>
                            </div>
                        </div>
                        <div class="mt-5 grid grid-cols-12 gap-x-5 gap-y-4">
                            <!-- Purchase unit -->
                            <div
                                class="col-span-12 space-y-1.5 sm:col-span-6 lg:col-span-3"
                            >
                                <label class="text-sm font-medium"
                                    >Purchase unit</label
                                >
                                <Select v-model="purchaseUnit">
                                    <SelectTrigger
                                        :class="{
                                            'border-red-500':
                                                form.errors.package_unit,
                                        }"
                                        ><SelectValue placeholder="Select unit"
                                    /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="p in purchaseUnitOptions"
                                            :key="p.value"
                                            :value="p.value"
                                            >{{ p.label }}</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                                <p class="text-xs text-muted-foreground">
                                    How you buy it from the supplier.
                                </p>
                                <p
                                    v-if="form.errors.package_unit"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.package_unit }}
                                </p>
                            </div>

                            <!-- Container size -->
                            <div
                                class="col-span-12 space-y-1.5 sm:col-span-6 lg:col-span-3"
                            >
                                <label
                                    for="item-package-size"
                                    class="text-sm font-medium"
                                    >Container size</label
                                >
                                <div class="flex items-center gap-2">
                                    <Input
                                        id="item-package-size"
                                        v-model="form.package_size"
                                        type="number"
                                        min="1"
                                        placeholder="5000"
                                        :disabled="!purchaseSelected"
                                        :class="{
                                            'border-red-500':
                                                form.errors.package_size,
                                        }"
                                    />
                                    <span
                                        class="shrink-0 text-sm text-muted-foreground"
                                        >{{ form.unit }}</span
                                    >
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    <template v-if="purchaseSelected"
                                        >e.g. 5000 {{ form.unit }} per
                                        {{ form.package_unit }}</template
                                    >
                                    <template v-else
                                        >Not needed when bought in
                                        {{ form.unit }}.</template
                                    >
                                </p>
                                <p
                                    v-if="form.errors.package_size"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.package_size }}
                                </p>
                            </div>

                            <!-- Stock quantity -->
                            <div
                                class="col-span-12 space-y-1.5 sm:col-span-6 lg:col-span-3"
                            >
                                <label
                                    for="item-quantity"
                                    class="text-sm font-medium"
                                    >Stock quantity
                                    <span class="text-red-500">*</span></label
                                >
                                <div class="flex items-center gap-2">
                                    <Input
                                        id="item-quantity"
                                        v-model="form.quantity"
                                        type="number"
                                        min="0"
                                        :step="usesPackages ? 'any' : 1"
                                        :class="{
                                            'border-red-500':
                                                form.errors.quantity,
                                        }"
                                    />
                                    <span
                                        class="shrink-0 text-sm text-muted-foreground"
                                        >{{ entryLabel }}</span
                                    >
                                </div>
                                <p
                                    v-if="usesPackages"
                                    class="inline-block rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-950/40 dark:text-blue-300"
                                >
                                    Total stock:
                                    {{ inUnit(form.quantity).toLocaleString() }}
                                    {{ form.unit }}
                                </p>
                                <p
                                    v-if="form.errors.quantity"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.quantity }}
                                </p>
                            </div>

                            <!-- Consumption unit -->
                            <div
                                class="col-span-12 space-y-1.5 sm:col-span-6 lg:col-span-3"
                            >
                                <label class="text-sm font-medium"
                                    >Consumption unit
                                    <span class="text-red-500">*</span></label
                                >
                                <Select v-model="form.unit">
                                    <SelectTrigger
                                        :class="{
                                            'border-red-500': form.errors.unit,
                                        }"
                                        ><SelectValue placeholder="Select unit"
                                    /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="u in unitOptions"
                                            :key="u.value"
                                            :value="u.value"
                                            >{{ u.label }}</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                                <p class="text-xs text-muted-foreground">
                                    Used for internal &amp; add-on usage.
                                </p>
                                <p
                                    v-if="form.errors.unit"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.unit }}
                                </p>
                            </div>

                            <!-- Cost -->
                            <div
                                class="col-span-12 space-y-1.5 sm:col-span-6 lg:col-span-3"
                            >
                                <label
                                    for="item-cost"
                                    class="text-sm font-medium"
                                    >{{
                                        usesPackages
                                            ? `Cost per ${form.package_unit.trim()} (₱)`
                                            : `Cost per ${form.unit} (₱)`
                                    }}</label
                                >
                                <Input
                                    id="item-cost"
                                    v-model="form.unit_price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                    :class="{
                                        'border-red-500':
                                            form.errors.unit_price,
                                    }"
                                />
                                <p
                                    v-if="form.errors.unit_price"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.unit_price }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- 3. Sales options -->
                    <section class="rounded-xl border bg-card p-5">
                        <div class="flex items-start gap-3">
                            <span
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white"
                                >3</span
                            >
                            <div>
                                <h3 class="text-base font-semibold">
                                    Sales Options
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    Choose how customers are charged for this
                                    item. Internal use is set per service in
                                    Operations → Services.
                                </p>
                            </div>
                        </div>
                        <div class="mt-5">
                            <InventorySalesOptions
                                :form="form"
                                :unit="form.unit"
                                :cost-per-unit="costPerUnit"
                                :package-unit="form.package_unit.trim()"
                                :package-size="form.package_size"
                            />
                        </div>
                    </section>

                    <!-- 4. Stock alerts & settings -->
                    <section class="rounded-xl border bg-card p-5">
                        <div class="flex items-start gap-3">
                            <span
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white"
                                >4</span
                            >
                            <div>
                                <h3 class="text-base font-semibold">
                                    Stock Alerts &amp; Additional Settings
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    When to be alerted, where it comes from, and
                                    any notes.
                                </p>
                            </div>
                        </div>
                        <div class="mt-5 grid grid-cols-12 gap-x-5 gap-y-4">
                            <div class="col-span-12 space-y-1.5 sm:col-span-4">
                                <label
                                    for="item-min"
                                    class="text-sm font-medium"
                                    >Reorder level ({{ entryLabel }})
                                    <span class="text-red-500">*</span></label
                                >
                                <Input
                                    id="item-min"
                                    v-model="form.min_stock"
                                    type="number"
                                    min="0"
                                    :step="usesPackages ? 'any' : 1"
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
                                    <span class="text-xs text-muted-foreground"
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
                            <div class="col-span-12 space-y-1.5 sm:col-span-4">
                                <label class="text-sm font-medium"
                                    >Supplier</label
                                >
                                <Select v-model="form.supplier_id">
                                    <SelectTrigger
                                        ><SelectValue
                                            placeholder="Select supplier"
                                    /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="none"
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
                            <div class="col-span-12 space-y-1.5 sm:col-span-6">
                                <label
                                    for="item-notes"
                                    class="text-sm font-medium"
                                    >Notes
                                    <span class="text-xs text-muted-foreground"
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

                <!-- Side panel -->
                <aside
                    class="rounded-xl border bg-card p-4 shadow-sm lg:sticky lg:top-4"
                >
                    <p
                        class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                    >
                        Item summary
                    </p>
                    <div class="mt-3 flex items-start gap-3">
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-500 dark:bg-blue-950/40"
                        >
                            <Package class="h-5 w-5" aria-hidden="true" />
                        </span>
                        <div class="min-w-0">
                            <p
                                class="truncate text-base font-bold"
                                :class="
                                    form.name ? '' : 'text-muted-foreground/60'
                                "
                            >
                                {{ form.name || 'Item name' }}
                            </p>
                            <p class="truncate text-xs text-muted-foreground">
                                {{ categoryName }} · {{ supplierName }}
                            </p>
                        </div>
                    </div>

                    <ul class="mt-3 space-y-1 text-sm">
                        <li class="flex items-center gap-2">
                            <CheckCircle2
                                class="h-4 w-4 text-emerald-500"
                                aria-hidden="true"
                            />
                            Internal use (set in Services)
                        </li>
                        <li
                            class="flex items-center gap-2"
                            :class="
                                form.addon_enabled
                                    ? ''
                                    : 'text-muted-foreground/60'
                            "
                        >
                            <CheckCircle2
                                class="h-4 w-4"
                                :class="
                                    form.addon_enabled
                                        ? 'text-emerald-500'
                                        : 'text-muted-foreground/40'
                                "
                                aria-hidden="true"
                            />
                            Additional charge
                        </li>
                        <li
                            class="flex items-center gap-2"
                            :class="
                                form.retail_enabled
                                    ? ''
                                    : 'text-muted-foreground/60'
                            "
                        >
                            <CheckCircle2
                                class="h-4 w-4"
                                :class="
                                    form.retail_enabled
                                        ? 'text-emerald-500'
                                        : 'text-muted-foreground/40'
                                "
                                aria-hidden="true"
                            />
                            Retail product
                        </li>
                    </ul>

                    <dl class="mt-4 space-y-2 border-t pt-4 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-muted-foreground">
                                Starting stock
                            </dt>
                            <dd class="font-medium">
                                {{
                                    usesPackages
                                        ? inUnit(form.quantity).toLocaleString()
                                        : form.quantity || 0
                                }}
                                {{ form.unit }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-muted-foreground">Alert at</dt>
                            <dd class="font-medium">
                                {{
                                    usesPackages
                                        ? inUnit(
                                              form.min_stock,
                                          ).toLocaleString()
                                        : form.min_stock || 0
                                }}
                                {{ form.unit }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-muted-foreground">Cost</dt>
                            <dd class="font-medium">
                                {{
                                    form.unit_price !== ''
                                        ? `${peso(form.unit_price)}${usesPackages ? ` / ${form.package_unit.trim()}` : ''}`
                                        : '—'
                                }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-muted-foreground">Status</dt>
                            <dd class="font-medium capitalize">
                                {{ form.status }}
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-4 border-t pt-4">
                        <p
                            class="text-xs"
                            :class="
                                form.isDirty
                                    ? 'text-amber-600'
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
                                aria-hidden="true"
                            />
                            <Save
                                v-else
                                class="mr-2 h-4 w-4"
                                aria-hidden="true"
                            />
                            {{ form.processing ? 'Saving…' : 'Save item' }}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            class="mt-2 w-full"
                            :disabled="form.processing"
                            @click="router.visit(base)"
                        >
                            Cancel
                        </Button>
                    </div>
                </aside>
            </form>
        </div>
    </ShopLayout>
</template>
