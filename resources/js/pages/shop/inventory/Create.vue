<script setup lang="ts">
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
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, Package } from 'lucide-vue-next';
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
    unit: 'pcs',
    package_unit: '',
    package_size: '',
    quantity: 0,
    min_stock: 5,
    max_stock: '',
    unit_price: '',
    selling_price: '',
    status: 'active',
    inventory_categories_id: '',
    supplier_id: '',
});

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
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

const margin = computed(() => {
    if (form.unit_price === '' || form.selling_price === '') {
        return null;
    }

    return Number(form.selling_price) - Number(form.unit_price);
});

const saveStatus = computed(() =>
    form.isDirty ? 'You have unsaved changes' : 'No changes yet',
);

// When the item is bought in packages (e.g. bottles of 5,000 ml), the stock
// fields are typed in packages and saved in the item's own unit.
const usesPackages = computed(
    () => form.package_unit.trim() !== '' && Number(form.package_size) > 0,
);
const packageCount = computed(() => Number(form.package_size) || 0);
const inUnit = (packages: number | string) =>
    Math.round(Number(packages || 0) * packageCount.value);
const entryLabel = computed(() =>
    usesPackages.value ? form.package_unit.trim() : form.unit,
);

function submit() {
    form.transform((data) =>
        usesPackages.value
            ? {
                  ...data,
                  quantity: inUnit(data.quantity),
                  min_stock: inUnit(data.min_stock),
                  max_stock:
                      data.max_stock === '' ? '' : inUnit(data.max_stock),
              }
            : { ...data, package_unit: '', package_size: '' },
    ).post(base.value, {
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
                <div>
                    <nav
                        aria-label="Breadcrumb"
                        class="mb-2 flex flex-wrap items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <span>Inventory</span>
                        <span aria-hidden="true">/</span>
                        <Link :href="base" class="hover:text-foreground"
                            >Items</Link
                        >
                        <span aria-hidden="true">/</span>
                        <span class="font-medium text-foreground"
                            >Add item</span
                        >
                    </nav>
                    <h2 class="text-2xl font-bold tracking-tight">
                        New inventory item
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Add a supply or product and set when to be alerted to
                        restock.
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
                            What the item is and how it is counted.
                        </p>
                        <div class="mt-5 grid grid-cols-12 gap-x-6 gap-y-5">
                            <div class="col-span-12 space-y-1.5 sm:col-span-8">
                                <label
                                    for="item-name"
                                    class="text-sm font-medium"
                                    >Item name
                                    <span class="text-red-500">*</span></label
                                >
                                <Input
                                    id="item-name"
                                    v-model="form.name"
                                    placeholder="e.g. Detergent Powder"
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
                            <div class="col-span-12 space-y-1.5 sm:col-span-4">
                                <label class="text-sm font-medium"
                                    >Unit
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
                                        <SelectItem value="pcs"
                                            >Pieces (pcs)</SelectItem
                                        >
                                        <SelectItem value="kg"
                                            >Kilograms (kg)</SelectItem
                                        >
                                        <SelectItem value="g"
                                            >Grams (g)</SelectItem
                                        >
                                        <SelectItem value="liters"
                                            >Liters (L)</SelectItem
                                        >
                                        <SelectItem value="ml"
                                            >Milliliters (ml)</SelectItem
                                        >
                                        <SelectItem value="bottles"
                                            >Bottles</SelectItem
                                        >
                                        <SelectItem value="boxes"
                                            >Boxes</SelectItem
                                        >
                                        <SelectItem value="packs"
                                            >Packs</SelectItem
                                        >
                                        <SelectItem value="rolls"
                                            >Rolls</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                                <p
                                    v-if="form.errors.unit"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.unit }}
                                </p>
                            </div>
                            <div class="col-span-12 space-y-1.5">
                                <label
                                    for="item-description"
                                    class="text-sm font-medium"
                                    >Description</label
                                >
                                <Textarea
                                    id="item-description"
                                    v-model="form.description"
                                    placeholder="Optional item description..."
                                    rows="2"
                                />
                                <p
                                    v-if="form.errors.description"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.description }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Stock -->
                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">Stock</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            Starting quantity and the levels that trigger a
                            low-stock alert.
                        </p>
                        <div class="mt-5 grid grid-cols-12 gap-x-6 gap-y-5">
                            <div class="col-span-12 space-y-1.5 sm:col-span-4">
                                <label
                                    for="item-package-unit"
                                    class="text-sm font-medium"
                                    >Bought as
                                    <span class="text-xs text-muted-foreground"
                                        >(optional)</span
                                    ></label
                                >
                                <Input
                                    id="item-package-unit"
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
                            <div class="col-span-12 space-y-1.5 sm:col-span-4">
                                <label
                                    for="item-package-size"
                                    class="text-sm font-medium"
                                    >Contents per
                                    {{ form.package_unit.trim() || 'package' }}
                                    <span class="text-xs text-muted-foreground"
                                        >({{ form.unit }})</span
                                    ></label
                                >
                                <Input
                                    id="item-package-size"
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
                            <p
                                class="col-span-12 -mt-2 text-xs text-muted-foreground"
                            >
                                <template v-if="usesPackages"
                                    >Stock below is entered in
                                    {{ form.package_unit.trim() }}s. It is saved
                                    as {{ form.unit }} (1
                                    {{ form.package_unit.trim() }} =
                                    {{ packageCount.toLocaleString() }}
                                    {{ form.unit }}).</template
                                >
                                <template v-else
                                    >Leave these empty to enter stock directly in
                                    {{ form.unit }}.</template
                                >
                            </p>
                            <div class="col-span-12 space-y-1.5 sm:col-span-4">
                                <label
                                    for="item-quantity"
                                    class="text-sm font-medium"
                                    >Initial quantity ({{ entryLabel }})
                                    <span class="text-red-500">*</span></label
                                >
                                <Input
                                    id="item-quantity"
                                    v-model="form.quantity"
                                    type="number"
                                    min="0"
                                    :step="usesPackages ? 'any' : 1"
                                    :class="{
                                        'border-red-500': form.errors.quantity,
                                    }"
                                />
                                <p
                                    v-if="form.errors.quantity"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.quantity }}
                                </p>
                            </div>
                            <div class="col-span-12 space-y-1.5 sm:col-span-4">
                                <label
                                    for="item-min"
                                    class="text-sm font-medium"
                                    >Minimum stock ({{ entryLabel }})
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
                                    Alert triggers at or below this.
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
                        </div>
                    </section>

                    <!-- Pricing -->
                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">Pricing</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            What the item costs you and, if sold, what customers
                            pay.
                        </p>
                        <div class="mt-5 grid grid-cols-12 gap-x-6 gap-y-5">
                            <div class="col-span-12 space-y-1.5 sm:col-span-6">
                                <label
                                    for="item-cost"
                                    class="text-sm font-medium"
                                    >Unit price (₱)</label
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
                                <p class="text-xs text-muted-foreground">
                                    Your cost from the supplier.
                                </p>
                                <p
                                    v-if="form.errors.unit_price"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.unit_price }}
                                </p>
                            </div>
                            <div class="col-span-12 space-y-1.5 sm:col-span-6">
                                <label
                                    for="item-sell"
                                    class="text-sm font-medium"
                                    >Selling price (₱)</label
                                >
                                <Input
                                    id="item-sell"
                                    v-model="form.selling_price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                    :class="{
                                        'border-red-500':
                                            form.errors.selling_price,
                                    }"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Charged to the customer.
                                </p>
                                <p
                                    v-if="form.errors.selling_price"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.selling_price }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Classification -->
                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">Classification</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            Group the item and note where you buy it.
                        </p>
                        <div class="mt-5 grid grid-cols-12 gap-x-6 gap-y-5">
                            <div class="col-span-12 space-y-1.5 sm:col-span-6">
                                <label class="text-sm font-medium"
                                    >Category</label
                                >
                                <Select v-model="form.inventory_categories_id">
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
                            <div class="col-span-12 space-y-1.5 sm:col-span-6">
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
                        </div>
                    </section>

                    <!-- Status -->
                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">Status</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            Inactive items stay on record but are set aside from
                            day-to-day use.
                        </p>
                        <div
                            class="mt-5 grid max-w-md grid-cols-2 gap-3"
                            role="radiogroup"
                            aria-label="Item status"
                        >
                            <button
                                v-for="option in statusOptions"
                                :key="option.value"
                                type="button"
                                role="radio"
                                :aria-checked="form.status === option.value"
                                class="rounded-lg border px-4 py-2.5 text-left text-sm font-medium transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="
                                    form.status === option.value
                                        ? 'border-blue-500 bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300'
                                        : 'border-input hover:bg-muted/50'
                                "
                                @click="form.status = option.value"
                            >
                                {{ option.label }}
                            </button>
                        </div>
                        <p
                            v-if="form.errors.status"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ form.errors.status }}
                        </p>
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

                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-muted-foreground">
                                Starting stock
                            </dt>
                            <dd class="font-medium">
                                {{ usesPackages ? inUnit(form.quantity).toLocaleString() : form.quantity || 0 }}
                                {{ form.unit }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-muted-foreground">Alert at</dt>
                            <dd class="font-medium">
                                {{ usesPackages ? inUnit(form.min_stock).toLocaleString() : form.min_stock || 0 }}
                                {{ form.unit }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-muted-foreground">Cost</dt>
                            <dd class="font-medium">
                                {{
                                    form.unit_price !== ''
                                        ? peso(form.unit_price)
                                        : '—'
                                }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-muted-foreground">Selling price</dt>
                            <dd class="font-medium">
                                {{
                                    form.selling_price !== ''
                                        ? peso(form.selling_price)
                                        : '—'
                                }}
                            </dd>
                        </div>
                        <div
                            v-if="margin !== null"
                            class="flex items-center justify-between"
                        >
                            <dt class="text-muted-foreground">
                                Margin per unit
                            </dt>
                            <dd
                                class="font-medium"
                                :class="
                                    margin < 0
                                        ? 'text-red-600'
                                        : 'text-emerald-600'
                                "
                            >
                                {{ peso(margin) }}
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
                            />
                            {{ form.processing ? 'Saving…' : 'Add item' }}
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
