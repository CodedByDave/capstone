<script setup lang="ts">
import RatingSummary from '@/components/customer/RatingSummary.vue';
import { Input } from '@/components/ui/input';
import UserLayout from '@/layouts/user/UserLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import {
    ChevronRight,
    Loader2,
    MapPin,
    Navigation,
    Phone,
    Search,
    WashingMachine,
} from 'lucide-vue-next';
import { ref } from 'vue';

// ─── Types ────────────────────────────────────────────────────────────────────

interface ShopService {
    id: number;
    service_name: string;
    pricing_model: 'per_kg' | 'per_bundle';
    price_per_kg: string | null;
    bundle_price: string | null;
    bundle_weight_kg: string | null;
}

interface Shop {
    id: number;
    shop_name: string;
    phone: string | null;
    block_street: string | null;
    municipality: string;
    barangay: string;
    cover_photo: string | null;
    latitude: number | null;
    longitude: number | null;
    distance_km: number | null;
    starting_price: number | null;
    starting_price_unit: 'kg' | 'bundle' | null;
    services_count: number;
    rating_avg: number | null;
    rating_count: number;
    services: ShopService[];
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    shops: Shop[];
    userLat: number | null;
    userLng: number | null;
    search: string;
}>();

// ─── Search ───────────────────────────────────────────────────────────────────

const searchQuery = ref(props.search ?? '');

function applySearch() {
    router.get(
        '/user/shops',
        {
            search: searchQuery.value || undefined,
            lat: props.userLat ?? undefined,
            lng: props.userLng ?? undefined,
        },
        { preserveState: true, replace: true },
    );
}

// ─── Re-locate ────────────────────────────────────────────────────────────────

const locating = ref(false);

function locateMe() {
    if (!navigator.geolocation) return;
    locating.value = true;
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            locating.value = false;
            router.get(
                '/user/shops',
                {
                    lat: pos.coords.latitude,
                    lng: pos.coords.longitude,
                    search: searchQuery.value || undefined,
                },
                { replace: true },
            );
        },
        () => {
            locating.value = false;
        },
        { timeout: 8000 },
    );
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatAddress(shop: Shop) {
    const parts = [shop.block_street, shop.barangay, shop.municipality].filter(
        Boolean,
    );
    return parts.join(', ');
}

function formatPrice(shop: Shop) {
    if (shop.starting_price)
        return `From ₱${Number(shop.starting_price).toFixed(0)}${shop.starting_price_unit === 'bundle' ? ' / bundle' : '/kg'}`;
    return shop.services_count > 0
        ? `${shop.services_count} service${shop.services_count !== 1 ? 's' : ''}`
        : 'No services';
}

// Fallback gradient colour when there's no photo
const gradients = [
    'from-blue-500 to-indigo-600',
    'from-teal-500 to-cyan-600',
    'from-violet-500 to-purple-600',
    'from-rose-500 to-pink-600',
    'from-amber-500 to-orange-500',
];
function shopGradient(id: number) {
    return gradients[id % gradients.length];
}
</script>

<template>
    <Head title="Shops" />
    <UserLayout>
        <div class="pb-6">
            <!-- ── Sticky search bar ──────────────────────────── -->
            <div
                class="sticky top-0 z-10 space-y-3 border-b border-gray-100 bg-white px-4 pt-4 pb-3"
            >
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
                        />
                        <Input
                            v-model="searchQuery"
                            placeholder="Search shops or location..."
                            class="h-11 rounded-xl border-gray-200 bg-gray-50 pr-4 pl-9"
                            @keyup.enter="applySearch"
                        />
                    </div>
                    <button
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white transition hover:bg-blue-700 active:scale-95 disabled:opacity-60"
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
                    <div
                        v-if="userLat && userLng"
                        class="flex items-center gap-1.5 text-xs text-blue-600"
                    >
                        <MapPin class="h-3 w-3 shrink-0" />
                        <span>Sorted by distance</span>
                    </div>
                    <div v-else class="text-xs text-gray-400">All shops</div>
                    <span class="text-xs font-medium text-gray-500">
                        {{ shops.length }} shop{{
                            shops.length !== 1 ? 's' : ''
                        }}
                        <span v-if="search"> for "{{ search }}"</span>
                    </span>
                </div>
            </div>

            <!-- ── Empty state ────────────────────────────────── -->
            <div
                v-if="shops.length === 0"
                class="flex flex-col items-center px-6 py-20 text-center text-gray-400"
            >
                <WashingMachine class="mb-4 h-14 w-14 opacity-20" />
                <p class="text-base font-semibold text-gray-500">
                    No shops found
                </p>
                <p class="mt-1 text-sm">
                    Try a different search or broaden your area
                </p>
                <button
                    class="mt-5 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 active:scale-95"
                    @click="router.get('/user/shops')"
                >
                    Clear filters
                </button>
            </div>

            <!-- ── Shop cards ─────────────────────────────────── -->
            <div v-else class="space-y-4 px-4 pt-4">
                <button
                    v-for="shop in shops"
                    :key="shop.id"
                    class="w-full overflow-hidden rounded-2xl border border-gray-100 bg-white text-left shadow-sm transition-shadow hover:shadow-lg active:scale-[0.99]"
                    @click="
                        router.visit(
                            `/user/shops/${shop.id}${userLat ? `?lat=${userLat}&lng=${userLng}` : ''}`,
                        )
                    "
                >
                    <!-- Cover photo -->
                    <div class="relative h-36 overflow-hidden">
                        <img
                            v-if="shop.cover_photo"
                            :src="shop.cover_photo"
                            :alt="shop.shop_name"
                            class="h-full w-full object-cover"
                        />
                        <div
                            v-else
                            :class="`h-full w-full bg-gradient-to-br ${shopGradient(shop.id)} flex items-center justify-center`"
                        >
                            <WashingMachine class="h-12 w-12 text-white/60" />
                        </div>

                        <!-- Dark gradient overlay -->
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"
                        />

                        <!-- Distance badge -->
                        <div
                            v-if="shop.distance_km !== null"
                            class="absolute top-3 right-3 flex items-center gap-1 rounded-full bg-black/50 px-2.5 py-1 text-[11px] font-bold text-white backdrop-blur-sm"
                        >
                            <MapPin class="h-3 w-3" />
                            {{ shop.distance_km }} km
                        </div>

                        <!-- Shop name overlay -->
                        <div class="absolute right-0 bottom-0 left-0 px-4 py-3">
                            <p
                                class="text-base leading-tight font-bold text-white drop-shadow"
                            >
                                {{ shop.shop_name }}
                            </p>
                        </div>
                    </div>

                    <!-- Card body -->
                    <div class="px-4 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1 space-y-1.5">
                                <!-- Address -->
                                <div
                                    class="flex items-start gap-1.5 text-xs text-gray-500"
                                >
                                    <MapPin
                                        class="mt-0.5 h-3.5 w-3.5 shrink-0 text-gray-400"
                                    />
                                    <span class="line-clamp-1">{{
                                        formatAddress(shop)
                                    }}</span>
                                </div>
                                <!-- Phone -->
                                <div
                                    v-if="shop.phone"
                                    class="flex items-center gap-1.5 text-xs text-gray-500"
                                >
                                    <Phone
                                        class="h-3.5 w-3.5 shrink-0 text-gray-400"
                                    />
                                    <span>{{ shop.phone }}</span>
                                </div>
                                <!-- Chips row -->
                                <div class="flex flex-wrap gap-1.5 pt-1">
                                    <span
                                        class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700"
                                    >
                                        {{ formatPrice(shop) }}
                                    </span>
                                    <span
                                        class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-600"
                                    >
                                        {{ shop.services_count }} service{{
                                            shop.services_count !== 1 ? 's' : ''
                                        }}
                                    </span>
                                    <RatingSummary
                                        :average="shop.rating_avg"
                                        :count="shop.rating_count"
                                        class="self-center"
                                    />
                                </div>
                            </div>

                            <!-- CTA -->
                            <div
                                class="flex shrink-0 flex-col items-center justify-center gap-1 self-center"
                            >
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 shadow-sm"
                                >
                                    <ChevronRight class="h-5 w-5 text-white" />
                                </div>
                                <span
                                    class="text-[10px] font-semibold text-blue-600"
                                    >Order</span
                                >
                            </div>
                        </div>
                    </div>
                </button>
            </div>
        </div>
    </UserLayout>
</template>
