<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import UserLayout from '@/layouts/user/UserLayout.vue'
import { type AppPageProps } from '@/types'
import {
    MapPin, ChevronRight, ShoppingBag, ClipboardList,
    Loader2, AlertCircle, Package,
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

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    recentOrders: RecentOrder[]
    totalOrders: number
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
})

// ─── Location ─────────────────────────────────────────────────────────────────

const locating = ref(false)
const locError = ref('')

function findNearby() {
    if (!navigator.geolocation) {
        locError.value = 'Geolocation is not supported on this device.'
        return
    }
    locating.value = true
    locError.value = ''
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            locating.value = false
            router.get('/user/shops', {
                lat: pos.coords.latitude,
                lng: pos.coords.longitude,
            })
        },
        () => {
            locating.value = false
            locError.value = 'Location access denied. Showing all available shops.'
            router.get('/user/shops')
        },
        { timeout: 8000, maximumAge: 60000 },
    )
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
                </div>
            </div>

            <!-- ── Find Nearby ────────────────────────────────── -->
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Find a Shop</h3>
                <button
                    class="w-full flex items-center justify-center gap-2 py-3.5 rounded-xl border-2 border-dashed border-blue-300 text-blue-600 font-medium text-sm bg-blue-50 hover:bg-blue-100 transition-colors active:scale-95"
                    :disabled="locating"
                    @click="findNearby"
                >
                    <Loader2 v-if="locating" class="h-4 w-4 animate-spin" />
                    <MapPin v-else class="h-4 w-4" />
                    {{ locating ? 'Getting your location...' : 'Find Laundry Near Me' }}
                </button>
                <p v-if="locError" class="text-xs text-red-500 mt-2 flex items-center gap-1">
                    <AlertCircle class="h-3.5 w-3.5 shrink-0" /> {{ locError }}
                </p>

                <!-- Browse all -->
                <button
                    class="w-full mt-2 py-3 text-sm text-gray-500 hover:text-gray-700 font-medium underline-offset-2 hover:underline"
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
