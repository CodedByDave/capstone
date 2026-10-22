<script setup lang="ts">
import { createBaseMap, dotIcon, formatPeso } from '@/lib/maps';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import L from 'leaflet';
import {
    Camera,
    CheckCircle2,
    Clock,
    MapPin,
    Navigation,
    Route,
    Truck,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

export type DeliveryStatus =
    | 'pending'
    | 'assigned'
    | 'picked_up'
    | 'delivered'
    | 'failed';

type Point = { lat: number | null; lng: number | null };

export interface DeliveryTracking {
    public_id: string;
    status: DeliveryStatus;
    is_active: boolean;
    delivery_address: string | null;
    distance_km: number | null;
    delivery_fee: number;
    rider_name: string | null;
    courier: 'own_rider' | 'lalamove';
    courier_info: {
        tracking_url: string | null;
        driver_name: string | null;
        driver_phone: string | null;
        vehicle_plate: string | null;
    } | null;
    photos: { public_id: string; uploaded_at: string | null }[];
    shop: Point & { name: string | null };
    destination: Point;
    rider: { lat: number; lng: number; located_at: string | null } | null;
    timeline: {
        requested_at: string | null;
        assigned_at: string | null;
        picked_up_at: string | null;
        delivered_at: string | null;
        failed_at: string | null;
    };
}

const props = defineProps<{
    paymentPublicId: string;
    delivery: DeliveryTracking;
}>();

const tracking = ref<DeliveryTracking>(props.delivery);
watch(
    () => props.delivery,
    (value) => (tracking.value = value),
);

const statusConfig: Record<
    DeliveryStatus,
    { label: string; classes: string; icon: typeof Truck }
> = {
    pending: {
        label: 'Pending',
        classes:
            'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
        icon: Clock,
    },
    assigned: {
        label: 'Assigned',
        classes:
            'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300',
        icon: Truck,
    },
    picked_up: {
        label: 'Picked Up',
        classes:
            'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300',
        icon: Truck,
    },
    delivered: {
        label: 'Delivered',
        classes:
            'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
        icon: CheckCircle2,
    },
    failed: {
        label: 'Failed',
        classes: 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300',
        icon: X,
    },
};

const status = computed(() => statusConfig[tracking.value.status]);

const timeline = computed(() => {
    const t = tracking.value.timeline;
    const steps = [
        { key: 'pending', label: 'Requested', at: t.requested_at },
        {
            key: 'assigned',
            label:
                tracking.value.courier === 'lalamove'
                    ? 'Courier booked'
                    : 'Rider assigned',
            at: t.assigned_at,
        },
        { key: 'picked_up', label: 'Picked up', at: t.picked_up_at },
    ];
    steps.push(
        tracking.value.status === 'failed'
            ? { key: 'failed', label: 'Delivery failed', at: t.failed_at }
            : { key: 'delivered', label: 'Delivered', at: t.delivered_at },
    );
    const order = ['pending', 'assigned', 'picked_up', 'delivered'];
    const current =
        tracking.value.status === 'failed'
            ? order.length
            : order.indexOf(tracking.value.status);

    return steps.map((step, index) => ({
        ...step,
        done: step.key === 'failed' || index <= current,
    }));
});

function formatTime(iso: string | null) {
    if (!iso) return null;
    return new Date(iso).toLocaleString('en-PH', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}

const riderUpdatedAgo = computed(() => {
    const at = tracking.value.rider?.located_at;
    if (!at) return null;
    const seconds = Math.max(
        0,
        Math.round((Date.now() - new Date(at).getTime()) / 1000),
    );
    if (seconds < 60) return 'just now';
    const minutes = Math.round(seconds / 60);
    return `${minutes} min ago`;
});

// ─── Map ─────────────────────────────────────────────────────────────────────

const mapEl = ref<HTMLElement | null>(null);
let map: L.Map | null = null;
let layers: L.LayerGroup | null = null;
let fitted = false;

function hasPoint(point: Point | null): point is { lat: number; lng: number } {
    return !!point && point.lat !== null && point.lng !== null;
}

const hasMap = computed(
    () => hasPoint(tracking.value.destination) || hasPoint(tracking.value.shop),
);

function drawMap() {
    if (!map || !layers) return;
    layers.clearLayers();

    const { shop, destination, rider } = tracking.value;
    const bounds: L.LatLngTuple[] = [];

    if (hasPoint(shop)) {
        L.marker([shop.lat, shop.lng], {
            icon: dotIcon('#7c3aed'),
            keyboard: false,
        })
            .bindTooltip(shop.name ?? 'Shop')
            .addTo(layers);
        bounds.push([shop.lat, shop.lng]);
    }

    if (hasPoint(destination)) {
        L.marker([destination.lat, destination.lng], {
            icon: dotIcon('#059669', 20),
            keyboard: false,
        })
            .bindTooltip('Delivery address')
            .addTo(layers);
        bounds.push([destination.lat, destination.lng]);
    }

    if (rider) {
        L.marker([rider.lat, rider.lng], {
            icon: dotIcon('#ea580c', 22),
            keyboard: false,
            zIndexOffset: 1000,
        })
            .bindTooltip(tracking.value.rider_name ?? 'Rider')
            .addTo(layers);
        bounds.push([rider.lat, rider.lng]);
    }

    // Line from where the laundry is now (rider, else shop) to the customer.
    const from = rider ?? (hasPoint(shop) ? shop : null);
    if (from && hasPoint(destination)) {
        L.polyline(
            [
                [from.lat, from.lng],
                [destination.lat, destination.lng],
            ],
            {
                color: rider ? '#ea580c' : '#6366f1',
                weight: 3,
                dashArray: rider ? undefined : '6 8',
            },
        ).addTo(layers);
    }

    if (!fitted && bounds.length > 0) {
        if (bounds.length === 1) map.setView(bounds[0], 15);
        else map.fitBounds(bounds, { padding: [30, 30], maxZoom: 16 });
        fitted = true;
    }
}

function recenter() {
    fitted = false;
    drawMap();
}

onMounted(() => {
    if (mapEl.value && hasMap.value) {
        map = createBaseMap(mapEl.value);
        layers = L.layerGroup().addTo(map);
        drawMap();
    }
    startPolling();
});

watch(tracking, drawMap, { deep: true });

// ─── Polling ─────────────────────────────────────────────────────────────────

const POLL_ACTIVE_MS = 5_000;
const POLL_PENDING_MS = 15_000;
const FAILURES_BEFORE_TOAST = 3;

let pollTimer: ReturnType<typeof setTimeout> | null = null;
let failures = 0;
let failureToastShown = false;
let stopped = false;

function shouldPoll() {
    return ['pending', 'assigned', 'picked_up'].includes(tracking.value.status);
}

function startPolling() {
    if (stopped || !shouldPoll()) return;
    const delay =
        tracking.value.status === 'pending' ? POLL_PENDING_MS : POLL_ACTIVE_MS;
    pollTimer = setTimeout(poll, delay);
}

async function poll() {
    if (stopped) return;
    // Skip work while the tab is hidden; resume on the next tick.
    if (document.hidden) {
        startPolling();
        return;
    }

    const previous = tracking.value.status;
    try {
        const { data } = await axios.get<DeliveryTracking>(
            `/user/orders/${props.paymentPublicId}/tracking`,
        );
        if (stopped) return;
        tracking.value = data;
        failures = 0;
        failureToastShown = false;

        if (data.status !== previous) {
            // Refresh billing and order details when the delivery moves on.
            router.reload({ only: ['order'] });
        }
    } catch {
        failures += 1;
        if (failures >= FAILURES_BEFORE_TOAST && !failureToastShown) {
            failureToastShown = true;
            toast.error(
                'Live tracking is having trouble connecting. Retrying in the background…',
            );
        }
    }
    startPolling();
}

onUnmounted(() => {
    stopped = true;
    if (pollTimer) clearTimeout(pollTimer);
    map?.remove();
    map = null;
});
</script>

<template>
    <div
        class="space-y-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
    >
        <div class="flex items-center justify-between gap-2">
            <p
                class="text-xs font-semibold tracking-wide text-gray-400 uppercase"
            >
                Order Tracking
            </p>
            <span
                class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                :class="status.classes"
            >
                <component :is="status.icon" class="h-3.5 w-3.5" />
                {{ status.label }}
            </span>
        </div>

        <!-- Map -->
        <div
            v-if="hasMap"
            class="relative isolate overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700"
        >
            <div
                ref="mapEl"
                class="h-56 w-full sm:h-72"
                role="img"
                aria-label="Map showing the shop, your delivery address and the rider"
            />
            <button
                type="button"
                class="absolute right-2 bottom-2 z-[500] flex items-center gap-1 rounded-lg bg-white/95 px-2 py-1 text-xs font-medium text-gray-700 shadow dark:bg-gray-900/95 dark:text-gray-200"
                @click="recenter"
            >
                <Navigation class="h-3.5 w-3.5" />
                Recenter
            </button>
        </div>
        <div
            v-if="hasMap"
            class="flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-gray-500 dark:text-gray-400"
        >
            <span class="flex items-center gap-1"
                ><span class="h-2.5 w-2.5 rounded-full bg-violet-600" />
                Shop</span
            >
            <span class="flex items-center gap-1"
                ><span class="h-2.5 w-2.5 rounded-full bg-emerald-600" />
                You</span
            >
            <span class="flex items-center gap-1"
                ><span class="h-2.5 w-2.5 rounded-full bg-orange-600" />
                Rider</span
            >
            <span v-if="riderUpdatedAgo" class="ml-auto">
                Rider location updated {{ riderUpdatedAgo }}
            </span>
            <span
                v-else-if="tracking.is_active && tracking.status !== 'pending'"
                class="ml-auto"
            >
                Waiting for the rider's live location…
            </span>
        </div>

        <!-- Details -->
        <div class="space-y-2 text-xs text-gray-500 dark:text-gray-400">
            <div
                v-if="tracking.delivery_address"
                class="flex items-start gap-2"
            >
                <MapPin class="mt-0.5 h-3.5 w-3.5 shrink-0 text-gray-400" />
                <span>{{ tracking.delivery_address }}</span>
            </div>
            <div
                v-if="tracking.courier === 'lalamove' && tracking.courier_info"
                class="space-y-1.5 rounded-xl bg-orange-50 p-3 dark:bg-orange-500/10"
            >
                <div class="flex items-center gap-2">
                    <Truck class="h-3.5 w-3.5 shrink-0 text-orange-500" />
                    <span class="font-medium text-gray-700 dark:text-gray-200"
                        >Delivered by Lalamove</span
                    >
                </div>
                <p v-if="tracking.courier_info.driver_name" class="pl-5.5">
                    Driver:
                    <span
                        class="font-medium text-gray-700 dark:text-gray-200"
                        >{{ tracking.courier_info.driver_name }}</span
                    >
                    <span v-if="tracking.courier_info.vehicle_plate">
                        · {{ tracking.courier_info.vehicle_plate }}</span
                    >
                </p>
                <p v-if="tracking.courier_info.driver_phone" class="pl-5.5">
                    Phone: {{ tracking.courier_info.driver_phone }}
                </p>
                <a
                    v-if="tracking.courier_info.tracking_url"
                    :href="tracking.courier_info.tracking_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1 pl-5.5 font-medium text-orange-600 hover:underline"
                >
                    <Navigation class="h-3.5 w-3.5" />
                    Track on Lalamove
                </a>
            </div>
            <div
                v-else-if="tracking.rider_name"
                class="flex items-center gap-2"
            >
                <Truck class="h-3.5 w-3.5 shrink-0 text-gray-400" />
                <span
                    >Rider:
                    <span
                        class="font-medium text-gray-700 dark:text-gray-200"
                        >{{ tracking.rider_name }}</span
                    ></span
                >
            </div>
            <div class="flex items-center gap-2">
                <Route class="h-3.5 w-3.5 shrink-0 text-gray-400" />
                <span>
                    <template v-if="tracking.distance_km !== null"
                        >{{ tracking.distance_km.toFixed(1) }} km ·
                    </template>
                    Delivery fee
                    <span
                        class="font-medium text-gray-700 dark:text-gray-200"
                        >{{
                            tracking.delivery_fee > 0
                                ? formatPeso(tracking.delivery_fee)
                                : 'Free'
                        }}</span
                    >
                </span>
            </div>
        </div>

        <!-- Proof photos from the shop -->
        <div v-if="tracking.photos.length > 0" class="space-y-2">
            <p
                class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400"
            >
                <Camera class="h-3.5 w-3.5" />
                Photos from the shop
            </p>
            <div class="grid grid-cols-3 gap-2">
                <a
                    v-for="photo in tracking.photos"
                    :key="photo.public_id"
                    :href="`/user/orders/delivery-photos/${photo.public_id}`"
                    target="_blank"
                    rel="noopener"
                    class="aspect-square overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                >
                    <img
                        :src="`/user/orders/delivery-photos/${photo.public_id}`"
                        alt="Delivery photo from the shop"
                        class="h-full w-full object-cover"
                        loading="lazy"
                    />
                </a>
            </div>
        </div>

        <!-- Timeline -->
        <ol class="space-y-0 pt-1">
            <li
                v-for="(step, index) in timeline"
                :key="step.key"
                class="flex gap-3"
            >
                <div class="flex flex-col items-center">
                    <span
                        class="flex h-5 w-5 items-center justify-center rounded-full border-2"
                        :class="
                            step.key === 'failed'
                                ? 'border-red-500 bg-red-500'
                                : step.done
                                  ? 'border-blue-600 bg-blue-600'
                                  : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900'
                        "
                    >
                        <X
                            v-if="step.key === 'failed'"
                            class="h-3 w-3 text-white"
                        />
                        <CheckCircle2
                            v-else-if="step.done"
                            class="h-3 w-3 text-white"
                        />
                    </span>
                    <span
                        v-if="index < timeline.length - 1"
                        class="h-6 w-0.5"
                        :class="
                            timeline[index + 1].done
                                ? 'bg-blue-600'
                                : 'bg-gray-200 dark:bg-gray-700'
                        "
                    />
                </div>
                <div class="-mt-0.5 pb-2">
                    <p
                        class="text-sm font-medium"
                        :class="
                            step.key === 'failed'
                                ? 'text-red-600 dark:text-red-400'
                                : step.done
                                  ? 'text-gray-800 dark:text-gray-100'
                                  : 'text-gray-400'
                        "
                    >
                        {{ step.label }}
                    </p>
                    <p v-if="step.at" class="text-xs text-gray-400">
                        {{ formatTime(step.at) }}
                    </p>
                </div>
            </li>
        </ol>
    </div>
</template>
