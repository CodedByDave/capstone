<script setup lang="ts">
import UserLayout from '@/layouts/user/UserLayout.vue';
import { type AppPageProps } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    ChevronRight,
    ClipboardList,
    Loader2,
    MapPin,
    Package,
    ShoppingBag,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

// ─── Types ────────────────────────────────────────────────────────────────────

interface RecentOrder {
    id: number;
    order_number: string;
    shop_name: string;
    service_name: string;
    status: string;
    payment_status: string;
    total_amount: number;
    created_at: string;
}

// ─── Props ────────────────────────────────────────────────────────────────────

defineProps<{
    recentOrders: RecentOrder[];
    totalOrders: number;
}>();

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

// ─── Location ─────────────────────────────────────────────────────────────────

const locating = ref(false);
const locError = ref('');

function findNearby() {
    if (!navigator.geolocation) {
        locError.value = 'Geolocation is not supported on this device.';
        return;
    }
    locating.value = true;
    locError.value = '';
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            locating.value = false;
            router.get('/user/shops', {
                lat: pos.coords.latitude,
                lng: pos.coords.longitude,
            });
        },
        () => {
            locating.value = false;
            locError.value =
                'Location access denied. Showing all available shops.';
            router.get('/user/shops');
        },
        { timeout: 8000, maximumAge: 60000 },
    );
}

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
                    Find laundry shops near you
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

            <!-- ── Find Nearby ────────────────────────────────── -->
            <div>
                <h3 class="mb-3 text-sm font-semibold text-gray-700">
                    Find a Shop
                </h3>
                <button
                    class="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-blue-300 bg-blue-50 py-3.5 text-sm font-medium text-blue-600 transition-colors hover:bg-blue-100 active:scale-95"
                    :disabled="locating"
                    @click="findNearby"
                >
                    <Loader2 v-if="locating" class="h-4 w-4 animate-spin" />
                    <MapPin v-else class="h-4 w-4" />
                    {{
                        locating
                            ? 'Getting your location...'
                            : 'Find Laundry Near Me'
                    }}
                </button>
                <p
                    v-if="locError"
                    class="mt-2 flex items-center gap-1 text-xs text-red-500"
                >
                    <AlertCircle class="h-3.5 w-3.5 shrink-0" /> {{ locError }}
                </p>

                <!-- Browse all -->
                <button
                    class="mt-2 w-full py-3 text-sm font-medium text-gray-500 underline-offset-2 hover:text-gray-700 hover:underline"
                    @click="router.visit('/user/shops')"
                >
                    Browse all shops →
                </button>
            </div>

            <!-- ── Quick Actions ──────────────────────────────── -->
            <div class="grid grid-cols-2 gap-3">
                <button
                    class="flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-white py-4 shadow-sm transition-shadow hover:shadow-md active:scale-95"
                    @click="router.visit('/user/shops')"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100"
                    >
                        <MapPin class="h-5 w-5 text-blue-600" />
                    </div>
                    <span class="text-xs font-medium text-gray-700"
                        >Browse Shops</span
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
                        @click="router.visit(`/user/orders/${order.id}`)"
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
