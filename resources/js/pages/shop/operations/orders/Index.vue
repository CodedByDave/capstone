<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import {
    Package, Plus, Search, Pencil, Trash2, Eye,
    Clock, CheckCircle, Loader, AlertCircle, CreditCard,
    X, Archive, RotateCcw
} from 'lucide-vue-next'
import { debounce } from 'lodash'
import { type AppPageProps } from '@/types'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

// ─── Types ─────────────────────────────────────────

interface Order {
    id: number
    order_number: string
    customer_name: string
    customer_phone: string | null
    status: string
    payment_status: string
    payment_method: string
    total_amount: string
    amount_paid: string
    created_at: string
    service?: { service_name: string }
}

interface Paginator {
    data: Order[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    links: { url: string | null; label: string; active: boolean }[]
}

// ─── Props ─────────────────────────────────────────

const props = defineProps<{
    orders: Paginator
    statusSummary: Record<string, number>
    filters: Record<string, string>
    showArchived: boolean
}>()

// ─── Toast ─────────────────────────────────────────

const page = usePage<AppPageProps>()

watch(
    () => page.props.toast as any,
    (t) => {
        if (!t) return
        if (t.type === 'success') toast.success(t.message)
        if (t.type === 'error')   toast.error(t.message)
        if (t.type === 'warning') toast.warning(t.message)
        if (t.type === 'info')    toast.info(t.message)
    },
    { immediate: true }
)

// ─── Base URL ──────────────────────────────────────

const base = '/shop/operations/orders'

// ─── Filters ───────────────────────────────────────

const search        = ref(props.filters.search ?? '')
const status        = ref(props.filters.status ?? '')
const paymentStatus = ref(props.filters.payment_status ?? '')

const applyFilters = debounce(() => {
    router.get(base, {
        search:         search.value || undefined,
        status:         status.value || undefined,
        payment_status: paymentStatus.value || undefined,
    }, { preserveState: true, replace: true })
}, 300)

watch([search, status, paymentStatus], applyFilters)

const hasActiveFilters = computed(() =>
    !props.showArchived && (search.value || status.value || paymentStatus.value)
)

const clearFilters = () => {
    search.value        = ''
    status.value        = ''
    paymentStatus.value = ''
}

// ─── KPIs ──────────────────────────────────────────

const totalOrders = computed(() =>
    Object.values(props.statusSummary).reduce((a, b) => a + b, 0)
)

const totalRevenue = computed(() =>
    props.orders.data.reduce((sum, o) => sum + Number(o.total_amount), 0)
)

const totalCollected = computed(() =>
    props.orders.data.reduce((sum, o) => sum + Number(o.amount_paid), 0)
)

const outstandingBalance = computed(() => totalRevenue.value - totalCollected.value)

const unpaidCount = computed(() =>
    props.orders.data.filter(o => o.payment_status === 'unpaid').length
)

// ─── Helpers ───────────────────────────────────────

const formatCurrency = (value: string | number) =>
    `₱${Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`

const formatDate = (date: string) =>
    new Date(date).toLocaleDateString('en-PH', {
        month: 'short', day: 'numeric', year: 'numeric',
    })

const formatLabel = (s: string) =>
    s.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())

const statusIcon = (s: string) => ({
    pending:     Clock,
    in_progress: Loader,
    completed:   CheckCircle,
}[s] ?? Package)

const statusStyle = (s: string) => ({
    pending:     'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
    in_progress: 'bg-blue-50 text-blue-700 ring-1 ring-blue-200',
    completed:   'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
}[s] ?? 'bg-gray-50 text-gray-700 ring-1 ring-gray-200')

const paymentStyle = (s: string) => ({
    unpaid:  'bg-red-50 text-red-700 ring-1 ring-red-200',
    partial: 'bg-orange-50 text-orange-700 ring-1 ring-orange-200',
    paid:    'bg-green-50 text-green-700 ring-1 ring-green-200',
}[s] ?? 'bg-gray-50 text-gray-700 ring-1 ring-gray-200')

// ─── Toggle Archive View ───────────────────────────

const toggleArchived = () => {
    router.get(base, { archived: props.showArchived ? undefined : true }, {
        preserveState: false,
        replace: true,
    })
}

// ─── Restore ───────────────────────────────────────

const restoreOrder = (order: Order) => {
    router.patch(`${base}/${order.id}/restore`)
}

const restoreAll = () => {
    router.post(`${base}/restore-all`)
}

// ─── Delete Dialog ─────────────────────────────────

const orderToDelete   = ref<Order | null>(null)
const showDeleteDialog = ref(false)

const confirmDelete = (order: Order) => {
    orderToDelete.value   = order
    showDeleteDialog.value = true
}

const handleDelete = () => {
    if (!orderToDelete.value) return
    router.delete(`${base}/${orderToDelete.value.id}`)
    showDeleteDialog.value = false
    orderToDelete.value   = null
}
</script>

<template>
    <Head :title="showArchived ? 'Archived Orders' : 'Orders'" />

    <ShopLayout :title="showArchived ? 'Archived Orders' : 'Orders'">
        <div class="px-6 space-y-6">

            <!-- ── KPI Cards (active view only) ──────────── -->
            <div v-if="!showArchived" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <Card>
                    <CardContent class="pt-5 pb-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-medium uppercase text-muted-foreground tracking-wide">Total Orders</p>
                            <Package class="h-4 w-4 text-blue-500" />
                        </div>
                        <p class="text-2xl font-bold">{{ totalOrders }}</p>
                        <p class="text-xs text-muted-foreground mt-1">All statuses</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="pt-5 pb-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-medium uppercase text-muted-foreground tracking-wide">Collected</p>
                            <CheckCircle class="h-4 w-4 text-green-500" />
                        </div>
                        <p class="text-2xl font-bold">{{ formatCurrency(totalCollected) }}</p>
                        <p class="text-xs text-muted-foreground mt-1">
                            {{ totalRevenue ? ((totalCollected / totalRevenue) * 100).toFixed(0) : 0 }}% of revenue
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="pt-5 pb-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-medium uppercase text-muted-foreground tracking-wide">Outstanding</p>
                            <AlertCircle class="h-4 w-4 text-orange-500" />
                        </div>
                        <p class="text-2xl font-bold" :class="outstandingBalance > 0 ? 'text-orange-600' : ''">
                            {{ formatCurrency(outstandingBalance) }}
                        </p>
                        <p class="text-xs text-muted-foreground mt-1">Uncollected balance</p>
                    </CardContent>
                </Card>

                <Card
                    class="cursor-pointer transition hover:ring-2 hover:ring-red-200"
                    :class="{ 'ring-2 ring-red-400': paymentStatus === 'unpaid' }"
                    @click="paymentStatus = paymentStatus === 'unpaid' ? '' : 'unpaid'"
                >
                    <CardContent class="pt-5 pb-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-medium uppercase text-muted-foreground tracking-wide">Unpaid Orders</p>
                            <CreditCard class="h-4 w-4 text-red-500" />
                        </div>
                        <p class="text-2xl font-bold" :class="unpaidCount > 0 ? 'text-red-600' : ''">
                            {{ unpaidCount }}
                        </p>
                        <p class="text-xs text-muted-foreground mt-1">Click to filter</p>
                    </CardContent>
                </Card>
            </div>

            <!-- ── Archived Banner ───────────────────────── -->
            <div v-if="showArchived"
                class="flex items-center gap-3 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3">
                <Archive class="h-5 w-5 text-amber-600 shrink-0" />
                <div class="flex-1">
                    <p class="text-sm font-semibold text-amber-800">Viewing Archived Orders</p>
                    <p class="text-xs text-amber-600">These orders have been soft-deleted. You can restore them individually or all at once.</p>
                </div>
                <Button
                    v-if="orders.data.length > 0"
                    size="sm"
                    variant="outline"
                    class="border-amber-300 text-amber-700 hover:bg-amber-100 gap-1.5"
                    @click="restoreAll"
                >
                    <RotateCcw class="h-3.5 w-3.5" /> Restore All
                </Button>
            </div>

            <!-- ── Table Card ─────────────────────────────── -->
            <Card>
                <CardHeader>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <CardTitle>{{ showArchived ? 'Archived Orders' : 'Orders List' }}</CardTitle>
                        <div class="flex gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                class="gap-1.5"
                                :class="showArchived ? 'border-amber-400 text-amber-700 bg-amber-50' : ''"
                                @click="toggleArchived"
                            >
                                <Archive class="h-4 w-4" />
                                {{ showArchived ? 'Back to Active' : 'View Archives' }}
                            </Button>
                            <Button v-if="!showArchived" @click="router.visit(`${base}/create`)">
                                <Plus class="h-4 w-4 mr-1" /> New Order
                            </Button>
                        </div>
                    </div>
                </CardHeader>

                <CardContent class="space-y-4">

                    <!-- Filters (active view only) -->
                    <div v-if="!showArchived" class="flex flex-col sm:flex-row gap-2">
                        <div class="relative flex-1">
                            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input v-model="search" class="pl-8" placeholder="Search by name, order # or phone…" />
                        </div>
                        <select v-model="status"
                            class="h-9 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                        <select v-model="paymentStatus"
                            class="h-9 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                            <option value="">All Payments</option>
                            <option value="unpaid">Unpaid</option>
                            <option value="partial">Partial</option>
                            <option value="paid">Paid</option>
                        </select>
                        <Button v-if="hasActiveFilters" variant="ghost" size="sm" class="h-9 gap-1" @click="clearFilters">
                            <X class="h-3.5 w-3.5" /> Clear
                        </Button>
                    </div>

                    <!-- Active filter tags -->
                    <div v-if="hasActiveFilters" class="flex flex-wrap gap-2">
                        <span v-if="status"
                            class="inline-flex items-center gap-1 rounded-full bg-primary/10 text-primary px-2.5 py-0.5 text-xs font-medium">
                            Status: {{ formatLabel(status) }}
                            <button @click="status = ''" class="hover:text-primary/70"><X class="h-3 w-3" /></button>
                        </span>
                        <span v-if="paymentStatus"
                            class="inline-flex items-center gap-1 rounded-full bg-primary/10 text-primary px-2.5 py-0.5 text-xs font-medium">
                            Payment: {{ formatLabel(paymentStatus) }}
                            <button @click="paymentStatus = ''" class="hover:text-primary/70"><X class="h-3 w-3" /></button>
                        </span>
                    </div>

                    <!-- Table -->
                    <div class="border rounded-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/40 text-xs uppercase tracking-wide">
                                <tr>
                                    <th class="px-4 py-3 text-left">Order #</th>
                                    <th class="px-4 py-3 text-left">Customer</th>
                                    <th class="px-4 py-3 text-left">Service</th>
                                    <th v-if="!showArchived" class="px-4 py-3 text-left">Status</th>
                                    <th v-if="!showArchived" class="px-4 py-3 text-left">Payment</th>
                                    <th class="px-4 py-3 text-right">Total</th>
                                    <th class="px-4 py-3 text-left">Date</th>
                                    <th class="px-4 py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="order in orders.data" :key="order.id"
                                    class="border-b last:border-0 hover:bg-muted/30 transition-colors">
                                    <td class="px-4 py-3">
                                        <button
                                            v-if="!showArchived"
                                            class="font-medium text-blue-600 hover:text-blue-800 hover:underline"
                                            @click="router.visit(`${base}/${order.id}`)">
                                            {{ order.order_number }}
                                        </button>
                                        <span v-else class="font-medium text-muted-foreground">
                                            {{ order.order_number }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium">{{ order.customer_name }}</p>
                                        <p v-if="order.customer_phone" class="text-xs text-muted-foreground">{{ order.customer_phone }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ order.service?.service_name ?? '—' }}
                                    </td>
                                    <td v-if="!showArchived" class="px-4 py-3">
                                        <span :class="statusStyle(order.status)"
                                            class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold">
                                            <component :is="statusIcon(order.status)" class="h-3 w-3" />
                                            {{ formatLabel(order.status) }}
                                        </span>
                                    </td>
                                    <td v-if="!showArchived" class="px-4 py-3">
                                        <span :class="paymentStyle(order.payment_status)"
                                            class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold">
                                            {{ formatLabel(order.payment_status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium">
                                        {{ formatCurrency(order.total_amount) }}
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground whitespace-nowrap">
                                        {{ formatDate(order.created_at) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex justify-center gap-0.5">

                                            <!-- Active view actions -->
                                            <template v-if="!showArchived">
                                                <Button size="icon" variant="ghost"
                                                    @click="router.visit(`${base}/${order.id}`)">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                                <Button size="icon" variant="ghost"
                                                    @click="router.visit(`${base}/${order.id}/edit`)">
                                                    <Pencil class="h-4 w-4 text-green-500" />
                                                </Button>
                                                <Button size="icon" variant="ghost" @click="confirmDelete(order)">
                                                    <Trash2 class="h-4 w-4 text-red-500" />
                                                </Button>
                                            </template>

                                            <!-- Archived view actions -->
                                            <template v-else>
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    class="gap-1.5 text-emerald-700 border-emerald-300 hover:bg-emerald-50"
                                                    @click="restoreOrder(order)"
                                                >
                                                    <RotateCcw class="h-3.5 w-3.5" /> Restore
                                                </Button>
                                            </template>

                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="orders.data.length === 0">
                                    <td :colspan="showArchived ? 6 : 8" class="text-center py-12 text-muted-foreground">
                                        <component
                                            :is="showArchived ? Archive : Package"
                                            class="h-10 w-10 mx-auto mb-2 opacity-30"
                                        />
                                        <p>{{ showArchived ? 'No archived orders' : 'No orders found' }}</p>
                                        <p v-if="hasActiveFilters" class="text-xs mt-1">Try adjusting your filters</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="orders.last_page > 1" class="flex items-center justify-between pt-2">
                        <p class="text-xs text-muted-foreground">
                            Showing {{ (orders.current_page - 1) * orders.per_page + 1 }}–{{
                                Math.min(orders.current_page * orders.per_page, orders.total) }}
                            of {{ orders.total }}
                        </p>
                        <div class="flex gap-1">
                            <Button v-for="link in orders.links" :key="link.label" size="sm"
                                :variant="link.active ? 'default' : 'outline'" :disabled="!link.url"
                                @click="link.url && router.visit(link.url)" v-html="link.label" />
                        </div>
                    </div>

                </CardContent>
            </Card>

        </div>

        <!-- ── Delete Alert Dialog ────────────────────── -->
        <AlertDialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Archive Order</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to archive order
                        <span class="font-semibold text-foreground">{{ orderToDelete?.order_number }}</span>
                        for <span class="font-semibold text-foreground">{{ orderToDelete?.customer_name }}</span>?
                        This will restore any deducted inventory and the order will be moved to archive.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="showDeleteDialog = false">Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        class="bg-red-600 hover:bg-red-700 text-white"
                        @click="handleDelete"
                    >
                        Archive Order
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

    </ShopLayout>
</template>