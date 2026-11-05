<script setup lang="ts">
import MapView from '@/components/map/MapView.vue';
import { addMarker, pinElement, toLngLat } from '@/lib/mapbox';
import type { LatLng } from '@/lib/maps';
import type { Map as MapboxMap, Marker } from 'mapbox-gl';
import { onBeforeUnmount, watch } from 'vue';

/**
 * A small map with a single pin. Replaces "open in Google Maps" links: the location is shown in
 * the page itself. With `pickable`, clicking or dragging the pin reports a new position.
 */
const props = withDefaults(
    defineProps<{
        lat: number | null;
        lng: number | null;
        label?: string;
        pickable?: boolean;
        heightClass?: string;
        zoom?: number;
    }>(),
    { label: 'Location', pickable: false, heightClass: 'h-56', zoom: 16 },
);

const emit = defineEmits<{ pick: [point: LatLng] }>();

let map: MapboxMap | null = null;
let marker: Marker | null = null;

function current(): LatLng | null {
    return props.lat !== null && props.lng !== null
        ? { lat: props.lat, lng: props.lng }
        : null;
}

function draw(recenter = true) {
    const point = current();
    if (!map || !point) return;

    if (marker) marker.setLngLat(toLngLat(point));
    else {
        const element = pinElement();
        element.title = props.label;
        marker = addMarker(map, element, point, {
            anchor: 'bottom',
            draggable: props.pickable,
        });
        marker.on('dragend', () => {
            const at = marker!.getLngLat();
            emit('pick', { lat: at.lat, lng: at.lng });
        });
    }

    if (recenter) map.easeTo({ center: toLngLat(point) });
}

function onReady(created: MapboxMap) {
    map = created;
    draw(false);

    if (props.pickable) {
        map.on('click', (event) =>
            emit('pick', { lat: event.lngLat.lat, lng: event.lngLat.lng }),
        );
    }
}

watch(
    () => [props.lat, props.lng],
    () => draw(),
);

onBeforeUnmount(() => {
    marker?.remove();
    map = null;
});
</script>

<template>
    <MapView
        class="rounded-xl"
        :center="lat !== null && lng !== null ? { lat, lng } : null"
        :zoom="zoom"
        :height-class="heightClass"
        role="img"
        :aria-label="`Map showing ${label}`"
        @ready="onReady"
    />
</template>
