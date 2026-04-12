<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import UserLayout from '@/layouts/user/UserLayout.vue'
import { Input } from '@/components/ui/input'
import {
    MapPin, Search, Navigation, Loader2,
    ChevronRight, Phone, WashingMachine,
} from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface ShopService {
    id: number
    service_name: string
    pricing_model: 'per_kg' | 'per_bundle'
    price_per_kg: string | null
    bundle_price: string | null
    bundle_weight_kg: string | null
}

interface Shop {
    id: number
    shop_name: string
    branch_name: string | null
    phone: string | null
    block_street: string | null
    municipality: string
    barangay: string
    cover_photo: string | null
    latitude: number | null
    longitude: number | null
    distance_km: number | null
    starting_price: number | null
    services_count: number
    services: ShopService[]
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    shops: Shop[]
    userLat: number | null
    userLng: number | null
    search: string
}>()

// ─── Search ───────────────────────────────────────────────────────────────────

const searchQuery = ref(props.search ?? '')

function applySearch() {
    router.get('/user/shops', {
        search: searchQuery.value || undefined,
        lat: props.userLat ?? undefined,
        lng: props.userLng ?? undefined,
    }, { preserveState: true, replace: true })
}

// ─── Re-locate ────────────────────────────────────────────────────────────────

const locating = ref(false)

function locateMe() {
    if (!navigator.geolocation) return
    locating.value = true
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            locating.value = false
            router.get('/user/shops', {
                lat:    pos.coords.latitude,
                lng:    pos.coords.longitude,
                search: searchQuery.value || undefined,
            }, { replace: true })
        },
        () => { locating.value = false },
        { timeout: 8000 },
    )
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatAddress(shop: Shop) {
    const parts = [shop.block_street, shop.barangay, shop.municipality].filter(Boolean)
    return parts.join(', ')
}

function formatPrice(shop: Shop) {
    if (shop.starting_price) return `From ₱${Number(shop.starting_price).toFixed(0)}/kg`
    return shop.services_count > 0 ? `${shop.services_count} service${shop.services_count !== 1 ? 's' : ''}` : 'No services'
}

// Fallback gradient colour when there's no photo
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
    <Head title="Shops" />
    <UserLayout>
        <div class="pb-6">

            <!-- ── Sticky search bar ──────────────────────────── -->
            <div class="sticky top-0 z-10 bg-white border-b border-gray-100 px-4 pt-4 pb-3 space-y-3">
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                        <Input
                            v-model="searchQuery"
                            placeholder="Search shops or location..."
                            class="pl-9 pr-4 h-11 rounded-xl bg-gray-50 border-gray-200"
                            @keyup.enter="applySearch"
                        />
                    </div>
                    <button
                        class="h-11 w-11 flex items-center justify-center rounded-xl bg-blue-600 text-white shrink-0 hover:bg-blue-700 transition active:scale-95 disabled:opacity-60"
                        :disabled="locating"
                        title="Use my location"
                        @click="locateMe"
                    >
                        <Loader2 v-if="locating" class="h-5 w-5 animate-spin" />
                        <Navigation v-else class="h-5 w-5" />
                    </button>
                </div>

                <!-- Location / count row -->
                <div class="flex items-center justify-between">
                    <div v-if="userLat && userLng" class="flex items-center gap-1.5 text-xs text-blue-600">
                        <MapPin class="h-3 w-3 shrink-0" />
                        <span>Sorted by distance</span>
                    </div>
                    <div v-else class="text-xs text-gray-400">All shops</div>
                    <span class="text-xs text-gray-500 font-medium">
                        {{ shops.length }} shop{{ shops.length !== 1 ? 's' : '' }}
                        <span v-if="search"> for "{{ search }}"</span>
                    </span>
                </div>
            </div>

            <!-- ── Empty state ────────────────────────────────── -->
            <div v-if="shops.length === 0" class="flex flex-col items-center py-20 px-6 text-center text-gray-400">
                <WashingMachine class="h-14 w-14 mb-4 opacity-20" />
                <p class="text-base font-semibold text-gray-500">No shops found</p>
                <p class="text-sm mt-1">Try a different search or broaden your area</p>
                <button
                    class="mt-5 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition active:scale-95"
                    @click="router.get('/user/shops')"
                >
                    Clear filters
                </button>
            </div>

            <!-- ── Shop cards ─────────────────────────────────── -->
            <div v-else class="px-4 pt-4 space-y-4">
                <button
                    v-for="shop in shops"
                    :key="shop.id"
                    class="w-full text-left bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow active:scale-[0.99]"
                    @click="router.visit(`/user/shops/${shop.id}${userLat ? `?lat=${userLat}&lng=${userLng}` : ''}`)"
                >
                    <!-- Cover photo -->
                    <div class="relative h-36 overflow-hidden">
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
                            <WashingMachine class="h-12 w-12 text-white/60" />
                        </div>

                        <!-- Dark gradient overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent" />

                        <!-- Distance badge -->
                        <div
                            v-if="shop.distance_km !== null"
                            class="absolute top-3 right-3 bg-black/50 backdrop-blur-sm text-white text-[11px] font-bold px-2.5 py-1 rounded-full flex items-center gap-1"
                        >
                            <MapPin class="h-3 w-3" />
                            {{ shop.distance_km }} km
                        </div>

                        <!-- Shop name overlay -->
                        <div class="absolute bottom-0 left-0 right-0 px-4 py-3">
                            <p class="text-white font-bold text-base leading-tight drop-shadow">{{ shop.shop_name }}</p>
                            <p v-if="shop.branch_name" class="text-white/80 text-xs mt-0.5">{{ shop.branch_name }}</p>
                        </div>
                    </div>

                    <!-- Card body -->
                    <div class="px-4 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1 space-y-1.5">
                                <!-- Address -->
                                <div class="flex items-start gap-1.5 text-gray-500 text-xs">
                                    <MapPin class="h-3.5 w-3.5 shrink-0 mt-0.5 text-gray-400" />
                                    <span class="line-clamp-1">{{ formatAddress(shop) }}</span>
                                </div>
                                <!-- Phone -->
                                <div v-if="shop.phone" class="flex items-center gap-1.5 text-gray-500 text-xs">
                                    <Phone class="h-3.5 w-3.5 shrink-0 text-gray-400" />
                                    <span>{{ shop.phone }}</span>
                                </div>
                                <!-- Chips row -->
                                <div class="flex flex-wrap gap-1.5 pt-1">
                                    <span class="bg-blue-50 text-blue-700 text-[11px] font-semibold px-2.5 py-1 rounded-full">
                                        {{ formatPrice(shop) }}
                                    </span>
                                    <span class="bg-gray-100 text-gray-600 text-[11px] font-medium px-2.5 py-1 rounded-full">
                                        {{ shop.services_count }} service{{ shop.services_count !== 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </div>

                            <!-- CTA -->
                            <div class="flex flex-col items-center justify-center gap-1 shrink-0 self-center">
                                <div class="h-10 w-10 rounded-xl bg-blue-600 flex items-center justify-center shadow-sm">
                                    <ChevronRight class="h-5 w-5 text-white" />
                                </div>
                                <span class="text-[10px] font-semibold text-blue-600">Order</span>
                            </div>
                        </div>
                    </div>
                </button>
            </div>

        </div>
    </UserLayout>
</template>
