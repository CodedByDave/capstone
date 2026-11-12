<script setup lang="ts">
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Building2,
    ExternalLink,
    IdCard,
    Truck,
    Upload,
    User,
    X,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

/**
 * Vehicle details for an employee whose position is Rider, shown inside the
 * employee create and edit forms. A shop vehicle needs only its type; a
 * rider's own vehicle also needs its plate number and their license photo.
 */
const props = defineProps<{
    vehicleTypes: string[];
    errors: Record<string, string>;
    /** Edit only: a license photo is already on file. */
    hasLicense?: boolean;
    /** Edit only: where the saved license photo can be opened. */
    licenseUrl?: string | null;
}>();

const vehicleOwnership = defineModel<'shop' | 'own'>('vehicleOwnership', {
    default: 'shop',
});
const vehicleType = defineModel<string>('vehicleType', { default: '' });

const ownershipOptions = [
    {
        value: 'shop',
        label: 'Shop vehicle',
        hint: 'Rides one of the shop’s vehicles.',
        icon: Building2,
    },
    {
        value: 'own',
        label: 'Own vehicle',
        hint: 'Needs plate number and license.',
        icon: User,
    },
] as const;

const ownVehicle = computed(() => vehicleOwnership.value === 'own');
const plateNumber = defineModel<string>('plateNumber', { default: '' });
const licensePhoto = defineModel<File | null>('licensePhoto', {
    default: null,
});

const fileInput = ref<HTMLInputElement | null>(null);
const previewUrl = ref<string | null>(null);
const localError = ref<string | null>(null);

const MAX_BYTES = 5 * 1024 * 1024;
const ACCEPTED = ['image/jpeg', 'image/png', 'image/webp'];

const licenseRequired = computed(() => !props.hasLicense);

watch(licensePhoto, (file) => {
    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = file ? URL.createObjectURL(file) : null;
});

onBeforeUnmount(() => {
    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
});

function onFileChange(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    localError.value = null;

    if (file && !ACCEPTED.includes(file.type)) {
        localError.value = 'The license must be a JPG, PNG or WebP photo.';
    } else if (file && file.size > MAX_BYTES) {
        localError.value = 'The license photo must be 5 MB or smaller.';
    } else {
        licensePhoto.value = file;
        return;
    }

    licensePhoto.value = null;
    if (fileInput.value) fileInput.value.value = '';
}

function clearPhoto() {
    licensePhoto.value = null;
    localError.value = null;
    if (fileInput.value) fileInput.value.value = '';
}
</script>

<template>
    <section class="border-t pt-8">
        <h3 class="flex items-center gap-2 text-base font-semibold">
            <Truck class="h-4 w-4 text-muted-foreground" aria-hidden="true" />
            Rider details
        </h3>
        <p class="mt-0.5 text-sm text-muted-foreground">
            The vehicle this rider uses for pickups and deliveries.
        </p>

        <fieldset class="mt-5">
            <legend class="text-sm font-medium">
                Whose vehicle? <span class="text-red-500">*</span>
            </legend>
            <div class="mt-1.5 grid gap-3 sm:grid-cols-2">
                <label
                    v-for="option in ownershipOptions"
                    :key="option.value"
                    class="flex min-h-11 cursor-pointer items-start gap-3 rounded-xl border p-3 transition-colors focus-within:ring-2 focus-within:ring-ring hover:bg-muted/50"
                    :class="
                        vehicleOwnership === option.value
                            ? 'border-primary bg-primary/5'
                            : ''
                    "
                >
                    <input
                        v-model="vehicleOwnership"
                        type="radio"
                        name="rider-vehicle-ownership"
                        :value="option.value"
                        class="mt-1 h-4 w-4 accent-primary"
                    />
                    <component
                        :is="option.icon"
                        class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground"
                        aria-hidden="true"
                    />
                    <span class="min-w-0">
                        <span class="block text-sm font-medium">{{
                            option.label
                        }}</span>
                        <span class="block text-xs text-muted-foreground">{{
                            option.hint
                        }}</span>
                    </span>
                </label>
            </div>
            <p
                v-if="errors.vehicle_ownership"
                class="mt-1.5 text-xs text-red-500"
            >
                {{ errors.vehicle_ownership }}
            </p>
        </fieldset>

        <div class="mt-5 grid gap-x-6 gap-y-5 sm:grid-cols-2">
            <div class="space-y-1.5">
                <label class="text-sm font-medium" for="rider-vehicle-type"
                    >Vehicle Type <span class="text-red-500">*</span></label
                >
                <Select v-model="vehicleType">
                    <SelectTrigger
                        id="rider-vehicle-type"
                        class="w-full"
                        :class="{ 'border-red-500': errors.vehicle_type }"
                    >
                        <SelectValue placeholder="Select vehicle type" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="type in vehicleTypes"
                            :key="type"
                            :value="type"
                            >{{ type }}</SelectItem
                        >
                    </SelectContent>
                </Select>
                <p v-if="errors.vehicle_type" class="text-xs text-red-500">
                    {{ errors.vehicle_type }}
                </p>
                <p v-else class="text-xs text-muted-foreground">
                    The choices come from the rider vehicle types in Settings.
                </p>
            </div>

            <div v-if="ownVehicle" class="space-y-1.5">
                <label class="text-sm font-medium" for="rider-plate-number"
                    >Plate Number <span class="text-red-500">*</span></label
                >
                <Input
                    id="rider-plate-number"
                    v-model="plateNumber"
                    maxlength="12"
                    placeholder="e.g. ABC 1234"
                    autocomplete="off"
                    class="uppercase"
                    :class="{ 'border-red-500': errors.plate_number }"
                />
                <p v-if="errors.plate_number" class="text-xs text-red-500">
                    {{ errors.plate_number }}
                </p>
            </div>

            <div v-if="ownVehicle" class="space-y-1.5 sm:col-span-2">
                <label class="text-sm font-medium" for="rider-license-photo"
                    >Driver's License Photo
                    <span v-if="licenseRequired" class="text-red-500"
                        >*</span
                    ></label
                >

                <div
                    class="flex flex-col gap-3 rounded-xl border border-dashed p-4 sm:flex-row sm:items-center"
                    :class="{
                        'border-red-500': errors.license_photo || localError,
                    }"
                >
                    <div
                        class="flex h-20 w-32 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-muted"
                    >
                        <img
                            v-if="previewUrl"
                            :src="previewUrl"
                            alt="Selected driver's license photo"
                            class="h-full w-full object-cover"
                        />
                        <IdCard
                            v-else
                            class="h-8 w-8 text-muted-foreground"
                            aria-hidden="true"
                        />
                    </div>

                    <div class="min-w-0 flex-1 space-y-1 text-sm">
                        <p v-if="licensePhoto" class="truncate font-medium">
                            {{ licensePhoto.name }}
                        </p>
                        <p v-else-if="hasLicense" class="font-medium">
                            A license photo is on file.
                        </p>
                        <p v-else class="font-medium">No photo selected</p>
                        <p class="text-xs text-muted-foreground">
                            JPG, PNG or WebP, up to 5 MB. Stored encrypted and
                            visible only to your shop.
                            <span v-if="hasLicense && !licensePhoto"
                                >Upload a new photo to replace it.</span
                            >
                        </p>
                        <a
                            v-if="hasLicense && licenseUrl && !licensePhoto"
                            :href="licenseUrl"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center gap-1 text-xs font-medium text-primary underline-offset-4 hover:underline"
                        >
                            <ExternalLink
                                class="h-3.5 w-3.5"
                                aria-hidden="true"
                            />
                            View current license
                        </a>
                    </div>

                    <div class="flex shrink-0 gap-2">
                        <label
                            for="rider-license-photo"
                            class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-md border bg-background px-3 text-sm font-medium focus-within:ring-2 focus-within:ring-ring hover:bg-muted"
                        >
                            <Upload class="h-4 w-4" aria-hidden="true" />
                            {{
                                licensePhoto || hasLicense
                                    ? 'Replace'
                                    : 'Upload'
                            }}
                            <input
                                id="rider-license-photo"
                                ref="fileInput"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                class="sr-only"
                                @change="onFileChange"
                            />
                        </label>
                        <button
                            v-if="licensePhoto"
                            type="button"
                            class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-md border hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                            aria-label="Remove selected license photo"
                            @click="clearPhoto"
                        >
                            <X class="h-4 w-4" aria-hidden="true" />
                        </button>
                    </div>
                </div>
                <p
                    v-if="localError || errors.license_photo"
                    class="text-xs text-red-500"
                >
                    {{ localError || errors.license_photo }}
                </p>
            </div>
        </div>
    </section>
</template>
