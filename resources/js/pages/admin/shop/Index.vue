<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import { ref, onMounted } from 'vue'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import {
    AlertDialog, AlertDialogContent, AlertDialogHeader,
    AlertDialogTitle, AlertDialogDescription, AlertDialogFooter,
    AlertDialogCancel, AlertDialogAction,
} from '@/components/ui/alert-dialog'
import {
    Store, User, CheckCircle, ShieldOff, ShieldCheck,
    Eye, Pencil, Trash2, Search, RefreshCcw,
} from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface ShopOwner {
    id: number
    name: string
    email: string
    phone: string | null
}

interface ShopItem {
    id: number
    shop_name: string
    branch_name: string | null
    phone: string
    municipality: string
    barangay: string
    status: string
    disable_reason: string | null
    created_at: string
    owner: ShopOwner | null
    subscription_plan: string | null
    expires_at: string | null
    is_expired: boolean
    is_expiring_soon: boolean
}

interface Paginator {
    data: ShopItem[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    links: { url: string | null; label: string; active: boolean }[]
}

// ─── Props ────────────────────────────────────────────────────────────────────

const { shops, stats, filters } = defineProps<{
    shops:   Paginator
    stats:   { today: number; total: number; active: number }
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
    { title: 'Dashboard',       href: '/admin/dashboard' },
    { title: 'Shop Management', href: '/admin/shop' },
]

// ─── Filters ──────────────────────────────────────────────────────────────────

const search       = ref(filters.search ?? '')
const statusFilter = ref(filters.status ?? 'all')
const planFilter   = ref(filters.plan   ?? 'all')

function applyFilters() {
    router.get('/admin/shop', {
        search: search.value       || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
        plan:   planFilter.value   !== 'all' ? planFilter.value   : undefined,
    }, { preserveState: true, replace: true })
}

function resetFilters() {
    search.value       = ''
    statusFilter.value = 'all'
    planFilter.value   = 'all'
    router.get('/admin/shop', {}, { preserveState: true, replace: true })
}

// ─── Disable dialog ───────────────────────────────────────────────────────────

const disableDialogOpen = ref(false)
const selectedShop      = ref<ShopItem | null>(null)
const disableReason     = ref('')

function openDisableDialog(shop: ShopItem) {
    selectedShop.value      = shop
    disableReason.value     = ''
    disableDialogOpen.value = true
}

function confirmDisable() {
    if (!selectedShop.value) return
    router.post(`/admin/shop/${selectedShop.value.id}/disable`, { reason: disableReason.value }, {
        preserveScroll: true,
        onSuccess: () => {
            disableDialogOpen.value = false
            selectedShop.value      = null
            disableReason.value     = ''
            toast.success('Shop has been disabled.')
        },
        onError: () => toast.error('Failed to disable shop.'),
    })
}

function enableShop(id: number) {
    router.post(`/admin/shop/${id}/enable`, {}, {
        preserveScroll: true,
        onSuccess: () => toast.success('Shop has been enabled.'),
        onError:   () => toast.error('Failed to enable shop.'),
    })
}

// ─── Archive ──────────────────────────────────────────────────────────────────

const archiveId   = ref<number | null>(null)
const archiveName = ref('')
const archiveOpen = ref(false)

function openArchive(shop: ShopItem) {
    archiveId.value   = shop.id
    archiveName.value = shop.shop_name
    archiveOpen.value = true
}

function cancelArchive() {
    archiveOpen.value = false
    setTimeout(() => { archiveId.value = null; archiveName.value = '' }, 200)
}

function confirmArchive() {
    if (!archiveId.value) return
    router.delete(`/admin/shop/${archiveId.value}`, {
        preserveScroll: true,
        onSuccess: () => { toast.success('Shop archived.'); archiveOpen.value = false },
        onError:   () => toast.error('Failed to archive shop.'),
    })
}

// ─── Plan badge ───────────────────────────────────────────────────────────────

const planStyles: Record<string, { label: string; cls: string }> = {
    monthly:       { label: 'Monthly',     cls: 'bg-blue-100 text-blue-700'     },
    quarterly:     { label: 'Quarterly',   cls: 'bg-purple-100 text-purple-700' },
    semi_annually: { label: 'Semi-Annual', cls: 'bg-green-100 text-green-700'   },
    annually:      { label: 'Annually',    cls: 'bg-amber-100 text-amber-700'   },
}

function getPlanBadge(plan: string | null): { label: string; cls: string } {
    if (!plan) return { label: 'No Plan', cls: 'bg-gray-100 text-gray-400' }
    return planStyles[plan] ?? { label: plan, cls: 'bg-gray-100 text-gray-500' }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatDate(date: string | null) {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('en-PH', {
        year: 'numeric', month: 'short', day: 'numeric',
    })
}
</script>

<template>
    <Head title="Shop Management" />
    <AdminLayout :breadcrumbs="breadcrumbs" title="Shop Management">
        <div class="px-6 space-y-6">

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Registered Today</p>
                            <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <Store class="h-4 w-4 text-blue-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-blue-600">{{ stats.today }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Total Shops</p>
                            <div class="h-8 w-8 rounded-lg bg-purple-100 flex items-center justify-center">
                                <User class="h-4 w-4 text-purple-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-purple-600">{{ stats.total }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Active Shops</p>
                            <div class="h-8 w-8 rounded-lg bg-green-100 flex items-center justify-center">
                                <CheckCircle class="h-4 w-4 text-green-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-green-600">{{ stats.active }}</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Table card -->
            <Card>
                <CardHeader class="pb-3">
                    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
                        <CardTitle class="flex items-center gap-2">
                            <Store class="h-4 w-4 text-muted-foreground" />
                            Shop List
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

                        <Select v-model="statusFilter" @update:model-value="applyFilters">
                            <SelectTrigger class="w-36">
                                <SelectValue placeholder="Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Status</SelectItem>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="inactive">Inactive</SelectItem>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="disabled">Disabled</SelectItem>
                            </SelectContent>
                        </Select>

                        <Select v-model="planFilter" @update:model-value="applyFilters">
                            <SelectTrigger class="w-36">
                                <SelectValue placeholder="Plan" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Plans</SelectItem>
                                <SelectItem value="monthly">Monthly</SelectItem>
                                <SelectItem value="quarterly">Quarterly</SelectItem>
                                <SelectItem value="semi_annually">Semi-Annual</SelectItem>
                                <SelectItem value="annually">Annually</SelectItem>
                                <SelectItem value="none">No Plan</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Table -->
                    <div class="rounded-lg border overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                                    <th class="text-left px-4 py-3 font-medium">Shop</th>
                                    <th class="text-left px-4 py-3 font-medium">Owner</th>
                                    <th class="text-left px-4 py-3 font-medium">Phone</th>
                                    <th class="text-left px-4 py-3 font-medium">Location</th>
                                    <th class="text-left px-4 py-3 font-medium">Plan</th>
                                    <th class="text-left px-4 py-3 font-medium">Expires</th>
                                    <th class="text-left px-4 py-3 font-medium">Status</th>
                                    <th class="text-center px-4 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="shop in shops.data" :key="shop.id"
                                    class="border-b last:border-0 hover:bg-muted/20 transition-colors"
                                    :class="{ 'bg-red-50/50 dark:bg-red-900/10': shop.status === 'disabled' }"
                                >
                                    <td class="px-4 py-3">
                                        <p class="font-medium whitespace-nowrap">{{ shop.shop_name }}</p>
                                        <p v-if="shop.branch_name" class="text-xs text-muted-foreground">{{ shop.branch_name }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium whitespace-nowrap">{{ shop.owner?.name ?? '—' }}</p>
                                        <p class="text-xs text-muted-foreground">{{ shop.owner?.email ?? '' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground whitespace-nowrap">
                                        {{ shop.phone }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground">
                                        <p>{{ shop.municipality }}</p>
                                        <p class="text-muted-foreground/70">{{ shop.barangay }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full font-medium"
                                            :class="getPlanBadge(shop.subscription_plan).cls"
                                        >
                                            {{ getPlanBadge(shop.subscription_plan).label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span
                                            v-if="shop.expires_at"
                                            class="text-xs font-medium"
                                            :class="{
                                                'text-red-500':          shop.is_expired,
                                                'text-amber-500':        shop.is_expiring_soon && !shop.is_expired,
                                                'text-muted-foreground': !shop.is_expired && !shop.is_expiring_soon,
                                            }"
                                        >
                                            {{ formatDate(shop.expires_at) }}
                                            <span v-if="shop.is_expired" class="block text-xs">Expired</span>
                                            <span v-else-if="shop.is_expiring_soon" class="block text-xs">Expiring soon</span>
                                        </span>
                                        <span v-else class="text-xs text-muted-foreground">—</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                                            :class="{
                                                'bg-green-100 text-green-700':   shop.status === 'active',
                                                'bg-red-100 text-red-600':       shop.status === 'inactive',
                                                'bg-yellow-100 text-yellow-700': shop.status === 'pending',
                                                'bg-gray-100 text-gray-500':     shop.status === 'disabled',
                                            }"
                                        >
                                            {{ shop.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-1">
                                            <Button size="icon" variant="ghost" @click="router.visit(`/admin/shop/${shop.id}`)">
                                                <Eye class="h-4 w-4 text-blue-500" />
                                            </Button>
                                            <Button size="icon" variant="ghost" @click="router.visit(`/admin/shop/${shop.id}/edit`)">
                                                <Pencil class="h-4 w-4 text-green-500" />
                                            </Button>
                                            <Button size="icon" variant="ghost" @click="openArchive(shop)">
                                                <Trash2 class="h-4 w-4 text-amber-500" />
                                            </Button>
                                            <Button
                                                v-if="shop.status !== 'disabled'"
                                                size="icon" variant="ghost"
                                                title="Disable Shop"
                                                @click="openDisableDialog(shop)"
                                            >
                                                <ShieldOff class="h-4 w-4 text-orange-500" />
                                            </Button>
                                            <Button
                                                v-else
                                                size="icon" variant="ghost"
                                                title="Enable Shop"
                                                @click="enableShop(shop.id)"
                                            >
                                                <ShieldCheck class="h-4 w-4 text-green-600" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="shops.data.length === 0">
                                    <td colspan="8" class="px-4 py-12 text-center text-sm text-muted-foreground">
                                        <Store class="h-10 w-10 mx-auto mb-2 opacity-20" />
                                        No shops found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="shops.last_page > 1" class="flex items-center justify-between pt-2">
                        <p class="text-xs text-muted-foreground">
                            Showing {{ shops.data.length }} of {{ shops.total }} shops
                        </p>
                        <div class="flex gap-1">
                            <Button
                                v-for="link in shops.links" :key="link.label"
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

        <!-- Archive confirm -->
        <AlertDialog v-model:open="archiveOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Archive Shop</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to archive
                        <strong>{{ archiveName }}</strong>?
                        This can be undone later.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <Button variant="outline" @click="cancelArchive">Cancel</Button>
                    <Button variant="destructive" @click="confirmArchive">Archive</Button>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <!-- Disable dialog -->
        <AlertDialog :open="disableDialogOpen" @update:open="disableDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle class="flex items-center gap-2 text-orange-600">
                        <ShieldOff class="h-5 w-5" />
                        Disable Shop
                    </AlertDialogTitle>
                    <AlertDialogDescription>
                        You are about to disable <strong>{{ selectedShop?.shop_name }}</strong>.
                        The owner will lose access. Please provide a reason.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <div class="mt-2 space-y-1">
                    <label class="text-sm font-medium">Reason for disabling</label>
                    <Textarea
                        v-model="disableReason"
                        placeholder="e.g. Violation of terms of service..."
                        rows="3"
                    />
                </div>
                <AlertDialogFooter class="mt-4">
                    <AlertDialogCancel @click="disableDialogOpen = false">Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        class="bg-orange-600 text-white hover:bg-orange-700"
                        :disabled="!disableReason.trim()"
                        @click="confirmDisable"
                    >
                        Disable Shop
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

    </AdminLayout>
</template>
