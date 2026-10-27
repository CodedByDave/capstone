<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useForm } from '@inertiajs/vue3';
import { Banknote, EyeOff, Loader2, Receipt, Wallet } from 'lucide-vue-next';
import { toast } from 'vue3-toastify';

const props = defineProps<{
    mode: 'none' | 'delivery_fee' | 'daily_rate';
    dailyRate: number;
}>();

const form = useForm({
    driver_pay_mode: props.mode,
    driver_daily_rate: props.dailyRate > 0 ? String(props.dailyRate) : '',
});

const modes = [
    {
        value: 'delivery_fee' as const,
        label: 'Delivery fee per run',
        hint: 'Drivers see the delivery fee of each order they complete',
        icon: Receipt,
    },
    {
        value: 'daily_rate' as const,
        label: 'Daily income',
        hint: 'A flat amount for every day a driver completes a run',
        icon: Banknote,
    },
    {
        value: 'none' as const,
        label: 'Hide earnings',
        hint: 'The driver app shows no money amounts',
        icon: EyeOff,
    },
];

function save() {
    form.patch('/shop/settings/driver-pay', {
        preserveScroll: true,
        preserveState: (page) =>
            Object.keys(page.props.errors ?? {}).length > 0,
        onError: () => toast.error('Driver earnings were not saved.'),
    });
}
</script>

<template>
    <section class="space-y-5">
        <div>
            <div class="flex items-center gap-2">
                <Wallet class="h-4 w-4 text-muted-foreground" />
                <h3 class="text-base font-semibold">Driver Earnings</h3>
            </div>
            <p class="mt-1 text-sm text-muted-foreground">
                Choose what drivers see as their earnings in the driver app.
            </p>
        </div>

        <div class="space-y-5">
            <div
                class="grid gap-2 sm:grid-cols-3"
                role="radiogroup"
                aria-label="Driver earnings mode"
            >
                <button
                    v-for="option in modes"
                    :key="option.value"
                    type="button"
                    role="radio"
                    :aria-checked="form.driver_pay_mode === option.value"
                    :class="[
                        'flex flex-col items-start gap-1 rounded-xl border p-3 text-left transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none',
                        form.driver_pay_mode === option.value
                            ? 'border-primary bg-primary/5'
                            : 'hover:border-primary/40',
                    ]"
                    @click="form.driver_pay_mode = option.value"
                >
                    <component :is="option.icon" class="h-4 w-4" />
                    <span class="text-sm font-medium">{{ option.label }}</span>
                    <span class="text-xs text-muted-foreground">{{
                        option.hint
                    }}</span>
                </button>
            </div>
            <InputError :message="form.errors.driver_pay_mode" />

            <div
                v-if="form.driver_pay_mode === 'daily_rate'"
                class="max-w-xs space-y-1.5"
            >
                <label class="text-sm font-medium" for="driver-daily-rate">
                    Daily rate (₱)
                    <span class="text-red-500" aria-hidden="true">*</span>
                </label>
                <Input
                    id="driver-daily-rate"
                    v-model="form.driver_daily_rate"
                    type="number"
                    min="1"
                    step="0.01"
                    placeholder="e.g. 500"
                />
                <InputError :message="form.errors.driver_daily_rate" />
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3">
                <p class="text-xs text-muted-foreground" aria-live="polite">
                    {{
                        form.isDirty
                            ? 'You have unsaved changes'
                            : 'No changes yet'
                    }}
                </p>
                <Button
                    :disabled="form.processing || !form.isDirty"
                    @click="save"
                >
                    <Loader2
                        v-if="form.processing"
                        class="mr-1.5 h-4 w-4 animate-spin"
                    />
                    Save
                </Button>
            </div>
        </div>
    </section>
</template>
