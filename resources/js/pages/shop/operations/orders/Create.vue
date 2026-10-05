<script setup lang="ts">
import PhilippinePhoneInput from '@/components/PhilippinePhoneInput.vue';
import CustomerServiceAgreement from '@/components/customer/CustomerServiceAgreement.vue';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

// ─── Types ─────────────────────────────────────────

interface Service {
    id: number;
    service_name: string;
    pricing_model: 'per_kg' | 'per_bundle';
    price_per_kg: string | null;
    bundle_weight_kg: string | null;
    bundle_price: string | null;
    estimated_hours: number | null;
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
    pickupTypes: string[];
    paymentMethods: string[];
    services?: Service[];
    inventoryItems?: InventoryItem[];
    promotions?: Promotion[];
    customerAgreement: { title: string; version: string; content: string; effective_at: string };
}>();

// ─── Base URL ──────────────────────────────────────

const page = usePage<AppPageProps>();
const isOwner = computed(() => page.props.auth.user.role === 'owner');
const base = computed(() =>
    isOwner.value ? '/shop/operations/orders' : '/staff/operations/orders',
);
const availablePickupTypes = computed(() =>
    props.pickupTypes.filter(
        (type) =>
            type !== 'pickup' || page.props.shopCapabilities?.offers_pickup,
    ),
);

// ─── Form ──────────────────────────────────────────

const form = useForm({
    shop_id: props.shopId,
    customer_name: '',
    customer_phone: '',
    customer_address: '',
    service_id: '' as string | number,
    pickup_type: 'walk_in',
    pricing_model: 'per_kg' as 'per_kg' | 'per_bundle',
    estimated_weight_kg: '' as string | number,
    actual_weight_kg: '' as string | number,
    price_per_kg: 0,
    bundle_weight_kg: '' as string | number,
    bundle_price: 0,
    bundle_quantity: 1,
    additional_charges: 0,
    discount_amount: 0,
    total_amount: 0,
    payment_status: 'unpaid',
    amount_paid: 0,
    estimated_completion_at: '',
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
    return `${svc.service_name} — ${formatCurrency(svc.bundle_price)} / ${svc.bundle_weight_kg}kg bundle`;
};

const formatLabel = (s: string) =>
    s.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());

const formatCurrency = (v: string | number | null) =>
    v !== null
        ? `₱${Number(v).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
        : '—';

// ─── Derived ───────────────────────────────────────

const selectedService = computed(() =>
    props.services?.find((s) => s.id === Number(form.service_id)),
);

const isPerKg = computed(() => form.pricing_model === 'per_kg');

const onServiceChange = () => {
    const svc = selectedService.value;
    if (!svc) return;

    form.pricing_model = svc.pricing_model;

    if (svc.pricing_model === 'per_kg') {
        form.price_per_kg = parseFloat(svc.price_per_kg ?? '0');
        form.bundle_price = 0;
        form.bundle_weight_kg = '';
        form.bundle_quantity = 1;
    } else {
        form.bundle_price = parseFloat(svc.bundle_price ?? '0');
        form.bundle_weight_kg = svc.bundle_weight_kg ?? '';
        form.price_per_kg = 0;
        form.estimated_weight_kg = '';
        form.actual_weight_kg = '';
    }

    if (svc.estimated_hours) {
        const completion = new Date();
        completion.setHours(completion.getHours() + svc.estimated_hours);
        form.estimated_completion_at = completion.toISOString().slice(0, 16);
    }
};

const selectedPromotion = computed(() =>
    props.promotions?.find((p) => p.id === Number(form.promotion_id)),
);

const baseTotal = computed(() => {
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

const submit = () => {
    form.total_amount = computedTotal.value;
    form.post(base.value);
};
</script>

<template>
    <Head title="New Order" />

    <ShopLayout title="New Order">
        <div class="space-y-8 px-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">New Order</h2>
                    <p class="text-sm text-muted-foreground">
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

            <form @submit.prevent="submit" class="space-y-8">
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
                                    'border-red-500': form.errors.customer_name,
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
                            <label class="text-sm font-medium">Phone</label>
                            <PhilippinePhoneInput
                                v-model="form.customer_phone"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            />
                        </div>
                        <div class="col-span-12 space-y-1 sm:col-span-4">
                            <label class="text-sm font-medium"
                                >Pickup Type
                                <span class="text-red-500">*</span></label
                            >
                            <select
                                v-model="form.pickup_type"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            >
                                <option
                                    v-for="t in availablePickupTypes"
                                    :key="t"
                                    :value="t"
                                >
                                    {{ formatLabel(t) }}
                                </option>
                            </select>
                        </div>
                        <div class="col-span-12 space-y-1">
                            <label class="text-sm font-medium">Address</label>
                            <textarea
                                v-model="form.customer_address"
                                rows="2"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- ── Service ───────────────────────────── -->
                <div>
                    <p
                        class="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                    >
                        Service
                    </p>
                    <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                        <div class="col-span-12 space-y-1 sm:col-span-5">
                            <label class="text-sm font-medium"
                                >Service
                                <span class="text-red-500">*</span></label
                            >
                            <select
                                v-model="form.service_id"
                                @change="onServiceChange"
                                required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                :class="{
                                    'border-red-500': form.errors.service_id,
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

                        <div
                            v-if="selectedService"
                            class="col-span-12 flex items-end pb-0.5 sm:col-span-7"
                        >
                            <div
                                class="flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-sm"
                                :class="
                                    isPerKg
                                        ? 'border border-blue-200 bg-blue-50 text-blue-800'
                                        : 'border border-violet-200 bg-violet-50 text-violet-800'
                                "
                            >
                                <span class="font-semibold">{{
                                    selectedService.service_name
                                }}</span>
                                <span class="text-xs opacity-70">•</span>
                                <span v-if="isPerKg"
                                    >{{
                                        formatCurrency(
                                            selectedService.price_per_kg,
                                        )
                                    }}
                                    per kg</span
                                >
                                <span v-else
                                    >{{
                                        formatCurrency(
                                            selectedService.bundle_price,
                                        )
                                    }}
                                    / {{ selectedService.bundle_weight_kg }}kg
                                    bundle</span
                                >
                                <span
                                    v-if="selectedService.estimated_hours"
                                    class="ml-auto text-xs opacity-70"
                                >
                                    ~{{ selectedService.estimated_hours }}h
                                    turnaround
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Weight / Bundle ───────────────────── -->
                <div>
                    <p
                        class="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                    >
                        {{ isPerKg ? 'Weight' : 'Bundle' }}
                    </p>
                    <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                        <template v-if="isPerKg">
                            <div class="col-span-12 space-y-1 sm:col-span-4">
                                <label class="text-sm font-medium"
                                    >Estimated Weight (kg)
                                    <span class="text-red-500">*</span></label
                                >
                                <input
                                    v-model="form.estimated_weight_kg"
                                    type="number"
                                    step="0.01"
                                    min="0.1"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    :class="{
                                        'border-red-500':
                                            form.errors.estimated_weight_kg,
                                    }"
                                />
                                <p
                                    v-if="form.errors.estimated_weight_kg"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.estimated_weight_kg }}
                                </p>
                            </div>
                            <div class="col-span-12 space-y-1 sm:col-span-4">
                                <label class="text-sm font-medium"
                                    >Actual Weight (kg)
                                    <span class="text-red-500">*</span></label
                                >
                                <input
                                    v-model="form.actual_weight_kg"
                                    type="number"
                                    step="0.01"
                                    min="0.1"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    :class="{
                                        'border-red-500':
                                            form.errors.actual_weight_kg,
                                    }"
                                />
                                <p
                                    v-if="form.errors.actual_weight_kg"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.actual_weight_kg }}
                                </p>
                            </div>
                            <div class="col-span-12 space-y-1 sm:col-span-4">
                                <label class="text-sm font-medium"
                                    >Price per KG
                                    <span class="text-xs text-muted-foreground"
                                        >(auto-filled)</span
                                    ></label
                                >
                                <input
                                    v-model="form.price_per_kg"
                                    type="number"
                                    step="0.01"
                                    readonly
                                    class="w-full rounded-lg border border-gray-200 bg-muted/40 px-3 py-2.5 text-sm text-muted-foreground"
                                />
                            </div>
                        </template>

                        <template v-else>
                            <div class="col-span-12 space-y-1 sm:col-span-4">
                                <label class="text-sm font-medium"
                                    >Bundle Size
                                    <span class="text-xs text-muted-foreground"
                                        >(auto-filled)</span
                                    ></label
                                >
                                <div
                                    class="flex h-[42px] items-center rounded-lg border border-gray-200 bg-muted/40 px-3 text-sm text-muted-foreground"
                                >
                                    {{
                                        form.bundle_weight_kg
                                            ? `${form.bundle_weight_kg} kg / bundle`
                                            : '—'
                                    }}
                                </div>
                            </div>
                            <div class="col-span-12 space-y-1 sm:col-span-4">
                                <label class="text-sm font-medium"
                                    >Bundle Price
                                    <span class="text-xs text-muted-foreground"
                                        >(auto-filled)</span
                                    ></label
                                >
                                <div
                                    class="flex h-[42px] items-center rounded-lg border border-gray-200 bg-muted/40 px-3 text-sm text-muted-foreground"
                                >
                                    {{
                                        form.bundle_price
                                            ? formatCurrency(form.bundle_price)
                                            : '—'
                                    }}
                                </div>
                            </div>
                            <div class="col-span-12 space-y-1 sm:col-span-4">
                                <label class="text-sm font-medium"
                                    >Number of Bundles
                                    <span class="text-red-500">*</span></label
                                >
                                <input
                                    v-model="form.bundle_quantity"
                                    type="number"
                                    min="1"
                                    step="1"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                />
                                <p class="mt-0.5 text-xs text-muted-foreground">
                                    Est. total weight:
                                    {{
                                        (
                                            Number(form.bundle_quantity) *
                                            Number(form.bundle_weight_kg)
                                        ).toFixed(1)
                                    }}
                                    kg
                                </p>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- ── Pricing ───────────────────────────── -->
                <div>
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
                            <label class="text-sm font-medium">Discount</label>
                            <input
                                v-model="form.discount_amount"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            />
                            <p
                                v-if="selectedPromotion && promoDiscount > 0"
                                class="text-xs text-green-600"
                            >
                                Auto-filled from promotion
                            </p>
                        </div>
                        <div class="col-span-12 space-y-1 sm:col-span-3">
                            <label class="text-sm font-medium"
                                >Computed Total</label
                            >
                            <div
                                class="flex h-[42px] items-center justify-between rounded-lg border border-blue-200 bg-blue-50 px-4"
                            >
                                <span class="text-sm text-blue-700">
                                    <template v-if="isPerKg"
                                        >{{ form.actual_weight_kg || 0 }} kg ×
                                        {{
                                            formatCurrency(form.price_per_kg)
                                        }}</template
                                    >
                                    <template v-else
                                        >{{ form.bundle_quantity }} bundle(s) ×
                                        {{
                                            formatCurrency(form.bundle_price)
                                        }}</template
                                    >
                                </span>
                                <span class="text-xl font-bold text-blue-900">
                                    ₱{{
                                        computedTotal.toLocaleString('en-PH', {
                                            minimumFractionDigits: 2,
                                        })
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Payment ───────────────────────────── -->
                <div>
                    <p
                        class="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                    >
                        Payment
                    </p>
                    <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                        <div class="col-span-12 space-y-1 sm:col-span-3">
                            <label class="text-sm font-medium"
                                >Payment Status</label
                            >
                            <select
                                v-model="form.payment_status"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            >
                                <option value="unpaid">Unpaid</option>
                                <option value="partial">Partial</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                        <div class="col-span-12 space-y-1 sm:col-span-3">
                            <label class="text-sm font-medium"
                                >Amount Paid</label
                            >
                            <input
                                v-model="form.amount_paid"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            />
                        </div>
                        <div class="col-span-12 space-y-1 sm:col-span-3">
                            <label class="text-sm font-medium"
                                >Estimated Completion</label
                            >
                            <input
                                v-model="form.estimated_completion_at"
                                type="datetime-local"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- ── Supplies ───────────────────────────── -->
                <div>
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
                            class="grid grid-cols-12 items-end gap-x-4 rounded-lg border border-gray-200 bg-gray-50/50 p-3"
                        >
                            <!-- Inventory Item -->
                            <div class="col-span-12 space-y-1 sm:col-span-4">
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
                            <div class="col-span-11 space-y-1 sm:col-span-3">
                                <label
                                    class="text-xs font-medium text-muted-foreground"
                                    >Line Cost</label
                                >
                                <div
                                    class="flex h-[38px] items-center rounded-lg border border-amber-200 bg-amber-50 px-3 text-sm font-semibold text-amber-700"
                                >
                                    {{
                                        (() => {
                                            const item = inventoryItems?.find(
                                                (x) =>
                                                    x.id ===
                                                    supply.inventory_id,
                                            );
                                            const price = item?.selling_price
                                                ? parseFloat(item.selling_price)
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
                            v-if="form.supplies.length > 0 && supplyCost > 0"
                            class="flex items-center justify-between rounded-lg border border-amber-200 bg-amber-50 px-4 py-2.5"
                        >
                            <span class="text-sm text-amber-700"
                                >Supply cost auto-added to Additional
                                Charges</span
                            >
                            <span class="text-base font-bold text-amber-800">
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

                <!-- ── Actions ───────────────────────────── -->
                <CustomerServiceAgreement
                    v-model="form.customer_agreement_accepted"
                    :agreement="customerAgreement"
                    confirmation-text="I confirm that the customer was shown this agreement and accepted its garment care, damage, refund, compensation, and claim terms before I created the order."
                />
                <p v-if="form.errors.customer_agreement_accepted" class="text-xs text-red-500">{{ form.errors.customer_agreement_accepted }}</p>

                <div class="flex items-center justify-end gap-3 border-t pt-4">
                    <button
                        type="button"
                        @click="router.visit(base)"
                        class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Creating…' : 'Create Order' }}
                    </button>
                </div>
            </form>
        </div>
    </ShopLayout>
</template>
