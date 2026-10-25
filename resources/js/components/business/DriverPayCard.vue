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
    <Card>
        <CardHeader class="pb-3">
            <div class="flex items-center gap-2">
                <Wallet class="h-4 w-4 text-muted-foreground" />
                <CardTitle class="text-sm">Driver Earnings</CardTitle>
            </div>
            <CardDescription class="text-xs">
                Choose what drivers see as their earnings in the driver app.
            </CardDescription>
        </CardHeader>

        <CardContent class="space-y-4">
            <div class="grid gap-2 sm:grid-cols-3">
                <button
                    v-for="option in modes"
                    :key="option.value"
                    type="button"
                    :class="[
                        'flex flex-col items-start gap-1 rounded-xl border p-3 text-left transition-colors',
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
                <label class="text-xs font-medium" for="driver-daily-rate">
                    Daily rate (₱)
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

            <div class="flex justify-end">
                <Button :disabled="form.processing" @click="save">
                    <Loader2
                        v-if="form.processing"
                        class="mr-1.5 h-4 w-4 animate-spin"
                    />
                    Save
                </Button>
            </div>
        </CardContent>
    </Card>
</template>
