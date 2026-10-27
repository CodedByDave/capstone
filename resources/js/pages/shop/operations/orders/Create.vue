<script setup lang="ts">
import PhilippinePhoneInput from '@/components/PhilippinePhoneInput.vue';
import CustomerServiceAgreement from '@/components/customer/CustomerServiceAgreement.vue';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps } from '@/types';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

// ─── Types ─────────────────────────────────────────

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

const props = defineProps<{
    shopId: number;
    /** Pickup orders at this shop are weighed only after the rider brings the laundry in. */
    pickupAwaitsReceipt: boolean;
    /** Minutes from placing an order to its estimated completion, from the shop's order automation setting. */
    completionMinutes: number;
    pickupTypes: string[];
    paymentMethods: string[];
    services?: Service[];
    inventoryItems?: InventoryItem[];
    promotions?: Promotion[];
    customerAgreement: {
        title: string;
        version: string;
        content: string;
        effective_at: string;
    };
}>();

// ─── Base URL ──────────────────────────────────────

const page = usePage<AppPageProps>();
const isOwner = computed(() => page.props.auth.user.role === 'owner');
const base = computed(() =>
    isOwner.value ? '/shop/operations/orders' : '/staff/operations/orders',
);

// ─── Form ──────────────────────────────────────────

const form = useForm({
    shop_id: props.shopId,
    customer_name: '',
    customer_phone: '',
    alternate_contact_name: '',
    alternate_contact_phone: '',
    customer_address: '',
    deliver_to_customer: false,
    service_id: '' as string | number,
    pickup_type: 'walk_in',
    pricing_model: 'per_kg' as 'per_kg' | 'per_bundle' | 'per_piece',
    actual_weight_kg: '' as string | number,
    price_per_kg: 0,
    bundle_weight_kg: '' as string | number,
    bundle_price: 0,
    bundle_quantity: 1,
    price_per_piece: 0,
    piece_count: '' as string | number,
    additional_charges: 0,
    discount_amount: 0,
    total_amount: 0,
    amount_paid: 0,
    promotion_id: '' as string | number,
    supplies: [] as {
        inventory_id: number;
        quantity_used: number;
        unit: string;
    }[],
    customer_agreement_version: props.customerAgreement.version,
    customer_agreement_accepted: false,
});

// ─── Helpers ───────────────────────────────────────

const formatServiceLabel = (svc: Service) => {
    if (svc.pricing_model === 'per_kg')
        return `${svc.service_name} — ${formatCurrency(svc.price_per_kg)}/kg`;
    if (svc.pricing_model === 'per_piece')
        return `${svc.service_name} — ${formatCurrency(svc.price_per_piece)}/piece`;
    return `${svc.service_name} — ${formatCurrency(svc.bundle_price)} / ${svc.bundle_weight_kg}kg bundle`;
};

// The laundry is dropped off at the shop either way. "Delivery" means the shop sends the finished
// laundry to the customer; "Walk-in" means the customer collects it.
const offersDelivery = computed(
    () => !!page.props.shopCapabilities?.offers_delivery,
);
const handoffOptions = computed(() => [
    { value: 'walk_in', label: 'Walk-in' },
    ...(offersDelivery.value ? [{ value: 'delivery', label: 'Delivery' }] : []),
]);
const handoff = computed({
    get: () => (form.deliver_to_customer ? 'delivery' : 'walk_in'),
    set: (value: string) => {
        form.pickup_type = 'walk_in';
        form.deliver_to_customer = value === 'delivery';
    },
});

const formatCurrency = (v: string | number | null) =>
    v !== null
        ? `₱${Number(v).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
        : '—';

// ─── Derived ───────────────────────────────────────

const selectedService = computed(() =>
    props.services?.find((s) => s.id === Number(form.service_id)),
);

const isPerKg = computed(() => form.pricing_model === 'per_kg');
const isPerPiece = computed(() => form.pricing_model === 'per_piece');

const onServiceChange = () => {
    const svc = selectedService.value;
    if (!svc) return;

    form.pricing_model = svc.pricing_model;

    form.piece_count = svc.pricing_model === 'per_piece' ? 1 : '';
    form.price_per_piece =
        svc.pricing_model === 'per_piece'
            ? parseFloat(svc.price_per_piece ?? '0')
            : 0;

    if (svc.pricing_model === 'per_piece') {
        form.price_per_kg = 0;
        form.bundle_price = 0;
        form.bundle_weight_kg = '';
        form.bundle_quantity = 1;
        form.actual_weight_kg = '';
    } else if (svc.pricing_model === 'per_kg') {
        form.price_per_kg = parseFloat(svc.price_per_kg ?? '0');
        form.bundle_price = 0;
        form.bundle_weight_kg = '';
        form.bundle_quantity = 1;
    } else {
        form.bundle_price = parseFloat(svc.bundle_price ?? '0');
        form.bundle_weight_kg = svc.bundle_weight_kg ?? '';
        form.price_per_kg = 0;
        form.actual_weight_kg = '';
    }
};

const selectedPromotion = computed(() =>
    props.promotions?.find((p) => p.id === Number(form.promotion_id)),
);

const baseTotal = computed(() => {
    if (isPerPiece.value) {
        return (
            (parseInt(String(form.piece_count)) || 0) *
            (parseFloat(String(form.price_per_piece)) || 0)
        );
    }

    if (isPerKg.value) {
        return (
            (parseFloat(String(form.actual_weight_kg)) || 0) *
            (parseFloat(String(form.price_per_kg)) || 0)
        );
    }
    return (
        (parseFloat(String(form.bundle_quantity)) || 1) *
        (parseFloat(String(form.bundle_price)) || 0)
    );
});

const promoDiscount = computed(() => {
    const promo = selectedPromotion.value;
    if (!promo) return 0;
    const total =
        baseTotal.value + (parseFloat(String(form.additional_charges)) || 0);
    const min = promo.min_order_amount ? parseFloat(promo.min_order_amount) : 0;
    if (min > 0 && total < min) return 0;
    if (promo.type === 'percentage')
        return Math.round(total * (parseFloat(promo.value) / 100) * 100) / 100;
    return Math.min(parseFloat(promo.value), total);
});

const onPromotionChange = () => {
    form.discount_amount = promoDiscount.value;
};

const computedTotal = computed(() => {
    const charges = parseFloat(String(form.additional_charges)) || 0;
    const discount = parseFloat(String(form.discount_amount)) || 0;
    return Math.max(0, baseTotal.value + charges - discount);
});

// ─── Supplies ──────────────────────────────────────

const unitOptions = [
    'pcs',
    'kg',
    'g',
    'liters',
    'ml',
    'bottles',
    'boxes',
    'packs',
    'rolls',
    'sachets',
];

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

// Sum (qty × selling_price) for every supply that has a price
const supplyCost = computed(() => {
    return form.supplies.reduce((sum, supply) => {
        const item = props.inventoryItems?.find(
            (i) => i.id === supply.inventory_id,
        );
        const price = item?.selling_price ? parseFloat(item.selling_price) : 0;
        const qty = parseFloat(String(supply.quantity_used)) || 0;
        return sum + price * qty;
    }, 0);
});

function recalcSupplyCharges() {
    form.additional_charges = parseFloat(supplyCost.value.toFixed(2));
}

// Re-run whenever any qty_used value changes
watch(() => form.supplies.map((s) => s.quantity_used), recalcSupplyCharges, {
    deep: true,
});

// ─── Submit ────────────────────────────────────────

// ─── Payment ───────────────────────────────────────

type PaymentMode = 'unpaid' | 'partial' | 'full';

const paymentOptions: { value: PaymentMode; label: string }[] = [
    { value: 'unpaid', label: 'Not paid yet' },
    { value: 'partial', label: 'Partial payment' },
    { value: 'full', label: 'Paid in full' },
];
const paymentMode = ref<PaymentMode>('unpaid');

watch(paymentMode, (mode) => {
    form.amount_paid = mode === 'full' ? computedTotal.value : 0;
});
// A full payment follows the total while the order is still being filled in.
watch(computedTotal, (total) => {
    if (paymentMode.value === 'full') form.amount_paid = total;
});

// ─── Estimated completion ──────────────────────────

// The server sets the real time when the order is saved; this shows what it will be.
const now = ref(new Date());
let clock: ReturnType<typeof setInterval> | undefined;
onMounted(() => {
    clock = setInterval(() => (now.value = new Date()), 30_000);
});
onBeforeUnmount(() => clearInterval(clock));

const completionAt = computed(
    () => new Date(now.value.getTime() + props.completionMinutes * 60_000),
);

const completionLabel = computed(() => {
    const time = completionAt.value.toLocaleTimeString('en-PH', {
        hour: 'numeric',
        minute: '2-digit',
    });
    const sameDay =
        completionAt.value.toDateString() === now.value.toDateString();

    return sameDay
        ? `${time} today`
        : `${time}, ${completionAt.value.toLocaleDateString('en-PH', { month: 'short', day: 'numeric' })}`;
});

const completionWait = computed(() => {
    const minutes = props.completionMinutes;
    const hours = Math.floor(minutes / 60);
    const rest = minutes % 60;
    const parts = [
        hours ? `${hours} hour${hours === 1 ? '' : 's'}` : '',
        rest ? `${rest} minute${rest === 1 ? '' : 's'}` : '',
    ].filter(Boolean);

    return parts.join(' ') || 'right away';
});

const saveStatus = computed(() =>
    form.isDirty ? 'You have unsaved changes' : 'No changes yet',
);

// Scrolling the page with a number box focused must not nudge its value (20 becoming 19.99).
const blurOnWheel = (event: WheelEvent) =>
    (event.target as HTMLInputElement).blur();

const submit = () => {
    form.total_amount = computedTotal.value;
    form.post(base.value);
};

watch(
    () => [form.actual_weight_kg, form.bundle_weight_kg, form.pricing_model],
    () => {
        if (form.pricing_model !== 'per_bundle') return;

        const actualKg = parseFloat(String(form.actual_weight_kg)) || 0;
        const bundleKg = parseFloat(String(form.bundle_weight_kg)) || 1;
        form.bundle_quantity =
            actualKg > 0 ? Math.max(1, Math.ceil(actualKg / bundleKg)) : 1;
    },
);
</script>

<template>
    <Head title="New Order" />

    <ShopLayout title="New Order">
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
                        <span class="font-medium text-foreground"
                            >New order</span
                        >
                    </nav>
                    <h2 class="text-2xl font-bold tracking-tight">New order</h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Fill in the details to create a new laundry order.
                    </p>
                </div>
                <button
                    @click="router.visit(base)"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                >
                    &larr; Back to Orders
                </button>
            </div>

            <form
                class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_300px]"
                @submit.prevent="submit"
            >
                <!-- Form -->
                <div class="space-y-8">
                    <!-- ── Customer Information ──────────────── -->
                    <div>
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
                                        v-if="form.deliver_to_customer"
                                        class="text-red-500"
                                        >*</span
                                    ></label
                                >
                                <PhilippinePhoneInput
                                    v-model="form.customer_phone"
                                    :required="form.deliver_to_customer"
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
                            <div class="col-span-12 space-y-1">
                                <label class="text-sm font-medium"
                                    >Address
                                    <span
                                        v-if="form.deliver_to_customer"
                                        class="text-red-500"
                                        >*</span
                                    ></label
                                >
                                <textarea
                                    v-model="form.customer_address"
                                    :required="form.deliver_to_customer"
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
                            <template v-if="form.deliver_to_customer">
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

                    <!-- ── Service and weight ─────────────────── -->
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
                                        <label class="text-sm font-medium"
                                            >Service
                                            <span class="text-red-500"
                                                >*</span
                                            ></label
                                        >
                                        <select
                                            v-model="form.service_id"
                                            @change="onServiceChange"
                                            required
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                            :class="{
                                                'border-red-500':
                                                    form.errors.service_id,
                                            }"
                                        >
                                            <option value="" disabled>
                                                Select a service…
                                            </option>
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
                                        <div
                                            class="col-span-12 space-y-1 sm:col-span-12"
                                        >
                                            <label class="text-sm font-medium"
                                                >Price per Piece
                                                <span
                                                    class="text-xs text-muted-foreground"
                                                    >(auto-filled)</span
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
                                        <div
                                            class="col-span-12 space-y-1 sm:col-span-12"
                                        >
                                            <label class="text-sm font-medium"
                                                >Number of Pieces
                                                <span class="text-red-500"
                                                    >*</span
                                                ></label
                                            >
                                            <input
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

                                    <template v-else-if="isPerKg">
                                        <div
                                            class="col-span-12 space-y-1 sm:col-span-12"
                                        >
                                            <label class="text-sm font-medium"
                                                >Actual Weight (kg)
                                                <span class="text-red-500"
                                                    >*</span
                                                ></label
                                            >
                                            <input
                                                v-model="form.actual_weight_kg"
                                                type="number"
                                                @wheel="blurOnWheel"
                                                step="0.01"
                                                min="0.1"
                                                required
                                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                                :class="{
                                                    'border-red-500':
                                                        form.errors
                                                            .actual_weight_kg,
                                                }"
                                            />
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

                                    <template v-else>
                                        <div
                                            class="col-span-12 space-y-1 sm:col-span-12"
                                        >
                                            <label
                                                class="text-sm font-medium"
                                                for="bundle-actual-weight"
                                                >Actual Weight (kg)
                                                <span class="text-red-500"
                                                    >*</span
                                                ></label
                                            >
                                            <input
                                                id="bundle-actual-weight"
                                                v-model="form.actual_weight_kg"
                                                type="number"
                                                @wheel="blurOnWheel"
                                                step="0.01"
                                                min="0.1"
                                                required
                                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                                :class="{
                                                    'border-red-500':
                                                        form.errors
                                                            .actual_weight_kg,
                                                }"
                                            />
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

                    <!-- ── Pricing ───────────────────────────── -->
                    <div class="border-t pt-8">
                        <p
                            class="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                        >
                            Pricing
                        </p>
                        <div class="grid grid-cols-12 gap-x-6 gap-y-5">
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
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    :class="{
                                        'border-amber-300 bg-amber-50':
                                            supplyCost > 0,
                                    }"
                                />
                                <p
                                    v-if="supplyCost > 0"
                                    class="text-xs text-amber-600"
                                >
                                    Auto-filled from supplies (₱{{
                                        supplyCost.toLocaleString('en-PH', {
                                            minimumFractionDigits: 2,
                                        })
                                    }})
                                </p>
                            </div>
                            <div class="col-span-12 space-y-1 sm:col-span-3">
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
                                        selectedPromotion &&
                                        promoDiscount === 0 &&
                                        selectedPromotion.min_order_amount
                                    "
                                    class="text-xs text-amber-600"
                                >
                                    Order must be at least ₱{{
                                        parseFloat(
                                            selectedPromotion.min_order_amount,
                                        ).toFixed(2)
                                    }}
                                    to apply.
                                </p>
                            </div>
                            <div class="col-span-12 space-y-1 sm:col-span-3">
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
                                <p
                                    v-if="
                                        selectedPromotion && promoDiscount > 0
                                    "
                                    class="text-xs text-green-600"
                                >
                                    Auto-filled from promotion
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ── Payment ───────────────────────────── -->
                    <div class="border-t pt-8">
                        <p
                            class="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                        >
                            Payment
                        </p>
                        <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                            <div class="col-span-12 space-y-2">
                                <p
                                    id="payment-mode-label"
                                    class="text-sm font-medium"
                                >
                                    Payment received
                                </p>
                                <div
                                    class="flex flex-wrap gap-2"
                                    role="radiogroup"
                                    aria-labelledby="payment-mode-label"
                                >
                                    <label
                                        v-for="option in paymentOptions"
                                        :key="option.value"
                                        class="flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2.5 text-sm transition has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-ring"
                                        :class="
                                            paymentMode === option.value
                                                ? 'border-blue-500 bg-blue-50 text-blue-700'
                                                : 'border-gray-300 hover:border-gray-400'
                                        "
                                    >
                                        <input
                                            v-model="paymentMode"
                                            type="radio"
                                            name="payment_mode"
                                            :value="option.value"
                                            class="h-4 w-4 accent-blue-600"
                                        />
                                        {{ option.label }}
                                    </label>
                                </div>
                            </div>

                            <div
                                v-if="paymentMode === 'partial'"
                                class="col-span-12 space-y-1 sm:col-span-4"
                            >
                                <label
                                    class="text-sm font-medium"
                                    for="amount-paid"
                                    >Amount received
                                    <span class="text-red-500">*</span></label
                                >
                                <input
                                    id="amount-paid"
                                    v-model="form.amount_paid"
                                    type="number"
                                    @wheel="blurOnWheel"
                                    step="0.01"
                                    min="0.01"
                                    :max="Math.max(0.01, computedTotal - 0.01)"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    :class="{
                                        'border-red-500':
                                            form.errors.amount_paid,
                                    }"
                                />
                                <p
                                    v-if="form.errors.amount_paid"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.amount_paid }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Balance after this payment:
                                    {{
                                        formatCurrency(
                                            Math.max(
                                                0,
                                                computedTotal -
                                                    (Number(form.amount_paid) ||
                                                        0),
                                            ),
                                        )
                                    }}
                                </p>
                            </div>

                            <p
                                v-else-if="paymentMode === 'full'"
                                class="col-span-12 text-sm text-muted-foreground"
                            >
                                Amount received:
                                <span class="font-semibold text-foreground">{{
                                    formatCurrency(computedTotal)
                                }}</span>
                            </p>

                            <div class="col-span-12 space-y-1">
                                <p class="text-sm font-medium">
                                    Estimated completion
                                </p>
                                <p class="text-sm">
                                    <span class="font-semibold">{{
                                        completionLabel
                                    }}</span>
                                    <span class="text-muted-foreground">
                                        · {{ completionWait }} from now</span
                                    >
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Set automatically from your order automation
                                    setting ({{ completionMinutes }} minutes).
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ── Supplies ───────────────────────────── -->
                    <div class="border-t pt-8">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="flex flex-col">
                                <p
                                    class="text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                                >
                                    Additional Supplies Used
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Note: Record any additional supplies used
                                    during processing, if applicable.
                                </p>
                            </div>

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
                                class="grid grid-cols-12 items-end gap-x-4 rounded-lg border border-gray-200 bg-gray-50/50 p-3"
                            >
                                <!-- Inventory Item -->
                                <div
                                    class="col-span-12 space-y-1 sm:col-span-4"
                                >
                                    <label
                                        class="text-xs font-medium text-muted-foreground"
                                        >Inventory Item</label
                                    >
                                    <select
                                        v-model="supply.inventory_id"
                                        @change="onSupplyItemChange(supply)"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    >
                                        <option :value="0" disabled>
                                            Select item…
                                        </option>
                                        <option
                                            v-for="item in inventoryItems"
                                            :key="item.id"
                                            :value="item.id"
                                        >
                                            {{ item.name }} ({{ item.quantity }}
                                            {{ item.unit }} left{{
                                                item.selling_price
                                                    ? ` · ₱${parseFloat(item.selling_price).toFixed(2)}`
                                                    : ''
                                            }})
                                        </option>
                                    </select>
                                </div>

                                <!-- Qty Used -->
                                <div class="col-span-5 space-y-1 sm:col-span-2">
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

                                <!-- Unit dropdown -->
                                <div class="col-span-5 space-y-1 sm:col-span-2">
                                    <label
                                        class="text-xs font-medium text-muted-foreground"
                                        >Unit</label
                                    >
                                    <select
                                        v-model="supply.unit"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    >
                                        <option value="" disabled>
                                            Select unit…
                                        </option>
                                        <option
                                            v-for="u in unitOptions"
                                            :key="u"
                                            :value="u"
                                        >
                                            {{ u }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Line cost -->
                                <div
                                    class="col-span-11 space-y-1 sm:col-span-3"
                                >
                                    <label
                                        class="text-xs font-medium text-muted-foreground"
                                        >Line Cost</label
                                    >
                                    <div
                                        class="flex h-[38px] items-center rounded-lg border border-amber-200 bg-amber-50 px-3 text-sm font-semibold text-amber-700"
                                    >
                                        {{
                                            (() => {
                                                const item =
                                                    inventoryItems?.find(
                                                        (x) =>
                                                            x.id ===
                                                            supply.inventory_id,
                                                    );
                                                const price =
                                                    item?.selling_price
                                                        ? parseFloat(
                                                              item.selling_price,
                                                          )
                                                        : 0;
                                                const qty =
                                                    parseFloat(
                                                        String(
                                                            supply.quantity_used,
                                                        ),
                                                    ) || 0;
                                                return price
                                                    ? `₱${(price * qty).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
                                                    : '—';
                                            })()
                                        }}
                                    </div>
                                </div>

                                <!-- Remove -->
                                <div class="col-span-1 flex justify-end pb-0.5">
                                    <button
                                        type="button"
                                        @click="removeSupply(i)"
                                        class="rounded p-1 text-red-400 transition hover:text-red-600"
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

                            <!-- Supply total summary -->
                            <div
                                v-if="
                                    form.supplies.length > 0 && supplyCost > 0
                                "
                                class="flex items-center justify-between rounded-lg border border-amber-200 bg-amber-50 px-4 py-2.5"
                            >
                                <span class="text-sm text-amber-700"
                                    >Supply cost auto-added to Additional
                                    Charges</span
                                >
                                <span
                                    class="text-base font-bold text-amber-800"
                                >
                                    ₱{{
                                        supplyCost.toLocaleString('en-PH', {
                                            minimumFractionDigits: 2,
                                        })
                                    }}
                                </span>
                            </div>
                        </div>

                        <!-- Supplies errors -->
                        <p
                            v-if="form.errors.supplies"
                            class="mt-2 text-xs text-red-500"
                        >
                            {{ form.errors.supplies }}
                        </p>
                    </div>

                    <!-- ── Agreement ─────────────────────────── -->
                    <div class="border-t pt-8">
                        <CustomerServiceAgreement
                            v-model="form.customer_agreement_accepted"
                            :agreement="customerAgreement"
                            confirmation-text="I confirm that the customer was shown this agreement and accepted its garment care, damage, refund, compensation, and claim terms before I created the order."
                        />
                        <p
                            v-if="form.errors.customer_agreement_accepted"
                            class="text-xs text-red-500"
                        >
                            {{ form.errors.customer_agreement_accepted }}
                        </p>
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
                        </dl>
                        <div
                            class="mt-3 flex items-baseline justify-between border-t pt-3"
                        >
                            <span class="text-sm font-semibold">Total</span>
                            <span class="text-xl font-bold tabular-nums">{{
                                formatCurrency(computedTotal)
                            }}</span>
                        </div>
                    </section>

                    <section class="mt-4 border-t pt-4">
                        <p
                            class="text-xs text-muted-foreground"
                            role="status"
                            aria-live="polite"
                        >
                            {{ saveStatus }}
                        </p>
                        <button
                            type="submit"
                            :disabled="!form.isDirty || form.processing"
                            class="mt-3 w-full rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none disabled:opacity-50"
                        >
                            {{ form.processing ? 'Creating…' : 'Create Order' }}
                        </button>
                        <button
                            type="button"
                            @click="router.visit(base)"
                            class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        >
                            Cancel
                        </button>
                    </section>
                </aside>
            </form>
        </div>
    </ShopLayout>
</template>
