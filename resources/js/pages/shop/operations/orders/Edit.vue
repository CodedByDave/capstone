<script setup lang="ts">
import PhilippinePhoneInput from '@/components/PhilippinePhoneInput.vue';
import { Button } from '@/components/ui/button';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { toLocalInputValue } from '@/lib/datetime';
import { type AppPageProps } from '@/types';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Check, Loader2, Truck } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

interface Service {
    id: number;
    service_name: string;
    pricing_model: 'per_kg' | 'per_bundle' | 'per_piece';
    price_per_piece: string | null;
    price_per_kg: string | null;
    bundle_weight_kg: string | null;
    bundle_price: string | null;
}

interface InventoryItem {
    id: number;
    name: string;
    unit: string;
    quantity: number;
    selling_price: string | null;
}

interface Promotion {
    id: number;
    name: string;
    type: 'percentage' | 'fixed';
    value: string;
    min_order_amount: string | null;
}

interface Order {
    id: number;
    payment_public_id: string;
    order_number: string;
    customer_name: string;
    customer_phone: string | null;
    alternate_contact_name: string | null;
    alternate_contact_phone: string | null;
    customer_address: string | null;
    deliver_to_customer: boolean;
    service_id: number;
    pickup_type: string;
    pricing_model: 'per_kg' | 'per_bundle' | 'per_piece';
    price_per_piece: string | null;
    piece_count: number | null;
    estimated_weight_kg: string | null;
    actual_weight_kg: string | null;
    price_per_kg: string | null;
    bundle_weight_kg: string | null;
    bundle_price: string | null;
    bundle_quantity: number | null;
    promotion_id: number | null;
    additional_charges: string;
    discount_amount: string;
    delivery_fee?: string | null;
    total_amount: string;
    payment_method: string;
    payment_status: string;
    amount_paid: string;
    status: string;
    estimated_completion_at: string | null;
    supplies: { id: number; pivot: { quantity_used: number; unit: string } }[];
}

const props = defineProps<{
    shopOrder: Order;
    shopId: number;
    pickupTypes: string[];
    paymentMethods: string[];
    statuses: string[];
    /** Set while a pickup order's laundry has not yet reached the shop. */
    weighingBlockedReason?: string | null;
    services?: Service[];
    inventoryItems?: InventoryItem[];
    promotions?: Promotion[];
}>();

const page = usePage<AppPageProps>();
const isOwner = computed(() => page.props.auth.user.role === 'owner');
const base = computed(() =>
    isOwner.value ? '/shop/operations/orders' : '/staff/operations/orders',
);
onMounted(() => {
    const t = page.props.toast as any;
    if (!t) return;
    if (t.type === 'success') toast.success(t.message);
    if (t.type === 'error') toast.error(t.message);
});

const form = useForm({
    customer_name: props.shopOrder.customer_name,
    customer_phone: props.shopOrder.customer_phone ?? '',
    alternate_contact_name: props.shopOrder.alternate_contact_name ?? '',
    alternate_contact_phone: props.shopOrder.alternate_contact_phone ?? '',
    customer_address: props.shopOrder.customer_address ?? '',
    deliver_to_customer: props.shopOrder.deliver_to_customer ?? false,
    service_id: props.shopOrder.service_id,
    pickup_type: props.shopOrder.pickup_type,
    pricing_model: props.shopOrder.pricing_model,
    estimated_weight_kg: props.shopOrder.estimated_weight_kg ?? '',
    actual_weight_kg: props.shopOrder.actual_weight_kg ?? '',
    price_per_kg: props.shopOrder.price_per_kg ?? 0,
    bundle_weight_kg: props.shopOrder.bundle_weight_kg ?? '',
    bundle_price: props.shopOrder.bundle_price ?? 0,
    bundle_quantity: props.shopOrder.bundle_quantity ?? 1,
    price_per_piece: props.shopOrder.price_per_piece ?? 0,
    piece_count: (props.shopOrder.piece_count ?? '') as string | number,
    promotion_id: props.shopOrder.promotion_id ?? ('' as string | number),
    additional_charges: props.shopOrder.additional_charges,
    discount_amount: props.shopOrder.discount_amount,
    total_amount: props.shopOrder.total_amount,
    payment_method: props.shopOrder.payment_method,
    // Not paid or Fully paid. A partly paid order starts with neither chosen.
    payment_state: (props.shopOrder.payment_status === 'paid'
        ? 'paid'
        : props.shopOrder.payment_status === 'unpaid'
          ? 'unpaid'
          : '') as 'paid' | 'unpaid' | '',
    status: props.shopOrder.status,
    estimated_completion_at: props.shopOrder.estimated_completion_at
        ? toLocalInputValue(new Date(props.shopOrder.estimated_completion_at))
        : '',
    supplies: props.shopOrder.supplies.map((s) => ({
        inventory_id: s.id,
        quantity_used: s.pivot.quantity_used,
        unit: s.pivot.unit,
    })),
});

const isPerKg = computed(() => form.pricing_model === 'per_kg');
const isPerPiece = computed(() => form.pricing_model === 'per_piece');

// When actual weight is entered for per_bundle orders, auto-update bundle quantity
watch(
    () => form.actual_weight_kg,
    (val) => {
        if (form.pricing_model === 'per_bundle' && val) {
            const bundleSize = parseFloat(String(form.bundle_weight_kg)) || 1;
            const actualKg = parseFloat(String(val)) || 0;
            if (actualKg > 0) {
                form.bundle_quantity = Math.ceil(actualKg / bundleSize);
            }
        }
    },
);

const selectedService = computed(() =>
    props.services?.find((s) => s.id === Number(form.service_id)),
);

const onServiceChange = () => {
    const svc = selectedService.value;
    if (!svc) return;
    form.pricing_model = svc.pricing_model;
    form.price_per_piece =
        svc.pricing_model === 'per_piece'
            ? parseFloat(svc.price_per_piece ?? '0')
            : 0;
    if (svc.pricing_model === 'per_piece') {
        // Keep a count that is already there; otherwise start from one piece.
        form.piece_count = Number(form.piece_count) > 0 ? form.piece_count : 1;
        form.price_per_kg = 0;
        form.bundle_price = 0;
        form.bundle_weight_kg = '';
    } else if (svc.pricing_model === 'per_kg') {
        form.piece_count = '';
        form.price_per_kg = parseFloat(svc.price_per_kg ?? '0');
        form.bundle_price = 0;
        form.bundle_weight_kg = '';
    } else {
        form.piece_count = '';
        form.bundle_price = parseFloat(svc.bundle_price ?? '0');
        form.bundle_weight_kg = svc.bundle_weight_kg ?? '';
        form.price_per_kg = 0;
    }
};

const selectedPromotion = computed(() =>
    props.promotions?.find((p) => p.id === Number(form.promotion_id)),
);

const baseTotal = computed(() => {
    if (isPerPiece.value)
        return (
            (parseInt(String(form.piece_count)) || 0) *
            (parseFloat(String(form.price_per_piece)) || 0)
        );
    if (isPerKg.value)
        return (
            (parseFloat(String(form.actual_weight_kg)) || 0) *
            (parseFloat(String(form.price_per_kg)) || 0)
        );
    return (
        (parseFloat(String(form.bundle_quantity)) || 1) *
        (parseFloat(String(form.bundle_price)) || 0)
    );
});

const promoDiscount = computed(() => {
    const promo = selectedPromotion.value;
    if (!promo) return 0;
    const total = baseTotal.value;
    if (promo.min_order_amount && total < parseFloat(promo.min_order_amount))
        return 0;
    if (promo.type === 'percentage')
        return Math.round(total * (parseFloat(promo.value) / 100) * 100) / 100;
    return Math.min(parseFloat(promo.value), total);
});

const onPromotionChange = () => {
    form.discount_amount = String(promoDiscount.value);
};

// Set by the customer's delivery request; the server keeps it in the total.
const deliveryFee = computed(
    () => parseFloat(String(props.shopOrder.delivery_fee ?? 0)) || 0,
);

const computedTotal = computed(() => {
    const charges = parseFloat(String(form.additional_charges)) || 0;
    const discount = parseFloat(String(form.discount_amount)) || 0;
    return (
        Math.max(0, baseTotal.value + charges - discount) + deliveryFee.value
    );
});

const formatServiceLabel = (svc: Service) => {
    if (svc.pricing_model === 'per_kg')
        return `${svc.service_name} — ₱${svc.price_per_kg}/kg`;
    if (svc.pricing_model === 'per_piece')
        return `${svc.service_name} — ₱${svc.price_per_piece}/piece`;
    return `${svc.service_name} — ₱${svc.bundle_price} / ${svc.bundle_weight_kg}kg bundle`;
};

const formatCurrency = (v: string | number | null) =>
    v !== null
        ? `₱${Number(v).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
        : '—';

// The order's own supplies were already taken out of stock, so while editing it can use that
// quantity again on top of what is left on the shelf.
const heldByOrder = (inventoryId: number): number =>
    props.shopOrder.supplies
        .filter((s) => s.id === inventoryId)
        .reduce((sum, s) => sum + Number(s.pivot.quantity_used), 0);

const availableFor = (item: InventoryItem): number =>
    Number(item.quantity) + heldByOrder(item.id);

const selectedItem = (inventoryId: number): InventoryItem | undefined =>
    props.inventoryItems?.find((i) => i.id === inventoryId);

const addSupply = () =>
    form.supplies.push({ inventory_id: 0, quantity_used: 0, unit: '' });

const removeSupply = (i: number) => {
    form.supplies.splice(i, 1);
    recalcSupplyCharges();
};

const onSupplyItemChange = (supply: {
    inventory_id: number;
    quantity_used: number;
    unit: string;
}) => {
    const item = props.inventoryItems?.find(
        (i) => i.id === supply.inventory_id,
    );
    if (item) supply.unit = item.unit;
    recalcSupplyCharges();
};

const supplyCost = computed(() =>
    form.supplies.reduce((sum, supply) => {
        const item = props.inventoryItems?.find(
            (i) => i.id === supply.inventory_id,
        );
        const price = item?.selling_price ? parseFloat(item.selling_price) : 0;
        const qty = parseFloat(String(supply.quantity_used)) || 0;
        return sum + price * qty;
    }, 0),
);

function recalcSupplyCharges() {
    form.additional_charges = String(parseFloat(supplyCost.value.toFixed(2)));
}

watch(() => form.supplies.map((s) => s.quantity_used), recalcSupplyCharges, {
    deep: true,
});

const paymentOptions = [
    { value: 'unpaid', label: 'Not paid' },
    { value: 'paid', label: 'Fully paid' },
] as const;

const paymentNote = computed(() => {
    const status = props.shopOrder.payment_status;

    if (form.payment_state === 'paid' && status !== 'paid') {
        return `Saving records ${formatCurrency(balanceDue.value)} as received (${form.payment_method.toUpperCase()}).`;
    }
    if (form.payment_state === 'unpaid' && paidSoFar.value > 0) {
        return `Saving voids the ${formatCurrency(paidSoFar.value)} recorded as paid. Online payments cannot be undone here.`;
    }
    if (status === 'paid')
        return `Fully paid · ${formatCurrency(paidSoFar.value)}`;
    if (status === 'partial') {
        return `${formatCurrency(paidSoFar.value)} paid so far. Choose Fully paid to record the remaining ${formatCurrency(balanceDue.value)}.`;
    }

    return 'Nothing has been paid yet.';
});

const saveStatus = computed(() => {
    if (form.isDirty) return 'You have unsaved changes';

    return form.recentlySuccessful ? 'Changes saved' : 'No changes yet';
});

// Scrolling the page with a number box focused must not nudge its value (20 becoming 19.99).
const blurOnWheel = (event: WheelEvent) =>
    (event.target as HTMLInputElement).blur();

const submit = () => {
    form.total_amount = String(computedTotal.value);
    form.put(`${base.value}/${props.shopOrder.payment_public_id}`);
};

// ─── Hand-off: how the customer gets the finished laundry ──────────────────

// Same choice as the New Order page. An online order a rider collects keeps its own option, so
// editing it never turns it into a different kind of order by accident.
const handoffOptions = computed(() => [
    { value: 'walk_in', label: 'Walk-in' },
    ...(page.props.shopCapabilities?.offers_delivery || form.deliver_to_customer
        ? [{ value: 'delivery', label: 'Delivery' }]
        : []),
    ...(props.shopOrder.pickup_type === 'pickup'
        ? [{ value: 'pickup', label: 'Rider pickup' }]
        : []),
]);
const handoff = computed({
    get: () =>
        form.pickup_type === 'pickup'
            ? 'pickup'
            : form.deliver_to_customer
              ? 'delivery'
              : 'walk_in',
    set: (value: string) => {
        if (value === 'pickup') {
            form.pickup_type = 'pickup';
            return;
        }
        form.pickup_type = 'walk_in';
        form.deliver_to_customer = value === 'delivery';
    },
});
// A rider is involved either way, so the customer must be reachable.
const riderInvolved = computed(
    () => form.pickup_type === 'pickup' || form.deliver_to_customer,
);

// ─── Order status progression ──────────────────────────────────────────────

const statusSteps = [
    { value: 'pending', label: 'Pending' },
    { value: 'in_progress', label: 'In progress' },
    { value: 'completed', label: 'Completed' },
    { value: 'picked_up', label: 'Picked up' },
];
const isCancelled = computed(() => props.shopOrder.status === 'cancelled');
const isPickedUp = computed(() => props.shopOrder.status === 'picked_up');
// The status bar only shows progress; status changes happen from the orders list and details page.
const currentStep = computed(() =>
    statusSteps.findIndex((step) => step.value === form.status),
);
const stepCircle = (index: number) =>
    index < currentStep.value
        ? 'border-blue-500 bg-blue-500 text-white'
        : index === currentStep.value
          ? 'border-blue-500'
          : 'border-input';

const statusLabel = computed(
    () =>
        ({
            pending: 'Pending',
            in_progress: 'In progress',
            completed: 'Completed',
            picked_up: 'Picked up',
            cancelled: 'Cancelled',
        })[props.shopOrder.status] ?? props.shopOrder.status,
);
const statusPill = computed(
    () =>
        ({
            pending: 'bg-amber-50 text-amber-700 ring-amber-200',
            in_progress: 'bg-blue-50 text-blue-700 ring-blue-200',
            completed: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            picked_up: 'bg-slate-100 text-slate-700 ring-slate-200',
            cancelled: 'bg-red-50 text-red-700 ring-red-200',
        })[props.shopOrder.status] ?? 'bg-gray-50 text-gray-700 ring-gray-200',
);

const canCancel = computed(() => !isCancelled.value && !isPickedUp.value);
const confirmingCancel = ref(false);
const cancelling = ref(false);

function cancelOrder() {
    cancelling.value = true;
    router.patch(
        `${base.value}/${props.shopOrder.payment_public_id}/status`,
        { status: 'cancelled' },
        {
            preserveScroll: true,
            onSuccess: () => {
                // The page now shows the cancelled order, so the form starts from that status too.
                form.status = 'cancelled';
                form.defaults('status', 'cancelled');
                confirmingCancel.value = false;
            },
            onError: (errors) =>
                toast.error(
                    errors.status ?? 'The order could not be cancelled.',
                ),
            onFinish: () => {
                cancelling.value = false;
            },
        },
    );
}

// ─── Summary ───────────────────────────────────────────────────────────────

const paidSoFar = computed(() => Number(props.shopOrder.amount_paid) || 0);
const balanceDue = computed(() =>
    Math.max(
        0,
        Math.round((computedTotal.value - paidSoFar.value) * 100) / 100,
    ),
);
</script>

<template>
    <Head :title="`Edit Order ${shopOrder.order_number}`" />

    <ShopLayout :title="`Edit Order ${shopOrder.order_number}`">
        <div class="mx-auto max-w-6xl space-y-6 px-6 pb-10">
            <!-- Header -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <nav
                        aria-label="Breadcrumb"
                        class="mb-2 flex flex-wrap items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <span>Operations</span>
                        <span aria-hidden="true">/</span>
                        <Link :href="base" class="hover:text-foreground"
                            >Orders</Link
                        >
                        <span aria-hidden="true">/</span>
                        <Link
                            :href="`${base}/${shopOrder.payment_public_id}`"
                            class="hover:text-foreground"
                            >{{ shopOrder.order_number }}</Link
                        >
                        <span aria-hidden="true">/</span>
                        <span class="font-medium text-foreground">Edit</span>
                    </nav>
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="text-2xl font-bold tracking-tight">
                            Edit order
                        </h2>
                        <span
                            class="rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1"
                            :class="statusPill"
                            >{{ statusLabel }}</span
                        >
                    </div>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ shopOrder.order_number }} · Customer:
                        {{ shopOrder.customer_name }}
                    </p>
                </div>
                <Button
                    variant="outline"
                    @click="
                        router.visit(`${base}/${shopOrder.payment_public_id}`)
                    "
                >
                    <ArrowLeft class="mr-2 h-4 w-4" /> Back
                </Button>
            </div>

            <form
                class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_300px]"
                @submit.prevent="submit"
            >
                <!-- Form -->
                <div class="space-y-8">
                    <!-- Order status: progression -->
                    <div>
                        <p
                            class="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                        >
                            Order status
                        </p>

                        <div
                            v-if="isCancelled"
                            class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700"
                            role="status"
                        >
                            This order is cancelled.
                        </div>

                        <ol
                            class="flex items-center"
                            aria-label="Order status"
                        >
                            <template
                                v-for="(step, index) in statusSteps"
                                :key="step.value"
                            >
                                <li class="flex items-center">
                                    <div
                                        class="flex items-center gap-2 rounded-full py-1 pr-2 text-sm"
                                        :aria-current="
                                            form.status === step.value
                                                ? 'step'
                                                : undefined
                                        "
                                    >
                                        <span
                                            class="flex h-5 w-5 items-center justify-center rounded-full border-2 transition"
                                            :class="stepCircle(index)"
                                        >
                                            <Check
                                                v-if="index < currentStep"
                                                class="h-3 w-3"
                                            />
                                            <span
                                                v-else-if="
                                                    index === currentStep
                                                "
                                                class="h-2 w-2 rounded-full bg-blue-500"
                                            />
                                        </span>
                                        <span
                                            :class="
                                                index <= currentStep
                                                    ? 'font-semibold text-foreground'
                                                    : 'text-muted-foreground'
                                            "
                                            >{{ step.label }}</span
                                        >
                                    </div>
                                </li>
                                <li
                                    v-if="index < statusSteps.length - 1"
                                    aria-hidden="true"
                                    class="mx-3 h-px flex-1 transition"
                                    :class="
                                        index < currentStep
                                            ? 'bg-blue-500'
                                            : 'bg-border'
                                    "
                                />
                            </template>
                        </ol>

                        <div v-if="canCancel" class="mt-5">
                            <button
                                v-if="!confirmingCancel"
                                type="button"
                                class="text-sm font-medium text-red-600 hover:underline focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                @click="confirmingCancel = true"
                            >
                                Cancel this order
                            </button>
                            <div
                                v-else
                                class="flex flex-wrap items-center gap-3 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700"
                                role="alert"
                            >
                                <span class="flex-1"
                                    >Cancel this order? Any stock it used is put
                                    back.</span
                                >
                                <Button
                                    type="button"
                                    variant="destructive"
                                    size="sm"
                                    :disabled="cancelling"
                                    @click="cancelOrder"
                                >
                                    <Loader2
                                        v-if="cancelling"
                                        class="mr-2 h-4 w-4 animate-spin"
                                    />
                                    Yes, cancel order
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    :disabled="cancelling"
                                    @click="confirmingCancel = false"
                                >
                                    Keep order
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Customer -->
                    <div class="border-t pt-8">
                        <p
                            class="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                        >
                            Customer Information
                        </p>
                        <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                            <div class="col-span-12 space-y-1 sm:col-span-4">
                                <label class="text-sm font-medium"
                                    >Customer Name
                                    <span class="text-red-500">*</span></label
                                >
                                <input
                                    v-model="form.customer_name"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    :class="{
                                        'border-red-500':
                                            form.errors.customer_name,
                                    }"
                                />
                                <p
                                    v-if="form.errors.customer_name"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.customer_name }}
                                </p>
                            </div>
                            <div class="col-span-12 space-y-1 sm:col-span-4">
                                <label class="text-sm font-medium"
                                    >Phone
                                    <span
                                        v-if="riderInvolved"
                                        class="text-red-500"
                                        >*</span
                                    ></label
                                >
                                <PhilippinePhoneInput
                                    v-model="form.customer_phone"
                                    :required="riderInvolved"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                />
                                <p
                                    v-if="form.errors.customer_phone"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.customer_phone }}
                                </p>
                            </div>
                            <div class="col-span-12 space-y-1 sm:col-span-4">
                                <p
                                    id="pickup-type-label"
                                    class="text-sm font-medium"
                                >
                                    Pickup Type
                                    <span class="text-red-500">*</span>
                                </p>
                                <div
                                    class="flex gap-2"
                                    role="radiogroup"
                                    aria-labelledby="pickup-type-label"
                                >
                                    <label
                                        v-for="option in handoffOptions"
                                        :key="option.value"
                                        class="flex flex-1 cursor-pointer items-center gap-2 rounded-lg border px-3 py-2.5 text-sm transition has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-ring"
                                        :class="
                                            handoff === option.value
                                                ? 'border-blue-500 bg-blue-50 text-blue-700'
                                                : 'border-gray-300 hover:border-gray-400'
                                        "
                                    >
                                        <input
                                            v-model="handoff"
                                            type="radio"
                                            name="handoff"
                                            :value="option.value"
                                            class="h-4 w-4 accent-blue-600"
                                        />
                                        {{ option.label }}
                                    </label>
                                </div>
                            </div>
                            <div
                                v-if="props.shopOrder.pickup_type !== 'pickup'"
                                class="col-span-12 space-y-1 sm:col-span-4"
                            >
                                <p
                                    id="dropoff-type-label"
                                    class="text-sm font-medium"
                                >
                                    Drop-off Type
                                </p>
                                <div
                                    class="flex gap-2"
                                    role="radiogroup"
                                    aria-labelledby="dropoff-type-label"
                                >
                                    <label
                                        class="flex flex-1 cursor-pointer items-center gap-2 rounded-lg border border-blue-500 bg-blue-50 px-3 py-2.5 text-sm text-blue-700"
                                    >
                                        <input
                                            type="radio"
                                            name="dropoff"
                                            value="walk_in"
                                            checked
                                            class="h-4 w-4 accent-blue-600"
                                        />
                                        Walk-in
                                    </label>
                                </div>
                            </div>
                            <div class="col-span-12 space-y-1">
                                <label class="text-sm font-medium"
                                    >Address
                                    <span
                                        v-if="riderInvolved"
                                        class="text-red-500"
                                        >*</span
                                    ></label
                                >
                                <textarea
                                    v-model="form.customer_address"
                                    :required="riderInvolved"
                                    rows="2"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                />
                                <p
                                    v-if="form.errors.customer_address"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.customer_address }}
                                </p>
                            </div>
                            <template v-if="riderInvolved">
                                <div
                                    class="col-span-12 space-y-1 sm:col-span-6"
                                >
                                    <label
                                        class="text-sm font-medium"
                                        for="alternate-contact-name"
                                        >Alternate contact name
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        id="alternate-contact-name"
                                        required
                                        v-model="form.alternate_contact_name"
                                        type="text"
                                        maxlength="150"
                                        placeholder="e.g. a family member or neighbour"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                        :class="{
                                            'border-red-500':
                                                form.errors
                                                    .alternate_contact_name,
                                        }"
                                    />
                                    <p
                                        v-if="
                                            form.errors.alternate_contact_name
                                        "
                                        class="text-xs text-red-500"
                                    >
                                        {{ form.errors.alternate_contact_name }}
                                    </p>
                                </div>
                                <div
                                    class="col-span-12 space-y-1 sm:col-span-6"
                                >
                                    <label class="text-sm font-medium"
                                        >Alternate contact number
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <PhilippinePhoneInput
                                        v-model="form.alternate_contact_phone"
                                        required
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    />
                                    <p
                                        v-if="
                                            form.errors.alternate_contact_phone
                                        "
                                        class="text-xs text-red-500"
                                    >
                                        {{
                                            form.errors.alternate_contact_phone
                                        }}
                                    </p>
                                </div>
                                <p
                                    class="col-span-12 -mt-2 text-xs text-muted-foreground"
                                >
                                    Someone the rider can call, or hand the
                                    laundry over to, if the customer is not
                                    there or cannot be reached.
                                </p>
                            </template>
                        </div>
                    </div>

                    <!-- Service and weight -->
                    <div class="border-t pt-8">
                        <div class="grid gap-x-8 gap-y-8 lg:grid-cols-2">
                            <div>
                                <p
                                    class="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                                >
                                    Services
                                </p>
                                <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                                    <div class="col-span-12 space-y-1">
                                        <label
                                            class="text-sm font-medium"
                                            for="service-select"
                                            >Service
                                            <span class="text-red-500"
                                                >*</span
                                            ></label
                                        >
                                        <select
                                            id="service-select"
                                            v-model="form.service_id"
                                            required
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                            :class="{
                                                'border-red-500':
                                                    form.errors.service_id,
                                            }"
                                            @change="onServiceChange"
                                        >
                                            <option
                                                v-for="svc in services"
                                                :key="svc.id"
                                                :value="svc.id"
                                            >
                                                {{ formatServiceLabel(svc) }}
                                            </option>
                                        </select>
                                        <p
                                            v-if="form.errors.service_id"
                                            class="text-xs text-red-500"
                                        >
                                            {{ form.errors.service_id }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="border-t pt-8 lg:border-t-0 lg:border-l lg:pt-0 lg:pl-8"
                            >
                                <p
                                    class="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                                >
                                    {{ isPerPiece ? 'Pieces' : 'Weight' }}
                                </p>
                                <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                                    <template v-if="isPerPiece">
                                        <div class="col-span-12 space-y-1">
                                            <label class="text-sm font-medium"
                                                >Price per Piece
                                                <span
                                                    class="text-xs text-muted-foreground"
                                                    >(from service)</span
                                                ></label
                                            >
                                            <div
                                                class="flex h-[42px] items-center rounded-lg border border-gray-200 bg-muted/40 px-3 text-sm text-muted-foreground"
                                            >
                                                {{
                                                    form.price_per_piece
                                                        ? formatCurrency(
                                                              form.price_per_piece,
                                                          )
                                                        : '—'
                                                }}
                                            </div>
                                        </div>
                                        <div class="col-span-12 space-y-1">
                                            <label
                                                class="text-sm font-medium"
                                                for="piece-count"
                                                >Number of Pieces
                                                <span class="text-red-500"
                                                    >*</span
                                                ></label
                                            >
                                            <input
                                                id="piece-count"
                                                v-model="form.piece_count"
                                                type="number"
                                                @wheel="blurOnWheel"
                                                min="1"
                                                max="500"
                                                step="1"
                                                required
                                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                                :class="{
                                                    'border-red-500':
                                                        form.errors.piece_count,
                                                }"
                                            />
                                            <p
                                                v-if="form.errors.piece_count"
                                                class="text-xs text-red-500"
                                            >
                                                {{ form.errors.piece_count }}
                                            </p>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div
                                            v-if="form.estimated_weight_kg"
                                            class="col-span-12 space-y-1"
                                        >
                                            <label class="text-sm font-medium"
                                                >Customer's estimate</label
                                            >
                                            <div
                                                class="flex h-[42px] items-center rounded-lg border border-gray-200 bg-muted/40 px-3 text-sm text-muted-foreground"
                                            >
                                                {{ form.estimated_weight_kg }}
                                                kg
                                            </div>
                                        </div>
                                        <div class="col-span-12 space-y-1">
                                            <label
                                                class="text-sm font-medium"
                                                for="actual-weight"
                                                >Actual Weight (kg)</label
                                            >
                                            <input
                                                id="actual-weight"
                                                v-model="form.actual_weight_kg"
                                                :disabled="
                                                    !!weighingBlockedReason
                                                "
                                                type="number"
                                                @wheel="blurOnWheel"
                                                step="0.01"
                                                min="0.1"
                                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                                :class="{
                                                    'border-red-500':
                                                        form.errors
                                                            .actual_weight_kg,
                                                }"
                                            />
                                            <p
                                                class="text-xs text-muted-foreground"
                                            >
                                                Enter the scale reading to
                                                finalize the amount and unlock
                                                customer payment.
                                            </p>
                                            <p
                                                v-if="weighingBlockedReason"
                                                class="flex items-start gap-1.5 text-xs text-amber-700"
                                                role="status"
                                            >
                                                <Truck
                                                    class="mt-0.5 h-3.5 w-3.5 shrink-0"
                                                />
                                                {{ weighingBlockedReason }}
                                            </p>
                                            <p
                                                v-if="
                                                    form.errors.actual_weight_kg
                                                "
                                                class="text-xs text-red-500"
                                            >
                                                {{
                                                    form.errors.actual_weight_kg
                                                }}
                                            </p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="border-t pt-8">
                        <p
                            class="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                        >
                            Pricing
                        </p>
                        <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                            <div class="col-span-12 space-y-1 sm:col-span-4">
                                <label class="text-sm font-medium"
                                    >Apply Promotion</label
                                >
                                <select
                                    v-model="form.promotion_id"
                                    @change="onPromotionChange"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                >
                                    <option value="">No promotion</option>
                                    <option
                                        v-for="p in promotions"
                                        :key="p.id"
                                        :value="p.id"
                                    >
                                        {{ p.name }} —
                                        {{
                                            p.type === 'percentage'
                                                ? `${p.value}% off`
                                                : `₱${parseFloat(p.value).toFixed(2)} off`
                                        }}
                                        {{
                                            p.min_order_amount
                                                ? ` (min ₱${parseFloat(p.min_order_amount).toFixed(0)})`
                                                : ''
                                        }}
                                    </option>
                                </select>
                                <p
                                    v-if="
                                        selectedPromotion && promoDiscount > 0
                                    "
                                    class="text-xs text-green-600"
                                >
                                    Discount: ₱{{ promoDiscount.toFixed(2) }}
                                    applied
                                </p>
                                <p
                                    v-else-if="
                                        selectedPromotion && promoDiscount === 0
                                    "
                                    class="text-xs text-amber-600"
                                >
                                    Order total does not meet the minimum
                                    requirement.
                                </p>
                            </div>
                            <div class="col-span-12 space-y-1 sm:col-span-3">
                                <label class="text-sm font-medium"
                                    >Additional Charges</label
                                >
                                <input
                                    v-model="form.additional_charges"
                                    type="number"
                                    @wheel="blurOnWheel"
                                    step="0.01"
                                    min="0"
                                    :class="[
                                        'w-full rounded-lg border px-3 py-2.5 text-sm focus:ring-1',
                                        supplyCost > 0
                                            ? 'border-amber-400 bg-amber-50 focus:border-amber-500 focus:ring-amber-400'
                                            : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500',
                                    ]"
                                />
                                <p
                                    v-if="supplyCost > 0"
                                    class="text-xs text-amber-600"
                                >
                                    Auto-filled from supplies (₱{{
                                        supplyCost.toFixed(2)
                                    }})
                                </p>
                            </div>
                            <div class="col-span-12 space-y-1 sm:col-span-2">
                                <label class="text-sm font-medium"
                                    >Discount</label
                                >
                                <input
                                    v-model="form.discount_amount"
                                    type="number"
                                    @wheel="blurOnWheel"
                                    step="0.01"
                                    min="0"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div class="border-t pt-8">
                        <p
                            class="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                        >
                            Payment and schedule
                        </p>
                        <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                            <div class="col-span-12 space-y-1 sm:col-span-3">
                                <label class="text-sm font-medium"
                                    >Payment Method</label
                                >
                                <select
                                    v-model="form.payment_method"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                >
                                    <option
                                        v-for="m in paymentMethods"
                                        :key="m"
                                        :value="m"
                                    >
                                        {{ m.toUpperCase() }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-span-12 space-y-2 sm:col-span-6">
                                <p
                                    id="payment-state-label"
                                    class="text-sm font-medium"
                                >
                                    Payment
                                </p>
                                <div
                                    class="flex gap-2"
                                    role="radiogroup"
                                    aria-labelledby="payment-state-label"
                                >
                                    <label
                                        v-for="option in paymentOptions"
                                        :key="option.value"
                                        class="flex flex-1 cursor-pointer items-center gap-2 rounded-lg border px-3 py-2.5 text-sm transition has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-ring"
                                        :class="
                                            form.payment_state === option.value
                                                ? 'border-blue-500 bg-blue-50 text-blue-700'
                                                : 'border-gray-300 hover:border-gray-400'
                                        "
                                    >
                                        <input
                                            v-model="form.payment_state"
                                            type="radio"
                                            name="payment_state"
                                            :value="option.value"
                                            class="h-4 w-4 accent-blue-600"
                                        />
                                        {{ option.label }}
                                    </label>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    {{ paymentNote }}
                                </p>
                                <p
                                    v-if="form.errors.payment_state"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.payment_state }}
                                </p>
                            </div>
                            <div class="col-span-12 space-y-1 sm:col-span-3">
                                <label class="text-sm font-medium"
                                    >Estimated Completion</label
                                >
                                <input
                                    v-model="form.estimated_completion_at"
                                    type="datetime-local"
                                    disabled
                                    class="w-full cursor-not-allowed rounded-lg border border-gray-300 bg-muted/50 px-3 py-2.5 text-sm text-muted-foreground focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Supplies -->
                    <div class="border-t pt-8">
                        <div class="mb-4 flex items-center justify-between">
                            <p
                                class="text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                            >
                                Supplies Used
                            </p>
                            <button
                                type="button"
                                @click="addSupply"
                                class="text-sm font-medium text-blue-600 transition hover:text-blue-800"
                            >
                                + Add Supply
                            </button>
                        </div>
                        <p
                            v-if="form.supplies.length === 0"
                            class="text-sm text-muted-foreground italic"
                        >
                            No supplies added yet.
                        </p>
                        <div class="space-y-3">
                            <div
                                v-for="(supply, i) in form.supplies"
                                :key="i"
                                class="grid grid-cols-12 items-end gap-x-4"
                            >
                                <div
                                    class="col-span-12 space-y-1 sm:col-span-5"
                                >
                                    <label
                                        class="text-xs font-medium text-muted-foreground"
                                        >Inventory Item</label
                                    >
                                    <select
                                        v-model="supply.inventory_id"
                                        @change="onSupplyItemChange(supply)"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    >
                                        <option :value="0" disabled>
                                            Select item
                                        </option>
                                        <option
                                            v-for="item in inventoryItems"
                                            :key="item.id"
                                            :value="item.id"
                                        >
                                            {{ item.name }} ({{
                                                availableFor(item)
                                            }}
                                            {{ item.unit }}){{
                                                item.selling_price
                                                    ? ' — ₱' +
                                                      parseFloat(
                                                          item.selling_price,
                                                      ).toFixed(2)
                                                    : ''
                                            }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="
                                            heldByOrder(supply.inventory_id) > 0
                                        "
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{ heldByOrder(supply.inventory_id) }}
                                        already used on this order.
                                    </p>
                                    <p
                                        v-if="
                                            selectedItem(supply.inventory_id) &&
                                            Number(supply.quantity_used) >
                                                availableFor(
                                                    selectedItem(
                                                        supply.inventory_id,
                                                    )!,
                                                )
                                        "
                                        class="text-xs text-red-600"
                                    >
                                        Only
                                        {{
                                            availableFor(
                                                selectedItem(
                                                    supply.inventory_id,
                                                )!,
                                            )
                                        }}
                                        available for this order.
                                    </p>
                                </div>
                                <div class="col-span-4 space-y-1 sm:col-span-2">
                                    <label
                                        class="text-xs font-medium text-muted-foreground"
                                        >Qty Used</label
                                    >
                                    <input
                                        v-model="supply.quantity_used"
                                        type="number"
                                        @wheel="blurOnWheel"
                                        step="0.01"
                                        min="0.01"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    />
                                </div>
                                <div class="col-span-4 space-y-1 sm:col-span-2">
                                    <label
                                        class="text-xs font-medium text-muted-foreground"
                                        >Unit</label
                                    >
                                    <input
                                        v-model="supply.unit"
                                        type="text"
                                        placeholder="e.g. ml"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    />
                                </div>
                                <div class="col-span-3 space-y-1 sm:col-span-2">
                                    <label
                                        class="text-xs font-medium text-amber-600"
                                        >Line Cost</label
                                    >
                                    <div
                                        class="flex h-[38px] items-center rounded-lg border border-amber-200 bg-amber-50 px-3 text-sm font-medium text-amber-800"
                                    >
                                        ₱{{
                                            (
                                                (parseFloat(
                                                    inventoryItems?.find(
                                                        (it) =>
                                                            it.id ===
                                                            supply.inventory_id,
                                                    )?.selling_price ?? '0',
                                                ) || 0) *
                                                (supply.quantity_used || 0)
                                            ).toFixed(2)
                                        }}
                                    </div>
                                </div>
                                <div class="col-span-1 flex justify-end pb-1">
                                    <button
                                        type="button"
                                        @click="removeSupply(i)"
                                        class="text-red-400 hover:text-red-600"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Supply total -->
                        <div
                            v-if="supplyCost > 0"
                            class="mt-2 flex items-center justify-between rounded-lg border border-amber-200 bg-amber-50 px-4 py-2"
                        >
                            <span class="text-sm font-medium text-amber-700"
                                >Supplies Total</span
                            >
                            <span class="text-base font-bold text-amber-900"
                                >₱{{ supplyCost.toFixed(2) }}</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Side panel: the only card on the page -->
                <aside
                    class="rounded-xl border bg-card p-4 shadow-sm lg:sticky lg:top-4"
                >
                    <section aria-labelledby="order-summary">
                        <p
                            id="order-summary"
                            class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                        >
                            Order summary
                        </p>
                        <p
                            class="mt-3 truncate text-base font-bold"
                            :class="
                                form.customer_name
                                    ? ''
                                    : 'text-muted-foreground/60'
                            "
                        >
                            {{ form.customer_name || 'Customer name' }}
                        </p>
                        <p class="truncate text-xs text-muted-foreground">
                            {{
                                selectedService?.service_name ??
                                'No service selected'
                            }}
                        </p>
                        <dl class="mt-3 space-y-1.5 text-xs">
                            <div class="flex justify-between gap-3">
                                <dt class="text-muted-foreground">Service</dt>
                                <dd class="font-medium">
                                    {{ formatCurrency(baseTotal) }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-muted-foreground">
                                    Additional charges
                                </dt>
                                <dd class="font-medium">
                                    {{
                                        formatCurrency(
                                            Number(form.additional_charges) ||
                                                0,
                                        )
                                    }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-muted-foreground">Discount</dt>
                                <dd class="font-medium">
                                    −{{
                                        formatCurrency(
                                            Number(form.discount_amount) || 0,
                                        )
                                    }}
                                </dd>
                            </div>
                            <div
                                v-if="deliveryFee > 0"
                                class="flex justify-between gap-3"
                            >
                                <dt class="text-muted-foreground">
                                    Delivery fee
                                </dt>
                                <dd class="font-medium">
                                    {{ formatCurrency(deliveryFee) }}
                                </dd>
                            </div>
                        </dl>
                        <div
                            class="mt-3 flex items-baseline justify-between border-t pt-3"
                        >
                            <span class="text-sm font-semibold">Total</span>
                            <span class="text-xl font-bold tabular-nums">{{
                                formatCurrency(computedTotal)
                            }}</span>
                        </div>
                        <dl class="mt-3 space-y-1.5 text-xs">
                            <div class="flex justify-between gap-3">
                                <dt class="text-muted-foreground">
                                    Paid so far
                                </dt>
                                <dd class="font-medium">
                                    {{ formatCurrency(paidSoFar) }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-muted-foreground">
                                    Balance due
                                </dt>
                                <dd class="font-semibold">
                                    {{ formatCurrency(balanceDue) }}
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section class="mt-4 border-t pt-4">
                        <p
                            class="text-xs text-muted-foreground"
                            role="status"
                            aria-live="polite"
                        >
                            {{ saveStatus }}
                        </p>
                        <Button
                            type="submit"
                            class="mt-3 w-full"
                            :disabled="!form.isDirty || form.processing"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            {{ form.processing ? 'Saving…' : 'Save Changes' }}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            class="mt-2 w-full"
                            :disabled="form.processing"
                            @click="
                                router.visit(
                                    `${base}/${shopOrder.payment_public_id}`,
                                )
                            "
                        >
                            Cancel
                        </Button>
                    </section>
                </aside>
            </form>
        </div>
    </ShopLayout>
</template>
