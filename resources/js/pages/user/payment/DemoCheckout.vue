<script setup lang="ts">
import UserLayout from '@/layouts/user/UserLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    FlaskConical,
    Loader2,
    LockKeyhole,
    ShieldCheck,
} from 'lucide-vue-next';
import { computed, nextTick, ref } from 'vue';

const props = defineProps<{
    order: {
        id: number;
        payment_public_id: string;
        order_number: string;
        customer_name: string;
        amount: number;
        shop_name: string;
        session_id: string;
    };
}>();

const method = ref<'gcash' | 'maya' | 'card' | 'grabpay'>('gcash');
const processing = ref(false);
const step = ref<'method' | 'details' | 'verify'>('method');
const mobileNumber = ref('09171234567');
const cardholderName = ref('Demo Customer');
const cardNumber = ref('4120 0000 0000 0007');
const cardExpiry = ref('12/30');
const cardCvc = ref('123');
const otp = ref('');
const errorMessage = ref('');
const checkoutPanel = ref<HTMLElement | null>(null);

const methods = [
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

const selectedMethod = computed(() =>
    methods.find((item) => item.value === method.value),
);

const isCard = computed(() => method.value === 'card');

function chooseMethod(value: (typeof methods)[number]['value']) {
    method.value = value;
    step.value = 'method';
    errorMessage.value = '';
}

async function revealCurrentStep() {
    await nextTick();
    checkoutPanel.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

async function continueToDetails() {
    errorMessage.value = '';
    step.value = 'details';
    await revealCurrentStep();
}

async function continueToVerification() {
    errorMessage.value = '';

    if (isCard.value) {
        if (
            !cardholderName.value.trim() ||
            cardNumber.value.replace(/\s/g, '').length < 12 ||
            !/^\d{2}\/\d{2}$/.test(cardExpiry.value) ||
            !/^\d{3,4}$/.test(cardCvc.value)
        ) {
            errorMessage.value = 'Enter the provided demo card details.';
            return;
        }
    } else if (!/^09\d{9}$/.test(mobileNumber.value.replace(/\s/g, ''))) {
        errorMessage.value = 'Enter an 11-digit demo mobile number.';
        return;
    }

    step.value = 'verify';
    await revealCurrentStep();
}

function goBack() {
    errorMessage.value = '';
    step.value = step.value === 'verify' ? 'details' : 'method';
}

function completePayment() {
    errorMessage.value = '';

    if (otp.value !== '123456') {
        errorMessage.value = 'Use the demo OTP 123456 to approve payment.';
        return;
    }

    processing.value = true;
    router.post(
        `/user/orders/${props.order.payment_public_id}/pay-online`,
        { payment_method: method.value },
        {
            preserveScroll: true,
            onError: () => {
                errorMessage.value =
                    'PayMongo test checkout could not be opened. Please try again.';
            },
            onFinish: () => (processing.value = false),
        },
    );
}

function cancelPayment() {
    router.get(`/user/orders/${props.order.payment_public_id}/payment/cancel`);
}

function peso(value: number) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(value);
}
</script>

<template>
    <Head title="Demo Checkout" />
    <UserLayout>
        <div class="space-y-4 px-4 py-6">
            <div
                class="flex items-start gap-3 rounded-2xl border border-violet-200 bg-violet-50 p-4"
            >
                <FlaskConical class="mt-0.5 h-5 w-5 shrink-0 text-violet-600" />
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="font-semibold text-violet-950">
                            LaundryHub Demo Checkout
                        </p>
                        <span
                            class="rounded-full bg-violet-200 px-2 py-0.5 text-[10px] font-bold text-violet-800 uppercase"
                        >
                            Simulation
                        </span>
                    </div>
                    <p class="mt-1 text-xs leading-5 text-violet-800">
                        Continue from this legacy demo session to PayMongo's
                        hosted test checkout. It uses test mode, so no real
                        money is charged.
                    </p>
                </div>
            </div>

            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white"
            >
                <div class="border-b border-gray-100 p-5">
                    <p class="text-xs font-medium text-gray-500">Paying</p>
                    <h1 class="mt-1 text-lg font-bold text-gray-900">
                        {{ order.shop_name }}
                    </h1>
                    <p class="mt-0.5 text-sm text-gray-500">
                        Laundry order {{ order.order_number }}
                    </p>
                </div>

                <div class="p-5 text-center">
                    <p
                        class="text-xs font-medium tracking-wide text-gray-500 uppercase"
                    >
                        Amount due
                    </p>
                    <p class="mt-1 text-3xl font-bold text-gray-950">
                        {{ peso(order.amount) }}
                    </p>
                </div>
            </div>

            <div
                ref="checkoutPanel"
                class="scroll-mt-4 rounded-2xl border border-gray-200 bg-white p-5"
            >
                <p class="text-sm font-semibold text-gray-900">
                    Select a simulated payment method
                </p>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <button
                        v-for="item in methods"
                        :key="item.value"
                        type="button"
                        :class="[
                            'relative flex items-center gap-2 rounded-xl border px-3 py-3 text-left text-sm font-medium transition',
                            method === item.value
                                ? 'border-blue-500 bg-blue-50 text-blue-800 ring-1 ring-blue-500'
                                : 'border-gray-200 text-gray-700 hover:bg-gray-50',
                        ]"
                        @click="chooseMethod(item.value)"
                    >
                        <img
                            :src="item.image"
                            :alt="`${item.label} logo`"
                            class="h-7 w-14 shrink-0 object-contain"
                        />
                        <span>{{ item.label }}</span>
                        <CheckCircle2
                            v-if="method === item.value"
                            class="absolute top-2 right-2 h-4 w-4 text-blue-600"
                        />
                    </button>
                </div>

                <div
                    v-if="step === 'method'"
                    class="mt-4 flex items-start gap-2 rounded-xl bg-gray-50 p-3 text-xs leading-5 text-gray-600"
                >
                    <ShieldCheck
                        class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600"
                    />
                    Continue to the simulated {{ selectedMethod?.label }}
                    authorization, then continue to PayMongo's hosted test
                    checkout through LaundryHub's test account.
                </div>

                <button
                    v-if="step === 'method'"
                    type="button"
                    class="mt-4 flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700"
                    @click.prevent="continueToDetails"
                >
                    <Loader2
                        v-if="processing"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    <LockKeyhole v-else class="mr-2 h-4 w-4" />
                    {{
                        processing
                            ? 'Simulating payment…'
                            : `Continue with ${selectedMethod?.label}`
                    }}
                </button>

                <div
                    v-if="step !== 'method'"
                    class="mt-4 border-t border-gray-100 pt-4"
                >
                    <button
                        type="button"
                        class="mb-4 flex items-center gap-1 text-xs font-semibold text-gray-500 hover:text-gray-800"
                        :disabled="processing"
                        @click="goBack"
                    >
                        <ArrowLeft class="h-3.5 w-3.5" />
                        Back
                    </button>

                    <div class="mb-4 flex items-center gap-3">
                        <img
                            :src="selectedMethod?.image"
                            :alt="`${selectedMethod?.label} logo`"
                            class="h-8 w-16 object-contain"
                        />
                        <div>
                            <p class="text-sm font-bold text-gray-900">
                                {{ selectedMethod?.label }} Demo Payment
                            </p>
                            <p class="text-xs text-gray-500">
                                Simulation only · {{ peso(order.amount) }}
                            </p>
                        </div>
                    </div>

                    <div v-if="step === 'details'" class="space-y-3">
                        <template v-if="isCard">
                            <label
                                class="block text-xs font-semibold text-gray-700"
                            >
                                Cardholder name
                                <input
                                    v-model="cardholderName"
                                    type="text"
                                    class="mt-1 h-11 w-full rounded-xl border border-gray-200 px-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                />
                            </label>
                            <label
                                class="block text-xs font-semibold text-gray-700"
                            >
                                Demo card number
                                <input
                                    v-model="cardNumber"
                                    inputmode="numeric"
                                    type="text"
                                    class="mt-1 h-11 w-full rounded-xl border border-gray-200 px-3 font-mono text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                />
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label
                                    class="block text-xs font-semibold text-gray-700"
                                >
                                    Expiry
                                    <input
                                        v-model="cardExpiry"
                                        type="text"
                                        class="mt-1 h-11 w-full rounded-xl border border-gray-200 px-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                    />
                                </label>
                                <label
                                    class="block text-xs font-semibold text-gray-700"
                                >
                                    CVC
                                    <input
                                        v-model="cardCvc"
                                        inputmode="numeric"
                                        type="password"
                                        class="mt-1 h-11 w-full rounded-xl border border-gray-200 px-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                    />
                                </label>
                            </div>
                        </template>
                        <label
                            v-else
                            class="block text-xs font-semibold text-gray-700"
                        >
                            Demo mobile number
                            <input
                                v-model="mobileNumber"
                                inputmode="numeric"
                                type="tel"
                                maxlength="11"
                                class="mt-1 h-11 w-full rounded-xl border border-gray-200 px-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            />
                        </label>

                        <button
                            type="button"
                            class="flex h-11 w-full items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700"
                            @click.prevent="continueToVerification"
                        >
                            Send Demo OTP
                        </button>
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            class="rounded-xl border border-blue-200 bg-blue-50 p-3 text-xs leading-5 text-blue-800"
                        >
                            Local demo OTP:
                            <strong class="font-mono">123456</strong>. PayMongo
                            will handle the final test-payment screen.
                        </div>
                        <label
                            class="block text-xs font-semibold text-gray-700"
                        >
                            Enter OTP
                            <input
                                v-model="otp"
                                inputmode="numeric"
                                type="text"
                                maxlength="6"
                                placeholder="123456"
                                class="mt-1 h-12 w-full rounded-xl border border-gray-200 px-3 text-center font-mono text-lg tracking-[0.35em] outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                @keyup.enter="completePayment"
                            />
                        </label>

                        <button
                            type="button"
                            class="flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:pointer-events-none disabled:opacity-50"
                            :disabled="processing"
                            @click.prevent="completePayment"
                        >
                            <Loader2
                                v-if="processing"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            <LockKeyhole v-else class="mr-2 h-4 w-4" />
                            {{
                                processing
                                    ? 'Opening PayMongo…'
                                    : `Continue to PayMongo · ${peso(order.amount)}`
                            }}
                        </button>
                    </div>

                    <p
                        v-if="errorMessage"
                        class="mt-3 rounded-xl bg-red-50 p-3 text-xs font-medium text-red-700"
                    >
                        {{ errorMessage }}
                    </p>
                </div>

                <button
                    type="button"
                    class="mt-3 w-full text-center text-sm font-medium text-gray-500 hover:text-gray-800"
                    :disabled="processing"
                    @click="cancelPayment"
                >
                    Cancel and return to order
                </button>
            </div>

            <p
                class="text-center font-mono text-[10px] break-all text-gray-400"
            >
                Demo session: {{ order.session_id }}
            </p>
        </div>
    </UserLayout>
</template>
