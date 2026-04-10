<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { usePermissions } from '@/composables/usePermissions'
import {
    TrendingUp, TrendingDown, Wallet, AlertCircle,
    ReceiptText, ArrowRight, DollarSign, ArrowUpDown,
} from 'lucide-vue-next'
import {
    Chart as ChartJS, CategoryScale, LinearScale, BarElement,
    Title, Tooltip, Legend,
} from 'chart.js'
import { Bar } from 'vue-chartjs'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

// ─── Types ────────────────────────────────────────────────────────────────────

interface ChartEntry {
    month: string
    revenue: number
    expenses: number
    profit: number
}

interface TopCategory {
    name: string
    total: number
}

interface RecentExpense {
    id: number
    title: string
    amount: string
    expense_date: string
    category: { id: number; name: string } | null
    creator: { id: number; name: string } | null
}

interface DashboardData {
    revenue_this_month: number
    expenses_this_month: number
    profit_this_month: number
    total_revenue: number
    total_expenses: number
    total_profit: number
    unpaid_amount: number
    chart_data: ChartEntry[]
    top_categories: TopCategory[]
    recent_expenses: RecentExpense[]
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{ data: DashboardData }>()

// ─── RBAC ─────────────────────────────────────────────────────────────────────

const { isOwner, can } = usePermissions()
const base = computed(() => isOwner.value ? '/shop' : '/staff')

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Finance', href: `${base.value}/finance` },
    { title: 'Dashboard', href: `${base.value}/finance` },
]

// ─── Chart ────────────────────────────────────────────────────────────────────

const chartData = computed(() => ({
    labels: props.data.chart_data.map(d => d.month),
    datasets: [
        {
            label: 'Revenue',
            data: props.data.chart_data.map(d => d.revenue),
            backgroundColor: 'rgba(34, 197, 94, 0.7)',
            borderColor: 'rgb(34, 197, 94)',
            borderWidth: 1,
            borderRadius: 4,
        },
        {
            label: 'Expenses',
            data: props.data.chart_data.map(d => d.expenses),
            backgroundColor: 'rgba(239, 68, 68, 0.7)',
            borderColor: 'rgb(239, 68, 68)',
            borderWidth: 1,
            borderRadius: 4,
        },
        {
            label: 'Net Profit',
            data: props.data.chart_data.map(d => d.profit),
            backgroundColor: 'rgba(59, 130, 246, 0.7)',
            borderColor: 'rgb(59, 130, 246)',
            borderWidth: 1,
            borderRadius: 4,
        },
    ],
}))

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'top' as const },
        tooltip: {
            callbacks: {
                label: (ctx: any) => ` ₱${Number(ctx.raw).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`,
            },
        },
    },
    scales: {
        y: {
            ticks: {
                callback: (val: any) => `₱${Number(val).toLocaleString('en-PH')}`,
            },
        },
    },
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function currency(val: number): string {
    return `₱${val.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
}

function formatDate(val: string): string {
    return new Date(val + 'T00:00:00').toLocaleDateString('en-PH', {
        year: 'numeric', month: 'short', day: 'numeric',
    })
}
</script>

<template>
    <Head title="Finance Dashboard" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Finance">
        <div class="px-6 space-y-6">

            <!-- ── Header ──────────────────────────────────────── -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold">Finance Overview</h2>
                    <p class="text-sm text-muted-foreground">
                        Revenue, expenses, and profitability for your shop.
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <Button variant="outline" size="sm" @click="router.visit(`${base}/finance/income`)">
                        <TrendingUp class="h-4 w-4 mr-2" />
                        Income
                    </Button>
                    <Button variant="outline" size="sm" @click="router.visit(`${base}/finance/transactions`)">
                        <ArrowUpDown class="h-4 w-4 mr-2" />
                        Transactions
                    </Button>
                    <Button size="sm" @click="router.visit(`${base}/finance/expenses`)">
                        <ReceiptText class="h-4 w-4 mr-2" />
                        Expenses
                    </Button>
                </div>
            </div>

            <!-- ── This Month Stats ───────────────────────────── -->
            <div>
                <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-3">This Month</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <Card>
                        <CardContent class="pt-4 pb-4 flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-green-100 flex items-center justify-center shrink-0">
                                <TrendingUp class="h-5 w-5 text-green-600" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-lg font-bold truncate">{{ currency(data.revenue_this_month) }}</p>
                                <p class="text-xs text-muted-foreground">Revenue</p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="pt-4 pb-4 flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                                <TrendingDown class="h-5 w-5 text-red-600" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-lg font-bold truncate">{{ currency(data.expenses_this_month) }}</p>
                                <p class="text-xs text-muted-foreground">Expenses</p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="pt-4 pb-4 flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg flex items-center justify-center shrink-0"
                                :class="data.profit_this_month >= 0 ? 'bg-blue-100' : 'bg-orange-100'">
                                <DollarSign class="h-5 w-5"
                                    :class="data.profit_this_month >= 0 ? 'text-blue-600' : 'text-orange-600'" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-lg font-bold truncate"
                                    :class="data.profit_this_month >= 0 ? 'text-blue-700' : 'text-orange-600'">
                                    {{ currency(data.profit_this_month) }}
                                </p>
                                <p class="text-xs text-muted-foreground">Net Profit</p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="pt-4 pb-4 flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                                <AlertCircle class="h-5 w-5 text-amber-600" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-lg font-bold truncate text-amber-700">{{ currency(data.unpaid_amount) }}</p>
                                <p class="text-xs text-muted-foreground">Unpaid Orders</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- ── All-time Stats ─────────────────────────────── -->
            <div>
                <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-3">All Time</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <Card>
                        <CardContent class="pt-4 pb-4">
                            <p class="text-xs text-muted-foreground mb-1">Total Revenue</p>
                            <p class="text-2xl font-bold text-green-700">{{ currency(data.total_revenue) }}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="pt-4 pb-4">
                            <p class="text-xs text-muted-foreground mb-1">Total Expenses</p>
                            <p class="text-2xl font-bold text-red-600">{{ currency(data.total_expenses) }}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="pt-4 pb-4">
                            <p class="text-xs text-muted-foreground mb-1">Net Profit</p>
                            <p class="text-2xl font-bold"
                                :class="data.total_profit >= 0 ? 'text-blue-700' : 'text-orange-600'">
                                {{ currency(data.total_profit) }}
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- ── Chart & Top Categories ─────────────────────── -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <Card class="lg:col-span-2">
                    <CardHeader>
                        <CardTitle class="text-sm font-semibold">Monthly Overview (Last 6 Months)</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="h-64">
                            <Bar :data="chartData" :options="chartOptions" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm font-semibold">Top Expenses This Month</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="data.top_categories.length === 0"
                            class="flex flex-col items-center justify-center h-32 text-muted-foreground text-sm">
                            <Wallet class="h-6 w-6 mb-2 opacity-40" />
                            No expenses recorded this month.
                        </div>
                        <ul v-else class="space-y-3">
                            <li v-for="cat in data.top_categories" :key="cat.name"
                                class="flex items-center justify-between">
                                <span class="text-sm font-medium truncate">{{ cat.name }}</span>
                                <span class="text-sm font-bold text-red-600 shrink-0 ml-2">{{ currency(cat.total) }}</span>
                            </li>
                        </ul>
                    </CardContent>
                </Card>
            </div>

            <!-- ── Recent Expenses ────────────────────────────── -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle class="text-sm font-semibold">Recent Expenses</CardTitle>
                    <Button size="sm" variant="ghost" @click="router.visit(`${base}/finance/expenses`)">
                        View All <ArrowRight class="h-3.5 w-3.5 ml-1" />
                    </Button>
                </CardHeader>
                <CardContent class="p-0">
                    <div v-if="data.recent_expenses.length === 0"
                        class="flex flex-col items-center justify-center py-10 text-muted-foreground text-sm">
                        <ReceiptText class="h-6 w-6 mb-2 opacity-40" />
                        No expenses recorded yet.
                    </div>
                    <table v-else class="w-full text-sm">
                        <thead>
                            <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                                <th class="text-left px-4 py-3 font-medium">Title</th>
                                <th class="text-left px-4 py-3 font-medium hidden sm:table-cell">Category</th>
                                <th class="text-left px-4 py-3 font-medium hidden sm:table-cell">Date</th>
                                <th class="text-right px-4 py-3 font-medium">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="exp in data.recent_expenses" :key="exp.id"
                                class="border-b last:border-0 hover:bg-muted/20 transition-colors">
                                <td class="px-4 py-3 font-medium">{{ exp.title }}</td>
                                <td class="px-4 py-3 text-muted-foreground hidden sm:table-cell">
                                    {{ exp.category?.name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-muted-foreground hidden sm:table-cell">
                                    {{ formatDate(exp.expense_date) }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-red-600">
                                    {{ currency(parseFloat(exp.amount as any)) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>

        </div>
    </ShopLayout>
</template>
