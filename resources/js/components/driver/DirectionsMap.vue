<script setup lang="ts">
import MapView from '@/components/map/MapView.vue';
import {
    addMarker,
    drawRouteLine,
    fitPoints,
    pinElement,
    toLngLat,
} from '@/lib/mapbox';
import type { LatLng } from '@/lib/maps';
import { fetchRoute, summarizeRoute, type RouteSummary } from '@/lib/routing';
import type { Map as MapboxMap, Marker } from 'mapbox-gl';
import { onBeforeUnmount, watch } from 'vue';

const props = defineProps<{
    /** Where the driver is now; the route starts here. */
    origin: LatLng | null;
    destination: {
        lat: number | null;
        lng: number | null;
        address?: string | null;
    };
    heightClass?: string;
}>();

const emit = defineEmits<{ route: [summary: RouteSummary | null] }>();

const ROUTE_ID = 'driver-route';
// A moving driver re-routes at most this often.
const REROUTE_MS = 15_000;

let map: MapboxMap | null = null;
let destinationMarker: Marker | null = null;
let originMarker: Marker | null = null;
let lastRouteAt = 0;
let hasRoute = false;
let abort: AbortController | null = null;

function destinationPoint(): LatLng | null {
    return props.destination.lat !== null && props.destination.lng !== null
        ? { lat: props.destination.lat, lng: props.destination.lng }
        : null;
}

async function draw() {
    const destination = destinationPoint();
    if (!map || !destination) return;

    if (destinationMarker) destinationMarker.setLngLat(toLngLat(destination));
    else {
        destinationMarker = addMarker(map, pinElement('#0f766e'), destination, {
            anchor: 'bottom',
        });
    }

    if (!props.origin) {
        map.easeTo({ center: toLngLat(destination), zoom: 15 });
        return;
    }

    const origin = props.origin;
    if (originMarker) originMarker.setLngLat(toLngLat(origin));
    else {
        const dot = document.createElement('span');
        dot.style.cssText =
            'display:block;width:18px;height:18px;border-radius:9999px;background:#2563eb;border:3px solid #fff;box-shadow:0 0 0 6px rgba(37,99,235,.2)';
        originMarker = addMarker(map, dot, origin);
    }

    if (Date.now() - lastRouteAt < REROUTE_MS && hasRoute) return;
    lastRouteAt = Date.now();

    abort?.abort();
    abort = new AbortController();

    try {
        const route = await fetchRoute(origin, destination, abort.signal);
        drawRouteLine(
            map,
            ROUTE_ID,
            route.path.map((p) => toLngLat(p)),
            { color: '#0f766e', dashed: !route.isRoad },
        );
        if (!hasRoute) fitPoints(map, [origin, destination], { padding: 60 });
        hasRoute = true;
        emit('route', summarizeRoute(route));
    } catch {
        // A newer request replaced this one.
    }
}

function onReady(created: MapboxMap) {
    map = created;
    void draw();
}

watch(() => [props.origin?.lat, props.origin?.lng, props.destination], draw, {
    deep: true,
});

onBeforeUnmount(() => {
    abort?.abort();
    map = null;
});
</script>

<template>
    <MapView
        :center="destinationPoint()"
        :zoom="15"
        :height-class="heightClass ?? 'h-64'"
        @ready="onReady"
    />
</template>
