<script setup lang="ts">
import { computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import UserLayout from '@/layouts/user/UserLayout.vue'
import {
    ArrowLeft, MapPin, Phone, WashingMachine, CreditCard,
    Package, Clock, CheckCircle2, Loader, Info,
} from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface Shop {
    name: string
    phone: string | null
    municipality: string
    barangay: string
    block_street: string | null
}

interface Order {
    id: number
    order_number: string
    status: 'pending' | 'in_progress' | 'completed'
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
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{ order: Order }>()

// ─── Helpers ──────────────────────────────────────────────────────────────────

const statusSteps = ['pending', 'in_progress', 'completed']
const currentStep = computed(() => statusSteps.indexOf(props.order.status))

const statusConfig = {
    pending:     { label: 'Pending',     icon: Clock,         classes: 'text-yellow-600 bg-yellow-50' },
    in_progress: { label: 'In Progress', icon: Loader,        classes: 'text-blue-600 bg-blue-50' },
    completed:   { label: 'Completed',   icon: CheckCircle2,  classes: 'text-green-600 bg-green-50' },
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
                <div class="flex items-center gap-0">
                    <template v-for="(step, idx) in statusSteps" :key="step">
                        <!-- Step circle -->
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
                        <!-- Connector line -->
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

        </div>
    </UserLayout>
</template>
