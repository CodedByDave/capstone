<script setup lang="ts">
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
import { compressPhoto } from '@/lib/compressPhoto';
import type { DriverJob } from '@/types/driver';
import { router } from '@inertiajs/vue3';
import { BellRing, Camera, Phone } from 'lucide-vue-next';
import { computed, ref, watch, type Ref } from 'vue';

/**
 * Everything a driver does on a run: customer and payment details, progress, the checklist,
 * confirming pickup/delivery (with photo proof and cash collection) and reporting an issue.
 */
const props = defineProps<{ job: DriverJob }>();

const isPickup = computed(() => props.job.type === 'pickup');

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

// Photos are shrunk on the phone first, so a large camera photo still fits the upload limit.
const photoProcessing = ref(false);

async function pickPhoto(event: Event, photo: Ref<File | null>) {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;

    photoProcessing.value = true;
    statusError.value = '';
    try {
        photo.value = await compressPhoto(file);
    } finally {
        photoProcessing.value = false;
    }
}

// Why the server refused the step, shown in the dialog so the rider can fix it and try again.
const statusError = ref('');

// Handing a delivery over is when its balance is paid, so the rider confirms the cash they took first.
const collectOpen = ref(false);
const collected = ref('');
const collectDue = computed(() => props.job.collect_amount ?? 0);
const collectedNumber = computed(() => Number(collected.value));

const money = (value: number) =>
    `₱${value.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;

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

// Cash that changes hands needs a photo of it as proof.
const proofPhoto = ref<File | null>(null);
const proofInput = ref<HTMLInputElement | null>(null);
const proofError = computed(() =>
    collectedNumber.value > 0 && !proofPhoto.value
        ? 'Take a photo of the cash as proof.'
        : '',
);

const onProofPicked = (event: Event) => pickPhoto(event, proofPhoto);

const methodLabel = (method: string) =>
    ({ gcash: 'GCash', grabpay: 'GrabPay' })[method] ??
    method.charAt(0).toUpperCase() + method.slice(1);

function advance(extra: Record<string, unknown> = {}) {
    if (!props.job.next_status) return;

    busy.value = true;
    statusError.value = '';
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            collectOpen.value = false;
            laundryPhotoOpen.value = false;
        },
        onError: (errors: Record<string, string>) => {
            statusError.value =
                Object.values(errors)[0] ?? 'Could not update this run.';
        },
        onFinish: () => {
            busy.value = false;
        },
    };

    if (Object.values(extra).some((value) => value instanceof File)) {
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

// Collecting the laundry from the customer, and handing it back, each need a photo of it as proof.
const laundryPhotoOpen = ref(false);
const laundryPhoto = ref<File | null>(null);
const laundryInput = ref<HTMLInputElement | null>(null);
const handoverInput = ref<HTMLInputElement | null>(null);

const onLaundryPicked = (event: Event) => pickPhoto(event, laundryPhoto);

const handoverError = computed(() =>
    props.job.requires_delivery_photo && !laundryPhoto.value
        ? 'Take a photo of the laundry you handed over.'
        : '',
);

const laundryPhotoFields = computed(() =>
    props.job.requires_pickup_photo
        ? { proof_photo: laundryPhoto.value }
        : { delivery_photo: laundryPhoto.value },
);

function confirmLaundryPhoto() {
    if (!laundryPhoto.value) return;

    advance(laundryPhotoFields.value);
}

function confirm() {
    if (!props.job.next_status || !checklistDone.value) return;

    statusError.value = '';
    laundryPhoto.value = null;

    if (props.job.next_status === 'delivered' && collectDue.value > 0) {
        collected.value = collectDue.value.toFixed(2);
        proofPhoto.value = null;
        collectOpen.value = true;
        return;
    }

    if (props.job.requires_pickup_photo || props.job.requires_delivery_photo) {
        laundryPhotoOpen.value = true;
        return;
    }

    advance();
}

function confirmCollection() {
    if (collectError.value || proofError.value || handoverError.value) return;

    advance({
        collected_amount: collected.value,
        ...(collectedNumber.value > 0 ? { proof_photo: proofPhoto.value } : {}),
        ...(props.job.requires_delivery_photo ? laundryPhotoFields.value : {}),
    });
}

// ─── Accept or reject a newly assigned run ──────────────────────────────────

function accept() {
    busy.value = true;
    router.post(
        `/driver/jobs/${props.job.public_id}/accept`,
        {},
        { preserveScroll: true, onFinish: () => (busy.value = false) },
    );
}

const rejectReasons = [
    { value: 'emergency', label: 'Emergency' },
    { value: 'vehicle_problem', label: 'Vehicle problem or breakdown' },
    { value: 'feeling_unwell', label: 'Feeling unwell' },
    { value: 'bad_weather', label: 'Unsafe weather or road conditions' },
    { value: 'other', label: 'Other reason' },
];

const rejectOpen = ref(false);
const rejectReason = ref('');
const rejectNote = ref('');
const rejectPhoto = ref<File | null>(null);
const rejectInput = ref<HTMLInputElement | null>(null);
const rejectErrors = ref<Record<string, string>>({});

const rejectProblem = computed(() => {
    if (!rejectReason.value) return 'Choose why you cannot take this order.';
    if (rejectReason.value === 'other' && rejectNote.value.trim().length < 5) {
        return 'Tell the shop what happened.';
    }
    if (!rejectPhoto.value) return 'Take a photo as proof of your reason.';

    return '';
});

function openReject() {
    rejectReason.value = '';
    rejectNote.value = '';
    rejectPhoto.value = null;
    rejectErrors.value = {};
    if (rejectInput.value) rejectInput.value.value = '';
    rejectOpen.value = true;
}

const onRejectPhotoPicked = (event: Event) => pickPhoto(event, rejectPhoto);

function reject() {
    if (rejectProblem.value) return;

    busy.value = true;
    router.post(
        `/driver/jobs/${props.job.public_id}/reject`,
        {
            reason: rejectReason.value,
            note: rejectNote.value.trim() || null,
            proof_photo: rejectPhoto.value,
        },
        {
            forceFormData: true,
            onError: (errors) => (rejectErrors.value = errors),
            onSuccess: () => (rejectOpen.value = false),
            onFinish: () => (busy.value = false),
        },
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
    <div class="space-y-4">
        <!-- Customer phone + alternate contact -->
        <div class="space-y-1 text-sm">
            <p v-if="job.customer_phone" class="font-semibold">
                {{ job.customer_phone }}
            </p>
            <div
                v-if="job.alternate_contact_name || job.alternate_contact_phone"
            >
                <p class="mt-3 text-[11px] font-semibold text-slate-500">
                    Alternate contact
                </p>
                <p class="font-semibold">
                    {{ job.alternate_contact_name ?? 'Contact' }}
                </p>
                <p class="flex items-center gap-2">
                    {{ job.alternate_contact_phone ?? 'No phone number' }}
                    <a
                        v-if="job.alternate_contact_phone"
                        :href="`tel:${job.alternate_contact_phone}`"
                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-200 text-slate-700"
                        aria-label="Call alternate contact"
                    >
                        <Phone class="h-4 w-4" />
                    </a>
                </p>
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

        <!-- What the customer already paid -->
        <div
            v-if="job.paid_payments?.length"
            class="rounded-2xl bg-[#f3f3f3] p-4"
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

        <!-- Progress -->
        <div>
            <h2 class="text-base font-bold">Progress</h2>
            <div class="mt-2 grid grid-cols-4 gap-1.5">
                <div v-for="(step, index) in steps" :key="step">
                    <div
                        :class="[
                            'h-1.5 rounded-full',
                            index < reached ? 'bg-[#0a2dff]' : 'bg-slate-200',
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
        <div v-if="job.checklist.length">
            <h2 class="text-base font-bold">
                {{ isPickup ? 'Pickup' : 'Delivery' }} checklist
            </h2>
            <ul class="mt-2 space-y-3">
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

        <p v-if="job.notes" class="text-sm text-slate-600">
            <span class="block text-base font-bold text-slate-900">Notes</span>
            {{ job.notes }}
        </p>

        <!-- A new run waits for the driver's answer before anything else -->
        <div
            v-if="job.awaiting_acceptance"
            class="space-y-3 rounded-2xl border border-blue-200 bg-blue-50 p-4"
            role="status"
        >
            <div class="flex items-start gap-3">
                <BellRing class="mt-0.5 h-5 w-5 shrink-0 text-blue-700" />
                <div>
                    <p class="font-semibold text-blue-900">
                        New order assigned to you
                    </p>
                    <p class="text-sm text-blue-800">
                        Accept it to start, or reject it if you cannot take it
                        (you will need to give a reason and a photo as proof).
                    </p>
                </div>
            </div>
            <div class="flex gap-3">
                <Button
                    variant="outline"
                    class="h-12 flex-1 rounded-lg border-red-300 font-semibold text-red-700 hover:bg-red-50"
                    :disabled="busy"
                    @click="openReject"
                >
                    Reject
                </Button>
                <Button
                    class="h-12 flex-1 rounded-lg bg-[#0a2dff] font-semibold hover:bg-[#0822cc]"
                    :disabled="busy"
                    @click="accept"
                >
                    Accept order
                </Button>
            </div>
        </div>

        <!-- Actions -->
        <div v-else class="flex gap-3 pt-1">
            <template v-if="job.next_status">
                <Button
                    variant="outline"
                    class="h-11 rounded-lg px-5 font-semibold"
                    :disabled="busy"
                    @click="reportOpen = true"
                >
                    Report issue
                </Button>
                <Button
                    class="h-11 flex-1 rounded-lg bg-[#0a2dff] font-semibold hover:bg-[#0822cc]"
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

        <Dialog v-model:open="rejectOpen">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Reject this order</DialogTitle>
                    <DialogDescription>
                        The shop is told why and gives the order to another
                        driver. You will go offline until you switch back on.
                    </DialogDescription>
                </DialogHeader>

                <fieldset class="space-y-2">
                    <legend class="text-sm font-medium">Reason</legend>
                    <label
                        v-for="reason in rejectReasons"
                        :key="reason.value"
                        class="flex min-h-11 cursor-pointer items-center gap-3 rounded-xl border px-3 text-sm has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50"
                    >
                        <input
                            v-model="rejectReason"
                            type="radio"
                            name="reject-reason"
                            :value="reason.value"
                        />
                        {{ reason.label }}
                    </label>
                    <p v-if="rejectErrors.reason" class="text-xs text-red-600">
                        {{ rejectErrors.reason }}
                    </p>
                </fieldset>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium" for="reject-note">
                        What happened{{
                            rejectReason === 'other' ? '' : ' (optional)'
                        }}
                    </label>
                    <Textarea
                        id="reject-note"
                        v-model="rejectNote"
                        maxlength="500"
                        rows="3"
                        placeholder="Describe the problem briefly"
                    />
                    <p v-if="rejectErrors.note" class="text-xs text-red-600">
                        {{ rejectErrors.note }}
                    </p>
                </div>

                <div class="space-y-1.5">
                    <p class="text-sm font-medium">Proof photo</p>
                    <input
                        ref="rejectInput"
                        type="file"
                        accept="image/*"
                        capture="environment"
                        class="hidden"
                        @change="onRejectPhotoPicked"
                    />
                    <Button
                        type="button"
                        variant="outline"
                        class="h-12 w-full rounded-xl"
                        @click="rejectInput?.click()"
                    >
                        <Camera class="mr-2 h-4 w-4" />
                        {{ rejectPhoto ? 'Retake photo' : 'Take a photo' }}
                    </Button>
                    <p
                        v-if="rejectPhoto"
                        class="truncate text-xs text-emerald-700"
                    >
                        {{ rejectPhoto.name }}
                    </p>
                    <p
                        v-if="rejectErrors.proof_photo"
                        class="text-xs text-red-600"
                    >
                        {{ rejectErrors.proof_photo }}
                    </p>
                </div>

                <p v-if="rejectProblem" class="text-xs text-muted-foreground">
                    {{ rejectProblem }}
                </p>

                <DialogFooter>
                    <Button variant="outline" @click="rejectOpen = false">
                        Cancel
                    </Button>
                    <Button
                        class="bg-red-600 hover:bg-red-700"
                        :disabled="busy || photoProcessing || !!rejectProblem"
                        @click="reject"
                    >
                        Reject order
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

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
                        <Camera class="mr-2 h-4 w-4" />
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
                <div v-if="job.requires_delivery_photo" class="space-y-1.5">
                    <p class="text-sm font-medium">Proof of the handover</p>
                    <input
                        ref="handoverInput"
                        type="file"
                        accept="image/*"
                        capture="environment"
                        class="hidden"
                        @change="onLaundryPicked"
                    />
                    <Button
                        type="button"
                        variant="outline"
                        class="h-12 w-full rounded-xl"
                        @click="handoverInput?.click()"
                    >
                        <Camera class="mr-2 h-4 w-4" />
                        {{
                            laundryPhoto
                                ? 'Retake photo of the laundry'
                                : 'Take photo of the laundry'
                        }}
                    </Button>
                    <p
                        v-if="laundryPhoto"
                        class="truncate text-xs text-emerald-700"
                    >
                        {{ laundryPhoto.name }}
                    </p>
                    <p v-else class="text-xs text-red-500">
                        {{ handoverError }}
                    </p>
                </div>
                <p v-if="statusError" class="text-sm text-red-600" role="alert">
                    {{ statusError }}
                </p>
                <DialogFooter>
                    <Button variant="outline" @click="collectOpen = false">
                        Cancel
                    </Button>
                    <Button
                        class="bg-[#0a2dff] text-white hover:bg-[#0822cc]"
                        :disabled="
                            busy ||
                            photoProcessing ||
                            !!collectError ||
                            !!proofError ||
                            !!handoverError
                        "
                        @click="confirmCollection"
                    >
                        Confirm delivery
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="laundryPhotoOpen">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Photo of the laundry</DialogTitle>
                    <DialogDescription>
                        {{
                            job.requires_pickup_photo
                                ? 'Take a photo of the laundry you are picking up.'
                                : 'Take a photo of the laundry you are handing over.'
                        }}
                        The shop and the customer can see it as proof.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-1.5">
                    <input
                        ref="laundryInput"
                        type="file"
                        accept="image/*"
                        capture="environment"
                        class="hidden"
                        @change="onLaundryPicked"
                    />
                    <Button
                        type="button"
                        variant="outline"
                        class="h-12 w-full rounded-xl"
                        @click="laundryInput?.click()"
                    >
                        <Camera class="mr-2 h-4 w-4" />
                        {{
                            laundryPhoto
                                ? 'Retake photo of the laundry'
                                : 'Take photo of the laundry'
                        }}
                    </Button>
                    <p
                        v-if="laundryPhoto"
                        class="truncate text-xs text-emerald-700"
                    >
                        {{ laundryPhoto.name }}
                    </p>
                    <p v-else class="text-xs text-red-500">
                        {{
                            job.requires_pickup_photo
                                ? 'A photo is required to confirm the pickup.'
                                : 'A photo is required to confirm the delivery.'
                        }}
                    </p>
                    <p
                        v-if="statusError"
                        class="text-sm text-red-600"
                        role="alert"
                    >
                        {{ statusError }}
                    </p>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="laundryPhotoOpen = false">
                        Cancel
                    </Button>
                    <Button
                        class="bg-[#0a2dff] text-white hover:bg-[#0822cc]"
                        :disabled="busy || photoProcessing || !laundryPhoto"
                        @click="confirmLaundryPhoto"
                    >
                        {{
                            job.requires_pickup_photo
                                ? 'Confirm pickup'
                                : 'Confirm delivery'
                        }}
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
    </div>
</template>
