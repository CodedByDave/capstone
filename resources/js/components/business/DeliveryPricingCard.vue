<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { formatPeso } from '@/lib/maps';
import { router, useForm } from '@inertiajs/vue3';
import { Banknote, Info, Loader2, MapPin, Route, Truck } from 'lucide-vue-next';
import { computed } from 'vue';
import { toast } from 'vue3-toastify';

export interface DeliveryPricing {
    offers_delivery: boolean;
    has_location: boolean;
    delivery_fee_mode: 'fixed' | 'per_km';
    delivery_fixed_fee: number;
    delivery_base_fee: number;
    delivery_per_km_rate: number;
    delivery_free_km: number;
    delivery_min_fee: number;
    delivery_max_km: number | null;
}

const props = defineProps<{ pricing: DeliveryPricing }>();

const form = useForm({
    delivery_fee_mode: props.pricing.delivery_fee_mode,
    delivery_fixed_fee: String(props.pricing.delivery_fixed_fee),
    delivery_base_fee: String(props.pricing.delivery_base_fee),
    delivery_per_km_rate: String(props.pricing.delivery_per_km_rate),
    delivery_free_km: String(props.pricing.delivery_free_km),
    delivery_min_fee: String(props.pricing.delivery_min_fee),
    delivery_max_km:
        props.pricing.delivery_max_km === null
            ? ''
            : String(props.pricing.delivery_max_km),
});

const modes = [
    {
        value: 'fixed' as const,
        label: 'Fixed rate',
        hint: 'Same fee for every address',
        icon: Banknote,
    },
    {
        value: 'per_km' as const,
        label: 'Per kilometer',
        hint: 'Fee grows with road distance',
        icon: Route,
    },
];

function num(value: string): number {
    const parsed = parseFloat(value);
    return Number.isFinite(parsed) && parsed > 0 ? parsed : 0;
}

/** Mirrors App\Services\DeliveryFeeService::calculate for the live preview. */
function feeFor(km: number): number | null {
    const maxKm =
        form.delivery_max_km === '' ? null : num(form.delivery_max_km);
    if (maxKm !== null && km > maxKm) return null;

    if (form.delivery_fee_mode === 'fixed') {
        return Math.round(num(form.delivery_fixed_fee) * 100) / 100;
    }

    const chargeable = Math.max(0, km - num(form.delivery_free_km));
    const fee =
        num(form.delivery_base_fee) +
        chargeable * num(form.delivery_per_km_rate);
    return Math.round(Math.max(num(form.delivery_min_fee), fee) * 100) / 100;
}

const preview = computed(() =>
    [1, 3, 5, 10].map((km) => ({ km, fee: feeFor(km) })),
);

function save() {
    form.patch('/shop/settings/delivery-pricing', {
        preserveScroll: true,
        // Keep typed values when validation fails; a successful save reloads the page,
        // which shows the flash toast.
        preserveState: (page) =>
            Object.keys(page.props.errors ?? {}).length > 0,
        onError: () => {
            toast.error(
                'Delivery pricing was not saved. Please check the highlighted fields.',
            );
        },
    });
}
</script>

<template>
    <Card>
        <CardHeader class="pb-3">
            <div class="flex items-center gap-2">
                <Truck class="h-4 w-4 text-muted-foreground" />
                <CardTitle class="text-sm">Delivery Pricing</CardTitle>
            </div>
            <CardDescription class="text-xs">
                Set how much customers pay when they request delivery. The fee
                is added to the order balance.
            </CardDescription>
        </CardHeader>

        <CardContent v-if="!pricing.offers_delivery" class="space-y-3">
            <div
                class="flex items-start gap-2 rounded-xl border border-dashed px-4 py-3 text-xs text-muted-foreground"
            >
                <Info class="mt-0.5 h-4 w-4 shrink-0" />
                <p>
                    Delivery is turned off for your shop. Enable customer
                    delivery in Operations Setup to configure pricing.
                </p>
            </div>
            <Button
                variant="outline"
                size="sm"
                @click="router.visit('/shop/setup')"
            >
                Review Operations Setup
            </Button>
        </CardContent>

        <CardContent v-else class="space-y-4">
            <div
                v-if="!pricing.has_location"
                class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300"
            >
                <MapPin class="mt-0.5 h-4 w-4 shrink-0" />
                <p>
                    Set your Shop Location (GPS) below. Customers cannot get a
                    delivery quote until your shop has map coordinates.
                </p>
            </div>

            <!-- Mode -->
            <div
                class="grid grid-cols-1 gap-2 sm:grid-cols-2"
                role="radiogroup"
                aria-label="Delivery fee mode"
            >
                <button
                    v-for="mode in modes"
                    :key="mode.value"
                    type="button"
                    role="radio"
                    :aria-checked="form.delivery_fee_mode === mode.value"
                    class="flex items-start gap-3 rounded-xl border-2 px-3 py-2.5 text-left transition"
                    :class="
                        form.delivery_fee_mode === mode.value
                            ? 'border-primary bg-primary/5'
                            : 'border-border hover:border-muted-foreground/40'
                    "
                    @click="form.delivery_fee_mode = mode.value"
                >
                    <component
                        :is="mode.icon"
                        class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground"
                    />
                    <span>
                        <span class="block text-sm font-medium">{{
                            mode.label
                        }}</span>
                        <span class="block text-xs text-muted-foreground">{{
                            mode.hint
                        }}</span>
                    </span>
                </button>
            </div>
            <InputError :message="form.errors.delivery_fee_mode" />

            <!-- Fields -->
            <div
                v-if="form.delivery_fee_mode === 'fixed'"
                class="grid grid-cols-1 gap-3 sm:grid-cols-2"
            >
                <div class="space-y-1.5">
                    <label for="delivery_fixed_fee" class="text-xs font-medium"
                        >Delivery fee (₱)</label
                    >
                    <Input
                        id="delivery_fixed_fee"
                        v-model="form.delivery_fixed_fee"
                        type="number"
                        min="0"
                        step="0.01"
                        inputmode="decimal"
                        class="h-9"
                    />
                    <InputError :message="form.errors.delivery_fixed_fee" />
                </div>
            </div>

            <div v-else class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <label for="delivery_base_fee" class="text-xs font-medium"
                        >Base fee (₱)</label
                    >
                    <Input
                        id="delivery_base_fee"
                        v-model="form.delivery_base_fee"
                        type="number"
                        min="0"
                        step="0.01"
                        inputmode="decimal"
                        class="h-9"
                    />
                    <InputError :message="form.errors.delivery_base_fee" />
                </div>
                <div class="space-y-1.5">
                    <label
                        for="delivery_per_km_rate"
                        class="text-xs font-medium"
                        >Rate per km (₱)</label
                    >
                    <Input
                        id="delivery_per_km_rate"
                        v-model="form.delivery_per_km_rate"
                        type="number"
                        min="0"
                        step="0.01"
                        inputmode="decimal"
                        class="h-9"
                    />
                    <InputError :message="form.errors.delivery_per_km_rate" />
                </div>
                <div class="space-y-1.5">
                    <label for="delivery_free_km" class="text-xs font-medium"
                        >Free kilometers</label
                    >
                    <Input
                        id="delivery_free_km"
                        v-model="form.delivery_free_km"
                        type="number"
                        min="0"
                        step="0.1"
                        inputmode="decimal"
                        class="h-9"
                    />
                    <p class="text-[11px] text-muted-foreground">
                        Covered by the base fee before the per-km rate applies.
                    </p>
                    <InputError :message="form.errors.delivery_free_km" />
                </div>
                <div class="space-y-1.5">
                    <label for="delivery_min_fee" class="text-xs font-medium"
                        >Minimum fee (₱)</label
                    >
                    <Input
                        id="delivery_min_fee"
                        v-model="form.delivery_min_fee"
                        type="number"
                        min="0"
                        step="0.01"
                        inputmode="decimal"
                        class="h-9"
                    />
                    <InputError :message="form.errors.delivery_min_fee" />
                </div>
            </div>

            <div class="space-y-1.5 sm:w-1/2 sm:pr-1.5">
                <label for="delivery_max_km" class="text-xs font-medium"
                    >Maximum delivery radius (km)</label
                >
                <Input
                    id="delivery_max_km"
                    v-model="form.delivery_max_km"
                    type="number"
                    min="0.5"
                    step="0.5"
                    inputmode="decimal"
                    placeholder="No limit"
                    class="h-9"
                />
                <p class="text-[11px] text-muted-foreground">
                    Measured by road distance. Leave empty for no limit.
                </p>
                <InputError :message="form.errors.delivery_max_km" />
            </div>

            <!-- Preview -->
            <div class="rounded-xl border bg-muted/30 p-3">
                <p class="mb-2 text-xs font-medium text-muted-foreground">
                    Example fees
                </p>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <div
                        v-for="row in preview"
                        :key="row.km"
                        class="rounded-lg border bg-background px-3 py-2"
                    >
                        <p class="text-[11px] text-muted-foreground">
                            {{ row.km }} km
                        </p>
                        <p
                            class="text-sm font-semibold"
                            :class="
                                row.fee === null ? 'text-muted-foreground' : ''
                            "
                        >
                            {{
                                row.fee === null
                                    ? 'Out of range'
                                    : row.fee === 0
                                      ? 'Free'
                                      : formatPeso(row.fee)
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <Button size="sm" :disabled="form.processing" @click="save">
                <Loader2
                    v-if="form.processing"
                    class="mr-1.5 h-3.5 w-3.5 animate-spin"
                />
                {{ form.processing ? 'Saving…' : 'Save Delivery Pricing' }}
            </Button>
        </CardContent>
    </Card>
</template>
