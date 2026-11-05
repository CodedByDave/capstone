<script setup lang="ts">
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
import { Check, Download, Printer, ReceiptText, X } from 'lucide-vue-next';

export interface OrderReceipt {
    title: string;
    shop_name: string | null;
    customer_name: string;
    invoice_number: string;
    order_number: string;
    order_time: string | null;
    payment_method: string;
    payment_status: 'paid' | 'partial' | 'unpaid';
    payment_status_label: string;
    amount_paid: number;
    amount_tendered: number | null;
    change_given: number | null;
    items: { label: string; amount: number }[];
    total: number;
    balance_due: number;
    is_estimate: boolean;
}

const props = defineProps<{
    receipt: OrderReceipt;
    pdfUrl: string;
}>();

const open = defineModel<boolean>('open', { required: true });

// Prints the PDF receipt through a hidden frame, so the rest of the page is never printed.
function printReceipt() {
    const frame = document.createElement('iframe');
    frame.style.cssText =
        'position:fixed;right:0;bottom:0;width:0;height:0;border:0;';
    frame.src = `${props.pdfUrl}${props.pdfUrl.includes('?') ? '&' : '?'}inline=1`;
    frame.onload = () => {
        frame.contentWindow?.focus();
        frame.contentWindow?.print();
        setTimeout(() => frame.remove(), 60_000);
    };
    document.body.appendChild(frame);
}

function money(value: number) {
    const formatted = Math.abs(value).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    return `${value < 0 ? '-' : ''}₱${formatted}`;
}

const badge: Record<string, string> = {
    paid: 'bg-green-700 text-white',
    partial: 'bg-amber-700 text-white',
    unpaid: 'bg-red-700 text-white',
};
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent
            :show-close-button="false"
            class="max-h-[92vh] max-w-[22rem] gap-0 overflow-y-auto border-0 bg-transparent p-0 shadow-none sm:max-w-[22rem]"
        >
            <!-- Top: icon and title, with ticket notches where it meets the details -->
            <div class="receipt-top relative bg-white px-6 pt-7 pb-6 text-center">
                <DialogClose
                    class="absolute top-4 right-4 flex h-11 w-11 items-center justify-center rounded-full text-slate-700 hover:bg-slate-100 focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:outline-none"
                    aria-label="Close receipt"
                >
                    <X class="h-5 w-5" />
                </DialogClose>

                <div class="relative mx-auto h-16 w-16 text-indigo-900">
                    <ReceiptText class="h-16 w-16" stroke-width="1.5" />
                    <span
                        class="absolute right-0 bottom-1 flex h-6 w-6 items-center justify-center rounded-full bg-lime-500 text-white"
                    >
                        <Check class="h-4 w-4" stroke-width="3" />
                    </span>
                </div>
                <DialogTitle class="mt-4 text-lg font-semibold text-slate-900">
                    {{ receipt.title }}
                </DialogTitle>
                <DialogDescription class="sr-only">
                    Receipt for order {{ receipt.order_number }}
                </DialogDescription>
            </div>

            <!-- Details, with a scalloped bottom edge -->
            <div
                class="receipt-body border-t border-dashed border-slate-300 bg-white px-6 pt-5 pb-9"
            >
                <h3 class="text-sm font-semibold text-slate-900">
                    Payment Details
                </h3>
                <dl class="mt-3 space-y-3 text-sm">
                    <div class="flex gap-2">
                        <dt class="w-[44%] shrink-0 text-slate-600">
                            Invoice Number
                        </dt>
                        <dd class="min-w-0 break-words text-slate-900">
                            <span aria-hidden="true" class="mr-2">:</span
                            >{{ receipt.invoice_number }}
                        </dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="w-[44%] shrink-0 text-slate-600">
                            Customer
                        </dt>
                        <dd class="min-w-0 break-words text-slate-900">
                            <span aria-hidden="true" class="mr-2">:</span
                            >{{ receipt.customer_name }}
                        </dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="w-[44%] shrink-0 text-slate-600">
                            Order Time
                        </dt>
                        <dd class="min-w-0 text-slate-900">
                            <span aria-hidden="true" class="mr-2">:</span
                            >{{ receipt.order_time }}
                        </dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="w-[44%] shrink-0 text-slate-600">
                            Payment Method
                        </dt>
                        <dd class="min-w-0 text-slate-900">
                            <span aria-hidden="true" class="mr-2">:</span
                            >{{ receipt.payment_method }}
                        </dd>
                    </div>
                    <div class="flex items-center gap-2">
                        <dt class="w-[44%] shrink-0 text-slate-600">
                            Payment Status
                        </dt>
                        <dd class="min-w-0 text-slate-900">
                            <span aria-hidden="true" class="mr-2">:</span>
                            <span
                                class="rounded px-2 py-0.5 text-xs font-medium"
                                :class="badge[receipt.payment_status]"
                                >{{ receipt.payment_status_label }}</span
                            >
                        </dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="w-[44%] shrink-0 text-slate-600">Amount</dt>
                        <dd class="min-w-0 text-slate-900">
                            <span aria-hidden="true" class="mr-2">:</span
                            >{{ money(receipt.amount_paid) }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-4 border-t border-dashed border-slate-300 pt-4">
                    <h3 class="text-sm font-semibold text-slate-900">
                        Order Details
                    </h3>
                    <dl class="mt-3 space-y-3 text-sm">
                        <div
                            v-for="(item, index) in receipt.items"
                            :key="index"
                            class="flex gap-2"
                        >
                            <dt class="w-[44%] shrink-0 text-slate-600">
                                {{ item.label }}
                            </dt>
                            <dd class="min-w-0 font-semibold text-slate-900">
                                <span
                                    aria-hidden="true"
                                    class="mr-2 font-normal"
                                    >:</span
                                >{{ money(item.amount) }}
                            </dd>
                        </div>
                        <div class="flex gap-2">
                            <dt class="w-[44%] shrink-0 text-slate-600">
                                Total Amount{{
                                    receipt.is_estimate ? ' (estimate)' : ''
                                }}
                            </dt>
                            <dd class="min-w-0 font-semibold text-slate-900">
                                <span
                                    aria-hidden="true"
                                    class="mr-2 font-normal"
                                    >:</span
                                >{{ money(receipt.total) }}
                            </dd>
                        </div>
                        <div v-if="receipt.balance_due > 0" class="flex gap-2">
                            <dt class="w-[44%] shrink-0 text-slate-600">
                                Balance Due
                            </dt>
                            <dd class="min-w-0 font-semibold text-red-700">
                                <span
                                    aria-hidden="true"
                                    class="mr-2 font-normal text-slate-900"
                                    >:</span
                                >{{ money(receipt.balance_due) }}
                            </dd>
                        </div>
                        <template v-if="receipt.amount_tendered !== null">
                            <div class="flex gap-2">
                                <dt class="w-[44%] shrink-0 text-slate-600">
                                    Cash Received
                                </dt>
                                <dd
                                    class="min-w-0 font-semibold text-slate-900"
                                >
                                    <span
                                        aria-hidden="true"
                                        class="mr-2 font-normal"
                                        >:</span
                                    >{{ money(receipt.amount_tendered) }}
                                </dd>
                            </div>
                            <div class="flex gap-2">
                                <dt class="w-[44%] shrink-0 text-slate-600">
                                    Change
                                </dt>
                                <dd
                                    class="min-w-0 font-semibold text-green-700"
                                >
                                    <span
                                        aria-hidden="true"
                                        class="mr-2 font-normal text-slate-900"
                                        >:</span
                                    >{{ money(receipt.change_given ?? 0) }}
                                </dd>
                            </div>
                        </template>
                    </dl>
                </div>

                <div
                    class="mt-4 space-y-2 border-t border-dashed border-slate-300 pt-4"
                >
                    <button
                        type="button"
                        class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-slate-900 text-sm font-medium text-white transition hover:bg-slate-800 focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2 focus-visible:outline-none"
                        @click="printReceipt"
                    >
                        <Printer class="h-4 w-4" />
                        Print Receipt
                    </button>
                    <a
                        :href="pdfUrl"
                        class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-slate-100 text-sm font-medium text-slate-900 transition hover:bg-slate-200 focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:outline-none"
                    >
                        <Download class="h-4 w-4" />
                        Download PDF Receipt
                    </a>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
/* Half-circle notches on both sides of the dashed divider, like a torn ticket. */
.receipt-top {
    border-radius: 1rem 1rem 0 0;
    -webkit-mask:
        radial-gradient(circle 9px at 0 100%, #0000 97%, #000),
        radial-gradient(circle 9px at 100% 100%, #0000 97%, #000);
    -webkit-mask-composite: source-in;
    mask:
        radial-gradient(circle 9px at 0 100%, #0000 97%, #000),
        radial-gradient(circle 9px at 100% 100%, #0000 97%, #000);
    mask-composite: intersect;
}

/* The same notches at the top, and a scalloped bottom edge. */
.receipt-body {
    -webkit-mask:
        radial-gradient(circle 9px at 0 0, #0000 97%, #000),
        radial-gradient(circle 9px at 100% 0, #0000 97%, #000),
        radial-gradient(circle 7px at 50% 100%, #0000 97%, #000) 0 0 / 18px 100%
            repeat-x;
    -webkit-mask-composite: source-in;
    mask:
        radial-gradient(circle 9px at 0 0, #0000 97%, #000),
        radial-gradient(circle 9px at 100% 0, #0000 97%, #000),
        radial-gradient(circle 7px at 50% 100%, #0000 97%, #000) 0 0 / 18px 100%
            repeat-x;
    mask-composite: intersect;
}
</style>
