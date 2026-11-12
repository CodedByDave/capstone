<script setup lang="ts">
import { StatusPill } from '@/components/data-table';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import type { PurchaseOrderStatus } from '@/lib/purchaseOrders';
import { PO_STATUS, peso, qty, shortDate } from '@/lib/purchaseOrders';
import { useForm } from '@inertiajs/vue3';
import { CheckCircle2, ClipboardList, Loader2, XCircle } from 'lucide-vue-next';
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
                    <InputError :message="form.errors.status" />
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
