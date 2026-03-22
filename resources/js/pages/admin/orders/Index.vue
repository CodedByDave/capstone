<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import {
    ListOrdered, BadgeDollarSign, Clock, CheckCircle2,
    AlertTriangle, Search, RefreshCcw, Eye,
} from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface Module {
    id: number
    name: string
    price: string
}

interface Payment {
    id: number
    payment_method: string
    amount: string
    status: string
    paid_at: string | null
}

interface OrderItem {
    id: number
    shop_name: string
    owner_name: string
    email: string
    phone: string
    municipality: string
    barangay: string
    subscription_plan: string | null
    total_price: string
    status: string
    expires_at: string | null
    created_at: string
    modules: Module[]
    payments: Payment[]
}

interface Paginator {
    data: OrderItem[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    links: { url: string | null; label: string; active: boolean }[]
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    orders:  Paginator
    stats:   {
        total: number
        paid: number
        pending: number
        expired: number
        revenue: number
    }
    filters: Record<string, string>
}>()

// ─── Flash ────────────────────────────────────────────────────────────────────

const page = usePage()

onMounted(() => {
    const flash = page.props.toast as { type: string; message: string } | undefined
    if (!flash) return
    switch (flash.type) {
        case 'success': toast.success(flash.message); break
        case 'error':   toast.error(flash.message);   break
        default:        toast(flash.message)
    }
})

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard',        href: '/admin/dashboard' },
    { title: 'Order Management', href: '/admin/orders' },
]

// ─── Filters ──────────────────────────────────────────────────────────────────

const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? 'all')
const plan   = ref(props.filters.plan   ?? 'all')
const date   = ref(props.filters.date   ?? '')

function applyFilters() {
    router.get('/admin/orders', {
        search: search.value || undefined,
        status: status.value !== 'all' ? status.value : undefined,
        plan:   plan.value   !== 'all' ? plan.value   : undefined,
        date:   date.value   || undefined,
    }, { preserveState: true, replace: true })
}

function resetFilters() {
    search.value = ''
    status.value = 'all'
    plan.value   = 'all'
    date.value   = ''
    router.get('/admin/orders', {}, { preserveState: true, replace: true })
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatDate(d: string | null) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('en-PH', {
        year: 'numeric', month: 'short', day: 'numeric',
    })
}

function formatPrice(p: string | number) {
    return `₱${Number(p).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
}

function isExpired(expiresAt: string | null) {
    if (!expiresAt) return false
    return new Date(expiresAt) < new Date()
}

const planStyles: Record<string, { label: string; cls: string }> = {
    monthly:       { label: 'Monthly',     cls: 'bg-blue-100 text-blue-700'     },
    annually:      { label: 'Annually',    cls: 'bg-amber-100 text-amber-700'   },
}

function getPlanBadge(p: string | null) {
    if (!p) return { label: 'None', cls: 'bg-gray-100 text-gray-400' }
    return planStyles[p] ?? { label: p, cls: 'bg-gray-100 text-gray-500' }
}

const statusBadge: Record<string, string> = {
    paid:    'bg-green-100 text-green-700',
    pending: 'bg-amber-100 text-amber-700',
    failed:  'bg-red-100 text-red-600',
}
</script>

<template>
    <Head title="Order Management" />
    <AdminLayout :breadcrumbs="breadcrumbs" title="Order Management">
        <div class="px-6 space-y-6">

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Total Orders</p>
                            <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <ListOrdered class="h-4 w-4 text-blue-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold">{{ stats.total.toLocaleString() }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Paid</p>
                            <div class="h-8 w-8 rounded-lg bg-green-100 flex items-center justify-center">
                                <CheckCircle2 class="h-4 w-4 text-green-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-green-600">{{ stats.paid.toLocaleString() }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Pending</p>
                            <div class="h-8 w-8 rounded-lg bg-amber-100 flex items-center justify-center">
                                <Clock class="h-4 w-4 text-amber-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-amber-600">{{ stats.pending.toLocaleString() }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Expired</p>
                            <div class="h-8 w-8 rounded-lg bg-red-100 flex items-center justify-center">
                                <AlertTriangle class="h-4 w-4 text-red-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-red-600">{{ stats.expired.toLocaleString() }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Total Revenue</p>
                            <div class="h-8 w-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                                <BadgeDollarSign class="h-4 w-4 text-emerald-600" />
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-emerald-600">{{ formatPrice(stats.revenue) }}</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Table card -->
            <Card>
                <CardHeader class="pb-3">
                    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
                        <CardTitle class="flex items-center gap-2">
                            <ListOrdered class="h-4 w-4 text-muted-foreground" />
                            All Orders
                        </CardTitle>
                        <Button size="sm" variant="ghost" @click="resetFilters">
                            <RefreshCcw class="h-4 w-4 mr-1.5" /> Reset
                        </Button>
                    </div>
                </CardHeader>

                <CardContent class="space-y-4">
                    <!-- Filters -->
                    <div class="flex flex-wrap gap-2">
                        <div class="relative flex-1 min-w-48">
                            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input
                                v-model="search"
                                placeholder="Search shop, owner, email..."
                                class="pl-8"
                                @keyup.enter="applyFilters"
                            />
                        </div>

                        <Select v-model="status" @update:model-value="applyFilters">
                            <SelectTrigger class="w-36">
                                <SelectValue placeholder="Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Status</SelectItem>
                                <SelectItem value="paid">Paid</SelectItem>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="failed">Failed</SelectItem>
                            </SelectContent>
                        </Select>

                        <Select v-model="plan" @update:model-value="applyFilters">
                            <SelectTrigger class="w-36">
                                <SelectValue placeholder="Plan" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Plans</SelectItem>
                                <SelectItem value="monthly">Monthly</SelectItem>
                                <SelectItem value="annually">Annually</SelectItem>
                            </SelectContent>
                        </Select>

                        <Input
                            v-model="date"
                            type="date"
                            class="w-40"
                            @change="applyFilters"
                        />
                    </div>

                    <!-- Table -->
                    <div class="rounded-lg border overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                                    <th class="text-left px-4 py-3 font-medium">#</th>
                                    <th class="text-left px-4 py-3 font-medium">Shop</th>
                                    <th class="text-left px-4 py-3 font-medium">Owner</th>
                                    <th class="text-left px-4 py-3 font-medium">Plan</th>
                                    <th class="text-left px-4 py-3 font-medium">Modules</th>
                                    <th class="text-left px-4 py-3 font-medium">Total</th>
                                    <th class="text-left px-4 py-3 font-medium">Status</th>
                                    <th class="text-left px-4 py-3 font-medium">Expires</th>
                                    <th class="text-left px-4 py-3 font-medium">Ordered</th>
                                    <th class="text-center px-4 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="order in orders.data" :key="order.id"
                                    class="border-b last:border-0 hover:bg-muted/20 transition-colors"
                                >
                                    <td class="px-4 py-3 font-mono text-xs text-muted-foreground">
                                        #{{ order.id }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium whitespace-nowrap">{{ order.shop_name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ order.municipality }}, {{ order.barangay }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium whitespace-nowrap">{{ order.owner_name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ order.email }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full font-medium"
                                            :class="getPlanBadge(order.subscription_plan).cls"
                                        >
                                            {{ getPlanBadge(order.subscription_plan).label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            <span
                                                v-for="mod in order.modules" :key="mod.id"
                                                class="text-xs px-1.5 py-0.5 rounded bg-muted text-muted-foreground"
                                            >
                                                {{ mod.name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-medium whitespace-nowrap">
                                        {{ formatPrice(order.total_price) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                                            :class="statusBadge[order.status] ?? 'bg-gray-100 text-gray-500'"
                                        >
                                            {{ order.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span
                                            v-if="order.expires_at"
                                            class="text-xs font-medium"
                                            :class="isExpired(order.expires_at)
                                                ? 'text-red-500'
                                                : 'text-muted-foreground'"
                                        >
                                            {{ formatDate(order.expires_at) }}
                                            <span v-if="isExpired(order.expires_at)" class="block">Expired</span>
                                        </span>
                                        <span v-else class="text-xs text-muted-foreground">—</span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground whitespace-nowrap">
                                        {{ formatDate(order.created_at) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <Button
                                            size="icon" variant="ghost"
                                            @click="router.visit(`/admin/orders/${order.id}`)"
                                        >
                                            <Eye class="h-4 w-4 text-blue-500" />
                                        </Button>
                                    </td>
                                </tr>

                                <tr v-if="orders.data.length === 0">
                                    <td colspan="10" class="px-4 py-12 text-center text-sm text-muted-foreground">
                                        <ListOrdered class="h-10 w-10 mx-auto mb-2 opacity-20" />
                                        No orders found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="orders.last_page > 1" class="flex items-center justify-between pt-2">
                        <p class="text-xs text-muted-foreground">
                            Showing {{ orders.data.length }} of {{ orders.total }} orders
                        </p>
                        <div class="flex gap-1">
                            <Button
                                v-for="link in orders.links" :key="link.label"
                                size="sm"
                                :variant="link.active ? 'default' : 'outline'"
                                :disabled="!link.url"
                                class="h-7 min-w-7 text-xs"
                                @click="link.url && router.visit(link.url)"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>

        </div>
    </AdminLayout>
</template>
