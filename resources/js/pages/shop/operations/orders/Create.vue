<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

// ─── Types ─────────────────────────────────────────

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
}

const props = defineProps<{
    shopId: number
    pickupTypes: string[]
    paymentMethods: string[]
    services?: Service[]
    inventoryItems?: InventoryItem[]
}>()

// ─── Base URL ──────────────────────────────────────

const base = '/shop/operations/orders'

// ─── Form ──────────────────────────────────────────

const form = useForm({
    shop_id:                props.shopId,
    customer_name:          '',
    customer_phone:         '',
    customer_address:       '',
    service_id:             '' as string | number,
    pickup_type:            'walk_in',
    pricing_model:          'per_kg' as 'per_kg' | 'per_bundle',
    estimated_weight_kg:    '' as string | number,
    actual_weight_kg:       '' as string | number,
    price_per_kg:           0,
    bundle_weight_kg:       '' as string | number,
    bundle_price:           0,
    bundle_quantity:        1,
    additional_charges:     0,
    discount_amount:        0,
    total_amount:           0,
    payment_method:         'cash',
    payment_status:         'unpaid',
    amount_paid:            0,
    estimated_completion_at: '',
    supplies: [] as { inventory_id: number; quantity_used: number; unit: string }[],
})

// ─── Helpers ───────────────────────────────────────

const formatServiceLabel = (svc: Service) => {
    if (svc.pricing_model === 'per_kg')
        return `${svc.service_name} — ${formatCurrency(svc.price_per_kg)}/kg`
    return `${svc.service_name} — ${formatCurrency(svc.bundle_price)} / ${svc.bundle_weight_kg}kg bundle`
}

const formatLabel = (s: string) =>
    s.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())

const formatCurrency = (v: string | number | null) =>
    v !== null ? `₱${Number(v).toLocaleString('en-PH', { minimumFractionDigits: 2 })}` : '—'

// ─── Derived ───────────────────────────────────────

const selectedService = computed(() =>
    props.services?.find(s => s.id === Number(form.service_id))
)

const isPerKg = computed(() => form.pricing_model === 'per_kg')

const onServiceChange = () => {
    const svc = selectedService.value
    if (!svc) return

    form.pricing_model = svc.pricing_model

    if (svc.pricing_model === 'per_kg') {
        form.price_per_kg     = parseFloat(svc.price_per_kg ?? '0')
        form.bundle_price     = 0
        form.bundle_weight_kg = ''
        form.bundle_quantity  = 1
    } else {
        form.bundle_price        = parseFloat(svc.bundle_price ?? '0')
        form.bundle_weight_kg    = svc.bundle_weight_kg ?? ''
        form.price_per_kg        = 0
        form.estimated_weight_kg = ''
        form.actual_weight_kg    = ''
    }

    if (svc.estimated_hours) {
        const completion = new Date()
        completion.setHours(completion.getHours() + svc.estimated_hours)
        form.estimated_completion_at = completion.toISOString().slice(0, 16)
    }
}

const computedTotal = computed(() => {
    const charges  = parseFloat(String(form.additional_charges)) || 0
    const discount = parseFloat(String(form.discount_amount)) || 0
    let base = 0
    if (isPerKg.value) {
        base = (parseFloat(String(form.actual_weight_kg)) || 0) *
               (parseFloat(String(form.price_per_kg)) || 0)
    } else {
        base = (parseFloat(String(form.bundle_quantity)) || 1) *
               (parseFloat(String(form.bundle_price)) || 0)
    }
    return Math.max(0, base + charges - discount)
})

// ─── Supplies ──────────────────────────────────────

const unitOptions = ['pcs', 'kg', 'g', 'liters', 'ml', 'bottles', 'boxes', 'packs', 'rolls', 'sachets']

const addSupply = () =>
    form.supplies.push({ inventory_id: 0, quantity_used: 0, unit: '' })

const removeSupply = (i: number) =>
    form.supplies.splice(i, 1)

const onSupplyItemChange = (supply: { inventory_id: number; quantity_used: number; unit: string }) => {
    const item = props.inventoryItems?.find(i => i.id === supply.inventory_id)
    if (item) supply.unit = item.unit
}

// ─── Submit ────────────────────────────────────────

const submit = () => {
    form.total_amount = computedTotal.value
    form.post(base)
}
</script>

<template>
    <Head title="New Order" />

    <ShopLayout title="New Order">
        <div class="px-6 space-y-8">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">New Order</h2>
                    <p class="text-sm text-muted-foreground">Fill in the details to create a new laundry order.</p>
                </div>
                <button @click="router.visit(base)"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    &larr; Back to Orders
                </button>
            </div>

            <form @submit.prevent="submit" class="space-y-8">

                <!-- ── Customer Information ──────────────── -->
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
                            <label class="text-sm font-medium">Pickup Type <span class="text-red-500">*</span></label>
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

                <!-- ── Service ───────────────────────────── -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">Service</p>
                    <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                        <div class="col-span-12 sm:col-span-5 space-y-1">
                            <label class="text-sm font-medium">Service <span class="text-red-500">*</span></label>
                            <select v-model="form.service_id" @change="onServiceChange" required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.service_id }">
                                <option value="" disabled>Select a service…</option>
                                <option v-for="svc in services" :key="svc.id" :value="svc.id">
                                    {{ formatServiceLabel(svc) }}
                                </option>
                            </select>
                            <p v-if="form.errors.service_id" class="text-xs text-red-500">{{ form.errors.service_id }}</p>
                        </div>

                        <div v-if="selectedService" class="col-span-12 sm:col-span-7 flex items-end pb-0.5">
                            <div class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm w-full"
                                :class="isPerKg
                                    ? 'bg-blue-50 border border-blue-200 text-blue-800'
                                    : 'bg-violet-50 border border-violet-200 text-violet-800'">
                                <span class="font-semibold">{{ selectedService.service_name }}</span>
                                <span class="text-xs opacity-70">•</span>
                                <span v-if="isPerKg">{{ formatCurrency(selectedService.price_per_kg) }} per kg</span>
                                <span v-else>{{ formatCurrency(selectedService.bundle_price) }} / {{ selectedService.bundle_weight_kg }}kg bundle</span>
                                <span v-if="selectedService.estimated_hours" class="ml-auto text-xs opacity-70">
                                    ~{{ selectedService.estimated_hours }}h turnaround
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Weight / Bundle ───────────────────── -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">
                        {{ isPerKg ? 'Weight' : 'Bundle' }}
                    </p>
                    <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                        <template v-if="isPerKg">
                            <div class="col-span-12 sm:col-span-4 space-y-1">
                                <label class="text-sm font-medium">Estimated Weight (kg) <span class="text-red-500">*</span></label>
                                <input v-model="form.estimated_weight_kg" type="number" step="0.01" min="0.1" required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.estimated_weight_kg }" />
                                <p v-if="form.errors.estimated_weight_kg" class="text-xs text-red-500">{{ form.errors.estimated_weight_kg }}</p>
                            </div>
                            <div class="col-span-12 sm:col-span-4 space-y-1">
                                <label class="text-sm font-medium">Actual Weight (kg) <span class="text-red-500">*</span></label>
                                <input v-model="form.actual_weight_kg" type="number" step="0.01" min="0.1" required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.actual_weight_kg }" />
                                <p v-if="form.errors.actual_weight_kg" class="text-xs text-red-500">{{ form.errors.actual_weight_kg }}</p>
                            </div>
                            <div class="col-span-12 sm:col-span-4 space-y-1">
                                <label class="text-sm font-medium">Price per KG <span class="text-muted-foreground text-xs">(auto-filled)</span></label>
                                <input v-model="form.price_per_kg" type="number" step="0.01" readonly
                                    class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm bg-muted/40 text-muted-foreground" />
                            </div>
                        </template>

                        <template v-else>
                            <div class="col-span-12 sm:col-span-4 space-y-1">
                                <label class="text-sm font-medium">Bundle Size <span class="text-muted-foreground text-xs">(auto-filled)</span></label>
                                <div class="flex items-center h-[42px] rounded-lg bg-muted/40 border border-gray-200 px-3 text-sm text-muted-foreground">
                                    {{ form.bundle_weight_kg ? `${form.bundle_weight_kg} kg / bundle` : '—' }}
                                </div>
                            </div>
                            <div class="col-span-12 sm:col-span-4 space-y-1">
                                <label class="text-sm font-medium">Bundle Price <span class="text-muted-foreground text-xs">(auto-filled)</span></label>
                                <div class="flex items-center h-[42px] rounded-lg bg-muted/40 border border-gray-200 px-3 text-sm text-muted-foreground">
                                    {{ form.bundle_price ? formatCurrency(form.bundle_price) : '—' }}
                                </div>
                            </div>
                            <div class="col-span-12 sm:col-span-4 space-y-1">
                                <label class="text-sm font-medium">Number of Bundles <span class="text-red-500">*</span></label>
                                <input v-model="form.bundle_quantity" type="number" min="1" step="1" required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                                <p class="text-xs text-muted-foreground mt-0.5">
                                    Est. total weight: {{ (Number(form.bundle_quantity) * Number(form.bundle_weight_kg)).toFixed(1) }} kg
                                </p>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- ── Pricing ───────────────────────────── -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">Pricing</p>
                    <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                        <div class="col-span-12 sm:col-span-3 space-y-1">
                            <label class="text-sm font-medium">Additional Charges</label>
                            <input v-model="form.additional_charges" type="number" step="0.01" min="0"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                        </div>
                        <div class="col-span-12 sm:col-span-3 space-y-1">
                            <label class="text-sm font-medium">Discount</label>
                            <input v-model="form.discount_amount" type="number" step="0.01" min="0"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                        </div>
                        <div class="col-span-12 sm:col-span-6 space-y-1">
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

                <!-- ── Payment ───────────────────────────── -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-4">Payment</p>
                    <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                        <div class="col-span-12 sm:col-span-3 space-y-1">
                            <label class="text-sm font-medium">Payment Method <span class="text-red-500">*</span></label>
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

                <!-- ── Supplies ───────────────────────────── -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">Supplies Used</p>
                        <button type="button" @click="addSupply"
                            class="text-sm font-medium text-blue-600 hover:text-blue-800 transition">
                            + Add Supply
                        </button>
                    </div>

                    <p v-if="form.supplies.length === 0" class="text-sm text-muted-foreground italic">
                        No supplies added yet.
                    </p>

                    <div class="space-y-3">
                        <div v-for="(supply, i) in form.supplies" :key="i"
                            class="grid grid-cols-12 gap-x-4 items-end p-3 rounded-lg border border-gray-200 bg-gray-50/50">

                            <!-- Inventory Item -->
                            <div class="col-span-12 sm:col-span-5 space-y-1">
                                <label class="text-xs font-medium text-muted-foreground">Inventory Item</label>
                                <select
                                    v-model="supply.inventory_id"
                                    @change="onSupplyItemChange(supply)"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white">
                                    <option :value="0" disabled>Select item…</option>
                                    <option v-for="item in inventoryItems" :key="item.id" :value="item.id">
                                        {{ item.name }} — {{ item.quantity }} {{ item.unit }} available
                                    </option>
                                </select>
                            </div>

                            <!-- Qty Used -->
                            <div class="col-span-6 sm:col-span-3 space-y-1">
                                <label class="text-xs font-medium text-muted-foreground">Qty Used</label>
                                <input
                                    v-model="supply.quantity_used"
                                    type="number" step="0.01" min="0.01"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                            </div>

                            <!-- Unit dropdown -->
                            <div class="col-span-5 sm:col-span-3 space-y-1">
                                <label class="text-xs font-medium text-muted-foreground">Unit</label>
                                <select
                                    v-model="supply.unit"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white">
                                    <option value="" disabled>Select unit…</option>
                                    <option v-for="u in unitOptions" :key="u" :value="u">{{ u }}</option>
                                </select>
                            </div>

                            <!-- Remove -->
                            <div class="col-span-1 flex justify-end pb-0.5">
                                <button type="button" @click="removeSupply(i)"
                                    class="text-red-400 hover:text-red-600 transition p-1 rounded">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- Supplies errors -->
                    <p v-if="form.errors.supplies" class="mt-2 text-xs text-red-500">
                        {{ form.errors.supplies }}
                    </p>
                </div>

                <!-- ── Actions ───────────────────────────── -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <button type="button" @click="router.visit(base)"
                        class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" :disabled="form.processing"
                        class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-50 transition">
                        {{ form.processing ? 'Creating…' : 'Create Order' }}
                    </button>
                </div>

            </form>
        </div>
    </ShopLayout>
</template>