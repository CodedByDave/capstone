<script setup lang="ts">
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { purchaseUnitsFor, usageUnitsFor } from '@/lib/inventoryUnits';
import {
    ArrowDown,
    ArrowRight,
    FlaskConical,
    Info,
    ShoppingCart,
} from 'lucide-vue-next';
import { computed } from 'vue';

interface Option {
    value: string;
    label: string;
}

// The three-step unit setup shared by the inventory create and edit pages:
// 1) how the item is consumed, 2) how it is bought, 3) the stock and cost.
const props = defineProps<{
    unitOptions: Option[];
    purchaseOptions: Option[];
    purchaseSelected: boolean;
    packageUnit: string;
    entryLabel: string;
    usesPackages: boolean;
    totalStock: number;
    costLabel: string;
    errors: Record<string, string | undefined>;
    stockReadonly?: boolean;
    stockHint?: string;
}>();

const unit = defineModel<string>('unit', { required: true });
const purchaseUnit = defineModel<string>('purchaseUnit', { required: true });
const packageSize = defineModel<string | number>('packageSize', {
    required: true,
});
const quantity = defineModel<string | number>('quantity', { required: true });
const cost = defineModel<string | number>('cost', { required: true });

// Each dropdown only offers what fits the other one's choice. The current value always stays
// listed so an older item with an unusual unit still displays correctly.
const visibleUnitOptions = computed(() => {
    const allowed = usageUnitsFor(purchaseUnit.value);

    return allowed
        ? props.unitOptions.filter(
              (option) =>
                  allowed.includes(option.value) || option.value === unit.value,
          )
        : props.unitOptions;
});

const visiblePurchaseOptions = computed(() => {
    const allowed = purchaseUnitsFor(unit.value);

    return allowed
        ? props.purchaseOptions.filter(
              (option) =>
                  option.value === 'none' ||
                  option.value === purchaseUnit.value ||
                  allowed.includes(option.value),
          )
        : props.purchaseOptions;
});

const chip =
    'inline-block rounded-md bg-blue-100 px-2 py-0.5 text-[11px] font-medium text-blue-700 dark:bg-blue-950/60 dark:text-blue-300';
const suffix =
    'flex min-w-10 shrink-0 items-center justify-center rounded-r-md border border-l-0 bg-muted px-2 text-sm text-muted-foreground';
</script>

<template>
    <div class="@container">
        <div
            class="grid items-stretch gap-2 @min-[34rem]:grid-cols-[minmax(0,1fr)_auto_minmax(0,1.25fr)_auto_minmax(0,1.25fr)]"
        >
            <!-- 1. Consumption unit -->
            <div
                class="space-y-3 rounded-xl border border-blue-200 bg-blue-50/60 p-3 dark:border-blue-900 dark:bg-blue-950/20"
            >
                <span :class="chip">1. Choose how it's consumed</span>
                <div class="space-y-1.5">
                    <label class="text-sm font-semibold"
                        >Consumption Unit
                        <span class="text-red-500">*</span></label
                    >
                    <Select v-model="unit">
                        <SelectTrigger
                            class="w-full min-w-0 bg-background"
                            :class="{ 'border-red-500': errors.unit }"
                        >
                            <span class="flex min-w-0 items-center gap-2">
                                <FlaskConical
                                    class="h-4 w-4 shrink-0 text-muted-foreground"
                                    aria-hidden="true"
                                />
                                <SelectValue placeholder="Select unit" />
                            </span>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="u in visibleUnitOptions"
                                :key="u.value"
                                :value="u.value"
                                >{{ u.label }}</SelectItem
                            >
                        </SelectContent>
                    </Select>
                    <p class="text-xs text-muted-foreground">
                        The smallest unit it is used up in.
                    </p>
                    <p v-if="errors.unit" class="text-xs text-red-500">
                        {{ errors.unit }}
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-center text-muted-foreground">
                <ArrowDown
                    class="h-5 w-5 @min-[34rem]:hidden"
                    aria-hidden="true"
                />
                <ArrowRight
                    class="hidden h-5 w-5 @min-[34rem]:block"
                    aria-hidden="true"
                />
            </div>

            <!-- 2. Purchase details -->
            <div class="@container space-y-3 rounded-xl border bg-card p-3">
                <span :class="chip">2. Set purchase details</span>
                <div class="grid gap-3 @min-[14rem]:grid-cols-2">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium"
                            >Stock Unit
                            <span class="text-red-500">*</span></label
                        >
                        <Select v-model="purchaseUnit">
                            <SelectTrigger
                                class="w-full min-w-0"
                                :class="{
                                    'border-red-500': errors.package_unit,
                                }"
                            >
                                <span class="flex min-w-0 items-center gap-2">
                                    <ShoppingCart
                                        class="h-4 w-4 shrink-0 text-muted-foreground"
                                        aria-hidden="true"
                                    />
                                    <SelectValue placeholder="Select unit" />
                                </span>
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="p in visiblePurchaseOptions"
                                    :key="p.value"
                                    :value="p.value"
                                    >{{ p.label }}</SelectItem
                                >
                            </SelectContent>
                        </Select>
                        <p class="text-xs text-muted-foreground">
                            How it is bought and stored. The cost price is
                            entered per purchase unit.
                        </p>
                        <p
                            v-if="errors.package_unit"
                            class="text-xs text-red-500"
                        >
                            {{ errors.package_unit }}
                        </p>
                    </div>
                    <div class="space-y-1.5">
                        <label
                            for="item-package-size"
                            class="text-sm font-medium"
                            >Container Size
                            <span class="text-red-500">*</span></label
                        >
                        <div class="flex">
                            <Input
                                id="item-package-size"
                                v-model="packageSize"
                                type="number"
                                min="1"
                                placeholder="5000"
                                class="rounded-r-none"
                                :disabled="!purchaseSelected"
                                :class="{
                                    'border-red-500': errors.package_size,
                                }"
                            />
                            <span :class="suffix">{{ unit }}</span>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            <template v-if="purchaseSelected"
                                >e.g. 5000 {{ unit }} per
                                {{ packageUnit }}.</template
                            >
                            <template v-else
                                >Choose the purchase unit first.</template
                            >
                        </p>
                        <p
                            v-if="errors.package_size"
                            class="text-xs text-red-500"
                        >
                            {{ errors.package_size }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-center text-muted-foreground">
                <ArrowDown
                    class="h-5 w-5 @min-[34rem]:hidden"
                    aria-hidden="true"
                />
                <ArrowRight
                    class="hidden h-5 w-5 @min-[34rem]:block"
                    aria-hidden="true"
                />
            </div>

            <!-- 3. Stock and cost -->
            <div class="@container space-y-3 rounded-xl border bg-card p-3">
                <span :class="chip">3. Set stock and cost</span>
                <div class="grid gap-3 @min-[14rem]:grid-cols-2">
                    <div class="space-y-1.5">
                        <label for="item-quantity" class="text-sm font-medium"
                            >Stock Quantity
                            <span v-if="!stockReadonly" class="text-red-500"
                                >*</span
                            ></label
                        >
                        <div class="flex">
                            <Input
                                id="item-quantity"
                                v-model="quantity"
                                type="number"
                                min="0"
                                :step="usesPackages ? 'any' : 1"
                                class="rounded-r-none"
                                :readonly="stockReadonly"
                                :disabled="stockReadonly"
                                :class="{ 'border-red-500': errors.quantity }"
                            />
                            <span :class="suffix">{{ entryLabel }}</span>
                        </div>
                        <p
                            v-if="stockHint"
                            class="text-xs text-muted-foreground"
                        >
                            {{ stockHint }}
                        </p>
                        <p v-if="errors.quantity" class="text-xs text-red-500">
                            {{ errors.quantity }}
                        </p>
                    </div>
                    <div class="space-y-1.5">
                        <label for="item-cost" class="text-sm font-medium">{{
                            costLabel
                        }}</label>
                        <Input
                            id="item-cost"
                            v-model="cost"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="0.00"
                            :class="{ 'border-red-500': errors.unit_price }"
                        />
                        <p
                            v-if="errors.unit_price"
                            class="text-xs text-red-500"
                        >
                            {{ errors.unit_price }}
                        </p>
                    </div>
                </div>
                <p
                    v-if="usesPackages"
                    class="inline-flex items-center gap-1.5 rounded-md bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950/40 dark:text-blue-300"
                >
                    <Info class="h-3.5 w-3.5" aria-hidden="true" />
                    Total stock: {{ props.totalStock.toLocaleString() }}
                    {{ unit }}
                </p>
            </div>
        </div>
    </div>
</template>
