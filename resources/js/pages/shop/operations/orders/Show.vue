<script setup lang="ts">
import CustomerServiceAgreement from '@/components/customer/CustomerServiceAgreement.vue';
import { DataTable, type DataTableHeader } from '@/components/data-table';
import OrderPaymentsCard, {
    type OrderPayment,
} from '@/components/shop/OrderPaymentsCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { usePickupAddressAutofill } from '@/composables/usePickupAddressAutofill';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle,
    Clock,
    CreditCard,
    Loader,
    Loader2,
    Package,
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
    customer_address: string | null;
    status: string;
    payment_status: string;
    payment_method: string;
    pricing_model: 'per_kg' | 'per_bundle';
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

const isAwaitingWeight = computed(
    () => props.shopOrder.actual_weight_kg === null,
);

const page = usePage<AppPageProps>();
const isOwner = computed(() => page.props.auth.user.role === 'owner');
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
    })[s] ?? Package;
</script>

<template>
    <Head :title="`Order ${shopOrder.order_number}`" />

    <ShopLayout :title="`Order ${shopOrder.order_number}`">
        <div class="space-y-6 px-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <Button variant="outline" @click="router.visit(base)">
                    <ArrowLeft class="mr-2 h-4 w-4" /> Back to Orders
                </Button>
                <Button
                    @click="
                        router.visit(
                            `${base}/${shopOrder.payment_public_id}/edit`,
                        )
                    "
                >
                    <Pencil class="mr-2 h-4 w-4" /> Edit Order
                </Button>
            </div>

            <!-- Status Bar -->
            <div class="flex flex-wrap items-center gap-3">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-semibold"
                    :class="statusStyle(shopOrder.status)"
                >
                    <component
                        :is="statusIcon(shopOrder.status)"
                        class="h-4 w-4"
                    />
                    {{ formatLabel(shopOrder.status) }}
                </span>
                <span
                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-semibold"
                    :class="paymentStyle(shopOrder.payment_status)"
                >
                    <CreditCard class="h-4 w-4" />
                    {{ formatLabel(shopOrder.payment_status) }}
                </span>
                <span class="text-sm text-muted-foreground">{{
                    formatDate(shopOrder.created_at)
                }}</span>
            </div>

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

            <div class="grid grid-cols-12 gap-6">
                <!-- Left Column -->
                <div class="col-span-12 space-y-6 lg:col-span-8">
                    <!-- Customer -->
                    <Card>
                        <CardHeader
                            ><CardTitle
                                >Customer Information</CardTitle
                            ></CardHeader
                        >
                        <CardContent>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p
                                        class="mb-1 text-xs tracking-wide text-muted-foreground uppercase"
                                    >
                                        Name
                                    </p>
                                    <p class="font-medium">
                                        {{ shopOrder.customer_name }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="mb-1 text-xs tracking-wide text-muted-foreground uppercase"
                                    >
                                        Phone
                                    </p>
                                    <p class="font-medium">
                                        {{ shopOrder.customer_phone ?? '—' }}
                                    </p>
                                </div>
                                <div class="col-span-2">
                                    <p
                                        class="mb-1 text-xs tracking-wide text-muted-foreground uppercase"
                                    >
                                        Address
                                    </p>
                                    <p class="font-medium">
                                        {{ shopOrder.customer_address ?? '—' }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="mb-1 text-xs tracking-wide text-muted-foreground uppercase"
                                    >
                                        Pickup Type
                                    </p>
                                    <p class="font-medium">
                                        {{ formatLabel(shopOrder.pickup_type) }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Service & Weight -->
                    <Card>
                        <CardHeader
                            ><CardTitle>Service & Weight</CardTitle></CardHeader
                        >
                        <CardContent>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p
                                        class="mb-1 text-xs tracking-wide text-muted-foreground uppercase"
                                    >
                                        Service
                                    </p>
                                    <p class="font-medium">
                                        {{
                                            shopOrder.service?.service_name ??
                                            '—'
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="mb-1 text-xs tracking-wide text-muted-foreground uppercase"
                                    >
                                        Pricing Model
                                    </p>
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                                        :class="
                                            shopOrder.pricing_model === 'per_kg'
                                                ? 'bg-blue-50 text-blue-700'
                                                : 'bg-violet-50 text-violet-700'
                                        "
                                    >
                                        {{
                                            shopOrder.pricing_model === 'per_kg'
                                                ? 'Per KG'
                                                : 'Per Bundle'
                                        }}
                                    </span>
                                </div>

                                <!-- Per KG -->
                                <template
                                    v-if="shopOrder.pricing_model === 'per_kg'"
                                >
                                    <div>
                                        <p
                                            class="mb-1 text-xs tracking-wide text-muted-foreground uppercase"
                                        >
                                            Estimated Weight
                                        </p>
                                        <p class="font-medium">
                                            {{
                                                shopOrder.estimated_weight_kg ??
                                                '—'
                                            }}
                                            kg
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="mb-1 text-xs tracking-wide text-muted-foreground uppercase"
                                        >
                                            Actual Weight
                                        </p>
                                        <p class="font-medium">
                                            {{
                                                shopOrder.actual_weight_kg ??
                                                '—'
                                            }}
                                            kg
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="mb-1 text-xs tracking-wide text-muted-foreground uppercase"
                                        >
                                            Price per KG
                                        </p>
                                        <p class="font-medium">
                                            {{
                                                formatCurrency(
                                                    shopOrder.price_per_kg,
                                                )
                                            }}
                                        </p>
                                    </div>
                                </template>

                                <!-- Per Bundle -->
                                <template v-else>
                                    <div>
                                        <p
                                            class="mb-1 text-xs tracking-wide text-muted-foreground uppercase"
                                        >
                                            Bundle Size
                                        </p>
                                        <p class="font-medium">
                                            {{ shopOrder.bundle_weight_kg }} kg
                                            / bundle
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="mb-1 text-xs tracking-wide text-muted-foreground uppercase"
                                        >
                                            Bundle Price
                                        </p>
                                        <p class="font-medium">
                                            {{
                                                formatCurrency(
                                                    shopOrder.bundle_price,
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="mb-1 text-xs tracking-wide text-muted-foreground uppercase"
                                        >
                                            No. of Bundles
                                        </p>
                                        <p class="font-medium">
                                            {{ shopOrder.bundle_quantity }}
                                        </p>
                                    </div>
                                </template>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Supplies -->
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
                        <template #toolbar>
                            <h2 class="font-semibold">Supplies Used</h2>
                        </template>

                        <template #item-name="supply">
                            <span class="font-medium">{{ supply.name }}</span>
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

                <!-- Right Column -->
                <div class="col-span-12 space-y-6 lg:col-span-4">
                    <!-- Pricing Summary -->
                    <Card>
                        <CardHeader
                            ><CardTitle>Pricing Summary</CardTitle></CardHeader
                        >
                        <CardContent class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground"
                                    >Subtotal</span
                                >
                                <span>
                                    <template
                                        v-if="
                                            shopOrder.pricing_model ===
                                                'per_kg' &&
                                            shopOrder.actual_weight_kg
                                        "
                                    >
                                        {{ shopOrder.actual_weight_kg }}kg ×
                                        {{
                                            formatCurrency(
                                                shopOrder.price_per_kg,
                                            )
                                        }}
                                    </template>
                                    <template v-else-if="isAwaitingWeight">
                                        Awaiting scale reading
                                    </template>
                                    <template v-else>
                                        {{ shopOrder.bundle_quantity }} ×
                                        {{
                                            formatCurrency(
                                                shopOrder.bundle_price,
                                            )
                                        }}
                                    </template>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground"
                                    >Additional Charges</span
                                >
                                <span>{{
                                    formatCurrency(shopOrder.additional_charges)
                                }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground"
                                    >Discount</span
                                >
                                <span class="text-red-600"
                                    >-{{
                                        formatCurrency(
                                            shopOrder.discount_amount,
                                        )
                                    }}</span
                                >
                            </div>
                            <div
                                class="flex justify-between border-t pt-3 text-base font-bold"
                            >
                                <span>Total</span>
                                <span>{{
                                    formatCurrency(shopOrder.total_amount)
                                }}</span>
                            </div>
                            <div class="flex justify-between text-emerald-600">
                                <span>Amount Paid</span>
                                <span class="font-semibold">{{
                                    formatCurrency(shopOrder.amount_paid)
                                }}</span>
                            </div>
                            <div class="flex justify-between text-orange-600">
                                <span>Balance</span>
                                <span class="font-semibold">
                                    {{
                                        formatCurrency(
                                            Number(shopOrder.total_amount) -
                                                Number(shopOrder.amount_paid),
                                        )
                                    }}
                                </span>
                            </div>
                            <div class="pt-1">
                                <p
                                    class="mb-1 text-xs tracking-wide text-muted-foreground uppercase"
                                >
                                    Payment Method
                                </p>
                                <p class="font-medium">
                                    {{ shopOrder.payment_method.toUpperCase() }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <OrderPaymentsCard
                        :order="shopOrder"
                        :payments="payments"
                        :base="base"
                    />

                    <CustomerServiceAgreement
                        v-if="customerAgreement"
                        :agreement="customerAgreement"
                        readonly
                    />

                    <!-- Dates -->
                    <Card>
                        <CardHeader><CardTitle>Timeline</CardTitle></CardHeader>
                        <CardContent class="space-y-3 text-sm">
                            <div>
                                <p
                                    class="mb-1 text-xs tracking-wide text-muted-foreground uppercase"
                                >
                                    Order Created
                                </p>
                                <p class="font-medium">
                                    {{ formatDate(shopOrder.created_at) }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="mb-1 text-xs tracking-wide text-muted-foreground uppercase"
                                >
                                    Est. Completion
                                </p>
                                <p class="font-medium">
                                    {{
                                        formatDate(
                                            shopOrder.estimated_completion_at,
                                        )
                                    }}
                                </p>
                            </div>
                            <div v-if="shopOrder.completed_at">
                                <p
                                    class="mb-1 text-xs tracking-wide text-muted-foreground uppercase"
                                >
                                    Completed At
                                </p>
                                <p class="font-medium text-emerald-600">
                                    {{ formatDate(shopOrder.completed_at) }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>
