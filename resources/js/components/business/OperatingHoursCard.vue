<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import {
    patchSettingsForm,
    useSettingsSection,
} from '@/composables/useSettingsSaveBar';
import { useForm } from '@inertiajs/vue3';
import { Clock, Copy, Sun, Sunrise } from 'lucide-vue-next';
import { computed, watch } from 'vue';

const props = defineProps<{
    hours: {
        is_open_24_7: boolean;
        opens_at: string;
        closes_at: string;
        operating_days: number[];
        daily_hours: Record<number, { opens_at: string; closes_at: string }>;
    };
}>();

const form = useForm({
    is_open_24_7: props.hours.is_open_24_7,
    opens_at: props.hours.opens_at,
    closes_at: props.hours.closes_at,
    operating_days: [...props.hours.operating_days],
    daily_hours: Object.fromEntries(
        Object.entries(props.hours.daily_hours ?? {}).map(([d, h]) => [
            d,
            { ...h },
        ]),
    ) as Record<number, { opens_at: string; closes_at: string }>,
});

// Declared before the immediate watcher below, which reads it on setup.
const days = [
    { value: 1, label: 'Mon', full: 'Monday' },
    { value: 2, label: 'Tue', full: 'Tuesday' },
    { value: 3, label: 'Wed', full: 'Wednesday' },
    { value: 4, label: 'Thu', full: 'Thursday' },
    { value: 5, label: 'Fri', full: 'Friday' },
    { value: 6, label: 'Sat', full: 'Saturday' },
    { value: 0, label: 'Sun', full: 'Sunday' },
];

function hoursFor(day: number) {
    if (!form.daily_hours[day]) {
        form.daily_hours[day] = {
            opens_at: form.opens_at || '08:00',
            closes_at: form.closes_at || '17:00',
        };
    }
    return form.daily_hours[day];
}

// Days in week order that are currently open, each with its own times.
const openDayRows = computed(() =>
    days.filter((d) => form.operating_days.includes(d.value)),
);

// Keep the shop-wide fallback in step with the first open day.
watch(
    () => [form.operating_days, form.daily_hours],
    () => {
        const first = openDayRows.value[0];
        if (!first) return;
        const h = hoursFor(first.value);
        form.opens_at = h.opens_at;
        form.closes_at = h.closes_at;
    },
    { deep: true, immediate: true },
);

function copyToAllDays(from: number) {
    const src = hoursFor(from);
    for (const d of openDayRows.value) {
        form.daily_hours[d.value] = { ...src };
    }
}

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

            <div v-if="!form.is_open_24_7" class="max-w-2xl divide-y rounded-xl border">
                <p
                    v-if="openDayRows.length === 0"
                    class="p-3 text-sm text-muted-foreground"
                >
                    Choose the days you are open to set their hours.
                </p>
                <div
                    v-for="(day, index) in openDayRows"
                    :key="day.value"
                    class="flex flex-wrap items-center gap-x-4 gap-y-2 p-3"
                >
                    <span class="w-24 text-sm font-medium">{{ day.full }}</span>
                    <div class="flex items-center gap-2">
                        <label class="sr-only" :for="`opens-${day.value}`">
                            {{ day.full }} opens at
                        </label>
                        <Input
                            :id="`opens-${day.value}`"
                            v-model="hoursFor(day.value).opens_at"
                            type="time"
                            class="w-36"
                        />
                        <span class="text-sm text-muted-foreground">to</span>
                        <label class="sr-only" :for="`closes-${day.value}`">
                            {{ day.full }} closes at
                        </label>
                        <Input
                            :id="`closes-${day.value}`"
                            v-model="hoursFor(day.value).closes_at"
                            type="time"
                            class="w-36"
                        />
                    </div>
                    <button
                        v-if="index === 0 && openDayRows.length > 1"
                        type="button"
                        class="inline-flex min-h-11 items-center gap-1.5 rounded-lg px-2 text-xs font-medium text-primary hover:bg-primary/5 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        @click="copyToAllDays(day.value)"
                    >
                        <Copy class="h-3.5 w-3.5" aria-hidden="true" />
                        Apply to all days
                    </button>
                    <InputError
                        class="w-full"
                        :message="
                            (form.errors as Record<string, string>)[
                                `daily_hours.${day.value}.opens_at`
                            ] ||
                            (form.errors as Record<string, string>)[
                                `daily_hours.${day.value}.closes_at`
                            ]
                        "
                    />
                </div>
                <InputError
                    class="p-3"
                    :message="form.errors.opens_at || form.errors.closes_at"
                />
            </div>
        </div>
    </section>
</template>
