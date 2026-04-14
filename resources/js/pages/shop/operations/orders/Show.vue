<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import { type AppPageProps } from '@/types'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { ArrowLeft, Pencil, Clock, CheckCircle, Loader, Package, CreditCard, Send, Loader2 } from 'lucide-vue-next'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

interface Supply {
    id: number
    name: string
    pivot: {
        quantity_used: number
        unit: string
    }
}

interface Order {
    id: number
    order_number: string
    customer_name: string
    customer_phone: string | null
    customer_address: string | null
    status: string
    payment_status: string
    payment_method: string
    pricing_model: 'per_kg' | 'per_bundle'
    estimated_weight_kg: string | null
    actual_weight_kg: string | null
    price_per_kg: string | null
    bundle_weight_kg: string | null
    bundle_price: string | null
    bundle_quantity: number | null
    additional_charges: string
    discount_amount: string
    total_amount: string
    amount_paid: string
    pickup_type: string
    estimated_completion_at: string | null
    completed_at: string | null
    created_at: string
    service?: { service_name: string }
    supplies: Supply[]
}

const props = defineProps<{
    shopOrder: Order
    gcash_qr: string | null
    maya_qr:  string | null
}>()

const sending = ref(false)

const activeQr = computed(() => {
    if (props.shopOrder.payment_method === 'gcash') return props.gcash_qr
    if (props.shopOrder.payment_method === 'maya')  return props.maya_qr
    return null
})

const isDigitalPayment = computed(() =>
    ['gcash', 'maya'].includes(props.shopOrder.payment_method)
)

function sendPaymentRequest() {
    sending.value = true
    router.post(`${base.value}/${props.shopOrder.id}/payment-request`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`Payment request sent to ${props.shopOrder.customer_name} successfully.`)
        },
        onError: () => {
            toast.error('Failed to send payment request. Please try again.')
        },
        onFinish: () => { sending.value = false },
    })
}

const page = usePage<AppPageProps>()
const isOwner = computed(() => page.props.auth.user.role === 'owner')
const base = computed(() => isOwner.value ? '/shop/operations/orders' : '/staff/operations/orders')

onMounted(() => {
    const t = page.props.toast as any
    if (!t) return
    if (t.type === 'success') toast.success(t.message)
    if (t.type === 'error')   toast.error(t.message)
})

// ─── Helpers ───────────────────────────────────────

const formatQty = (v: string | number) => {
    const n = Number(v)
    return n % 1 === 0 ? String(n) : n.toFixed(2)
}

const formatCurrency = (v: string | number | null) =>
    v !== null && v !== undefined
        ? `₱${Number(v).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
        : '—'

const formatDate = (d: string | null) =>
    d ? new Date(d).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—'

const formatLabel = (s: string) =>
    s.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())

const statusStyle = (s: string) => ({
    pending:     'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
    in_progress: 'bg-blue-50 text-blue-700 ring-1 ring-blue-200',
    completed:   'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
}[s] ?? 'bg-gray-50 text-gray-700 ring-1 ring-gray-200')

const paymentStyle = (s: string) => ({
    unpaid:  'bg-red-50 text-red-700 ring-1 ring-red-200',
    partial: 'bg-orange-50 text-orange-700 ring-1 ring-orange-200',
    paid:    'bg-green-50 text-green-700 ring-1 ring-green-200',
}[s] ?? 'bg-gray-50 text-gray-700 ring-1 ring-gray-200')

const statusIcon = (s: string) => ({
    pending:     Clock,
    in_progress: Loader,
    completed:   CheckCircle,
}[s] ?? Package)
</script>

<template>
    <Head :title="`Order ${shopOrder.order_number}`" />

    <ShopLayout :title="`Order ${shopOrder.order_number}`">
        <div class="px-6 space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <Button variant="outline" @click="router.visit(base)">
                    <ArrowLeft class="h-4 w-4 mr-2" /> Back to Orders
                </Button>
                <Button @click="router.visit(`${base}/${shopOrder.id}/edit`)">
                    <Pencil class="h-4 w-4 mr-2" /> Edit Order
                </Button>
            </div>

            <!-- Status Bar -->
            <div class="flex flex-wrap items-center gap-3">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-semibold"
                    :class="statusStyle(shopOrder.status)"
                >
                    <component :is="statusIcon(shopOrder.status)" class="h-4 w-4" />
                    {{ formatLabel(shopOrder.status) }}
                </span>
                <span
                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-semibold"
                    :class="paymentStyle(shopOrder.payment_status)"
                >
                    <CreditCard class="h-4 w-4" />
                    {{ formatLabel(shopOrder.payment_status) }}
                </span>
                <span class="text-sm text-muted-foreground">{{ formatDate(shopOrder.created_at) }}</span>
            </div>

            <div class="grid grid-cols-12 gap-6">

                <!-- Left Column -->
                <div class="col-span-12 lg:col-span-8 space-y-6">

                    <!-- Customer -->
                    <Card>
                        <CardHeader><CardTitle>Customer Information</CardTitle></CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-xs text-muted-foreground uppercase tracking-wide mb-1">Name</p>
                                    <p class="font-medium">{{ shopOrder.customer_name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-muted-foreground uppercase tracking-wide mb-1">Phone</p>
                                    <p class="font-medium">{{ shopOrder.customer_phone ?? '—' }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-xs text-muted-foreground uppercase tracking-wide mb-1">Address</p>
                                    <p class="font-medium">{{ shopOrder.customer_address ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-muted-foreground uppercase tracking-wide mb-1">Pickup Type</p>
                                    <p class="font-medium">{{ formatLabel(shopOrder.pickup_type) }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Service & Weight -->
                    <Card>
                        <CardHeader><CardTitle>Service & Weight</CardTitle></CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-xs text-muted-foreground uppercase tracking-wide mb-1">Service</p>
                                    <p class="font-medium">{{ shopOrder.service?.service_name ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-muted-foreground uppercase tracking-wide mb-1">Pricing Model</p>
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                                        :class="shopOrder.pricing_model === 'per_kg'
                                            ? 'bg-blue-50 text-blue-700'
                                            : 'bg-violet-50 text-violet-700'"
                                    >
                                        {{ shopOrder.pricing_model === 'per_kg' ? 'Per KG' : 'Per Bundle' }}
                                    </span>
                                </div>

                                <!-- Per KG -->
                                <template v-if="shopOrder.pricing_model === 'per_kg'">
                                    <div>
                                        <p class="text-xs text-muted-foreground uppercase tracking-wide mb-1">Estimated Weight</p>
                                        <p class="font-medium">{{ shopOrder.estimated_weight_kg ?? '—' }} kg</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-muted-foreground uppercase tracking-wide mb-1">Actual Weight</p>
                                        <p class="font-medium">{{ shopOrder.actual_weight_kg ?? '—' }} kg</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-muted-foreground uppercase tracking-wide mb-1">Price per KG</p>
                                        <p class="font-medium">{{ formatCurrency(shopOrder.price_per_kg) }}</p>
                                    </div>
                                </template>

                                <!-- Per Bundle -->
                                <template v-else>
                                    <div>
                                        <p class="text-xs text-muted-foreground uppercase tracking-wide mb-1">Bundle Size</p>
                                        <p class="font-medium">{{ shopOrder.bundle_weight_kg }} kg / bundle</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-muted-foreground uppercase tracking-wide mb-1">Bundle Price</p>
                                        <p class="font-medium">{{ formatCurrency(shopOrder.bundle_price) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-muted-foreground uppercase tracking-wide mb-1">No. of Bundles</p>
                                        <p class="font-medium">{{ shopOrder.bundle_quantity }}</p>
                                    </div>
                                </template>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Supplies -->
                    <Card>
                        <CardHeader><CardTitle>Supplies Used</CardTitle></CardHeader>
                        <CardContent>
                            <div v-if="shopOrder.supplies.length === 0" class="text-sm text-muted-foreground italic">
                                No supplies recorded.
                            </div>
                            <div v-else class="border rounded-lg overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead class="bg-muted/40 text-xs uppercase tracking-wide">
                                        <tr>
                                            <th class="px-4 py-2 text-left">Item</th>
                                            <th class="px-4 py-2 text-left">Qty Used</th>
                                            <th class="px-4 py-2 text-left">Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="supply in shopOrder.supplies" :key="supply.id"
                                            class="border-t hover:bg-muted/20">
                                            <td class="px-4 py-2 font-medium">{{ supply.name }}</td>
                                            <td class="px-4 py-2">{{ formatQty(supply.pivot.quantity_used) }}</td>
                                            <td class="px-4 py-2 text-muted-foreground">{{ supply.pivot.unit }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>

                </div>

                <!-- Right Column -->
                <div class="col-span-12 lg:col-span-4 space-y-6">

                    <!-- Pricing Summary -->
                    <Card>
                        <CardHeader><CardTitle>Pricing Summary</CardTitle></CardHeader>
                        <CardContent class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Subtotal</span>
                                <span>
                                    <template v-if="shopOrder.pricing_model === 'per_kg'">
                                        {{ shopOrder.actual_weight_kg }}kg × {{ formatCurrency(shopOrder.price_per_kg) }}
                                    </template>
                                    <template v-else>
                                        {{ shopOrder.bundle_quantity }} × {{ formatCurrency(shopOrder.bundle_price) }}
                                    </template>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Additional Charges</span>
                                <span>{{ formatCurrency(shopOrder.additional_charges) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Discount</span>
                                <span class="text-red-600">-{{ formatCurrency(shopOrder.discount_amount) }}</span>
                            </div>
                            <div class="border-t pt-3 flex justify-between font-bold text-base">
                                <span>Total</span>
                                <span>{{ formatCurrency(shopOrder.total_amount) }}</span>
                            </div>
                            <div class="flex justify-between text-emerald-600">
                                <span>Amount Paid</span>
                                <span class="font-semibold">{{ formatCurrency(shopOrder.amount_paid) }}</span>
                            </div>
                            <div class="flex justify-between text-orange-600">
                                <span>Balance</span>
                                <span class="font-semibold">
                                    {{ formatCurrency(Number(shopOrder.total_amount) - Number(shopOrder.amount_paid)) }}
                                </span>
                            </div>
                            <div class="pt-1">
                                <p class="text-xs text-muted-foreground uppercase tracking-wide mb-1">Payment Method</p>
                                <p class="font-medium">{{ shopOrder.payment_method.toUpperCase() }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- GCash / Maya Payment Request -->
                    <Card v-if="isDigitalPayment">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <CreditCard class="h-4 w-4" />
                                {{ shopOrder.payment_method.toUpperCase() }} Payment
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">

                            <!-- QR code -->
                            <div v-if="activeQr" class="flex flex-col items-center gap-2">
                                <p class="text-xs text-muted-foreground text-center">
                                    Show or share this QR for the customer to scan
                                </p>
                                <img :src="activeQr"
                                    :alt="`${shopOrder.payment_method} QR`"
                                    class="h-44 w-44 object-contain rounded-xl border bg-white p-2" />
                                <p class="text-sm font-bold">{{ formatCurrency(shopOrder.total_amount) }}</p>
                            </div>
                            <div v-else class="text-xs text-amber-700 bg-amber-50 rounded-lg p-3">
                                No QR code set up yet. Go to
                                <a href="/shop/payment-qr" class="underline font-medium">Payment QR</a>
                                in settings to add one.
                            </div>

                            <!-- Send in-app notification -->
                            <Button
                                class="w-full"
                                :disabled="sending || shopOrder.payment_status === 'paid'"
                                @click="sendPaymentRequest"
                            >
                                <Loader2 v-if="sending" class="h-4 w-4 mr-2 animate-spin" />
                                <Send v-else class="h-4 w-4 mr-2" />
                                {{ sending ? 'Sending…' : 'Send Payment Request to Customer' }}
                            </Button>
                            <p v-if="shopOrder.payment_status === 'paid'" class="text-xs text-emerald-600 text-center">
                                Order is already fully paid.
                            </p>
                            <p class="text-xs text-muted-foreground text-center">
                                Sends an in-app notification with the amount and QR link.
                            </p>
                        </CardContent>
                    </Card>

                    <!-- Dates -->
                    <Card>
                        <CardHeader><CardTitle>Timeline</CardTitle></CardHeader>
                        <CardContent class="space-y-3 text-sm">
                            <div>
                                <p class="text-xs text-muted-foreground uppercase tracking-wide mb-1">Order Created</p>
                                <p class="font-medium">{{ formatDate(shopOrder.created_at) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground uppercase tracking-wide mb-1">Est. Completion</p>
                                <p class="font-medium">{{ formatDate(shopOrder.estimated_completion_at) }}</p>
                            </div>
                            <div v-if="shopOrder.completed_at">
                                <p class="text-xs text-muted-foreground uppercase tracking-wide mb-1">Completed At</p>
                                <p class="font-medium text-emerald-600">{{ formatDate(shopOrder.completed_at) }}</p>
                            </div>
                        </CardContent>
                    </Card>

                </div>
            </div>
        </div>
    </ShopLayout>
</template>