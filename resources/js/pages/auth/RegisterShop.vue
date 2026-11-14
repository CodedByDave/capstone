<script setup lang="ts">
import AuthPanelShell from '@/components/auth/AuthPanelShell.vue';
import {
    eyeButtonClass,
    googleButtonClass,
    inputClass,
    inputErrorClass,
    labelClass,
    primaryButtonClass,
    secondaryButtonClass,
    textLinkClass,
} from '@/components/auth/authStyles';
import GoogleIcon from '@/components/auth/GoogleIcon.vue';
import InputError from '@/components/InputError.vue';
import MapView from '@/components/map/MapView.vue';
import PhilippinePhoneInput from '@/components/PhilippinePhoneInput.vue';
import { Spinner } from '@/components/ui/spinner';
import { caviteLocations } from '@/constants/caviteLocations';
import { addMarker, pinElement, toLngLat } from '@/lib/mapbox';
import {
    reverseGeocodeDetails,
    searchPlaces,
    type PlaceResult,
} from '@/lib/maps';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Eye,
    EyeOff,
    MapPin,
    Search,
    XCircle,
} from 'lucide-vue-next';
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
const formEl = ref<HTMLFormElement | null>(null);

/** Moves focus to the first field of a freshly shown step (keyboard and screen readers). */
function focusFirstField() {
    formEl.value
        ?.querySelector<HTMLElement>(
            'input:not([type="checkbox"]):not([readonly]), select',
        )
        ?.focus({ preventScroll: true });
}
// Three form steps, then the email code on the verify page.
const PROGRESS_STEPS = 4;

const panelCopies = {
    1: {
        title: "Let's get your shop online",
        text: 'Start with a few details about the owner.',
    },
    2: {
        title: 'Help customers find you',
        text: 'Pin your shop on the map so nearby customers can book a pickup.',
    },
    3: {
        title: 'Almost done!',
        text: 'Set a strong password to keep your shop account safe.',
    },
} as const;

const panelCopy = computed(
    () => panelCopies[step.value as 1 | 2 | 3] ?? panelCopies[1],
);

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
        return {
            label: 'Weak',
            color: 'bg-red-500',
            text: 'text-red-600',
            width: 'w-1/4',
        };
    if (passed === 2)
        return {
            label: 'Fair',
            color: 'bg-orange-400',
            text: 'text-orange-600',
            width: 'w-2/4',
        };
    if (passed === 3)
        return {
            label: 'Good',
            color: 'bg-yellow-400',
            text: 'text-yellow-700',
            width: 'w-3/4',
        };
    return {
        label: 'Strong',
        color: 'bg-green-500',
        text: 'text-green-700',
        width: 'w-full',
    };
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
    <Head title="Register your shop" />

    <AuthPanelShell
        screen="shop"
        title="Register Laundry Shop"
        description="Create your shop account"
    >
        <!-- Step progress: three form steps, then email verification -->
        <div
            class="mt-4 flex items-center justify-center gap-1.5"
            role="progressbar"
            aria-label="Registration progress"
            :aria-valuenow="step"
            aria-valuemin="1"
            :aria-valuemax="PROGRESS_STEPS"
        >
            <span
                v-for="s in PROGRESS_STEPS"
                :key="s"
                :class="[
                    'h-1.5 rounded-full transition-all duration-300',
                    s === step
                        ? 'w-10 bg-blue-600'
                        : s < step
                          ? 'w-5 bg-blue-300'
                          : 'w-5 bg-slate-200',
                ]"
            />
        </div>

        <form
            ref="formEl"
            @submit.prevent="submit"
            class="mt-5 flex flex-col gap-5"
        >
            <Transition
                mode="out-in"
                enter-active-class="transition-opacity duration-150 motion-reduce:transition-none"
                leave-active-class="transition-opacity duration-100 motion-reduce:transition-none"
                enter-from-class="opacity-0"
                leave-to-class="opacity-0"
                @after-enter="focusFirstField"
            >
                <!-- STEP 1: Owner Info -->
                <div v-if="step === 1" key="owner" class="grid gap-4">
                    <h2 class="text-center text-base font-semibold">
                        Step 1 — Owner Info
                    </h2>

                    <div
                        v-if="isFromGoogle"
                        class="flex items-center gap-3 rounded-xl bg-blue-50 px-3 py-2.5 text-sm text-blue-800"
                    >
                        <img
                            v-if="props.googleUser?.avatar"
                            :src="props.googleUser.avatar"
                            alt=""
                            class="size-7 shrink-0 rounded-full object-cover"
                        />
                        <span>
                            Signed in with Google as
                            <span class="font-semibold">{{
                                props.googleUser!.email
                            }}</span
                            >. Complete your shop details below.
                        </span>
                    </div>

                    <div class="space-y-2">
                        <label for="shop-owner-name" :class="labelClass"
                            >Owner Name</label
                        >
                        <input
                            id="shop-owner-name"
                            v-model="form.name"
                            autocomplete="name"
                            placeholder="John Doe"
                            :class="[
                                inputClass,
                                errors.name ? inputErrorClass : '',
                            ]"
                            :data-error="errors.name ? '' : undefined"
                        />
                        <p v-if="errors.name" class="text-sm text-red-600">
                            {{ errors.name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label for="shop-email" :class="labelClass"
                            >Email</label
                        >
                        <input
                            id="shop-email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            placeholder="shop@email.com"
                            :readonly="isFromGoogle"
                            :class="[
                                inputClass,
                                errors.email || form.errors.email
                                    ? inputErrorClass
                                    : '',
                                isFromGoogle
                                    ? 'cursor-not-allowed text-slate-500'
                                    : '',
                            ]"
                            :data-error="errors.email ? '' : undefined"
                        />
                        <p v-if="isFromGoogle" class="text-xs text-slate-500">
                            Email is locked to your Google account.
                        </p>
                        <p v-if="errors.email" class="text-sm text-red-600">
                            {{ errors.email }}
                        </p>
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="space-y-2">
                        <label for="shop-phone" :class="labelClass"
                            >Phone</label
                        >
                        <PhilippinePhoneInput
                            id="shop-phone"
                            v-model="form.phone"
                            :class="[
                                inputClass,
                                errors.phone ? inputErrorClass : '',
                            ]"
                            :data-error="errors.phone ? '' : undefined"
                        />
                        <p v-if="errors.phone" class="text-sm text-red-600">
                            {{ errors.phone }}
                        </p>
                    </div>

                    <button
                        type="button"
                        :class="primaryButtonClass"
                        @click="nextStep"
                    >
                        Next
                    </button>

                    <!-- Google OAuth button — only show if NOT already from Google -->
                    <template v-if="!isFromGoogle">
                        <div class="flex items-center gap-4" aria-hidden="true">
                            <span class="h-px flex-1 bg-slate-200" />
                            <span class="text-sm text-slate-500">or</span>
                            <span class="h-px flex-1 bg-slate-200" />
                        </div>

                        <button
                            type="button"
                            :class="googleButtonClass"
                            @click="goToGoogle"
                        >
                            <GoogleIcon />
                            Continue with Google
                        </button>
                    </template>
                </div>

                <!-- STEP 2: Shop Info -->
                <div v-else-if="step === 2" key="shop" class="grid gap-4">
                    <h2 class="text-center text-base font-semibold">
                        Step 2 — Shop Info
                    </h2>

                    <div class="space-y-2">
                        <label for="shop-name" :class="labelClass"
                            >Business Name</label
                        >
                        <input
                            id="shop-name"
                            v-model="form.shop_name"
                            placeholder="Laundry Hub"
                            :class="[
                                inputClass,
                                errors.shop_name ? inputErrorClass : '',
                            ]"
                            :data-error="errors.shop_name ? '' : undefined"
                        />
                        <p v-if="errors.shop_name" class="text-sm text-red-600">
                            {{ errors.shop_name }}
                        </p>
                    </div>

                    <!-- Map Pin -->
                    <div class="space-y-1.5">
                        <span
                            :class="[labelClass, 'flex items-center gap-1.5']"
                        >
                            <MapPin
                                class="size-4 text-blue-600"
                                aria-hidden="true"
                            />
                            Pin Your Shop Location
                        </span>
                        <p class="text-xs text-slate-500">
                            Search for your shop address or click on the map to
                            drop a pin. You can drag the pin to adjust.
                        </p>

                        <div
                            :class="[
                                'relative w-full overflow-hidden rounded-xl border',
                                errors.pin
                                    ? 'border-red-500'
                                    : 'border-slate-200',
                            ]"
                        >
                            <div
                                id="map-search-wrapper"
                                class="absolute top-2 right-2 left-2 z-[1000]"
                            >
                                <div class="relative">
                                    <div
                                        class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white/95 px-3 py-2.5 shadow-md backdrop-blur-sm"
                                    >
                                        <Search
                                            v-if="!searchLoading"
                                            class="size-4 shrink-0 text-slate-500"
                                            aria-hidden="true"
                                        />
                                        <span
                                            v-else
                                            class="inline-block size-4 shrink-0 animate-spin rounded-full border-2 border-blue-600 border-t-transparent"
                                        />
                                        <input
                                            v-model="searchQuery"
                                            type="text"
                                            aria-label="Search for your shop location"
                                            placeholder="Search for your shop location…"
                                            class="flex-1 bg-transparent text-sm outline-none placeholder:text-slate-500"
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
                                            aria-label="Clear search"
                                            class="rounded text-slate-500 hover:text-slate-900 focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:outline-none"
                                            @click="
                                                searchQuery = '';
                                                searchResults = [];
                                                showSuggestions = false;
                                            "
                                        >
                                            <XCircle class="size-4" />
                                        </button>
                                    </div>

                                    <ul
                                        v-if="showSuggestions"
                                        class="absolute top-full mt-1 max-h-52 w-full overflow-hidden overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg"
                                    >
                                        <li
                                            v-for="result in searchResults"
                                            :key="`${result.lat},${result.lng}`"
                                            @click="selectSuggestion(result)"
                                            class="flex cursor-pointer items-start gap-2 px-3 py-2 text-xs transition-colors hover:bg-slate-100"
                                        >
                                            <MapPin
                                                class="mt-0.5 size-3.5 shrink-0 text-blue-600"
                                                aria-hidden="true"
                                            />
                                            <span class="line-clamp-2">{{
                                                result.label
                                            }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

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
                            class="flex items-center gap-1.5 text-xs text-slate-500"
                        >
                            <span
                                class="inline-block size-3 animate-spin rounded-full border-2 border-blue-600 border-t-transparent"
                            />
                            Getting address from pin…
                        </div>
                        <div
                            v-else-if="pinned && !pinError"
                            class="flex items-center gap-1.5 text-xs text-green-700"
                        >
                            <CheckCircle2 class="size-3.5" aria-hidden="true" />
                            Location pinned — address filled below.
                        </div>
                        <p v-if="pinError" class="text-xs text-red-600">
                            {{ pinError }}
                        </p>
                        <p v-if="errors.pin" class="text-xs text-red-600">
                            {{ errors.pin }}
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <label for="shop-street" :class="labelClass"
                                >Block / Street</label
                            >
                            <input
                                id="shop-street"
                                v-model="form.block_street"
                                placeholder="e.g. Block 5, St. 12"
                                :class="[
                                    inputClass,
                                    errors.block_street ? inputErrorClass : '',
                                ]"
                                :data-error="
                                    errors.block_street ? '' : undefined
                                "
                            />
                            <p
                                v-if="errors.block_street"
                                class="text-sm text-red-600"
                            >
                                {{ errors.block_street }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label for="shop-municipality" :class="labelClass"
                                >Municipality</label
                            >
                            <select
                                id="shop-municipality"
                                v-model="form.municipality"
                                :class="[
                                    inputClass,
                                    errors.municipality ? inputErrorClass : '',
                                ]"
                                :data-error="
                                    errors.municipality ? '' : undefined
                                "
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
                                class="text-sm text-red-600"
                            >
                                {{ errors.municipality }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label for="shop-barangay" :class="labelClass"
                                >Barangay</label
                            >
                            <input
                                v-if="form.municipality && !hasBarangayList"
                                id="shop-barangay"
                                v-model="form.barangay"
                                placeholder="Enter barangay"
                                :class="[
                                    inputClass,
                                    errors.barangay ? inputErrorClass : '',
                                ]"
                                :data-error="errors.barangay ? '' : undefined"
                            />
                            <select
                                v-else
                                id="shop-barangay"
                                v-model="form.barangay"
                                :class="[
                                    inputClass,
                                    errors.barangay ? inputErrorClass : '',
                                ]"
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
                            <p
                                v-if="errors.barangay"
                                class="text-sm text-red-600"
                            >
                                {{ errors.barangay }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label for="shop-postal" :class="labelClass"
                                >Postal Code</label
                            >
                            <input
                                id="shop-postal"
                                :value="autoPostalCode"
                                placeholder="Auto-filled"
                                readonly
                                :class="[inputClass, 'cursor-not-allowed']"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <button
                            type="button"
                            :class="secondaryButtonClass"
                            @click="prevStep"
                        >
                            Back
                        </button>
                        <button
                            type="button"
                            :class="primaryButtonClass"
                            @click="nextStep"
                        >
                            Next
                        </button>
                    </div>
                </div>

                <!-- STEP 3: Password + Terms -->
                <div v-else-if="step === 3" key="password" class="grid gap-4">
                    <h2 class="text-center text-base font-semibold">
                        Step 3 — Set Password
                    </h2>

                    <div class="space-y-2">
                        <label for="shop-password" :class="labelClass"
                            >Password</label
                        >
                        <div class="relative">
                            <input
                                id="shop-password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="new-password"
                                placeholder="Create a strong password"
                                :class="[
                                    inputClass,
                                    'pr-12',
                                    errors.password ? inputErrorClass : '',
                                ]"
                                :data-error="errors.password ? '' : undefined"
                            />
                            <button
                                type="button"
                                :class="eyeButtonClass"
                                :aria-label="
                                    showPassword
                                        ? 'Hide password'
                                        : 'Show password'
                                "
                                :aria-pressed="showPassword"
                                @click="showPassword = !showPassword"
                            >
                                <EyeOff v-if="showPassword" class="size-5" />
                                <Eye v-else class="size-5" />
                            </button>
                        </div>

                        <div v-if="form.password.length > 0" class="pt-1">
                            <div class="mb-1 flex items-center justify-between">
                                <span class="text-xs text-slate-500"
                                    >Password strength</span
                                >
                                <span
                                    class="text-xs font-semibold"
                                    :class="passwordStrength.text"
                                    >{{ passwordStrength.label }}</span
                                >
                            </div>
                            <div
                                class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100"
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

                        <ul class="space-y-1 pt-1">
                            <li
                                v-for="rule in passwordRules"
                                :key="rule.label"
                                class="flex items-center gap-1.5 text-xs"
                                :class="
                                    rule.pass
                                        ? 'text-green-700'
                                        : 'text-slate-500'
                                "
                            >
                                <CheckCircle2
                                    v-if="rule.pass"
                                    class="size-3.5 shrink-0"
                                    aria-hidden="true"
                                />
                                <XCircle
                                    v-else
                                    class="size-3.5 shrink-0"
                                    aria-hidden="true"
                                />
                                {{ rule.label }}
                            </li>
                        </ul>

                        <p v-if="errors.password" class="text-sm text-red-600">
                            {{ errors.password }}
                        </p>
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="space-y-2">
                        <label for="shop-password-confirm" :class="labelClass"
                            >Confirm Password</label
                        >
                        <div class="relative">
                            <input
                                id="shop-password-confirm"
                                v-model="form.password_confirmation"
                                :type="
                                    showConfirmPassword ? 'text' : 'password'
                                "
                                autocomplete="new-password"
                                placeholder="Re-enter your password"
                                :class="[
                                    inputClass,
                                    'pr-12',
                                    errors.password_confirmation
                                        ? inputErrorClass
                                        : '',
                                ]"
                                :data-error="
                                    errors.password_confirmation
                                        ? ''
                                        : undefined
                                "
                            />
                            <button
                                type="button"
                                :class="eyeButtonClass"
                                :aria-label="
                                    showConfirmPassword
                                        ? 'Hide password'
                                        : 'Show password'
                                "
                                :aria-pressed="showConfirmPassword"
                                @click="
                                    showConfirmPassword = !showConfirmPassword
                                "
                            >
                                <EyeOff
                                    v-if="showConfirmPassword"
                                    class="size-5"
                                />
                                <Eye v-else class="size-5" />
                            </button>
                        </div>

                        <p
                            v-if="form.password_confirmation.length > 0"
                            class="flex items-center gap-1 text-xs"
                            :class="
                                passwordsMatch
                                    ? 'text-green-700'
                                    : 'text-red-600'
                            "
                        >
                            <CheckCircle2
                                v-if="passwordsMatch"
                                class="size-3.5"
                                aria-hidden="true"
                            />
                            <XCircle
                                v-else
                                class="size-3.5"
                                aria-hidden="true"
                            />
                            {{
                                passwordsMatch
                                    ? 'Passwords match'
                                    : 'Passwords do not match'
                            }}
                        </p>
                        <p
                            v-if="errors.password_confirmation"
                            class="text-sm text-red-600"
                        >
                            {{ errors.password_confirmation }}
                        </p>
                    </div>

                    <div
                        class="rounded-xl border p-3 transition-colors"
                        :class="
                            errors.agree
                                ? 'border-red-400 bg-red-50'
                                : 'border-slate-200'
                        "
                        :data-error="errors.agree ? '' : undefined"
                    >
                        <label
                            class="flex cursor-pointer items-start gap-2.5 text-sm"
                        >
                            <input
                                type="checkbox"
                                :checked="form.agree"
                                @change="
                                    form.agree = (
                                        $event.target as HTMLInputElement
                                    ).checked
                                "
                                class="mt-0.5 size-4 shrink-0 accent-blue-600"
                            />
                            <span>
                                I have read and agree to the
                                <a
                                    href="#"
                                    :class="[textLinkClass, 'underline']"
                                    >Terms &amp; Conditions</a
                                >
                                and
                                <a
                                    href="#"
                                    :class="[textLinkClass, 'underline']"
                                    >Privacy Policy</a
                                >.
                            </span>
                        </label>
                        <p
                            v-if="errors.agree"
                            class="mt-1.5 ml-6 text-xs text-red-600"
                        >
                            {{ errors.agree }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        :class="primaryButtonClass"
                    >
                        <Spinner v-if="form.processing" />
                        Create Account
                    </button>

                    <button
                        type="button"
                        :class="secondaryButtonClass"
                        @click="prevStep"
                    >
                        Back
                    </button>
                </div>
            </Transition>

            <p class="text-center text-sm text-slate-500">
                Already have an account?
                <Link href="/login?intent=shop" :class="textLinkClass"
                    >Login</Link
                >
            </p>
        </form>

        <template #panel>
            <p class="text-xs font-bold tracking-widest uppercase">
                Step {{ step }} of {{ PROGRESS_STEPS }}
            </p>
            <h2
                class="mt-3 max-w-xs font-display text-3xl font-bold tracking-tight"
            >
                {{ panelCopy.title }}
            </h2>
            <p class="mt-3 max-w-[310px] text-[15px] leading-relaxed">
                {{ panelCopy.text }}
            </p>
        </template>
    </AuthPanelShell>
</template>
