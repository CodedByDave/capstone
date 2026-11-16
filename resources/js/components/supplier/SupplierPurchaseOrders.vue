<script setup lang="ts">
import { StatusPill } from '@/components/data-table';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import type { PurchaseOrderStatus } from '@/lib/purchaseOrders';
import { PO_STATUS, peso, qty, shortDate } from '@/lib/purchaseOrders';
import { useForm } from '@inertiajs/vue3';
import {
    Camera,
    CheckCircle2,
    ClipboardList,
    ExternalLink,
    Loader2,
    Truck,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

export interface PortalPurchaseOrder {
    public_id: string;
    po_number: string;
    status: PurchaseOrderStatus;
    total: number;
    notes: string | null;
    expected_date: string | null;
    supplier_note: string | null;
    sent_at: string | null;
    delivered_at: string | null;
    has_proof: boolean;
    items: {
        name: string;
        unit: string | null;
        quantity: number;
        unit_price: number;
        line_total: number;
    }[];
}

const props = defineProps<{
    orders: PortalPurchaseOrder[];
    awaiting: number;
    shopName: string | null;
}>();

// Statuses read from the supplier's side of the order.
const SUPPLIER_LABEL: Partial<Record<PurchaseOrderStatus, string>> = {
    sent: 'Needs your answer',
    confirmed: 'You confirmed',
    received: 'Received by the shop',
    declined: 'You declined',
};

const today = new Date().toISOString().slice(0, 10);

// One answer form at a time: the order being answered and how.
const answering = ref<{ id: string; answer: 'confirm' | 'decline' } | null>(
    null,
);
const form = useForm({
    answer: 'confirm' as 'confirm' | 'decline',
    expected_date: today,
    note: '',
});

function start(order: PortalPurchaseOrder, answer: 'confirm' | 'decline') {
    answering.value = { id: order.public_id, answer };
    form.reset();
    form.clearErrors();
    form.answer = answer;
}

function submit(order: PortalPurchaseOrder) {
    form.post(`/supplier-portal/purchase-orders/${order.public_id}/answer`, {
        preserveScroll: true,
        onSuccess: () => (answering.value = null),
    });
}

// The delivery photo: the supplier hands the order over and attaches proof for the shop.
const delivering = ref<string | null>(null);
const deliverForm = useForm({ photo: null as File | null });

function startDeliver(order: PortalPurchaseOrder) {
    delivering.value = order.public_id;
    deliverForm.reset();
    deliverForm.clearErrors();
}

function onPhoto(event: Event) {
    deliverForm.photo = (event.target as HTMLInputElement).files?.[0] ?? null;
    deliverForm.clearErrors();
}

function submitDelivery(order: PortalPurchaseOrder) {
    deliverForm.post(
        `/supplier-portal/purchase-orders/${order.public_id}/deliver`,
        {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => (delivering.value = null),
        },
    );
}

const sorted = computed(() =>
    [...props.orders].sort(
        (a, b) => Number(b.status === 'sent') - Number(a.status === 'sent'),
    ),
);
</script>

<template>
    <section id="orders" class="scroll-mt-4 space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="flex items-center gap-2 text-lg font-semibold">
                <ClipboardList
                    class="h-5 w-5 text-muted-foreground"
                    aria-hidden="true"
                />
                Orders from {{ shopName ?? 'the shop' }}
            </h2>
            <StatusPill v-if="awaiting > 0" tone="info"
                >{{ awaiting }} waiting for your answer</StatusPill
            >
        </div>

        <p
            v-if="orders.length === 0"
            class="rounded-[14px] border bg-card p-6 text-center text-sm text-muted-foreground"
        >
            No orders yet. When the shop orders from you, it appears here.
        </p>

        <article
            v-for="order in sorted"
            :key="order.public_id"
            class="rounded-[14px] border bg-card p-4"
            :class="order.status === 'sent' ? 'border-primary/40' : ''"
        >
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="font-semibold">{{ order.po_number }}</p>
                    <p class="text-xs text-muted-foreground">
                        Sent {{ shortDate(order.sent_at) }}
                        <template v-if="order.expected_date">
                            · Delivery {{ shortDate(order.expected_date) }}
                        </template>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-lg font-bold tabular-nums">{{
                        peso(order.total)
                    }}</span>
                    <StatusPill :tone="PO_STATUS[order.status].tone">{{
                        SUPPLIER_LABEL[order.status] ??
                        PO_STATUS[order.status].label
                    }}</StatusPill>
                </div>
            </div>

            <ul class="mt-3 divide-y text-sm">
                <li
                    v-for="(item, index) in order.items"
                    :key="index"
                    class="flex justify-between gap-3 py-1.5"
                >
                    <span
                        >{{ item.name }} ·
                        <span class="text-muted-foreground"
                            >{{ qty(item.quantity) }} {{ item.unit ?? '' }} ×
                            {{ peso(item.unit_price) }}</span
                        ></span
                    >
                    <span class="tabular-nums">{{
                        peso(item.line_total)
                    }}</span>
                </li>
            </ul>

            <p
                v-if="order.notes"
                class="mt-2 rounded-md bg-muted/50 px-3 py-2 text-sm"
            >
                <span class="font-medium">Shop note:</span> {{ order.notes }}
            </p>
            <p
                v-if="order.supplier_note && order.status !== 'sent'"
                class="mt-2 text-sm text-muted-foreground"
            >
                Your note: “{{ order.supplier_note }}”
            </p>

            <!-- Delivery -->
            <div v-if="order.status === 'confirmed'" class="mt-4 border-t pt-4">
                <p
                    v-if="order.delivered_at"
                    class="flex flex-wrap items-center gap-2 text-sm"
                >
                    <Truck
                        class="h-4 w-4 text-muted-foreground"
                        aria-hidden="true"
                    />
                    You marked this delivered on
                    {{ shortDate(order.delivered_at) }}. Waiting for the shop to
                    receive it.
                    <a
                        v-if="order.has_proof"
                        :href="`/supplier-portal/purchase-orders/${order.public_id}/proof/supplier`"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-1 text-primary hover:underline"
                    >
                        <ExternalLink class="h-3.5 w-3.5" aria-hidden="true" />
                        View your photo
                    </a>
                </p>

                <template v-else>
                    <Button
                        v-if="delivering !== order.public_id"
                        class="min-h-11"
                        @click="startDeliver(order)"
                    >
                        <Truck class="h-4 w-4" /> Mark as delivered
                    </Button>

                    <form
                        v-else
                        class="space-y-3"
                        @submit.prevent="submitDelivery(order)"
                    >
                        <div class="max-w-xl space-y-1.5">
                            <label
                                :for="`photo-${order.public_id}`"
                                class="flex items-center gap-2 text-sm font-medium"
                            >
                                <Camera class="h-4 w-4" aria-hidden="true" />
                                Photo of the delivery
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                :id="`photo-${order.public_id}`"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                capture="environment"
                                required
                                class="block w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-primary/10 file:px-3 file:py-2 file:text-sm file:font-medium file:text-primary"
                                @change="onPhoto"
                            />
                            <p class="text-xs text-muted-foreground">
                                Show the delivered supplies. JPG, PNG or WEBP,
                                up to 5 MB.
                            </p>
                            <InputError :message="deliverForm.errors.photo" />
                            <InputError
                                :message="
                                    (
                                        deliverForm.errors as Record<
                                            string,
                                            string | undefined
                                        >
                                    ).status
                                "
                            />
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <Button
                                type="submit"
                                class="min-h-11"
                                :disabled="
                                    deliverForm.processing || !deliverForm.photo
                                "
                            >
                                <Loader2
                                    v-if="deliverForm.processing"
                                    class="h-4 w-4 animate-spin"
                                />
                                Mark delivered
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                class="min-h-11"
                                @click="delivering = null"
                            >
                                Back
                            </Button>
                        </div>
                    </form>
                </template>
            </div>

            <!-- Answer -->
            <div v-if="order.status === 'sent'" class="mt-4 border-t pt-4">
                <div
                    v-if="answering?.id !== order.public_id"
                    class="flex flex-wrap gap-2"
                >
                    <Button
                        variant="success"
                        class="min-h-11"
                        @click="start(order, 'confirm')"
                    >
                        <CheckCircle2 class="h-4 w-4" /> Confirm order
                    </Button>
                    <Button
                        variant="destructive-outline"
                        class="min-h-11"
                        @click="start(order, 'decline')"
                    >
                        <XCircle class="h-4 w-4" /> Decline
                    </Button>
                </div>

                <form v-else class="space-y-3" @submit.prevent="submit(order)">
                    <div
                        v-if="form.answer === 'confirm'"
                        class="max-w-xs space-y-1.5"
                    >
                        <label
                            :for="`deliver-${order.public_id}`"
                            class="text-sm font-medium"
                            >When can you deliver?</label
                        >
                        <input
                            :id="`deliver-${order.public_id}`"
                            v-model="form.expected_date"
                            type="date"
                            :min="today"
                            required
                            class="h-11 w-full rounded-md border border-input bg-background px-3 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        />
                        <InputError :message="form.errors.expected_date" />
                    </div>
                    <div class="max-w-xl space-y-1.5">
                        <label
                            :for="`note-${order.public_id}`"
                            class="text-sm font-medium"
                            >{{
                                form.answer === 'decline'
                                    ? 'Why are you declining?'
                                    : 'Note to the shop (optional)'
                            }}</label
                        >
                        <input
                            :id="`note-${order.public_id}`"
                            v-model="form.note"
                            type="text"
                            maxlength="500"
                            :required="form.answer === 'decline'"
                            :placeholder="
                                form.answer === 'decline'
                                    ? 'e.g. Out of stock until next week'
                                    : 'e.g. Delivery in the afternoon'
                            "
                            class="h-11 w-full rounded-md border border-input bg-background px-3 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        />
                        <InputError :message="form.errors.note" />
                    </div>
                    <InputError
                        :message="
                            (form.errors as Record<string, string | undefined>)
                                .status
                        "
                    />
                    <div class="flex flex-wrap gap-2">
                        <Button
                            type="submit"
                            class="min-h-11"
                            :variant="
                                form.answer === 'decline'
                                    ? 'destructive'
                                    : 'success'
                            "
                            :disabled="form.processing"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="h-4 w-4 animate-spin"
                            />
                            {{
                                form.answer === 'decline'
                                    ? 'Decline order'
                                    : 'Confirm order'
                            }}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            class="min-h-11"
                            @click="answering = null"
                        >
                            Back
                        </Button>
                    </div>
                </form>
            </div>
        </article>
    </section>
</template>
