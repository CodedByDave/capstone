<script setup lang="ts">
import { Button } from '@/components/ui/button';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowUp,
    Banknote,
    BarChart3,
    Check,
    ClipboardList,
    Clock,
    Loader2,
    Package,
    RotateCcw,
    Users,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    currentPlan: string | null;
    currentExpiresAt: string | null;
    availableUpgrades: string[];
    isExpired: boolean;
    vatPct: number;
}>();

// ─── Plan data ────────────────────────────────────────────────────────────────

interface Plan {
    name: string;
    price: number;
    badge?: string;
    moduleNames: string[];
}

const MODULE_ICONS: Record<string, any> = {
    HRM: Users,
    Operations: ClipboardList,
    'Inventory Management': Package,
    'Finance Management': Banknote,
    'Reports & Analytics': BarChart3,
};

const modLabel = (mod: string, plan: string) =>
    mod === 'Reports & Analytics'
        ? plan === 'Premium'
            ? 'Advanced Reports & Analytics'
            : 'Basic Reports'
        : mod;

const ALL_PLANS: Plan[] = [
    {
        name: 'Basic',
        price: 999,
        moduleNames: ['HRM', 'Operations', 'Reports & Analytics'],
    },
    {
        name: 'Standard',
        price: 1499,
        badge: 'Most popular',
        moduleNames: [
            'HRM',
            'Operations',
            'Inventory Management',
            'Finance Management',
            'Reports & Analytics',
        ],
    },
    {
        name: 'Premium',
        price: 1999,
        moduleNames: [
            'HRM',
            'Operations',
            'Inventory Management',
            'Finance Management',
            'Reports & Analytics',
        ],
    },
];

const upgradePlans = computed(() =>
    ALL_PLANS.filter((p) => props.availableUpgrades.includes(p.name)),
);

// ─── Selected plan ────────────────────────────────────────────────────────────

const selectedPlanName = ref<string>(upgradePlans.value[0]?.name ?? '');
const selectedPlan = computed(
    () =>
        ALL_PLANS.find((p) => p.name === selectedPlanName.value) ??
        upgradePlans.value[0],
);

// ─── Billing periods ──────────────────────────────────────────────────────────

interface Period {
    months: number;
    shortLabel: string;
    fullLabel: string;
    discountPct: number;
}

const PERIODS: Period[] = [
    { months: 1, shortLabel: '1 mo', fullLabel: '1 month', discountPct: 0 },
    {
        months: 12,
        shortLabel: '12 mo',
        fullLabel: '12 months',
        discountPct: 10,
    },
    {
        months: 24,
        shortLabel: '24 mo',
        fullLabel: '24 months',
        discountPct: 20,
    },
    {
        months: 48,
        shortLabel: '48 mo',
        fullLabel: '48 months',
        discountPct: 30,
    },
];

const selectedPeriod = ref<Period>(PERIODS[1]);

// ─── Pricing ──────────────────────────────────────────────────────────────────

const discountedMonthly = computed(() =>
    Math.round(
        (selectedPlan.value?.price ?? 0) *
            (1 - selectedPeriod.value.discountPct / 100),
    ),
);
const totalDue = computed(
    () => discountedMonthly.value * selectedPeriod.value.months,
);
const savedAmount = computed(
    () =>
        Math.round(
            (selectedPlan.value?.price ?? 0) * selectedPeriod.value.months,
        ) - totalDue.value,
);

function fmt(n: number) {
    return '₱' + Math.round(n).toLocaleString('en-PH');
}

// ─── Submit ───────────────────────────────────────────────────────────────────

const submitting = ref(false);

function proceed() {
    if (!selectedPlan.value) return;
    submitting.value = true;
    router.post(
        '/shop/upgrade/select',
        {
            plan_name: selectedPlan.value.name,
            billing_months: selectedPeriod.value.months,
            discount_pct: selectedPeriod.value.discountPct,
            monthly_price: discountedMonthly.value,
            total_amount: totalDue.value,
        },
        {
            onFinish: () => {
                submitting.value = false;
            },
        },
    );
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatDate(d: string | null) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <Head :title="isExpired ? 'Renew Plan' : 'Upgrade Plan'" />

    <ShopLayout :title="isExpired ? 'Renew Plan' : 'Upgrade Plan'">
        <div class="mx-auto max-w-3xl space-y-8 px-4 py-8">
            <!-- Header -->
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold">
                    <RotateCcw
                        v-if="isExpired"
                        class="h-6 w-6 text-amber-500"
                    />
                    <ArrowUp v-else class="h-6 w-6 text-indigo-600" />
                    {{ isExpired ? 'Renew Your Plan' : 'Upgrade Your Plan' }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    <template v-if="isExpired && currentPlan">
                        Your
                        <span class="font-semibold text-foreground">{{
                            currentPlan
                        }}</span>
                        plan expired on
                        <span class="font-medium">{{
                            formatDate(currentExpiresAt)
                        }}</span
                        >. Renew it or choose a higher plan below.
                    </template>
                    <template v-else-if="currentPlan">
                        You are currently on the
                        <span class="font-semibold text-foreground">{{
                            currentPlan
                        }}</span>
                        plan
                        <template v-if="currentExpiresAt">
                            · expires
                            <span class="font-medium">{{
                                formatDate(currentExpiresAt)
                            }}</span> </template
                        >. Choose a higher plan below.
                    </template>
                    <template v-else> Select a plan to get started. </template>
                </p>
            </div>

            <!-- Already on highest plan notice (non-expired) -->
            <div
                v-if="upgradePlans.length === 0"
                class="rounded-2xl border border-indigo-200 bg-indigo-50 p-6 text-center"
            >
                <p class="font-semibold text-indigo-700">
                    You are already on the Premium plan.
                </p>
                <p class="mt-1 text-sm text-indigo-600">
                    No higher plans are available.
                </p>
            </div>

            <!-- Plan cards -->
            <div
                v-else
                class="grid gap-4"
                :class="
                    upgradePlans.length === 1
                        ? 'max-w-sm grid-cols-1'
                        : 'grid-cols-1 sm:grid-cols-2'
                "
            >
                <button
                    v-for="plan in upgradePlans"
                    :key="plan.name"
                    type="button"
                    @click="selectedPlanName = plan.name"
                    class="rounded-2xl border-2 p-5 text-left transition-all focus:outline-none"
                    :class="
                        selectedPlanName === plan.name
                            ? 'border-indigo-500 bg-indigo-50 shadow-md'
                            : 'border-gray-200 bg-white hover:border-indigo-300'
                    "
                >
                    <div class="mb-3 flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="text-base font-bold text-gray-900">
                                    {{ plan.name }}
                                </p>
                                <!-- Renew badge for the current expired plan -->
                                <span
                                    v-if="
                                        isExpired && plan.name === currentPlan
                                    "
                                    class="flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700"
                                >
                                    <RotateCcw class="h-2.5 w-2.5" /> Renew
                                </span>
                                <span
                                    v-else-if="plan.badge"
                                    class="rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-semibold text-indigo-700"
                                >
                                    {{ plan.badge }}
                                </span>
                            </div>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                ₱{{ plan.price.toLocaleString('en-PH') }} / mo
                            </p>
                        </div>
                        <div
                            v-if="selectedPlanName === plan.name"
                            class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-indigo-500"
                        >
                            <Check class="h-3 w-3 text-white" />
                        </div>
                    </div>

                    <ul class="space-y-1.5">
                        <li
                            v-for="mod in plan.moduleNames"
                            :key="mod"
                            class="flex items-center gap-2 text-xs text-gray-700"
                        >
                            <component
                                :is="MODULE_ICONS[mod]"
                                class="h-3.5 w-3.5 shrink-0 text-indigo-500"
                            />
                            {{ modLabel(mod, plan.name) }}
                        </li>
                    </ul>
                </button>
            </div>

            <!-- Billing period + summary + CTA (hidden when no upgrades available) -->
            <template v-if="upgradePlans.length > 0">
                <!-- Billing period -->
                <div>
                    <p class="mb-3 text-sm font-semibold">Billing Period</p>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="period in PERIODS"
                            :key="period.months"
                            type="button"
                            @click="selectedPeriod = period"
                            class="rounded-lg border px-3 py-1.5 text-sm font-medium transition-all"
                            :class="
                                selectedPeriod.months === period.months
                                    ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                                    : 'border-gray-200 text-gray-600 hover:border-indigo-300'
                            "
                        >
                            {{ period.shortLabel }}
                            <span
                                v-if="period.discountPct > 0"
                                class="ml-1 text-[10px] font-semibold text-emerald-600"
                            >
                                -{{ period.discountPct }}%
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Price summary -->
                <div
                    class="space-y-3 rounded-2xl border bg-gray-50 p-5 text-sm"
                >
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Plan</span>
                        <span class="font-medium">{{
                            selectedPlan?.name
                        }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Monthly price</span>
                        <span class="font-medium">{{
                            fmt(discountedMonthly)
                        }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Duration</span>
                        <span class="font-medium">{{
                            selectedPeriod.fullLabel
                        }}</span>
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
                    <div
                        class="flex justify-between border-t pt-3 text-base font-bold"
                    >
                        <span>Total (VAT incl.)</span>
                        <span class="text-indigo-700">
                            {{ fmt(Math.round(totalDue * (1 + vatPct / 100))) }}
                        </span>
                    </div>
                    <p
                        class="flex items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <Clock class="h-3.5 w-3.5" />
                        Your new plan starts once the upgrade order is approved.
                    </p>
                </div>

                <!-- CTA -->
                <div class="flex justify-end">
                    <Button
                        :disabled="submitting || !selectedPlan"
                        class="gap-2 px-8"
                        @click="proceed"
                    >
                        <Loader2
                            v-if="submitting"
                            class="h-4 w-4 animate-spin"
                        />
                        <RotateCcw
                            v-else-if="
                                isExpired && selectedPlanName === currentPlan
                            "
                            class="h-4 w-4"
                        />
                        <ArrowUp v-else class="h-4 w-4" />
                        {{
                            submitting
                                ? 'Please wait…'
                                : isExpired && selectedPlanName === currentPlan
                                  ? 'Renew Plan'
                                  : 'Proceed to order'
                        }}
                    </Button>
                </div>
            </template>
        </div>
    </ShopLayout>
</template>
