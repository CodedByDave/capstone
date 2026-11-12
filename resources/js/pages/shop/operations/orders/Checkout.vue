<script setup lang="ts">
import { Button } from '@/components/ui/button';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Banknote,
    CheckCircle2,
    Clock,
    Coins,
    Info,
    Loader2,
    ReceiptText,
    Smartphone,
    Wallet,
} from 'lucide-vue-next';
import { computed, watch, type Component } from 'vue';

type PaymentMode = 'unpaid' | 'partial' | 'full';

const props = defineProps<{
    // Not "order": that prop name is the shared subscription the sidebar reads.
    shopOrder: {
        public_id: string;
        order_number: string;
        customer_name: string;
        pickup_type: string;
        status: string;
    };
    summary: {
        items: { label: string; amount: number }[];
        total: number;
        amount_paid: number;
        balance_due: number;
        is_estimate: boolean;
    };
    base: string;
}>();

const form = useForm({
    payment_mode: 'unpaid' as PaymentMode,
    payment_method: 'cash',
    amount: '' as string | number,
    amount_tendered: '' as string | number,
});

const balance = computed(() => props.summary.balance_due);
const fullyPaid = computed(() => balance.value <= 0);

const paymentOptions = computed<
    { value: PaymentMode; label: string; hint: string; icon: Component }[]
>(() => [
    {
        value: 'unpaid',
        label: 'Not paid yet',
        hint: 'Customer pays later',
        icon: Clock,
    },
    {
        value: 'partial',
        label: 'Partial payment',
        hint: 'Take a deposit now',
        icon: Coins,
    },
    {
        value: 'full',
        label: 'Paid in full',
        hint: money(balance.value),
        icon: CheckCircle2,
    },
]);

// How the money was received at the counter. Only cash can have change.
const methodOptions: { value: string; label: string; icon: Component }[] = [
    { value: 'cash', label: 'Cash', icon: Banknote },
    { value: 'gcash', label: 'GCash', icon: Wallet },
    { value: 'maya', label: 'Maya', icon: Smartphone },
];

const pickupLabel = computed(
    () =>
        ({
            walk_in: 'Walk-in',
            pickup: 'Rider pickup',
            delivery: 'Delivery',
        })[props.shopOrder.pickup_type] ?? props.shopOrder.pickup_type,
);

watch(
    () => form.payment_mode,
    () => {
        form.amount = '';
        form.amount_tendered = '';
        form.clearErrors();
    },
);
watch(
    () => form.payment_method,
    () => {
        form.amount_tendered = '';
    },
);

// The amount this payment covers: the whole balance, the part typed in, or nothing.
const payingNow = computed(() => {
    if (form.payment_mode === 'full') return balance.value;
    if (form.payment_mode === 'partial') return Number(form.amount) || 0;
    return 0;
});
const balanceAfter = computed(() =>
    Math.max(0, round(balance.value - payingNow.value)),
);

// Quick picks for a deposit: half the balance and round amounts below it.
const depositPicks = computed(() =>
    [round(balance.value / 2), 100, 200, 500]
        .filter((value) => value > 0 && value < balance.value)
        .filter((value, index, all) => all.indexOf(value) === index)
        .slice(0, 4),
);

// Cash handed over at the counter: the change is worked out here and again on the server.
const tendered = computed(() =>
    form.payment_method !== 'cash' || form.amount_tendered === ''
        ? null
        : Number(form.amount_tendered) || 0,
);
const changeDue = computed(() =>
    tendered.value === null || form.payment_mode === 'unpaid'
        ? null
        : round(tendered.value - payingNow.value),
);

// The bills a customer is likely to hand over: the exact amount, then the next round amounts.
const cashPicks = computed(() => {
    const due = payingNow.value;
    if (due <= 0) return [];

    return [
        due,
        Math.ceil(due / 100) * 100,
        Math.ceil(due / 500) * 500,
        Math.ceil(due / 1000) * 1000,
    ]
        .filter((value, index, all) => all.indexOf(value) === index)
        .slice(0, 4);
});

const showCash = computed(
    () =>
        form.payment_mode !== 'unpaid' &&
        form.payment_method === 'cash' &&
        payingNow.value > 0,
);

const submitLabel = computed(() => {
    if (form.processing) return 'Saving…';
    if (fullyPaid.value || form.payment_mode === 'unpaid') {
        return 'Finish without payment';
    }

    return `Confirm payment of ${money(payingNow.value)}`;
});

const canSubmit = computed(() => {
    if (form.processing) return false;
    if (form.payment_mode === 'partial') {
        return payingNow.value > 0 && payingNow.value < balance.value;
    }
    if (changeDue.value !== null && changeDue.value < 0) return false;

    return true;
});

function round(value: number): number {
    return Math.round(value * 100) / 100;
}

function money(value: number): string {
    return `₱${value.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

// Scrolling the page with a number box focused must not nudge its value.
const blurOnWheel = (event: WheelEvent) =>
    (event.target as HTMLInputElement).blur();

const tileClass = (selected: boolean) =>
    selected
        ? 'border-primary bg-primary/5 ring-1 ring-primary'
        : 'border-border bg-card hover:border-muted-foreground/40';

function submit() {
    if (fullyPaid.value) form.payment_mode = 'unpaid';
    form.post(`${props.base}/${props.shopOrder.public_id}/checkout`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="`Payment · ${shopOrder.order_number}`" />

    <ShopLayout title="Payment">
        <div class="mx-auto max-w-6xl space-y-6 px-4 pb-10 sm:px-6">
            <!-- Header -->
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <nav
                        aria-label="Breadcrumb"
                        class="mb-2 flex flex-wrap items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <span>Operations</span>
                        <span aria-hidden="true">/</span>
                        <span>Orders</span>
                        <span aria-hidden="true">/</span>
                        <span class="text-foreground">Payment</span>
                    </nav>
                    <h1 class="text-2xl font-bold">Payment</h1>
                </div>
                <Button
                    variant="back"
                    @click="router.visit(`${base}/${shopOrder.public_id}`)"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to order details
                </Button>
            </div>

            <!-- Steps -->
            <ol
                class="grid grid-cols-2 overflow-hidden rounded-[14px] border bg-card text-sm"
                aria-label="Order steps"
            >
                <li
                    class="flex items-center gap-3 border-r px-4 py-3 text-muted-foreground"
                >
                    <CheckCircle2
                        class="h-5 w-5 shrink-0 text-emerald-600"
                        aria-hidden="true"
                    />
                    <span>
                        <span class="block text-xs">Step 1</span>
                        <span class="font-medium text-foreground"
                            >Order details saved</span
                        >
                    </span>
                </li>
                <li
                    class="flex items-center gap-3 bg-primary/5 px-4 py-3"
                    aria-current="step"
                >
                    <span
                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-semibold text-primary-foreground"
                        >2</span
                    >
                    <span>
                        <span class="block text-xs text-muted-foreground"
                            >Step 2</span
                        >
                        <span class="font-semibold">Take payment</span>
                    </span>
                </li>
            </ol>

            <form
                class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]"
                @submit.prevent="submit"
            >
                <div class="space-y-8">
                    <!-- Amount due -->
                    <section
                        class="flex flex-wrap items-end justify-between gap-4 rounded-[14px] border bg-card p-6"
                        aria-labelledby="amount-due-label"
                    >
                        <div>
                            <p
                                id="amount-due-label"
                                class="text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                            >
                                Amount due
                            </p>
                            <p
                                class="mt-1 text-4xl font-bold tracking-tight tabular-nums"
                            >
                                {{ money(balance) }}
                            </p>
                            <p
                                v-if="summary.is_estimate"
                                class="mt-1 text-xs text-muted-foreground"
                            >
                                Estimated until the laundry is weighed.
                            </p>
                        </div>
                        <dl class="flex flex-wrap gap-x-6 gap-y-2 text-sm">
                            <div>
                                <dt class="text-xs text-muted-foreground">
                                    Order
                                </dt>
                                <dd class="font-medium">
                                    {{ shopOrder.order_number }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-muted-foreground">
                                    Customer
                                </dt>
                                <dd class="font-medium">
                                    {{ shopOrder.customer_name }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-muted-foreground">
                                    Type
                                </dt>
                                <dd class="font-medium">{{ pickupLabel }}</dd>
                            </div>
                        </dl>
                    </section>

                    <p
                        v-if="fullyPaid"
                        class="flex items-center gap-2 rounded-[14px] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-transparent dark:text-emerald-300"
                    >
                        <CheckCircle2 class="h-4 w-4" aria-hidden="true" />
                        This order is already paid in full.
                    </p>

                    <template v-else>
                        <!-- 1. How much -->
                        <section
                            class="space-y-3"
                            aria-labelledby="payment-mode-label"
                        >
                            <h2
                                id="payment-mode-label"
                                class="text-sm font-semibold"
                            >
                                <span class="text-muted-foreground">1.</span>
                                How much is the customer paying?
                            </h2>
                            <div
                                class="grid gap-3 sm:grid-cols-3"
                                role="radiogroup"
                                aria-labelledby="payment-mode-label"
                            >
                                <label
                                    v-for="option in paymentOptions"
                                    :key="option.value"
                                    class="relative flex min-h-11 cursor-pointer items-start gap-3 rounded-xl border p-4 transition has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-ring"
                                    :class="
                                        tileClass(
                                            form.payment_mode === option.value,
                                        )
                                    "
                                >
                                    <input
                                        v-model="form.payment_mode"
                                        type="radio"
                                        name="payment_mode"
                                        :value="option.value"
                                        class="sr-only"
                                    />
                                    <span
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg"
                                        :class="
                                            form.payment_mode === option.value
                                                ? 'bg-primary text-primary-foreground'
                                                : 'bg-muted text-muted-foreground'
                                        "
                                    >
                                        <component
                                            :is="option.icon"
                                            class="h-4 w-4"
                                            aria-hidden="true"
                                        />
                                    </span>
                                    <span class="min-w-0">
                                        <span
                                            class="block text-sm font-semibold"
                                            >{{ option.label }}</span
                                        >
                                        <span
                                            class="block text-xs text-muted-foreground tabular-nums"
                                            >{{ option.hint }}</span
                                        >
                                    </span>
                                    <CheckCircle2
                                        v-if="
                                            form.payment_mode === option.value
                                        "
                                        class="absolute top-3 right-3 h-4 w-4 text-primary"
                                        aria-hidden="true"
                                    />
                                </label>
                            </div>
                        </section>

                        <!-- Not paid yet -->
                        <p
                            v-if="form.payment_mode === 'unpaid'"
                            class="flex gap-3 rounded-xl border border-dashed p-4 text-sm text-muted-foreground"
                        >
                            <Info
                                class="mt-0.5 h-4 w-4 shrink-0"
                                aria-hidden="true"
                            />
                            <span>
                                The order is saved with a balance of
                                <strong class="text-foreground tabular-nums">{{
                                    money(balance)
                                }}</strong
                                >. Record the payment later from the order's
                                Edit page, for example when the customer
                                collects the laundry.
                            </span>
                        </p>

                        <template v-else>
                            <!-- 2. Payment type -->
                            <section
                                class="space-y-3"
                                aria-labelledby="payment-method-label"
                            >
                                <h2
                                    id="payment-method-label"
                                    class="text-sm font-semibold"
                                >
                                    <span class="text-muted-foreground"
                                        >2.</span
                                    >
                                    Payment type
                                </h2>
                                <div
                                    class="grid grid-cols-3 gap-3"
                                    role="radiogroup"
                                    aria-labelledby="payment-method-label"
                                >
                                    <label
                                        v-for="method in methodOptions"
                                        :key="method.value"
                                        class="flex min-h-11 cursor-pointer flex-col items-center justify-center gap-1.5 rounded-xl border px-3 py-4 text-sm font-medium transition has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-ring"
                                        :class="
                                            tileClass(
                                                form.payment_method ===
                                                    method.value,
                                            )
                                        "
                                    >
                                        <input
                                            v-model="form.payment_method"
                                            type="radio"
                                            name="payment_method"
                                            :value="method.value"
                                            class="sr-only"
                                        />
                                        <component
                                            :is="method.icon"
                                            class="h-5 w-5"
                                            :class="
                                                form.payment_method ===
                                                method.value
                                                    ? 'text-primary'
                                                    : 'text-muted-foreground'
                                            "
                                            aria-hidden="true"
                                        />
                                        {{ method.label }}
                                    </label>
                                </div>
                                <p
                                    v-if="form.errors.payment_method"
                                    class="text-xs text-red-600"
                                >
                                    {{ form.errors.payment_method }}
                                </p>
                            </section>

                            <!-- 3. Amount -->
                            <section
                                class="space-y-4"
                                aria-labelledby="amount-label"
                            >
                                <h2
                                    id="amount-label"
                                    class="text-sm font-semibold"
                                >
                                    <span class="text-muted-foreground"
                                        >3.</span
                                    >
                                    {{
                                        form.payment_mode === 'partial'
                                            ? 'Amount received'
                                            : 'Amount'
                                    }}
                                </h2>

                                <div
                                    v-if="form.payment_mode === 'partial'"
                                    class="space-y-2"
                                >
                                    <label for="amount-paid" class="sr-only"
                                        >Amount received</label
                                    >
                                    <div class="relative max-w-xs">
                                        <span
                                            class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted-foreground"
                                            aria-hidden="true"
                                            >₱</span
                                        >
                                        <input
                                            id="amount-paid"
                                            v-model="form.amount"
                                            type="number"
                                            inputmode="decimal"
                                            step="0.01"
                                            min="0.01"
                                            :max="
                                                Math.max(0.01, balance - 0.01)
                                            "
                                            required
                                            placeholder="0.00"
                                            class="h-12 w-full rounded-lg border bg-background pr-3 pl-8 text-lg font-semibold tabular-nums focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                                            :class="{
                                                'border-red-500':
                                                    form.errors.amount,
                                            }"
                                            @wheel="blurOnWheel"
                                        />
                                    </div>
                                    <div
                                        v-if="depositPicks.length"
                                        class="flex flex-wrap gap-2"
                                    >
                                        <button
                                            v-for="pick in depositPicks"
                                            :key="pick"
                                            type="button"
                                            class="min-h-11 rounded-lg border px-3 text-sm font-medium tabular-nums transition hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                            :class="
                                                Number(form.amount) === pick
                                                    ? 'border-primary text-primary'
                                                    : ''
                                            "
                                            @click="form.amount = pick"
                                        >
                                            {{ money(pick) }}
                                        </button>
                                    </div>
                                    <p class="text-xs text-muted-foreground">
                                        Must be less than the
                                        {{ money(balance) }} due.
                                    </p>
                                </div>

                                <p v-else class="text-sm text-muted-foreground">
                                    The full
                                    <strong
                                        class="text-foreground tabular-nums"
                                        >{{ money(balance) }}</strong
                                    >
                                    is received now.
                                </p>

                                <!-- Cash and change -->
                                <div
                                    v-if="showCash"
                                    class="space-y-2 rounded-xl border bg-muted/30 p-4"
                                >
                                    <label
                                        for="amount-tendered"
                                        class="text-sm font-medium"
                                        >Cash handed over
                                        <span
                                            class="font-normal text-muted-foreground"
                                            >(optional, to work out the
                                            change)</span
                                        ></label
                                    >
                                    <div class="flex flex-wrap gap-2">
                                        <button
                                            v-for="pick in cashPicks"
                                            :key="pick"
                                            type="button"
                                            class="min-h-11 rounded-lg border bg-background px-3 text-sm font-medium tabular-nums transition hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                            :class="
                                                Number(form.amount_tendered) ===
                                                pick
                                                    ? 'border-primary text-primary'
                                                    : ''
                                            "
                                            @click="form.amount_tendered = pick"
                                        >
                                            {{
                                                pick === payingNow
                                                    ? 'Exact'
                                                    : money(pick)
                                            }}
                                        </button>
                                    </div>
                                    <div class="relative max-w-xs">
                                        <span
                                            class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted-foreground"
                                            aria-hidden="true"
                                            >₱</span
                                        >
                                        <input
                                            id="amount-tendered"
                                            v-model="form.amount_tendered"
                                            type="number"
                                            inputmode="decimal"
                                            step="0.01"
                                            min="0"
                                            placeholder="Other amount"
                                            class="h-11 w-full rounded-lg border bg-background pr-3 pl-8 text-sm tabular-nums focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                                            :class="{
                                                'border-red-500':
                                                    form.errors
                                                        .amount_tendered ||
                                                    (changeDue !== null &&
                                                        changeDue < 0),
                                            }"
                                            @wheel="blurOnWheel"
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.amount_tendered"
                                        class="text-xs text-red-600"
                                    >
                                        {{ form.errors.amount_tendered }}
                                    </p>
                                    <p
                                        v-else-if="
                                            changeDue !== null && changeDue < 0
                                        "
                                        class="text-sm text-red-600"
                                        role="alert"
                                    >
                                        Short by {{ money(-changeDue) }}. The
                                        cash must cover {{ money(payingNow) }}.
                                    </p>
                                    <p
                                        v-else-if="changeDue !== null"
                                        class="flex items-baseline gap-2"
                                        aria-live="polite"
                                    >
                                        <span
                                            class="text-sm text-muted-foreground"
                                            >Change to give back</span
                                        >
                                        <span
                                            class="text-2xl font-bold text-green-700 tabular-nums dark:text-green-400"
                                            >{{ money(changeDue) }}</span
                                        >
                                    </p>
                                </div>

                                <p
                                    v-if="form.errors.amount"
                                    class="text-sm text-red-600"
                                    role="alert"
                                >
                                    {{ form.errors.amount }}
                                </p>
                            </section>
                        </template>

                        <p
                            v-if="form.errors.payment_mode"
                            class="text-sm text-red-600"
                            role="alert"
                        >
                            {{ form.errors.payment_mode }}
                        </p>
                    </template>
                </div>

                <!-- Order summary -->
                <aside
                    class="h-fit overflow-hidden rounded-[14px] border bg-card lg:sticky lg:top-6"
                    aria-label="Order summary"
                >
                    <div class="flex items-center gap-3 border-b px-5 py-4">
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-muted text-muted-foreground"
                        >
                            <ReceiptText class="h-4 w-4" aria-hidden="true" />
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold">Order summary</p>
                            <p class="truncate text-xs text-muted-foreground">
                                {{ shopOrder.order_number }} ·
                                {{ shopOrder.customer_name }}
                            </p>
                        </div>
                    </div>

                    <div class="px-5 py-4">
                        <dl class="space-y-2 text-sm">
                            <div
                                v-for="(item, index) in summary.items"
                                :key="index"
                                class="flex justify-between gap-3"
                            >
                                <dt class="text-muted-foreground">
                                    {{ item.label }}
                                </dt>
                                <dd class="font-medium tabular-nums">
                                    {{
                                        item.amount < 0
                                            ? `−${money(-item.amount)}`
                                            : money(item.amount)
                                    }}
                                </dd>
                            </div>
                        </dl>

                        <div
                            class="mt-4 flex items-baseline justify-between border-t border-dashed pt-4"
                        >
                            <span class="text-sm font-semibold">Total</span>
                            <span class="text-xl font-bold tabular-nums">{{
                                money(summary.total)
                            }}</span>
                        </div>

                        <dl
                            class="mt-4 space-y-2 rounded-lg bg-muted/40 p-3 text-sm"
                            aria-live="polite"
                        >
                            <div
                                v-if="summary.amount_paid > 0"
                                class="flex justify-between gap-3"
                            >
                                <dt class="text-muted-foreground">
                                    Already paid
                                </dt>
                                <dd class="font-medium tabular-nums">
                                    {{ money(summary.amount_paid) }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-muted-foreground">
                                    Paying now
                                </dt>
                                <dd class="font-medium tabular-nums">
                                    {{ money(payingNow) }}
                                </dd>
                            </div>
                            <div
                                v-if="changeDue !== null && changeDue >= 0"
                                class="flex justify-between gap-3"
                            >
                                <dt class="text-muted-foreground">Change</dt>
                                <dd
                                    class="font-medium text-green-700 tabular-nums dark:text-green-400"
                                >
                                    {{ money(changeDue) }}
                                </dd>
                            </div>
                            <div
                                class="flex justify-between gap-3 border-t pt-2"
                            >
                                <dt class="font-semibold">Balance after</dt>
                                <dd
                                    class="font-bold tabular-nums"
                                    :class="
                                        balanceAfter === 0
                                            ? 'text-green-700 dark:text-green-400'
                                            : ''
                                    "
                                >
                                    {{ money(balanceAfter) }}
                                </dd>
                            </div>
                        </dl>

                        <Button
                            type="submit"
                            :variant="
                                form.payment_mode === 'unpaid' || fullyPaid
                                    ? 'primary-outline'
                                    : 'default'
                            "
                            class="mt-5 min-h-12 w-full text-base"
                            :disabled="!canSubmit"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="h-4 w-4 animate-spin"
                            />
                            {{ submitLabel }}
                        </Button>
                    </div>
                </aside>
            </form>
        </div>
    </ShopLayout>
</template>
