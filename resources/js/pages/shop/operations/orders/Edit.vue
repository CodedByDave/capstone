<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, onMounted, watch } from 'vue'
import { type AppPageProps } from '@/types'
import { Button } from '@/components/ui/button'
import { ArrowLeft, Loader2 } from 'lucide-vue-next'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

interface Service {
    id: number
    service_name: string
    pricing_model: 'per_kg' | 'per_bundle'
    price_per_kg: string | null
    bundle_weight_kg: string | null
    bundle_price: string | null
    estimated_hours: number | null
}

interface InventoryItem {
    id: number
    name: string
    unit: string
    quantity: number
    selling_price: string | null
}

interface Promotion {
    id: number
    name: string
    type: 'percentage' | 'fixed'
    value: string
    min_order_amount: string | null
}

interface Order {
    id: number
    order_number: string
    customer_name: string
    customer_phone: string | null
    customer_address: string | null
    service_id: number
    pickup_type: string
    pricing_model: 'per_kg' | 'per_bundle'
    estimated_weight_kg: string | null
    actual_weight_kg: string | null
    price_per_kg: string | null
    bundle_weight_kg: string | null
    bundle_price: string | null
    bundle_quantity: number | null
    promotion_id: number | null
    additional_charges: string
    discount_amount: string
    total_amount: string
    payment_method: string
    payment_status: string
    amount_paid: string
    status: string
    estimated_completion_at: string | null
    supplies: { id: number; pivot: { quantity_used: number; unit: string } }[]
}

const props = defineProps<{
    shopOrder: Order
    shopId: number
    pickupTypes: string[]
    paymentMethods: string[]
    statuses: string[]
    services?: Service[]
    inventoryItems?: InventoryItem[]
    promotions?: Promotion[]
}>()

const page = usePage<AppPageProps>()
const isOwner = computed(() => page.props.auth.user.role === 'owner')
const base = computed(() => isOwner.value ? '/shop/operations/orders' : '/staff/operations/orders')
onMounted(() => {
    const t = page.props.toast as any
    if (!t) return
    if (t.type === 'success') toast.success(t.message)
    if (t.type === 'error')   toast.error(t.message)
})

const form = useForm({
    customer_name:           props.shopOrder.customer_name,
    customer_phone:          props.shopOrder.customer_phone ?? '',
    customer_address:        props.shopOrder.customer_address ?? '',
    service_id:              props.shopOrder.service_id,
    pickup_type:             props.shopOrder.pickup_type,
    pricing_model:           props.shopOrder.pricing_model,
    estimated_weight_kg:     props.shopOrder.estimated_weight_kg ?? '',
    actual_weight_kg:        props.shopOrder.actual_weight_kg ?? '',
    price_per_kg:            props.shopOrder.price_per_kg ?? 0,
    bundle_weight_kg:        props.shopOrder.bundle_weight_kg ?? '',
    bundle_price:            props.shopOrder.bundle_price ?? 0,
    bundle_quantity:         props.shopOrder.bundle_quantity ?? 1,
    promotion_id:            props.shopOrder.promotion_id ?? ('' as string | number),
    additional_charges:      props.shopOrder.additional_charges,
    discount_amount:         props.shopOrder.discount_amount,
    total_amount:            props.shopOrder.total_amount,
    payment_method:          props.shopOrder.payment_method,
    payment_status:          props.shopOrder.payment_status,
    amount_paid:             props.shopOrder.amount_paid,
    status:                  props.shopOrder.status,
    estimated_completion_at: props.shopOrder.estimated_completion_at
        ? new Date(props.shopOrder.estimated_completion_at).toISOString().slice(0, 16)
        : '',
    supplies: props.shopOrder.supplies.map(s => ({
        inventory_id:  s.id,
        quantity_used: s.pivot.quantity_used,
        unit:          s.pivot.unit,
    })),
})

const isPerKg = computed(() => form.pricing_model === 'per_kg')

// When actual weight is entered for per_bundle orders, auto-update bundle quantity
watch(() => form.actual_weight_kg, (val) => {
    if (!isPerKg.value && val) {
        const bundleSize = parseFloat(String(form.bundle_weight_kg)) || 1
        const actualKg   = parseFloat(String(val)) || 0
        if (actualKg > 0) {
            form.bundle_quantity = Math.ceil(actualKg / bundleSize)
        }
    }
})

const selectedService = computed(() =>
    props.services?.find(s => s.id === Number(form.service_id))
)

const onServiceChange = () => {
    const svc = selectedService.value
    if (!svc) return
    form.pricing_model = svc.pricing_model
    if (svc.pricing_model === 'per_kg') {
        form.price_per_kg     = parseFloat(svc.price_per_kg ?? '0')
        form.bundle_price     = 0
        form.bundle_weight_kg = ''
    } else {
        form.bundle_price     = parseFloat(svc.bundle_price ?? '0')
        form.bundle_weight_kg = svc.bundle_weight_kg ?? ''
        form.price_per_kg     = 0
    }
}

const selectedPromotion = computed(() =>
    props.promotions?.find(p => p.id === Number(form.promotion_id))
)

const baseTotal = computed(() => {
    if (isPerKg.value)
        return (parseFloat(String(form.actual_weight_kg)) || 0) * (parseFloat(String(form.price_per_kg)) || 0)
    return (parseFloat(String(form.bundle_quantity)) || 1) * (parseFloat(String(form.bundle_price)) || 0)
})

const promoDiscount = computed(() => {
    const promo = selectedPromotion.value
    if (!promo) return 0
    const total = baseTotal.value
    if (promo.min_order_amount && total < parseFloat(promo.min_order_amount)) return 0
    if (promo.type === 'percentage') return Math.round(total * (parseFloat(promo.value) / 100) * 100) / 100
    return Math.min(parseFloat(promo.value), total)
})

const onPromotionChange = () => {
    form.discount_amount = String(promoDiscount.value)
}

const computedTotal = computed(() => {
    const charges  = parseFloat(String(form.additional_charges)) || 0
    const discount = parseFloat(String(form.discount_amount)) || 0
    return Math.max(0, baseTotal.value + charges - discount)
})

watch(computedTotal, (val) => {
    if (form.payment_status !== 'paid') {
        form.amount_paid = String(val)
    }
}, { immediate: true })

const formatServiceLabel = (svc: Service) => {
    if (svc.pricing_model === 'per_kg') return `${svc.service_name} — ₱${svc.price_per_kg}/kg`
    return `${svc.service_name} — ₱${svc.bundle_price} / ${svc.bundle_weight_kg}kg bundle`
}

const formatCurrency = (v: string | number | null) =>
    v !== null ? `₱${Number(v).toLocaleString('en-PH', { minimumFractionDigits: 2 })}` : '—'

const formatLabel = (s: string) =>
    s.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())

const addSupply = () => form.supplies.push({ inventory_id: 0, quantity_used: 0, unit: '' })

const removeSupply = (i: number) => {
    form.supplies.splice(i, 1)
    recalcSupplyCharges()
}

const onSupplyItemChange = (supply: { inventory_id: number; quantity_used: number; unit: string }) => {
    const item = props.inventoryItems?.find(i => i.id === supply.inventory_id)
    if (item) supply.unit = item.unit
    recalcSupplyCharges()
}

const supplyCost = computed(() =>
    form.supplies.reduce((sum, supply) => {
        const item  = props.inventoryItems?.find(i => i.id === supply.inventory_id)
        const price = item?.selling_price ? parseFloat(item.selling_price) : 0
        const qty   = parseFloat(String(supply.quantity_used)) || 0
        return sum + price * qty
    }, 0)
)

function recalcSupplyCharges() {
    form.additional_charges = String(parseFloat(supplyCost.value.toFixed(2)))
}

watch(() => form.supplies.map(s => s.quantity_used), recalcSupplyCharges, { deep: true })

const submit = () => {
    form.total_amount = String(computedTotal.value)
    form.put(`${base.value}/${props.shopOrder.id}`)
}
</script>

<template>
    <Head :title="`Edit Order ${shopOrder.order_number}`" />

    <ShopLayout :title="`Edit Order ${shopOrder.order_number}`">
        <div class="px-6 space-y-8">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Edit Order</h2>
                    <p class="text-sm text-muted-foreground">{{ shopOrder.order_number }}</p>
                </div>
                <Button variant="outline" @click="router.visit(`${base}/${shopOrder.id}`)">
                    <ArrowLeft class="h-4 w-4 mr-2" /> Back
                </Button>
            </div>

            <form @submit.prevent="submit" class="space-y-8">

                <!-- Status -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">Order Status</p>
                    <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                        <div class="col-span-12 sm:col-span-4 space-y-1">
                            <label class="text-sm font-medium">Status</label>
                            <select v-model="form.status"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <option v-for="s in statuses" :key="s" :value="s">{{ formatLabel(s) }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Customer -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">Customer Information</p>
                    <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                        <div class="col-span-12 sm:col-span-4 space-y-1">
                            <label class="text-sm font-medium">Customer Name <span class="text-red-500">*</span></label>
                            <input v-model="form.customer_name" type="text" required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.customer_name }" />
                            <p v-if="form.errors.customer_name" class="text-xs text-red-500">{{ form.errors.customer_name }}</p>
                        </div>
                        <div class="col-span-12 sm:col-span-4 space-y-1">
                            <label class="text-sm font-medium">Phone</label>
                            <input v-model="form.customer_phone" type="text"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                        </div>
                        <div class="col-span-12 sm:col-span-4 space-y-1">
                            <label class="text-sm font-medium">Pickup Type</label>
                            <select v-model="form.pickup_type"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <option v-for="t in pickupTypes" :key="t" :value="t">{{ formatLabel(t) }}</option>
                            </select>
                        </div>
                        <div class="col-span-12 space-y-1">
                            <label class="text-sm font-medium">Address</label>
                            <textarea v-model="form.customer_address" rows="2"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                        </div>
                    </div>
                </div>

                <!-- Service -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">Service</p>
                    <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                        <div class="col-span-12 sm:col-span-5 space-y-1">
                            <label class="text-sm font-medium">Service <span class="text-red-500">*</span></label>
                            <select v-model="form.service_id" @change="onServiceChange"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <option v-for="svc in services" :key="svc.id" :value="svc.id">
                                    {{ formatServiceLabel(svc) }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Weight / Bundle -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">
                        {{ isPerKg ? 'Weight' : 'Bundle' }}
                    </p>
                    <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                        <template v-if="isPerKg">
                            <div class="col-span-12 sm:col-span-4 space-y-1">
                                <label class="text-sm font-medium">Estimated Weight (kg)</label>
                                <input v-model="form.estimated_weight_kg" type="number" step="0.01" min="0.1"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                            </div>
                            <div class="col-span-12 sm:col-span-4 space-y-1">
                                <label class="text-sm font-medium">Actual Weight (kg)</label>
                                <input v-model="form.actual_weight_kg" type="number" step="0.01" min="0.1"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                            </div>
                            <div class="col-span-12 sm:col-span-4 space-y-1">
                                <label class="text-sm font-medium">Price per KG</label>
                                <input v-model="form.price_per_kg" type="number" step="0.01" readonly
                                    class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm bg-muted/40" />
                            </div>
                        </template>
                        <template v-else>
                            <!-- Row 1: estimated + actual weight + bundle size -->
                            <div class="col-span-12 sm:col-span-4 space-y-1">
                                <label class="text-sm font-medium">Estimated Weight (kg)</label>
                                <div class="flex items-center h-[42px] rounded-lg bg-muted/40 border border-gray-200 px-3 text-sm text-muted-foreground">
                                    {{ form.estimated_weight_kg || '—' }} kg
                                    <span class="ml-2 text-xs text-blue-500">(customer's estimate)</span>
                                </div>
                            </div>
                            <div class="col-span-12 sm:col-span-4 space-y-1">
                                <label class="text-sm font-medium">Actual Weight (kg)</label>
                                <input v-model="form.actual_weight_kg" type="number" step="0.01" min="0.1"
                                    placeholder="Enter after weighing"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                                <p class="text-xs text-muted-foreground">Auto-updates number of bundles</p>
                            </div>
                            <div class="col-span-12 sm:col-span-4 space-y-1">
                                <label class="text-sm font-medium">Bundle Size</label>
                                <div class="flex items-center h-[42px] rounded-lg bg-muted/40 border border-gray-200 px-3 text-sm text-muted-foreground">
                                    {{ form.bundle_weight_kg }} kg / bundle
                                </div>
                            </div>
                            <!-- Row 2: bundle price + bundle quantity -->
                            <div class="col-span-12 sm:col-span-4 space-y-1">
                                <label class="text-sm font-medium">Bundle Price</label>
                                <div class="flex items-center h-[42px] rounded-lg bg-muted/40 border border-gray-200 px-3 text-sm text-muted-foreground">
                                    {{ formatCurrency(form.bundle_price) }}
                                </div>
                            </div>
                            <div class="col-span-12 sm:col-span-4 space-y-1">
                                <label class="text-sm font-medium">Number of Bundles</label>
                                <input v-model="form.bundle_quantity" type="number" min="1" step="1"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Pricing -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">Pricing</p>
                    <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                        <div class="col-span-12 sm:col-span-4 space-y-1">
                            <label class="text-sm font-medium">Apply Promotion</label>
                            <select v-model="form.promotion_id" @change="onPromotionChange"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <option value="">No promotion</option>
                                <option v-for="p in promotions" :key="p.id" :value="p.id">
                                    {{ p.name }} —
                                    {{ p.type === 'percentage' ? `${p.value}% off` : `₱${parseFloat(p.value).toFixed(2)} off` }}
                                    {{ p.min_order_amount ? ` (min ₱${parseFloat(p.min_order_amount).toFixed(0)})` : '' }}
                                </option>
                            </select>
                            <p v-if="selectedPromotion && promoDiscount > 0" class="text-xs text-green-600">
                                Discount: ₱{{ promoDiscount.toFixed(2) }} applied
                            </p>
                            <p v-else-if="selectedPromotion && promoDiscount === 0" class="text-xs text-amber-600">
                                Order total does not meet the minimum requirement.
                            </p>
                        </div>
                        <div class="col-span-12 sm:col-span-3 space-y-1">
                            <label class="text-sm font-medium">Additional Charges</label>
                            <input v-model="form.additional_charges" type="number" step="0.01" min="0"
                                :class="['w-full rounded-lg border px-3 py-2.5 text-sm focus:ring-1', supplyCost > 0 ? 'border-amber-400 bg-amber-50 focus:border-amber-500 focus:ring-amber-400' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500']" />
                            <p v-if="supplyCost > 0" class="text-xs text-amber-600">Auto-filled from supplies (₱{{ supplyCost.toFixed(2) }})</p>
                        </div>
                        <div class="col-span-12 sm:col-span-2 space-y-1">
                            <label class="text-sm font-medium">Discount</label>
                            <input v-model="form.discount_amount" type="number" step="0.01" min="0"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                        </div>
                        <div class="col-span-12 sm:col-span-3 space-y-1">
                            <label class="text-sm font-medium">Computed Total</label>
                            <div class="flex items-center justify-between rounded-lg bg-blue-50 border border-blue-200 px-4 h-[42px]">
                                <span class="text-sm text-blue-700">
                                    <template v-if="isPerKg">{{ form.actual_weight_kg || 0 }} kg × {{ formatCurrency(form.price_per_kg) }}</template>
                                    <template v-else>{{ form.bundle_quantity }} bundle(s) × {{ formatCurrency(form.bundle_price) }}</template>
                                </span>
                                <span class="text-xl font-bold text-blue-900">
                                    ₱{{ computedTotal.toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">Payment</p>
                    <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                        <div class="col-span-12 sm:col-span-3 space-y-1">
                            <label class="text-sm font-medium">Payment Method</label>
                            <select v-model="form.payment_method"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <option v-for="m in paymentMethods" :key="m" :value="m">{{ m.toUpperCase() }}</option>
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-3 space-y-1">
                            <label class="text-sm font-medium">Payment Status</label>
                            <select v-model="form.payment_status"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <option value="unpaid">Unpaid</option>
                                <option value="partial">Partial</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-3 space-y-1">
                            <label class="text-sm font-medium">Amount Paid</label>
                            <input v-model="form.amount_paid" type="number" step="0.01" min="0"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                        </div>
                        <div class="col-span-12 sm:col-span-3 space-y-1">
                            <label class="text-sm font-medium">Estimated Completion</label>
                            <input v-model="form.estimated_completion_at" type="datetime-local"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                        </div>
                    </div>
                </div>

                <!-- Supplies -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">Supplies Used</p>
                        <button type="button" @click="addSupply"
                            class="text-sm font-medium text-blue-600 hover:text-blue-800 transition">
                            + Add Supply
                        </button>
                    </div>
                    <p v-if="form.supplies.length === 0" class="text-sm text-muted-foreground italic">No supplies added yet.</p>
                    <div class="space-y-3">
                        <div v-for="(supply, i) in form.supplies" :key="i" class="grid grid-cols-12 gap-x-4 items-end">
                            <div class="col-span-12 sm:col-span-5 space-y-1">
                                <label class="text-xs font-medium text-muted-foreground">Inventory Item</label>
                                <select v-model="supply.inventory_id" @change="onSupplyItemChange(supply)"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                    <option :value="0" disabled>Select item</option>
                                    <option v-for="item in inventoryItems" :key="item.id" :value="item.id">
                                        {{ item.name }} ({{ item.quantity }} {{ item.unit }}){{ item.selling_price ? ' — ₱' + parseFloat(item.selling_price).toFixed(2) : '' }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-span-4 sm:col-span-2 space-y-1">
                                <label class="text-xs font-medium text-muted-foreground">Qty Used</label>
                                <input v-model="supply.quantity_used" type="number" step="0.01" min="0.01"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                            </div>
                            <div class="col-span-4 sm:col-span-2 space-y-1">
                                <label class="text-xs font-medium text-muted-foreground">Unit</label>
                                <input v-model="supply.unit" type="text" placeholder="e.g. ml"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                            </div>
                            <div class="col-span-3 sm:col-span-2 space-y-1">
                                <label class="text-xs font-medium text-amber-600">Line Cost</label>
                                <div class="flex items-center h-[38px] rounded-lg bg-amber-50 border border-amber-200 px-3 text-sm text-amber-800 font-medium">
                                    ₱{{ ((parseFloat(inventoryItems.find(it => it.id === supply.inventory_id)?.selling_price ?? '0') || 0) * (supply.quantity_used || 0)).toFixed(2) }}
                                </div>
                            </div>
                            <div class="col-span-1 flex justify-end pb-1">
                                <button type="button" @click="removeSupply(i)" class="text-red-400 hover:text-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Supply total -->
                    <div v-if="supplyCost > 0" class="flex items-center justify-between rounded-lg bg-amber-50 border border-amber-200 px-4 py-2 mt-2">
                        <span class="text-sm text-amber-700 font-medium">Supplies Total</span>
                        <span class="text-base font-bold text-amber-900">₱{{ supplyCost.toFixed(2) }}</span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <Button variant="outline" :disabled="form.processing" @click="router.visit(`${base}/${shopOrder.id}`)">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="h-4 w-4 mr-2 animate-spin" />
                        {{ form.processing ? 'Saving…' : 'Save Changes' }}
                    </Button>
                </div>

            </form>
        </div>
    </ShopLayout>
</template>
