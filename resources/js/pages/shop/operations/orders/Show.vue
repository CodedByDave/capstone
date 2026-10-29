<script setup lang="ts">
import {
    DataTable,
    StatusPill,
    type DataTableHeader,
    type StatusTone,
} from '@/components/data-table';
import { type OrderPayment } from '@/components/shop/OrderPaymentsCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { usePickupAddressAutofill } from '@/composables/usePickupAddressAutofill';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Check,
    CheckCircle,
    Clock,
    CreditCard,
    Loader,
    Loader2,
    Package,
    PackageCheck,
    Pencil,
    Truck,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

interface Supply {
    id: number;
    name: string;
    pivot: {
        quantity_used: number;
        unit: string;
    };
}

interface Order {
    id: number;
    payment_public_id: string;
    order_number: string;
    customer_name: string;
    customer_phone: string | null;
    alternate_contact_name?: string | null;
    alternate_contact_phone?: string | null;
    customer_address: string | null;
    status: string;
    payment_status: string;
    payment_method: string | null;
    pricing_model: 'per_kg' | 'per_bundle' | 'per_piece';
    price_per_piece: string | null;
    piece_count: number | null;
    estimated_weight_kg: string | null;
    actual_weight_kg: string | null;
    price_per_kg: string | null;
    bundle_weight_kg: string | null;
    bundle_price: string | null;
    bundle_quantity: number | null;
    additional_charges: string;
    discount_amount: string;
    total_amount: string;
    amount_paid: string;
    pickup_type: string;
    deliver_to_customer?: boolean;
    estimated_completion_at: string | null;
    completed_at: string | null;
    created_at: string;
    service?: { service_name: string };
    supplies: Supply[];
}

const props = defineProps<{
    shopOrder: Order;
    payments: OrderPayment[];
    deliveryOption: {
        offers_delivery: boolean;
        can_send: boolean;
        delivery_status: string | null;
        has_address: boolean;
        address: string | null;
    };
    customerAgreement: {
        title: string;
        version: string;
        content: string;
        accepted_at?: string;
        customer_name?: string;
    } | null;
}>();

// A per-piece order is priced by count, so there is nothing to weigh.
const isAwaitingWeight = computed(
    () =>
        props.shopOrder.actual_weight_kg === null &&
        props.shopOrder.pricing_model !== 'per_piece',
);

const page = usePage<AppPageProps>();
const isOwner = computed(() => page.props.auth.user.role === 'owner');
const { can } = usePermissions();
const canManage = computed(() => can('Operations', 'manage'));
const base = computed(() =>
    isOwner.value ? '/shop/operations/orders' : '/staff/operations/orders',
);

// ─── Send for delivery ─────────────────────────────

const deliveryDialog = ref(false);
const deliveryAddress = ref('');
const sendingDelivery = ref(false);
const deliveryError = ref('');
const { locating, failed, detectAddress } = usePickupAddressAutofill();

function openDeliveryDialog() {
    deliveryAddress.value = props.deliveryOption.address ?? '';
    deliveryError.value = '';
    deliveryDialog.value = true;
}

async function useCurrentLocation() {
    const detected = await detectAddress();
    if (detected) deliveryAddress.value = detected;
}

function sendForDelivery() {
    sendingDelivery.value = true;
    deliveryError.value = '';
    router.post(
        `${base.value}/${props.shopOrder.payment_public_id}/send-for-delivery`,
        { delivery_address: deliveryAddress.value || null },
        {
            preserveScroll: true,
            onSuccess: () => {
                deliveryDialog.value = false;
            },
            onError: (errors) => {
                deliveryError.value =
                    errors.delivery_address ??
                    errors.delivery ??
                    'Could not send the order for delivery.';
            },
            onFinish: () => {
                sendingDelivery.value = false;
            },
        },
    );
}

onMounted(() => {
    const t = page.props.toast as any;
    if (!t) return;
    if (t.type === 'success') toast.success(t.message);
    if (t.type === 'error') toast.error(t.message);
});

const supplyHeaders: DataTableHeader[] = [
    { text: 'Item', value: 'name' },
    { text: 'Qty used', value: 'quantity_used' },
    { text: 'Unit', value: 'unit' },
];

// ─── Status progression (read-only) ────────────────

const statusSteps = [
    { value: 'pending', label: 'Pending' },
    { value: 'in_progress', label: 'In progress' },
    { value: 'completed', label: 'Completed' },
];
const isCancelled = computed(() => props.shopOrder.status === 'cancelled');
const currentStep = computed(() =>
    props.shopOrder.status === 'picked_up'
        ? statusSteps.length
        : statusSteps.findIndex(
              (step) => step.value === props.shopOrder.status,
          ),
);
const stepCircle = (index: number) =>
    index < currentStep.value
        ? 'border-blue-500 bg-blue-500 text-white'
        : index === currentStep.value
          ? 'border-blue-500'
          : 'border-input';

// ─── Payments ──────────────────────────────────────

const paymentHeaders: DataTableHeader[] = [
    { text: 'Date', value: 'date', width: 170 },
    { text: 'Method', value: 'method', width: 110 },
    { text: 'Reference', value: 'reference', width: 150 },
    { text: 'Amount', value: 'amount', width: 120 },
    { text: 'Status', value: 'status', width: 170 },
];

const paymentRows = computed(() =>
    [...props.payments].reverse().map((payment) => ({ ...payment, date: '' })),
);

const methodLabel = (method: string) =>
    ({ gcash: 'GCash', grabpay: 'GrabPay' })[method] ??
    method.charAt(0).toUpperCase() + method.slice(1);

const paymentBadge = (status: string): { label: string; tone: StatusTone } =>
    ({
        confirmed: { label: 'Received', tone: 'success' as StatusTone },
        pending: {
            label: 'Waiting for online payment',
            tone: 'warning' as StatusTone,
        },
        voided: { label: 'Voided', tone: 'neutral' as StatusTone },
    })[status] ?? { label: status, tone: 'neutral' as StatusTone };

const balanceDue = computed(() =>
    Math.max(
        0,
        Number(props.shopOrder.total_amount) -
            Number(props.shopOrder.amount_paid),
    ),
);

// ─── Helpers ───────────────────────────────────────

const formatQty = (v: string | number) => {
    const n = Number(v);
    return n % 1 === 0 ? String(n) : n.toFixed(2);
};

const formatCurrency = (v: string | number | null) =>
    v !== null && v !== undefined
        ? `₱${Number(v).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
        : '—';

const formatDate = (d: string | null) =>
    d
        ? new Date(d).toLocaleDateString('en-PH', {
              month: 'short',
              day: 'numeric',
              year: 'numeric',
              hour: '2-digit',
              minute: '2-digit',
          })
        : '—';

const formatLabel = (s: string) =>
    s.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());

const statusStyle = (s: string) =>
    ({
        pending: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
        in_progress: 'bg-blue-50 text-blue-700 ring-1 ring-blue-200',
        completed: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
        picked_up: 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
    })[s] ?? 'bg-gray-50 text-gray-700 ring-1 ring-gray-200';

const paymentStyle = (s: string) =>
    ({
        unpaid: 'bg-red-50 text-red-700 ring-1 ring-red-200',
        partial: 'bg-orange-50 text-orange-700 ring-1 ring-orange-200',
        paid: 'bg-green-50 text-green-700 ring-1 ring-green-200',
    })[s] ?? 'bg-gray-50 text-gray-700 ring-1 ring-gray-200';

const statusIcon = (s: string) =>
    ({
        pending: Clock,
        in_progress: Loader,
        completed: CheckCircle,
        picked_up: PackageCheck,
    })[s] ?? Package;
</script>

<template>
    <Head :title="`Order ${shopOrder.order_number}`" />

    <ShopLayout :title="`Order ${shopOrder.order_number}`">
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
                            >All Orders</Link
                        >
                        <span aria-hidden="true">/</span>
                        <span class="font-medium text-foreground">{{
                            shopOrder.order_number
                        }}</span>
                    </nav>
                    <h2 class="text-2xl font-bold tracking-tight">
                        {{ shopOrder.order_number }}
                    </h2>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                            :class="statusStyle(shopOrder.status)"
                        >
                            <component
                                :is="statusIcon(shopOrder.status)"
                                class="h-3.5 w-3.5"
                            />
                            {{ formatLabel(shopOrder.status) }}
                        </span>
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                            :class="paymentStyle(shopOrder.payment_status)"
                        >
                            <CreditCard class="h-3.5 w-3.5" />
                            {{ formatLabel(shopOrder.payment_status) }}
                        </span>
                        <span class="text-sm text-muted-foreground">{{
                            formatDate(shopOrder.created_at)
                        }}</span>
                    </div>
                </div>
                <div class="flex shrink-0 flex-wrap justify-end gap-2">
                    <Button variant="outline" @click="router.visit(base)">
                        <ArrowLeft class="mr-2 h-4 w-4" /> Back to orders
                    </Button>
                    <Button
                        v-if="canManage"
                        @click="
                            router.visit(
                                `${base}/${shopOrder.payment_public_id}/edit`,
                            )
                        "
                    >
                        <Pencil class="mr-2 h-4 w-4" /> Edit order
                    </Button>
                </div>
            </div>

            <!-- Status progression -->
            <div
                v-if="isCancelled"
                class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700"
                role="status"
            >
                This order is cancelled.
            </div>
            <ol v-else class="flex items-center" aria-label="Order progress">
                <template
                    v-for="(step, index) in statusSteps"
                    :key="step.value"
                >
                    <li class="flex items-center gap-2 py-1 text-sm">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full border-2"
                            :class="stepCircle(index)"
                        >
                            <Check v-if="index < currentStep" class="h-3 w-3" />
                            <span
                                v-else-if="index === currentStep"
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
                    </li>
                    <li
                        v-if="index < statusSteps.length - 1"
                        aria-hidden="true"
                        class="mx-3 h-px max-w-40 flex-1"
                        :class="
                            index < currentStep ? 'bg-blue-500' : 'bg-border'
                        "
                    />
                </template>
            </ol>

            <!-- Delivery -->
            <div
                v-if="
                    deliveryOption.offers_delivery &&
                    (deliveryOption.can_send || deliveryOption.delivery_status)
                "
                class="flex flex-col gap-3 rounded-xl border border-sky-200 bg-sky-50 p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-start gap-3">
                    <Truck class="mt-0.5 h-5 w-5 shrink-0 text-sky-600" />
                    <div>
                        <p class="text-sm font-semibold text-sky-900">
                            {{
                                deliveryOption.delivery_status
                                    ? 'Delivery on the logistics board'
                                    : 'Customer wants it delivered?'
                            }}
                        </p>
                        <p class="text-xs text-sky-800/80">
                            <template v-if="deliveryOption.delivery_status"
                                >Status:
                                {{
                                    formatLabel(deliveryOption.delivery_status)
                                }}. Manage it in Logistics.</template
                            >
                            <template v-else
                                >Create the delivery from this order. The
                                customer's details are filled in and a free
                                rider is assigned.</template
                            >
                        </p>
                    </div>
                </div>
                <Button
                    v-if="deliveryOption.can_send"
                    class="shrink-0"
                    @click="openDeliveryDialog"
                >
                    <Truck class="mr-2 h-4 w-4" /> Send for delivery
                </Button>
            </div>

            <div
                v-if="deliveryDialog"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm"
                @click.self="deliveryDialog = false"
            >
                <div
                    class="w-full max-w-md space-y-4 rounded-2xl border bg-background p-5 shadow-xl"
                >
                    <div>
                        <p class="text-sm font-semibold">Send for delivery</p>
                        <p class="mt-0.5 text-xs text-muted-foreground">
                            {{ shopOrder.customer_name }} ·
                            {{ shopOrder.order_number }}
                        </p>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium"
                            >Delivery address
                            <span
                                v-if="!deliveryOption.has_address"
                                class="text-red-500"
                                >*</span
                            ></label
                        >
                        <Input
                            v-model="deliveryAddress"
                            placeholder="Where should the rider bring it?"
                            class="h-9 text-sm"
                        />
                        <button
                            type="button"
                            class="text-xs font-medium text-blue-600 hover:underline disabled:opacity-60"
                            :disabled="locating"
                            @click="useCurrentLocation"
                        >
                            {{
                                locating
                                    ? 'Finding address…'
                                    : failed
                                      ? 'Could not detect, type the address'
                                      : 'Use current location'
                            }}
                        </button>
                        <p v-if="deliveryError" class="text-xs text-red-500">
                            {{ deliveryError }}
                        </p>
                    </div>
                    <div class="flex justify-end gap-2">
                        <Button
                            variant="outline"
                            @click="deliveryDialog = false"
                            >Cancel</Button
                        >
                        <Button
                            :disabled="sendingDelivery"
                            @click="sendForDelivery"
                        >
                            <Loader2
                                v-if="sendingDelivery"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            {{
                                sendingDelivery
                                    ? 'Sending…'
                                    : 'Send for delivery'
                            }}
                        </Button>
                    </div>
                </div>
            </div>
            <div
                v-if="isAwaitingWeight"
                class="flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-start gap-3">
                    <Clock class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" />
                    <div>
                        <p class="font-semibold text-amber-900">
                            Actual weight required
                        </p>
                        <p class="text-sm text-amber-700">
                            Weigh the laundry and record the scale reading. The
                            system will calculate the final amount and unlock
                            customer payment.
                        </p>
                    </div>
                </div>
                <Button
                    v-if="canManage"
                    class="shrink-0"
                    @click="
                        router.visit(
                            `${base}/${shopOrder.payment_public_id}/edit`,
                        )
                    "
                >
                    Record Actual Weight
                </Button>
            </div>

            <div
                class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_320px]"
            >
                <!-- Details -->
                <div class="space-y-8">
                    <!-- Customer -->
                    <section>
                        <h3 class="text-base font-semibold">Customer</h3>
                        <dl
                            class="mt-4 grid gap-x-8 gap-y-5 text-sm sm:grid-cols-3"
                        >
                            <div>
                                <dt
                                    class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                                >
                                    Name
                                </dt>
                                <dd class="mt-1 text-sm">
                                    {{ shopOrder.customer_name }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                                >
                                    Phone
                                </dt>
                                <dd class="mt-1 text-sm">
                                    {{ shopOrder.customer_phone ?? '—' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                                >
                                    Drop-off type
                                </dt>
                                <dd class="mt-1 text-sm">
                                    {{
                                        shopOrder.pickup_type === 'pickup'
                                            ? 'Rider pickup'
                                            : 'Walk-in'
                                    }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                                >
                                    Pickup type
                                </dt>
                                <dd class="mt-1 text-sm">
                                    {{
                                        shopOrder.deliver_to_customer
                                            ? 'Delivery'
                                            : 'Walk-in'
                                    }}
                                </dd>
                            </div>
                            <div
                                v-if="
                                    shopOrder.alternate_contact_name ||
                                    shopOrder.alternate_contact_phone
                                "
                                class="sm:col-span-3"
                            >
                                <dt
                                    class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                                >
                                    Alternate contact
                                </dt>
                                <dd class="mt-1 text-sm">
                                    {{
                                        shopOrder.alternate_contact_name ?? '—'
                                    }}
                                    <span
                                        v-if="shopOrder.alternate_contact_phone"
                                        class="text-muted-foreground"
                                        >·
                                        {{
                                            shopOrder.alternate_contact_phone
                                        }}</span
                                    >
                                </dd>
                            </div>
                            <div class="sm:col-span-3">
                                <dt
                                    class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                                >
                                    Address
                                </dt>
                                <dd class="mt-1 text-sm">
                                    {{ shopOrder.customer_address ?? '—' }}
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <!-- Service and weight -->
                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">
                            Service and weight
                        </h3>
                        <dl
                            class="mt-4 grid gap-x-8 gap-y-5 text-sm sm:grid-cols-3"
                        >
                            <div>
                                <dt
                                    class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                                >
                                    Service
                                </dt>
                                <dd class="mt-1 text-sm">
                                    {{ shopOrder.service?.service_name ?? '—' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                                >
                                    Pricing model
                                </dt>
                                <dd class="mt-1">
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                                        :class="
                                            shopOrder.pricing_model === 'per_kg'
                                                ? 'bg-blue-50 text-blue-700'
                                                : shopOrder.pricing_model ===
                                                    'per_piece'
                                                  ? 'bg-emerald-50 text-emerald-700'
                                                  : 'bg-violet-50 text-violet-700'
                                        "
                                    >
                                        {{
                                            shopOrder.pricing_model === 'per_kg'
                                                ? 'Per kg'
                                                : shopOrder.pricing_model ===
                                                    'per_piece'
                                                  ? 'Per piece'
                                                  : 'Per bundle'
                                        }}
                                    </span>
                                </dd>
                            </div>

                            <template
                                v-if="shopOrder.pricing_model === 'per_kg'"
                            >
                                <div>
                                    <dt
                                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                                    >
                                        Price per kg
                                    </dt>
                                    <dd class="mt-1 text-sm">
                                        {{
                                            formatCurrency(
                                                shopOrder.price_per_kg,
                                            )
                                        }}
                                    </dd>
                                </div>
                                <div>
                                    <dt
                                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                                    >
                                        Estimated weight
                                    </dt>
                                    <dd class="mt-1 text-sm">
                                        {{
                                            shopOrder.estimated_weight_kg ?? '—'
                                        }}
                                        kg
                                    </dd>
                                </div>
                                <div>
                                    <dt
                                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                                    >
                                        Actual weight
                                    </dt>
                                    <dd class="mt-1 text-sm">
                                        {{
                                            shopOrder.actual_weight_kg ?? '—'
                                        }}
                                        kg
                                    </dd>
                                </div>
                            </template>

                            <template
                                v-else-if="
                                    shopOrder.pricing_model === 'per_piece'
                                "
                            >
                                <div>
                                    <dt
                                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                                    >
                                        Price per piece
                                    </dt>
                                    <dd class="mt-1 text-sm">
                                        {{
                                            formatCurrency(
                                                shopOrder.price_per_piece,
                                            )
                                        }}
                                    </dd>
                                </div>
                                <div>
                                    <dt
                                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                                    >
                                        Number of pieces
                                    </dt>
                                    <dd class="mt-1 text-sm">
                                        {{ shopOrder.piece_count }}
                                    </dd>
                                </div>
                            </template>

                            <template v-else>
                                <div>
                                    <dt
                                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                                    >
                                        Bundle size
                                    </dt>
                                    <dd class="mt-1 text-sm">
                                        {{ shopOrder.bundle_weight_kg }} kg /
                                        bundle
                                    </dd>
                                </div>
                                <div>
                                    <dt
                                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                                    >
                                        Bundle price
                                    </dt>
                                    <dd class="mt-1 text-sm">
                                        {{
                                            formatCurrency(
                                                shopOrder.bundle_price,
                                            )
                                        }}
                                    </dd>
                                </div>
                                <div>
                                    <dt
                                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                                    >
                                        Number of bundles
                                    </dt>
                                    <dd class="mt-1 text-sm">
                                        {{ shopOrder.bundle_quantity }}
                                    </dd>
                                </div>
                            </template>
                        </dl>
                    </section>

                    <!-- Payments -->
                    <section class="border-t pt-8">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-base font-semibold">Payments</h3>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                :class="paymentStyle(shopOrder.payment_status)"
                            >
                                <CreditCard class="h-3.5 w-3.5" />
                                {{ formatLabel(shopOrder.payment_status) }}
                            </span>
                        </div>
                        <p
                            v-if="balanceDue > 0 && !isCancelled"
                            class="mt-1 text-xs text-muted-foreground"
                        >
                            To record a payment, open
                            <Link
                                :href="`${base}/${shopOrder.payment_public_id}/edit`"
                                class="font-medium text-primary hover:underline"
                                >Edit order</Link
                            >.
                        </p>
                        <div class="mt-4">
                            <DataTable
                                :headers="paymentHeaders"
                                :items="paymentRows"
                                noun="payments"
                                :empty-icon="CreditCard"
                                empty-message="No payments recorded yet."
                                :resettable="false"
                                :min-width="640"
                                compact
                                hide-footer
                            >
                                <template #item-date="{ paid_at, created_at }">
                                    <span class="whitespace-nowrap">{{
                                        formatDate(paid_at ?? created_at)
                                    }}</span>
                                </template>
                                <template #item-method="{ method }">
                                    {{ methodLabel(method) }}
                                </template>
                                <template #item-reference="{ reference }">
                                    {{ reference ?? '—' }}
                                </template>
                                <template #item-amount="{ amount, status }">
                                    <span
                                        class="font-medium whitespace-nowrap"
                                        :class="
                                            status === 'voided'
                                                ? 'text-muted-foreground line-through'
                                                : ''
                                        "
                                        >{{ formatCurrency(amount) }}</span
                                    >
                                </template>
                                <template #item-status="{ status }">
                                    <StatusPill
                                        :tone="paymentBadge(status).tone"
                                    >
                                        {{ paymentBadge(status).label }}
                                    </StatusPill>
                                </template>
                            </DataTable>
                        </div>
                    </section>

                    <!-- Supplies -->
                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">Supplies used</h3>
                        <div class="mt-4">
                            <DataTable
                                :headers="supplyHeaders"
                                :items="shopOrder.supplies"
                                noun="supplies"
                                :empty-icon="Package"
                                empty-message="No supplies recorded."
                                :resettable="false"
                                compact
                                hide-footer
                            >
                                <template #item-name="supply">
                                    <span class="font-medium">{{
                                        supply.name
                                    }}</span>
                                </template>
                                <template #item-quantity_used="supply">
                                    {{ formatQty(supply.pivot.quantity_used) }}
                                </template>
                                <template #item-unit="supply">
                                    <span class="text-muted-foreground">{{
                                        supply.pivot.unit
                                    }}</span>
                                </template>
                            </DataTable>
                        </div>
                    </section>

                    <!-- Agreement -->
                    <section v-if="customerAgreement" class="border-t pt-8">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-base font-semibold">
                                    {{ customerAgreement.title }}
                                </h3>
                                <p class="mt-0.5 text-xs text-muted-foreground">
                                    Version {{ customerAgreement.version }}
                                    <template
                                        v-if="customerAgreement.accepted_at"
                                    >
                                        · Accepted by
                                        {{
                                            customerAgreement.customer_name ||
                                            'customer'
                                        }}
                                        on
                                        {{
                                            formatDate(
                                                customerAgreement.accepted_at,
                                            )
                                        }}
                                    </template>
                                </p>
                            </div>
                            <span
                                class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200"
                                >Accepted</span
                            >
                        </div>
                        <details class="mt-4 rounded-lg border">
                            <summary
                                class="cursor-pointer px-3 py-2.5 text-sm font-medium text-primary"
                            >
                                Read the complete agreement
                            </summary>
                            <div
                                class="max-h-72 overflow-y-auto border-t px-3 py-3 text-xs leading-5 whitespace-pre-wrap text-muted-foreground"
                            >
                                {{ customerAgreement.content }}
                            </div>
                        </details>
                    </section>
                </div>

                <!-- Summary -->
                <aside class="rounded-xl border p-5 lg:sticky lg:top-4">
                    <p
                        class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        Pricing summary
                    </p>
                    <div class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <span class="text-muted-foreground">Subtotal</span>
                            <span>
                                <template
                                    v-if="
                                        shopOrder.pricing_model === 'per_kg' &&
                                        shopOrder.actual_weight_kg
                                    "
                                >
                                    {{ shopOrder.actual_weight_kg }} kg ×
                                    {{ formatCurrency(shopOrder.price_per_kg) }}
                                </template>
                                <template
                                    v-else-if="
                                        shopOrder.pricing_model === 'per_piece'
                                    "
                                >
                                    {{ shopOrder.piece_count }} ×
                                    {{
                                        formatCurrency(
                                            shopOrder.price_per_piece,
                                        )
                                    }}
                                </template>
                                <template v-else-if="isAwaitingWeight">
                                    Awaiting scale reading
                                </template>
                                <template v-else>
                                    {{ shopOrder.bundle_quantity }} ×
                                    {{ formatCurrency(shopOrder.bundle_price) }}
                                </template>
                            </span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-muted-foreground"
                                >Additional charges</span
                            >
                            <span>{{
                                formatCurrency(shopOrder.additional_charges)
                            }}</span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-muted-foreground">Discount</span>
                            <span class="text-emerald-600"
                                >−
                                {{
                                    formatCurrency(shopOrder.discount_amount)
                                }}</span
                            >
                        </div>
                    </div>
                    <div
                        class="mt-4 flex items-baseline justify-between gap-3 border-t pt-4"
                    >
                        <span class="text-sm font-semibold">Total</span>
                        <span class="text-2xl font-bold">{{
                            formatCurrency(shopOrder.total_amount)
                        }}</span>
                    </div>
                    <div class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <span class="text-muted-foreground"
                                >Amount paid</span
                            >
                            <span class="font-semibold text-emerald-600">{{
                                formatCurrency(shopOrder.amount_paid)
                            }}</span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-muted-foreground">Balance</span>
                            <span
                                class="font-semibold"
                                :class="balanceDue > 0 ? 'text-orange-600' : ''"
                                >{{ formatCurrency(balanceDue) }}</span
                            >
                        </div>
                    </div>
                    <div class="mt-4 border-t pt-4">
                        <p
                            class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                        >
                            Payment method
                        </p>
                        <p class="mt-1 text-sm">
                            {{
                                shopOrder.payment_method
                                    ? methodLabel(shopOrder.payment_method)
                                    : 'Not set'
                            }}
                        </p>
                    </div>
                    <div class="mt-4 space-y-3 border-t pt-4 text-sm">
                        <div>
                            <p
                                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                Order created
                            </p>
                            <p class="mt-1 text-sm">
                                {{ formatDate(shopOrder.created_at) }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                Est. completion
                            </p>
                            <p class="mt-1 text-sm">
                                {{
                                    formatDate(
                                        shopOrder.estimated_completion_at,
                                    )
                                }}
                            </p>
                        </div>
                        <div v-if="shopOrder.completed_at">
                            <p
                                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                Completed at
                            </p>
                            <p class="mt-1 text-sm text-emerald-600">
                                {{ formatDate(shopOrder.completed_at) }}
                            </p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </ShopLayout>
</template>
