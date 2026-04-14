<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { usePermissions } from '@/composables/usePermissions'

import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select'
import { TrendingUp, ChevronLeft, ChevronRight } from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface IncomeEntry {
    id: number
    order_number: string
    customer_name: string
    service: { id: number; service_name: string } | null
    amount_paid: string
    total_amount: string
    payment_status: string
    payment_method: string | null
    paid_at: string | null
    created_at: string
}

interface PaginatedIncome {
    data: IncomeEntry[]
    current_page: number
    last_page: number
    total: number
    from: number | null
    to: number | null
}

interface Stats {
    total_income: number
    this_month_income: number
    total_count: number
    unpaid_count: number
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    income: PaginatedIncome
    stats: Stats
    filters: {
        dateFrom?: string | null
        dateTo?: string | null
        method?: string | null
    }
}>()

// ─── RBAC ─────────────────────────────────────────────────────────────────────

const { isOwner } = usePermissions()
const base = computed(() => isOwner.value ? '/shop' : '/staff')

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Finance', href: `${base.value}/finance` },
    { title: 'Income', href: `${base.value}/finance/income` },
]

// ─── Filters ──────────────────────────────────────────────────────────────────

const dateFrom = ref(props.filters.dateFrom ?? '')
const dateTo   = ref(props.filters.dateTo ?? '')
const method   = ref(props.filters.method ?? '')

function applyFilters() {
    router.get(
        `${base.value}/finance/income`,
        {
            date_from: dateFrom.value || undefined,
            date_to:   dateTo.value   || undefined,
            method:    method.value   || undefined,
        },
        { replace: true },
    )
}

function clearFilters() {
    dateFrom.value = ''
    dateTo.value   = ''
    method.value   = ''
    router.get(`${base.value}/finance/income`, {}, { replace: true })
}

// ─── Pagination ───────────────────────────────────────────────────────────────

function goToPage(p: number) {
    router.get(
        `${base.value}/finance/income`,
        { page: p, ...props.filters },
        { preserveScroll: true },
    )
}

const visiblePages = computed(() => {
    const pages: number[] = []
    const current = props.income.current_page
    const total   = props.income.last_page
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

function formatDate(val: string | null): string {
    if (!val) return '—'
    return new Date(val).toLocaleDateString('en-PH', {
        year: 'numeric', month: 'short', day: 'numeric',
    })
}

function methodLabel(val: string | null): string {
    const map: Record<string, string> = { cash: 'Cash', gcash: 'GCash', maya: 'Maya' }
    return val ? (map[val] ?? val) : '—'
}

function statusClass(status: string): string {
    return {
        paid:    'bg-green-100 text-green-700',
        partial: 'bg-amber-100 text-amber-700',
        unpaid:  'bg-red-100 text-red-700',
    }[status] ?? 'bg-gray-100 text-gray-700'
}
</script>

<template>
    <Head title="Income" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Finance">
        <div class="px-6 space-y-6">

            <!-- ── Header ──────────────────────────────────────── -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold">Income</h2>
                    <p class="text-sm text-muted-foreground">
                        Revenue collected from customer orders.
                    </p>
                </div>
                <Button variant="outline" @click="router.visit(`${base}/finance/transactions`)">
                    View Transactions
                </Button>
            </div>

            <!-- ── Stats ───────────────────────────────────────── -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <Card>
                    <CardContent class="pt-4 pb-4 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-green-100 flex items-center justify-center">
                            <TrendingUp class="h-5 w-5 text-green-600" />
                        </div>
                        <div>
                            <p class="text-lg font-bold">{{ currency(stats.total_income) }}</p>
                            <p class="text-xs text-muted-foreground">Total Collected</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-4 pb-4 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                            <TrendingUp class="h-5 w-5 text-emerald-600" />
                        </div>
                        <div>
                            <p class="text-lg font-bold">{{ currency(stats.this_month_income) }}</p>
                            <p class="text-xs text-muted-foreground">This Month</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-4 pb-4">
                        <p class="text-2xl font-bold">{{ stats.total_count }}</p>
                        <p class="text-xs text-muted-foreground">Paid Transactions</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-4 pb-4">
                        <p class="text-2xl font-bold text-amber-600">{{ stats.unpaid_count }}</p>
                        <p class="text-xs text-muted-foreground">Unpaid Orders</p>
                    </CardContent>
                </Card>
            </div>

            <!-- ── Filters ─────────────────────────────────────── -->
            <div class="flex flex-col sm:flex-row gap-2 flex-wrap">
                <Input v-model="dateFrom" type="date" class="sm:w-40" placeholder="From" />
                <Input v-model="dateTo"   type="date" class="sm:w-40" placeholder="To" />
                <Select v-model="method">
                    <SelectTrigger class="sm:w-40">
                        <SelectValue placeholder="All methods" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">All methods</SelectItem>
                        <SelectItem value="cash">Cash</SelectItem>
                        <SelectItem value="gcash">GCash</SelectItem>
                        <SelectItem value="maya">Maya</SelectItem>
                    </SelectContent>
                </Select>
                <Button @click="applyFilters">Filter</Button>
                <Button variant="outline" @click="clearFilters">Clear</Button>
            </div>

            <!-- ── Table ───────────────────────────────────────── -->
            <div class="rounded-xl border overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                            <th class="text-left px-4 py-3 font-medium">Order #</th>
                            <th class="text-left px-4 py-3 font-medium">Customer</th>
                            <th class="text-left px-4 py-3 font-medium hidden sm:table-cell">Service</th>
                            <th class="text-left px-4 py-3 font-medium hidden md:table-cell">Method</th>
                            <th class="text-left px-4 py-3 font-medium hidden md:table-cell">Date Paid</th>
                            <th class="text-left px-4 py-3 font-medium hidden lg:table-cell">Status</th>
                            <th class="text-right px-4 py-3 font-medium">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="entry in income.data" :key="entry.id"
                            class="border-b last:border-0 hover:bg-muted/20 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs text-muted-foreground">
                                {{ entry.order_number }}
                            </td>
                            <td class="px-4 py-3 font-medium">{{ entry.customer_name }}</td>
                            <td class="px-4 py-3 text-muted-foreground hidden sm:table-cell">
                                {{ entry.service?.service_name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                                    {{ methodLabel(entry.payment_method) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground hidden md:table-cell">
                                {{ formatDate(entry.paid_at) }}
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full capitalize"
                                    :class="statusClass(entry.payment_status)">
                                    {{ entry.payment_status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-green-700">
                                {{ currency(entry.amount_paid) }}
                            </td>
                        </tr>

                        <tr v-if="income.data.length === 0">
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-muted-foreground">
                                <TrendingUp class="h-8 w-8 mx-auto mb-2 opacity-30" />
                                <p>No income records found for the selected filters.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ── Pagination ──────────────────────────────────── -->
            <div v-if="income.last_page > 1" class="flex items-center justify-between px-4 py-3">
                <p class="text-xs text-muted-foreground">
                    Showing {{ income.from }}–{{ income.to }} of {{ income.total }}
                </p>
                <div class="flex items-center gap-1">
                    <Button size="icon" variant="outline" :disabled="income.current_page === 1"
                        @click="goToPage(income.current_page - 1)">
                        <ChevronLeft class="h-4 w-4" />
                    </Button>
                    <Button v-for="p in visiblePages" :key="p" size="icon"
                        :variant="p === income.current_page ? 'default' : 'outline'"
                        @click="goToPage(p)">
                        {{ p }}
                    </Button>
                    <Button size="icon" variant="outline" :disabled="income.current_page === income.last_page"
                        @click="goToPage(income.current_page + 1)">
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                </div>
            </div>

        </div>
    </ShopLayout>
</template>
