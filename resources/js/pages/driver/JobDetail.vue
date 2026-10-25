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
import { googleMapsNavigationUrl, type GoogleRoute } from '@/lib/googleMaps';
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

function confirm() {
    if (!props.job.next_status || !checklistDone.value) return;

    busy.value = true;
    router.patch(
        `/driver/jobs/${props.job.public_id}/status`,
        { status: props.job.next_status },
        { preserveScroll: true, onFinish: () => (busy.value = false) },
    );
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
                    :origin="position"
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
                        <a
                            :href="googleMapsNavigationUrl(job.target)"
                            target="_blank"
                            rel="noopener"
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-600 text-white"
                            aria-label="Open turn-by-turn navigation in Google Maps"
                        >
                            <Navigation class="h-5 w-5" />
                        </a>
                    </div>
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
