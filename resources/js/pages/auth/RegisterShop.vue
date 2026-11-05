<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import MapView from '@/components/map/MapView.vue';
import PhilippinePhoneInput from '@/components/PhilippinePhoneInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { caviteLocations } from '@/constants/caviteLocations';
import AuthBase from '@/layouts/AuthLayout.vue';
import { addMarker, pinElement, toLngLat } from '@/lib/mapbox';
import {
    reverseGeocodeDetails,
    searchPlaces,
    type PlaceResult,
} from '@/lib/maps';
import { Head, router, useForm } from '@inertiajs/vue3';
import { CheckCircle2, Eye, EyeOff, MapPin, XCircle } from 'lucide-vue-next';
import type { Map as MapboxMap, Marker } from 'mapbox-gl';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

/* ---------------- PROPS (from GoogleAuthController) ---------------- */
const props = defineProps<{
    googleUser?: {
        google_id: string;
        name: string;
        email: string;
        avatar: string;
    } | null;
}>();

/* ---------------- STEP ---------------- */
const step = ref(1);
const TOTAL_STEPS = 3;

/* ---------------- FORM ---------------- */
const form = useForm({
    name: '',
    email: '',
    phone: '',

    shop_name: '',
    block_street: '',
    municipality: '',
    barangay: '',
    postal_code: '',
    latitude: '' as string | number,
    longitude: '' as string | number,

    agree: false,

    password: '',
    password_confirmation: '',

    google_id: '',
});

/* ---------------- PRE-FILL FROM GOOGLE ---------------- */
const isFromGoogle = computed(() => !!props.googleUser);

onMounted(() => {
    if (props.googleUser) {
        form.name = props.googleUser.name;
        form.email = props.googleUser.email;
        form.google_id = props.googleUser.google_id;
    }
});

/* ---------------- FIELD ERRORS (client-side) ---------------- */
const errors = ref<Record<string, string>>({});

/* ---------------- PASSWORD ---------------- */
const showPassword = ref(false);
const showConfirmPassword = ref(false);

const passwordRules = computed(() => [
    { label: 'At least 8 characters', pass: form.password.length >= 8 },
    { label: 'Contains a letter', pass: /[A-Za-z]/.test(form.password) },
    { label: 'Contains a number', pass: /\d/.test(form.password) },
    {
        label: 'Contains a special character',
        pass: /[^A-Za-z0-9]/.test(form.password),
    },
]);

const isPasswordValid = computed(() =>
    passwordRules.value.every((r) => r.pass),
);

const passwordsMatch = computed(
    () =>
        form.password === form.password_confirmation &&
        form.password_confirmation.length > 0,
);

const passwordStrength = computed(() => {
    const passed = passwordRules.value.filter((r) => r.pass).length;
    if (passed <= 1)
        return { label: 'Weak', color: 'bg-red-500', width: 'w-1/4' };
    if (passed === 2)
        return { label: 'Fair', color: 'bg-orange-400', width: 'w-2/4' };
    if (passed === 3)
        return { label: 'Good', color: 'bg-yellow-400', width: 'w-3/4' };
    return { label: 'Strong', color: 'bg-green-500', width: 'w-full' };
});

/* ---------------- MAP PIN ---------------- */
let mapInstance: MapboxMap | null = null;
let pinMarker: Marker | null = null;
const geocoding = ref(false);
const pinError = ref('');
const pinned = ref(false);

/* --- Search bar --- */
const searchQuery = ref('');
const searchResults = ref<PlaceResult[]>([]);
const searchLoading = ref(false);
const showSuggestions = ref(false);
let searchDebounce: ReturnType<typeof setTimeout> | null = null;

function onSearchInput() {
    if (searchDebounce) clearTimeout(searchDebounce);
    const q = searchQuery.value.trim();
    if (q.length < 3) {
        searchResults.value = [];
        showSuggestions.value = false;
        return;
    }
    searchDebounce = setTimeout(() => fetchSuggestions(q), 400);
}

async function fetchSuggestions(q: string) {
    searchLoading.value = true;
    try {
        searchResults.value = await searchPlaces(q, MAP_CENTER);
        showSuggestions.value = searchResults.value.length > 0;
    } catch {
        searchResults.value = [];
    } finally {
        searchLoading.value = false;
    }
}

function placePin(lat: number, lng: number) {
    if (!mapInstance) return;

    if (pinMarker) {
        pinMarker.setLngLat(toLngLat({ lat, lng }));
        return;
    }

    pinMarker = addMarker(
        mapInstance,
        pinElement(),
        { lat, lng },
        {
            anchor: 'bottom',
            draggable: true,
        },
    );
    pinMarker.on('dragend', async () => {
        const pos = pinMarker!.getLngLat();
        await reverseGeocode(pos.lat, pos.lng);
    });
}

async function selectSuggestion(result: PlaceResult) {
    showSuggestions.value = false;
    searchQuery.value = result.label;

    if (!mapInstance) return;

    mapInstance.easeTo({ center: toLngLat(result), zoom: 17 });
    placePin(result.lat, result.lng);

    await reverseGeocode(result.lat, result.lng);
}

function closeSearchOnOutsideClick(e: MouseEvent) {
    const searchEl = document.getElementById('map-search-wrapper');
    if (searchEl && !searchEl.contains(e.target as Node)) {
        showSuggestions.value = false;
    }
}

onMounted(() => document.addEventListener('click', closeSearchOnOutsideClick));
onUnmounted(() =>
    document.removeEventListener('click', closeSearchOnOutsideClick),
);

// Cavite province center as default view
const MAP_CENTER = { lat: 14.2456, lng: 120.8687 };

function onMapReady(map: MapboxMap) {
    mapInstance = map;

    map.on('click', async (e) => {
        const { lat, lng } = e.lngLat;

        placePin(lat, lng);
        await reverseGeocode(lat, lng);
    });
}

async function reverseGeocode(lat: number, lng: number) {
    geocoding.value = true;
    pinError.value = '';
    pinned.value = false;

    form.latitude = lat;
    form.longitude = lng;

    try {
        const details = await reverseGeocodeDetails(lat, lng);

        // Mapbox's `place` is the city or municipality (the province is a separate level).
        const rawCity = normalizePlace(details.place ?? '');

        const matchedMunicipality = rawCity
            ? municipalities.find((m) => normalizePlace(m) === rawCity)
            : undefined;

        if (matchedMunicipality) {
            form.municipality = matchedMunicipality;

            const rawBarangayName = String(
                details.neighborhood ?? details.locality ?? '',
            ).trim();
            const rawBarangay = rawBarangayName.toLowerCase();

            if (rawBarangay) {
                await nextTick();
                const barangays =
                    barangaysByMunicipality[matchedMunicipality] ?? [];

                if (barangays.length === 0) {
                    // No curated list for this town: use the name from the map.
                    form.barangay = rawBarangayName;
                } else {
                    const wanted = normalizeBarangay(rawBarangayName);
                    const matchedBarangay =
                        barangays.find(
                            (b) => normalizeBarangay(b.name) === wanted,
                        ) ??
                        barangays.find(
                            (b) =>
                                normalizeBarangay(b.name).includes(wanted) ||
                                wanted.includes(normalizeBarangay(b.name)),
                        );
                    if (matchedBarangay) form.barangay = matchedBarangay.name;
                }
            }
        } else {
            pinError.value =
                'We could not match this pin to a Cavite town. Pin inside Cavite or pick the municipality manually.';
        }

        const road = details.street ?? '';
        if (road) form.block_street = road;

        pinned.value = true;
    } catch {
        pinError.value =
            'Could not get address from pin. Please fill in manually.';
        pinned.value = true; // still allow submit
    } finally {
        geocoding.value = false;
    }
}

/* ---------------- MUNICIPALITIES / BARANGAYS ---------------- */
// Complete PSGC list of Cavite cities/municipalities and their barangays.
const barangaysByMunicipality: Record<
    string,
    Array<{ name: string; postal: string }>
> = Object.fromEntries(
    Object.entries(caviteLocations).map(([municipality, data]) => [
        municipality,
        data.barangays.map((name) => ({ name, postal: data.postal })),
    ]),
);

const postalByMunicipality: Record<string, string> = Object.fromEntries(
    Object.entries(caviteLocations).map(([municipality, data]) => [
        municipality,
        data.postal,
    ]),
);

/** Lower-case barangay name without "Barangay"/"Brgy" prefixes or "(Pob.)" suffixes. */
function normalizeBarangay(name: string): string {
    return name
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/\(.*?\)/g, '')
        .replace(/^(barangay|brgy\.?)\s+/, '')
        .replace(/\s+/g, ' ')
        .trim();
}

/** Lower-case, accent-free town name without a "City of" prefix, for matching map results. */
function normalizePlace(name: string): string {
    return name
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/^city of\s+/, '')
        .replace(/^gen\.\s*/, 'general ')
        .replace(/\s+city$/, '')
        .replace(/^cavite city$/, 'cavite')
        .trim();
}

const municipalities = Object.keys(barangaysByMunicipality).sort((a, b) =>
    normalizePlace(a).localeCompare(normalizePlace(b)),
);

const hasBarangayList = computed(
    () => (barangaysByMunicipality[form.municipality] ?? []).length > 0,
);

const selectedBarangays = computed(
    () => barangaysByMunicipality[form.municipality] ?? [],
);

const autoPostalCode = computed(() => {
    const found = selectedBarangays.value.find((b) => b.name === form.barangay);
    if (found) return found.postal;

    return form.barangay ? (postalByMunicipality[form.municipality] ?? '') : '';
});

watch(autoPostalCode, (val) => {
    form.postal_code = val;
});

watch(
    () => form.municipality,
    () => {
        form.barangay = '';
        form.postal_code = '';
    },
);

/* ---------------- VALIDATION per step ---------------- */
const validateStep1 = (): boolean => {
    const errs: Record<string, string> = {};
    if (form.name.trim().length <= 2)
        errs.name = 'Owner name must be more than 2 characters.';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email))
        errs.email = 'Please enter a valid email address.';
    if (!/^\+63-9\d{2}-\d{4}-\d{3}$/.test(form.phone))
        errs.phone =
            'Please enter a valid PH mobile number (e.g. +63-912-3456-789).';
    errors.value = errs;
    return Object.keys(errs).length === 0;
};

const validateStep2 = (): boolean => {
    const errs: Record<string, string> = {};
    if (form.shop_name.trim().length <= 2)
        errs.shop_name = 'Shop name must be more than 2 characters.';
    if (!form.latitude || !form.longitude)
        errs.pin = 'Please pin your shop location on the map.';
    if (!form.municipality) errs.municipality = 'Please select a municipality.';
    if (!form.barangay) errs.barangay = 'Please select a barangay.';
    errors.value = errs;
    return Object.keys(errs).length === 0;
};

const validateStep3 = (): boolean => {
    const errs: Record<string, string> = {};
    if (!isPasswordValid.value)
        errs.password = 'Password does not meet all requirements.';
    if (!passwordsMatch.value)
        errs.password_confirmation = 'Passwords do not match.';
    if (!form.agree)
        errs.agree =
            'You must agree to the Terms & Conditions before registering.';
    errors.value = errs;
    return Object.keys(errs).length === 0;
};

/* ---------------- NAV ---------------- */
const focusFirstError = () => {
    nextTick(() => {
        const el = document.querySelector('[data-error]') as HTMLElement | null;
        el?.focus();
        el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
};

const nextStep = () => {
    let valid = false;
    if (step.value === 1) valid = validateStep1();
    if (step.value === 2) valid = validateStep2();
    if (valid) {
        errors.value = {};
        step.value++;
    } else {
        focusFirstError();
    }
};

const prevStep = () => {
    errors.value = {};
    step.value--;
};

/* ---------------- SERVER ERROR → STEP REDIRECT ---------------- */
watch(
    () => form.errors,
    (serverErrors) => {
        if (!serverErrors || Object.keys(serverErrors).length === 0) return;

        if (serverErrors.name || serverErrors.email || serverErrors.phone) {
            step.value = 1;
            focusFirstError();
        } else if (
            serverErrors.shop_name ||
            serverErrors.municipality ||
            serverErrors.barangay
        ) {
            step.value = 2;
            focusFirstError();
        }
    },
    { deep: true },
);

/* ---------------- SUBMIT ---------------- */
const submit = () => {
    if (!validateStep3()) {
        focusFirstError();
        return;
    }

    form.post('/register/shop');
};

/* ---------------- GOOGLE OAuth ---------------- */
const goToGoogle = () => {
    window.location.href = '/auth/google';
};
</script>

<template>
    <AuthBase
        title="Register Laundry Shop"
        description="Create your shop account"
    >
        <Head title="Register" />

        <!-- Step progress dots -->
        <div class="mb-4 flex items-center justify-center gap-2">
            <template v-for="s in TOTAL_STEPS" :key="s">
                <div
                    :class="[
                        'h-2 rounded-full transition-all duration-300',
                        s === step
                            ? 'w-8 bg-primary'
                            : s < step
                              ? 'w-4 bg-primary/50'
                              : 'w-4 bg-muted',
                    ]"
                />
            </template>
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <!-- STEP 1: Owner Info -->
            <div v-if="step === 1" class="grid gap-4">
                <h2 class="text-center font-semibold">Step 1 — Owner Info</h2>

                <!-- Google banner if pre-filled -->
                <div
                    v-if="isFromGoogle"
                    class="flex items-center gap-3 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2.5 text-sm text-blue-700"
                >
                    <img
                        :src="props.googleUser!.avatar"
                        class="h-7 w-7 shrink-0 rounded-full object-cover"
                    />
                    <span>
                        Signed in with Google as
                        <span class="font-medium">{{
                            props.googleUser!.email
                        }}</span
                        >. Complete your shop details below.
                    </span>
                </div>

                <div>
                    <Label class="mb-2">Owner Name</Label>
                    <Input
                        v-model="form.name"
                        placeholder="John Doe"
                        :class="
                            errors.name
                                ? 'border-red-500 focus-visible:ring-red-500'
                                : ''
                        "
                        :data-error="errors.name ? '' : undefined"
                    />
                    <p v-if="errors.name" class="mt-1 text-xs text-red-500">
                        {{ errors.name }}
                    </p>
                </div>

                <div>
                    <Label class="mb-2">Email</Label>
                    <Input
                        v-model="form.email"
                        type="email"
                        placeholder="shop@email.com"
                        :readonly="isFromGoogle"
                        :class="[
                            errors.email || form.errors.email
                                ? 'border-red-500 focus-visible:ring-red-500'
                                : '',
                            isFromGoogle
                                ? 'cursor-not-allowed bg-muted/50'
                                : '',
                        ]"
                    />
                    <p
                        v-if="isFromGoogle"
                        class="mt-1 text-xs text-muted-foreground"
                    >
                        Email is locked to your Google account.
                    </p>
                    <p v-if="errors.email" class="mt-1 text-xs text-red-500">
                        {{ errors.email }}
                    </p>
                    <InputError :message="form.errors.email" />
                </div>

                <div>
                    <Label class="mb-2">Phone</Label>
                    <PhilippinePhoneInput
                        v-model="form.phone"
                        :class="
                            errors.phone
                                ? 'border-red-500 focus-visible:ring-red-500'
                                : ''
                        "
                        :data-error="errors.phone ? '' : undefined"
                    />
                    <p v-if="errors.phone" class="mt-1 text-xs text-red-500">
                        {{ errors.phone }}
                    </p>
                </div>

                <Button type="button" @click="nextStep" class="w-full"
                    >Next</Button
                >

                <!-- Google OAuth button — only show if NOT already from Google -->
                <template v-if="!isFromGoogle">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <span class="w-full border-t border-border" />
                        </div>
                        <div class="relative flex justify-center text-xs">
                            <span
                                class="bg-background px-2 text-muted-foreground"
                                >OR</span
                            >
                        </div>
                    </div>

                    <button
                        type="button"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-md border border-border bg-background px-4 py-2 text-sm font-medium text-foreground shadow-sm transition-colors hover:bg-muted"
                        @click="goToGoogle"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24">
                            <path
                                fill="#4285F4"
                                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                            />
                            <path
                                fill="#34A853"
                                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                            />
                            <path
                                fill="#FBBC05"
                                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"
                            />
                            <path
                                fill="#EA4335"
                                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                            />
                        </svg>
                        Continue with Google
                    </button>
                </template>
            </div>

            <!-- STEP 2: Shop Info -->
            <div v-if="step === 2" class="grid gap-4">
                <h2 class="text-center font-semibold">Step 2 — Shop Info</h2>

                <div>
                    <Label class="mb-2">Business Name</Label>
                    <Input
                        v-model="form.shop_name"
                        placeholder="Laundry Hub"
                        :class="
                            errors.shop_name
                                ? 'border-red-500 focus-visible:ring-red-500'
                                : ''
                        "
                        :data-error="errors.shop_name ? '' : undefined"
                    />
                    <p
                        v-if="errors.shop_name"
                        class="mt-1 text-xs text-red-500"
                    >
                        {{ errors.shop_name }}
                    </p>
                </div>

                <!-- Map Pin -->
                <div class="space-y-1.5">
                    <Label class="mb-1 flex items-center gap-1.5">
                        <MapPin class="h-3.5 w-3.5 text-primary" />
                        Pin Your Shop Location
                    </Label>
                    <p class="text-xs text-muted-foreground">
                        Search for your shop address or click on the map to drop
                        a pin. You can drag the pin to adjust.
                    </p>

                    <!-- Map wrapper with search bar overlaid -->
                    <div
                        :class="[
                            'relative w-full overflow-hidden rounded-lg border',
                            errors.pin ? 'border-red-500' : 'border-border',
                        ]"
                    >
                        <!-- Search bar overlay -->
                        <div
                            id="map-search-wrapper"
                            class="absolute top-2 right-2 left-2 z-[1000]"
                        >
                            <div class="relative">
                                <div
                                    class="flex items-center gap-2 rounded-lg border border-border bg-background/95 px-3 py-2 shadow-md backdrop-blur-sm"
                                >
                                    <svg
                                        v-if="!searchLoading"
                                        class="h-4 w-4 shrink-0 text-muted-foreground"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <circle cx="11" cy="11" r="8" />
                                        <path d="m21 21-4.35-4.35" />
                                    </svg>
                                    <span
                                        v-else
                                        class="inline-block h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-primary border-t-transparent"
                                    />
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Search for your shop location…"
                                        class="flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground"
                                        @input="onSearchInput"
                                        @focus="
                                            showSuggestions =
                                                searchResults.length > 0
                                        "
                                        autocomplete="off"
                                    />
                                    <button
                                        v-if="searchQuery"
                                        type="button"
                                        @click="
                                            searchQuery = '';
                                            searchResults = [];
                                            showSuggestions = false;
                                        "
                                        class="text-muted-foreground hover:text-foreground"
                                    >
                                        <XCircle class="h-4 w-4" />
                                    </button>
                                </div>

                                <!-- Suggestions dropdown -->
                                <ul
                                    v-if="showSuggestions"
                                    class="absolute top-full mt-1 max-h-52 w-full overflow-hidden overflow-y-auto rounded-lg border border-border bg-background shadow-lg"
                                >
                                    <li
                                        v-for="result in searchResults"
                                        :key="`${result.lat},${result.lng}`"
                                        @click="selectSuggestion(result)"
                                        class="flex cursor-pointer items-start gap-2 px-3 py-2 text-xs transition-colors hover:bg-muted"
                                    >
                                        <MapPin
                                            class="mt-0.5 h-3.5 w-3.5 shrink-0 text-primary"
                                        />
                                        <span class="line-clamp-2">{{
                                            result.label
                                        }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Map canvas -->
                        <MapView
                            height-class="h-72 w-full"
                            :center="MAP_CENTER"
                            :zoom="12"
                            :pitch="0"
                            :bearing="0"
                            @ready="onMapReady"
                        />
                    </div>

                    <div
                        v-if="geocoding"
                        class="flex items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <span
                            class="inline-block h-3 w-3 animate-spin rounded-full border-2 border-primary border-t-transparent"
                        />
                        Getting address from pin…
                    </div>
                    <div
                        v-else-if="pinned && !pinError"
                        class="flex items-center gap-1.5 text-xs text-green-600"
                    >
                        <CheckCircle2 class="h-3.5 w-3.5" />
                        Location pinned — address filled below.
                    </div>
                    <p v-if="pinError" class="text-xs text-red-500">
                        {{ pinError }}
                    </p>
                    <p v-if="errors.pin" class="text-xs text-red-500">
                        {{ errors.pin }}
                    </p>
                </div>

                <div>
                    <Label class="mb-2">Block / Street</Label>
                    <Input
                        v-model="form.block_street"
                        placeholder="e.g. Block 5, St. 12"
                        :class="
                            errors.block_street
                                ? 'border-red-500 focus-visible:ring-red-500'
                                : ''
                        "
                        :data-error="errors.block_street ? '' : undefined"
                    />
                    <p
                        v-if="errors.block_street"
                        class="mt-1 text-xs text-red-500"
                    >
                        {{ errors.block_street }}
                    </p>
                </div>

                <div>
                    <Label class="mb-2">Municipality</Label>
                    <select
                        v-model="form.municipality"
                        class="w-full rounded border p-2 text-sm"
                        :class="errors.municipality ? 'border-red-500' : ''"
                        :data-error="errors.municipality ? '' : undefined"
                    >
                        <option value="">Select municipality</option>
                        <option
                            v-for="mun in municipalities"
                            :key="mun"
                            :value="mun"
                        >
                            {{ mun }}
                        </option>
                    </select>
                    <p
                        v-if="errors.municipality"
                        class="mt-1 text-xs text-red-500"
                    >
                        {{ errors.municipality }}
                    </p>
                </div>

                <div>
                    <Label class="mb-2">Barangay</Label>
                    <Input
                        v-if="form.municipality && !hasBarangayList"
                        v-model="form.barangay"
                        placeholder="Enter barangay"
                        :class="errors.barangay ? 'border-red-500' : ''"
                        :data-error="errors.barangay ? '' : undefined"
                    />
                    <select
                        v-else
                        v-model="form.barangay"
                        class="w-full rounded border p-2 text-sm"
                        :class="errors.barangay ? 'border-red-500' : ''"
                        :disabled="!form.municipality"
                        :data-error="errors.barangay ? '' : undefined"
                    >
                        <option value="">Select barangay</option>
                        <option
                            v-for="b in selectedBarangays"
                            :key="b.name"
                            :value="b.name"
                        >
                            {{ b.name }}
                        </option>
                    </select>
                    <p v-if="errors.barangay" class="mt-1 text-xs text-red-500">
                        {{ errors.barangay }}
                    </p>
                </div>

                <div>
                    <Label class="mb-2">Postal Code</Label>
                    <Input
                        :value="autoPostalCode"
                        placeholder="Auto-filled on barangay select"
                        readonly
                        class="cursor-not-allowed bg-muted/50"
                    />
                </div>

                <Button type="button" @click="nextStep" class="w-full"
                    >Next</Button
                >
                <Button
                    type="button"
                    variant="outline"
                    @click="prevStep"
                    class="w-full"
                    >Back</Button
                >
            </div>

            <!-- STEP 3: Password + Terms -->
            <div v-if="step === 3" class="grid gap-4">
                <h2 class="text-center font-semibold">Step 3 — Set Password</h2>

                <!-- Password -->
                <div>
                    <Label class="mb-2">Password</Label>
                    <div class="relative">
                        <Input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            class="pr-10"
                            :class="
                                errors.password
                                    ? 'border-red-500 focus-visible:ring-red-500'
                                    : ''
                            "
                            :data-error="errors.password ? '' : undefined"
                            placeholder="Create a strong password"
                        />
                        <button
                            type="button"
                            class="absolute top-1/2 right-3 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                            @click="showPassword = !showPassword"
                        >
                            <Eye v-if="!showPassword" class="h-4 w-4" />
                            <EyeOff v-else class="h-4 w-4" />
                        </button>
                    </div>

                    <!-- Strength bar -->
                    <div v-if="form.password.length > 0" class="mt-2">
                        <div class="mb-1 flex items-center justify-between">
                            <span class="text-xs text-muted-foreground"
                                >Password strength</span
                            >
                            <span
                                class="text-xs font-medium"
                                :class="{
                                    'text-red-500':
                                        passwordStrength.label === 'Weak',
                                    'text-orange-400':
                                        passwordStrength.label === 'Fair',
                                    'text-yellow-500':
                                        passwordStrength.label === 'Good',
                                    'text-green-500':
                                        passwordStrength.label === 'Strong',
                                }"
                                >{{ passwordStrength.label }}</span
                            >
                        </div>
                        <div
                            class="h-1.5 w-full overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                :class="[
                                    'h-full rounded-full transition-all duration-300',
                                    passwordStrength.color,
                                    passwordStrength.width,
                                ]"
                            />
                        </div>
                    </div>

                    <!-- Requirements checklist -->
                    <ul class="mt-2 space-y-1">
                        <li
                            v-for="rule in passwordRules"
                            :key="rule.label"
                            class="flex items-center gap-1.5 text-xs"
                            :class="
                                rule.pass
                                    ? 'text-green-600'
                                    : 'text-muted-foreground'
                            "
                        >
                            <CheckCircle2
                                v-if="rule.pass"
                                class="h-3.5 w-3.5 shrink-0 text-green-500"
                            />
                            <XCircle
                                v-else
                                class="h-3.5 w-3.5 shrink-0 text-muted-foreground/50"
                            />
                            {{ rule.label }}
                        </li>
                    </ul>

                    <p v-if="errors.password" class="mt-1 text-xs text-red-500">
                        {{ errors.password }}
                    </p>
                    <InputError :message="form.errors.password" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <Label class="mb-2">Confirm Password</Label>
                    <div class="relative">
                        <Input
                            v-model="form.password_confirmation"
                            :type="showConfirmPassword ? 'text' : 'password'"
                            class="pr-10"
                            :class="
                                errors.password_confirmation
                                    ? 'border-red-500 focus-visible:ring-red-500'
                                    : ''
                            "
                            :data-error="
                                errors.password_confirmation ? '' : undefined
                            "
                            placeholder="Re-enter your password"
                        />
                        <button
                            type="button"
                            class="absolute top-1/2 right-3 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                            @click="showConfirmPassword = !showConfirmPassword"
                        >
                            <Eye v-if="!showConfirmPassword" class="h-4 w-4" />
                            <EyeOff v-else class="h-4 w-4" />
                        </button>
                    </div>

                    <!-- Match indicator -->
                    <p
                        v-if="form.password_confirmation.length > 0"
                        class="mt-1 flex items-center gap-1 text-xs"
                        :class="
                            passwordsMatch ? 'text-green-600' : 'text-red-500'
                        "
                    >
                        <CheckCircle2
                            v-if="passwordsMatch"
                            class="h-3.5 w-3.5"
                        />
                        <XCircle v-else class="h-3.5 w-3.5" />
                        {{
                            passwordsMatch
                                ? 'Passwords match'
                                : 'Passwords do not match'
                        }}
                    </p>
                    <p
                        v-if="errors.password_confirmation"
                        class="mt-1 text-xs text-red-500"
                    >
                        {{ errors.password_confirmation }}
                    </p>
                </div>

                <!-- Terms & Conditions -->
                <div
                    class="rounded-lg border p-3 transition-colors"
                    :class="
                        errors.agree
                            ? 'border-red-400 bg-red-50'
                            : 'border-muted'
                    "
                    :data-error="errors.agree ? '' : undefined"
                >
                    <label
                        class="flex cursor-pointer items-start gap-2 text-sm"
                    >
                        <input
                            type="checkbox"
                            :checked="form.agree"
                            @change="
                                form.agree = (
                                    $event.target as HTMLInputElement
                                ).checked
                            "
                            class="mt-0.5 accent-primary"
                        />
                        <span>
                            I have read and agree to the
                            <a
                                href="#"
                                class="font-medium text-primary underline hover:text-primary/80"
                                >Terms &amp; Conditions</a
                            >
                            and
                            <a
                                href="#"
                                class="font-medium text-primary underline hover:text-primary/80"
                                >Privacy Policy</a
                            >.
                        </span>
                    </label>
                    <p
                        v-if="errors.agree"
                        class="mt-1.5 ml-5 text-xs text-red-500"
                    >
                        {{ errors.agree }}
                    </p>
                </div>

                <Button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full"
                >
                    <Spinner v-if="form.processing" class="mr-2" />
                    Create Account
                </Button>

                <Button
                    type="button"
                    variant="outline"
                    @click="prevStep"
                    class="w-full"
                    >Back</Button
                >
            </div>

            <!-- Login link -->
            <div class="text-center text-sm">
                Already have an account?
                <button
                    type="button"
                    @click="router.visit('/login?intent=shop')"
                    class="text-primary underline hover:text-primary/80"
                >
                    Login
                </button>
            </div>
        </form>
    </AuthBase>
</template>
