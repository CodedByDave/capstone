<script setup lang="ts">
import MapView from '@/components/map/MapView.vue';
import {
    addMarker,
    drawRouteLine,
    fitPoints,
    glideMarker,
    LIGHT_PRESETS,
    setLightPreset,
    toLngLat,
} from '@/lib/mapbox';
import type { LatLng } from '@/lib/maps';
import {
    bearing,
    distanceMeters,
    fetchRoute,
    formatClock,
    type RoadRoute,
} from '@/lib/routing';
import {
    chipElement,
    rotateVehicle,
    shopElement,
    stopElement,
    vehicleElement,
    type VehicleKind,
} from '@/lib/trackingMap';
import { Layers, LocateFixed } from 'lucide-vue-next';
import type { Map as MapboxMap, Marker } from 'mapbox-gl';
import { onUnmounted, ref, watch } from 'vue';

export interface TrackingEta {
    arriveAt: Date;
    seconds: number;
    meters: number;
    isRoad: boolean;
}

type Point = { lat: number | null; lng: number | null };

const props = defineProps<{
    vehicle: VehicleKind | null;
    /** Where the rider is now, or null before they share a location. */
    rider: { lat: number; lng: number } | null;
    shop: Point;
    /** The stop the rider is heading to (the shop or the customer). */
    target: Point;
    /** Short label above the time, e.g. "DROPOFF". */
    targetLabel: string;
    /** Draw the live route and vehicle; false once the trip is over. */
    active: boolean;
}>();

const emit = defineEmits<{ eta: [value: TrackingEta | null] }>();

const lightIndex = ref(0);
const following = ref(true);

const ROUTE_ID = 'trk-route';

let map: MapboxMap | null = null;
let vehicleMarker: Marker | null = null;
let stopMarker: Marker | null = null;
let chipMarker: Marker | null = null;
let shopMarker: Marker | null = null;
let cancelGlide: (() => void) | null = null;
let lastRiderPoint: LatLng | null = null;
let heading = 0;
let eta: TrackingEta | null = null;

const GLIDE_MS = 4_500;
const MIN_TURN_METERS = 8;
const REROUTE_METERS = 60;
const REROUTE_MIN_INTERVAL_MS = 8_000;
let lastRouteAt = 0;
let lastRouteFrom: LatLng | null = null;
let lastRouteTo: LatLng | null = null;
let routeAbort: AbortController | null = null;

function point(p: Point): LatLng | null {
    return p.lat !== null && p.lng !== null ? { lat: p.lat, lng: p.lng } : null;
}

function setEta(next: TrackingEta | null) {
    eta = next;
    emit('eta', next);
    drawChip();
}

function drawChip() {
    if (!map) return;
    const stop = point(props.target);

    if (!stop || !props.active) {
        chipMarker?.remove();
        chipMarker = null;
        return;
    }

    const element = chipElement(
        props.targetLabel,
        eta ? formatClock(eta.arriveAt) : '',
    );

    chipMarker?.remove();
    chipMarker = addMarker(map, element, stop, { anchor: 'bottom' });
    chipMarker.setOffset([0, -18]);
}

function drawStatic() {
    if (!map) return;

    const shop = point(props.shop);
    if (shop) {
        if (shopMarker) shopMarker.setLngLat(toLngLat(shop));
        else shopMarker = addMarker(map, shopElement(), shop);
    }

    const stop = point(props.target);
    if (stop && props.active) {
        if (stopMarker) stopMarker.setLngLat(toLngLat(stop));
        else stopMarker = addMarker(map, stopElement(), stop);
    } else {
        stopMarker?.remove();
        stopMarker = null;
    }

    drawChip();
}

function drawRoute(route: RoadRoute | null) {
    if (!map) return;

    drawRouteLine(
        map,
        ROUTE_ID,
        route ? route.path.map((p) => toLngLat(p)) : null,
        { dashed: route ? !route.isRoad : false },
    );
}

async function updateRoute(force = false) {
    const stop = point(props.target);
    const from = props.rider
        ? { lat: props.rider.lat, lng: props.rider.lng }
        : null;

    if (!props.active || !stop || !from) {
        routeAbort?.abort();
        // Forget the last route so one is drawn again when the rider comes back.
        lastRouteFrom = lastRouteTo = null;
        drawRoute(null);
        setEta(null);
        return;
    }

    const movedEnough =
        !lastRouteFrom || distanceMeters(lastRouteFrom, from) > REROUTE_METERS;
    const targetChanged = !lastRouteTo || distanceMeters(lastRouteTo, stop) > 5;
    const waited = Date.now() - lastRouteAt > REROUTE_MIN_INTERVAL_MS;

    if (!force && !targetChanged && !(movedEnough && waited)) return;

    routeAbort?.abort();
    routeAbort = new AbortController();
    lastRouteAt = Date.now();
    lastRouteFrom = from;
    lastRouteTo = stop;

    try {
        const route = await fetchRoute(from, stop, routeAbort.signal);
        // A straight-line fallback should be replaced by a road route as soon as the router answers.
        if (!route.isRoad) lastRouteFrom = null;
        drawRoute(route);
        setEta({
            arriveAt: new Date(Date.now() + route.durationSeconds * 1000),
            seconds: route.durationSeconds,
            meters: route.distanceMeters,
            isRoad: route.isRoad,
        });
    } catch {
        // A newer request replaced this one.
    }
}

function placeVehicle(
    mapbox: MapboxMap,
    at: LatLng,
    kind: VehicleKind | null,
): Marker {
    const marker = addMarker(mapbox, vehicleElement(kind ?? 'motorcycle'), at);
    marker.getElement().style.zIndex = '10';
    vehicleMarker = marker;

    return marker;
}

function moveVehicle(next: LatLng) {
    if (!map) return;

    if (!vehicleMarker) {
        const created = placeVehicle(map, next, props.vehicle);
        lastRiderPoint = next;

        // Before it has moved, face the stop so the icon points somewhere sensible.
        const stop = point(props.target);
        if (stop) {
            heading = bearing(next, stop);
            rotateVehicle(created.getElement(), heading);
        }

        // The rider just appeared: bring them into view.
        if (following.value) fit();

        return;
    }

    cancelGlide?.();

    // Ignore GPS jitter; only turn when the vehicle has really moved.
    if (
        lastRiderPoint &&
        distanceMeters(lastRiderPoint, next) > MIN_TURN_METERS
    ) {
        // Turn the short way round (350 to 10 degrees is 20, not 340).
        const delta =
            ((bearing(lastRiderPoint, next) - heading + 540) % 360) - 180;
        heading += delta;
        rotateVehicle(vehicleMarker.getElement(), heading);
    }

    lastRiderPoint = next;
    cancelGlide = glideMarker(vehicleMarker, next, GLIDE_MS, (position) => {
        if (following.value && map) map.setCenter(toLngLat(position));
    });
}

function fit() {
    if (!map) return;

    const points: LatLng[] = [];
    if (props.rider) points.push(props.rider);
    const stop = point(props.target);
    if (stop && props.active) points.push(stop);
    const shop = point(props.shop);
    if (shop && points.length < 2) points.push(shop);

    fitPoints(map, points, { padding: 80, maxZoom: 17 });
}

function recenter() {
    following.value = true;
    fit();
}

function cycleLighting() {
    lightIndex.value = (lightIndex.value + 1) % LIGHT_PRESETS.length;
    if (map) setLightPreset(map, LIGHT_PRESETS[lightIndex.value]);
}

function onReady(created: MapboxMap) {
    map = created;

    // The user dragging the map means they want to look around: stop following the vehicle.
    map.on('dragstart', () => {
        following.value = false;
    });

    drawStatic();
    if (props.rider && props.active) moveVehicle(props.rider);
    fit();
    void updateRoute(true);
}

watch(
    () => [props.target.lat, props.target.lng, props.active, props.targetLabel],
    () => {
        drawStatic();
        void updateRoute(true);
        if (following.value) fit();
    },
);

watch(
    () => [props.shop.lat, props.shop.lng],
    () => drawStatic(),
);

watch(
    () => props.rider,
    (rider) => {
        if (!map) return;

        if (!rider || !props.active) {
            vehicleMarker?.remove();
            vehicleMarker = null;
            lastRiderPoint = null;
            cancelGlide?.();
            void updateRoute(true);
            return;
        }

        moveVehicle({ lat: rider.lat, lng: rider.lng });
        void updateRoute();
    },
    { deep: true },
);

watch(
    () => props.vehicle,
    (kind) => {
        if (!vehicleMarker || !map) return;
        const at = vehicleMarker.getLngLat();
        vehicleMarker.remove();
        const replaced = placeVehicle(map, { lat: at.lat, lng: at.lng }, kind);
        rotateVehicle(replaced.getElement(), heading);
    },
);

onUnmounted(() => {
    cancelGlide?.();
    routeAbort?.abort();
    map = null;
});
</script>

<template>
    <div class="trk-map relative h-full w-full overflow-hidden bg-[#e9eef2]">
        <MapView
            class="absolute inset-0"
            height-class="h-full"
            :light-toggle="false"
            :zoom="16"
            :center="point(shop) ?? point(target)"
            @ready="onReady"
        />

        <div class="absolute top-4 right-4 z-[5] flex flex-col gap-3">
            <button
                type="button"
                class="trk-round-button"
                :aria-label="`Change map lighting (now ${LIGHT_PRESETS[lightIndex]})`"
                @click="cycleLighting"
            >
                <Layers class="h-5 w-5" aria-hidden="true" />
            </button>
            <button
                type="button"
                class="trk-round-button"
                :class="{ 'trk-round-button--active': following }"
                aria-label="Re-center the map on the rider"
                @click="recenter"
            >
                <LocateFixed class="h-5 w-5" aria-hidden="true" />
            </button>
        </div>
    </div>
</template>

<style>
.trk-round-button {
    display: flex;
    height: 3rem;
    width: 3rem;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    background: #fff;
    color: #111827;
    box-shadow:
        0 2px 10px rgba(15, 23, 42, 0.18),
        0 0 0 1px rgba(15, 23, 42, 0.04);
    transition: transform 0.15s ease;
}

.trk-round-button:active {
    transform: scale(0.94);
}

.trk-round-button--active {
    color: #059669;
}

/* The vehicle: the inner body turns, the marker stays put. */
.trk-vehicle__body {
    transition: transform 0.6s ease;
    transform-origin: 50% 50%;
    filter: drop-shadow(0 3px 5px rgba(15, 23, 42, 0.35));
}

/* The stop: a green dot with a soft pulse. */
.trk-stop {
    position: relative;
}

.trk-stop__dot {
    position: absolute;
    inset: 4px;
    border-radius: 9999px;
    background: #10b981;
    border: 3px solid #fff;
    box-shadow: 0 1px 5px rgba(15, 23, 42, 0.4);
}

.trk-stop__pulse {
    position: absolute;
    inset: 0;
    border-radius: 9999px;
    background: rgba(16, 185, 129, 0.35);
    animation: trk-pulse 1.8s ease-out infinite;
}

@keyframes trk-pulse {
    0% {
        transform: scale(0.6);
        opacity: 0.9;
    }
    100% {
        transform: scale(2.1);
        opacity: 0;
    }
}

.trk-shop__pin {
    display: flex;
    height: 28px;
    width: 28px;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    background: #111827;
    border: 2px solid #fff;
    box-shadow: 0 1px 5px rgba(15, 23, 42, 0.4);
}

/* The time chip above the stop, like "DROPOFF 1:54 pm". */
.trk-chip {
    display: flex;
    flex-direction: column;
    gap: 1px;
    pointer-events: none;
    white-space: nowrap;
    border-radius: 0.75rem;
    background: #fff;
    padding: 0.4rem 0.75rem;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.2);
}

.trk-chip__label {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    color: #10b981;
}

.trk-chip__time {
    font-size: 1.05rem;
    font-weight: 600;
    line-height: 1.1;
    color: #111827;
}

@media (prefers-reduced-motion: reduce) {
    .trk-stop__pulse {
        animation: none;
    }

    .trk-vehicle__body {
        transition: none;
    }
}
</style>
