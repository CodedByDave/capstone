<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    patchSettingsForm,
    useSettingsSection,
} from '@/composables/useSettingsSaveBar';
import { useForm } from '@inertiajs/vue3';
import { Bike, Plus, Trash2, Weight } from 'lucide-vue-next';
import { computed, nextTick, ref } from 'vue';

const DEFAULT_VEHICLE = 'Motorcycle';
const MAX_VEHICLE_TYPES = 10;

const props = defineProps<{
    limit: { min: number | null; max: number | null };
    vehicle: { selected: string; choices: string[] };
}>();

const form = useForm({
    rider_min_weight_kg:
        props.limit.min !== null ? String(props.limit.min) : '',
    rider_max_weight_kg:
        props.limit.max !== null ? String(props.limit.max) : '',
    rider_vehicle_type: props.vehicle.selected,
    rider_vehicle_types: [...props.vehicle.choices],
});

const addingVehicle = ref(false);
const newVehicle = ref('');
const newVehicleError = ref('');
const newVehicleField = ref<HTMLElement | null>(null);

const canAddVehicle = computed(
    () => form.rider_vehicle_types.length < MAX_VEHICLE_TYPES,
);

function openAddVehicle() {
    addingVehicle.value = true;
    nextTick(() => newVehicleField.value?.querySelector('input')?.focus());
}

function closeAddVehicle() {
    addingVehicle.value = false;
    newVehicle.value = '';
    newVehicleError.value = '';
}

function addVehicle() {
    const name = newVehicle.value.trim().replace(/\s+/g, ' ');

    if (!name) {
        newVehicleError.value = 'Enter a vehicle type.';
        return;
    }

    const taken = form.rider_vehicle_types.some(
        (type) => type.toLowerCase() === name.toLowerCase(),
    );

    if (taken) {
        newVehicleError.value = `${name} is already in the list.`;
        return;
    }

    form.rider_vehicle_types = [...form.rider_vehicle_types, name];
    form.rider_vehicle_type = name;
    closeAddVehicle();
}

function removeSelectedVehicle() {
    form.rider_vehicle_types = form.rider_vehicle_types.filter(
        (type) => type !== form.rider_vehicle_type,
    );
    form.rider_vehicle_type = DEFAULT_VEHICLE;
}

useSettingsSection({
    key: 'rider-load',
    label: 'Rider load limit',
    isDirty: () => form.isDirty,
    save: () => patchSettingsForm(form, '/shop/settings/rider-load'),
    discard: () => {
        form.reset();
        form.clearErrors();
        closeAddVehicle();
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

        <div class="max-w-md space-y-2">
            <label
                id="rider-vehicle-label"
                class="flex items-center gap-2 text-sm font-medium"
            >
                <Bike class="h-4 w-4 text-muted-foreground" />
                Vehicle type
            </label>
            <div class="flex items-center gap-2">
                <Select v-model="form.rider_vehicle_type">
                    <SelectTrigger
                        aria-labelledby="rider-vehicle-label"
                        class="w-full"
                    >
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="type in form.rider_vehicle_types"
                            :key="type"
                            :value="type"
                        >
                            {{ type }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Button
                    v-if="form.rider_vehicle_type !== DEFAULT_VEHICLE"
                    type="button"
                    variant="outline"
                    size="icon"
                    :aria-label="`Remove ${form.rider_vehicle_type}`"
                    @click="removeSelectedVehicle"
                >
                    <Trash2 class="h-4 w-4" />
                </Button>
            </div>
            <p class="text-sm text-muted-foreground">
                The vehicle your riders use. It applies to all of this shop's
                riders.
            </p>
            <InputError :message="form.errors.rider_vehicle_type" />
            <InputError :message="form.errors.rider_vehicle_types" />

            <div v-if="addingVehicle" ref="newVehicleField" class="space-y-2">
                <label for="rider-new-vehicle" class="text-sm font-medium">
                    New vehicle type
                </label>
                <div class="flex flex-wrap items-center gap-2">
                    <Input
                        id="rider-new-vehicle"
                        v-model="newVehicle"
                        class="min-w-40 flex-1"
                        maxlength="40"
                        placeholder="e.g. Tricycle"
                        @keydown.enter.prevent="addVehicle"
                        @keydown.esc.prevent="closeAddVehicle"
                    />
                    <Button type="button" @click="addVehicle">Add</Button>
                    <Button
                        type="button"
                        variant="ghost"
                        @click="closeAddVehicle"
                    >
                        Cancel
                    </Button>
                </div>
                <InputError :message="newVehicleError" />
            </div>
            <Button
                v-else
                type="button"
                variant="outline"
                :disabled="!canAddVehicle"
                @click="openAddVehicle"
            >
                <Plus class="h-4 w-4" />
                Add vehicle type
            </Button>
        </div>
    </section>
</template>
