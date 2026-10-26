<script setup lang="ts">
import {
    createBaseMap,
    dotIcon,
    formatPeso,
    pinIcon,
    reverseGeocode,
    searchPlaces,
    type LatLng,
    type PlaceResult,
} from '@/lib/maps';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import L from 'leaflet';
import {
    Info,
    Loader,
    LocateFixed,
    MapPin,
    Route,
    Search,
    Truck,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { toast } from 'vue3-toastify';

interface FeeBreakdown {
    mode: 'fixed' | 'per_km';
    fee: number;
    base_fee: number;
    free_km: number;
    chargeable_km: number;
    rate_per_km: number;
    distance_fee: number;
    min_fee: number;
    min_applied: boolean;
}

interface Quote {
    distance_km: number | null;
    fee: number | null;
    breakdown: FeeBreakdown | null;
    available: boolean;
    message: string;
}

const props = defineProps<{
    paymentPublicId: string;
    initialAddress: string | null;
    shopName: string | null;
    shopLocation: LatLng | null;
}>();

const emit = defineEmits<{ cancel: [] }>();

// ─── Address, pin and search ─────────────────────────────────────────────────

const address = ref(props.initialAddress ?? '');
const pin = ref<LatLng | null>(null);
const searchQuery = ref('');
const suggestions = ref<PlaceResult[]>([]);
const searching = ref(false);
const locating = ref(false);
let searchTimer: ReturnType<typeof setTimeout> | null = null;

function onSearchInput() {
    if (searchTimer) clearTimeout(searchTimer);
    const query = searchQuery.value.trim();
    if (query.length < 3) {
        suggestions.value = [];
        return;
    }
    // Debounced; the Nominatim client also spaces requests at least 1 s apart.
    searchTimer = setTimeout(() => runSearch(query), 700);
}

async function runSearch(query: string) {
    searching.value = true;
    try {
        suggestions.value = await searchPlaces(query);
        if (suggestions.value.length === 0) {
            toast.info('No matching places found. Try a nearby landmark.');
        }
    } catch {
        toast.error(
            'Address search is unavailable right now. Drop the pin on the map instead.',
        );
    } finally {
        searching.value = false;
    }
}

function chooseSuggestion(place: PlaceResult) {
    suggestions.value = [];
    searchQuery.value = '';
    address.value = place.display_name;
    setPin({ lat: parseFloat(place.lat), lng: parseFloat(place.lon) }, false);
    map?.setView([pin.value!.lat, pin.value!.lng], 17);
}

function useMyLocation() {
    if (!navigator.geolocation) {
        toast.error('Location is not supported by this browser.');
        return;
    }
    locating.value = true;
    navigator.geolocation.getCurrentPosition(
        (position) => {
            locating.value = false;
            setPin({
                lat: position.coords.latitude,
                lng: position.coords.longitude,
            });
            map?.setView(
                [position.coords.latitude, position.coords.longitude],
                17,
            );
        },
        (error) => {
            locating.value = false;
            toast.error(
                error.code === error.PERMISSION_DENIED
                    ? 'Location permission was denied. Search for your address or tap the map instead.'
                    : 'Could not get your location. Search for your address or tap the map instead.',
            );
        },
        { enableHighAccuracy: true, timeout: 10_000 },
    );
}

// ─── Map ─────────────────────────────────────────────────────────────────────

const mapEl = ref<HTMLElement | null>(null);
let map: L.Map | null = null;
let pinMarker: L.Marker | null = null;

onMounted(() => {
    if (!mapEl.value) return;
    const center: L.LatLngTuple | undefined = props.shopLocation
        ? [props.shopLocation.lat, props.shopLocation.lng]
        : undefined;
    map = createBaseMap(mapEl.value, center, 14);

    if (props.shopLocation) {
        L.marker([props.shopLocation.lat, props.shopLocation.lng], {
            icon: dotIcon('#7c3aed'),
            keyboard: false,
        })
            .bindTooltip(props.shopName ?? 'Shop')
            .addTo(map);
    }

    map.on('click', (event: L.LeafletMouseEvent) => setPin(event.latlng));
});

onUnmounted(() => {
    if (searchTimer) clearTimeout(searchTimer);
    if (quoteTimer) clearTimeout(quoteTimer);
    map?.remove();
    map = null;
});

function setPin(point: LatLng, lookupAddress = true) {
    pin.value = { lat: point.lat, lng: point.lng };

    if (map) {
        if (pinMarker) {
            pinMarker.setLatLng([point.lat, point.lng]);
        } else {
            pinMarker = L.marker([point.lat, point.lng], {
                icon: pinIcon(),
                draggable: true,
                title: 'Delivery location',
            }).addTo(map);
            pinMarker.on('dragend', () => {
                const position = pinMarker!.getLatLng();
                setPin({ lat: position.lat, lng: position.lng });
            });
        }
    }

    if (lookupAddress) fillAddress(point);
    scheduleQuote();
}

async function fillAddress(point: LatLng) {
    try {
        const name = await reverseGeocode(point.lat, point.lng);
        // Ignore stale answers if the pin moved while waiting.
        if (
            name &&
            pin.value?.lat === point.lat &&
            pin.value?.lng === point.lng
        ) {
            address.value = name;
        }
    } catch {
        // The customer can still type the address; the pin is what prices the delivery.
    }
}

// ─── Quote ───────────────────────────────────────────────────────────────────

const quote = ref<Quote | null>(null);
const quoting = ref(false);
let quoteTimer: ReturnType<typeof setTimeout> | null = null;
let quoteRequest = 0;

function scheduleQuote() {
    if (quoteTimer) clearTimeout(quoteTimer);
    quoteTimer = setTimeout(fetchQuote, 400);
}

async function fetchQuote() {
    if (!pin.value) return;
    const requestId = ++quoteRequest;
    quoting.value = true;
    try {
        const { data } = await axios.post<Quote>(
            `/user/orders/${props.paymentPublicId}/delivery-quote`,
            {
                destination_lat: pin.value.lat,
                destination_lng: pin.value.lng,
            },
        );
        if (requestId !== quoteRequest) return;
        quote.value = data;
        if (!data.available) toast.warning(data.message);
    } catch (error) {
        if (requestId !== quoteRequest) return;
        quote.value = null;
        const status = axios.isAxiosError(error)
            ? error.response?.status
            : null;
        toast.error(
            status === 429
                ? 'Too many quote requests. Please wait a moment and move the pin again.'
                : 'Could not get a delivery quote. Please try again.',
        );
    } finally {
        if (requestId === quoteRequest) quoting.value = false;
    }
}

// ─── Submit ──────────────────────────────────────────────────────────────────

const submitting = ref(false);

const canSubmit = computed(
    () =>
        !!pin.value &&
        address.value.trim().length > 0 &&
        !quoting.value &&
        quote.value?.available === true &&
        !submitting.value,
);

function submit() {
    if (!canSubmit.value || !pin.value) return;
    submitting.value = true;
    router.post(
        `/user/orders/${props.paymentPublicId}/request-delivery`,
        {
            delivery_address: address.value.trim(),
            destination_lat: pin.value.lat,
            destination_lng: pin.value.lng,
        },
        {
            preserveScroll: true,
            onError: (errors) => {
                toast.error(
                    Object.values(errors)[0] ??
                        'Delivery could not be requested. Please try again.',
                );
            },
            onFinish: () => {
                submitting.value = false;
            },
        },
    );
}
</script>

<template>
    <form class="space-y-3" @submit.prevent="submit">
        <div
            v-if="!shopLocation"
            class="flex items-start gap-2 rounded-xl bg-amber-50 p-3 text-xs text-amber-700 dark:bg-amber-500/10 dark:text-amber-300"
        >
            <Info class="mt-0.5 h-3.5 w-3.5 shrink-0" />
            <span>
                This shop has not set its map location yet, so delivery fees
                cannot be quoted. Please contact the shop.
            </span>
        </div>

        <!-- Search -->
        <div class="relative">
            <Search
                class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
            />
            <input
                v-model="searchQuery"
                type="search"
                autocomplete="off"
                placeholder="Search your street, barangay or landmark"
                aria-label="Search delivery address"
                class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pr-9 pl-9 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                @input="onSearchInput"
            />
            <Loader
                v-if="searching"
                class="absolute top-1/2 right-3 h-4 w-4 -translate-y-1/2 animate-spin text-gray-400"
            />
            <ul
                v-if="suggestions.length"
                class="absolute z-[1000] mt-1 max-h-56 w-full overflow-auto rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900"
            >
                <li v-for="place in suggestions" :key="place.place_id">
                    <button
                        type="button"
                        class="flex w-full items-start gap-2 px-3 py-2 text-left text-xs text-gray-700 hover:bg-blue-50 dark:text-gray-200 dark:hover:bg-gray-800"
                        @click="chooseSuggestion(place)"
                    >
                        <MapPin
                            class="mt-0.5 h-3.5 w-3.5 shrink-0 text-gray-400"
                        />
                        <span>{{ place.display_name }}</span>
                    </button>
                </li>
            </ul>
        </div>

        <!-- Map -->
        <div
            class="isolate overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700"
        >
            <div
                ref="mapEl"
                class="h-64 w-full sm:h-72"
                role="application"
                aria-label="Delivery location map. Tap to drop a pin; drag the pin to adjust."
            />
        </div>
        <div class="flex items-center justify-between gap-2">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Tap the map or drag the pin to your exact door.
            </p>
            <button
                type="button"
                :disabled="locating"
                class="flex shrink-0 items-center gap-1.5 rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                @click="useMyLocation"
            >
                <Loader v-if="locating" class="h-3.5 w-3.5 animate-spin" />
                <LocateFixed v-else class="h-3.5 w-3.5" />
                Use my location
            </button>
        </div>

        <!-- Address text -->
        <div>
            <label
                for="delivery-address"
                class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300"
                >Delivery Address <span class="text-red-500">*</span></label
            >
            <textarea
                id="delivery-address"
                v-model="address"
                rows="2"
                maxlength="500"
                placeholder="House no., street, barangay, city — add landmarks for the rider"
                class="w-full resize-none rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
            />
        </div>

        <!-- Quote -->
        <div
            class="flex items-center justify-between gap-3 rounded-xl bg-gray-50 px-3 py-2.5 text-sm dark:bg-gray-800/60"
            aria-live="polite"
        >
            <div
                class="flex items-center gap-2 text-gray-600 dark:text-gray-300"
            >
                <Route class="h-4 w-4 shrink-0 text-gray-400" />
                <span v-if="!pin">Drop a pin to see the delivery fee</span>
                <span v-else-if="quoting">Calculating…</span>
                <span v-else-if="quote && quote.distance_km !== null">
                    {{ quote.distance_km.toFixed(1) }} km away
                </span>
                <span v-else>Fee unavailable</span>
            </div>
            <span
                v-if="quote?.available && !quoting"
                class="font-semibold text-gray-900 dark:text-gray-100"
            >
                {{ quote.fee ? formatPeso(quote.fee) : 'Free' }}
            </span>
            <span
                v-else-if="quote && !quoting"
                class="text-xs font-semibold text-red-600 dark:text-red-400"
                >Not available</span
            >
        </div>
        <dl
            v-if="
                quote?.available &&
                !quoting &&
                quote.breakdown?.mode === 'per_km'
            "
            class="space-y-1 rounded-xl border border-gray-100 px-3 py-2 text-xs text-gray-600 dark:border-gray-700 dark:text-gray-300"
        >
            <div class="flex justify-between gap-3">
                <dt>Base fee</dt>
                <dd>{{ formatPeso(quote.breakdown.base_fee) }}</dd>
            </div>
            <div class="flex justify-between gap-3">
                <dt>
                    {{ quote.breakdown.chargeable_km }} km ×
                    {{ formatPeso(quote.breakdown.rate_per_km) }}/km
                    <span v-if="quote.breakdown.free_km > 0">
                        (first {{ quote.breakdown.free_km }} km free)
                    </span>
                </dt>
                <dd>{{ formatPeso(quote.breakdown.distance_fee) }}</dd>
            </div>
            <div
                v-if="quote.breakdown.min_applied"
                class="flex justify-between gap-3"
            >
                <dt>Minimum delivery fee applied</dt>
                <dd>{{ formatPeso(quote.breakdown.min_fee) }}</dd>
            </div>
            <div
                class="flex justify-between gap-3 border-t pt-1 font-semibold text-gray-900 dark:text-gray-100"
            >
                <dt>Delivery fee</dt>
                <dd>{{ formatPeso(quote.breakdown.fee) }}</dd>
            </div>
        </dl>
        <p
            v-if="quote?.available && quote.fee && !quoting"
            class="text-xs text-gray-500 dark:text-gray-400"
        >
            The delivery fee is added to your order balance; pay it to the shop
            or rider.
        </p>

        <div class="flex gap-2">
            <button
                type="button"
                class="flex-1 rounded-xl border border-gray-200 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                @click="emit('cancel')"
            >
                Cancel
            </button>
            <button
                type="submit"
                :disabled="!canSubmit"
                class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-blue-600 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-50"
            >
                <Loader v-if="submitting" class="h-4 w-4 animate-spin" />
                <Truck v-else class="h-4 w-4" />
                {{ submitting ? 'Requesting…' : 'Confirm Request' }}
            </button>
        </div>
    </form>
</template>
