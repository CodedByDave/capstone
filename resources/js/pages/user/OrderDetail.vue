<script setup lang="ts">
import ComplaintStatusBadge from '@/components/ComplaintStatusBadge.vue';
import BillingBreakdown, {
    type BillingBreakdown as BillingBreakdownData,
} from '@/components/customer/BillingBreakdown.vue';
import CustomerServiceAgreement from '@/components/customer/CustomerServiceAgreement.vue';
import DeliveryRequestPanel from '@/components/customer/DeliveryRequestPanel.vue';
import DeliveryTrackingCard, {
    type DeliveryTracking,
} from '@/components/customer/DeliveryTrackingCard.vue';
import OrderComplaintDialog from '@/components/customer/OrderComplaintDialog.vue';
import OrderRatingDialog from '@/components/customer/OrderRatingDialog.vue';
import UserLayout from '@/layouts/user/UserLayout.vue';
import { isPostponed } from '@/lib/ratingPrompt';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
    CheckCircle2,
    Clock,
    CreditCard,
    Info,
    Loader,
    MapPin,
    Navigation,
    Phone,
    ReceiptText,
    RefreshCw,
    Smartphone,
    Star,
    Truck,
    WashingMachine,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Shop {
    name: string;
    phone: string | null;
    municipality: string;
    barangay: string;
    block_street: string | null;
    latitude: number | null;
    longitude: number | null;
}

interface Order {
    id: number;
    payment_public_id: string;
    order_number: string;
    status: 'pending' | 'in_progress' | 'completed' | 'cancelled';
    payment_status: 'unpaid' | 'partial' | 'paid';
    payment_method: string;
    total_amount: number;
    amount_paid: number;
    balance_due: number;
    payments: { id: string; amount: number; method: string; paid_at: string }[];
    pending: { amount: number; method: string; started_at: string } | null;
    billing: BillingBreakdownData;
    estimated_weight_kg: number | null;
    actual_weight_kg: number | null;
    piece_count?: number | null;
    pickup_type: string;
    customer_address: string | null;
    special_instructions: string | null;
    pricing_model: string;
    price_per_kg: string | null;
    created_at: string;
    completed_at: string | null;
    shop_has_paymongo: boolean;
    shop_uses_demo_payment: boolean;
    shop_offers_delivery: boolean;
    payment_ready: boolean;
    shop: Shop | null;
    service_name: string;
    delivery: DeliveryTracking | null;
    can_track: boolean;
    rating: {
        can_rate: boolean;
        rating: {
            stars: number;
            comment: string | null;
            rated_at: string;
        } | null;
    };
    complaints: {
        can_report: boolean;
        reason: string | null;
        types: Record<string, string>;
        items: {
            public_id: string;
            type: string;
            description: string;
            status: string;
            shop_reply: string | null;
            created_at: string;
            replied_at: string | null;
            photos: { public_id: string; url: string }[];
        }[];
    };
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    order: Order;
    customerAgreement: {
        title: string;
        version: string;
        content: string;
        accepted_at?: string;
        customer_name?: string;
    } | null;
}>();

// ─── Rating ───────────────────────────────────────────────────────────────────

// Open the rating popup when the customer arrives at a finished order, unless they said
// "maybe later" recently; the card below stays available either way.
const ratingOpen = ref(false);
onMounted(() => {
    if (
        props.order.rating.can_rate &&
        !isPostponed(props.order.payment_public_id)
    ) {
        ratingOpen.value = true;
    }
});

// ─── Report a problem ─────────────────────────────────────────────────────────

const complaintOpen = ref(false);

function formatComplaintDate(iso: string) {
    return new Date(iso).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

// ─── Flash toast ──────────────────────────────────────────────────────────────

const page = usePage();
watch(
    () =>
        page.props.toast as
            | {
                  type: 'success' | 'error' | 'warning' | 'info';
                  message: string;
              }
            | undefined,
    (flash) => {
        if (!flash?.message) return;
        (toast[flash.type] ?? toast)(flash.message);
    },
    { immediate: true },
);

// ─── Helpers ──────────────────────────────────────────────────────────────────

const statusSteps = ['pending', 'in_progress', 'completed'];
const currentStep = computed(() => statusSteps.indexOf(props.order.status));

const statusConfig = {
    pending: {
        label: 'Pending',
        icon: Clock,
        classes: 'text-yellow-600 bg-yellow-50',
    },
    in_progress: {
        label: 'In Progress',
        icon: Loader,
        classes: 'text-blue-600 bg-blue-50',
    },
    completed: {
        label: 'Completed',
        icon: CheckCircle2,
        classes: 'text-green-600 bg-green-50',
    },
    cancelled: {
        label: 'Cancelled',
        icon: X,
        classes: 'text-red-600 bg-red-50',
    },
};

// ─── Cancel order ─────────────────────────────────────────────────────────────

const showCancelConfirm = ref(false);
const cancelling = ref(false);

function confirmCancel() {
    cancelling.value = true;
    router.post(
        `/user/orders/${props.order.payment_public_id}/cancel`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                cancelling.value = false;
            },
        },
    );
}

const paymentConfig = {
    unpaid: { label: 'Unpaid', classes: 'bg-red-50 text-red-600' },
    partial: { label: 'Partial', classes: 'bg-orange-50 text-orange-600' },
    paid: { label: 'Paid', classes: 'bg-emerald-50 text-emerald-700' },
};

function formatAddress(shop: Shop) {
    return [shop.block_street, shop.barangay, shop.municipality]
        .filter(Boolean)
        .join(', ');
}

const canRequestDelivery = computed(
    () =>
        props.order.shop_offers_delivery &&
        props.order.status === 'completed' &&
        props.order.payment_status === 'paid' &&
        !props.order.delivery,
);

// ─── Pay Online ───────────────────────────────────────────────────────────────

const payingOnline = ref(false);
const onlinePaymentMethods = [
    {
        value: 'gcash',
        label: 'GCash',
        image: 'https://upload.wikimedia.org/wikipedia/commons/5/52/GCash_logo.svg',
    },
    {
        value: 'maya',
        label: 'Maya',
        image: 'https://upload.wikimedia.org/wikipedia/commons/e/e6/Maya_logo.svg',
    },
    {
        value: 'card',
        label: 'Card',
        image: 'https://upload.wikimedia.org/wikipedia/commons/9/98/Visa_Inc._logo_%282005%E2%80%932014%29.svg',
    },
    {
        value: 'grabpay',
        label: 'GrabPay',
        image: 'https://upload.wikimedia.org/wikipedia/commons/f/f6/Grab_Logo.svg',
    },
] as const;
const selectedOnlineMethod = ref<'gcash' | 'maya' | 'card' | 'grabpay'>(
    onlinePaymentMethods.some(
        (method) => method.value === props.order.payment_method,
    )
        ? (props.order.payment_method as 'gcash' | 'maya' | 'card' | 'grabpay')
        : 'gcash',
);

const canPayOnline = computed(
    () =>
        props.order.shop_has_paymongo &&
        props.order.payment_status !== 'paid' &&
        props.order.status !== 'cancelled' &&
        props.order.payment_ready &&
        (props.order.shop_uses_demo_payment ||
            props.order.billing.balance_due >= 100),
);

// Pay everything that is left, or only a part of it.
const MIN_ONLINE_PAYMENT = 100;
const payMode = ref<'full' | 'partial'>('full');
const partialAmount = ref('');

const canPayInParts = computed(
    () => props.order.balance_due > MIN_ONLINE_PAYMENT,
);

const partialError = computed(() => {
    if (payMode.value !== 'partial') return '';
    const amount = Number(partialAmount.value);
    if (!partialAmount.value || !Number.isFinite(amount)) {
        return 'Enter how much you want to pay.';
    }
    if (amount < MIN_ONLINE_PAYMENT) {
        return 'The smallest amount you can pay online is ₱100.';
    }
    if (amount > props.order.balance_due) {
        return `That is more than the remaining ${money(props.order.balance_due)}.`;
    }
    return '';
});

const amountToPay = computed(() =>
    payMode.value === 'partial'
        ? Number(partialAmount.value) || 0
        : props.order.balance_due,
);

const money = (value: number) =>
    `₱${value.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;

const formatPaidAt = (value: string) =>
    new Date(value).toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });

const checkingPayment = ref(false);

function checkPayment() {
    checkingPayment.value = true;
    router.post(
        `/user/orders/${props.order.payment_public_id}/payment/check`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                checkingPayment.value = false;
            },
        },
    );
}

function payOnline() {
    if (partialError.value) {
        toast.error(partialError.value);
        return;
    }

    payingOnline.value = true;
    router.post(
        `/user/orders/${props.order.payment_public_id}/pay-online`,
        {
            payment_method: selectedOnlineMethod.value,
            ...(payMode.value === 'partial'
                ? { amount: partialAmount.value }
                : {}),
        },
        {
            preserveScroll: true,
            // Flash toasts from the redirect are shown by the page-level watcher.
            onError: (errors) => {
                toast.error(
                    String(
                        errors.amount ??
                            errors.payment_method ??
                            'Checkout could not be opened. Please try again.',
                    ),
                );
            },
            onFinish: () => {
                payingOnline.value = false;
            },
        },
    );
}

const showDeliveryForm = ref(false);

const shopLocation = computed(() =>
    props.order.shop?.latitude != null && props.order.shop?.longitude != null
        ? { lat: props.order.shop.latitude, lng: props.order.shop.longitude }
        : null,
);
</script>

<template>
    <Head :title="`Order ${order.order_number}`" />
    <UserLayout>
        <!-- Back -->
        <div class="px-4 pt-4">
            <button
                class="mb-4 flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700"
                @click="router.visit('/user/orders')"
            >
                <ArrowLeft class="h-4 w-4" />
                Back to orders
            </button>
        </div>

        <div class="space-y-4 px-4 pb-8">
            <!-- ── Status progress bar ────────────────────────── -->
            <div
                class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm"
            >
                <p
                    class="mb-3 text-xs font-semibold tracking-wide text-gray-400 uppercase"
                >
                    Order Status
                </p>

                <!-- Cancelled banner -->
                <div
                    v-if="order.status === 'cancelled'"
                    class="flex items-center gap-2 rounded-xl border border-red-100 bg-red-50 px-4 py-3"
                >
                    <X class="h-4 w-4 shrink-0 text-red-500" />
                    <p class="text-sm font-semibold text-red-600">
                        This order has been cancelled.
                    </p>
                </div>

                <!-- Normal progress steps -->
                <div v-else class="flex items-center gap-0">
                    <template v-for="(step, idx) in statusSteps" :key="step">
                        <div class="flex flex-col items-center gap-1">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full border-2 text-xs font-bold transition-all"
                                :class="
                                    idx <= currentStep
                                        ? 'border-blue-600 bg-blue-600 text-white'
                                        : 'border-gray-200 bg-white text-gray-400'
                                "
                            >
                                <CheckCircle2
                                    v-if="idx < currentStep"
                                    class="h-4 w-4"
                                />
                                <span v-else>{{ idx + 1 }}</span>
                            </div>
                            <span
                                class="text-[10px] font-medium whitespace-nowrap"
                                :class="
                                    idx <= currentStep
                                        ? 'text-blue-600'
                                        : 'text-gray-400'
                                "
                            >
                                {{
                                    statusConfig[
                                        step as keyof typeof statusConfig
                                    ]?.label
                                }}
                            </span>
                        </div>
                        <div
                            v-if="idx < statusSteps.length - 1"
                            class="mx-1 mb-4 h-0.5 flex-1 transition-all"
                            :class="
                                idx < currentStep
                                    ? 'bg-blue-600'
                                    : 'bg-gray-200'
                            "
                        />
                    </template>
                </div>
            </div>

            <!-- ── Order info ─────────────────────────────────── -->
            <div
                class="space-y-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <p
                        class="text-xs font-semibold tracking-wide text-gray-400 uppercase"
                    >
                        Order Info
                    </p>
                    <span class="font-mono text-xs text-gray-500">{{
                        order.order_number
                    }}</span>
                </div>

                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100"
                    >
                        <WashingMachine class="h-5 w-5 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">
                            {{ order.service_name }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ order.shop?.name ?? '—' }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div
                        v-if="order.pricing_model === 'per_piece'"
                        class="col-span-2 rounded-xl bg-gray-50 p-3"
                    >
                        <p class="mb-0.5 text-gray-400">Pieces</p>
                        <p class="font-semibold text-gray-700">
                            {{ order.piece_count }}
                        </p>
                    </div>
                    <div
                        v-if="order.pricing_model !== 'per_piece'"
                        class="rounded-xl bg-gray-50 p-3"
                    >
                        <p class="mb-0.5 text-gray-400">Est. weight</p>
                        <p class="font-semibold text-gray-700">
                            {{ order.estimated_weight_kg }} kg
                        </p>
                    </div>
                    <div
                        v-if="order.pricing_model !== 'per_piece'"
                        class="rounded-xl bg-gray-50 p-3"
                    >
                        <p class="mb-0.5 text-gray-400">Actual weight</p>
                        <p class="font-semibold text-gray-700">
                            {{
                                order.actual_weight_kg
                                    ? `${order.actual_weight_kg} kg`
                                    : '—'
                            }}
                        </p>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-3">
                        <p class="mb-0.5 text-gray-400">Drop-off</p>
                        <p class="font-semibold text-gray-700 capitalize">
                            {{ order.pickup_type.replace('_', '-') }}
                        </p>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-3">
                        <p class="mb-0.5 text-gray-400">Placed on</p>
                        <p class="font-semibold text-gray-700">
                            {{ order.created_at }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="order.special_instructions"
                    class="flex items-start gap-2 rounded-xl bg-amber-50 p-3 text-xs text-amber-700"
                >
                    <Info class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                    <span>{{ order.special_instructions }}</span>
                </div>
            </div>

            <!-- ── Payment ────────────────────────────────────── -->
            <div
                class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm"
            >
                <p
                    class="mb-3 text-xs font-semibold tracking-wide text-gray-400 uppercase"
                >
                    Payment
                </p>

                <div class="mb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <CreditCard class="h-4 w-4 text-gray-400" />
                        <span class="text-sm text-gray-600 capitalize">{{
                            order.payment_method
                        }}</span>
                    </div>
                    <span
                        class="rounded-full px-2.5 py-1 text-xs font-semibold"
                        :class="
                            paymentConfig[order.payment_status]?.classes ??
                            'bg-gray-100 text-gray-600'
                        "
                    >
                        {{ paymentConfig[order.payment_status]?.label }}
                    </span>
                </div>

                <BillingBreakdown :breakdown="order.billing" />

                <div v-if="order.payments.length" class="mt-3 space-y-1">
                    <p
                        class="text-xs font-semibold tracking-wide text-gray-400 uppercase"
                    >
                        Payments made
                    </p>
                    <div
                        v-for="payment in order.payments"
                        :key="payment.id"
                        class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 text-xs"
                    >
                        <span class="text-gray-600 capitalize"
                            >{{ payment.method }} ·
                            {{ formatPaidAt(payment.paid_at) }}</span
                        >
                        <span class="font-semibold text-emerald-700">{{
                            money(payment.amount)
                        }}</span>
                    </div>
                </div>

                <div
                    v-if="order.pending"
                    class="mt-3 space-y-2 rounded-xl bg-amber-50 p-3 text-xs text-amber-800"
                >
                    <p>
                        Your {{ money(order.pending.amount) }} payment has not
                        been confirmed yet. If you already paid, check again in
                        a moment.
                    </p>
                    <button
                        type="button"
                        :disabled="checkingPayment"
                        class="flex items-center gap-1.5 rounded-lg bg-amber-600 px-3 py-1.5 font-semibold text-white transition hover:bg-amber-700 disabled:opacity-50"
                        @click="checkPayment"
                    >
                        <Loader
                            v-if="checkingPayment"
                            class="h-3.5 w-3.5 animate-spin"
                        />
                        <RefreshCw v-else class="h-3.5 w-3.5" />
                        Check payment status
                    </button>
                </div>

                <button
                    v-if="order.payments.length"
                    type="button"
                    class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 py-3 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100"
                    @click="
                        router.visit(
                            `/user/orders/${order.payment_public_id}/payment/receipt`,
                        )
                    "
                >
                    <ReceiptText class="h-4 w-4" />
                    {{
                        order.payment_status === 'paid'
                            ? 'View Invoice Receipt'
                            : 'View Payment Receipt'
                    }}
                </button>

                <!-- Pay Online button (shown when shop has PayMongo and order is unpaid) -->
                <div v-if="canPayOnline" class="mt-3 space-y-2">
                    <div>
                        <p class="mb-2 text-xs font-semibold text-gray-600">
                            Choose payment method
                        </p>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                v-for="method in onlinePaymentMethods"
                                :key="method.value"
                                type="button"
                                class="flex min-h-14 items-center justify-center gap-2 rounded-xl border-2 bg-white px-3 py-2 transition"
                                :class="
                                    selectedOnlineMethod === method.value
                                        ? 'border-blue-600 bg-blue-50 ring-1 ring-blue-600'
                                        : 'border-gray-200 hover:border-gray-300'
                                "
                                @click="selectedOnlineMethod = method.value"
                            >
                                <img
                                    :src="method.image"
                                    :alt="`${method.label} logo`"
                                    class="h-6 max-w-16 object-contain"
                                />
                                <span
                                    class="text-xs font-semibold text-gray-700"
                                >
                                    {{ method.label }}
                                </span>
                            </button>
                        </div>
                    </div>
                    <div v-if="canPayInParts" class="space-y-2">
                        <p class="text-xs font-semibold text-gray-600">
                            How much do you want to pay?
                        </p>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                type="button"
                                class="rounded-xl border-2 px-3 py-2 text-xs font-semibold transition"
                                :class="
                                    payMode === 'full'
                                        ? 'border-blue-600 bg-blue-50 text-blue-700 ring-1 ring-blue-600'
                                        : 'border-gray-200 text-gray-700 hover:border-gray-300'
                                "
                                @click="payMode = 'full'"
                            >
                                Full balance
                                <span class="block font-normal">{{
                                    money(order.balance_due)
                                }}</span>
                            </button>
                            <button
                                type="button"
                                class="rounded-xl border-2 px-3 py-2 text-xs font-semibold transition"
                                :class="
                                    payMode === 'partial'
                                        ? 'border-blue-600 bg-blue-50 text-blue-700 ring-1 ring-blue-600'
                                        : 'border-gray-200 text-gray-700 hover:border-gray-300'
                                "
                                @click="payMode = 'partial'"
                            >
                                Pay part now
                                <span class="block font-normal">Min. ₱100</span>
                            </button>
                        </div>
                        <div v-if="payMode === 'partial'" class="space-y-1">
                            <input
                                v-model="partialAmount"
                                type="number"
                                step="0.01"
                                :min="MIN_ONLINE_PAYMENT"
                                :max="order.balance_due"
                                inputmode="decimal"
                                placeholder="Amount in ₱"
                                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            />
                            <p
                                v-if="partialError && partialAmount"
                                class="text-xs text-red-500"
                            >
                                {{ partialError }}
                            </p>
                            <p v-else class="text-xs text-muted-foreground">
                                You will still owe
                                {{
                                    money(
                                        Math.max(
                                            0,
                                            order.balance_due - amountToPay,
                                        ),
                                    )
                                }}
                                after this payment.
                            </p>
                        </div>
                    </div>
                    <button
                        :disabled="
                            payingOnline ||
                            (payMode === 'partial' && !!partialError)
                        "
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-50"
                        @click="payOnline"
                    >
                        <Loader
                            v-if="payingOnline"
                            class="h-4 w-4 animate-spin"
                        />
                        <Smartphone v-else class="h-4 w-4" />
                        {{
                            payingOnline
                                ? 'Redirecting to payment…'
                                : `Pay ${money(amountToPay)} with ${onlinePaymentMethods.find((method) => method.value === selectedOnlineMethod)?.label}`
                        }}
                    </button>
                    <p class="text-center text-xs text-muted-foreground">
                        GCash · Maya · Card · GrabPay — secured by PayMongo
                    </p>
                </div>

                <div
                    v-else-if="order.payment_status !== 'paid'"
                    class="mt-3 flex items-start gap-2 rounded-xl bg-amber-50 p-3 text-xs text-amber-700"
                >
                    <Info class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                    <span v-if="!order.payment_ready">
                        Awaiting the shop's actual weight. The amount shown is
                        only an estimate; online payment will unlock after the
                        final weight and price are confirmed.
                    </span>
                    <span v-else>
                        Online payment is unavailable for this order. Please
                        coordinate payment with the shop.
                    </span>
                </div>
            </div>

            <!-- ── Shop info ──────────────────────────────────── -->
            <CustomerServiceAgreement
                v-if="customerAgreement"
                :agreement="customerAgreement"
                readonly
            />

            <div
                v-if="order.shop"
                class="space-y-2.5 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm"
            >
                <p
                    class="text-xs font-semibold tracking-wide text-gray-400 uppercase"
                >
                    Shop
                </p>
                <p class="text-sm font-semibold text-gray-800">
                    {{ order.shop.name }}
                </p>
                <div class="flex items-start gap-2 text-xs text-gray-500">
                    <MapPin class="mt-0.5 h-3.5 w-3.5 shrink-0 text-gray-400" />
                    <span>{{ formatAddress(order.shop) }}</span>
                </div>
                <div
                    v-if="order.shop.phone"
                    class="flex items-center gap-2 text-xs text-gray-500"
                >
                    <Phone class="h-3.5 w-3.5 shrink-0 text-gray-400" />
                    <a
                        :href="`tel:${order.shop.phone}`"
                        class="hover:text-blue-600"
                        >{{ order.shop.phone }}</a
                    >
                </div>
            </div>

            <!-- ── Delivery ───────────────────────────────────── -->

            <!-- Full-screen live map of the rider (pickup and delivery) -->
            <Link
                v-if="order.can_track"
                :href="`/user/orders/${order.payment_public_id}/track`"
                class="flex w-full items-center justify-center gap-2 rounded-2xl bg-emerald-600 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700"
            >
                <Navigation class="h-4 w-4" aria-hidden="true" />
                Track live on the map
            </Link>

            <!-- Live tracking for an existing delivery -->
            <DeliveryTrackingCard
                v-if="order.delivery"
                :payment-public-id="order.payment_public_id"
                :delivery="order.delivery"
            />

            <!-- Request delivery (completed + paid, no delivery yet) -->
            <div
                v-else-if="canRequestDelivery"
                class="space-y-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <p
                    class="text-xs font-semibold tracking-wide text-gray-400 uppercase"
                >
                    Delivery
                </p>

                <div v-if="!showDeliveryForm">
                    <p class="mb-3 text-sm text-gray-600 dark:text-gray-300">
                        Your laundry is ready. Want it delivered to you?
                    </p>
                    <button
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 py-3 text-sm font-semibold text-white transition hover:bg-blue-700"
                        @click="showDeliveryForm = true"
                    >
                        <Truck class="h-4 w-4" />
                        Request Delivery
                    </button>
                </div>

                <DeliveryRequestPanel
                    v-else
                    :payment-public-id="order.payment_public_id"
                    :initial-address="order.customer_address"
                    :shop-name="order.shop?.name ?? null"
                    :shop-location="shopLocation"
                    @cancel="showDeliveryForm = false"
                />
            </div>

            <!-- ── Rating ─────────────────────────────────────── -->
            <div
                v-if="order.rating.can_rate"
                class="space-y-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm dark:border-amber-500/30 dark:bg-amber-500/10"
            >
                <p
                    class="text-xs font-semibold tracking-wide text-amber-700 uppercase dark:text-amber-300"
                >
                    Rate your order
                </p>
                <p class="text-sm text-gray-700 dark:text-gray-200">
                    Your laundry is done. How did
                    {{ order.shop?.name ?? 'the shop' }} do?
                </p>
                <button
                    type="button"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 py-3 text-sm font-semibold text-white transition hover:bg-amber-600"
                    @click="ratingOpen = true"
                >
                    <Star class="h-4 w-4" />
                    Rate now
                </button>
            </div>
            <div
                v-else-if="order.rating.rating"
                class="space-y-2 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <p
                    class="text-xs font-semibold tracking-wide text-gray-400 uppercase"
                >
                    Your rating
                </p>
                <div
                    class="flex items-center gap-1"
                    role="img"
                    :aria-label="`You rated this order ${order.rating.rating.stars} out of 5 stars`"
                >
                    <Star
                        v-for="n in 5"
                        :key="n"
                        class="h-5 w-5"
                        :class="
                            n <= order.rating.rating.stars
                                ? 'fill-amber-400 text-amber-400'
                                : 'text-gray-300'
                        "
                        aria-hidden="true"
                    />
                </div>
                <p
                    v-if="order.rating.rating.comment"
                    class="text-sm text-gray-600 dark:text-gray-300"
                >
                    “{{ order.rating.rating.comment }}”
                </p>
            </div>

            <OrderRatingDialog
                v-model:open="ratingOpen"
                :payment-public-id="order.payment_public_id"
                :order-number="order.order_number"
                :shop-name="order.shop?.name ?? 'the shop'"
            />

            <!-- ── Report a problem ───────────────────────────── -->
            <div
                v-if="
                    order.complaints.can_report || order.complaints.items.length
                "
                class="space-y-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <p
                    class="text-xs font-semibold tracking-wide text-gray-400 uppercase"
                >
                    Problem with your order?
                </p>

                <div
                    v-for="item in order.complaints.items"
                    :key="item.public_id"
                    class="space-y-2 rounded-xl border border-gray-100 p-3 dark:border-gray-800"
                >
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-semibold">{{ item.type }}</p>
                        <ComplaintStatusBadge :status="item.status" />
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        {{ item.description }}
                    </p>
                    <div v-if="item.photos.length" class="flex flex-wrap gap-2">
                        <a
                            v-for="photo in item.photos"
                            :key="photo.public_id"
                            :href="photo.url"
                            target="_blank"
                            rel="noopener"
                        >
                            <img
                                :src="photo.url"
                                alt="Photo you attached to this report"
                                class="h-16 w-16 rounded-lg border object-cover"
                                loading="lazy"
                            />
                        </a>
                    </div>
                    <p class="text-xs text-gray-400">
                        Sent {{ formatComplaintDate(item.created_at) }}
                    </p>
                    <div
                        v-if="item.shop_reply"
                        class="rounded-lg bg-blue-50 p-3 text-sm text-blue-900 dark:bg-blue-500/10 dark:text-blue-200"
                    >
                        <p class="mb-1 text-xs font-semibold uppercase">
                            Reply from {{ order.shop?.name ?? 'the shop' }}
                        </p>
                        {{ item.shop_reply }}
                    </div>
                </div>

                <button
                    v-if="order.complaints.can_report"
                    type="button"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-red-600 py-3 text-sm font-semibold text-white transition hover:bg-red-700"
                    @click="complaintOpen = true"
                >
                    <AlertTriangle class="h-4 w-4" aria-hidden="true" />
                    Report a problem
                </button>
                <p
                    v-else-if="order.complaints.reason"
                    class="text-xs text-gray-400"
                >
                    {{ order.complaints.reason }}
                </p>
            </div>

            <OrderComplaintDialog
                v-model:open="complaintOpen"
                :payment-public-id="order.payment_public_id"
                :order-number="order.order_number"
                :shop-name="order.shop?.name ?? 'the shop'"
                :types="order.complaints.types"
            />

            <!-- ── Cancel order ───────────────────────────────── -->
            <div
                v-if="order.status === 'pending'"
                class="space-y-3 rounded-2xl border border-red-100 bg-white p-4 shadow-sm"
            >
                <p
                    class="text-xs font-semibold tracking-wide text-gray-400 uppercase"
                >
                    Cancel Order
                </p>

                <!-- Default state -->
                <div v-if="!showCancelConfirm">
                    <p class="mb-3 text-sm text-gray-500">
                        Changed your mind? You can cancel this order while it's
                        still pending.
                    </p>
                    <button
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-red-200 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                        @click="showCancelConfirm = true"
                    >
                        <X class="h-4 w-4" />
                        Cancel Order
                    </button>
                </div>

                <!-- Confirmation state -->
                <div v-else class="space-y-3">
                    <div
                        class="flex items-start gap-2 rounded-xl bg-red-50 px-3 py-3 text-xs text-red-700"
                    >
                        <AlertTriangle class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                        <span
                            >Are you sure you want to cancel order
                            <span class="font-semibold">{{
                                order.order_number
                            }}</span
                            >? This cannot be undone.</span
                        >
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="flex-1 rounded-xl border border-gray-200 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50"
                            :disabled="cancelling"
                            @click="showCancelConfirm = false"
                        >
                            Go Back
                        </button>
                        <button
                            type="button"
                            :disabled="cancelling"
                            class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-red-600 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 disabled:opacity-50"
                            @click="confirmCancel"
                        >
                            <Loader
                                v-if="cancelling"
                                class="h-4 w-4 animate-spin"
                            />
                            <X v-else class="h-4 w-4" />
                            {{ cancelling ? 'Cancelling…' : 'Yes, Cancel' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
