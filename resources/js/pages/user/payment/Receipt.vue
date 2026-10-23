<script setup lang="ts">
import { Button } from '@/components/ui/button';
import UserLayout from '@/layouts/user/UserLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    Download,
    Printer,
    ReceiptText,
} from 'lucide-vue-next';
import { onMounted } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

defineProps<{
    receipt: {
        order_id: number;
        payment_public_id: string;
        order_number: string;
        receipt_number: string;
        payment_reference: string | null;
        payment_method: string | null;
        paid_at: string;
        customer_name: string;
        customer_phone: string | null;
        shop: { name: string; phone: string | null; address: string };
        service_name: string;
        weight_kg: number;
        additional_charges: number;
        supplies: {
            name: string;
            quantity: number;
            unit: string | null;
            unit_price: number | null;
            amount: number | null;
        }[];
        other_charges: number | null;
        discount_amount: number;
        delivery_fee?: number;
        total_amount: number;
        amount_paid: number;
        balance_due: number;
        is_fully_paid: boolean;
        payments: { amount: number; method: string; paid_at: string }[];
    };
}>();

const page = usePage();

onMounted(() => {
    const flash = (page.props as any).toast as
        | { type: string; message: string }
        | undefined;
    if (flash?.type === 'success') toast.success(flash.message);
});

function peso(value: number) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(value);
}

function dateTime(value: string) {
    return new Intl.DateTimeFormat('en-PH', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function printReceipt() {
    window.print();
}
</script>

<template>
    <Head :title="`Receipt ${receipt.receipt_number}`" />
    <UserLayout>
        <div class="receipt-page space-y-4 px-4 py-6">
            <div class="no-print flex items-center justify-between gap-3">
                <Link
                    :href="`/user/orders/${receipt.payment_public_id}`"
                    class="flex items-center gap-1.5 text-sm font-medium text-gray-600 hover:text-gray-900"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to Order
                </Link>
                <Button variant="outline" size="sm" @click="printReceipt">
                    <Download class="mr-2 h-4 w-4" />
                    Print / Save PDF
                </Button>
            </div>

            <article
                class="receipt-sheet overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm"
            >
                <header class="border-b border-gray-100 p-6 text-center">
                    <div
                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-white"
                    >
                        <ReceiptText class="h-6 w-6" />
                    </div>
                    <h1 class="mt-3 text-xl font-bold text-gray-950">
                        {{ receipt.shop.name }}
                    </h1>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ receipt.shop.address }}
                    </p>
                    <p v-if="receipt.shop.phone" class="text-xs text-gray-500">
                        {{ receipt.shop.phone }}
                    </p>
                </header>

                <div class="p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p
                                class="text-xs font-semibold tracking-wide text-gray-400 uppercase"
                            >
                                Invoice receipt
                            </p>
                            <p
                                class="mt-1 font-mono text-sm font-bold text-gray-900"
                            >
                                {{ receipt.receipt_number }}
                            </p>
                        </div>
                        <div
                            class="flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-700"
                        >
                            <CheckCircle2 class="h-4 w-4" />
                            {{ receipt.is_fully_paid ? 'PAID' : 'PARTIAL' }}
                        </div>
                    </div>

                    <dl class="mt-5 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        <div>
                            <dt class="text-xs text-gray-400">Customer</dt>
                            <dd class="mt-0.5 font-medium text-gray-800">
                                {{ receipt.customer_name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">Paid on</dt>
                            <dd class="mt-0.5 font-medium text-gray-800">
                                {{ dateTime(receipt.paid_at) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">Order number</dt>
                            <dd
                                class="mt-0.5 font-mono text-xs font-medium text-gray-800"
                            >
                                {{ receipt.order_number }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">
                                Payment method
                            </dt>
                            <dd
                                class="mt-0.5 font-medium text-gray-800 capitalize"
                            >
                                {{ receipt.payment_method ?? 'Online' }}
                            </dd>
                        </div>
                    </dl>

                    <div
                        class="mt-6 overflow-hidden rounded-xl border border-gray-200"
                    >
                        <div
                            class="grid grid-cols-[1fr_auto] bg-gray-50 px-4 py-2 text-xs font-semibold text-gray-500 uppercase"
                        >
                            <span>Description</span>
                            <span>Amount</span>
                        </div>
                        <div
                            class="grid grid-cols-[1fr_auto] gap-4 px-4 py-4 text-sm"
                        >
                            <div>
                                <p class="font-semibold text-gray-800">
                                    {{ receipt.service_name }}
                                </p>
                                <p
                                    v-if="receipt.weight_kg"
                                    class="mt-0.5 text-xs text-gray-500"
                                >
                                    {{ receipt.weight_kg }} kg
                                </p>
                            </div>
                            <span class="font-semibold text-gray-800">{{
                                peso(
                                    receipt.total_amount -
                                        receipt.additional_charges -
                                        (receipt.delivery_fee ?? 0) +
                                        receipt.discount_amount,
                                )
                            }}</span>
                        </div>
                        <div
                            v-if="receipt.additional_charges > 0"
                            class="flex justify-between border-t border-gray-100 px-4 py-2 text-sm"
                        >
                            <span class="text-gray-500"
                                >Additional charges</span
                            >
                            <span>{{ peso(receipt.additional_charges) }}</span>
                        </div>
                        <div
                            v-for="supply in receipt.supplies"
                            :key="supply.name"
                            class="flex justify-between gap-4 px-4 pb-1.5 pl-8 text-xs text-gray-500"
                        >
                            <span>
                                {{ supply.name }}
                                <template v-if="supply.unit_price !== null">
                                    — {{ Number(supply.quantity) }}
                                    {{ supply.unit }} ×
                                    {{ peso(supply.unit_price) }}
                                </template>
                                <template v-else>
                                    — {{ Number(supply.quantity) }}
                                    {{ supply.unit }}
                                </template>
                            </span>
                            <span v-if="supply.amount !== null">{{
                                peso(supply.amount)
                            }}</span>
                        </div>
                        <div
                            v-if="
                                receipt.supplies.length > 0 &&
                                receipt.other_charges !== null &&
                                receipt.other_charges !== 0
                            "
                            class="flex justify-between gap-4 px-4 pb-1.5 pl-8 text-xs text-gray-500"
                        >
                            <span>{{
                                receipt.other_charges > 0
                                    ? 'Other charges'
                                    : 'Adjustment'
                            }}</span>
                            <span>{{ peso(receipt.other_charges) }}</span>
                        </div>
                        <div
                            v-if="(receipt.delivery_fee ?? 0) > 0"
                            class="flex justify-between border-t border-gray-100 px-4 py-2 text-sm"
                        >
                            <span class="text-gray-500">Delivery fee</span>
                            <span>{{ peso(receipt.delivery_fee ?? 0) }}</span>
                        </div>
                        <div
                            v-if="receipt.discount_amount > 0"
                            class="flex justify-between border-t border-gray-100 px-4 py-2 text-sm text-emerald-700"
                        >
                            <span>Discount</span>
                            <span>-{{ peso(receipt.discount_amount) }}</span>
                        </div>
                        <div
                            class="flex items-center justify-between border-t border-gray-200 bg-gray-50 px-4 py-4"
                        >
                            <span class="font-bold text-gray-900">{{
                                receipt.is_fully_paid
                                    ? 'Total paid'
                                    : 'Paid so far'
                            }}</span>
                            <span class="text-lg font-bold text-gray-950">{{
                                peso(receipt.amount_paid)
                            }}</span>
                        </div>
                        <div
                            v-if="!receipt.is_fully_paid"
                            class="flex items-center justify-between border-t border-gray-200 px-4 py-3 text-sm"
                        >
                            <span class="font-semibold text-orange-700"
                                >Balance remaining</span
                            >
                            <span class="font-semibold text-orange-700">{{
                                peso(receipt.balance_due)
                            }}</span>
                        </div>
                    </div>

                    <div v-if="receipt.payments.length > 1" class="mt-5">
                        <p
                            class="text-[10px] font-semibold tracking-wide text-gray-400 uppercase"
                        >
                            Payments received
                        </p>
                        <div
                            v-for="(payment, index) in receipt.payments"
                            :key="index"
                            class="mt-1 flex justify-between text-xs text-gray-600"
                        >
                            <span class="capitalize"
                                >{{ payment.method }} ·
                                {{ dateTime(payment.paid_at) }}</span
                            >
                            <span class="font-medium">{{
                                peso(payment.amount)
                            }}</span>
                        </div>
                    </div>

                    <div class="mt-5 rounded-xl bg-gray-50 p-3">
                        <p
                            class="text-[10px] font-semibold tracking-wide text-gray-400 uppercase"
                        >
                            Payment reference
                        </p>
                        <p
                            class="mt-1 font-mono text-xs break-all text-gray-600"
                        >
                            {{ receipt.payment_reference ?? 'Not available' }}
                        </p>
                    </div>

                    <p class="mt-6 text-center text-xs leading-5 text-gray-400">
                        Thank you for choosing {{ receipt.shop.name }}.<br />
                        This document serves as your payment receipt.
                    </p>
                </div>
            </article>

            <Button class="no-print w-full" @click="printReceipt">
                <Printer class="mr-2 h-4 w-4" />
                Print Invoice Receipt
            </Button>
        </div>
    </UserLayout>
</template>

<style scoped>
@media print {
    :global(body) {
        background: white !important;
    }

    :global(header),
    :global(nav),
    .no-print {
        display: none !important;
    }

    .receipt-page {
        padding: 0 !important;
    }

    .receipt-sheet {
        border: 0 !important;
        box-shadow: none !important;
    }
}
</style>
