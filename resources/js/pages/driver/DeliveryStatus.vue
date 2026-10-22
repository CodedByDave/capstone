<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import {
    CheckCircle2,
    ChevronRight,
    Clock,
    ExternalLink,
    Loader2,
    LocateFixed,
    MapPin,
    Package,
    Phone,
    Truck,
    User,
    XCircle,
} from 'lucide-vue-next';
import { computed, onUnmounted, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Delivery {
    id: number;
    token: string;
    status: string;
    customer_name: string;
    customer_phone: string | null;
    delivery_address: string | null;
    destination_lat: number | null;
    destination_lng: number | null;
    notes: string | null;
    order_number: string | null;
    shop_name: string | null;
    rider_name: string | null;
    assigned_at: string | null;
    picked_up_at: string | null;
    delivered_at: string | null;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{ delivery: Delivery }>();

// ─── Flash toast ──────────────────────────────────────────────────────────────

const page = usePage();
// Watched (not just on mount) because status updates keep this page mounted
// so live location sharing is not interrupted.
watch(
    () =>
        (page.props as any).toast as
            | { type: string; message: string }
            | undefined,
    (flash) => {
        if (!flash?.message) return;
        if (flash.type === 'success') toast.success(flash.message);
        else toast.error(flash.message);
    },
    { immediate: true },
);

// ─── Status config ────────────────────────────────────────────────────────────

const STATUS: Record<string, { label: string; color: string; icon: any }> = {
    pending: {
        label: 'Pending',
        color: 'text-amber-600 bg-amber-50 border-amber-200',
        icon: Clock,
    },
    assigned: {
        label: 'Assigned',
        color: 'text-blue-600 bg-blue-50 border-blue-200',
        icon: User,
    },
    picked_up: {
        label: 'Picked Up',
        color: 'text-violet-600 bg-violet-50 border-violet-200',
        icon: Truck,
    },
    delivered: {
        label: 'Delivered',
        color: 'text-green-600 bg-green-50 border-green-200',
        icon: CheckCircle2,
    },
    failed: {
        label: 'Failed',
        color: 'text-red-600 bg-red-50 border-red-200',
        icon: XCircle,
    },
};

// Timeline steps shown to the rider
const STEPS = [
    { key: 'assigned', label: 'Assigned', timeKey: 'assigned_at' },
    { key: 'picked_up', label: 'Picked Up', timeKey: 'picked_up_at' },
    { key: 'delivered', label: 'Delivered', timeKey: 'delivered_at' },
];

const statusOrder = ['pending', 'assigned', 'picked_up', 'delivered', 'failed'];

function stepDone(key: string) {
    return (
        statusOrder.indexOf(props.delivery.status) >= statusOrder.indexOf(key)
    );
}

// ─── Next action ─────────────────────────────────────────────────────────────

const NEXT: Record<string, { status: string; label: string; color: string }> = {
    assigned: {
        status: 'picked_up',
        label: 'Mark as Picked Up',
        color: 'bg-violet-600 hover:bg-violet-700',
    },
    picked_up: {
        status: 'delivered',
        label: 'Mark as Delivered',
        color: 'bg-green-600 hover:bg-green-700',
    },
};

const next = computed(() => NEXT[props.delivery.status] ?? null);

// ─── Fail action ──────────────────────────────────────────────────────────────

const showFailForm = ref(false);
const failNotes = ref('');
const submitting = ref(false);

function submitStatus(status: string, notes?: string) {
    submitting.value = true;
    router.patch(
        `/driver/${props.delivery.token}/status`,
        {
            status,
            notes: notes || undefined,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                submitting.value = false;
                showFailForm.value = false;
            },
        },
    );
}

// ─── Live location sharing ───────────────────────────────────────────────────

const SEND_INTERVAL_MS = 5_000;
const sharing = ref(false);
const lastSentAt = ref<Date | null>(null);
const canShare = computed(() =>
    ['assigned', 'picked_up'].includes(props.delivery.status),
);

let watchId: number | null = null;
let sendTimer: ReturnType<typeof setInterval> | null = null;
let latest: { lat: number; lng: number } | null = null;
let sending = false;
let failures = 0;

function startSharing() {
    if (!('geolocation' in navigator)) {
        toast.error('Live location is not supported on this device.');
        return;
    }
    sharing.value = true;
    failures = 0;
    watchId = navigator.geolocation.watchPosition(
        (position) => {
            const first = latest === null;
            latest = {
                lat: position.coords.latitude,
                lng: position.coords.longitude,
            };
            if (first) sendLocation();
        },
        (error) => {
            stopSharing();
            toast.error(
                error.code === error.PERMISSION_DENIED
                    ? 'Location permission was denied. Allow location access to share your live location.'
                    : 'Could not read your location. Please try again.',
            );
        },
        { enableHighAccuracy: true, maximumAge: 5_000, timeout: 20_000 },
    );
    // Throttle uploads to one every few seconds, whatever the GPS update rate.
    sendTimer = setInterval(sendLocation, SEND_INTERVAL_MS);
}

function stopSharing() {
    sharing.value = false;
    if (watchId !== null) navigator.geolocation.clearWatch(watchId);
    if (sendTimer) clearInterval(sendTimer);
    watchId = null;
    sendTimer = null;
    latest = null;
}

async function sendLocation() {
    if (!latest || sending) return;
    sending = true;
    try {
        await axios.post(`/driver/${props.delivery.token}/location`, latest);
        lastSentAt.value = new Date();
        failures = 0;
    } catch (error) {
        const status = axios.isAxiosError(error)
            ? error.response?.status
            : null;
        if (status === 409 || status === 404) {
            stopSharing();
            toast.info(
                'Location sharing stopped because this delivery is no longer active.',
            );
        } else if (++failures === 3) {
            toast.error(
                'Your location is not reaching the server. Check your connection.',
            );
        }
    } finally {
        sending = false;
    }
}

function toggleSharing() {
    if (sharing.value) {
        stopSharing();
        toast.info('Live location sharing stopped.');
    } else {
        startSharing();
    }
}

watch(canShare, (allowed) => {
    if (!allowed && sharing.value) stopSharing();
});

onUnmounted(stopSharing);

const navigationUrl = computed(() =>
    props.delivery.destination_lat !== null &&
    props.delivery.destination_lng !== null
        ? `https://www.google.com/maps/dir/?api=1&destination=${props.delivery.destination_lat},${props.delivery.destination_lng}`
        : null,
);

// ─── Helpers ─────────────────────────────────────────────────────────────────

function fmt(iso: string | null) {
    if (!iso) return '';
    return new Date(iso).toLocaleString('en-PH', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function callCustomer() {
    if (props.delivery.customer_phone) {
        window.location.href = `tel:${props.delivery.customer_phone}`;
    }
}
</script>

<template>
    <Head title="My Delivery" />

    <div class="flex min-h-screen flex-col bg-gray-50">
        <!-- ── Header ─────────────────────────────────────────────────────── -->
        <div
            class="flex items-center gap-3 border-b bg-white px-4 py-4 shadow-sm"
        >
            <div
                class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10"
            >
                <Truck class="h-5 w-5 text-primary" />
            </div>
            <div>
                <p class="text-sm leading-tight font-bold text-gray-900">
                    Delivery
                </p>
                <p v-if="delivery.shop_name" class="text-xs text-gray-500">
                    {{ delivery.shop_name }}
                </p>
            </div>
            <div class="ml-auto">
                <span
                    class="rounded-full border px-2.5 py-1 text-xs font-medium"
                    :class="STATUS[delivery.status]?.color"
                >
                    {{ STATUS[delivery.status]?.label }}
                </span>
            </div>
        </div>

        <div class="mx-auto w-full max-w-md flex-1 space-y-4 px-4 py-5">
            <!-- ── Order ref ───────────────────────────────────────────────── -->
            <div
                v-if="delivery.order_number"
                class="flex items-center gap-2 rounded-xl border bg-white px-4 py-2.5 font-mono text-xs text-gray-500"
            >
                <Package class="h-3.5 w-3.5 shrink-0" />
                Order #{{ delivery.order_number }}
            </div>

            <!-- ── Customer card ───────────────────────────────────────────── -->
            <div class="space-y-3 rounded-2xl border bg-white p-4 shadow-sm">
                <p
                    class="text-xs font-semibold tracking-wide text-gray-400 uppercase"
                >
                    Customer
                </p>

                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-gray-900">
                            {{ delivery.customer_name }}
                        </p>
                        <p
                            v-if="delivery.customer_phone"
                            class="mt-0.5 flex items-center gap-1 text-sm text-gray-500"
                        >
                            <Phone class="h-3.5 w-3.5" />
                            {{ delivery.customer_phone }}
                        </p>
                    </div>
                    <!-- Call button -->
                    <button
                        v-if="delivery.customer_phone"
                        @click="callCustomer"
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-green-600 text-white shadow transition-all hover:bg-green-700 active:scale-95"
                    >
                        <Phone class="h-4.5 w-4.5" />
                    </button>
                </div>

                <div
                    v-if="delivery.delivery_address"
                    class="flex items-start gap-2 rounded-xl bg-gray-50 px-3 py-2.5 text-sm text-gray-600"
                >
                    <MapPin class="mt-0.5 h-4 w-4 shrink-0 text-gray-400" />
                    <span>{{ delivery.delivery_address }}</span>
                </div>

                <a
                    v-if="navigationUrl"
                    :href="navigationUrl"
                    target="_blank"
                    rel="noopener"
                    class="flex items-center gap-1.5 text-xs font-medium text-primary hover:underline"
                >
                    <ExternalLink class="h-3.5 w-3.5" />
                    Open directions to the pinned location
                </a>

                <div
                    v-if="delivery.notes"
                    class="border-t pt-2 text-xs text-gray-500 italic"
                >
                    Note: {{ delivery.notes }}
                </div>
            </div>

            <!-- ── Live location ───────────────────────────────────────────── -->
            <div
                v-if="canShare"
                class="flex items-center justify-between gap-3 rounded-2xl border bg-white p-4 shadow-sm"
            >
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                        :class="
                            sharing
                                ? 'bg-green-100 text-green-600'
                                : 'bg-gray-100 text-gray-500'
                        "
                    >
                        <LocateFixed class="h-4.5 w-4.5" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">
                            Share my live location
                        </p>
                        <p class="text-xs text-gray-500">
                            {{
                                sharing
                                    ? lastSentAt
                                        ? `Customer can see you · updated ${lastSentAt.toLocaleTimeString('en-PH', { hour: 'numeric', minute: '2-digit', second: '2-digit' })}`
                                        : 'Waiting for GPS…'
                                    : 'Lets the customer follow the delivery on a map.'
                            }}
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    role="switch"
                    :aria-checked="sharing"
                    aria-label="Share my live location"
                    class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors"
                    :class="sharing ? 'bg-green-600' : 'bg-gray-300'"
                    @click="toggleSharing"
                >
                    <span
                        class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform"
                        :class="sharing ? 'translate-x-5' : 'translate-x-0.5'"
                    />
                </button>
            </div>

            <!-- ── Timeline ────────────────────────────────────────────────── -->
            <div class="rounded-2xl border bg-white p-4 shadow-sm">
                <p
                    class="mb-4 text-xs font-semibold tracking-wide text-gray-400 uppercase"
                >
                    Progress
                </p>
                <div class="space-y-3">
                    <div
                        v-for="(step, i) in STEPS"
                        :key="step.key"
                        class="flex items-start gap-3"
                    >
                        <!-- Circle -->
                        <div class="flex flex-col items-center">
                            <div
                                class="flex h-6 w-6 items-center justify-center rounded-full border-2 transition-colors"
                                :class="
                                    stepDone(step.key)
                                        ? 'border-primary bg-primary'
                                        : 'border-gray-200 bg-white'
                                "
                            >
                                <CheckCircle2
                                    v-if="stepDone(step.key)"
                                    class="h-3.5 w-3.5 text-white"
                                />
                            </div>
                            <!-- Connector line -->
                            <div
                                v-if="i < STEPS.length - 1"
                                class="mt-1 h-6 w-0.5 transition-colors"
                                :class="
                                    stepDone(STEPS[i + 1].key)
                                        ? 'bg-primary'
                                        : 'bg-gray-200'
                                "
                            />
                        </div>
                        <!-- Label -->
                        <div class="pt-0.5">
                            <p
                                class="text-sm font-medium"
                                :class="
                                    stepDone(step.key)
                                        ? 'text-gray-900'
                                        : 'text-gray-400'
                                "
                            >
                                {{ step.label }}
                            </p>
                            <p
                                v-if="(delivery as any)[step.timeKey]"
                                class="mt-0.5 text-xs text-gray-400"
                            >
                                {{ fmt((delivery as any)[step.timeKey]) }}
                            </p>
                        </div>
                    </div>

                    <!-- Failed state -->
                    <div
                        v-if="delivery.status === 'failed'"
                        class="flex items-start gap-3"
                    >
                        <div
                            class="flex h-6 w-6 items-center justify-center rounded-full border-2 border-red-500 bg-red-500"
                        >
                            <XCircle class="h-3.5 w-3.5 text-white" />
                        </div>
                        <div class="pt-0.5">
                            <p class="text-sm font-medium text-red-600">
                                Failed
                            </p>
                            <p
                                v-if="delivery.notes"
                                class="mt-0.5 text-xs text-gray-400"
                            >
                                {{ delivery.notes }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Action buttons ──────────────────────────────────────────── -->
            <div
                v-if="
                    next &&
                    delivery.status !== 'delivered' &&
                    delivery.status !== 'failed'
                "
                class="space-y-2"
            >
                <!-- Primary next action -->
                <button
                    :disabled="submitting"
                    @click="submitStatus(next.status)"
                    class="flex w-full items-center justify-center gap-2 rounded-2xl py-3.5 text-sm font-semibold text-white shadow-md transition-all active:scale-[0.98] disabled:opacity-60"
                    :class="next.color"
                >
                    <Loader2 v-if="submitting" class="h-4 w-4 animate-spin" />
                    <ChevronRight v-else class="h-4 w-4" />
                    {{ submitting ? 'Updating…' : next.label }}
                </button>

                <!-- Report failure -->
                <div v-if="!showFailForm">
                    <button
                        @click="showFailForm = true"
                        class="w-full rounded-2xl border border-red-200 py-2.5 text-sm font-medium text-red-600 transition-all hover:bg-red-50 active:scale-[0.98]"
                    >
                        Report Failed Delivery
                    </button>
                </div>
                <div
                    v-else
                    class="space-y-3 rounded-2xl border border-red-200 bg-white p-4"
                >
                    <p class="text-sm font-semibold text-red-600">
                        Report Failed Delivery
                    </p>
                    <textarea
                        v-model="failNotes"
                        rows="3"
                        placeholder="Reason (e.g. customer not home, wrong address…)"
                        class="w-full resize-none rounded-xl border px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 focus:outline-none"
                    />
                    <div class="flex gap-2">
                        <button
                            @click="showFailForm = false"
                            class="flex-1 rounded-xl border py-2 text-sm font-medium text-gray-600 hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            :disabled="submitting"
                            @click="submitStatus('failed', failNotes)"
                            class="flex-1 rounded-xl bg-red-600 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60"
                        >
                            {{ submitting ? 'Saving…' : 'Confirm Failed' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── Done state ──────────────────────────────────────────────── -->
            <div
                v-if="delivery.status === 'delivered'"
                class="rounded-2xl border border-green-200 bg-green-50 px-4 py-5 text-center"
            >
                <CheckCircle2 class="mx-auto mb-2 h-10 w-10 text-green-500" />
                <p class="font-bold text-green-700">Delivery Complete!</p>
                <p class="mt-1 text-xs text-green-600">
                    Great job. The customer has been notified.
                </p>
            </div>

            <div
                v-if="delivery.status === 'failed'"
                class="rounded-2xl border border-red-200 bg-red-50 px-4 py-5 text-center"
            >
                <XCircle class="mx-auto mb-2 h-10 w-10 text-red-400" />
                <p class="font-bold text-red-700">Delivery Failed</p>
                <p class="mt-1 text-xs text-red-500">
                    Please contact the shop for further instructions.
                </p>
            </div>
        </div>
    </div>
</template>
