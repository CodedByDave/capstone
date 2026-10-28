<script setup lang="ts">
import OrderRatingDialog from '@/components/customer/OrderRatingDialog.vue';
import RatingSummary from '@/components/customer/RatingSummary.vue';
import UserLayout from '@/layouts/user/UserLayout.vue';
import { type AppPageProps } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    ChevronRight,
    ClipboardList,
    MapPin,
    Package,
    QrCode,
    ShoppingBag,
    Star,
    WashingMachine,
} from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

// ─── Types ────────────────────────────────────────────────────────────────────

interface RecentOrder {
    id: number;
    payment_public_id: string;
    order_number: string;
    shop_name: string;
    service_name: string;
    status: string;
    payment_status: string;
    total_amount: number;
    created_at: string;
}

interface NearbyShop {
    id: number;
    shop_name: string;
    municipality: string;
    barangay: string;
    cover_photo: string | null;
    distance_km: number | null;
    starting_price: string | null;
    starting_price_unit: 'kg' | 'bundle' | null;
    services_count: number;
    rating_avg: number | null;
    rating_count: number;
}

interface RatingPrompt {
    payment_public_id: string;
    order_number: string;
    shop_name: string;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    recentOrders: RecentOrder[];
    totalOrders: number;
    nearbyShops: NearbyShop[];
    ratingPrompt: RatingPrompt | null;
}>();

// The "rate your recent order" popup; the card opens it.
const ratingOpen = ref(false);

// ─── Auth ─────────────────────────────────────────────────────────────────────

const page = usePage<AppPageProps>();
const user = computed(() => page.props.auth.user);
const greeting = computed(() => {
    const h = new Date().getHours();
    if (h < 12) return 'Good morning';
    if (h < 17) return 'Good afternoon';
    return 'Good evening';
});

// ─── Flash toast ──────────────────────────────────────────────────────────────

// A rating sent from the card on this page comes back to the same page without remounting it,
// so the first-load check below would never show its message.
watch(
    () => page.props.toast as { type: string; message: string } | undefined,
    (flash) => {
        if (!flash?.message) return;
        if (flash.type === 'success') toast.success(flash.message);
        else if (flash.type === 'error') toast.error(flash.message);
    },
);

onMounted(() => {
    const flashToast = page.props.toast as
        | { type: string; message: string }
        | undefined;
    if (flashToast) {
        switch (flashToast.type) {
            case 'success':
                toast.success(flashToast.message);
                break;
            case 'error':
                toast.error(flashToast.message);
                break;
            default:
                toast(flashToast.message);
        }
    }
});

// ─── Helpers ──────────────────────────────────────────────────────────────────

const statusColors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-700',
    in_progress: 'bg-blue-100 text-blue-700',
    completed: 'bg-green-100 text-green-700',
};

const statusLabel: Record<string, string> = {
    pending: 'Pending',
    in_progress: 'In Progress',
    completed: 'Completed',
};

function currency(val: number) {
    return `₱${val.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
}

function goToShop(shop: NearbyShop) {
    router.visit(`/user/shops/${shop.id}`);
}

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
    <Head title="Home" />
    <UserLayout>
        <div class="space-y-6 px-4 pt-4 pb-6">
            <!-- ── Greeting ───────────────────────────────────── -->
            <div
                class="rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 p-5 text-white shadow-md"
            >
                <p class="text-sm opacity-80">{{ greeting }},</p>
                <h2 class="mt-0.5 text-xl font-bold">
                    {{ user.name.split(' ')[0] }}! 👋
                </h2>
                <p class="mt-1 text-sm opacity-80">
                    Order from your laundry shop
                </p>

                <div class="mt-4 flex items-center gap-3">
                    <div
                        class="flex flex-1 items-center gap-2 rounded-xl bg-white/10 px-3 py-2.5"
                    >
                        <ShoppingBag class="h-4 w-4 opacity-80" />
                        <span class="text-sm font-medium"
                            >{{ totalOrders }} order{{
                                totalOrders !== 1 ? 's' : ''
                            }}
                            placed</span
                        >
                    </div>
                </div>
            </div>

            <!-- ── Rate your recent order ─────────────────────── -->
            <div
                v-if="ratingPrompt"
                class="flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm"
            >
                <Star
                    class="h-8 w-8 shrink-0 fill-amber-400 text-amber-400"
                    aria-hidden="true"
                />
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-gray-800">
                        How was your laundry?
                    </p>
                    <p class="truncate text-xs text-gray-600">
                        Rate order #{{ ratingPrompt.order_number }} at
                        {{ ratingPrompt.shop_name }}
                    </p>
                </div>
                <button
                    type="button"
                    class="shrink-0 rounded-xl bg-amber-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-amber-600"
                    @click="ratingOpen = true"
                >
                    Rate now
                </button>
            </div>
            <OrderRatingDialog
                v-if="ratingPrompt"
                v-model:open="ratingOpen"
                :payment-public-id="ratingPrompt.payment_public_id"
                :order-number="ratingPrompt.order_number"
                :shop-name="ratingPrompt.shop_name"
            />

            <!-- ── Nearby Shops ───────────────────────────────── -->
            <div v-if="nearbyShops.length > 0">
                <div class="mb-3 flex items-center justify-between">
                    <h3
                        class="flex items-center gap-1.5 text-sm font-semibold text-gray-700"
                    >
                        <MapPin class="h-4 w-4 text-blue-500" />
                        Your Shop
                    </h3>
                </div>

                <!-- Horizontal scroll on mobile -->
                <div
                    class="-mx-4 flex snap-x snap-mandatory gap-3 overflow-x-auto px-4 pb-1"
                >
                    <button
                        v-for="shop in nearbyShops"
                        :key="shop.id"
                        class="w-52 shrink-0 snap-start overflow-hidden rounded-2xl border border-gray-100 bg-white text-left shadow-sm transition-shadow hover:shadow-lg active:scale-[0.98]"
                        @click="goToShop(shop)"
                    >
                        <!-- Cover photo -->
                        <div class="relative h-28 overflow-hidden">
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
                                <WashingMachine class="h-9 w-9 text-white/50" />
                            </div>
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/55 via-transparent to-transparent"
                            />
                            <!-- Name overlay -->
                            <div class="absolute right-3 bottom-2 left-3">
                                <p
                                    class="line-clamp-1 text-xs leading-tight font-bold text-white drop-shadow"
                                >
                                    {{ shop.shop_name }}
                                </p>
                            </div>
                        </div>

                        <!-- Card body -->
                        <div class="px-3 py-2.5">
                            <p
                                class="flex items-center gap-1 text-[11px] text-gray-500"
                            >
                                <MapPin
                                    class="h-3 w-3 shrink-0 text-gray-400"
                                />
                                <span class="line-clamp-1"
                                    >{{ shop.barangay }},
                                    {{ shop.municipality }}</span
                                >
                            </p>
                            <div class="mt-2 flex items-center justify-between">
                                <span
                                    v-if="shop.starting_price"
                                    class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700"
                                >
                                    From ₱{{
                                        Number(shop.starting_price).toFixed(0)
                                    }}{{
                                        shop.starting_price_unit === 'bundle'
                                            ? ' / bundle'
                                            : '/kg'
                                    }}
                                </span>
                                <span v-else class="text-[10px] text-gray-400"
                                    >{{ shop.services_count }} services</span
                                >
                                <RatingSummary
                                    :average="shop.rating_avg"
                                    :count="shop.rating_count"
                                />
                                <ChevronRight
                                    class="h-3.5 w-3.5 shrink-0 text-gray-300"
                                />
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- ── No shop yet ────────────────────────────────── -->
            <div
                v-if="nearbyShops.length === 0"
                class="rounded-2xl bg-muted/50 px-4 py-8 text-center"
            >
                <QrCode
                    class="mx-auto mb-2 h-10 w-10 text-gray-400"
                    aria-hidden="true"
                />
                <p class="text-sm font-semibold text-gray-700">
                    Scan your laundry shop's QR code
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    Ask the shop for its QR code and scan it to connect your
                    account and start ordering.
                </p>
            </div>

            <!-- ── Quick Actions ──────────────────────────────── -->
            <div class="grid grid-cols-2 gap-3">
                <button
                    class="flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-white py-4 shadow-sm transition-shadow hover:shadow-md active:scale-95"
                    :disabled="nearbyShops.length === 0"
                    @click="router.visit('/user/shops')"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100"
                    >
                        <MapPin class="h-5 w-5 text-blue-600" />
                    </div>
                    <span class="text-xs font-medium text-gray-700"
                        >Order Laundry</span
                    >
                </button>
                <button
                    class="flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-white py-4 shadow-sm transition-shadow hover:shadow-md active:scale-95"
                    @click="router.visit('/user/orders')"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-100"
                    >
                        <ClipboardList class="h-5 w-5 text-green-600" />
                    </div>
                    <span class="text-xs font-medium text-gray-700"
                        >My Orders</span
                    >
                </button>
            </div>

            <!-- ── Recent Orders ──────────────────────────────── -->
            <div>
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700">
                        Recent Orders
                    </h3>
                    <button
                        class="text-xs font-medium text-blue-600 hover:underline"
                        @click="router.visit('/user/orders')"
                    >
                        View all
                    </button>
                </div>

                <div
                    v-if="recentOrders.length === 0"
                    class="flex flex-col items-center py-10 text-gray-400"
                >
                    <Package class="mb-2 h-10 w-10 opacity-40" />
                    <p class="text-sm">No orders yet</p>
                    <p class="mt-1 text-xs">Place your first laundry order!</p>
                </div>

                <div v-else class="space-y-2">
                    <button
                        v-for="order in recentOrders"
                        :key="order.id"
                        class="flex w-full items-center gap-3 rounded-xl border border-gray-100 bg-white p-3.5 text-left shadow-sm transition hover:shadow-md active:scale-[0.98]"
                        @click="
                            router.visit(
                                `/user/orders/${order.payment_public_id}`,
                            )
                        "
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-100"
                        >
                            <ClipboardList class="h-5 w-5 text-gray-500" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p
                                class="truncate text-sm font-semibold text-gray-800"
                            >
                                {{ order.shop_name }}
                            </p>
                            <p class="truncate text-xs text-gray-500">
                                {{ order.service_name }} ·
                                {{ order.order_number }}
                            </p>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1">
                            <span
                                class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                :class="
                                    statusColors[order.status] ??
                                    'bg-gray-100 text-gray-600'
                                "
                            >
                                {{ statusLabel[order.status] ?? order.status }}
                            </span>
                            <span class="text-xs font-bold text-gray-700">{{
                                currency(order.total_amount)
                            }}</span>
                        </div>
                        <ChevronRight class="h-4 w-4 shrink-0 text-gray-300" />
                    </button>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
