<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { PlusCircle, ShoppingBag, TrendingUp } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

/**
 * How an inventory item is charged to customers. Internal use is not here: it is set per service
 * (Operations → Services). `form` is the page's Inertia form; this component edits its add-on and
 * retail fields in place.
 */
interface SalesForm {
    addon_enabled: boolean;
    addon_name: string;
    addon_quantity: number | string;
    addon_price: number | string;
    retail_enabled: boolean;
    retail_name: string;
    retail_pack_size: number | string;
    retail_price: number | string;
    errors: Record<string, string | undefined>;
}

const props = defineProps<{
    form: SalesForm;
    /** The item's unit, e.g. ml. */
    unit: string;
    /** What one unit costs the shop, or null when unknown. */
    costPerUnit: number | null;
    /** The purchase unit (e.g. bottle) and how many `unit`s it holds; empty when bought in `unit`. */
    packageUnit?: string;
    packageSize?: number | string;
}>();

// The form is the page's Inertia form: this component edits its fields in place.
const f = props.form;

// A retail pack is counted in the purchase unit when the item has one (1 bottle), and saved in the
// item's own unit (1 bottle × 1,000 ml = 1,000 ml), which is what the order flow prices.
const containerSize = computed(() => Number(props.packageSize) || 0);
const retailInPurchaseUnit = computed(
    () => !!props.packageUnit && containerSize.value > 0,
);
const retailUnitLabel = computed(() =>
    retailInPurchaseUnit.value ? (props.packageUnit as string) : props.unit,
);
const retailCount = ref<number | string>(
    retailInPurchaseUnit.value && f.retail_pack_size !== ''
        ? Math.round(
              (Number(f.retail_pack_size) / containerSize.value) * 10000,
          ) / 10000
        : f.retail_pack_size,
);
watch([retailCount, containerSize, retailInPurchaseUnit], () => {
    f.retail_pack_size =
        retailCount.value === ''
            ? ''
            : retailInPurchaseUnit.value
              ? Math.max(
                    1,
                    Math.round(Number(retailCount.value) * containerSize.value),
                )
              : retailCount.value;
});

const peso = (value: number) =>
    `₱${value.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 4 })}`;

const addonProfit = computed(() => {
    const qty = Number(props.form.addon_quantity);
    const price = Number(props.form.addon_price);
    if (
        !props.form.addon_enabled ||
        props.costPerUnit === null ||
        !qty ||
        !price
    ) {
        return null;
    }
    return price - props.costPerUnit * qty;
});

const retailProfit = computed(() => {
    const size = Number(props.form.retail_pack_size);
    const price = Number(props.form.retail_price);
    if (
        !props.form.retail_enabled ||
        props.costPerUnit === null ||
        !size ||
        !price
    ) {
        return null;
    }
    return price - props.costPerUnit * size;
});

// Plain-language profit summaries shown under each option (no inputs): what the stock used costs,
// and, when the item is bought in containers, what a whole container earns once sold this way.
const hasContainer = computed(
    () => !!props.packageUnit && containerSize.value > 0,
);
const containerCost = computed(() =>
    props.costPerUnit !== null && hasContainer.value
        ? props.costPerUnit * containerSize.value
        : null,
);

function summarise(size: number, price: number) {
    if (props.costPerUnit === null || !size) return null;

    const stockCost = props.costPerUnit * size;
    const count = hasContainer.value
        ? Math.floor(containerSize.value / size)
        : 0;
    const revenue = count * price;

    return {
        stockCost,
        price,
        perSale: price - stockCost,
        count,
        revenue,
        net:
            containerCost.value === null ? null : revenue - containerCost.value,
    };
}

const retailSummary = computed(() =>
    summarise(
        Number(props.form.retail_pack_size),
        Number(props.form.retail_price) || 0,
    ),
);

const retailPerUnit = computed(() => {
    const size = Number(props.form.retail_pack_size);
    return size > 0 ? Number(props.form.retail_price) / size : null;
});
</script>

<template>
    <div class="space-y-4">
        <div class="grid gap-4 md:grid-cols-2">
            <!-- Additional charge -->
            <div
                class="rounded-xl border p-4 transition-colors"
                :class="
                    form.addon_enabled
                        ? 'border-violet-300 bg-violet-50/60 dark:border-violet-900 dark:bg-violet-950/20'
                        : 'bg-card'
                "
            >
                <label class="flex cursor-pointer items-start gap-3">
                    <input
                        v-model="f.addon_enabled"
                        type="checkbox"
                        class="mt-1 h-4 w-4 accent-primary"
                    />
                    <PlusCircle
                        class="mt-0.5 h-5 w-5 shrink-0 text-violet-600"
                        aria-hidden="true"
                    />
                    <span>
                        <span class="block text-sm font-semibold"
                            >Additional charge</span
                        >
                        <span class="block text-xs text-muted-foreground"
                            >Let customers request extra of this item on an
                            order.</span
                        >
                    </span>
                    <span
                        v-if="form.addon_enabled"
                        class="ml-auto h-fit shrink-0 rounded-md bg-violet-100 px-2 py-0.5 text-xs font-semibold text-violet-700 dark:bg-violet-950/40 dark:text-violet-300"
                        >Enabled</span
                    >
                </label>
                <div v-if="form.addon_enabled" class="mt-4 space-y-3">
                    <div class="space-y-1">
                        <label for="addon-name" class="text-sm font-medium"
                            >Add-on name
                            <span class="text-red-500">*</span></label
                        >
                        <Input
                            id="addon-name"
                            v-model="f.addon_name"
                            placeholder="e.g. Extra Fabric Conditioner"
                            class="bg-background"
                        />
                        <p
                            v-if="form.errors.addon_name"
                            class="text-xs text-red-500"
                        >
                            {{ form.errors.addon_name }}
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label for="addon-qty" class="text-sm font-medium"
                                >Quantity ({{ unit }})
                                <span class="text-red-500">*</span></label
                            >
                            <Input
                                id="addon-qty"
                                v-model="f.addon_quantity"
                                type="number"
                                min="1"
                                placeholder="30"
                                class="bg-background"
                            />
                            <p
                                v-if="form.errors.addon_quantity"
                                class="text-xs text-red-500"
                            >
                                {{ form.errors.addon_quantity }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <label for="addon-price" class="text-sm font-medium"
                                >Customer price (₱)
                                <span class="text-red-500">*</span></label
                            >
                            <Input
                                id="addon-price"
                                v-model="f.addon_price"
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="15.00"
                                class="bg-background"
                            />
                            <p
                                v-if="form.errors.addon_price"
                                class="text-xs text-red-500"
                            >
                                {{ form.errors.addon_price }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Retail product -->
            <div
                class="rounded-xl border p-4 transition-colors"
                :class="
                    form.retail_enabled
                        ? 'border-emerald-300 bg-emerald-50/60 dark:border-emerald-900 dark:bg-emerald-950/20'
                        : 'bg-card'
                "
            >
                <label class="flex cursor-pointer items-start gap-3">
                    <input
                        v-model="f.retail_enabled"
                        type="checkbox"
                        class="mt-1 h-4 w-4 accent-primary"
                    />
                    <ShoppingBag
                        class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600"
                        aria-hidden="true"
                    />
                    <span>
                        <span class="block text-sm font-semibold"
                            >Retail product</span
                        >
                        <span class="block text-xs text-muted-foreground"
                            >Sell this item to customers in a ready pack.</span
                        >
                    </span>
                    <span
                        v-if="form.retail_enabled"
                        class="ml-auto h-fit shrink-0 rounded-md bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300"
                        >Enabled</span
                    >
                </label>
                <div v-if="form.retail_enabled" class="mt-4 space-y-3">
                    <div class="space-y-1">
                        <label for="retail-name" class="text-sm font-medium"
                            >Product name
                            <span class="text-red-500">*</span></label
                        >
                        <Input
                            id="retail-name"
                            v-model="f.retail_name"
                            placeholder="e.g. Fabric Conditioner 1 L"
                            class="bg-background"
                        />
                        <p
                            v-if="form.errors.retail_name"
                            class="text-xs text-red-500"
                        >
                            {{ form.errors.retail_name }}
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label for="retail-size" class="text-sm font-medium"
                                >Pack size ({{ retailUnitLabel }})
                                <span class="text-red-500">*</span></label
                            >
                            <Input
                                id="retail-size"
                                v-model="retailCount"
                                type="number"
                                min="0"
                                :step="retailInPurchaseUnit ? 'any' : 1"
                                :placeholder="
                                    retailInPurchaseUnit ? '1' : '1000'
                                "
                                class="bg-background"
                            />
                            <p
                                v-if="form.errors.retail_pack_size"
                                class="text-xs text-red-500"
                            >
                                {{ form.errors.retail_pack_size }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <label
                                for="retail-price"
                                class="text-sm font-medium"
                                >Selling price (₱)
                                <span class="text-red-500">*</span></label
                            >
                            <Input
                                id="retail-price"
                                v-model="f.retail_price"
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="150.00"
                                class="bg-background"
                            />
                            <p
                                v-if="form.errors.retail_price"
                                class="text-xs text-red-500"
                            >
                                {{ form.errors.retail_price }}
                            </p>
                        </div>
                    </div>
                    <div
                        v-if="retailSummary"
                        class="space-y-0.5 text-xs text-muted-foreground"
                    >
                        <p>
                            The stock in this pack costs
                            {{ peso(retailSummary.stockCost) }}.
                        </p>
                        <template v-if="retailSummary.price > 0">
                            <p v-if="retailSummary.count > 0">
                                1 {{ packageUnit }} ({{
                                    containerSize.toLocaleString()
                                }}
                                {{ unit }}) gives {{ retailSummary.count }}
                                {{
                                    retailSummary.count === 1 ? 'pack' : 'packs'
                                }}
                                × {{ peso(retailSummary.price) }} =
                                {{ peso(retailSummary.revenue) }}.
                            </p>
                            <p
                                v-if="retailSummary.net !== null"
                                class="font-medium"
                                :class="
                                    retailSummary.net < 0
                                        ? 'text-red-600'
                                        : 'text-emerald-600'
                                "
                            >
                                Net profit per {{ packageUnit }}:
                                {{ peso(retailSummary.net) }} ({{
                                    packageUnit
                                }}
                                costs {{ peso(containerCost ?? 0) }}).
                            </p>
                            <p
                                v-else
                                class="font-medium"
                                :class="
                                    retailSummary.perSale < 0
                                        ? 'text-red-600'
                                        : 'text-emerald-600'
                                "
                            >
                                Net profit per pack:
                                {{ peso(retailSummary.perSale) }}.
                            </p>
                        </template>
                    </div>
                    <p
                        v-if="retailPerUnit !== null && retailPerUnit > 0"
                        class="text-xs text-muted-foreground"
                    >
                        <template v-if="retailInPurchaseUnit"
                            >{{ retailCount || 0 }} {{ packageUnit }} =
                            {{
                                Number(
                                    form.retail_pack_size || 0,
                                ).toLocaleString()
                            }}
                            {{ unit }}. </template
                        >Works out to {{ peso(retailPerUnit) }} per {{ unit }}.
                        <template v-if="form.addon_enabled"
                            >Orders use the add-on price while it is switched
                            on.</template
                        >
                    </p>
                </div>
            </div>
        </div>

        <!-- Pricing overview -->
        <div
            v-if="
                costPerUnit !== null ||
                addonProfit !== null ||
                retailProfit !== null
            "
            class="rounded-xl border bg-card p-4"
        >
            <p class="flex items-center gap-2 text-sm font-semibold">
                <TrendingUp
                    class="h-4 w-4 text-emerald-600"
                    aria-hidden="true"
                />
                Pricing overview
            </p>
            <dl class="mt-3 space-y-1.5 text-sm">
                <div v-if="costPerUnit !== null" class="flex justify-between">
                    <dt class="text-muted-foreground">Cost per {{ unit }}</dt>
                    <dd class="font-medium">{{ peso(costPerUnit) }}</dd>
                </div>
                <div v-if="addonProfit !== null" class="flex justify-between">
                    <dt class="text-muted-foreground">
                        Profit per add-on ({{ form.addon_quantity }} {{ unit }})
                    </dt>
                    <dd
                        class="font-medium"
                        :class="
                            addonProfit < 0
                                ? 'text-red-600'
                                : 'text-emerald-600'
                        "
                    >
                        {{ peso(addonProfit) }}
                    </dd>
                </div>
                <p
                    v-if="addonProfit !== null && addonProfit < 0"
                    class="text-xs text-red-600"
                >
                    The add-on sells below what its stock costs. Raise the
                    price.
                </p>
                <div v-if="retailProfit !== null" class="flex justify-between">
                    <dt class="text-muted-foreground">
                        Profit per retail pack ({{ retailCount }}
                        {{ retailUnitLabel }})
                    </dt>
                    <dd
                        class="font-medium"
                        :class="
                            retailProfit < 0
                                ? 'text-red-600'
                                : 'text-emerald-600'
                        "
                    >
                        {{ peso(retailProfit) }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</template>
