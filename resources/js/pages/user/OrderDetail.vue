<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import UserLayout from '@/layouts/user/UserLayout.vue'
import {
    ArrowLeft, MapPin, Phone, WashingMachine, CreditCard,
    Package, Clock, CheckCircle2, Loader, Info, Truck, X, AlertTriangle,
} from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface Shop {
    name: string
    phone: string | null
    municipality: string
    barangay: string
    block_street: string | null
}

interface Delivery {
    id: number
    status: 'pending' | 'assigned' | 'picked_up' | 'delivered' | 'failed'
    delivery_address: string | null
    rider_name: string | null
    picked_up_at: string | null
    delivered_at: string | null
}

interface Order {
    id: number
    order_number: string
    status: 'pending' | 'in_progress' | 'completed' | 'cancelled'
    payment_status: 'unpaid' | 'partial' | 'paid'
    payment_method: string
    total_amount: number
    amount_paid: number
    estimated_weight_kg: number
    actual_weight_kg: number | null
    pickup_type: string
    customer_address: string | null
    special_instructions: string | null
    pricing_model: string
    price_per_kg: string | null
    created_at: string
    completed_at: string | null
    shop: Shop | null
    service_name: string
    delivery: Delivery | null
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{ order: Order }>()

// ─── Helpers ──────────────────────────────────────────────────────────────────

const statusSteps = ['pending', 'in_progress', 'completed']
const currentStep = computed(() => statusSteps.indexOf(props.order.status))

const statusConfig = {
    pending:     { label: 'Pending',     icon: Clock,          classes: 'text-yellow-600 bg-yellow-50' },
    in_progress: { label: 'In Progress', icon: Loader,         classes: 'text-blue-600 bg-blue-50' },
    completed:   { label: 'Completed',   icon: CheckCircle2,   classes: 'text-green-600 bg-green-50' },
    cancelled:   { label: 'Cancelled',   icon: X,              classes: 'text-red-600 bg-red-50' },
}

// ─── Cancel order ─────────────────────────────────────────────────────────────

const showCancelConfirm = ref(false)
const cancelling = ref(false)

function confirmCancel() {
    cancelling.value = true
    router.post(`/user/orders/${props.order.id}/cancel`, {}, {
        preserveScroll: true,
        onFinish: () => { cancelling.value = false },
    })
}

const paymentConfig = {
    unpaid:  { label: 'Unpaid',  classes: 'bg-red-50 text-red-600' },
    partial: { label: 'Partial', classes: 'bg-orange-50 text-orange-600' },
    paid:    { label: 'Paid',    classes: 'bg-emerald-50 text-emerald-700' },
}

function currency(val: number) {
    return `₱${val.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
}

function formatAddress(shop: Shop) {
    return [shop.block_street, shop.barangay, shop.municipality].filter(Boolean).join(', ')
}

const canRequestDelivery = computed(() =>
    props.order.status === 'completed' &&
    props.order.payment_status === 'paid' &&
    !props.order.delivery
)

const showDeliveryForm = ref(false)
const deliveryForm = useForm({ delivery_address: props.order.customer_address ?? '' })

function submitDelivery() {
    deliveryForm.post(`/user/orders/${props.order.id}/request-delivery`, {
        preserveScroll: true,
        onSuccess: () => { showDeliveryForm.value = false },
    })
}

const deliveryStatusConfig = {
    pending:   { label: 'Pending',    classes: 'bg-amber-50 text-amber-700',   icon: Clock },
    assigned:  { label: 'Assigned',   classes: 'bg-blue-50 text-blue-700',     icon: Truck },
    picked_up: { label: 'Picked Up',  classes: 'bg-indigo-50 text-indigo-700', icon: Truck },
    delivered: { label: 'Delivered',  classes: 'bg-emerald-50 text-emerald-700', icon: CheckCircle2 },
    failed:    { label: 'Failed',     classes: 'bg-red-50 text-red-700',       icon: X },
}
</script>

<template>
    <Head :title="`Order ${order.order_number}`" />
    <UserLayout>

        <!-- Back -->
        <div class="px-4 pt-4">
            <button
                class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-4"
                @click="router.visit('/user/orders')"
            >
                <ArrowLeft class="h-4 w-4" />
                Back to orders
            </button>
        </div>

        <div class="px-4 pb-8 space-y-4">

            <!-- ── Status progress bar ────────────────────────── -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Order Status</p>

                <!-- Cancelled banner -->
                <div v-if="order.status === 'cancelled'"
                    class="flex items-center gap-2 bg-red-50 border border-red-100 rounded-xl px-4 py-3">
                    <X class="h-4 w-4 text-red-500 shrink-0" />
                    <p class="text-sm font-semibold text-red-600">This order has been cancelled.</p>
                </div>

                <!-- Normal progress steps -->
                <div v-else class="flex items-center gap-0">
                    <template v-for="(step, idx) in statusSteps" :key="step">
                        <div class="flex flex-col items-center gap-1">
                            <div
                                class="h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-all"
                                :class="idx <= currentStep
                                    ? 'bg-blue-600 border-blue-600 text-white'
                                    : 'bg-white border-gray-200 text-gray-400'"
                            >
                                <CheckCircle2 v-if="idx < currentStep" class="h-4 w-4" />
                                <span v-else>{{ idx + 1 }}</span>
                            </div>
                            <span
                                class="text-[10px] font-medium whitespace-nowrap"
                                :class="idx <= currentStep ? 'text-blue-600' : 'text-gray-400'"
                            >
                                {{ statusConfig[step as keyof typeof statusConfig]?.label }}
                            </span>
                        </div>
                        <div
                            v-if="idx < statusSteps.length - 1"
                            class="flex-1 h-0.5 mb-4 mx-1 transition-all"
                            :class="idx < currentStep ? 'bg-blue-600' : 'bg-gray-200'"
                        />
                    </template>
                </div>
            </div>

            <!-- ── Order info ─────────────────────────────────── -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Order Info</p>
                    <span class="text-xs font-mono text-gray-500">{{ order.order_number }}</span>
                </div>

                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                        <WashingMachine class="h-5 w-5 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ order.service_name }}</p>
                        <p class="text-xs text-gray-500">{{ order.shop?.name ?? '—' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-gray-400 mb-0.5">Est. weight</p>
                        <p class="font-semibold text-gray-700">{{ order.estimated_weight_kg }} kg</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-gray-400 mb-0.5">Actual weight</p>
                        <p class="font-semibold text-gray-700">{{ order.actual_weight_kg ? `${order.actual_weight_kg} kg` : '—' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-gray-400 mb-0.5">Drop-off</p>
                        <p class="font-semibold text-gray-700 capitalize">{{ order.pickup_type.replace('_', '-') }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-gray-400 mb-0.5">Placed on</p>
                        <p class="font-semibold text-gray-700">{{ order.created_at }}</p>
                    </div>
                </div>

                <div v-if="order.special_instructions" class="flex items-start gap-2 bg-amber-50 rounded-xl p-3 text-xs text-amber-700">
                    <Info class="h-3.5 w-3.5 shrink-0 mt-0.5" />
                    <span>{{ order.special_instructions }}</span>
                </div>
            </div>

            <!-- ── Payment ────────────────────────────────────── -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Payment</p>

                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <CreditCard class="h-4 w-4 text-gray-400" />
                        <span class="text-sm text-gray-600 capitalize">{{ order.payment_method }}</span>
                    </div>
                    <span
                        class="text-xs font-semibold px-2.5 py-1 rounded-full"
                        :class="paymentConfig[order.payment_status]?.classes ?? 'bg-gray-100 text-gray-600'"
                    >
                        {{ paymentConfig[order.payment_status]?.label }}
                    </span>
                </div>

                <div class="flex items-center justify-between py-3 border-t border-gray-100">
                    <span class="text-sm text-gray-500">Total amount</span>
                    <span class="text-base font-bold text-gray-800">{{ currency(order.total_amount) }}</span>
                </div>
                <div v-if="order.amount_paid > 0" class="flex items-center justify-between text-sm text-gray-500">
                    <span>Amount paid</span>
                    <span class="font-semibold text-emerald-600">{{ currency(order.amount_paid) }}</span>
                </div>

                <div class="mt-3 bg-amber-50 rounded-xl p-3 text-xs text-amber-700 flex items-start gap-2">
                    <Info class="h-3.5 w-3.5 shrink-0 mt-0.5" />
                    Total is finalised by the shop after weighing. Payment is made at the shop.
                </div>
            </div>

            <!-- ── Shop info ──────────────────────────────────── -->
            <div v-if="order.shop" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-2.5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Shop</p>
                <p class="text-sm font-semibold text-gray-800">{{ order.shop.name }}</p>
                <div class="flex items-start gap-2 text-xs text-gray-500">
                    <MapPin class="h-3.5 w-3.5 shrink-0 mt-0.5 text-gray-400" />
                    <span>{{ formatAddress(order.shop) }}</span>
                </div>
                <div v-if="order.shop.phone" class="flex items-center gap-2 text-xs text-gray-500">
                    <Phone class="h-3.5 w-3.5 shrink-0 text-gray-400" />
                    <a :href="`tel:${order.shop.phone}`" class="hover:text-blue-600">{{ order.shop.phone }}</a>
                </div>
            </div>

            <!-- ── Delivery ───────────────────────────────────── -->

            <!-- Existing delivery status -->
            <div v-if="order.delivery" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Delivery</p>

                <div class="flex items-center gap-2">
                    <component
                        :is="deliveryStatusConfig[order.delivery.status]?.icon ?? Truck"
                        class="h-4 w-4 shrink-0"
                        :class="order.delivery.status === 'delivered' ? 'text-emerald-600' : 'text-blue-600'"
                    />
                    <span
                        class="text-xs font-semibold px-2.5 py-1 rounded-full"
                        :class="deliveryStatusConfig[order.delivery.status]?.classes ?? 'bg-gray-50 text-gray-600'"
                    >
                        {{ deliveryStatusConfig[order.delivery.status]?.label ?? order.delivery.status }}
                    </span>
                </div>

                <div v-if="order.delivery.delivery_address" class="flex items-start gap-2 text-xs text-gray-500">
                    <MapPin class="h-3.5 w-3.5 shrink-0 mt-0.5 text-gray-400" />
                    <span>{{ order.delivery.delivery_address }}</span>
                </div>
                <div v-if="order.delivery.rider_name" class="flex items-center gap-2 text-xs text-gray-500">
                    <Truck class="h-3.5 w-3.5 shrink-0 text-gray-400" />
                    <span>Rider: <span class="font-medium text-gray-700">{{ order.delivery.rider_name }}</span></span>
                </div>
                <div v-if="order.delivery.picked_up_at" class="text-xs text-gray-400">
                    Picked up: {{ order.delivery.picked_up_at }}
                </div>
                <div v-if="order.delivery.delivered_at" class="text-xs text-emerald-600 font-medium">
                    Delivered: {{ order.delivery.delivered_at }}
                </div>
            </div>

            <!-- Request delivery button (completed + paid, no delivery yet) -->
            <div v-else-if="canRequestDelivery" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Delivery</p>

                <div v-if="!showDeliveryForm">
                    <p class="text-sm text-gray-600 mb-3">
                        Your laundry is ready. Want it delivered to you?
                    </p>
                    <button
                        class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-3 rounded-xl transition"
                        @click="showDeliveryForm = true"
                    >
                        <Truck class="h-4 w-4" />
                        Request Delivery
                    </button>
                </div>

                <form v-else @submit.prevent="submitDelivery" class="space-y-3">
                    <div>
                        <label class="text-xs font-medium text-gray-600 block mb-1">Delivery Address <span class="text-red-500">*</span></label>
                        <textarea
                            v-model="deliveryForm.delivery_address"
                            rows="3"
                            placeholder="Enter your full delivery address"
                            class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none"
                            :class="deliveryForm.errors.delivery_address ? 'border-red-400' : ''"
                        />
                        <p v-if="deliveryForm.errors.delivery_address" class="text-xs text-red-500 mt-1">
                            {{ deliveryForm.errors.delivery_address }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition"
                            @click="showDeliveryForm = false"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="deliveryForm.processing"
                            class="flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-semibold py-2.5 rounded-xl transition"
                        >
                            <Loader v-if="deliveryForm.processing" class="h-4 w-4 animate-spin" />
                            <Truck v-else class="h-4 w-4" />
                            {{ deliveryForm.processing ? 'Requesting…' : 'Confirm Request' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- ── Cancel order ───────────────────────────────── -->
            <div v-if="order.status === 'pending'" class="bg-white rounded-2xl border border-red-100 shadow-sm p-4 space-y-3">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Cancel Order</p>

                <!-- Default state -->
                <div v-if="!showCancelConfirm">
                    <p class="text-sm text-gray-500 mb-3">
                        Changed your mind? You can cancel this order while it's still pending.
                    </p>
                    <button
                        class="w-full flex items-center justify-center gap-2 border border-red-200 text-red-600 hover:bg-red-50 text-sm font-semibold py-2.5 rounded-xl transition"
                        @click="showCancelConfirm = true"
                    >
                        <X class="h-4 w-4" />
                        Cancel Order
                    </button>
                </div>

                <!-- Confirmation state -->
                <div v-else class="space-y-3">
                    <div class="flex items-start gap-2 bg-red-50 rounded-xl px-3 py-3 text-xs text-red-700">
                        <AlertTriangle class="h-3.5 w-3.5 shrink-0 mt-0.5" />
                        <span>Are you sure you want to cancel order <span class="font-semibold">{{ order.order_number }}</span>? This cannot be undone.</span>
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition"
                            :disabled="cancelling"
                            @click="showCancelConfirm = false"
                        >
                            Go Back
                        </button>
                        <button
                            type="button"
                            :disabled="cancelling"
                            class="flex-1 flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white text-sm font-semibold py-2.5 rounded-xl transition"
                            @click="confirmCancel"
                        >
                            <Loader v-if="cancelling" class="h-4 w-4 animate-spin" />
                            <X v-else class="h-4 w-4" />
                            {{ cancelling ? 'Cancelling…' : 'Yes, Cancel' }}
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </UserLayout>
</template>
