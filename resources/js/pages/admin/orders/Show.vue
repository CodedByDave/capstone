<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
    ArrowLeft, ListOrdered, Store, User,
    CreditCard, Package, MapPin, Phone, Mail,
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
    transaction_id: string | null
    paymongo_payment_id: string | null
    paid_at: string | null
    created_at: string
}

interface Order {
    id: number
    shop_name: string
    owner_name: string
    email: string
    phone: string
    block_street: string
    municipality: string
    barangay: string
    postal_code: string
    subscription_plan: string | null
    total_price: string
    status: string
    expires_at: string | null
    created_at: string
    modules: Module[]
    payments: Payment[]
}

// ─── Props ────────────────────────────────────────────────────────────────────

const { order } = defineProps<{ order: Order }>()

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard',        href: '/admin/dashboard' },
    { title: 'Order Management', href: '/admin/orders' },
    { title: `Order #${order.id}`, href: `/admin/orders/${order.id}` },
]

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatDate(d: string | null) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('en-PH', {
        year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
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
    quarterly:     { label: 'Quarterly',   cls: 'bg-purple-100 text-purple-700' },
    semi_annually: { label: 'Semi-Annual', cls: 'bg-green-100 text-green-700'   },
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

const paymentStatusBadge: Record<string, string> = {
    paid:    'bg-green-100 text-green-700',
    pending: 'bg-amber-100 text-amber-700',
    failed:  'bg-red-100 text-red-600',
}
</script>

<template>
    <Head :title="`Order #${order.id}`" />
    <AdminLayout :breadcrumbs="breadcrumbs" :title="`Order #${order.id}`">
        <div class="px-6 space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <ListOrdered class="h-5 w-5 text-muted-foreground" />
                        Order #{{ order.id }}
                    </h2>
                    <p class="text-sm text-muted-foreground mt-0.5">
                        Placed on {{ formatDate(order.created_at) }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span
                        class="text-sm px-3 py-1 rounded-full font-medium capitalize"
                        :class="statusBadge[order.status] ?? 'bg-gray-100 text-gray-500'"
                    >
                        {{ order.status }}
                    </span>
                    <Button variant="outline" size="sm" @click="router.visit('/admin/orders')">
                        <ArrowLeft class="h-4 w-4 mr-1.5" /> Back
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">

                <!-- Left col -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Shop & Owner info -->
                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle class="text-sm font-semibold flex items-center gap-2">
                                <Store class="h-4 w-4 text-muted-foreground" /> Shop Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-xs text-muted-foreground mb-1">Shop Name</p>
                                <p class="font-medium">{{ order.shop_name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground mb-1">Owner</p>
                                <p class="font-medium flex items-center gap-1.5">
                                    <User class="h-3.5 w-3.5 text-muted-foreground" />
                                    {{ order.owner_name }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground mb-1">Email</p>
                                <p class="font-medium flex items-center gap-1.5">
                                    <Mail class="h-3.5 w-3.5 text-muted-foreground" />
                                    {{ order.email }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground mb-1">Phone</p>
                                <p class="font-medium flex items-center gap-1.5">
                                    <Phone class="h-3.5 w-3.5 text-muted-foreground" />
                                    {{ order.phone }}
                                </p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs text-muted-foreground mb-1">Address</p>
                                <p class="font-medium flex items-center gap-1.5">
                                    <MapPin class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                                    {{ [order.block_street, order.barangay, order.municipality].filter(Boolean).join(', ') }}
                                    <span v-if="order.postal_code" class="text-muted-foreground">{{ order.postal_code }}</span>
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Modules -->
                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle class="text-sm font-semibold flex items-center gap-2">
                                <Package class="h-4 w-4 text-muted-foreground" /> Subscribed Modules
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="rounded-lg border overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                                            <th class="text-left px-4 py-2.5 font-medium">Module</th>
                                            <th class="text-right px-4 py-2.5 font-medium">Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="mod in order.modules" :key="mod.id"
                                            class="border-b last:border-0"
                                        >
                                            <td class="px-4 py-2.5">{{ mod.name }}</td>
                                            <td class="px-4 py-2.5 text-right font-medium">{{ formatPrice(mod.price) }}</td>
                                        </tr>
                                        <tr class="bg-muted/20 font-semibold">
                                            <td class="px-4 py-2.5">Total</td>
                                            <td class="px-4 py-2.5 text-right text-green-600">{{ formatPrice(order.total_price) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Payment history -->
                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle class="text-sm font-semibold flex items-center gap-2">
                                <CreditCard class="h-4 w-4 text-muted-foreground" /> Payment History
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="order.payments.length > 0" class="rounded-lg border overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                                            <th class="text-left px-4 py-2.5 font-medium">Method</th>
                                            <th class="text-left px-4 py-2.5 font-medium">Amount</th>
                                            <th class="text-left px-4 py-2.5 font-medium">Status</th>
                                            <th class="text-left px-4 py-2.5 font-medium">Transaction ID</th>
                                            <th class="text-left px-4 py-2.5 font-medium">Paid At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="payment in order.payments" :key="payment.id"
                                            class="border-b last:border-0"
                                        >
                                            <td class="px-4 py-2.5 capitalize">{{ payment.payment_method }}</td>
                                            <td class="px-4 py-2.5 font-medium">{{ formatPrice(payment.amount) }}</td>
                                            <td class="px-4 py-2.5">
                                                <span
                                                    class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                                                    :class="paymentStatusBadge[payment.status] ?? 'bg-gray-100 text-gray-500'"
                                                >
                                                    {{ payment.status }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2.5 font-mono text-xs text-muted-foreground">
                                                {{ payment.transaction_id ?? payment.paymongo_payment_id ?? '—' }}
                                            </td>
                                            <td class="px-4 py-2.5 text-xs text-muted-foreground whitespace-nowrap">
                                                {{ formatDate(payment.paid_at) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div v-else class="py-8 text-center text-sm text-muted-foreground">
                                <CreditCard class="h-8 w-8 mx-auto mb-2 opacity-20" />
                                No payment records found.
                            </div>
                        </CardContent>
                    </Card>

                </div>

                <!-- Right col — subscription summary -->
                <div class="space-y-6">
                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle class="text-sm font-semibold">Subscription Summary</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4 text-sm">
                            <div class="flex items-center justify-between">
                                <p class="text-muted-foreground">Plan</p>
                                <span
                                    class="text-xs px-2 py-0.5 rounded-full font-medium"
                                    :class="getPlanBadge(order.subscription_plan).cls"
                                >
                                    {{ getPlanBadge(order.subscription_plan).label }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-muted-foreground">Order Status</p>
                                <span
                                    class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                                    :class="statusBadge[order.status] ?? 'bg-gray-100 text-gray-500'"
                                >
                                    {{ order.status }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-muted-foreground">Total Amount</p>
                                <p class="font-semibold text-green-600">{{ formatPrice(order.total_price) }}</p>
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-muted-foreground">Modules</p>
                                <p class="font-medium">{{ order.modules.length }}</p>
                            </div>
                            <div class="border-t pt-4">
                                <p class="text-muted-foreground text-xs mb-1">Subscription Expiry</p>
                                <p
                                    class="font-medium text-sm"
                                    :class="isExpired(order.expires_at) ? 'text-red-500' : 'text-foreground'"
                                >
                                    {{ formatDate(order.expires_at) }}
                                    <span v-if="isExpired(order.expires_at)" class="block text-xs text-red-400 mt-0.5">
                                        This subscription has expired
                                    </span>
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
