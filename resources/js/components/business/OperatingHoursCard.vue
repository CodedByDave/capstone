<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import {
    patchSettingsForm,
    useSettingsSection,
} from '@/composables/useSettingsSaveBar';
import { useForm } from '@inertiajs/vue3';
import { Clock, Sun, Sunrise } from 'lucide-vue-next';

const props = defineProps<{
    hours: {
        is_open_24_7: boolean;
        opens_at: string;
        closes_at: string;
        operating_days: number[];
    };
}>();

const form = useForm({
    is_open_24_7: props.hours.is_open_24_7,
    opens_at: props.hours.opens_at,
    closes_at: props.hours.closes_at,
    operating_days: [...props.hours.operating_days],
});

const days = [
    { value: 1, label: 'Mon', full: 'Monday' },
    { value: 2, label: 'Tue', full: 'Tuesday' },
    { value: 3, label: 'Wed', full: 'Wednesday' },
    { value: 4, label: 'Thu', full: 'Thursday' },
    { value: 5, label: 'Fri', full: 'Friday' },
    { value: 6, label: 'Sat', full: 'Saturday' },
    { value: 0, label: 'Sun', full: 'Sunday' },
];

function toggleDay(day: number) {
    form.operating_days = form.operating_days.includes(day)
        ? form.operating_days.filter((d) => d !== day)
        : [...form.operating_days, day];
}

const modes = [
    {
        value: true,
        label: 'Open 24/7',
        hint: 'Orders can be given to drivers at any hour',
        icon: Sun,
    },
    {
        value: false,
        label: 'Set opening hours',
        hint: 'Drivers get orders only between opening and closing time',
        icon: Sunrise,
    },
];

useSettingsSection({
    key: 'hours',
    label: 'Operating hours',
    isDirty: () => form.isDirty,
    save: () => patchSettingsForm(form, '/shop/settings/operating-hours'),
    discard: () => {
        form.reset();
        form.clearErrors();
    },
});
</script>

<template>
    <section class="space-y-5">
        <div>
            <div class="flex items-center gap-2">
                <Clock class="h-4 w-4 text-muted-foreground" />
                <h3 class="text-base font-semibold">Operating Hours</h3>
            </div>
            <p class="mt-1 text-sm text-muted-foreground">
                Choose the days and hours your shop is open. Customers cannot
                order while you are closed, and orders wait for a driver until
                you open again. A driver also needs a work schedule for the day.
            </p>
        </div>

        <div class="space-y-5">
            <div class="space-y-2">
                <p id="open-days-label" class="text-sm font-medium">
                    Days open
                    <span class="text-red-500" aria-hidden="true">*</span>
                </p>
                <div
                    class="flex flex-wrap gap-2"
                    role="group"
                    aria-labelledby="open-days-label"
                >
                    <button
                        v-for="day in days"
                        :key="day.value"
                        type="button"
                        :aria-pressed="form.operating_days.includes(day.value)"
                        :aria-label="day.full"
                        :class="[
                            'min-h-11 min-w-14 rounded-lg border px-3 text-sm font-medium transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none',
                            form.operating_days.includes(day.value)
                                ? 'border-primary bg-primary/5 text-primary'
                                : 'text-muted-foreground hover:border-primary/40',
                        ]"
                        @click="toggleDay(day.value)"
                    >
                        {{ day.label }}
                    </button>
                </div>
                <p class="text-xs text-muted-foreground">
                    Customers cannot place orders on days you are closed.
                </p>
                <InputError :message="form.errors.operating_days" />
            </div>

            <div
                class="grid gap-2 sm:grid-cols-2"
                role="radiogroup"
                aria-label="Operating hours mode"
            >
                <button
                    v-for="option in modes"
                    :key="String(option.value)"
                    type="button"
                    role="radio"
                    :aria-checked="form.is_open_24_7 === option.value"
                    :class="[
                        'flex flex-col items-start gap-1 rounded-xl border p-3 text-left transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none',
                        form.is_open_24_7 === option.value
                            ? 'border-primary bg-primary/5'
                            : 'hover:border-primary/40',
                    ]"
                    @click="form.is_open_24_7 = option.value"
                >
                    <component :is="option.icon" class="h-4 w-4" />
                    <span class="text-sm font-medium">{{ option.label }}</span>
                    <span class="text-xs text-muted-foreground">{{
                        option.hint
                    }}</span>
                </button>
            </div>

            <div
                v-if="!form.is_open_24_7"
                class="grid max-w-md gap-x-4 gap-y-5 sm:grid-cols-2"
            >
                <div class="space-y-1.5">
                    <label class="text-sm font-medium" for="opens-at">
                        Opens at
                        <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <Input id="opens-at" v-model="form.opens_at" type="time" />
                    <InputError :message="form.errors.opens_at" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium" for="closes-at">
                        Closes at
                        <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <Input
                        id="closes-at"
                        v-model="form.closes_at"
                        type="time"
                    />
                    <InputError :message="form.errors.closes_at" />
                </div>
            </div>
        </div>
    </section>
</template>
