<script setup lang="ts">
import type { LatLng } from '@/lib/maps';
import {
    bearing,
    distanceMeters,
    fetchRoute,
    formatClock,
    type RoadRoute,
} from '@/lib/routing';
import {
    createTrackingMap,
    glideMarker,
    rotateVehicle,
    shopIcon,
    stopIcon,
    vehicleIcon,
    type TrackingBaseLayer,
    type VehicleKind,
} from '@/lib/trackingMap';
import L from 'leaflet';
import { Layers, LocateFixed } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref, watch } from 'vue';

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

const mapEl = ref<HTMLElement | null>(null);
const baseLayer = ref<TrackingBaseLayer>('map');
const following = ref(true);

let map: L.Map | null = null;
let setBase: ((kind: TrackingBaseLayer) => void) | null = null;
let routeCasing: L.Polyline | null = null;
let routeLine: L.Polyline | null = null;
let vehicleMarker: L.Marker | null = null;
let stopMarker: L.Marker | null = null;
let chipMarker: L.Marker | null = null;
let shopMarker: L.Marker | null = null;
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

    const time = eta ? formatClock(eta.arriveAt) : '';
    const html = `<div class="trk-chip"><span class="trk-chip__label">${props.targetLabel}</span>${
        time ? `<span class="trk-chip__time">${time}</span>` : ''
    }</div>`;
    const icon = L.divIcon({
        className: 'trk-chip-wrap',
        html,
        iconSize: undefined,
        iconAnchor: [0, 18],
    });

    if (chipMarker) chipMarker.setIcon(icon).setLatLng(stop);
    else {
        chipMarker = L.marker(stop, {
            icon,
            interactive: false,
            keyboard: false,
            zIndexOffset: 500,
        }).addTo(map);
    }
}

function drawStatic() {
    if (!map) return;

    const shop = point(props.shop);
    if (shop) {
        if (shopMarker) shopMarker.setLatLng(shop);
        else {
            shopMarker = L.marker(shop, {
                icon: shopIcon(),
                keyboard: false,
                interactive: false,
            }).addTo(map);
        }
    }

    const stop = point(props.target);
    if (stop && props.active) {
        if (stopMarker) stopMarker.setLatLng(stop);
        else {
            stopMarker = L.marker(stop, {
                icon: stopIcon(),
                keyboard: false,
                interactive: false,
            }).addTo(map);
        }
    } else {
        stopMarker?.remove();
        stopMarker = null;
    }

    drawChip();
}

function drawRoute(route: RoadRoute | null) {
    if (!map) return;

    if (!route) {
        routeCasing?.remove();
        routeLine?.remove();
        routeCasing = routeLine = null;
        return;
    }

    const latlngs = route.path.map((p) => [p.lat, p.lng] as L.LatLngTuple);
    const dash = route.isRoad ? undefined : '6 10';

    if (!routeCasing) {
        routeCasing = L.polyline(latlngs, {
            color: '#ffffff',
            weight: 9,
            opacity: 0.95,
            lineCap: 'round',
            lineJoin: 'round',
            interactive: false,
        }).addTo(map);
        routeLine = L.polyline(latlngs, {
            color: '#10b981',
            weight: 5,
            lineCap: 'round',
            lineJoin: 'round',
            dashArray: dash,
            interactive: false,
        }).addTo(map);
    } else {
        routeCasing.setLatLngs(latlngs);
        routeLine?.setLatLngs(latlngs);
        routeLine?.setStyle({ dashArray: dash });
    }

    // Keep the vehicle above the route line.
    vehicleMarker?.setZIndexOffset(1000);
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

function moveVehicle(next: LatLng) {
    if (!map) return;

    if (!vehicleMarker) {
        vehicleMarker = L.marker(next, {
            icon: vehicleIcon(props.vehicle ?? 'motorcycle'),
            keyboard: false,
            interactive: false,
            zIndexOffset: 1000,
        }).addTo(map);
        lastRiderPoint = next;

        // Before it has moved, face the stop so the icon points somewhere sensible.
        const stop = point(props.target);
        if (stop) {
            heading = bearing(next, stop);
            rotateVehicle(vehicleMarker, heading);
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
        // Turn the short way round (350° to 10° is 20°, not 340°).
        const delta =
            ((bearing(lastRiderPoint, next) - heading + 540) % 360) - 180;
        heading += delta;
        rotateVehicle(vehicleMarker, heading);
    }

    lastRiderPoint = next;
    cancelGlide = glideMarker(vehicleMarker, next, GLIDE_MS, (position) => {
        if (following.value && map) {
            map.panTo([position.lat, position.lng], {
                animate: false,
                noMoveStart: true,
            });
        }
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

    if (points.length === 0) return;
    if (points.length === 1) {
        map.setView([points[0].lat, points[0].lng], 16);
        return;
    }

    map.fitBounds(
        points.map((p) => [p.lat, p.lng] as L.LatLngTuple),
        { padding: [60, 60], maxZoom: 17 },
    );
}

function recenter() {
    following.value = true;
    fit();
}

function toggleLayer() {
    baseLayer.value = baseLayer.value === 'map' ? 'satellite' : 'map';
    setBase?.(baseLayer.value);
}

let resizeObserver: ResizeObserver | null = null;

onMounted(() => {
    if (!mapEl.value) return;

    const created = createTrackingMap(mapEl.value);
    map = created.map;
    setBase = created.setBaseLayer;

    // The user dragging the map means they want to look around: stop following the vehicle.
    map.on('dragstart', () => {
        following.value = false;
    });

    resizeObserver = new ResizeObserver(() => map?.invalidateSize());
    resizeObserver.observe(mapEl.value);

    drawStatic();
    if (props.rider && props.active) moveVehicle(props.rider);
    fit();
    void updateRoute(true);
});

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
        vehicleMarker?.setIcon(vehicleIcon(kind ?? 'motorcycle'));
        if (vehicleMarker) rotateVehicle(vehicleMarker, heading);
    },
);

onUnmounted(() => {
    cancelGlide?.();
    routeAbort?.abort();
    resizeObserver?.disconnect();
    map?.remove();
    map = null;
});
</script>

<template>
    <div class="trk-map relative h-full w-full overflow-hidden bg-[#e9eef2]">
        <div ref="mapEl" class="absolute inset-0" />

        <div class="absolute top-4 right-4 z-[500] flex flex-col gap-3">
            <button
                type="button"
                class="trk-round-button"
                :aria-label="
                    baseLayer === 'map'
                        ? 'Switch to satellite view'
                        : 'Switch to map view'
                "
                @click="toggleLayer"
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
.trk-map .leaflet-container {
    background: #e9eef2;
    font-family: inherit;
}

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
.trk-chip-wrap {
    pointer-events: none;
}

.trk-chip {
    display: flex;
    flex-direction: column;
    gap: 1px;
    transform: translate(-50%, -100%);
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
