<script setup lang="ts">
import OrderRatingDialog from '@/components/customer/OrderRatingDialog.vue';
import UserLayout from '@/layouts/user/UserLayout.vue';
import { type AppPageProps } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ChevronRight, Package, QrCode, Star } from 'lucide-vue-next';
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

interface Promotion {
    id: number;
    name: string;
    headline: string;
    description: string | null;
    min_order_amount: number | null;
    ends_at: string | null;
}

interface RatingPrompt {
    payment_public_id: string;
    order_number: string;
    shop_name: string;
}

// ─── Props ────────────────────────────────────────────────────────────────────

defineProps<{
    recentOrders: RecentOrder[];
    totalOrders: number;
    nearbyShops: NearbyShop[];
    ratingPrompt: RatingPrompt | null;
    promotions: Promotion[];
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
    pending: 'bg-amber-50 text-amber-700',
    in_progress: 'bg-blue-50 text-blue-700',
    completed: 'bg-emerald-50 text-emerald-700',
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

// Promotions carousel: the dots follow the card closest to the left edge.
const promoTrack = ref<HTMLElement | null>(null);
const promoIndex = ref(0);

function onPromoScroll() {
    const track = promoTrack.value;
    if (!track || track.children.length === 0) return;
    const card = track.children[0] as HTMLElement;
    promoIndex.value = Math.round(track.scrollLeft / (card.offsetWidth + 12));
}

function promoDetail(promo: Promotion) {
    if (promo.description) return promo.description;
    if (promo.min_order_amount)
        return `Minimum order of ${currency(promo.min_order_amount)}.`;
    return 'Applied automatically on your next order.';
}
</script>

<template>
    <Head title="Home" />
    <UserLayout>
        <div class="px-5 pt-6 pb-8">
            <!-- ── Greeting ───────────────────────────────────── -->
            <h1
                class="font-display text-[28px] leading-9 font-bold text-slate-900"
            >
                {{ greeting }},
                <span class="block text-blue-600"
                    >{{ user.name.split(' ')[0] }}.</span
                >
            </h1>

            <!-- ── Rate your recent order ─────────────────────── -->
            <div
                v-if="ratingPrompt"
                class="mt-6 flex items-center gap-3 border-y border-amber-200 bg-amber-50 px-4 py-3"
            >
                <Star
                    class="h-6 w-6 shrink-0 fill-amber-400 text-amber-400"
                    aria-hidden="true"
                />
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-slate-800">
                        How was your laundry?
                    </p>
                    <p class="truncate text-xs text-slate-600">
                        Rate order #{{ ratingPrompt.order_number }} at
                        {{ ratingPrompt.shop_name }}
                    </p>
                </div>
                <button
                    type="button"
                    class="min-h-11 shrink-0 rounded-lg bg-amber-500 px-3 text-xs font-semibold text-white transition hover:bg-amber-600"
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

            <!-- ── Available promotions ───────────────────────── -->
            <section v-if="promotions.length > 0" class="mt-7">
                <h2
                    class="mb-3 text-[11px] font-medium tracking-[0.12em] text-slate-500 uppercase"
                >
                    Available promotions
                </h2>
                <div
                    ref="promoTrack"
                    class="-mx-5 flex snap-x snap-mandatory gap-3 overflow-x-auto px-5 pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                    @scroll.passive="onPromoScroll"
                >
                    <article
                        v-for="promo in promotions"
                        :key="promo.id"
                        class="relative w-[82%] shrink-0 snap-start overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 p-5 text-white"
                    >
                        <span
                            class="pointer-events-none absolute -top-10 -right-10 h-40 w-40 rounded-full border border-white/20"
                            aria-hidden="true"
                        />
                        <span
                            class="pointer-events-none absolute -top-4 -right-4 h-24 w-24 rounded-full border border-white/20"
                            aria-hidden="true"
                        />
                        <p
                            class="relative text-[10px] font-medium tracking-[0.14em] uppercase opacity-90"
                        >
                            {{ promo.name }}
                        </p>
                        <p
                            class="relative mt-6 font-display text-[32px] leading-10 font-bold"
                        >
                            {{ promo.headline }}
                        </p>
                        <p class="relative mt-1 text-xs leading-5 opacity-90">
                            {{ promoDetail(promo) }}
                        </p>
                    </article>
                </div>
                <div
                    v-if="promotions.length > 1"
                    class="mt-3 flex justify-center gap-1.5"
                    aria-hidden="true"
                >
                    <span
                        v-for="(promo, i) in promotions"
                        :key="promo.id"
                        class="h-1 rounded-full transition-all"
                        :class="
                            i === promoIndex
                                ? 'w-5 bg-blue-600'
                                : 'w-1.5 bg-slate-300'
                        "
                    />
                </div>
            </section>

            <!-- ── Your shop ──────────────────────────────────── -->
            <div
                v-for="shop in nearbyShops"
                :key="shop.id"
                class="mt-4 flex items-center justify-between gap-3 text-xs"
            >
                <p class="min-w-0 truncate text-slate-600">
                    {{ shop.shop_name }} · {{ shop.barangay }},
                    {{ shop.municipality }}
                </p>
                <button
                    type="button"
                    class="shrink-0 font-medium text-blue-600 hover:underline"
                    @click="goToShop(shop)"
                >
                    View shop
                </button>
            </div>

            <!-- ── No shop yet ────────────────────────────────── -->
            <div
                v-if="nearbyShops.length === 0"
                class="mt-7 flex flex-col items-center border-y border-slate-100 px-4 py-8 text-center"
            >
                <QrCode
                    class="mb-2 h-8 w-8 text-slate-400"
                    aria-hidden="true"
                />
                <p class="text-sm font-semibold text-slate-800">
                    Scan your laundry shop's QR code
                </p>
                <p class="mt-1 text-xs text-slate-500">
                    Ask the shop for its QR code and scan it to connect your
                    account and start ordering.
                </p>
            </div>

            <!-- ── Actions ────────────────────────────────────── -->
            <div class="mt-5 space-y-3">
                <button
                    type="button"
                    class="flex min-h-12 w-full items-center justify-center rounded-xl bg-blue-600 text-sm font-semibold text-white transition hover:bg-blue-700 active:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="nearbyShops.length === 0"
                    @click="router.visit('/user/shops')"
                >
                    Place an order
                </button>
                <button
                    type="button"
                    class="flex min-h-12 w-full items-center justify-center rounded-xl border border-blue-600 bg-white text-sm font-semibold text-blue-600 transition hover:bg-blue-50"
                    @click="router.visit('/user/orders')"
                >
                    Track my orders
                </button>
            </div>

            <!-- ── Recent orders ──────────────────────────────── -->
            <section class="mt-8">
                <div
                    class="flex items-center justify-between border-b border-slate-100 pb-3"
                >
                    <h2
                        class="text-[11px] font-medium tracking-[0.12em] text-slate-500 uppercase"
                    >
                        Recent orders
                    </h2>
                    <button
                        type="button"
                        class="text-xs font-medium text-blue-600 hover:underline"
                        @click="router.visit('/user/orders')"
                    >
                        View all
                    </button>
                </div>

                <div
                    v-if="recentOrders.length === 0"
                    class="flex flex-col items-center py-10 text-center"
                >
                    <Package
                        class="mb-3 h-7 w-7 text-slate-400"
                        aria-hidden="true"
                    />
                    <p
                        class="font-display text-sm font-semibold text-slate-900"
                    >
                        Nothing here yet
                    </p>
                    <p class="mt-1 max-w-52 text-xs leading-5 text-slate-500">
                        Your first order will appear here once you place it.
                    </p>
                </div>

                <ul v-else class="divide-y divide-slate-100">
                    <li v-for="order in recentOrders" :key="order.id">
                        <button
                            type="button"
                            class="flex min-h-16 w-full items-center gap-3 py-3 text-left transition hover:bg-slate-50"
                            @click="
                                router.visit(
                                    `/user/orders/${order.payment_public_id}`,
                                )
                            "
                        >
                            <div class="min-w-0 flex-1">
                                <p
                                    class="truncate text-sm font-semibold text-slate-900"
                                >
                                    {{ order.shop_name }}
                                </p>
                                <p class="truncate text-xs text-slate-500">
                                    {{ order.service_name }} ·
                                    {{ order.order_number }}
                                </p>
                            </div>
                            <div class="flex shrink-0 flex-col items-end gap-1">
                                <span
                                    class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                    :class="
                                        statusColors[order.status] ??
                                        'bg-slate-100 text-slate-600'
                                    "
                                >
                                    {{
                                        statusLabel[order.status] ??
                                        order.status
                                    }}
                                </span>
                                <span
                                    class="text-xs font-semibold text-slate-800"
                                    >{{ currency(order.total_amount) }}</span
                                >
                            </div>
                            <ChevronRight
                                class="h-4 w-4 shrink-0 text-slate-300"
                            />
                        </button>
                    </li>
                </ul>
            </section>
        </div>
    </UserLayout>
</template>
