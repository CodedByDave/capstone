<script setup lang="ts">
import { Button } from '@/components/ui/button';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowUp,
    Banknote,
    BarChart3,
    CheckCircle2,
    ClipboardList,
    CreditCard,
    Loader2,
    Package,
    Users,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    planName: string;
    billingMonths: number;
    vatPct: number;
    checkoutUrl: string | null;
    user?: { name: string; email: string };
    shop?: {
        shop_name?: string;
        phone?: string;
        block_street?: string;
        municipality?: string;
        barangay?: string;
        postal_code?: string;
    };
}>();

// ─── Auto-redirect to PayMongo ────────────────────────────────────────────────

// Use watch instead of onMounted so it fires even when Inertia reuses
// the same component instance (no unmount/remount on same-component navigation).
watch(
    () => props.checkoutUrl,
    (url) => {
        if (url) window.location.href = url;
    },
    { immediate: true },
);

// ─── Plan data ────────────────────────────────────────────────────────────────

const PLAN_PRICES: Record<string, number> = {
    Basic: 999,
    Standard: 1499,
    Premium: 1999,
};
const PLAN_MODULES: Record<string, string[]> = {
    Basic: ['HRM', 'Operations', 'Reports & Analytics'],
    Standard: [
        'HRM',
        'Operations',
        'Inventory Management',
        'Finance Management',
        'Reports & Analytics',
    ],
    Premium: [
        'HRM',
        'Operations',
        'Inventory Management',
        'Finance Management',
        'Reports & Analytics',
    ],
};
const modLabel = (mod: string, plan: string) =>
    mod === 'Reports & Analytics'
        ? plan === 'Premium'
            ? 'Advanced Reports & Analytics'
            : 'Basic Reports'
        : mod;

const MODULE_ICONS: Record<string, any> = {
    HRM: Users,
    Operations: ClipboardList,
    'Inventory Management': Package,
    'Finance Management': Banknote,
    'Reports & Analytics': BarChart3,
};

const DISCOUNTS: Record<number, number> = { 1: 0, 12: 10, 24: 20, 48: 30 };

const basePrice = computed(() => PLAN_PRICES[props.planName] ?? 0);
const discountPct = computed(() => DISCOUNTS[props.billingMonths] ?? 0);
const monthlyPrice = computed(() =>
    Math.round(basePrice.value * (1 - discountPct.value / 100)),
);
const subtotal = computed(() => monthlyPrice.value * props.billingMonths);
const vatAmount = computed(() =>
    Math.round(subtotal.value * (props.vatPct / 100)),
);
const totalDue = computed(() => subtotal.value + vatAmount.value);
const savedAmount = computed(
    () => Math.round(basePrice.value * props.billingMonths) - subtotal.value,
);

function fmt(n: number) {
    return '₱' + Math.round(n).toLocaleString('en-PH');
}

function billingLabel(months: number) {
    if (months === 1) return '1 month';
    if (months === 12) return '12 months';
    if (months === 24) return '24 months';
    if (months === 48) return '48 months';
    return `${months} months`;
}

// ─── Payment methods ──────────────────────────────────────────────────────────

interface PaymentMethod {
    key: string;
    label: string;
    description: string;
    icon: string;
    tag?: string;
}

const PAYMENT_METHODS: PaymentMethod[] = [
    {
        key: 'gcash',
        label: 'GCash',
        description: 'Pay via GCash mobile wallet',
        icon: 'https://upload.wikimedia.org/wikipedia/commons/5/52/GCash_logo.svg',
        tag: 'Most Popular',
    },
    {
        key: 'maya',
        label: 'Maya',
        description: 'Pay via Maya (PayMaya) wallet',
        icon: 'https://upload.wikimedia.org/wikipedia/commons/e/e6/Maya_logo.svg',
    },
    {
        key: 'card',
        label: 'Credit / Debit Card',
        description: 'Visa, Mastercard, JCB',
        icon: 'https://upload.wikimedia.org/wikipedia/commons/9/98/Visa_Inc._logo_%282005%E2%80%932014%29.svg',
    },
    {
        key: 'grab_pay',
        label: 'GrabPay',
        description: 'Pay via GrabPay wallet',
        icon: 'https://upload.wikimedia.org/wikipedia/commons/f/f6/Grab_Logo.svg',
    },
    {
        key: 'dob',
        label: 'Online Banking',
        description: 'BDO, BPI, UnionBank, Metrobank',
        icon: 'https://upload.wikimedia.org/wikipedia/commons/4/49/BDO_Unibank_%28logo%29.svg',
    },
    {
        key: 'billease',
        label: 'BillEase',
        description: 'Buy now, pay later',
        icon: 'https://logobase.net/wp-content/uploads/2025/08/BillEase-Logo-1.webp',
        tag: 'Installment',
    },
];

const selectedPayment = ref('');

// ─── Submit ───────────────────────────────────────────────────────────────────

const submitting = ref(false);
const submitError = ref('');

const canSubmit = computed(() => !!selectedPayment.value);

function submit() {
    if (!canSubmit.value || submitting.value) return;
    submitting.value = true;
    submitError.value = '';

    router.post(
        '/shop/upgrade/process',
        {
            plan_name: props.planName,
            billing_months: props.billingMonths,
            payment_method: selectedPayment.value,
        },
        {
            onFinish: () => {
                submitting.value = false;
            },
        },
    );
}
</script>

<template>
    <Head title="Confirm Upgrade" />

    <ShopLayout title="Confirm Upgrade">
        <!-- Redirect splash while navigating to PayMongo -->
        <div
            v-if="checkoutUrl"
            class="flex flex-col items-center justify-center gap-4 py-32"
        >
            <Loader2 class="h-10 w-10 animate-spin text-indigo-600" />
            <p class="text-sm text-muted-foreground">
                Redirecting to payment gateway…
            </p>
        </div>

        <div v-else class="mx-auto max-w-2xl space-y-6 px-4 py-8">
            <!-- Header -->
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold">
                    <ArrowUp class="h-6 w-6 text-indigo-600" />
                    Confirm Upgrade
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Review your upgrade details and select a payment method.
                </p>
            </div>

            <!-- Plan summary card -->
            <div class="space-y-4 rounded-2xl border bg-white p-5 shadow-sm">
                <p
                    class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                >
                    Upgrade Plan
                </p>

                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-lg font-bold text-gray-900">
                            {{ planName }}
                        </p>
                        <p class="mt-0.5 text-xs text-muted-foreground">
                            {{ billingLabel(billingMonths) }} billing
                            <template v-if="discountPct > 0">
                                ·
                                <span class="font-semibold text-emerald-600"
                                    >{{ discountPct }}% off</span
                                >
                            </template>
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-indigo-700">
                            {{ fmt(monthlyPrice)
                            }}<span
                                class="text-xs font-normal text-muted-foreground"
                                >/mo</span
                            >
                        </p>
                    </div>
                </div>

                <!-- Included modules -->
                <ul class="flex flex-wrap gap-2">
                    <li
                        v-for="mod in PLAN_MODULES[planName]"
                        :key="mod"
                        class="flex items-center gap-1.5 rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700"
                    >
                        <component
                            :is="MODULE_ICONS[mod]"
                            class="h-3 w-3 shrink-0"
                        />
                        {{ modLabel(mod, planName) }}
                    </li>
                </ul>

                <!-- Price breakdown -->
                <div class="space-y-2 border-t pt-4 text-sm">
                    <div class="flex justify-between text-muted-foreground">
                        <span>Monthly price</span>
                        <span>{{ fmt(monthlyPrice) }}</span>
                    </div>
                    <div class="flex justify-between text-muted-foreground">
                        <span>Duration</span>
                        <span>{{ billingLabel(billingMonths) }}</span>
                    </div>
                    <div class="flex justify-between text-muted-foreground">
                        <span>Subtotal</span>
                        <span>{{ fmt(subtotal) }}</span>
                    </div>
                    <div
                        v-if="savedAmount > 0"
                        class="flex justify-between text-emerald-600"
                    >
                        <span>Discount savings</span>
                        <span class="font-semibold"
                            >−{{ fmt(savedAmount) }}</span
                        >
                    </div>
                    <div class="flex justify-between text-muted-foreground">
                        <span>VAT ({{ vatPct }}%)</span>
                        <span>{{ fmt(vatAmount) }}</span>
                    </div>
                    <div
                        class="flex justify-between border-t pt-2 text-base font-bold"
                    >
                        <span>Total</span>
                        <span class="text-indigo-700">{{ fmt(totalDue) }}</span>
                    </div>
                </div>
            </div>

            <!-- Shop & user info (read-only) -->
            <div
                v-if="shop || user"
                class="space-y-3 rounded-2xl border bg-white p-5 shadow-sm"
            >
                <p
                    class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                >
                    Account Details
                </p>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div v-if="user?.name">
                        <p class="mb-0.5 text-xs text-muted-foreground">Name</p>
                        <p class="font-medium">{{ user.name }}</p>
                    </div>
                    <div v-if="user?.email">
                        <p class="mb-0.5 text-xs text-muted-foreground">
                            Email
                        </p>
                        <p class="font-medium">{{ user.email }}</p>
                    </div>
                    <div v-if="shop?.shop_name">
                        <p class="mb-0.5 text-xs text-muted-foreground">
                            Shop Name
                        </p>
                        <p class="font-medium">{{ shop.shop_name }}</p>
                    </div>
                    <div v-if="shop?.phone">
                        <p class="mb-0.5 text-xs text-muted-foreground">
                            Phone
                        </p>
                        <p class="font-medium">{{ shop.phone }}</p>
                    </div>
                    <div v-if="shop?.municipality" class="col-span-2">
                        <p class="mb-0.5 text-xs text-muted-foreground">
                            Address
                        </p>
                        <p class="font-medium">
                            {{
                                [
                                    shop.block_street,
                                    shop.barangay,
                                    shop.municipality,
                                    shop.postal_code,
                                ]
                                    .filter(Boolean)
                                    .join(', ')
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Payment method -->
            <div class="space-y-3 rounded-2xl border bg-white p-5 shadow-sm">
                <p
                    class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                >
                    Payment Method
                </p>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <button
                        v-for="method in PAYMENT_METHODS"
                        :key="method.key"
                        type="button"
                        @click="selectedPayment = method.key"
                        class="relative flex items-center gap-3 rounded-xl border p-3 text-left transition-all focus:outline-none"
                        :class="
                            selectedPayment === method.key
                                ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-400'
                                : 'border-gray-200 hover:border-indigo-300'
                        "
                    >
                        <img
                            :src="method.icon"
                            :alt="method.label"
                            class="h-6 w-10 shrink-0 object-contain"
                        />
                        <div class="min-w-0 flex-1">
                            <p
                                class="flex items-center gap-1.5 text-xs font-semibold text-gray-900"
                            >
                                {{ method.label }}
                                <span
                                    v-if="method.tag"
                                    class="rounded-full bg-indigo-100 px-1.5 py-0.5 text-[9px] font-bold text-indigo-700"
                                >
                                    {{ method.tag }}
                                </span>
                            </p>
                            <p
                                class="truncate text-[10px] text-muted-foreground"
                            >
                                {{ method.description }}
                            </p>
                        </div>
                        <CheckCircle2
                            v-if="selectedPayment === method.key"
                            class="h-4 w-4 shrink-0 text-indigo-500"
                        />
                    </button>
                </div>
            </div>

            <!-- CTA -->
            <p v-if="submitError" class="pb-3 text-right text-sm text-red-600">
                {{ submitError }}
            </p>
            <div class="flex justify-end gap-3 pb-8">
                <Button variant="back" @click="router.get('/shop/upgrade')">
                    Back
                </Button>
                <Button
                    :disabled="!canSubmit || submitting"
                    class="gap-2 px-8"
                    @click="submit"
                >
                    <Loader2 v-if="submitting" class="h-4 w-4 animate-spin" />
                    <CreditCard v-else class="h-4 w-4" />
                    {{ submitting ? 'Processing…' : 'Pay ' + fmt(totalDue) }}
                </Button>
            </div>
        </div>
    </ShopLayout>
</template>
