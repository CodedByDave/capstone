<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import UserLayout from '@/layouts/user/UserLayout.vue'
import { type AppPageProps } from '@/types'
import {
    MapPin, ChevronRight, ShoppingBag, ClipboardList,
    Loader2, Package, WashingMachine, Navigation, X, Phone,
} from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface RecentOrder {
    id: number
    order_number: string
    shop_name: string
    service_name: string
    status: string
    payment_status: string
    total_amount: number
    created_at: string
}

interface NearbyShop {
    id: number
    shop_name: string
    branch_name: string | null
    municipality: string
    barangay: string
    cover_photo: string | null
    distance_km: number
    starting_price: string | null
    services_count: number
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    recentOrders: RecentOrder[]
    totalOrders: number
    nearbyShops: NearbyShop[]
    userLat: number | null
    userLng: number | null
}>()

// ─── Auth ─────────────────────────────────────────────────────────────────────

const page = usePage<AppPageProps>()
const user = computed(() => page.props.auth.user)
const greeting = computed(() => {
    const h = new Date().getHours()
    if (h < 12) return 'Good morning'
    if (h < 17) return 'Good afternoon'
    return 'Good evening'
})

// ─── Flash toast ──────────────────────────────────────────────────────────────

onMounted(() => {
    const flashToast = page.props.toast as { type: string; message: string } | undefined
    if (flashToast) {
        switch (flashToast.type) {
            case 'success': toast.success(flashToast.message); break
            case 'error':   toast.error(flashToast.message);   break
            default:        toast(flashToast.message)
        }
    }

    initLocation()
})

// ─── Location ─────────────────────────────────────────────────────────────────

const LOCATION_KEY  = 'lh_user_location'
const DENIED_KEY    = 'lh_location_denied'
const CACHE_MS      = 30 * 60 * 1000   // 30 minutes

const locating           = ref(false)
const locationDenied     = ref(false)
const showLocationBanner = ref(false)

function initLocation() {
    // Already loaded with coords — nothing to do
    if (props.userLat && props.userLng) return

    // Previously denied — show the "how-to-enable" hint instead of banner
    if (localStorage.getItem(DENIED_KEY)) {
        locationDenied.value = true
        return
    }

    // Try using a cached position
    const raw = localStorage.getItem(LOCATION_KEY)
    if (raw) {
        try {
            const { lat, lng, ts } = JSON.parse(raw)
            if (Date.now() - ts < CACHE_MS) {
                router.get('/user/dashboard', { lat, lng }, { replace: true, preserveScroll: true })
                return
            }
        } catch { /* ignore */ }
    }

    // No cached position — show the permission banner
    showLocationBanner.value = true
}

function requestLocation() {
    if (!navigator.geolocation) {
        locationDenied.value = true
        showLocationBanner.value = false
        return
    }
    locating.value = true
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const lat = pos.coords.latitude
            const lng = pos.coords.longitude
            localStorage.setItem(LOCATION_KEY, JSON.stringify({ lat, lng, ts: Date.now() }))
            localStorage.removeItem(DENIED_KEY)
            locating.value = false
            showLocationBanner.value = false
            router.get('/user/dashboard', { lat, lng }, { replace: true })
        },
        () => {
            locating.value = false
            localStorage.setItem(DENIED_KEY, '1')
            locationDenied.value = true
            showLocationBanner.value = false
        },
        { timeout: 10000, maximumAge: 0 },
    )
}

function dismissBanner() {
    showLocationBanner.value = false
}

function retryLocation() {
    localStorage.removeItem(DENIED_KEY)
    locationDenied.value = false
    showLocationBanner.value = true
}

function refreshLocation() {
    localStorage.removeItem(LOCATION_KEY)
    localStorage.removeItem(DENIED_KEY)
    locationDenied.value = false
    showLocationBanner.value = false
    requestLocation()
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

const statusColors: Record<string, string> = {
    pending:     'bg-yellow-100 text-yellow-700',
    in_progress: 'bg-blue-100 text-blue-700',
    completed:   'bg-green-100 text-green-700',
}

const statusLabel: Record<string, string> = {
    pending:     'Pending',
    in_progress: 'In Progress',
    completed:   'Completed',
}

function currency(val: number) {
    return `₱${val.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
}

function goToShop(shop: NearbyShop) {
    router.visit(`/user/shops/${shop.id}${props.userLat ? `?lat=${props.userLat}&lng=${props.userLng}` : ''}`)
}

const gradients = [
    'from-blue-500 to-indigo-600',
    'from-teal-500 to-cyan-600',
    'from-violet-500 to-purple-600',
    'from-rose-500 to-pink-600',
    'from-amber-500 to-orange-500',
]
function shopGradient(id: number) {
    return gradients[id % gradients.length]
}
</script>

<template>
    <Head title="Home" />
    <UserLayout>

        <div class="px-4 pt-4 pb-6 space-y-6">

            <!-- ── Greeting ───────────────────────────────────── -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-5 text-white shadow-md">
                <p class="text-sm opacity-80">{{ greeting }},</p>
                <h2 class="text-xl font-bold mt-0.5">{{ user.name.split(' ')[0] }}! 👋</h2>
                <p class="text-sm opacity-80 mt-1">Find laundry shops near you</p>

                <div class="flex items-center gap-3 mt-4">
                    <div class="flex-1 bg-white/10 rounded-xl px-3 py-2.5 flex items-center gap-2">
                        <ShoppingBag class="h-4 w-4 opacity-80" />
                        <span class="text-sm font-medium">{{ totalOrders }} order{{ totalOrders !== 1 ? 's' : '' }} placed</span>
                    </div>
                    <!-- Refresh location -->
                    <button
                        v-if="userLat && userLng"
                        class="bg-white/10 rounded-xl px-3 py-2.5 flex items-center gap-1.5 hover:bg-white/20 transition active:scale-95"
                        :disabled="locating"
                        title="Refresh location"
                        @click="refreshLocation"
                    >
                        <Loader2 v-if="locating" class="h-4 w-4 animate-spin" />
                        <Navigation v-else class="h-4 w-4" />
                        <span class="text-xs font-medium">{{ locating ? 'Locating…' : 'Near me' }}</span>
                    </button>
                </div>
            </div>

            <!-- ── Location Permission Banner ────────────────── -->
            <div
                v-if="showLocationBanner"
                class="relative bg-blue-50 border border-blue-200 rounded-2xl p-4"
            >
                <button
                    class="absolute top-3 right-3 text-blue-400 hover:text-blue-600"
                    @click="dismissBanner"
                >
                    <X class="h-4 w-4" />
                </button>
                <div class="flex items-start gap-3">
                    <div class="h-10 w-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                        <MapPin class="h-5 w-5 text-blue-600" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-blue-900">See shops near you</p>
                        <p class="text-xs text-blue-700 mt-0.5">Allow location access to get recommendations for nearby laundry shops.</p>
                        <button
                            class="mt-3 flex items-center gap-1.5 bg-blue-600 text-white text-xs font-semibold px-4 py-2 rounded-lg hover:bg-blue-700 transition active:scale-95 disabled:opacity-60"
                            :disabled="locating"
                            @click="requestLocation"
                        >
                            <Loader2 v-if="locating" class="h-3.5 w-3.5 animate-spin" />
                            <Navigation v-else class="h-3.5 w-3.5" />
                            {{ locating ? 'Getting location…' : 'Allow Location' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── Location Denied Banner ─────────────────────── -->
            <div
                v-else-if="locationDenied"
                class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3"
            >
                <div class="h-10 w-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                    <MapPin class="h-5 w-5 text-amber-600" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-amber-900">Location access blocked</p>
                    <p class="text-xs text-amber-700 mt-0.5">
                        To see nearby shops, enable location in your browser settings, then tap retry.
                    </p>
                    <button
                        class="mt-2 text-xs font-semibold text-amber-700 underline underline-offset-2"
                        @click="retryLocation"
                    >
                        Retry
                    </button>
                </div>
            </div>

            <!-- ── Nearby Shops ───────────────────────────────── -->
            <div v-if="nearbyShops.length > 0">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-1.5">
                        <MapPin class="h-4 w-4 text-blue-500" />
                        Nearby Shops
                    </h3>
                    <button
                        class="text-xs text-blue-600 font-medium hover:underline"
                        @click="router.visit(`/user/shops?lat=${userLat}&lng=${userLng}`)"
                    >
                        See all
                    </button>
                </div>

                <!-- Horizontal scroll on mobile -->
                <div class="flex gap-3 overflow-x-auto pb-1 -mx-4 px-4 snap-x snap-mandatory">
                    <button
                        v-for="shop in nearbyShops"
                        :key="shop.id"
                        class="shrink-0 w-52 snap-start text-left bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow active:scale-[0.98]"
                        @click="goToShop(shop)"
                    >
                        <!-- Cover photo -->
                        <div class="relative h-28 overflow-hidden">
                            <img
                                v-if="shop.cover_photo"
                                :src="shop.cover_photo"
                                :alt="shop.shop_name"
                                class="w-full h-full object-cover"
                            />
                            <div
                                v-else
                                :class="`w-full h-full bg-gradient-to-br ${shopGradient(shop.id)} flex items-center justify-center`"
                            >
                                <WashingMachine class="h-9 w-9 text-white/50" />
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-transparent to-transparent" />
                            <!-- Distance pill -->
                            <div class="absolute top-2 right-2 bg-black/50 backdrop-blur-sm text-white text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-0.5">
                                <MapPin class="h-2.5 w-2.5" />
                                {{ shop.distance_km }} km
                            </div>
                            <!-- Name overlay -->
                            <div class="absolute bottom-2 left-3 right-3">
                                <p class="text-white text-xs font-bold leading-tight line-clamp-1 drop-shadow">{{ shop.shop_name }}</p>
                            </div>
                        </div>

                        <!-- Card body -->
                        <div class="px-3 py-2.5">
                            <p class="text-[11px] text-gray-500 flex items-center gap-1">
                                <MapPin class="h-3 w-3 shrink-0 text-gray-400" />
                                <span class="line-clamp-1">{{ shop.barangay }}, {{ shop.municipality }}</span>
                            </p>
                            <div class="flex items-center justify-between mt-2">
                                <span v-if="shop.starting_price" class="bg-blue-50 text-blue-700 text-[10px] font-semibold px-2 py-0.5 rounded-full">
                                    From ₱{{ Number(shop.starting_price).toFixed(0) }}/kg
                                </span>
                                <span v-else class="text-[10px] text-gray-400">{{ shop.services_count }} services</span>
                                <ChevronRight class="h-3.5 w-3.5 text-gray-300 shrink-0" />
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- ── Browse Shops (fallback when no nearby) ─────── -->
            <div v-if="!userLat || nearbyShops.length === 0">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Find a Shop</h3>
                <button
                    class="w-full py-3 text-sm text-gray-500 hover:text-gray-700 font-medium underline-offset-2 hover:underline"
                    @click="router.visit('/user/shops')"
                >
                    Browse all shops →
                </button>
            </div>

            <!-- ── Quick Actions ──────────────────────────────── -->
            <div class="grid grid-cols-2 gap-3">
                <button
                    class="flex flex-col items-center gap-2 py-4 rounded-xl bg-white border border-gray-100 shadow-sm hover:shadow-md transition-shadow active:scale-95"
                    @click="router.visit('/user/shops')"
                >
                    <div class="h-10 w-10 rounded-xl bg-blue-100 flex items-center justify-center">
                        <MapPin class="h-5 w-5 text-blue-600" />
                    </div>
                    <span class="text-xs font-medium text-gray-700">Browse Shops</span>
                </button>
                <button
                    class="flex flex-col items-center gap-2 py-4 rounded-xl bg-white border border-gray-100 shadow-sm hover:shadow-md transition-shadow active:scale-95"
                    @click="router.visit('/user/orders')"
                >
                    <div class="h-10 w-10 rounded-xl bg-green-100 flex items-center justify-center">
                        <ClipboardList class="h-5 w-5 text-green-600" />
                    </div>
                    <span class="text-xs font-medium text-gray-700">My Orders</span>
                </button>
            </div>

            <!-- ── Recent Orders ──────────────────────────────── -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700">Recent Orders</h3>
                    <button
                        class="text-xs text-blue-600 font-medium hover:underline"
                        @click="router.visit('/user/orders')"
                    >
                        View all
                    </button>
                </div>

                <div v-if="recentOrders.length === 0" class="flex flex-col items-center py-10 text-gray-400">
                    <Package class="h-10 w-10 mb-2 opacity-40" />
                    <p class="text-sm">No orders yet</p>
                    <p class="text-xs mt-1">Place your first laundry order!</p>
                </div>

                <div v-else class="space-y-2">
                    <button
                        v-for="order in recentOrders"
                        :key="order.id"
                        class="w-full flex items-center gap-3 p-3.5 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition text-left active:scale-[0.98]"
                        @click="router.visit(`/user/orders/${order.id}`)"
                    >
                        <div class="h-10 w-10 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
                            <ClipboardList class="h-5 w-5 text-gray-500" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ order.shop_name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ order.service_name }} · {{ order.order_number }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-1 shrink-0">
                            <span
                                class="text-[10px] font-semibold px-2 py-0.5 rounded-full"
                                :class="statusColors[order.status] ?? 'bg-gray-100 text-gray-600'"
                            >
                                {{ statusLabel[order.status] ?? order.status }}
                            </span>
                            <span class="text-xs font-bold text-gray-700">{{ currency(order.total_amount) }}</span>
                        </div>
                        <ChevronRight class="h-4 w-4 text-gray-300 shrink-0" />
                    </button>
                </div>
            </div>

        </div>
    </UserLayout>
</template>
