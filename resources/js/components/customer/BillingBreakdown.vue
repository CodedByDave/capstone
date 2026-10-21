<script setup lang="ts">
import { Info, ReceiptText } from 'lucide-vue-next';
import { computed } from 'vue';

export interface BillingBreakdown {
    is_estimate: boolean;
    service: {
        name: string;
        pricing_model: 'per_kg' | 'per_bundle';
        quantity: number;
        unit: 'kg' | 'bundle';
        rate: number;
        bundle_weight_kg: number | null;
        amount: number;
    };
    additional_charges: number;
    discount: number;
    total: number;
    amount_paid: number;
    balance_due: number;
}

const props = withDefaults(
    defineProps<{
        breakdown: BillingBreakdown;
        showPayment?: boolean;
    }>(),
    { showPayment: true },
);

function currency(value: number) {
    return `₱${value.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
}

const serviceDetail = computed(() => {
    const { service } = props.breakdown;
    const quantity = Number(service.quantity.toFixed(2));

    if (service.unit === 'kg') {
        return `${quantity} kg × ${currency(service.rate)}/kg`;
    }

    const size = service.bundle_weight_kg
        ? ` (${Number(service.bundle_weight_kg)} kg each)`
        : '';

    return `${quantity} ${quantity === 1 ? 'bundle' : 'bundles'} × ${currency(service.rate)}${size}`;
});
</script>

<template>
    <div class="rounded-xl border border-gray-100 bg-white">
        <div
            class="flex items-center justify-between border-b border-gray-100 px-4 py-3"
        >
            <div class="flex items-center gap-2">
                <ReceiptText class="h-4 w-4 text-gray-400" />
                <span class="text-sm font-semibold text-gray-700"
                    >Billing breakdown</span
                >
            </div>
            <span
                class="rounded-full px-2.5 py-1 text-xs font-semibold"
                :class="
                    breakdown.is_estimate
                        ? 'bg-amber-50 text-amber-700'
                        : 'bg-emerald-50 text-emerald-700'
                "
            >
                {{ breakdown.is_estimate ? 'Estimate' : 'Final' }}
            </span>
        </div>

        <div class="flex items-start justify-between gap-3 px-4 py-3 text-sm">
            <div>
                <p class="font-medium text-gray-800">
                    {{ breakdown.service.name }}
                </p>
                <p class="mt-0.5 text-xs text-gray-500">{{ serviceDetail }}</p>
            </div>
            <span class="font-semibold whitespace-nowrap text-gray-800">{{
                currency(breakdown.service.amount)
            }}</span>
        </div>

        <div
            v-if="breakdown.additional_charges > 0"
            class="flex items-center justify-between border-t border-gray-100 px-4 py-2.5 text-sm"
        >
            <span class="text-gray-500">Additional charges</span>
            <span class="font-medium text-gray-700">{{
                currency(breakdown.additional_charges)
            }}</span>
        </div>

        <div
            v-if="breakdown.discount > 0"
            class="flex items-center justify-between border-t border-gray-100 px-4 py-2.5 text-sm text-emerald-700"
        >
            <span>Discount</span>
            <span class="font-medium">-{{ currency(breakdown.discount) }}</span>
        </div>

        <div
            class="flex items-center justify-between border-t border-gray-200 bg-gray-50 px-4 py-3"
        >
            <span class="text-sm font-bold text-gray-900">{{
                breakdown.is_estimate ? 'Estimated total' : 'Total'
            }}</span>
            <span class="text-base font-bold text-gray-900">{{
                currency(breakdown.total)
            }}</span>
        </div>

        <template v-if="showPayment && breakdown.amount_paid > 0">
            <div
                class="flex items-center justify-between border-t border-gray-100 px-4 py-2.5 text-sm"
            >
                <span class="text-gray-500">Amount paid</span>
                <span class="font-semibold text-emerald-600">{{
                    currency(breakdown.amount_paid)
                }}</span>
            </div>
            <div
                class="flex items-center justify-between border-t border-gray-100 px-4 py-2.5 text-sm"
            >
                <span class="text-gray-500">Balance due</span>
                <span class="font-semibold text-gray-800">{{
                    currency(breakdown.balance_due)
                }}</span>
            </div>
        </template>

        <div
            v-if="breakdown.is_estimate"
            class="flex items-start gap-2 border-t border-gray-100 px-4 py-3 text-xs text-gray-500"
        >
            <Info class="mt-0.5 h-3.5 w-3.5 shrink-0" />
            <span
                >Based on your estimated weight. The shop weighs your laundry
                and confirms the final amount before payment.</span
            >
        </div>
    </div>
</template>
