<script setup lang="ts">
import LiveTrackingMap, {
    type TrackingEta,
} from '@/components/tracking/LiveTrackingMap.vue';
import VehicleIllustration from '@/components/tracking/VehicleIllustration.vue';
import { formatClock, formatDuration } from '@/lib/routing';
import type { VehicleKind } from '@/lib/trackingMap';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import {
    ArrowLeft,
    CheckCircle2,
    ChevronRight,
    Circle,
    ExternalLink,
    MapPinOff,
    Package,
    Phone,
    ReceiptText,
    XCircle,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

type Point = { lat: number | null; lng: number | null };

interface Leg {
    public_id: string;
    type: 'pickup' | 'delivery';
    status: 'pending' | 'assigned' | 'picked_up' | 'delivered' | 'failed';
    delivery_address: string | null;
    rider_name: string | null;
    rider_phone: string | null;
    vehicle: VehicleKind | null;
    courier: 'own_rider' | 'lalamove';
    courier_info: {
        tracking_url: string | null;
        driver_name: string | null;
        driver_phone: string | null;
        vehicle_plate: string | null;
    } | null;
    shop: Point & { name: string | null };
    destination: Point;
    rider: {
        lat: number;
        lng: number;
        located_at: string | null;
        is_stale: boolean;
    } | null;
    timeline: {
        requested_at: string | null;
        assigned_at: string | null;
        picked_up_at: string | null;
        delivered_at: string | null;
        failed_at: string | null;
    };
}

interface Tracking {
    order: { order_number: string; payment_public_id: string };
    pickup: Leg | null;
    delivery: Leg | null;
    active: 'pickup' | 'delivery' | null;
}

const props = defineProps<{ tracking: Tracking }>();

const tracking = ref<Tracking>(props.tracking);
const eta = ref<TrackingEta | null>(null);

const leg = computed<Leg | null>(() =>
    tracking.value.active ? tracking.value[tracking.value.active] : null,
);

const isOpen = computed(
    () => !!leg.value && !['delivered', 'failed'].includes(leg.value.status),
);

/** The stop the rider is heading to right now. */
const target = computed<'shop' | 'customer' | null>(() => {
    const current = leg.value;
    if (!current || !isOpen.value) return null;

    const carrying = current.status === 'picked_up';

    if (current.type === 'pickup') return carrying ? 'shop' : 'customer';

    return carrying ? 'customer' : 'shop';
});

const targetPoint = computed<Point>(() => {
    const current = leg.value;
    if (!current || !target.value) return { lat: null, lng: null };

    return target.value === 'shop' ? current.shop : current.destination;
});

const targetLabel = computed(() => {
    if (target.value === 'shop') return 'SHOP';

    return leg.value?.type === 'pickup' ? 'PICKUP' : 'DROPOFF';
});

const shopName = computed(() => leg.value?.shop.name ?? 'the shop');
const etaTime = computed(() =>
    eta.value ? formatClock(eta.value.arriveAt) : null,
);

// The estimate counts down on its own between route updates.
const now = ref(Date.now());
let clock: ReturnType<typeof setInterval> | null = null;
const remainingSeconds = computed(() =>
    eta.value
        ? Math.max(0, (eta.value.arriveAt.getTime() - now.value) / 1000)
        : null,
);
const arrivingSoon = computed(
    () => remainingSeconds.value !== null && remainingSeconds.value <= 180,
);

const headline = computed(() => {
    const current = leg.value;
    if (!current) return 'No trip yet';

    if (current.type === 'pickup') {
        return {
            pending: 'Finding a rider',
            assigned: arrivingSoon.value
                ? 'Rider is almost there'
                : 'Rider is on the way to you',
            picked_up: 'Laundry collected',
            delivered: 'Laundry received at the shop',
            failed: "Pickup didn't go through",
        }[current.status];
    }

    return {
        pending: 'Preparing your delivery',
        assigned: 'Rider is heading to the shop',
        picked_up: arrivingSoon.value
            ? 'Arriving at dropoff'
            : 'On the way to you',
        delivered: 'Delivered',
        failed: "Delivery couldn't be completed",
    }[current.status];
});

const subtitle = computed(() => {
    const current = leg.value;
    if (!current) return '';

    const at = etaTime.value;

    if (current.status === 'pending') {
        return 'A rider will be assigned shortly.';
    }
    if (current.status === 'delivered') {
        return current.type === 'pickup'
            ? `${shopName.value} will start on your laundry soon.`
            : 'Enjoy your fresh laundry!';
    }
    if (current.status === 'failed') {
        return `Please contact ${shopName.value} for help.`;
    }
    if (current.courier === 'lalamove') {
        return 'Your courier is tracked on Lalamove.';
    }
    if (!current.rider) {
        return 'Waiting for the rider to share their location.';
    }
    if (current.rider.is_stale) {
        return "The rider's location hasn't updated recently.";
    }
    if (current.type === 'pickup') {
        return current.status === 'assigned'
            ? at
                ? `They'll reach your address at ${at}`
                : 'On the way to collect your laundry.'
            : at
              ? `Heading to ${shopName.value}, arriving at ${at}`
              : `Heading to ${shopName.value}.`;
    }

    return current.status === 'assigned'
        ? 'To pick up your clean laundry.'
        : at
          ? `We'll arrive at your address at ${at}`
          : 'On the way to your address.';
});

const noPin = computed(
    () =>
        isOpen.value &&
        leg.value?.status !== 'pending' &&
        target.value === 'customer' &&
        (targetPoint.value.lat === null || targetPoint.value.lng === null),
);

const riderInitials = computed(() => {
    const name = leg.value?.courier_info?.driver_name ?? leg.value?.rider_name;
    if (!name) return '•';

    return name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join('');
});

const callNumber = computed(
    () =>
        leg.value?.courier_info?.driver_phone ?? leg.value?.rider_phone ?? null,
);

const steps = computed(() => {
    const current = leg.value;
    if (!current) return [];

    const t = current.timeline;
    const pickup = current.type === 'pickup';
    const list = [
        { key: 'requested', label: 'Requested', at: t.requested_at },
        { key: 'assigned', label: 'Rider assigned', at: t.assigned_at },
        {
            key: 'picked_up',
            label: pickup ? 'Laundry collected' : 'Picked up from the shop',
            at: t.picked_up_at,
        },
        current.status === 'failed'
            ? { key: 'failed', label: 'Not completed', at: t.failed_at }
            : {
                  key: 'delivered',
                  label: pickup ? 'Received at the shop' : 'Delivered',
                  at: t.delivered_at,
              },
    ];

    return list.map((step) => ({ ...step, done: step.at !== null }));
});

function formatDateTime(iso: string | null) {
    if (!iso) return null;

    return new Date(iso).toLocaleString('en-PH', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}

const legSummary = computed(() =>
    (['pickup', 'delivery'] as const)
        .filter((key) => tracking.value[key] !== null)
        .map((key) => {
            const item = tracking.value[key] as Leg;

            return {
                key,
                label: key === 'pickup' ? 'Pickup' : 'Delivery',
                state:
                    item.status === 'delivered'
                        ? 'done'
                        : item.status === 'failed'
                          ? 'failed'
                          : tracking.value.active === key
                            ? 'current'
                            : 'waiting',
            };
        }),
);

// ─── Live updates ────────────────────────────────────────────────────────────

const POLL_ACTIVE_MS = 5_000;
const POLL_WAITING_MS = 15_000;
// Between the pickup and the delivery back there is nothing moving, but the delivery appears
// by itself once the order is finished, so the page keeps watching slowly.
const POLL_BETWEEN_TRIPS_MS = 30_000;
const FAILURES_BEFORE_WARNING = 3;

let timer: ReturnType<typeof setTimeout> | null = null;
let stopped = false;
let failures = 0;
const connectionLost = ref(false);

function nextDelay(): number | null {
    if (isOpen.value) {
        return leg.value?.status === 'pending'
            ? POLL_WAITING_MS
            : POLL_ACTIVE_MS;
    }

    // The pickup is over; the delivery back may still be created.
    return tracking.value.delivery === null ? POLL_BETWEEN_TRIPS_MS : null;
}

function schedule() {
    const delay = nextDelay();
    if (stopped || delay === null) return;

    timer = setTimeout(poll, delay);
}

async function poll() {
    if (stopped) return;
    if (document.hidden) {
        schedule();
        return;
    }

    try {
        const { data } = await axios.get<Tracking>(
            `/user/orders/${props.tracking.order.payment_public_id}/track/data`,
        );
        if (!stopped) tracking.value = data;
        failures = 0;
        connectionLost.value = false;
    } catch (error) {
        // Keep the last known position. A signed-out session or a missing order will not fix
        // itself, so stop asking; anything else is retried.
        const status = axios.isAxiosError(error)
            ? error.response?.status
            : undefined;
        failures += 1;
        if (failures >= FAILURES_BEFORE_WARNING) connectionLost.value = true;
        if (status !== undefined && [401, 403, 404, 419].includes(status)) {
            connectionLost.value = true;
            stopped = true;
            return;
        }
    }

    schedule();
}

onMounted(() => {
    clock = setInterval(() => (now.value = Date.now()), 10_000);
    schedule();
});
onUnmounted(() => {
    stopped = true;
    if (clock) clearInterval(clock);
    if (timer) clearTimeout(timer);
});
</script>

<template>
    <Head title="Live tracking" />

    <div
        class="flex h-dvh flex-col bg-slate-100 text-gray-900 dark:bg-gray-950 dark:text-gray-100"
    >
        <!-- Map -->
        <div class="relative h-[56dvh] min-h-[300px] shrink-0">
            <LiveTrackingMap
                :vehicle="leg?.vehicle ?? null"
                :rider="isOpen ? (leg?.rider ?? null) : null"
                :shop="leg?.shop ?? { lat: null, lng: null }"
                :target="targetPoint"
                :target-label="targetLabel"
                :active="isOpen"
                @eta="eta = $event"
            />

            <Link
                :href="`/user/orders/${tracking.order.payment_public_id}`"
                class="absolute top-4 left-4 z-[500] flex h-12 w-12 items-center justify-center rounded-full bg-white text-gray-900 shadow-lg ring-1 ring-black/5"
                aria-label="Back to your order"
            >
                <ArrowLeft class="h-5 w-5" aria-hidden="true" />
            </Link>
        </div>

        <!-- Sheet -->
        <main
            class="relative z-[600] -mt-6 flex-1 space-y-3 overflow-y-auto rounded-t-[2rem] bg-gradient-to-b from-slate-50 to-slate-200 px-4 pt-3 pb-8 shadow-[0_-8px_24px_rgba(15,23,42,0.12)] dark:from-gray-900 dark:to-gray-950"
        >
            <div
                class="mx-auto mb-1 h-1.5 w-12 rounded-full bg-gray-300 dark:bg-gray-700"
                aria-hidden="true"
            />

            <section
                class="mx-auto max-w-xl rounded-[1.75rem] bg-white px-5 py-6 text-center shadow-sm dark:bg-gray-900"
                aria-live="polite"
            >
                <p
                    v-if="connectionLost"
                    class="mb-3 rounded-xl bg-amber-50 px-3 py-2 text-xs text-amber-800 dark:bg-amber-500/10 dark:text-amber-200"
                    role="status"
                >
                    Live updates are paused. Check your connection, or reload
                    the page.
                </p>
                <h1 class="text-3xl leading-tight font-semibold tracking-tight">
                    {{ headline }}
                </h1>
                <p class="mt-2 text-base text-gray-500 dark:text-gray-400">
                    {{ subtitle }}
                </p>

                <p
                    v-if="noPin"
                    class="mx-auto mt-3 flex max-w-sm items-start gap-2 rounded-xl bg-amber-50 px-3 py-2 text-left text-xs text-amber-800 dark:bg-amber-500/10 dark:text-amber-200"
                >
                    <MapPinOff
                        class="mt-0.5 h-4 w-4 shrink-0"
                        aria-hidden="true"
                    />
                    This stop wasn't pinned on the map, so the route isn't
                    shown. The rider can still reach you at the address below.
                </p>
                <p
                    v-if="leg?.delivery_address"
                    class="mt-3 text-xs text-gray-400"
                >
                    {{ leg.delivery_address }}
                </p>

                <div class="mt-5 space-y-3">
                    <a
                        v-if="callNumber"
                        :href="`tel:${callNumber}`"
                        class="flex w-full items-center justify-center gap-2 rounded-2xl border border-gray-300 px-4 py-3.5 text-lg font-medium transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                    >
                        <Phone class="h-5 w-5" aria-hidden="true" />
                        Call rider
                    </a>
                    <a
                        v-if="leg?.courier_info?.tracking_url"
                        :href="leg.courier_info.tracking_url"
                        target="_blank"
                        rel="noopener"
                        class="flex w-full items-center justify-center gap-2 rounded-2xl border border-gray-300 px-4 py-3.5 text-lg font-medium transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                    >
                        <ExternalLink class="h-5 w-5" aria-hidden="true" />
                        Track on Lalamove
                    </a>
                    <Link
                        :href="`/user/orders/${tracking.order.payment_public_id}`"
                        class="flex w-full items-center justify-center gap-2 rounded-2xl border border-gray-300 px-4 py-3.5 text-lg font-medium transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                    >
                        <ReceiptText class="h-5 w-5" aria-hidden="true" />
                        View order #{{ tracking.order.order_number }}
                    </Link>
                </div>
            </section>

            <section
                v-if="leg"
                class="mx-auto max-w-xl rounded-[1.75rem] bg-white px-5 pt-4 pb-5 shadow-sm dark:bg-gray-900"
            >
                <div class="flex flex-col items-center">
                    <div
                        v-if="leg.rider_name || leg.courier_info?.driver_name"
                        class="relative z-10 -mb-2 flex items-center gap-2 rounded-full bg-gray-900 py-1.5 pr-3 pl-1.5 text-white"
                    >
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-violet-300 text-xs font-semibold"
                        >
                            {{ riderInitials }}
                        </span>
                        <span class="text-sm font-medium">{{
                            leg.courier_info?.driver_name ?? leg.rider_name
                        }}</span>
                        <ChevronRight
                            class="h-4 w-4 opacity-70"
                            aria-hidden="true"
                        />
                    </div>
                    <VehicleIllustration :kind="leg.vehicle ?? 'motorcycle'" />
                    <p
                        v-if="leg.courier_info?.vehicle_plate"
                        class="mt-1 rounded-md border border-gray-300 px-2 py-0.5 text-xs font-semibold tracking-widest"
                    >
                        {{ leg.courier_info.vehicle_plate }}
                    </p>
                    <p
                        v-if="eta && isOpen"
                        class="mt-2 text-sm text-gray-500 dark:text-gray-400"
                    >
                        About
                        {{ formatDuration(remainingSeconds ?? eta.seconds) }}
                        away{{ eta.isRoad ? '' : ' (estimate)' }}
                    </p>
                </div>

                <div
                    v-if="legSummary.length > 1"
                    class="mt-4 flex items-center justify-center gap-2 text-xs font-medium"
                >
                    <span
                        v-for="item in legSummary"
                        :key="item.key"
                        class="flex items-center gap-1 rounded-full px-3 py-1"
                        :class="{
                            'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300':
                                item.state === 'done',
                            'bg-gray-900 text-white': item.state === 'current',
                            'bg-gray-100 text-gray-500 dark:bg-gray-800':
                                item.state === 'waiting',
                            'bg-red-50 text-red-700': item.state === 'failed',
                        }"
                    >
                        <CheckCircle2
                            v-if="item.state === 'done'"
                            class="h-3.5 w-3.5"
                            aria-hidden="true"
                        />
                        <Package
                            v-else
                            class="h-3.5 w-3.5"
                            aria-hidden="true"
                        />
                        {{ item.label }}
                    </span>
                </div>
            </section>

            <section
                v-if="leg"
                class="mx-auto max-w-xl rounded-[1.75rem] bg-white px-5 py-5 shadow-sm dark:bg-gray-900"
            >
                <p
                    class="mb-3 text-xs font-semibold tracking-wide text-gray-400 uppercase"
                >
                    {{ leg.type === 'pickup' ? 'Pickup' : 'Delivery' }} progress
                </p>
                <ol class="space-y-3">
                    <li
                        v-for="step in steps"
                        :key="step.key"
                        class="flex items-start gap-3"
                    >
                        <XCircle
                            v-if="step.key === 'failed'"
                            class="mt-0.5 h-5 w-5 shrink-0 text-red-500"
                            aria-hidden="true"
                        />
                        <CheckCircle2
                            v-else-if="step.done"
                            class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500"
                            aria-hidden="true"
                        />
                        <Circle
                            v-else
                            class="mt-0.5 h-5 w-5 shrink-0 text-gray-300"
                            aria-hidden="true"
                        />
                        <div>
                            <p
                                class="text-sm font-medium"
                                :class="step.done ? '' : 'text-gray-400'"
                            >
                                {{ step.label }}
                            </p>
                            <p v-if="step.at" class="text-xs text-gray-400">
                                {{ formatDateTime(step.at) }}
                            </p>
                        </div>
                    </li>
                </ol>
            </section>
        </main>
    </div>
</template>
