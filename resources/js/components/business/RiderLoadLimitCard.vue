<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import {
    patchSettingsForm,
    useSettingsSection,
} from '@/composables/useSettingsSaveBar';
import { useForm } from '@inertiajs/vue3';
import { Weight } from 'lucide-vue-next';

const props = defineProps<{
    limit: { min: number | null; max: number | null };
}>();

const form = useForm({
    rider_min_weight_kg:
        props.limit.min !== null ? String(props.limit.min) : '',
    rider_max_weight_kg:
        props.limit.max !== null ? String(props.limit.max) : '',
});

useSettingsSection({
    key: 'rider-load',
    label: 'Rider load limit',
    isDirty: () => form.isDirty,
    save: () => patchSettingsForm(form, '/shop/settings/rider-load'),
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
                <Weight class="h-4 w-4 text-muted-foreground" />
                <h3 class="text-base font-semibold">Rider Load Limit</h3>
            </div>
            <p class="mt-1 text-sm text-muted-foreground">
                The weight of laundry one rider can carry for a pickup or a
                delivery. Both ends are allowed: with 3 to 15 kg, a 15 kg order
                still goes with a rider. Customers outside the range are asked
                to drop off at the shop, and a delivery that turns out heavier
                after weighing is held on the Logistics board for staff to
                decide. Leave a field empty for no limit on that side.
            </p>
        </div>

        <div class="grid max-w-md gap-4 sm:grid-cols-2">
            <div class="space-y-2">
                <label for="rider-min-weight" class="text-sm font-medium">
                    Minimum (kg)
                </label>
                <Input
                    id="rider-min-weight"
                    v-model="form.rider_min_weight_kg"
                    type="number"
                    min="0.5"
                    max="200"
                    step="0.5"
                    inputmode="decimal"
                    placeholder="No minimum"
                />
                <InputError :message="form.errors.rider_min_weight_kg" />
            </div>
            <div class="space-y-2">
                <label for="rider-max-weight" class="text-sm font-medium">
                    Maximum (kg)
                </label>
                <Input
                    id="rider-max-weight"
                    v-model="form.rider_max_weight_kg"
                    type="number"
                    min="0.5"
                    max="200"
                    step="0.5"
                    inputmode="decimal"
                    placeholder="No maximum"
                />
                <InputError :message="form.errors.rider_max_weight_kg" />
            </div>
        </div>
    </section>
</template>
