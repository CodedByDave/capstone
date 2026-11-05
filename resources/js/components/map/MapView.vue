<script setup lang="ts">
import {
    createStandardMap,
    hasMapboxToken,
    LIGHT_PRESETS,
    setLightPreset,
    type LightPreset,
} from '@/lib/mapbox';
import type { LatLng } from '@/lib/maps';
import { MapPinOff, Moon, Sun, Sunset } from 'lucide-vue-next';
import type { Map as MapboxMap } from 'mapbox-gl';
import { onBeforeUnmount, onMounted, ref, shallowRef } from 'vue';

/**
 * A Mapbox Standard map (3D buildings, labels, tilted view) with an optional day/dusk/night
 * toggle. Parents receive the map through `ready` and add their own markers and route lines.
 */
const props = withDefaults(
    defineProps<{
        center?: LatLng | null;
        zoom?: number;
        pitch?: number;
        bearing?: number;
        interactive?: boolean;
        lightToggle?: boolean;
        heightClass?: string;
    }>(),
    {
        center: null,
        zoom: 16,
        pitch: 60,
        bearing: -45,
        interactive: true,
        lightToggle: true,
        heightClass: 'h-72',
    },
);

const emit = defineEmits<{ ready: [map: MapboxMap] }>();

const el = ref<HTMLElement | null>(null);
const map = shallowRef<MapboxMap | null>(null);
const preset = ref<LightPreset>('day');
const failed = ref(!hasMapboxToken);
const problem = ref<string | null>(null);

const presetMeta: Record<LightPreset, { label: string; icon: typeof Sun }> = {
    day: { label: 'Day', icon: Sun },
    dusk: { label: 'Dusk', icon: Sunset },
    night: { label: 'Night', icon: Moon },
};

function choose(next: LightPreset) {
    preset.value = next;
    if (map.value?.isStyleLoaded()) setLightPreset(map.value, next);
}

let resizeObserver: ResizeObserver | null = null;

onMounted(() => {
    if (!el.value || failed.value) return;

    try {
        const created = createStandardMap(el.value, {
            center: props.center,
            zoom: props.zoom,
            pitch: props.pitch,
            bearing: props.bearing,
            interactive: props.interactive,
            lightPreset: preset.value,
        });
        map.value = created;
        created.on('error', (event) => {
            const error = event.error as { status?: number; message?: string };
            console.error('Mapbox error', event.error);

            // A bad or missing token surfaces as an auth error; show the fallback instead of a blank box.
            if (error?.status === 401) {
                failed.value = true;
                return;
            }

            problem.value = error?.message ?? 'The map could not load fully.';
        });
        created.once('load', () => emit('ready', created));

        resizeObserver = new ResizeObserver(() => created.resize());
        resizeObserver.observe(el.value);
    } catch {
        // WebGL is unavailable on some devices.
        failed.value = true;
    }
});

onBeforeUnmount(() => {
    resizeObserver?.disconnect();
    map.value?.remove();
    map.value = null;
});

defineExpose({ map });
</script>

<template>
    <div :class="['relative overflow-hidden bg-slate-200', heightClass]">
        <!-- Inline style: mapbox-gl.css sets position: relative on the container and is unlayered, so it
            would beat Tailwind's absolute/inset utilities and collapse the map to zero height. -->
        <div
            v-if="!failed"
            ref="el"
            style="position: absolute; inset: 0; width: 100%; height: 100%"
        />

        <div
            v-else
            class="flex h-full flex-col items-center justify-center gap-2 px-6 text-center text-sm text-slate-600"
        >
            <MapPinOff class="h-6 w-6" aria-hidden="true" />
            <p>The map is not available right now.</p>
        </div>

        <div
            v-if="!failed && lightToggle"
            class="absolute top-3 right-3 z-10 flex overflow-hidden rounded-xl bg-white shadow-md"
            role="group"
            aria-label="Map lighting"
        >
            <button
                v-for="key in LIGHT_PRESETS"
                :key="key"
                type="button"
                :class="[
                    'flex h-9 w-9 items-center justify-center transition-colors focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:outline-none',
                    preset === key
                        ? 'bg-blue-600 text-white'
                        : 'text-slate-600 hover:bg-slate-100',
                ]"
                :aria-label="`${presetMeta[key].label} lighting`"
                :aria-pressed="preset === key"
                @click="choose(key)"
            >
                <component :is="presetMeta[key].icon" class="h-4 w-4" />
            </button>
        </div>

        <p
            v-if="problem && !failed"
            class="absolute right-3 bottom-3 left-3 z-10 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700 shadow"
            role="alert"
        >
            {{ problem }}
        </p>

        <slot />
    </div>
</template>
