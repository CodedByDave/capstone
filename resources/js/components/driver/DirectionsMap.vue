<script setup lang="ts">
import {
    googleMapsNavigationUrl,
    loadGoogleMaps,
    requestRoute,
    type GoogleRoute,
} from '@/lib/googleMaps';
import type { LatLng } from '@/lib/maps';
import { MapPinOff, Navigation } from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps<{
    apiKey: string | null;
    /** Where the driver is now; the route starts here. */
    origin: LatLng | null;
    destination: {
        lat: number | null;
        lng: number | null;
        address?: string | null;
    };
    heightClass?: string;
}>();

const emit = defineEmits<{ route: [summary: GoogleRoute | null] }>();

const mapEl = ref<HTMLElement | null>(null);
const failed = ref(false);

let google: any = null;
let map: any = null;
let renderer: any = null;
let destinationMarker: any = null;
let lastRouteAt = 0;

const hasDestination = () =>
    props.destination.lat !== null && props.destination.lng !== null;

async function draw() {
    if (!google || !map || !hasDestination()) return;

    const destination = {
        lat: props.destination.lat as number,
        lng: props.destination.lng as number,
    };

    destinationMarker ??= new google.maps.Marker({
        map,
        position: destination,
    });
    destinationMarker.setPosition(destination);

    if (!props.origin) {
        map.setCenter(destination);
        map.setZoom(15);
        return;
    }

    // Directions are billed per request, so a moving driver re-routes at most every 20 seconds.
    if (Date.now() - lastRouteAt < 20_000 && renderer) return;
    lastRouteAt = Date.now();

    const route = await requestRoute(google, props.origin, destination);
    if (!route) {
        emit('route', null);
        return;
    }

    renderer ??= new google.maps.DirectionsRenderer({
        map,
        suppressMarkers: false,
        polylineOptions: {
            strokeOpacity: 0,
            icons: [
                {
                    icon: {
                        path: 'M 0,-1 0,1',
                        strokeOpacity: 1,
                        strokeColor: '#0f766e',
                        scale: 3,
                    },
                    offset: '0',
                    repeat: '10px',
                },
            ],
        },
    });
    renderer.setDirections(route.result);
    emit('route', route.summary);
}

onMounted(async () => {
    if (!props.apiKey || !mapEl.value) {
        failed.value = true;
        return;
    }

    try {
        google = await loadGoogleMaps(props.apiKey);
    } catch {
        failed.value = true;
        return;
    }

    map = new google.maps.Map(mapEl.value, {
        center: { lat: 14.2456, lng: 120.8687 },
        zoom: 13,
        disableDefaultUI: true,
        gestureHandling: 'greedy',
    });

    draw();
});

watch(() => [props.origin?.lat, props.origin?.lng, props.destination], draw, {
    deep: true,
});

onBeforeUnmount(() => {
    renderer?.setMap(null);
    destinationMarker?.setMap(null);
});
</script>

<template>
    <div
        :class="[
            'relative overflow-hidden bg-slate-200',
            heightClass ?? 'h-64',
        ]"
    >
        <div v-if="!failed" ref="mapEl" class="h-full w-full" />

        <div
            v-else
            class="flex h-full flex-col items-center justify-center gap-3 px-6 text-center text-sm text-slate-600"
        >
            <MapPinOff class="h-6 w-6" aria-hidden="true" />
            <p>The map preview is not available right now.</p>
            <a
                :href="googleMapsNavigationUrl(destination)"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 font-semibold text-white"
            >
                <Navigation class="h-4 w-4" />
                Open in Google Maps
            </a>
        </div>
    </div>
</template>
