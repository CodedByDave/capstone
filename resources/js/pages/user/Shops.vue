<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import UserLayout from '@/layouts/user/UserLayout.vue'
import { Input } from '@/components/ui/input'
import {
    MapPin, Search, Navigation, Loader2,
    ChevronRight, Phone, Star, WashingMachine,
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
</script>

<template>
    <Head title="Shops" />
    <UserLayout>
        <div class="px-4 pt-4 pb-6 space-y-4">

            <!-- ── Search Bar ─────────────────────────────────── -->
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search shops or location..."
                        class="pl-9 pr-4 h-11 rounded-xl"
                        @keyup.enter="applySearch"
                    />
                </div>
                <button
                    class="h-11 w-11 flex items-center justify-center rounded-xl bg-blue-600 text-white shrink-0 hover:bg-blue-700 transition active:scale-95"
                    :disabled="locating"
                    title="Use my location"
                    @click="locateMe"
                >
                    <Loader2 v-if="locating" class="h-5 w-5 animate-spin" />
                    <Navigation v-else class="h-5 w-5" />
                </button>
            </div>

            <!-- ── Location badge ─────────────────────────────── -->
            <div v-if="userLat && userLng" class="flex items-center gap-1.5 text-xs text-blue-600">
                <MapPin class="h-3.5 w-3.5 shrink-0" />
                <span>Showing shops sorted by distance from your location</span>
            </div>

            <!-- ── Count ──────────────────────────────────────── -->
            <p class="text-xs text-gray-500">
                {{ shops.length }} shop{{ shops.length !== 1 ? 's' : '' }} found
                <span v-if="search"> for "{{ search }}"</span>
            </p>

            <!-- ── Shops List ─────────────────────────────────── -->
            <div v-if="shops.length === 0" class="flex flex-col items-center py-16 text-gray-400">
                <WashingMachine class="h-12 w-12 mb-3 opacity-30" />
                <p class="text-sm font-medium">No shops found</p>
                <p class="text-xs mt-1">Try a different search or location</p>
                <button
                    class="mt-4 text-sm text-blue-600 font-medium hover:underline"
                    @click="router.get('/user/shops')"
                >
                    Clear search
                </button>
            </div>

            <div v-else class="space-y-3">
                <button
                    v-for="shop in shops"
                    :key="shop.id"
                    class="w-full text-left bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow active:scale-[0.99] overflow-hidden"
                    @click="router.visit(`/user/shops/${shop.id}${userLat ? `?lat=${userLat}&lng=${userLng}` : ''}`)"
                >
                    <!-- Shop header bar -->
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-500 px-4 py-2 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="h-8 w-8 rounded-lg bg-white/20 flex items-center justify-center">
                                <WashingMachine class="h-4 w-4 text-white" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-white leading-tight">{{ shop.shop_name }}</p>
                                <p v-if="shop.branch_name" class="text-[10px] text-white/80">{{ shop.branch_name }}</p>
                            </div>
                        </div>
                        <!-- Distance badge -->
                        <div v-if="shop.distance_km !== null" class="bg-white/20 rounded-full px-2.5 py-1 text-[10px] font-bold text-white">
                            {{ shop.distance_km }} km
                        </div>
                    </div>

                    <!-- Shop details -->
                    <div class="px-4 py-3">
                        <div class="flex items-start gap-1.5 text-gray-500 text-xs mb-2">
                            <MapPin class="h-3.5 w-3.5 shrink-0 mt-0.5" />
                            <span>{{ formatAddress(shop) }}</span>
                        </div>

                        <div v-if="shop.phone" class="flex items-center gap-1.5 text-gray-500 text-xs mb-3">
                            <Phone class="h-3.5 w-3.5 shrink-0" />
                            <span>{{ shop.phone }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <div class="bg-blue-50 text-blue-700 text-[10px] font-semibold px-2 py-1 rounded-full">
                                    {{ formatPrice(shop) }}
                                </div>
                                <div class="bg-gray-50 text-gray-600 text-[10px] font-medium px-2 py-1 rounded-full">
                                    {{ shop.services_count }} service{{ shop.services_count !== 1 ? 's' : '' }}
                                </div>
                            </div>
                            <div class="flex items-center gap-1 text-blue-600 text-xs font-semibold">
                                Order Now
                                <ChevronRight class="h-3.5 w-3.5" />
                            </div>
                        </div>
                    </div>
                </button>
            </div>

        </div>
    </UserLayout>
</template>
