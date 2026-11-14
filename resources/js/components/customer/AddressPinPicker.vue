<script setup lang="ts">
import MapView from '@/components/map/MapView.vue';
import { addMarker, DEFAULT_CENTER, pinElement, toLngLat } from '@/lib/mapbox';
import {
    reverseGeocode,
    searchPlaces,
    type LatLng,
    type PlaceResult,
} from '@/lib/maps';
import { Loader2, LocateFixed, MapPin, Search, X } from 'lucide-vue-next';
import type { Map as MapboxMap, Marker } from 'mapbox-gl';
import { onBeforeUnmount, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

/**
 * A map with a search bar for pinning an exact location. Tap the map, drag the pin,
 * search a place, or use the device location. `modelValue` is the pinned point and
 * `address` fires with a readable address for the point, so the form can fill itself in.
 */
const props = defineProps<{ modelValue: LatLng | null }>();

const emit = defineEmits<{
    'update:modelValue': [point: LatLng | null];
    address: [address: string];
}>();

let map: MapboxMap | null = null;
let marker: Marker | null = null;

const query = ref('');
const suggestions = ref<PlaceResult[]>([]);
const searching = ref(false);
const locating = ref(false);
const looking = ref(false);
let searchTimer: ReturnType<typeof setTimeout> | null = null;
let lookupId = 0;

const startCenter = props.modelValue ?? DEFAULT_CENTER;
const startZoom = props.modelValue ? 17 : 12;

function onMapReady(created: MapboxMap) {
    map = created;
    created.on('click', (event) =>
        setPin({ lat: event.lngLat.lat, lng: event.lngLat.lng }),
    );
    if (props.modelValue) placeMarker(props.modelValue);
}

function placeMarker(point: LatLng) {
    if (!map) return;

    if (marker) {
        marker.setLngLat(toLngLat(point));
        return;
    }

    const element = pinElement();
    element.title = 'Your exact location';
    marker = addMarker(map, element, point, {
        anchor: 'bottom',
        draggable: true,
    });
    marker.on('dragend', () => {
        const position = marker!.getLngLat();
        setPin({ lat: position.lat, lng: position.lng });
    });
}

function setPin(point: LatLng, options: { lookup?: boolean } = {}) {
    placeMarker(point);
    emit('update:modelValue', { lat: point.lat, lng: point.lng });
    if (options.lookup !== false) lookupAddress(point);
}

async function lookupAddress(point: LatLng) {
    const id = ++lookupId;
    looking.value = true;
    try {
        const name = await reverseGeocode(point.lat, point.lng);
        // Ignore the answer if the pin moved while waiting.
        if (id === lookupId && name) emit('address', name);
    } catch {
        // The customer can still type the address; the pin is what matters.
    } finally {
        if (id === lookupId) looking.value = false;
    }
}

function fly(point: LatLng) {
    map?.easeTo({ center: toLngLat(point), zoom: 17 });
}

// ─── Search ───────────────────────────────────────────────────────────────────

function onSearchInput() {
    if (searchTimer) clearTimeout(searchTimer);
    const text = query.value.trim();

    if (text.length < 3) {
        suggestions.value = [];
        return;
    }

    searchTimer = setTimeout(async () => {
        searching.value = true;
        try {
            suggestions.value = await searchPlaces(text, startCenter);
            if (suggestions.value.length === 0) {
                toast.info('No matching places found. Try a nearby landmark.');
            }
        } catch {
            suggestions.value = [];
            toast.error(
                'Search is unavailable right now. Tap the map to drop the pin.',
            );
        } finally {
            searching.value = false;
        }
    }, 450);
}

function choose(place: PlaceResult) {
    suggestions.value = [];
    query.value = '';
    const point = { lat: Number(place.lat), lng: Number(place.lng) };
    // The search result already carries a readable address.
    lookupId++;
    looking.value = false;
    setPin(point, { lookup: false });
    emit('address', place.label);
    fly(point);
}

function clearSearch() {
    query.value = '';
    suggestions.value = [];
}

// ─── Device location ──────────────────────────────────────────────────────────

function useMyLocation() {
    if (!navigator.geolocation) {
        toast.error('Location is not supported by this browser.');
        return;
    }

    locating.value = true;
    navigator.geolocation.getCurrentPosition(
        (position) => {
            locating.value = false;
            const point = {
                lat: position.coords.latitude,
                lng: position.coords.longitude,
            };
            setPin(point);
            fly(point);
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

// The parent can clear the pin (a fresh form) or set one (editing an address).
watch(
    () => props.modelValue,
    (point) => {
        if (!point) {
            marker?.remove();
            marker = null;
            return;
        }
        placeMarker(point);
    },
);

onBeforeUnmount(() => {
    if (searchTimer) clearTimeout(searchTimer);
    marker?.remove();
    marker = null;
    map = null;
});
</script>

<template>
    <div class="space-y-2">
        <div
            class="relative isolate overflow-hidden rounded-xl border border-slate-200"
        >
            <!-- Search bar over the map -->
            <div class="absolute top-2 right-2 left-2 z-10">
                <div
                    class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white/95 px-3 shadow-md backdrop-blur-sm"
                >
                    <Loader2
                        v-if="searching"
                        class="size-4 shrink-0 animate-spin text-slate-400"
                    />
                    <Search
                        v-else
                        class="size-4 shrink-0 text-slate-400"
                        aria-hidden="true"
                    />
                    <input
                        v-model="query"
                        type="search"
                        autocomplete="off"
                        aria-label="Search for a place to pin"
                        placeholder="Search street, barangay or landmark"
                        class="min-h-11 flex-1 bg-transparent text-sm text-slate-900 outline-none placeholder:text-slate-500"
                        @input="onSearchInput"
                        @keydown.enter.prevent
                    />
                    <button
                        v-if="query"
                        type="button"
                        class="flex size-8 items-center justify-center rounded text-slate-500 hover:text-slate-900 focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:outline-none"
                        aria-label="Clear search"
                        @click="clearSearch"
                    >
                        <X class="size-4" />
                    </button>
                </div>

                <ul
                    v-if="suggestions.length"
                    class="mt-1 max-h-44 overflow-auto rounded-xl border border-slate-200 bg-white shadow-lg"
                >
                    <li
                        v-for="place in suggestions"
                        :key="`${place.lat},${place.lng}`"
                    >
                        <button
                            type="button"
                            class="flex min-h-11 w-full items-start gap-2 px-3 py-2 text-left text-xs text-slate-700 hover:bg-blue-50 focus-visible:bg-blue-50 focus-visible:outline-none"
                            @click="choose(place)"
                        >
                            <MapPin
                                class="mt-0.5 size-3.5 shrink-0 text-slate-400"
                                aria-hidden="true"
                            />
                            <span>{{ place.label }}</span>
                        </button>
                    </li>
                </ul>
            </div>

            <MapView
                class="w-full"
                height-class="h-72"
                :center="startCenter"
                :zoom="startZoom"
                :pitch="0"
                :bearing="0"
                :light-toggle="false"
                role="application"
                aria-label="Map. Tap to drop a pin; drag the pin to adjust."
                @ready="onMapReady"
            />
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2">
            <button
                type="button"
                class="inline-flex min-h-11 items-center gap-1.5 rounded text-sm font-semibold text-blue-600 hover:text-blue-700 focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:outline-none disabled:opacity-60"
                :disabled="locating"
                @click="useMyLocation"
            >
                <Loader2 v-if="locating" class="size-4 animate-spin" />
                <LocateFixed v-else class="size-4" aria-hidden="true" />
                {{ locating ? 'Finding you…' : 'Use my current location' }}
            </button>
            <p
                class="flex items-center gap-1 text-xs"
                :class="modelValue ? 'text-green-700' : 'text-slate-500'"
                aria-live="polite"
            >
                <Loader2 v-if="looking" class="size-3 animate-spin" />
                <template v-if="modelValue">Location pinned</template>
                <template v-else>Tap the map or search to pin it</template>
            </p>
        </div>
    </div>
</template>
