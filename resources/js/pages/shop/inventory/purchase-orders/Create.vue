<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { peso } from '@/lib/purchaseOrders';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ClipboardList,
    Loader2,
    Plus,
    Send,
    Trash2,
    Truck,
} from 'lucide-vue-next';
import { computed, watch } from 'vue';

interface CatalogItem {
    public_id: string;
    name: string;
    unit: string | null;
    unit_price: number;
    stock_quantity: number;
}

interface SupplierOption {
    public_id: string;
    name: string;
    lead_time_days: number | null;
    items: CatalogItem[];
}

interface InventoryOption {
    id: number;
    name: string;
    unit: string;
    package_unit: string | null;
    package_size: number | null;
    supplier_id: number | null;
}

interface Line {
    supplier_item: string;
    quantity: string;
    inventory_id: number | '';
    stock_per_unit: number;
}

const props = defineProps<{
    suppliers: SupplierOption[];
    inventory: InventoryOption[];
    selectedSupplier: string | null;
    base: string;
}>();

const { isOwner } = usePermissions();
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Inventory',
        href: isOwner.value ? '/shop/inventory' : '/staff/inventory',
    },
    { title: 'Purchase Orders', href: props.base },
    { title: 'New order', href: `${props.base}/create` },
];

const blankLine = (): Line => ({
    supplier_item: '',
    quantity: '1',
    inventory_id: '',
    stock_per_unit: 1,
});

const form = useForm({
    supplier:
        props.suppliers.find((s) => s.public_id === props.selectedSupplier)
            ?.public_id ??
        (props.suppliers.length === 1 ? props.suppliers[0].public_id : ''),
    notes: '',
    send: true,
    items: [blankLine()] as Line[],
});

const supplier = computed(
    () => props.suppliers.find((s) => s.public_id === form.supplier) ?? null,
);
const catalog = computed(() => supplier.value?.items ?? []);

// A different supplier sells different things, so the lines start over.
watch(
    () => form.supplier,
    () => {
        form.items = [blankLine()];
        form.clearErrors();
    },
);

function catalogItem(publicId: string): CatalogItem | undefined {
    return catalog.value.find((item) => item.public_id === publicId);
}

function inventoryItem(id: number | ''): InventoryOption | undefined {
    return id === '' ? undefined : props.inventory.find((i) => i.id === id);
}

// Picking a supply links it to the inventory item with the same name, so receiving fills the stock.
function onPickItem(line: Line) {
    const picked = catalogItem(line.supplier_item);
    if (!picked) return;
    const match = props.inventory.find(
        (i) => i.name.trim().toLowerCase() === picked.name.trim().toLowerCase(),
    );
    line.inventory_id = match?.id ?? '';
    onPickInventory(line);
}

// One ordered unit adds a whole package to stock when the item is counted in packages.
function onPickInventory(line: Line) {
    const target = inventoryItem(line.inventory_id);
    line.stock_per_unit = target?.package_size ?? 1;
}

function addLine() {
    form.items = [...form.items, blankLine()];
}

function removeLine(index: number) {
    form.items = form.items.filter((_, i) => i !== index);
    if (form.items.length === 0) form.items = [blankLine()];
}

const usedItems = computed(
    () => new Set(form.items.map((line) => line.supplier_item)),
);

const lineTotal = (line: Line) =>
    (catalogItem(line.supplier_item)?.unit_price ?? 0) *
    (Number(line.quantity) || 0);

const total = computed(() =>
    form.items.reduce((sum, line) => sum + lineTotal(line), 0),
);
const filledLines = computed(
    () =>
        form.items.filter(
            (line) => line.supplier_item && Number(line.quantity) > 0,
        ).length,
);

const fieldError = (index: number, field: string) =>
    (form.errors as Record<string, string | undefined>)[
        `items.${index}.${field}`
    ];

function submit(send: boolean) {
    form.send = send;
    form.transform((data) => ({
        ...data,
        items: data.items
            .filter((line) => line.supplier_item)
            .map((line) => ({
                supplier_item: line.supplier_item,
                quantity: line.quantity,
                inventory_id:
                    line.inventory_id === '' ? null : line.inventory_id,
                stock_per_unit:
                    line.inventory_id === '' ? 1 : line.stock_per_unit,
            })),
    })).post(props.base);
}

const selectClass =
    'h-11 w-full rounded-md border border-input bg-background px-3 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none disabled:opacity-50';
</script>

<template>
    <Head title="New purchase order" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="New purchase order">
        <div class="mx-auto max-w-6xl space-y-6 px-6 pb-10">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">New purchase order</h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Order supplies from one of your suppliers. They confirm
                        it in their supplier portal.
                    </p>
                </div>
                <Button variant="back" @click="router.visit(base)">
                    <ArrowLeft class="h-4 w-4" />
                    Back to purchase orders
                </Button>
            </div>

            <!-- No suppliers yet -->
            <div
                v-if="suppliers.length === 0"
                class="rounded-[14px] border bg-card p-8 text-center"
            >
                <Truck
                    class="mx-auto h-10 w-10 text-muted-foreground"
                    aria-hidden="true"
                />
                <p class="mt-3 font-semibold">No active suppliers yet</p>
                <p class="mt-1 text-sm text-muted-foreground">
                    Add a supplier first. You can only order from your own
                    suppliers.
                </p>
                <Button
                    class="mt-4"
                    @click="
                        router.visit(
                            isOwner ? '/shop/supplier' : '/staff/supplier',
                        )
                    "
                >
                    Go to suppliers
                </Button>
            </div>

            <form
                v-else
                class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_320px]"
                @submit.prevent="submit(true)"
            >
                <div class="space-y-8">
                    <!-- Supplier -->
                    <section class="space-y-3">
                        <p
                            class="text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                        >
                            Supplier
                        </p>
                        <div class="max-w-md space-y-1.5">
                            <label for="po-supplier" class="text-sm font-medium"
                                >Order from
                                <span class="text-red-600">*</span></label
                            >
                            <select
                                id="po-supplier"
                                v-model="form.supplier"
                                :class="selectClass"
                                required
                            >
                                <option value="" disabled>
                                    Choose a supplier
                                </option>
                                <option
                                    v-for="s in suppliers"
                                    :key="s.public_id"
                                    :value="s.public_id"
                                >
                                    {{ s.name }}
                                </option>
                            </select>
                            <p
                                v-if="supplier?.lead_time_days"
                                class="text-xs text-muted-foreground"
                            >
                                Usually delivers in
                                {{ supplier.lead_time_days }}
                                {{
                                    supplier.lead_time_days === 1
                                        ? 'day'
                                        : 'days'
                                }}.
                            </p>
                            <InputError :message="form.errors.supplier" />
                        </div>
                    </section>

                    <!-- Items -->
                    <section v-if="supplier" class="space-y-3 border-t pt-8">
                        <p
                            class="text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                        >
                            Supplies
                        </p>

                        <p
                            v-if="catalog.length === 0"
                            class="rounded-lg border border-dashed p-4 text-sm text-muted-foreground"
                        >
                            {{ supplier.name }} has not listed any supplies yet.
                            They add their supplies and prices in the supplier
                            portal.
                        </p>

                        <template v-else>
                            <div
                                v-for="(line, index) in form.items"
                                :key="index"
                                class="space-y-3 rounded-xl border bg-card p-4"
                            >
                                <div
                                    class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_110px_auto]"
                                >
                                    <div class="space-y-1.5">
                                        <label
                                            :for="`po-item-${index}`"
                                            class="text-sm font-medium"
                                            >Supply</label
                                        >
                                        <select
                                            :id="`po-item-${index}`"
                                            v-model="line.supplier_item"
                                            :class="selectClass"
                                            @change="onPickItem(line)"
                                        >
                                            <option value="" disabled>
                                                Choose a supply
                                            </option>
                                            <option
                                                v-for="item in catalog"
                                                :key="item.public_id"
                                                :value="item.public_id"
                                                :disabled="
                                                    usedItems.has(
                                                        item.public_id,
                                                    ) &&
                                                    line.supplier_item !==
                                                        item.public_id
                                                "
                                            >
                                                {{ item.name }} —
                                                {{ peso(item.unit_price) }}
                                                / {{ item.unit ?? 'unit' }}
                                            </option>
                                        </select>
                                        <InputError
                                            :message="
                                                fieldError(
                                                    index,
                                                    'supplier_item',
                                                )
                                            "
                                        />
                                    </div>
                                    <div class="space-y-1.5">
                                        <label
                                            :for="`po-qty-${index}`"
                                            class="text-sm font-medium"
                                            >Quantity</label
                                        >
                                        <input
                                            :id="`po-qty-${index}`"
                                            v-model="line.quantity"
                                            type="number"
                                            inputmode="decimal"
                                            min="0.01"
                                            step="0.01"
                                            class="h-11 w-full rounded-md border border-input bg-background px-3 text-sm tabular-nums focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                        />
                                        <InputError
                                            :message="
                                                fieldError(index, 'quantity')
                                            "
                                        />
                                    </div>
                                    <div
                                        class="flex items-end justify-between gap-3 sm:justify-end"
                                    >
                                        <p
                                            class="pb-3 text-sm font-semibold tabular-nums"
                                        >
                                            {{ peso(lineTotal(line)) }}
                                        </p>
                                        <Button
                                            type="button"
                                            variant="destructive-outline"
                                            size="icon"
                                            class="h-11 w-11"
                                            :aria-label="`Remove line ${index + 1}`"
                                            @click="removeLine(index)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>

                                <!-- Where the stock goes when the order arrives -->
                                <div
                                    v-if="line.supplier_item"
                                    class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_200px]"
                                >
                                    <div class="space-y-1.5">
                                        <label
                                            :for="`po-inv-${index}`"
                                            class="text-sm font-medium"
                                            >Add to my inventory</label
                                        >
                                        <select
                                            :id="`po-inv-${index}`"
                                            v-model="line.inventory_id"
                                            :class="selectClass"
                                            @change="onPickInventory(line)"
                                        >
                                            <option value="">
                                                Don't add to stock
                                            </option>
                                            <option
                                                v-for="inv in inventory"
                                                :key="inv.id"
                                                :value="inv.id"
                                            >
                                                {{ inv.name }} ({{ inv.unit }})
                                            </option>
                                        </select>
                                        <InputError
                                            :message="
                                                fieldError(
                                                    index,
                                                    'inventory_id',
                                                )
                                            "
                                        />
                                    </div>
                                    <div
                                        v-if="line.inventory_id !== ''"
                                        class="space-y-1.5"
                                    >
                                        <label
                                            :for="`po-per-${index}`"
                                            class="text-sm font-medium"
                                            >Each
                                            {{
                                                catalogItem(line.supplier_item)
                                                    ?.unit ?? 'unit'
                                            }}
                                            adds</label
                                        >
                                        <div class="flex items-center gap-2">
                                            <input
                                                :id="`po-per-${index}`"
                                                v-model.number="
                                                    line.stock_per_unit
                                                "
                                                type="number"
                                                min="1"
                                                step="1"
                                                class="h-11 w-full rounded-md border border-input bg-background px-3 text-sm tabular-nums focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                            />
                                            <span
                                                class="shrink-0 text-sm text-muted-foreground"
                                                >{{
                                                    inventoryItem(
                                                        line.inventory_id,
                                                    )?.unit
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <Button
                                type="button"
                                variant="primary-outline"
                                :disabled="form.items.length >= catalog.length"
                                @click="addLine"
                            >
                                <Plus class="h-4 w-4" /> Add another supply
                            </Button>
                            <InputError :message="form.errors.items" />
                        </template>
                    </section>

                    <!-- Notes -->
                    <section v-if="supplier" class="space-y-3 border-t pt-8">
                        <label
                            for="po-notes"
                            class="text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                            >Note to supplier</label
                        >
                        <textarea
                            id="po-notes"
                            v-model="form.notes"
                            rows="3"
                            maxlength="1000"
                            placeholder="e.g. Please deliver before 10 AM."
                            class="w-full max-w-xl rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        />
                        <InputError :message="form.errors.notes" />
                    </section>
                </div>

                <!-- Summary -->
                <aside
                    class="h-fit rounded-[14px] border bg-card p-5 lg:sticky lg:top-6"
                    aria-label="Order summary"
                >
                    <p class="flex items-center gap-2 text-sm font-semibold">
                        <ClipboardList
                            class="h-4 w-4 text-muted-foreground"
                            aria-hidden="true"
                        />
                        Order summary
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        {{ supplier?.name ?? 'No supplier chosen' }}
                    </p>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-muted-foreground">Supplies</dt>
                            <dd class="font-medium tabular-nums">
                                {{ filledLines }}
                            </dd>
                        </div>
                    </dl>
                    <div
                        class="mt-3 flex items-baseline justify-between border-t border-dashed pt-3"
                    >
                        <span class="text-sm font-semibold">Total</span>
                        <span class="text-xl font-bold tabular-nums">{{
                            peso(total)
                        }}</span>
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">
                        At the supplier's current prices.
                    </p>

                    <Button
                        type="submit"
                        class="mt-5 min-h-11 w-full"
                        :disabled="form.processing || filledLines === 0"
                    >
                        <Loader2
                            v-if="form.processing && form.send"
                            class="h-4 w-4 animate-spin"
                        />
                        <Send v-else class="h-4 w-4" />
                        Send to supplier
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        class="mt-2 min-h-11 w-full"
                        :disabled="form.processing || filledLines === 0"
                        @click="submit(false)"
                    >
                        Save as draft
                    </Button>
                </aside>
            </form>
        </div>
    </ShopLayout>
</template>
