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
    Package, Loader2, CheckCircle2, Info, Clock, ChevronRight,
    Banknote, Smartphone, CreditCard,
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
    cover_photo: string | null
    gcash_qr: string | null
    maya_qr: string | null
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

const isOrderOpen     = ref(false)
const placing         = ref(false)
const selectedService = ref<ShopService | null>(null)

const paymentOptions = [
    { value: 'cash',  label: 'Pay at Shop', icon: Banknote,   note: 'Pay cash when you drop off or pick up.' },
    { value: 'gcash', label: 'GCash',        icon: Smartphone, note: 'Shop will send a GCash payment request.' },
    { value: 'maya',  label: 'Maya',         icon: CreditCard, note: 'Shop will send a Maya payment request.' },
]

const form = ref({
    shop_id:              props.shop.id,
    service_id:           0,
    customer_name:        user.value.name,
    customer_phone:       '',
    estimated_weight_kg:  '',
    pickup_type:          'walk_in' as 'walk_in' | 'pickup',
    payment_method:       'cash' as 'cash' | 'gcash' | 'maya',
    customer_address:     '',
    special_instructions: '',
})


function openOrder(service: ShopService) {
    selectedService.value  = service
    form.value.service_id  = service.id
    isOrderOpen.value      = true
}

function closeOrder() {
    isOrderOpen.value     = false
    selectedService.value = null
}

const selectedPayment = computed(() => paymentOptions.find(o => o.value === form.value.payment_method))

const estimatedTotal = computed(() => {
    if (!selectedService.value) return null
    const svc = selectedService.value
    const wt  = parseFloat(form.value.estimated_weight_kg)
    if (!wt || wt <= 0) return null
    if (svc.pricing_model === 'per_kg' && svc.price_per_kg)
        return (parseFloat(svc.price_per_kg) * wt).toFixed(2)
    if (svc.pricing_model === 'per_bundle' && svc.bundle_price) {
        const bundleSize = svc.bundle_weight_kg ? parseFloat(svc.bundle_weight_kg) : 1
        const bundles    = Math.ceil(wt / bundleSize)
        return (bundles * parseFloat(svc.bundle_price)).toFixed(2)
    }
    return null
})

function submitOrder() {
    placing.value = true
    router.post('/user/orders', form.value, {
        preserveScroll: true,
        onSuccess: () => { closeOrder() },
        onFinish:  () => { placing.value = false },
    })
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatAddress(shop: ShopInfo) {
    return [shop.block_street, shop.barangay, shop.municipality].filter(Boolean).join(', ')
}

function formatServicePrice(svc: ShopService) {
    if (svc.pricing_model === 'per_kg' && svc.price_per_kg)
        return `₱${parseFloat(svc.price_per_kg).toFixed(0)}/kg`
    if (svc.pricing_model === 'per_bundle' && svc.bundle_price)
        return `₱${parseFloat(svc.bundle_price).toFixed(0)} / ${svc.bundle_weight_kg}kg bundle`
    return '—'
}

// Service type icon colour
const serviceColours = ['bg-blue-100 text-blue-600', 'bg-teal-100 text-teal-600', 'bg-violet-100 text-violet-600', 'bg-amber-100 text-amber-600']
function svcColour(idx: number) { return serviceColours[idx % serviceColours.length] }

function goBack() {
    const params = props.userLat ? `?lat=${props.userLat}&lng=${props.userLng}` : ''
    router.visit(`/user/shops${params}`)
}
</script>

<template>
    <Head :title="shop.shop_name" />
    <UserLayout>

        <!-- ── Hero photo ─────────────────────────────────────── -->
        <div class="relative h-52 overflow-hidden">
            <img
                v-if="shop.cover_photo"
                :src="shop.cover_photo"
                :alt="shop.shop_name"
                class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                <WashingMachine class="h-16 w-16 text-white/40" />
            </div>

            <!-- Gradient overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-black/30" />

            <!-- Floating back button -->
            <button
                class="absolute top-4 left-4 h-9 w-9 flex items-center justify-center rounded-full bg-black/40 backdrop-blur-sm text-white hover:bg-black/60 transition active:scale-95"
                @click="goBack"
            >
                <ArrowLeft class="h-4 w-4" />
            </button>

            <!-- Distance pill -->
            <div
                v-if="shop.distance_km !== null"
                class="absolute top-4 right-4 bg-black/40 backdrop-blur-sm text-white text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1"
            >
                <MapPin class="h-3 w-3" />
                {{ shop.distance_km }} km away
            </div>

            <!-- Shop name overlay at bottom of photo -->
            <div class="absolute bottom-0 left-0 right-0 px-4 pb-4">
                <h1 class="text-xl font-extrabold text-white leading-tight drop-shadow-md">{{ shop.shop_name }}</h1>
                <p v-if="shop.branch_name" class="text-sm text-white/80 mt-0.5">{{ shop.branch_name }}</p>
            </div>
        </div>

        <div class="px-4 pb-8 space-y-5 pt-4">

            <!-- ── Shop info card ─────────────────────────────── -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-2.5">
                <div class="flex items-start gap-2 text-gray-600 text-sm">
                    <MapPin class="h-4 w-4 shrink-0 mt-0.5 text-blue-500" />
                    <span>{{ formatAddress(shop) }}</span>
                </div>
                <div v-if="shop.phone" class="flex items-center gap-2 text-gray-600 text-sm">
                    <Phone class="h-4 w-4 shrink-0 text-blue-500" />
                    <a :href="`tel:${shop.phone}`" class="hover:text-blue-600 transition">{{ shop.phone }}</a>
                </div>
            </div>

            <!-- ── Services ───────────────────────────────────── -->
            <div>
                <h2 class="text-base font-bold text-gray-800 mb-3">Available Services</h2>

                <div v-if="services.length === 0" class="flex flex-col items-center py-12 text-gray-400">
                    <Package class="h-10 w-10 mb-2 opacity-40" />
                    <p class="text-sm">No services available at this time.</p>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="(svc, idx) in services"
                        :key="svc.id"
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
                    >
                        <div class="p-4">
                            <div class="flex items-start gap-3">
                                <!-- Icon -->
                                <div :class="`h-10 w-10 rounded-xl flex items-center justify-center shrink-0 ${svcColour(idx)}`">
                                    <WashingMachine class="h-5 w-5" />
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="font-semibold text-gray-800 text-sm">{{ svc.service_name }}</p>
                                            <p v-if="svc.description" class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ svc.description }}</p>
                                        </div>
                                        <p class="text-base font-extrabold text-blue-600 shrink-0">{{ formatServicePrice(svc) }}</p>
                                    </div>

                                    <div class="flex items-center gap-2 mt-2.5">
                                        <div v-if="svc.estimated_hours" class="flex items-center gap-1 text-[11px] text-gray-400 bg-gray-50 px-2 py-1 rounded-full">
                                            <Clock class="h-3 w-3" />
                                            ~{{ svc.estimated_hours }}h
                                        </div>
                                        <div class="flex items-center gap-1 text-[11px] text-amber-600 bg-amber-50 px-2 py-1 rounded-full">
                                            <Info class="h-3 w-3" />
                                            Final price based on actual weight
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order CTA bar -->
                        <button
                            class="w-full flex items-center justify-between px-4 py-3 bg-blue-600 hover:bg-blue-700 transition active:bg-blue-800 text-white"
                            @click="openOrder(svc)"
                        >
                            <span class="text-sm font-semibold">Place Order</span>
                            <ChevronRight class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- ── Order Dialog ────────────────────────────────────── -->
        <Dialog v-model:open="isOrderOpen">
            <DialogContent class="max-w-sm mx-4 rounded-2xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle class="text-base">
                        Place Order — {{ selectedService?.service_name }}
                    </DialogTitle>
                </DialogHeader>

                <div class="space-y-4 py-2">

                    <!-- Shop reminder -->
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
                            <button type="button"
                                class="py-2.5 rounded-xl border-2 text-sm font-medium transition"
                                :class="form.pickup_type === 'walk_in'
                                    ? 'border-blue-600 bg-blue-50 text-blue-700'
                                    : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                                @click="form.pickup_type = 'walk_in'"
                            >Walk-in</button>
                            <button type="button"
                                class="py-2.5 rounded-xl border-2 text-sm font-medium transition"
                                :class="form.pickup_type === 'pickup'
                                    ? 'border-blue-600 bg-blue-50 text-blue-700'
                                    : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                                @click="form.pickup_type = 'pickup'"
                            >Pickup</button>
                        </div>
                    </div>

                    <!-- Address (pickup only) -->
                    <div v-if="form.pickup_type === 'pickup'" class="space-y-1.5">
                        <label class="text-sm font-medium">Pickup Address <span class="text-red-500">*</span></label>
                        <Input v-model="form.customer_address" placeholder="Your complete address"
                            :class="errors.customer_address ? 'border-red-400' : ''" />
                        <p v-if="errors.customer_address" class="text-xs text-red-500">{{ errors.customer_address }}</p>
                    </div>

                    <!-- Payment method -->
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium">Payment Method <span class="text-red-500">*</span></label>
                        <div class="space-y-2">
                            <button
                                v-for="opt in paymentOptions"
                                :key="opt.value"
                                type="button"
                                class="w-full flex items-center gap-3 p-3 rounded-xl border-2 text-left transition"
                                :class="form.payment_method === opt.value
                                    ? 'border-blue-600 bg-blue-50'
                                    : 'border-gray-200 hover:border-gray-300'"
                                @click="form.payment_method = opt.value as 'cash' | 'gcash' | 'maya'"
                            >
                                <component :is="opt.icon" class="h-4 w-4 shrink-0"
                                    :class="form.payment_method === opt.value ? 'text-blue-600' : 'text-gray-400'" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium" :class="form.payment_method === opt.value ? 'text-blue-700' : 'text-gray-700'">{{ opt.label }}</p>
                                    <p class="text-xs text-gray-400 truncate">{{ opt.note }}</p>
                                </div>
                            </button>
                        </div>
                        <p v-if="errors.payment_method" class="text-xs text-red-500">{{ errors.payment_method }}</p>
                    </div>

                    <!-- Special instructions -->
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium">Special Instructions <span class="text-xs text-gray-400">(optional)</span></label>
                        <Input v-model="form.special_instructions" placeholder="e.g. Separate whites, use fabric conditioner..." />
                    </div>

                    <!-- Disclaimer -->
                    <div class="bg-amber-50 rounded-xl p-3 text-xs text-amber-700">
                        <strong>Note:</strong> Estimated total is indicative — the shop will weigh your items and confirm the final price.
                        Payment via <strong>{{ selectedPayment?.label }}</strong> will be collected once the shop sets the final amount.
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
