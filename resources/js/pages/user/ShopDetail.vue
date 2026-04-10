<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import UserLayout from '@/layouts/user/UserLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
} from '@/components/ui/dialog'
import {
    MapPin, Phone, ArrowLeft, WashingMachine,
    Package, Loader2, CheckCircle2, Info,
} from 'lucide-vue-next'
import type { AppPageProps } from '@/types'

// ─── Types ────────────────────────────────────────────────────────────────────

interface ShopService {
    id: number
    service_name: string
    description: string | null
    pricing_model: 'per_kg' | 'per_bundle'
    price_per_kg: string | null
    bundle_weight_kg: string | null
    bundle_price: string | null
    estimated_hours: number | null
}

interface ShopInfo {
    id: number
    shop_name: string
    branch_name: string | null
    phone: string | null
    block_street: string | null
    municipality: string
    barangay: string
    latitude: number | null
    longitude: number | null
    distance_km: number | null
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    shop: ShopInfo
    services: ShopService[]
    userLat: number | null
    userLng: number | null
}>()

// ─── Auth ─────────────────────────────────────────────────────────────────────

const page   = usePage<AppPageProps>()
const user   = computed(() => page.props.auth.user)
const errors = computed(() => page.props.errors as Record<string, string>)

onMounted(() => {
    const flashToast = page.props.toast as { type: string; message: string } | undefined
    if (flashToast?.type === 'success') toast.success(flashToast.message)
    else if (flashToast?.type === 'error') toast.error(flashToast.message)
})

// ─── Order form ───────────────────────────────────────────────────────────────

const isOrderOpen    = ref(false)
const placing        = ref(false)
const selectedService = ref<ShopService | null>(null)

const form = ref({
    shop_id:               props.shop.id,
    service_id:            0,
    customer_name:         user.value.name,
    customer_phone:        '',
    estimated_weight_kg:   '',
    pickup_type:           'walk_in' as 'walk_in' | 'pickup',
    customer_address:      '',
    special_instructions:  '',
})

function openOrder(service: ShopService) {
    selectedService.value = service
    form.value.service_id = service.id
    isOrderOpen.value     = true
}

function closeOrder() {
    isOrderOpen.value = false
    selectedService.value = null
}

const estimatedTotal = computed(() => {
    if (!selectedService.value) return null
    const svc = selectedService.value
    const wt  = parseFloat(form.value.estimated_weight_kg)
    if (!wt || wt <= 0) return null
    if (svc.pricing_model === 'per_kg' && svc.price_per_kg) {
        return (parseFloat(svc.price_per_kg) * wt).toFixed(2)
    }
    if (svc.pricing_model === 'per_bundle' && svc.bundle_price) {
        return parseFloat(svc.bundle_price).toFixed(2)
    }
    return null
})

function submitOrder() {
    placing.value = true
    router.post('/user/orders', form.value, {
        preserveScroll: true,
        onSuccess: () => { closeOrder() },
        onFinish: () => { placing.value = false },
    })
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatAddress(shop: ShopInfo) {
    return [shop.block_street, shop.barangay, shop.municipality].filter(Boolean).join(', ')
}

function formatServicePrice(svc: ShopService) {
    if (svc.pricing_model === 'per_kg' && svc.price_per_kg) {
        return `₱${parseFloat(svc.price_per_kg).toFixed(0)}/kg`
    }
    if (svc.pricing_model === 'per_bundle' && svc.bundle_price) {
        return `₱${parseFloat(svc.bundle_price).toFixed(0)} / ${svc.bundle_weight_kg}kg bundle`
    }
    return '—'
}

function goBack() {
    const params = props.userLat ? `?lat=${props.userLat}&lng=${props.userLng}` : ''
    router.visit(`/user/shops${params}`)
}
</script>

<template>
    <Head :title="shop.shop_name" />
    <UserLayout>

        <!-- Back button -->
        <div class="px-4 pt-4">
            <button
                class="flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4"
                @click="goBack"
            >
                <ArrowLeft class="h-4 w-4" />
                Back to shops
            </button>
        </div>

        <div class="px-4 pb-6 space-y-5">

            <!-- ── Shop Header ────────────────────────────────── -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-5 text-white shadow-md">
                <div class="flex items-start gap-3">
                    <div class="h-14 w-14 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                        <WashingMachine class="h-8 w-8 text-white" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <h1 class="text-xl font-bold leading-tight">{{ shop.shop_name }}</h1>
                        <p v-if="shop.branch_name" class="text-sm opacity-80 mt-0.5">{{ shop.branch_name }}</p>

                        <div class="flex items-center gap-1.5 mt-2 text-sm opacity-90">
                            <MapPin class="h-3.5 w-3.5 shrink-0" />
                            <span class="truncate">{{ formatAddress(shop) }}</span>
                        </div>

                        <div class="flex items-center gap-4 mt-2">
                            <div v-if="shop.phone" class="flex items-center gap-1 text-sm opacity-90">
                                <Phone class="h-3.5 w-3.5 shrink-0" />
                                <span>{{ shop.phone }}</span>
                            </div>
                            <div v-if="shop.distance_km !== null" class="bg-white/20 rounded-full px-2.5 py-0.5 text-xs font-bold">
                                {{ shop.distance_km }} km away
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Services ───────────────────────────────────── -->
            <div>
                <h2 class="text-sm font-bold text-gray-800 mb-3">Available Services</h2>

                <div v-if="services.length === 0" class="flex flex-col items-center py-10 text-gray-400">
                    <Package class="h-10 w-10 mb-2 opacity-40" />
                    <p class="text-sm">No services available at this time.</p>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="svc in services"
                        :key="svc.id"
                        class="bg-white rounded-xl border border-gray-100 shadow-sm p-4"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-800 text-sm">{{ svc.service_name }}</p>
                                <p v-if="svc.description" class="text-xs text-gray-500 mt-0.5">{{ svc.description }}</p>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="text-sm font-bold text-blue-600">{{ formatServicePrice(svc) }}</span>
                                    <span v-if="svc.estimated_hours" class="text-[10px] text-gray-400 bg-gray-50 px-2 py-0.5 rounded-full">
                                        ~{{ svc.estimated_hours }}h turnaround
                                    </span>
                                </div>
                                <p class="text-[10px] text-amber-600 mt-1 flex items-center gap-1">
                                    <Info class="h-3 w-3 shrink-0" />
                                    Price is estimated. Actual price based on actual weight.
                                </p>
                            </div>
                            <Button
                                size="sm"
                                class="shrink-0 rounded-xl"
                                @click="openOrder(svc)"
                            >
                                Order
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ── Order Dialog ────────────────────────────────────── -->
        <Dialog v-model:open="isOrderOpen">
            <DialogContent class="max-w-sm mx-4 rounded-2xl">
                <DialogHeader>
                    <DialogTitle class="text-base">
                        Place Order — {{ selectedService?.service_name }}
                    </DialogTitle>
                </DialogHeader>

                <div class="space-y-4 py-2">

                    <!-- Shop info reminder -->
                    <div class="bg-blue-50 rounded-xl p-3 text-xs text-blue-700 flex items-start gap-2">
                        <Info class="h-3.5 w-3.5 shrink-0 mt-0.5" />
                        <div>
                            <strong>{{ shop.shop_name }}</strong><br />
                            {{ formatAddress(shop) }}
                        </div>
                    </div>

                    <!-- Customer name -->
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium">Your Name <span class="text-red-500">*</span></label>
                        <Input v-model="form.customer_name" placeholder="Full name"
                            :class="errors.customer_name ? 'border-red-400' : ''" />
                        <p v-if="errors.customer_name" class="text-xs text-red-500">{{ errors.customer_name }}</p>
                    </div>

                    <!-- Phone -->
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium">Phone Number <span class="text-red-500">*</span></label>
                        <Input v-model="form.customer_phone" type="tel" placeholder="e.g. 09XXXXXXXXX"
                            :class="errors.customer_phone ? 'border-red-400' : ''" />
                        <p v-if="errors.customer_phone" class="text-xs text-red-500">{{ errors.customer_phone }}</p>
                    </div>

                    <!-- Estimated weight -->
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium">Estimated Weight (kg) <span class="text-red-500">*</span></label>
                        <Input v-model="form.estimated_weight_kg" type="number" min="0.5" step="0.5" placeholder="e.g. 3"
                            :class="errors.estimated_weight_kg ? 'border-red-400' : ''" />
                        <p v-if="errors.estimated_weight_kg" class="text-xs text-red-500">{{ errors.estimated_weight_kg }}</p>
                        <!-- Estimated total preview -->
                        <div v-if="estimatedTotal" class="bg-green-50 rounded-lg p-2 text-xs text-green-700 flex items-center gap-1.5">
                            <CheckCircle2 class="h-3.5 w-3.5 shrink-0" />
                            Estimated total: <strong>₱{{ estimatedTotal }}</strong>
                            <span class="opacity-70">(indicative)</span>
                        </div>
                    </div>

                    <!-- Pickup type -->
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium">How will you drop-off? <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                type="button"
                                class="py-2.5 rounded-xl border-2 text-sm font-medium transition"
                                :class="form.pickup_type === 'walk_in'
                                    ? 'border-blue-600 bg-blue-50 text-blue-700'
                                    : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                                @click="form.pickup_type = 'walk_in'"
                            >
                                Walk-in
                            </button>
                            <button
                                type="button"
                                class="py-2.5 rounded-xl border-2 text-sm font-medium transition"
                                :class="form.pickup_type === 'pickup'
                                    ? 'border-blue-600 bg-blue-50 text-blue-700'
                                    : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                                @click="form.pickup_type = 'pickup'"
                            >
                                Pickup
                            </button>
                        </div>
                    </div>

                    <!-- Address (only for pickup) -->
                    <div v-if="form.pickup_type === 'pickup'" class="space-y-1.5">
                        <label class="text-sm font-medium">Pickup Address <span class="text-red-500">*</span></label>
                        <Input v-model="form.customer_address" placeholder="Your complete address"
                            :class="errors.customer_address ? 'border-red-400' : ''" />
                        <p v-if="errors.customer_address" class="text-xs text-red-500">{{ errors.customer_address }}</p>
                    </div>

                    <!-- Special instructions -->
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium">Special Instructions <span class="text-xs text-gray-400">(optional)</span></label>
                        <Input v-model="form.special_instructions" placeholder="e.g. Separate whites, use fabric conditioner..." />
                    </div>

                    <!-- Disclaimer -->
                    <div class="bg-amber-50 rounded-xl p-3 text-xs text-amber-700">
                        <strong>Note:</strong> The total amount will be finalized by the shop after weighing your laundry. Payment is made at the shop.
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <Button variant="outline" class="flex-1" @click="closeOrder">Cancel</Button>
                    <Button class="flex-1" :disabled="placing" @click="submitOrder">
                        <Loader2 v-if="placing" class="h-4 w-4 mr-2 animate-spin" />
                        {{ placing ? 'Placing...' : 'Place Order' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

    </UserLayout>
</template>
