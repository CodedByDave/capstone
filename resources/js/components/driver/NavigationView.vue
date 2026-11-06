<script setup lang="ts">
import MapView from '@/components/map/MapView.vue';
import { useNavigation } from '@/composables/useNavigation';
import { addMarker, drawRouteLine, pinElement, toLngLat } from '@/lib/mapbox';
import type { LatLng } from '@/lib/maps';
import { formatManeuverDistance, type NavStep } from '@/lib/navigation';
import { formatClock, formatDistance, formatDuration } from '@/lib/routing';
import {
    ArrowUp,
    ArrowUpLeft,
    ArrowUpRight,
    CornerUpLeft,
    CornerUpRight,
    Flag,
    List,
    Loader2,
    LocateFixed,
    MapPin,
    Merge,
    RotateCw,
    Split,
    Undo2,
    Volume2,
    VolumeX,
    X,
} from 'lucide-vue-next';
import mapboxgl, { type Map as MapboxMap } from 'mapbox-gl';
import { computed, onBeforeUnmount, onMounted, ref, toRef, watch } from 'vue';

/**
 * Full-screen, in-app turn-by-turn navigation for a rider's current stop: the next turn, a map
 * that follows the rider, spoken prompts, re-routing and arrival.
 */
const props = defineProps<{
    /** The rider's live GPS position. */
    position: LatLng | null;
    destination: {
        lat: number | null;
        lng: number | null;
        label?: string | null;
        address?: string | null;
    };
}>();

const emit = defineEmits<{ close: [] }>();

const destinationPoint = computed<LatLng | null>(() =>
    props.destination.lat !== null && props.destination.lng !== null
        ? { lat: props.destination.lat, lng: props.destination.lng }
        : null,
);

const active = ref(true);
const {
    route,
    progress,
    status,
    heading,
    muted,
    canSpeak,
    currentStep,
    toggleMute,
} = useNavigation(toRef(props, 'position'), destinationPoint, active);

// ─── Map ─────────────────────────────────────────────────────────────────────

const ROUTE_ID = 'nav-route';
let map: MapboxMap | null = null;
let riderMarker: mapboxgl.Marker | null = null;
let destinationMarker: mapboxgl.Marker | null = null;
const following = ref(true);
const showSteps = ref(false);

function riderElement(): HTMLElement {
    const el = document.createElement('span');
    el.style.cssText =
        'display:flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:9999px;background:#2563eb;border:3px solid #fff;box-shadow:0 0 0 8px rgba(37,99,235,.2),0 2px 6px rgba(0,0,0,.4)';
    // An arrow pointing the way the rider is going (the marker itself is rotated).
    el.innerHTML =
        '<svg width="18" height="18" viewBox="0 0 24 24" fill="#fff" aria-hidden="true"><path d="M12 2 20 21 12 16.5 4 21z"/></svg>';
    return el;
}

function drawRoute() {
    if (!map) return;
    drawRouteLine(
        map,
        ROUTE_ID,
        route.value ? route.value.path.map((p) => toLngLat(p)) : null,
        { color: '#2563eb' },
    );
}

function drawDestination() {
    if (!map || !destinationPoint.value) return;
    if (destinationMarker) {
        destinationMarker.setLngLat(toLngLat(destinationPoint.value));
    } else {
        destinationMarker = addMarker(
            map,
            pinElement('#0f766e'),
            destinationPoint.value,
            { anchor: 'bottom' },
        );
    }
}

function follow(animate = true) {
    if (!map || !props.position || !following.value) return;

    map.easeTo({
        center: toLngLat(props.position),
        bearing: heading.value ?? map.getBearing(),
        zoom: 17,
        pitch: 55,
        // Keep the rider below the turn banner and above the bottom panel.
        padding: { top: 180, bottom: 200, left: 0, right: 0 },
        duration: animate ? 900 : 0,
    });
}

function drawRider() {
    if (!map || !props.position) return;

    if (riderMarker) {
        riderMarker.setLngLat(toLngLat(props.position));
    } else {
        riderMarker = new mapboxgl.Marker({
            element: riderElement(),
            rotationAlignment: 'map',
        })
            .setLngLat(toLngLat(props.position))
            .addTo(map);
    }
    if (heading.value !== null) riderMarker.setRotation(heading.value);

    follow();
}

function onReady(created: MapboxMap) {
    map = created;
    // The rider moved the map by hand: stop following until they tap re-center.
    created.on('dragstart', () => {
        following.value = false;
    });
    drawDestination();
    drawRoute();
    drawRider();
    follow(false);
}

function recenter() {
    following.value = true;
    follow();
}

watch(route, drawRoute);
watch(destinationPoint, drawDestination, { deep: true });
watch(
    () => [props.position?.lat, props.position?.lng, heading.value],
    drawRider,
);

// ─── Keep the screen on while navigating ─────────────────────────────────────

let wakeLock: { release: () => Promise<void> } | null = null;

async function keepScreenOn() {
    try {
        const nav = navigator as Navigator & {
            wakeLock?: {
                request: (type: 'screen') => Promise<{
                    release: () => Promise<void>;
                }>;
            };
        };
        wakeLock = (await nav.wakeLock?.request('screen')) ?? null;
    } catch {
        // Not supported or not allowed; the rider's own screen settings apply.
    }
}

// The lock drops when the app goes to the background; take it again on return.
function onVisibility() {
    if (document.visibilityState === 'visible') void keepScreenOn();
}

onMounted(() => {
    void keepScreenOn();
    document.addEventListener('visibilitychange', onVisibility);
});

onBeforeUnmount(() => {
    active.value = false;
    document.removeEventListener('visibilitychange', onVisibility);
    void wakeLock?.release().catch(() => {});
    riderMarker?.remove();
    destinationMarker?.remove();
    map = null;
});

function close() {
    active.value = false;
    emit('close');
}

// ─── Turn banner ─────────────────────────────────────────────────────────────

function maneuverIcon(step: NavStep | null) {
    if (!step) return ArrowUp;
    if (step.type === 'arrive') return Flag;
    if (['roundabout', 'rotary', 'roundabout turn'].includes(step.type)) {
        return RotateCw;
    }
    if (step.type === 'merge') return Merge;
    if (step.type === 'fork') return Split;

    switch (step.modifier) {
        case 'uturn':
            return Undo2;
        case 'left':
        case 'sharp left':
            return CornerUpLeft;
        case 'slight left':
            return ArrowUpLeft;
        case 'right':
        case 'sharp right':
            return CornerUpRight;
        case 'slight right':
            return ArrowUpRight;
        default:
            return ArrowUp;
    }
}

const nextStep = computed(() => progress.value?.nextStep ?? null);

const bannerText = computed(() => {
    switch (status.value) {
        case 'locating':
            return 'Waiting for your GPS location…';
        case 'routing':
            return 'Finding the best route…';
        case 'rerouting':
            return 'Rerouting…';
        case 'unavailable':
            return 'Directions are unavailable right now. Retrying…';
        case 'arrived':
            return 'You have arrived';
        default:
            return nextStep.value?.instruction ?? 'Continue on the route';
    }
});

const remaining = computed(() => {
    const p = progress.value;
    if (!p) return null;

    return {
        time: formatDuration(p.remainingSeconds),
        distance: formatDistance(p.remainingMeters),
        arrival: formatClock(new Date(Date.now() + p.remainingSeconds * 1000)),
    };
});
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex flex-col bg-slate-900"
        role="dialog"
        aria-modal="true"
        aria-label="Turn-by-turn navigation"
    >
        <div class="absolute inset-0">
            <MapView
                :center="position ?? destinationPoint"
                :zoom="17"
                :pitch="55"
                :bearing="0"
                :light-toggle="false"
                height-class="h-full"
                @ready="onReady"
            />
        </div>

        <!-- Turn banner -->
        <div
            class="relative z-10 m-3 rounded-2xl p-4 text-white shadow-lg"
            :class="
                status === 'arrived'
                    ? 'bg-emerald-600'
                    : status === 'unavailable'
                      ? 'bg-amber-600'
                      : 'bg-blue-700'
            "
            aria-live="polite"
        >
            <div class="flex items-center gap-4">
                <div
                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-white/15"
                >
                    <Loader2
                        v-if="
                            status === 'locating' ||
                            status === 'routing' ||
                            status === 'rerouting'
                        "
                        class="h-8 w-8 animate-spin"
                        aria-hidden="true"
                    />
                    <Flag
                        v-else-if="status === 'arrived'"
                        class="h-8 w-8"
                        aria-hidden="true"
                    />
                    <component
                        :is="maneuverIcon(nextStep)"
                        v-else
                        class="h-8 w-8"
                        aria-hidden="true"
                    />
                </div>
                <div class="min-w-0">
                    <p
                        v-if="status === 'navigating' && progress && nextStep"
                        class="text-3xl leading-tight font-bold tabular-nums"
                    >
                        {{
                            formatManeuverDistance(progress.distanceToManeuver)
                        }}
                    </p>
                    <p class="text-base leading-snug font-semibold">
                        {{ bannerText }}
                    </p>
                    <p
                        v-if="status === 'arrived' && destination.address"
                        class="mt-0.5 truncate text-sm text-white/85"
                    >
                        {{ destination.address }}
                    </p>
                    <p
                        v-else-if="status === 'navigating' && currentStep?.name"
                        class="mt-0.5 truncate text-xs text-white/75"
                    >
                        Now on {{ currentStep.name }}
                    </p>
                </div>
            </div>
        </div>

        <div class="flex-1" />

        <!-- Re-center -->
        <div class="relative z-10 flex justify-end px-3 pb-3">
            <button
                v-if="!following"
                type="button"
                class="flex h-12 items-center gap-2 rounded-full bg-white px-4 text-sm font-semibold text-blue-700 shadow-lg focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:outline-none"
                @click="recenter"
            >
                <LocateFixed class="h-5 w-5" aria-hidden="true" />
                Re-center
            </button>
        </div>

        <!-- Bottom panel -->
        <div
            class="relative z-10 rounded-t-3xl bg-white px-5 pt-4 pb-[max(1rem,env(safe-area-inset-bottom))] shadow-[0_-4px_16px_rgba(0,0,0,.15)]"
        >
            <ol
                v-if="showSteps && route"
                class="mb-3 max-h-[40vh] divide-y overflow-y-auto rounded-xl border"
                aria-label="All directions"
            >
                <li
                    v-for="(step, index) in route.steps"
                    :key="index"
                    class="flex items-center gap-3 px-3 py-2.5 text-sm"
                    :class="
                        progress && index === progress.stepIndex + 1
                            ? 'bg-blue-50 font-semibold text-blue-800'
                            : progress && index <= progress.stepIndex
                              ? 'text-slate-400'
                              : 'text-slate-700'
                    "
                    :aria-current="
                        progress && index === progress.stepIndex + 1
                            ? 'step'
                            : undefined
                    "
                >
                    <component
                        :is="maneuverIcon(step)"
                        class="h-5 w-5 shrink-0"
                        aria-hidden="true"
                    />
                    <span class="min-w-0 flex-1">{{ step.instruction }}</span>
                    <span
                        v-if="step.distance > 0"
                        class="shrink-0 text-xs text-slate-500 tabular-nums"
                    >
                        {{ formatDistance(step.distance) }}
                    </span>
                </li>
            </ol>

            <div class="flex items-center gap-3">
                <div class="min-w-0 flex-1">
                    <template v-if="status === 'arrived'">
                        <p
                            class="flex items-center gap-1.5 text-lg font-bold text-emerald-700"
                        >
                            <MapPin class="h-5 w-5" aria-hidden="true" />
                            {{ destination.label ?? 'Destination' }}
                        </p>
                        <p class="text-xs text-slate-500">
                            Continue the job steps on the Map page.
                        </p>
                    </template>
                    <template v-else-if="remaining">
                        <p class="text-2xl font-bold text-emerald-700">
                            {{ remaining.time }}
                        </p>
                        <p class="text-sm text-slate-500">
                            {{ remaining.distance }} · Arrive
                            {{ remaining.arrival }}
                        </p>
                    </template>
                    <p v-else class="text-sm text-slate-500">
                        {{ destination.label ?? 'Your stop' }}
                    </p>
                </div>

                <button
                    v-if="route"
                    type="button"
                    class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-700 focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:outline-none"
                    :aria-label="
                        showSteps
                            ? 'Hide all directions'
                            : 'Show all directions'
                    "
                    :aria-expanded="showSteps"
                    @click="showSteps = !showSteps"
                >
                    <List class="h-5 w-5" />
                </button>
                <button
                    v-if="canSpeak"
                    type="button"
                    class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-700 focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:outline-none"
                    :aria-label="muted ? 'Turn voice on' : 'Mute voice'"
                    :aria-pressed="muted"
                    @click="toggleMute"
                >
                    <VolumeX v-if="muted" class="h-5 w-5" />
                    <Volume2 v-else class="h-5 w-5" />
                </button>
                <button
                    type="button"
                    class="flex h-12 items-center gap-1.5 rounded-full bg-red-600 px-4 text-sm font-semibold text-white focus-visible:ring-2 focus-visible:ring-red-400 focus-visible:outline-none"
                    @click="close"
                >
                    <X class="h-5 w-5" aria-hidden="true" />
                    {{ status === 'arrived' ? 'Done' : 'Exit' }}
                </button>
            </div>
        </div>
    </div>
</template>
