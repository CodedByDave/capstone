<script setup lang="ts">
import UserLayout from '@/layouts/user/UserLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    ChevronRight,
    ClipboardList,
    MapPin,
    Package,
    WashingMachine,
} from 'lucide-vue-next';
import { onMounted } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Order {
    id: number;
    order_number: string;
    shop: { name: string; location: string } | null;
    service_name: string;
    status: 'pending' | 'in_progress' | 'completed' | 'cancelled';
    payment_status: 'unpaid' | 'partial' | 'paid';
    total_amount: number;
    order_source: string;
    pickup_type: string;
    created_at: string;
}

interface PaginatedOrders {
    data: Order[];
    current_page: number;
    last_page: number;
    total: number;
}

// ─── Props ────────────────────────────────────────────────────────────────────

defineProps<{ orders: PaginatedOrders }>();

// ─── Toast ────────────────────────────────────────────────────────────────────

const page = usePage();

onMounted(() => {
    const flashToast = page.props.toast as
        | { type: string; message: string }
        | undefined;
    if (flashToast?.message) {
        switch (flashToast.type) {
            case 'success':
                toast.success(flashToast.message);
                break;
            case 'error':
                toast.error(flashToast.message);
                break;
            default:
                toast.info(flashToast.message);
        }
    }
});

// ─── Helpers ──────────────────────────────────────────────────────────────────

const statusConfig: Record<string, { label: string; classes: string }> = {
    pending: { label: 'Pending', classes: 'bg-yellow-100 text-yellow-700' },
    in_progress: { label: 'In Progress', classes: 'bg-blue-100 text-blue-700' },
    completed: { label: 'Completed', classes: 'bg-green-100 text-green-700' },
    cancelled: { label: 'Cancelled', classes: 'bg-red-100 text-red-600' },
};

const paymentConfig: Record<string, { label: string; classes: string }> = {
    unpaid: { label: 'Unpaid', classes: 'bg-red-50 text-red-600' },
    partial: { label: 'Partial', classes: 'bg-orange-50 text-orange-600' },
    paid: { label: 'Paid', classes: 'bg-emerald-50 text-emerald-700' },
};

function currency(val: number) {
    return `₱${val.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
}
</script>

<template>
    <Head title="My Orders" />
    <UserLayout>
        <div class="px-4 pt-4 pb-6">
            <!-- Header -->
            <div class="mb-4 flex items-center gap-2">
                <ClipboardList class="h-5 w-5 text-blue-600" />
                <h1 class="text-lg font-bold text-gray-900">My Orders</h1>
                <span class="ml-auto text-xs font-medium text-gray-400"
                    >{{ orders.total }} total</span
                >
            </div>

            <!-- Empty state -->
            <div
                v-if="orders.data.length === 0"
                class="flex flex-col items-center py-20 text-gray-400"
            >
                <Package class="mb-4 h-14 w-14 opacity-20" />
                <p class="text-base font-semibold text-gray-500">
                    No orders yet
                </p>
                <p class="mt-1 text-sm">
                    Browse shops and place your first order!
                </p>
                <button
                    class="mt-5 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 active:scale-95"
                    @click="router.visit('/user/shops')"
                >
                    Browse Shops
                </button>
            </div>

            <!-- Order list -->
            <div v-else class="space-y-3">
                <button
                    v-for="order in orders.data"
                    :key="order.id"
                    class="w-full overflow-hidden rounded-2xl border border-gray-100 bg-white text-left shadow-sm transition hover:shadow-md active:scale-[0.99]"
                    @click="router.visit(`/user/orders/${order.id}`)"
                >
                    <!-- Coloured status strip -->
                    <div
                        class="h-1 w-full"
                        :class="{
                            'bg-yellow-400': order.status === 'pending',
                            'bg-blue-500': order.status === 'in_progress',
                            'bg-green-500': order.status === 'completed',
                            'bg-red-400': order.status === 'cancelled',
                        }"
                    />

                    <div class="p-4">
                        <!-- Top row: shop + order number -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p
                                    class="flex items-center gap-1.5 truncate text-sm font-semibold text-gray-800"
                                >
                                    <WashingMachine
                                        class="h-3.5 w-3.5 shrink-0 text-blue-500"
                                    />
                                    {{ order.shop?.name ?? '—' }}
                                </p>
                                <p class="mt-0.5 text-xs text-gray-400">
                                    {{ order.order_number }}
                                </p>
                            </div>
                            <ChevronRight
                                class="mt-1 h-4 w-4 shrink-0 text-gray-300"
                            />
                        </div>

                        <!-- Location + service -->
                        <div
                            class="mt-2 flex items-center gap-1.5 text-xs text-gray-500"
                        >
                            <MapPin class="h-3 w-3 shrink-0 text-gray-400" />
                            <span class="truncate">{{
                                order.shop?.location ?? '—'
                            }}</span>
                        </div>
                        <p class="mt-0.5 pl-4 text-xs text-gray-500">
                            {{ order.service_name }}
                        </p>

                        <!-- Bottom row: badges + amount -->
                        <div class="mt-3 flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <span
                                    class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                    :class="
                                        statusConfig[order.status]?.classes ??
                                        'bg-gray-100 text-gray-600'
                                    "
                                >
                                    {{
                                        statusConfig[order.status]?.label ??
                                        order.status
                                    }}
                                </span>
                                <span
                                    class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                    :class="
                                        paymentConfig[order.payment_status]
                                            ?.classes ??
                                        'bg-gray-100 text-gray-600'
                                    "
                                >
                                    {{
                                        paymentConfig[order.payment_status]
                                            ?.label ?? order.payment_status
                                    }}
                                </span>
                            </div>
                            <span class="text-sm font-bold text-gray-700">{{
                                currency(order.total_amount)
                            }}</span>
                        </div>
                    </div>
                </button>

                <!-- Pagination -->
                <div
                    v-if="orders.last_page > 1"
                    class="flex justify-center gap-3 pt-2"
                >
                    <button
                        v-if="orders.current_page > 1"
                        class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm transition hover:bg-gray-50"
                        @click="
                            router.get('/user/orders', {
                                page: orders.current_page - 1,
                            })
                        "
                    >
                        ← Prev
                    </button>
                    <span class="px-4 py-2 text-sm text-gray-500"
                        >{{ orders.current_page }} /
                        {{ orders.last_page }}</span
                    >
                    <button
                        v-if="orders.current_page < orders.last_page"
                        class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm transition hover:bg-gray-50"
                        @click="
                            router.get('/user/orders', {
                                page: orders.current_page + 1,
                            })
                        "
                    >
                        Next →
                    </button>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
