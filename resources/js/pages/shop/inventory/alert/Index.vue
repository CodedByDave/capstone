<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { type BreadcrumbItem, type AppPageProps } from '@/types'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { AlertTriangle, CheckCircle2, X, Eye, BellOff, Loader2 } from 'lucide-vue-next'
import axios from 'axios'

interface AlertItem {
    id: number
    quantity_at_alert: number
    min_stock_at_alert: number
    status: 'unread' | 'read' | 'resolved' | 'dismissed'
    resolved_at: string | null
    created_at: string
    inventory: {
        id: number
        name: string
        sku: string
        unit: string
        quantity: number
    } | null
}

interface Paginator {
    data: AlertItem[]
    current_page: number
    last_page: number
    total: number
    links: { url: string | null; label: string; active: boolean }[]
}

const props = defineProps<{
    alerts:      Paginator
    unreadCount: number
}>()

const page = usePage<AppPageProps>()
const isOwner = computed(() => page.props.auth.user.role === 'owner')
const alertsBase = computed(() => isOwner.value ? '/shop/inventory/alerts' : '/staff/inventory/alerts')
const inventoryBase = computed(() => isOwner.value ? '/shop/inventory' : '/staff/inventory')

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: inventoryBase.value },
    { title: 'Stock Alerts', href: alertsBase.value },
]

const alertList   = ref<AlertItem[]>([...props.alerts.data])
const updatingId  = ref<number | null>(null)
const markingAll  = ref(false)

async function updateAlert(id: number, status: 'read' | 'resolved' | 'dismissed') {
    updatingId.value = id
    try {
        await axios.patch(`${alertsBase.value}/${id}`, { status })
        const alert = alertList.value.find(a => a.id === id)
        if (alert) alert.status = status
    } catch { /* silent */ }
    finally { updatingId.value = null }
}

async function markAllRead() {
    markingAll.value = true
    try {
        await axios.post(`${alertsBase.value}/mark-all-read`)
        alertList.value.forEach(a => { if (a.status === 'unread') a.status = 'read' })
    } catch { /* silent */ }
    finally { markingAll.value = false }
}

function formatDate(dt: string) {
    return new Date(dt).toLocaleString('en-PH', {
        month: 'short', day: 'numeric', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })
}

const statusStyle: Record<string, string> = {
    unread:    'bg-red-100 text-red-700',
    read:      'bg-gray-100 text-gray-600',
    resolved:  'bg-green-100 text-green-700',
    dismissed: 'bg-gray-100 text-gray-400',
}
</script>

<template>
    <Head title="Stock Alerts" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Low Stock Alerts">
        <div class="px-6 space-y-6">

            <!-- Summary -->
            <div class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                <AlertTriangle class="h-5 w-5 text-amber-500 shrink-0" />
                <div class="flex-1">
                    <p class="text-sm font-medium text-amber-800">
                        {{ unreadCount }} unread alert{{ unreadCount !== 1 ? 's' : '' }}
                    </p>
                </div>
                <Button
                    v-if="unreadCount > 0"
                    size="sm"
                    variant="outline"
                    class="border-amber-300 text-amber-700 hover:bg-amber-100"
                    :disabled="markingAll"
                    @click="markAllRead"
                >
                    <Loader2 v-if="markingAll" class="h-3.5 w-3.5 mr-1.5 animate-spin" />
                    Mark all as read
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <AlertTriangle class="h-4 w-4 text-amber-500" />
                        Alert History
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="rounded-lg border overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                                    <th class="text-left px-4 py-3 font-medium">Item</th>
                                    <th class="text-left px-4 py-3 font-medium">Stock at Alert</th>
                                    <th class="text-left px-4 py-3 font-medium">Current Stock</th>
                                    <th class="text-left px-4 py-3 font-medium">Status</th>
                                    <th class="text-left px-4 py-3 font-medium">Triggered</th>
                                    <th class="text-right px-4 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="alert in alertList"
                                    :key="alert.id"
                                    class="border-b last:border-0 hover:bg-muted/20 transition-colors"
                                    :class="{ 'opacity-50': alert.status === 'dismissed' }"
                                >
                                    <td class="px-4 py-3">
                                        <p class="font-medium">{{ alert.inventory?.name ?? 'Deleted item' }}</p>
                                        <p class="text-xs text-muted-foreground font-mono">{{ alert.inventory?.sku }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-medium text-red-600">{{ alert.quantity_at_alert }}</span>
                                        <span class="text-xs text-muted-foreground ml-1">/ min {{ alert.min_stock_at_alert }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span v-if="alert.inventory" :class="alert.inventory.quantity <= alert.min_stock_at_alert ? 'text-red-600 font-medium' : 'text-green-600 font-medium'">
                                            {{ alert.inventory.quantity }} {{ alert.inventory.unit }}
                                        </span>
                                        <span v-else class="text-muted-foreground">—</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                                            :class="statusStyle[alert.status]">
                                            {{ alert.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground">
                                        {{ formatDate(alert.created_at) }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <!-- View item -->
                                            <Button
                                                v-if="alert.inventory"
                                                size="icon" variant="ghost"
                                                @click="router.visit(`${inventoryBase}/${alert.inventory.id}`)"
                                            >
                                                <Eye class="h-3.5 w-3.5 text-blue-500" />
                                            </Button>

                                            <!-- Mark resolved -->
                                            <Button
                                                v-if="['unread','read'].includes(alert.status)"
                                                size="icon" variant="ghost"
                                                :disabled="updatingId === alert.id"
                                                @click="updateAlert(alert.id, 'resolved')"
                                            >
                                                <Loader2 v-if="updatingId === alert.id" class="h-3.5 w-3.5 animate-spin" />
                                                <CheckCircle2 v-else class="h-3.5 w-3.5 text-green-500" />
                                            </Button>

                                            <!-- Dismiss -->
                                            <Button
                                                v-if="alert.status !== 'dismissed'"
                                                size="icon" variant="ghost"
                                                :disabled="updatingId === alert.id"
                                                @click="updateAlert(alert.id, 'dismissed')"
                                            >
                                                <BellOff class="h-3.5 w-3.5 text-gray-400" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="alertList.length === 0">
                                    <td colspan="6" class="px-4 py-12 text-center text-sm text-muted-foreground">
                                        <CheckCircle2 class="h-10 w-10 mx-auto mb-2 opacity-20 text-green-500" />
                                        No alerts. All stock levels are healthy!
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="alerts.last_page > 1" class="flex justify-end gap-1 pt-4">
                        <Button
                            v-for="link in alerts.links" :key="link.label"
                            size="sm" :variant="link.active ? 'default' : 'outline'"
                            :disabled="!link.url" class="h-7 min-w-7 text-xs"
                            @click="link.url && router.visit(link.url)"
                            v-html="link.label"
                        />
                    </div>
                </CardContent>
            </Card>

        </div>
    </ShopLayout>
</template>
