<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { type BreadcrumbItem, type AppPageProps } from '@/types'
import { ref, computed, onMounted } from 'vue'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import {
    AlertDialog, AlertDialogContent, AlertDialogHeader,
    AlertDialogTitle, AlertDialogDescription, AlertDialogFooter,
} from '@/components/ui/alert-dialog'
import {
    Tag, Plus, Pencil, Trash2, RotateCcw,
    ToggleLeft, ToggleRight, Percent, BadgeDollarSign,
    CheckCircle2, XCircle, Archive,
} from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface Promotion {
    id: number
    name: string
    description: string | null
    type: 'percentage' | 'fixed'
    value: string
    min_order_amount: string | null
    is_active: boolean
    starts_at: string | null
    ends_at: string | null
    orders_count?: number
    deleted_at: string | null
}

interface Stats {
    total: number
    active: number
    inactive: number
    percentage: number
    fixed: number
}

interface Paginated {
    data: Promotion[]
    current_page: number
    last_page: number
    total: number
    from: number | null
    to: number | null
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    promotions: Paginated | Promotion[]
    stats: Stats
    archived: boolean
}>()

// ─── Setup ────────────────────────────────────────────────────────────────────

const page = usePage<AppPageProps>()
const isOwner = computed(() => page.props.auth.user.role === 'owner')
const base = computed(() => isOwner.value ? '/shop/operations/promos' : '/staff/operations/promos')

onMounted(() => {
    const flashToast = page.props.toast as { type: string; message: string } | undefined
    if (!flashToast) return
    switch (flashToast.type) {
        case 'success': toast.success(flashToast.message); break
        case 'error':   toast.error(flashToast.message);   break
        default:        toast(flashToast.message)
    }
})

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Operations', href: '/shop/operations/orders' },
    { title: 'Promotions', href: '/shop/operations/promos' },
]

// ─── Data ─────────────────────────────────────────────────────────────────────

const rows = computed<Promotion[]>(() =>
    Array.isArray(props.promotions) ? props.promotions : props.promotions.data
)

const paginated = computed(() =>
    Array.isArray(props.promotions) ? null : props.promotions
)

// ─── Delete ───────────────────────────────────────────────────────────────────

const toDelete = ref<Promotion | null>(null)
const isDeleteOpen = ref(false)

function confirmDelete() {
    if (!toDelete.value) return
    router.delete(`${base.value}/${toDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { isDeleteOpen.value = false; toDelete.value = null },
    })
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatValue(promo: Promotion) {
    return promo.type === 'percentage'
        ? `${parseFloat(promo.value)}%`
        : `₱${parseFloat(promo.value).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
}

function formatDate(d: string | null) {
    if (!d) return '—'
    return new Date(d + 'T00:00:00').toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>

<template>
    <Head title="Promotions" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Promotions">
        <div class="px-6 space-y-6">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold">Promotions</h2>
                    <p class="text-sm text-muted-foreground">Manage discount promotions applied to customer orders.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" @click="router.visit(`${base}?archived=${!archived}`)">
                        <Archive class="h-4 w-4 mr-2" />
                        {{ archived ? 'Back to Active' : 'View Archived' }}
                    </Button>
                    <Button v-if="!archived" @click="router.visit(`${base}/create`)">
                        <Plus class="h-4 w-4 mr-2" /> New Promotion
                    </Button>
                </div>
            </div>

            <!-- Stats -->
            <div v-if="!archived" class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                <Card>
                    <CardContent class="pt-4 pb-4 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                            <Tag class="h-5 w-5 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.total }}</p>
                            <p class="text-xs text-muted-foreground">Total</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-4 pb-4 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-green-100 flex items-center justify-center">
                            <CheckCircle2 class="h-5 w-5 text-green-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.active }}</p>
                            <p class="text-xs text-muted-foreground">Active</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-4 pb-4 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-red-100 flex items-center justify-center">
                            <XCircle class="h-5 w-5 text-red-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.inactive }}</p>
                            <p class="text-xs text-muted-foreground">Inactive</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-4 pb-4 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-violet-100 flex items-center justify-center">
                            <Percent class="h-5 w-5 text-violet-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.percentage }}</p>
                            <p class="text-xs text-muted-foreground">% Off</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-4 pb-4 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-amber-100 flex items-center justify-center">
                            <BadgeDollarSign class="h-5 w-5 text-amber-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.fixed }}</p>
                            <p class="text-xs text-muted-foreground">Fixed ₱</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Table -->
            <div class="rounded-xl border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                                <th class="text-left px-4 py-3 font-medium">Name</th>
                                <th class="text-left px-4 py-3 font-medium">Type</th>
                                <th class="text-left px-4 py-3 font-medium">Value</th>
                                <th class="text-left px-4 py-3 font-medium">Min. Order</th>
                                <th class="text-left px-4 py-3 font-medium">Validity</th>
                                <th class="text-left px-4 py-3 font-medium">Orders Used</th>
                                <th class="text-left px-4 py-3 font-medium">Status</th>
                                <th class="text-right px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="promo in rows" :key="promo.id"
                                class="border-b last:border-0 hover:bg-muted/20 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-medium">{{ promo.name }}</p>
                                    <p v-if="promo.description" class="text-xs text-muted-foreground truncate max-w-[200px]">
                                        {{ promo.description }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold"
                                        :class="promo.type === 'percentage'
                                            ? 'bg-violet-100 text-violet-700'
                                            : 'bg-amber-100 text-amber-700'">
                                        <Percent v-if="promo.type === 'percentage'" class="h-3 w-3" />
                                        <BadgeDollarSign v-else class="h-3 w-3" />
                                        {{ promo.type === 'percentage' ? 'Percentage' : 'Fixed Amount' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-semibold">{{ formatValue(promo) }}</td>
                                <td class="px-4 py-3 text-muted-foreground text-xs">
                                    {{ promo.min_order_amount
                                        ? `₱${parseFloat(promo.min_order_amount).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
                                        : '—' }}
                                </td>
                                <td class="px-4 py-3 text-xs text-muted-foreground">
                                    <span v-if="!promo.starts_at && !promo.ends_at">No limit</span>
                                    <span v-else>{{ formatDate(promo.starts_at) }} – {{ formatDate(promo.ends_at) }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center h-6 w-10 rounded bg-blue-100 text-blue-700 text-xs font-semibold">
                                        {{ promo.orders_count ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span v-if="!archived"
                                        class="px-2.5 py-0.5 text-xs font-semibold rounded-full"
                                        :class="promo.is_active
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-red-100 text-red-700'">
                                        {{ promo.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <span v-else class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                                        Archived
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <template v-if="!archived">
                                            <Button size="icon" variant="ghost"
                                                @click="router.patch(`${base}/${promo.id}/toggle`)"
                                                :title="promo.is_active ? 'Deactivate' : 'Activate'">
                                                <ToggleRight v-if="promo.is_active" class="h-4 w-4 text-green-500" />
                                                <ToggleLeft v-else class="h-4 w-4 text-gray-400" />
                                            </Button>
                                            <Button size="icon" variant="ghost"
                                                @click="router.visit(`${base}/${promo.id}/edit`)">
                                                <Pencil class="h-4 w-4 text-blue-500" />
                                            </Button>
                                            <Button size="icon" variant="ghost"
                                                @click="toDelete = promo; isDeleteOpen = true">
                                                <Trash2 class="h-4 w-4 text-red-500" />
                                            </Button>
                                        </template>
                                        <template v-else>
                                            <Button size="icon" variant="ghost"
                                                @click="router.patch(`${base}/${promo.id}/restore`)">
                                                <RotateCcw class="h-4 w-4 text-green-500" />
                                            </Button>
                                        </template>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="rows.length === 0">
                                <td colspan="8" class="px-4 py-12 text-center text-sm text-muted-foreground">
                                    <Tag class="h-8 w-8 mx-auto mb-2 opacity-30" />
                                    <p>{{ archived ? 'No archived promotions.' : 'No promotions yet. Create one to get started.' }}</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="paginated && paginated.last_page > 1" class="flex items-center justify-between px-2 py-2">
                <p class="text-xs text-muted-foreground">
                    Showing {{ paginated.from }}–{{ paginated.to }} of {{ paginated.total }}
                </p>
                <div class="flex items-center gap-1">
                    <Button size="sm" variant="outline" :disabled="paginated.current_page === 1"
                        @click="router.get(base, { page: paginated.current_page - 1 }, { preserveState: true })">
                        Prev
                    </Button>
                    <Button size="sm" variant="outline" :disabled="paginated.current_page === paginated.last_page"
                        @click="router.get(base, { page: paginated.current_page + 1 }, { preserveState: true })">
                        Next
                    </Button>
                </div>
            </div>

        </div>

        <!-- Delete confirm -->
        <AlertDialog v-model:open="isDeleteOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Archive Promotion</AlertDialogTitle>
                    <AlertDialogDescription>
                        Archive <strong>{{ toDelete?.name }}</strong>? It will no longer be available for new orders.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <Button variant="outline" @click="isDeleteOpen = false">Cancel</Button>
                    <Button variant="destructive" @click="confirmDelete">Archive</Button>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

    </ShopLayout>
</template>
