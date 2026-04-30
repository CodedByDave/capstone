<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import {
    ChevronLeft, ShieldCheck, Clock,
    Users, Package, ClipboardList, Banknote, BarChart3, Check,
} from 'lucide-vue-next'

/* ─────────────────────────────────────────
   TYPES
───────────────────────────────────────── */
interface Plan {
    name: string
    price: number
    badge?: string
    moduleNames: string[]
}

interface Period {
    months: number
    shortLabel: string
    fullLabel: string
    discountPct: number
}

/* ─────────────────────────────────────────
   PROPS — Laravel passes the selected plan
───────────────────────────────────────── */
const props = defineProps<{ planName: string }>()

/* ─────────────────────────────────────────
   PLAN DATA
───────────────────────────────────────── */
const MODULE_ICONS: Record<string, any> = {
    'HRM': Users,
    'Operations': ClipboardList,
    'Inventory Management': Package,
    'Finance Management': Banknote,
    'Reports & Analytics': BarChart3,
}

const PLANS: Plan[] = [
    {
        name: 'Basic',
        price: 1999,
        moduleNames: ['HRM', 'Operations'],
    },
    {
        name: 'Standard',
        price: 3999,
        badge: 'Most popular',
        moduleNames: ['HRM', 'Operations', 'Inventory Management', 'Finance Management'],
    },
    {
        name: 'Premium',
        price: 5999,
        moduleNames: ['HRM', 'Operations', 'Inventory Management', 'Finance Management', 'Reports & Analytics'],
    },
]

const plan = PLANS.find(p => p.name === props.planName) ?? PLANS[1]

/* ─────────────────────────────────────────
   BILLING PERIODS
───────────────────────────────────────── */
const PERIODS: Period[] = [
    { months: 1,  shortLabel: '1 mo',  fullLabel: '1 month',   discountPct: 0  },
    { months: 12, shortLabel: '12 mo', fullLabel: '12 months', discountPct: 10 },
    { months: 24, shortLabel: '24 mo', fullLabel: '24 months', discountPct: 20 },
    { months: 48, shortLabel: '48 mo', fullLabel: '48 months', discountPct: 30 },
]

const selectedPeriod = ref<Period>(PERIODS[1]) // default 12 months

/* ─────────────────────────────────────────
   COMPUTED PRICES
───────────────────────────────────────── */
const discountedMonthly = computed(() =>
    Math.round(plan.price * (1 - selectedPeriod.value.discountPct / 100))
)

const totalDue = computed(() =>
    discountedMonthly.value * selectedPeriod.value.months
)

const savedAmount = computed(() =>
    Math.round(plan.price * selectedPeriod.value.months) - totalDue.value
)

function fmt(n: number) {
    return '₱' + Math.round(n).toLocaleString('en-PH')
}

/* ─────────────────────────────────────────
   NAVIGATION
───────────────────────────────────────── */
function proceedToPayment() {
    router.post('/checkout/select', {
        plan_name:      plan.name,
        billing_months: selectedPeriod.value.months,
        discount_pct:   selectedPeriod.value.discountPct,
        monthly_price:  discountedMonthly.value,
        total_amount:   totalDue.value,
    }, {
        onSuccess: () => {
            router.visit(
                (window as any).__page?.props?.auth?.user
                    ? '/checkout/confirm'
                    : '/login'
            )
        }
    })
}

function goBack() {
    router.visit('/#modules')
}
</script>

<template>
    <Head :title="`Checkout — ${plan.name} Plan`" />

    <div class="min-h-screen bg-background">
        <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6">

            <!-- Top bar -->
            <div class="mb-8 flex items-center justify-between border-b border-border pb-5">
                <span class="text-base font-semibold text-foreground">
                    Laundry<span class="text-primary">Hub</span>
                </span>

                <!-- Step indicator -->
                <div class="flex items-center gap-2">
                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-primary/10 text-xs font-medium text-primary ring-1 ring-primary/30">
                        1
                    </div>
                    <div class="h-px w-7 bg-border" />
                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-muted text-xs font-medium text-muted-foreground">
                        2
                    </div>
                </div>
            </div>

            <!-- Back -->
            <button
                type="button"
                class="mb-6 inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
                @click="goBack"
            >
                <ChevronLeft class="h-4 w-4" />
                Back to plans
            </button>

            <!-- Page heading -->
            <div class="mb-8">
                <h1 class="text-2xl font-semibold text-foreground">Complete your order</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    You selected the <span class="font-medium text-foreground">{{ plan.name }}</span> plan.
                    Choose how long you'd like to subscribe below.
                </p>
            </div>

            <!-- Main grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_300px]">

                <!-- ── Left column ── -->
                <div class="space-y-5">

                    <!-- Billing period picker -->
                    <div class="rounded-xl border border-border bg-card p-5">
                        <p class="mb-4 text-xs font-medium uppercase tracking-widest text-muted-foreground">
                            Billing period
                        </p>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <button
                                v-for="period in PERIODS"
                                :key="period.months"
                                type="button"
                                class="relative flex flex-col items-center rounded-xl border py-4 px-2 text-center transition-all focus-visible:outline-none"
                                :class="selectedPeriod.months === period.months
                                    ? 'border-primary bg-primary/5 ring-1 ring-primary'
                                    : 'border-border hover:border-primary/40'"
                                @click="selectedPeriod = period"
                            >
                                <!-- Save badge -->
                                <span
                                    v-if="period.discountPct > 0"
                                    class="absolute -top-2.5 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-emerald-500 px-2 py-0.5 text-[10px] font-semibold text-white"
                                >
                                    Save {{ period.discountPct }}%
                                </span>

                                <span
                                    class="text-sm font-semibold"
                                    :class="selectedPeriod.months === period.months
                                        ? 'text-primary'
                                        : 'text-foreground'"
                                >
                                    {{ period.shortLabel }}
                                </span>
                                <span class="mt-1 text-xs text-muted-foreground">
                                    {{ fmt(Math.round(plan.price * (1 - period.discountPct / 100))) }}/mo
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Plan detail row -->
                    <div class="rounded-xl border border-border bg-card p-5">
                        <p class="mb-4 text-xs font-medium uppercase tracking-widest text-muted-foreground">
                            Plan details
                        </p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-foreground">{{ plan.name }} plan</span>
                                <span
                                    v-if="plan.badge"
                                    class="rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary"
                                >
                                    {{ plan.badge }}
                                </span>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-foreground">{{ fmt(discountedMonthly) }}/mo</p>
                                <p
                                    v-if="selectedPeriod.discountPct > 0"
                                    class="text-xs text-muted-foreground line-through"
                                >
                                    {{ fmt(plan.price) }}/mo
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Modules list -->
                    <div class="rounded-xl border border-border bg-card p-5">
                        <p class="mb-4 text-xs font-medium uppercase tracking-widest text-muted-foreground">
                            Modules included
                        </p>
                        <ul class="space-y-3">
                            <li
                                v-for="mod in plan.moduleNames"
                                :key="mod"
                                class="flex items-center gap-3"
                            >
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-muted">
                                    <component :is="MODULE_ICONS[mod]" class="h-3.5 w-3.5 text-muted-foreground" />
                                </span>
                                <span class="text-sm text-foreground">{{ mod }}</span>
                                <span class="ml-auto flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-emerald-500/10">
                                    <Check class="h-2.5 w-2.5 text-emerald-600" />
                                </span>
                            </li>
                        </ul>
                    </div>

                </div>

                <!-- ── Right: Order summary ── -->
                <div class="rounded-xl border border-border bg-card p-5 lg:sticky lg:top-6 h-fit">
                    <p class="mb-4 text-xs font-medium uppercase tracking-widest text-muted-foreground">
                        Order summary
                    </p>

                    <!-- Plan + period -->
                    <div class="mb-4 flex items-start justify-between">
                        <span class="text-sm font-semibold text-foreground">{{ plan.name }} plan</span>
                        <span class="text-xs text-muted-foreground">{{ selectedPeriod.fullLabel }}</span>
                    </div>

                    <!-- Breakdown rows -->
                    <div class="space-y-2.5">
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Monthly price</span>
                            <span class="font-medium text-foreground">{{ fmt(discountedMonthly) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Duration</span>
                            <span class="font-medium text-foreground">
                                × {{ selectedPeriod.months }} month{{ selectedPeriod.months > 1 ? 's' : '' }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Discount</span>
                            <span
                                class="font-medium"
                                :class="savedAmount > 0 ? 'text-emerald-600' : 'text-muted-foreground'"
                            >
                                {{ savedAmount > 0 ? '−' + fmt(savedAmount) : '—' }}
                            </span>
                        </div>
                    </div>

                    <hr class="my-4 border-border" />

                    <!-- Total -->
                    <div class="flex items-baseline justify-between">
                        <span class="text-sm font-medium text-foreground">Total due today</span>
                        <span class="text-xl font-semibold text-foreground">{{ fmt(totalDue) }}</span>
                    </div>
                    <p class="mt-1 text-right text-xs text-muted-foreground">
                        {{ selectedPeriod.months === 1 ? 'Billed monthly' : `Billed once for ${selectedPeriod.months} months` }}
                    </p>

                    <!-- CTA -->
                    <Button
                        type="button"
                        class="mt-5 w-full"
                        @click="proceedToPayment"
                    >
                        Proceed to order
                    </Button>

                    <!-- Trust badges -->
                    <div class="mt-4 flex items-center justify-center gap-5">
                        <span class="flex items-center gap-1.5 text-xs text-muted-foreground">
                            <ShieldCheck class="h-3.5 w-3.5" />
                            Secure checkout
                        </span>
                        <span class="flex items-center gap-1.5 text-xs text-muted-foreground">
                            <Clock class="h-3.5 w-3.5" />
                            Instant activation
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
