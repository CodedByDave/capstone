<script setup lang="ts">
import DirectionsMap from '@/components/driver/DirectionsMap.vue';
import DriverLayout from '@/components/driver/DriverLayout.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { useDriverLocation } from '@/composables/useDriverLocation';
import {
    canNavigateTo,
    googleMapsNavigationUrl,
    type GoogleRoute,
} from '@/lib/googleMaps';
import type { DriverInfo, DriverJob } from '@/types/driver';
import { router } from '@inertiajs/vue3';
import { ChevronLeft, MessageSquare, Navigation, Phone } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps<{
    driver: DriverInfo;
    job: DriverJob;
    maps_key: string | null;
}>();

const isPickup = computed(() => props.job.type === 'pickup');
const onTheRoad = computed(
    () => props.job.status === 'assigned' || props.job.status === 'picked_up',
);

// ─── Directions ──────────────────────────────────────────────────────────────

const jobId = computed(() => props.job.public_id);
const sharing = computed(() => onTheRoad.value && props.driver.is_online);
const {
    position,
    error: locationError,
    start,
} = useDriverLocation(jobId, sharing);
onMounted(start);

const route = ref<GoogleRoute | null>(null);

// The other stop is where the run starts from (the shop for a delivery); used until the phone
// reports its own position.
const startPin = computed(() => {
    const from =
        props.job.target.kind === 'customer'
            ? props.job.shop_stop
            : props.job.customer_stop;

    return from.lat !== null && from.lng !== null
        ? { lat: from.lat, lng: from.lng }
        : null;
});

// ─── Progress ────────────────────────────────────────────────────────────────

const steps = computed(() =>
    isPickup.value
        ? ['Assigned', 'On the way', 'Picked up', 'At shop']
        : ['Assigned', 'To shop', 'On the way', 'Delivered'],
);

const reached = computed(
    () =>
        ({ assigned: 2, picked_up: 3, delivered: 4, failed: 1 })[
            props.job.status
        ],
);

// ─── Checklist ───────────────────────────────────────────────────────────────

const ticked = ref<boolean[]>([]);
watch(
    () => [props.job.public_id, props.job.status],
    () => (ticked.value = props.job.checklist.map(() => false)),
    { immediate: true },
);
const checklistDone = computed(() => ticked.value.every(Boolean));

// ─── Actions ─────────────────────────────────────────────────────────────────

const busy = ref(false);

// Handing a delivery over is when its balance is paid, so the rider confirms the cash they took first.
const collectOpen = ref(false);
const collected = ref('');
const collectDue = computed(() => props.job.collect_amount ?? 0);
const collectedNumber = computed(() => Number(collected.value));
const collectError = computed(() => {
    if (collected.value === '' || Number.isNaN(collectedNumber.value)) {
        return 'Enter the amount you collected (0 if nothing).';
    }
    if (collectedNumber.value < 0) return 'The amount cannot be negative.';
    if (collectedNumber.value > collectDue.value) {
        return `You cannot collect more than ${money(collectDue.value)}.`;
    }

    return '';
});

const money = (value: number) =>
    `₱${value.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;

// Cash that changes hands needs a photo of it as proof.
const proofPhoto = ref<File | null>(null);
const proofInput = ref<HTMLInputElement | null>(null);
const proofError = computed(() =>
    collectedNumber.value > 0 && !proofPhoto.value
        ? 'Take a photo of the cash as proof.'
        : '',
);

function onProofPicked(event: Event) {
    proofPhoto.value = (event.target as HTMLInputElement).files?.[0] ?? null;
}

const methodLabel = (method: string) =>
    ({ gcash: 'GCash', grabpay: 'GrabPay' })[method] ??
    method.charAt(0).toUpperCase() + method.slice(1);

function advance(extra: Record<string, unknown> = {}) {
    if (!props.job.next_status) return;

    busy.value = true;
    const options = {
        preserveScroll: true,
        onFinish: () => {
            busy.value = false;
            collectOpen.value = false;
        },
    };

    if (extra.proof_photo instanceof File) {
        // A file cannot ride on a real PATCH, so it is sent as a POST that Laravel treats as PATCH.
        router.post(
            `/driver/jobs/${props.job.public_id}/status`,
            { _method: 'patch', status: props.job.next_status, ...extra },
            { ...options, forceFormData: true },
        );
        return;
    }

    router.patch(
        `/driver/jobs/${props.job.public_id}/status`,
        { status: props.job.next_status, ...extra },
        options,
    );
}

function confirm() {
    if (!props.job.next_status || !checklistDone.value) return;

    if (props.job.next_status === 'delivered' && collectDue.value > 0) {
        collected.value = collectDue.value.toFixed(2);
        proofPhoto.value = null;
        if (proofInput.value) proofInput.value.value = '';
        collectOpen.value = true;
        return;
    }

    advance();
}

function confirmCollection() {
    if (collectError.value || proofError.value) return;

    advance({
        collected_amount: collected.value,
        ...(collectedNumber.value > 0 ? { proof_photo: proofPhoto.value } : {}),
    });
}

const reportOpen = ref(false);
const reportNote = ref('');

function reportIssue() {
    busy.value = true;
    router.patch(
        `/driver/jobs/${props.job.public_id}/status`,
        { status: 'failed', notes: reportNote.value || null },
        {
            onFinish: () => {
                busy.value = false;
                reportOpen.value = false;
            },
        },
    );
}

const finishedLabel = computed(() =>
    props.job.status === 'delivered' ? 'Completed' : 'Reported as failed',
);
</script>

<template>
    <DriverLayout title="Job" hide-nav>
        <div class="-mx-4 -mt-4">
            <!-- Map -->
            <div class="relative">
                <DirectionsMap
                    :api-key="maps_key"
                    :origin="position ?? startPin"
                    :destination="job.target"
                    height-class="h-64"
                    @route="route = $event"
                />

                <button
                    type="button"
                    class="absolute top-4 left-4 flex h-11 w-11 items-center justify-center rounded-full bg-white shadow"
                    aria-label="Back to home"
                    @click="router.visit('/driver')"
                >
                    <ChevronLeft class="h-5 w-5" />
                </button>

                <span
                    v-if="route"
                    class="absolute top-4 right-4 rounded-full bg-white px-3 py-1.5 text-xs font-semibold shadow"
                >
                    {{ route.distanceText }} · {{ route.durationText }}
                </span>
                <p
                    v-else-if="locationError"
                    class="absolute right-3 bottom-3 left-3 rounded-lg bg-white/95 px-3 py-2 text-xs text-amber-800 shadow"
                >
                    {{ locationError }}
                </p>
            </div>

            <div class="space-y-3 rounded-t-3xl bg-[#f3f4f6] px-4 pt-4 pb-28">
                <!-- Heading -->
                <div>
                    <div class="flex items-center gap-2">
                        <span
                            :class="[
                                'rounded-md px-2.5 py-1 text-[11px] font-bold tracking-wide uppercase',
                                isPickup
                                    ? 'bg-orange-100 text-orange-700'
                                    : 'bg-blue-100 text-blue-700',
                            ]"
                        >
                            {{ job.type }}
                        </span>
                        <span
                            v-if="job.order_number"
                            class="text-sm text-slate-500"
                        >
                            Order #{{ job.order_number }}
                        </span>
                    </div>
                    <h1 class="mt-2 text-2xl font-bold">
                        {{ job.target.label }}
                    </h1>
                    <p class="text-sm text-slate-500">
                        {{ job.target.address }}
                    </p>
                    <p
                        v-if="job.target.kind === 'shop'"
                        class="mt-1 text-xs text-slate-500"
                    >
                        Customer: {{ job.customer_name }}
                    </p>
                </div>

                <!-- Contact + navigation -->
                <div
                    class="flex items-center justify-between rounded-2xl bg-white p-4"
                >
                    <div>
                        <p class="text-xs text-slate-500">
                            {{ job.service ?? 'Service' }}
                        </p>
                        <p class="text-sm font-bold">
                            {{ job.customer_phone ?? 'No phone number' }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <a
                            v-if="job.customer_phone"
                            :href="`tel:${job.customer_phone}`"
                            class="flex h-11 w-11 items-center justify-center rounded-xl border"
                            aria-label="Call customer"
                        >
                            <Phone class="h-5 w-5" />
                        </a>
                        <a
                            v-if="job.customer_phone"
                            :href="`sms:${job.customer_phone}`"
                            class="flex h-11 w-11 items-center justify-center rounded-xl border"
                            aria-label="Message customer"
                        >
                            <MessageSquare class="h-5 w-5" />
                        </a>
                    </div>
                </div>

                <!-- Cash due at the door -->
                <div
                    v-if="collectDue > 0"
                    class="flex items-center justify-between rounded-2xl bg-emerald-50 p-4"
                >
                    <div>
                        <p class="text-xs text-emerald-700">
                            Collect from the customer
                        </p>
                        <p class="text-2xl font-bold text-emerald-900">
                            {{ money(collectDue) }}
                        </p>
                    </div>
                    <p class="max-w-36 text-right text-xs text-emerald-700">
                        Balance of the order, delivery fee included. Cash only.
                    </p>
                </div>

                <!-- What the customer already paid, so the rider can see it is settled -->
                <div
                    v-if="job.paid_payments?.length"
                    class="rounded-2xl bg-white p-4"
                >
                    <p class="text-xs font-semibold text-slate-500">
                        {{
                            collectDue > 0
                                ? 'Already paid'
                                : 'Already paid in full. Nothing to collect.'
                        }}
                    </p>
                    <ul class="mt-2 space-y-1.5">
                        <li
                            v-for="(payment, index) in job.paid_payments"
                            :key="index"
                            class="flex items-start justify-between gap-3 text-sm"
                        >
                            <span>
                                <span class="font-semibold">{{
                                    methodLabel(payment.method)
                                }}</span>
                                <span class="text-slate-500">
                                    {{
                                        payment.source === 'online'
                                            ? 'online'
                                            : 'at the shop'
                                    }}</span
                                >
                                <span
                                    v-if="payment.reference"
                                    class="block text-xs text-slate-500"
                                    >Ref {{ payment.reference }}</span
                                >
                            </span>
                            <span class="font-semibold text-emerald-700">{{
                                money(payment.amount)
                            }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Someone else to call, or to hand the laundry to, if the customer is not there -->
                <div
                    v-if="
                        job.alternate_contact_name ||
                        job.alternate_contact_phone
                    "
                    class="flex items-center justify-between rounded-2xl bg-amber-50 p-4"
                >
                    <div>
                        <p class="text-xs text-amber-700">Alternate contact</p>
                        <p class="text-sm font-bold">
                            {{ job.alternate_contact_name ?? 'Contact' }}
                        </p>
                        <p class="text-sm">
                            {{
                                job.alternate_contact_phone ?? 'No phone number'
                            }}
                        </p>
                    </div>
                    <a
                        v-if="job.alternate_contact_phone"
                        :href="`tel:${job.alternate_contact_phone}`"
                        class="flex h-11 w-11 items-center justify-center rounded-xl border bg-white"
                        aria-label="Call alternate contact"
                    >
                        <Phone class="h-5 w-5" />
                    </a>
                </div>

                <!-- Navigation: both stops, the one to head to now is highlighted -->
                <div class="rounded-2xl bg-white p-4">
                    <h2 class="font-bold">Navigate in Google Maps</h2>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <component
                            :is="canNavigateTo(stop.stop) ? 'a' : 'span'"
                            v-for="stop in [
                                {
                                    label: 'To customer',
                                    stop: job.customer_stop,
                                    from: job.shop_stop,
                                },
                                {
                                    label: 'To shop',
                                    stop: job.shop_stop,
                                    from: job.customer_stop,
                                },
                            ]"
                            :key="stop.label"
                            :href="
                                canNavigateTo(stop.stop)
                                    ? googleMapsNavigationUrl(
                                          stop.stop,
                                          stop.from,
                                      )
                                    : undefined
                            "
                            target="_blank"
                            rel="noopener"
                            :class="[
                                'flex h-12 items-center justify-center gap-2 rounded-xl text-sm font-semibold',
                                !canNavigateTo(stop.stop)
                                    ? 'bg-slate-100 text-slate-400'
                                    : stop.stop.kind === job.target.kind
                                      ? 'bg-blue-600 text-white'
                                      : 'border text-slate-700',
                            ]"
                        >
                            <Navigation class="h-4 w-4" aria-hidden="true" />
                            {{ stop.label }}
                        </component>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">
                        Heading now:
                        {{
                            job.target.kind === 'customer'
                                ? 'the customer'
                                : 'the shop'
                        }}.
                        <span
                            v-if="!canNavigateTo(job.customer_stop)"
                            class="text-amber-700"
                        >
                            The customer has no address saved.
                        </span>
                    </p>
                </div>

                <!-- Progress -->
                <div class="rounded-2xl bg-white p-4">
                    <h2 class="font-bold">Progress</h2>
                    <div class="mt-3 grid grid-cols-4 gap-1.5">
                        <div v-for="(step, index) in steps" :key="step">
                            <div
                                :class="[
                                    'h-1.5 rounded-full',
                                    index < reached
                                        ? 'bg-blue-600'
                                        : 'bg-slate-200',
                                ]"
                            />
                            <p
                                :class="[
                                    'mt-1.5 text-[11px]',
                                    index < reached
                                        ? 'font-semibold text-slate-900'
                                        : 'text-slate-400',
                                ]"
                            >
                                {{ step }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Checklist -->
                <div
                    v-if="job.checklist.length"
                    class="rounded-2xl bg-white p-4"
                >
                    <h2 class="font-bold">
                        {{ isPickup ? 'Pickup' : 'Delivery' }} checklist
                    </h2>
                    <ul class="mt-3 space-y-4">
                        <li v-for="(item, index) in job.checklist" :key="item">
                            <label
                                class="flex cursor-pointer items-center gap-3 text-sm"
                            >
                                <input
                                    v-model="ticked[index]"
                                    type="checkbox"
                                    class="h-6 w-6 shrink-0 rounded border-slate-400"
                                />
                                {{ item }}
                            </label>
                        </li>
                    </ul>
                </div>

                <p
                    v-if="job.notes"
                    class="rounded-2xl bg-white p-4 text-sm text-slate-600"
                >
                    <span class="block text-xs font-semibold text-slate-500"
                        >Notes</span
                    >
                    {{ job.notes }}
                </p>
            </div>
        </div>

        <!-- Action bar -->
        <div
            class="fixed inset-x-0 bottom-0 z-30 mx-auto flex max-w-md gap-3 border-t bg-white p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]"
        >
            <template v-if="job.next_status">
                <Button
                    variant="outline"
                    class="h-12 rounded-xl px-5 font-semibold"
                    :disabled="busy"
                    @click="reportOpen = true"
                >
                    Report issue
                </Button>
                <Button
                    class="h-12 flex-1 rounded-xl bg-blue-600 font-semibold hover:bg-blue-700"
                    :disabled="busy || !checklistDone"
                    @click="confirm"
                >
                    {{ job.action_label }}
                </Button>
            </template>
            <p
                v-else
                class="flex-1 py-3 text-center text-sm font-semibold text-slate-600"
            >
                {{ finishedLabel }}
            </p>
        </div>

        <Dialog v-model:open="collectOpen">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Cash collected</DialogTitle>
                    <DialogDescription>
                        The customer owes {{ money(collectDue) }}. Enter what
                        you collected at the door. This is recorded as their
                        payment, and the order is marked paid when it covers the
                        balance.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium" for="collected-amount"
                        >Amount collected</label
                    >
                    <input
                        id="collected-amount"
                        v-model="collected"
                        type="number"
                        inputmode="decimal"
                        step="0.01"
                        min="0"
                        :max="collectDue"
                        class="h-12 w-full rounded-xl border px-3 text-lg font-semibold"
                        @wheel="($event.target as HTMLInputElement).blur()"
                    />
                    <p
                        v-if="collected !== '' && collectError"
                        class="text-xs text-red-500"
                    >
                        {{ collectError }}
                    </p>
                    <p v-else class="text-xs text-muted-foreground">
                        Enter 0 if the customer paid nothing.
                    </p>
                </div>
                <div v-if="collectedNumber > 0" class="space-y-1.5">
                    <p class="text-sm font-medium">Proof of the cash</p>
                    <input
                        ref="proofInput"
                        type="file"
                        accept="image/*"
                        capture="environment"
                        class="hidden"
                        @change="onProofPicked"
                    />
                    <Button
                        type="button"
                        variant="outline"
                        class="h-12 w-full rounded-xl"
                        @click="proofInput?.click()"
                    >
                        {{
                            proofPhoto
                                ? 'Retake photo of the cash'
                                : 'Take photo of the cash'
                        }}
                    </Button>
                    <p
                        v-if="proofPhoto"
                        class="truncate text-xs text-emerald-700"
                    >
                        {{ proofPhoto.name }}
                    </p>
                    <p v-else class="text-xs text-red-500">{{ proofError }}</p>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="collectOpen = false">
                        Cancel
                    </Button>
                    <Button
                        class="bg-blue-600 hover:bg-blue-700"
                        :disabled="busy || !!collectError || !!proofError"
                        @click="confirmCollection"
                    >
                        Confirm delivery
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="reportOpen">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Report an issue</DialogTitle>
                    <DialogDescription>
                        This marks the run as failed and tells the shop.
                        Describe what went wrong.
                    </DialogDescription>
                </DialogHeader>
                <Textarea
                    v-model="reportNote"
                    placeholder="e.g. Customer not at the address"
                    maxlength="500"
                />
                <DialogFooter>
                    <Button variant="outline" @click="reportOpen = false">
                        Cancel
                    </Button>
                    <Button
                        variant="destructive"
                        :disabled="busy"
                        @click="reportIssue"
                    >
                        Mark as failed
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </DriverLayout>
</template>
