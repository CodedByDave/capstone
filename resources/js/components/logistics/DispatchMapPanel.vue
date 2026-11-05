<script setup lang="ts">
import MapView from '@/components/map/MapView.vue';
import { Button } from '@/components/ui/button';
import { fetchShopRoute, mapErrorMessage, optimizeStops } from '@/lib/mapApi';
import {
    addMarker,
    dotElement,
    drawRouteLine,
    fitPoints,
    numberElement,
    toLngLat,
} from '@/lib/mapbox';
import { formatDistance, formatDuration } from '@/lib/routing';
import { Loader2, MapPin, Route, Sparkles, Store, X } from 'lucide-vue-next';
import type { Map as MapboxMap, Marker } from 'mapbox-gl';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

interface Stop {
    public_id: string;
    customer_name: string;
    address: string | null;
    status: string;
    type: 'pickup' | 'delivery';
    lat: number;
    lng: number;
}

interface ShopPoint {
    public_id: string;
    name: string | null;
    lat: number | null;
    lng: number | null;
}

const props = defineProps<{
    shop: ShopPoint | null;
    stops: Stop[];
}>();

const STATUS_COLOR: Record<string, string> = {
    pending: '#f59e0b',
    assigned: '#3b82f6',
    picked_up: '#6366f1',
};

const ROUTE_ID = 'dispatch-route';

const selected = ref<string[]>([]);
const focused = ref<string | null>(null);
const busy = ref(false);
/** Visiting order after optimising, by stop public_id. */
const order = ref<string[]>([]);
const summary = ref<{ distance_m: number; duration_s: number } | null>(null);

let map: MapboxMap | null = null;
let shopMarker: Marker | null = null;
let stopMarkers: Marker[] = [];

const shopReady = computed(
    () =>
        props.shop !== null &&
        props.shop.lat !== null &&
        props.shop.lng !== null,
);

const stopsById = computed(
    () => new Map(props.stops.map((stop) => [stop.public_id, stop])),
);

const shopPoint = computed(() =>
    shopReady.value
        ? { lat: props.shop!.lat as number, lng: props.shop!.lng as number }
        : null,
);

function sequenceOf(id: string): number | null {
    const index = order.value.indexOf(id);
    return index === -1 ? null : index + 1;
}

function drawMarkers() {
    if (!map) return;
    stopMarkers.forEach((marker) => marker.remove());
    stopMarkers = [];

    if (shopPoint.value && !shopMarker) {
        const element = dotElement('#111827', 22);
        element.title = props.shop?.name ?? 'Shop';
        shopMarker = addMarker(map, element, shopPoint.value);
    }

    for (const stop of props.stops) {
        const sequence = sequenceOf(stop.public_id);
        const element =
            sequence !== null
                ? numberElement(sequence)
                : dotElement(
                      STATUS_COLOR[stop.status] ?? '#6b7280',
                      selected.value.includes(stop.public_id) ? 24 : 18,
                  );
        element.title = `${stop.customer_name}${stop.address ? ` — ${stop.address}` : ''}`;
        element.style.cursor = 'pointer';
        element.addEventListener('click', () => focusStop(stop.public_id));
        stopMarkers.push(addMarker(map, element, stop));
    }
}

function clearRoute() {
    if (map) drawRouteLine(map, ROUTE_ID, null);
    summary.value = null;
}

function onReady(created: MapboxMap) {
    map = created;
    drawMarkers();
    fitAll();
}

function fitAll() {
    if (!map) return;
    const points = [
        ...(shopPoint.value ? [shopPoint.value] : []),
        ...props.stops.map((stop) => ({ lat: stop.lat, lng: stop.lng })),
    ];
    fitPoints(map, points, { padding: 70, maxZoom: 15 });
}

async function focusStop(id: string) {
    const stop = stopsById.value.get(id);
    if (!stop || !map || !props.shop) return;

    focused.value = id;
    order.value = [];
    drawMarkers();
    map.flyTo({ center: toLngLat(stop), zoom: 16, essential: true });

    busy.value = true;
    try {
        const route = await fetchShopRoute(props.shop.public_id, stop);
        if (focused.value !== id || !map) return;
        drawRouteLine(map, ROUTE_ID, route.geometry.coordinates, {
            color: '#2563eb',
        });
        summary.value = {
            distance_m: route.distance_m,
            duration_s: route.duration_s,
        };
    } catch (error) {
        clearRoute();
        toast.error(mapErrorMessage(error));
    } finally {
        busy.value = false;
    }
}

function toggle(id: string) {
    selected.value = selected.value.includes(id)
        ? selected.value.filter((value) => value !== id)
        : [...selected.value, id];
    order.value = [];
    drawMarkers();
}

function clearSelection() {
    selected.value = [];
    order.value = [];
    focused.value = null;
    clearRoute();
    drawMarkers();
    fitAll();
}

async function optimize() {
    if (!props.shop || !map || selected.value.length === 0) return;

    const chosen = selected.value
        .map((id) => stopsById.value.get(id))
        .filter((stop): stop is Stop => stop !== undefined);

    busy.value = true;
    focused.value = null;
    try {
        const result = await optimizeStops(
            props.shop.public_id,
            chosen.map((stop) => ({ lat: stop.lat, lng: stop.lng })),
        );
        order.value = result.order.map((index) => chosen[index].public_id);
        drawRouteLine(map, ROUTE_ID, result.geometry.coordinates, {
            color: '#2563eb',
        });
        summary.value = {
            distance_m: result.distance_m,
            duration_s: result.duration_s,
        };
        drawMarkers();
        fitPoints(
            map,
            [
                shopPoint.value!,
                ...chosen.map((s) => ({ lat: s.lat, lng: s.lng })),
            ],
            { padding: 70, maxZoom: 15 },
        );
    } catch (error) {
        toast.error(mapErrorMessage(error));
    } finally {
        busy.value = false;
    }
}

const orderedStops = computed(() =>
    order.value
        .map((id) => stopsById.value.get(id))
        .filter((stop): stop is Stop => stop !== undefined),
);

watch(
    () => props.stops,
    () => {
        const known = new Set(props.stops.map((stop) => stop.public_id));
        selected.value = selected.value.filter((id) => known.has(id));
        order.value = order.value.filter((id) => known.has(id));
        drawMarkers();
    },
);

onBeforeUnmount(() => {
    map = null;
});
</script>

<template>
    <section
        class="overflow-hidden rounded-[14px] border border-border bg-card"
        aria-labelledby="dispatch-map-title"
    >
        <div
            class="flex flex-wrap items-center justify-between gap-2 border-b border-border px-4 py-3"
        >
            <div class="flex items-center gap-2">
                <Route class="h-4 w-4 text-primary" aria-hidden="true" />
                <h2 id="dispatch-map-title" class="text-sm font-semibold">
                    Dispatch map
                </h2>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <Button
                    size="sm"
                    :disabled="busy || selected.length === 0 || !shopReady"
                    @click="optimize"
                >
                    <Loader2
                        v-if="busy"
                        class="mr-1.5 h-3.5 w-3.5 animate-spin"
                    />
                    <Sparkles v-else class="mr-1.5 h-3.5 w-3.5" />
                    Optimize route<span v-if="selected.length > 1">
                        ({{ selected.length }} stops)</span
                    >
                </Button>
                <Button
                    v-if="selected.length || focused"
                    size="sm"
                    variant="outline"
                    @click="clearSelection"
                >
                    <X class="mr-1.5 h-3.5 w-3.5" />
                    Clear
                </Button>
            </div>
        </div>

        <div
            v-if="!shopReady"
            class="flex items-start gap-2 p-4 text-sm text-amber-700"
        >
            <MapPin class="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true" />
            Set your shop location in Settings to see deliveries on the map.
        </div>

        <div v-else class="grid lg:grid-cols-[1fr_20rem]">
            <MapView
                :center="shopPoint"
                :zoom="14"
                height-class="h-80 lg:h-[26rem]"
                @ready="onReady"
            />

            <div
                class="max-h-80 overflow-y-auto border-t border-border lg:max-h-[26rem] lg:border-t-0 lg:border-l"
            >
                <p
                    v-if="summary"
                    class="border-b border-border bg-muted/40 px-4 py-2 text-xs font-medium"
                >
                    {{ formatDistance(summary.distance_m) }} ·
                    {{ formatDuration(summary.duration_s) }}
                    <span v-if="orderedStops.length">
                        · {{ orderedStops.length }} stops</span
                    >
                </p>

                <ol v-if="orderedStops.length" class="divide-y divide-border">
                    <li
                        v-for="(stop, index) in orderedStops"
                        :key="stop.public_id"
                        class="flex items-start gap-3 px-4 py-2.5 text-sm"
                    >
                        <span
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white"
                            >{{ index + 1 }}</span
                        >
                        <span class="min-w-0">
                            <span class="block font-medium">{{
                                stop.customer_name
                            }}</span>
                            <span
                                class="line-clamp-2 text-xs text-muted-foreground"
                                >{{ stop.address }}</span
                            >
                        </span>
                    </li>
                </ol>

                <ul v-else-if="stops.length" class="divide-y divide-border">
                    <li
                        v-for="stop in stops"
                        :key="stop.public_id"
                        :class="[
                            'flex items-start gap-3 px-4 py-2.5',
                            focused === stop.public_id && 'bg-primary/5',
                        ]"
                    >
                        <input
                            :id="`stop-${stop.public_id}`"
                            type="checkbox"
                            class="mt-1 h-4 w-4 shrink-0 rounded border-border"
                            :checked="selected.includes(stop.public_id)"
                            :aria-label="`Select ${stop.customer_name} for route`"
                            @change="toggle(stop.public_id)"
                        />
                        <button
                            type="button"
                            class="min-w-0 flex-1 text-left focus-visible:ring-2 focus-visible:ring-primary focus-visible:outline-none"
                            @click="focusStop(stop.public_id)"
                        >
                            <span
                                class="flex items-center gap-1.5 text-sm font-medium"
                            >
                                <span
                                    class="h-2 w-2 shrink-0 rounded-full"
                                    :style="{
                                        background:
                                            STATUS_COLOR[stop.status] ??
                                            '#6b7280',
                                    }"
                                />
                                {{ stop.customer_name }}
                            </span>
                            <span
                                class="line-clamp-2 text-xs text-muted-foreground"
                                >{{ stop.address }}</span
                            >
                        </button>
                    </li>
                </ul>

                <div
                    v-else
                    class="flex flex-col items-center gap-2 px-4 py-10 text-center text-sm text-muted-foreground"
                >
                    <Store class="h-6 w-6" aria-hidden="true" />
                    No open deliveries with a pinned address.
                </div>
            </div>
        </div>
    </section>
</template>
