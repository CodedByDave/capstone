<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import { type BreadcrumbItem, type AppPageProps } from '@/types'
import { usePermissions } from '@/composables/usePermissions'

import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { ChevronLeft, ChevronRight, RotateCcw, TrendingDown } from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface Expense {
    id: number
    title: string
    amount: string
    expense_date: string
    description: string | null
    category: { id: number; name: string } | null
    creator: { id: number; name: string } | null
    deleted_at: string
}

interface PaginatedExpenses {
    data: Expense[]
    current_page: number
    last_page: number
    total: number
    from: number | null
    to: number | null
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{ expenses: PaginatedExpenses }>()

// ─── RBAC ─────────────────────────────────────────────────────────────────────

const { isOwner } = usePermissions()
const base = computed(() => isOwner.value ? '/shop' : '/staff')

// ─── Flash toast ──────────────────────────────────────────────────────────────

const page = usePage<AppPageProps>()

onMounted(() => {
    const flashToast = page.props.toast as { type: string; message: string } | undefined
    if (!flashToast) return
    switch (flashToast.type) {
        case 'success': toast.success(flashToast.message); break
        case 'error':   toast.error(flashToast.message);   break
        default:        toast(flashToast.message)
    }
})

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Finance', href: `${base.value}/finance` },
    { title: 'Expenses', href: `${base.value}/finance/expenses` },
    { title: 'Archive', href: `${base.value}/finance/expenses/archive` },
]

// ─── Actions ──────────────────────────────────────────────────────────────────

function restore(id: number) {
    router.post(`${base.value}/finance/expenses/${id}/restore`, {}, { preserveScroll: true })
}

// ─── Pagination ───────────────────────────────────────────────────────────────

function goToPage(p: number) {
    router.get(`${base.value}/finance/expenses/archive`, { page: p }, { preserveScroll: true, preserveState: true })
}

const visiblePages = computed(() => {
    const pages: number[] = []
    const current = props.expenses.current_page
    const total   = props.expenses.last_page
    for (let i = Math.max(1, current - 2); i <= Math.min(total, current + 2); i++) {
        pages.push(i)
    }
    return pages
})

// ─── Helpers ──────────────────────────────────────────────────────────────────

function currency(val: string | number | null): string {
    const num = typeof val === 'string' ? parseFloat(val) : (val ?? 0)
    return `₱${num.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
}

function formatDate(val: string): string {
    return new Date(val + 'T00:00:00').toLocaleDateString('en-PH', {
        year: 'numeric', month: 'short', day: 'numeric',
    })
}
</script>

<template>
    <Head title="Expense Archive" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Finance">
        <div class="px-6 space-y-6">

            <!-- ── Header ──────────────────────────────────────── -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Expense Archive</h2>
                    <p class="text-sm text-muted-foreground">
                        Archived expenses. Restore any record to bring it back.
                    </p>
                </div>
                <Button variant="outline" @click="router.visit(`${base}/finance/expenses`)">
                    Back to Expenses
                </Button>
            </div>

            <!-- ── Table ───────────────────────────────────────── -->
            <div class="rounded-xl border overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                            <th class="text-left px-4 py-3 font-medium">Title</th>
                            <th class="text-left px-4 py-3 font-medium hidden sm:table-cell">Category</th>
                            <th class="text-left px-4 py-3 font-medium hidden md:table-cell">Date</th>
                            <th class="text-right px-4 py-3 font-medium">Amount</th>
                            <th class="text-right px-4 py-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="exp in expenses.data" :key="exp.id"
                            class="border-b last:border-0 hover:bg-muted/20 transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ exp.title }}</p>
                                <p v-if="exp.description" class="text-xs text-muted-foreground">{{ exp.description }}</p>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground hidden sm:table-cell">
                                {{ exp.category?.name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground hidden md:table-cell">
                                {{ formatDate(exp.expense_date) }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-red-600">
                                {{ currency(exp.amount) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Button size="icon" variant="ghost" @click="restore(exp.id)">
                                    <RotateCcw class="h-4 w-4 text-green-600" />
                                </Button>
                            </td>
                        </tr>

                        <tr v-if="expenses.data.length === 0">
                            <td colspan="5" class="px-4 py-12 text-center text-sm text-muted-foreground">
                                <TrendingDown class="h-8 w-8 mx-auto mb-2 opacity-30" />
                                <p>No archived expenses.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ── Pagination ──────────────────────────────────── -->
            <div v-if="expenses.last_page > 1" class="flex items-center justify-between px-4 py-3">
                <p class="text-xs text-muted-foreground">
                    Showing {{ expenses.from }}–{{ expenses.to }} of {{ expenses.total }}
                </p>
                <div class="flex items-center gap-1">
                    <Button size="icon" variant="outline" :disabled="expenses.current_page === 1"
                        @click="goToPage(expenses.current_page - 1)">
                        <ChevronLeft class="h-4 w-4" />
                    </Button>
                    <Button v-for="p in visiblePages" :key="p" size="icon"
                        :variant="p === expenses.current_page ? 'default' : 'outline'"
                        @click="goToPage(p)">
                        {{ p }}
                    </Button>
                    <Button size="icon" variant="outline" :disabled="expenses.current_page === expenses.last_page"
                        @click="goToPage(expenses.current_page + 1)">
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                </div>
            </div>

        </div>
    </ShopLayout>
</template>
