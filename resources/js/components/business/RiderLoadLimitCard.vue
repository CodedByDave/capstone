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

type VehicleLimit = { type: string; min: number | null; max: number | null };
type VehicleLimitRow = { type: string; min: string; max: string };

const props = defineProps<{
    limit: { min: number | null; max: number | null };
    vehicle: { selected: string; choices: string[]; limits?: VehicleLimit[] };
}>();

function toRow(type: string): VehicleLimitRow {
    const saved = props.vehicle.limits?.find((entry) => entry.type === type);
    const fallback = type === props.vehicle.selected ? props.limit : null;
    const min = saved ? saved.min : (fallback?.min ?? null);
    const max = saved ? saved.max : (fallback?.max ?? null);

    return {
        type,
        min: min !== null ? String(min) : '',
        max: max !== null ? String(max) : '',
    };
}

const form = useForm({
    rider_vehicle_type: props.vehicle.selected,
    rider_vehicle_types: [...props.vehicle.choices],
    rider_vehicle_limits: props.vehicle.choices.map(toRow),
});

/** The validation error for one vehicle row's field. */
function limitError(index: number, field: 'min' | 'max'): string | undefined {
    return (form.errors as Record<string, string | undefined>)[
        `rider_vehicle_limits.${index}.${field}`
    ];
}

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
    form.rider_vehicle_limits = [
        ...form.rider_vehicle_limits,
        { type: name, min: '', max: '' },
    ];
    closeAddVehicle();
}

function removeVehicle(name: string) {
    form.rider_vehicle_types = form.rider_vehicle_types.filter(
        (type) => type !== name,
    );
    form.rider_vehicle_limits = form.rider_vehicle_limits.filter(
        (row) => row.type !== name,
    );

    if (form.rider_vehicle_type === name) {
        form.rider_vehicle_type = DEFAULT_VEHICLE;
    }
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
                delivery, set for each vehicle type. The vehicle your riders use
                decides which limit applies. Both ends are allowed: with 3 to 15
                kg, a 15 kg order still goes with a rider. Customers outside the
                range are asked to drop off at the shop, and a delivery that
                turns out heavier after weighing is held on the Logistics board
                for staff to decide. Leave a field empty for no limit on that
                side.
            </p>
        </div>

        <div class="max-w-xl space-y-3">
            <div
                class="hidden grid-cols-[minmax(0,1fr)_7rem_7rem_2.75rem] gap-3 text-xs font-medium tracking-wide text-muted-foreground uppercase sm:grid"
                aria-hidden="true"
            >
                <span>Vehicle type</span>
                <span>Min (kg)</span>
                <span>Max (kg)</span>
                <span></span>
            </div>
            <div
                v-for="(row, index) in form.rider_vehicle_limits"
                :key="row.type"
                class="grid grid-cols-2 items-start gap-3 border-b pb-3 last:border-b-0 sm:grid-cols-[minmax(0,1fr)_7rem_7rem_2.75rem] sm:border-b-0 sm:pb-0"
            >
                <div
                    class="flex min-h-9 items-center gap-2 text-sm font-medium"
                >
                    <Bike class="h-4 w-4 shrink-0 text-muted-foreground" />
                    <span class="truncate">{{ row.type }}</span>
                    <span
                        v-if="row.type === form.rider_vehicle_type"
                        class="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary"
                    >
                        In use
                    </span>
                </div>
                <div class="flex justify-end sm:order-last">
                    <Button
                        v-if="row.type !== DEFAULT_VEHICLE"
                        type="button"
                        variant="destructive-outline"
                        size="icon"
                        :aria-label="`Remove ${row.type}`"
                        @click="removeVehicle(row.type)"
                    >
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>
                <div class="space-y-1">
                    <label
                        :for="`rider-min-${index}`"
                        class="text-xs text-muted-foreground sm:sr-only"
                    >
                        {{ row.type }} minimum (kg)
                    </label>
                    <Input
                        :id="`rider-min-${index}`"
                        v-model="row.min"
                        type="number"
                        min="0.5"
                        max="200"
                        step="0.5"
                        inputmode="decimal"
                        placeholder="No min"
                    />
                    <InputError :message="limitError(index, 'min')" />
                </div>
                <div class="space-y-1">
                    <label
                        :for="`rider-max-${index}`"
                        class="text-xs text-muted-foreground sm:sr-only"
                    >
                        {{ row.type }} maximum (kg)
                    </label>
                    <Input
                        :id="`rider-max-${index}`"
                        v-model="row.max"
                        type="number"
                        min="0.5"
                        max="200"
                        step="0.5"
                        inputmode="decimal"
                        placeholder="No max"
                    />
                    <InputError :message="limitError(index, 'max')" />
                </div>
            </div>
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
                        placeholder="e.g. Car"
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
                variant="primary-outline"
                :disabled="!canAddVehicle"
                @click="openAddVehicle"
            >
                <Plus class="h-4 w-4" />
                Add vehicle type
            </Button>
        </div>

        <div class="max-w-md space-y-2">
            <label
                id="rider-vehicle-label"
                class="flex items-center gap-2 text-sm font-medium"
            >
                <Bike class="h-4 w-4 text-muted-foreground" />
                Vehicle in use
            </label>
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
            <p class="text-sm text-muted-foreground">
                The vehicle your riders use. It applies to all of this shop's
                riders, and its load limit is the one customers and staff are
                held to.
            </p>
            <InputError :message="form.errors.rider_vehicle_type" />
        </div>
    </section>
</template>
