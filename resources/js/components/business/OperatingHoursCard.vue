<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useForm } from '@inertiajs/vue3';
import { Clock, Loader2, Sun, Sunrise } from 'lucide-vue-next';
import { toast } from 'vue3-toastify';

const props = defineProps<{
    hours: {
        is_open_24_7: boolean;
        opens_at: string;
        closes_at: string;
    };
}>();

const form = useForm({
    is_open_24_7: props.hours.is_open_24_7,
    opens_at: props.hours.opens_at,
    closes_at: props.hours.closes_at,
});

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

function save() {
    form.patch('/shop/settings/operating-hours', {
        preserveScroll: true,
        preserveState: (page) =>
            Object.keys(page.props.errors ?? {}).length > 0,
        onError: () => toast.error('Operating hours were not saved.'),
    });
}
</script>

<template>
    <section class="space-y-5">
        <div>
            <div class="flex items-center gap-2">
                <Clock class="h-4 w-4 text-muted-foreground" />
                <h3 class="text-base font-semibold">Operating Hours</h3>
            </div>
            <p class="mt-1 text-sm text-muted-foreground">
                Choose when your shop is open. Outside these hours, orders wait
                for a driver until you open again. A driver also needs a work
                schedule for the day.
            </p>
        </div>

        <div class="space-y-5">
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
