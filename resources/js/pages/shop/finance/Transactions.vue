<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { usePermissions } from '@/composables/usePermissions'

import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { TrendingUp, TrendingDown, DollarSign, ArrowUpDown } from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface Transaction {
    id: string
    type: 'income' | 'expense'
    date: string
    title: string
    reference: string
    category: string
    amount: number
}

interface Summary {
    total_income: number
    total_expenses: number
    net: number
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    transactions: Transaction[]
    summary: Summary
    filters: { dateFrom: string; dateTo: string }
}>()

// ─── RBAC ─────────────────────────────────────────────────────────────────────

const { isOwner } = usePermissions()
const base = computed(() => isOwner.value ? '/shop' : '/staff')

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Finance', href: `${base.value}/finance` },
    { title: 'Transactions', href: `${base.value}/finance/transactions` },
]

// ─── Filters ──────────────────────────────────────────────────────────────────

const dateFrom = ref(props.filters.dateFrom ?? '')
const dateTo   = ref(props.filters.dateTo ?? '')
const typeFilter = ref<'all' | 'income' | 'expense'>('all')

const filtered = computed(() =>
    typeFilter.value === 'all'
        ? props.transactions
        : props.transactions.filter(t => t.type === typeFilter.value)
)

function applyFilters() {
    router.get(
        `${base.value}/finance/transactions`,
        {
            date_from: dateFrom.value || undefined,
            date_to:   dateTo.value   || undefined,
        },
        { replace: true },
    )
}

function clearFilters() {
    const now = new Date()
    dateFrom.value = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01`
    dateTo.value   = now.toISOString().slice(0, 10)
    router.get(
        `${base.value}/finance/transactions`,
        { date_from: dateFrom.value, date_to: dateTo.value },
        { replace: true },
    )
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
    <Head title="Transactions" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Finance">
        <div class="px-6 space-y-6">

            <!-- ── Header ──────────────────────────────────────── -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold">Transactions</h2>
                    <p class="text-sm text-muted-foreground">
                        Combined ledger of income and expenses for the selected period.
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" @click="router.visit(`${base}/finance/income`)">
                        Income
                    </Button>
                    <Button variant="outline" size="sm" @click="router.visit(`${base}/finance/expenses`)">
                        Expenses
                    </Button>
                </div>
            </div>

            <!-- ── Summary Cards ───────────────────────────────── -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <Card>
                    <CardContent class="pt-4 pb-4 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-green-100 flex items-center justify-center">
                            <TrendingUp class="h-5 w-5 text-green-600" />
                        </div>
                        <div>
                            <p class="text-lg font-bold text-green-700">{{ currency(summary.total_income) }}</p>
                            <p class="text-xs text-muted-foreground">Total Income</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-4 pb-4 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-red-100 flex items-center justify-center">
                            <TrendingDown class="h-5 w-5 text-red-600" />
                        </div>
                        <div>
                            <p class="text-lg font-bold text-red-600">{{ currency(summary.total_expenses) }}</p>
                            <p class="text-xs text-muted-foreground">Total Expenses</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-4 pb-4 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg flex items-center justify-center"
                            :class="summary.net >= 0 ? 'bg-blue-100' : 'bg-orange-100'">
                            <DollarSign class="h-5 w-5"
                                :class="summary.net >= 0 ? 'text-blue-600' : 'text-orange-600'" />
                        </div>
                        <div>
                            <p class="text-lg font-bold"
                                :class="summary.net >= 0 ? 'text-blue-700' : 'text-orange-600'">
                                {{ currency(summary.net) }}
                            </p>
                            <p class="text-xs text-muted-foreground">Net</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- ── Filters ─────────────────────────────────────── -->
            <div class="flex flex-col sm:flex-row gap-2 flex-wrap items-center">
                <Input v-model="dateFrom" type="date" class="sm:w-40" />
                <span class="text-muted-foreground text-sm">to</span>
                <Input v-model="dateTo"   type="date" class="sm:w-40" />
                <Button @click="applyFilters">Apply</Button>
                <Button variant="outline" @click="clearFilters">This Month</Button>

                <!-- Type toggle -->
                <div class="flex rounded-lg border overflow-hidden ml-auto">
                    <button
                        class="px-3 py-1.5 text-xs font-medium transition-colors"
                        :class="typeFilter === 'all' ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'"
                        @click="typeFilter = 'all'">
                        All
                    </button>
                    <button
                        class="px-3 py-1.5 text-xs font-medium transition-colors border-l"
                        :class="typeFilter === 'income' ? 'bg-green-600 text-white' : 'hover:bg-muted'"
                        @click="typeFilter = 'income'">
                        Income
                    </button>
                    <button
                        class="px-3 py-1.5 text-xs font-medium transition-colors border-l"
                        :class="typeFilter === 'expense' ? 'bg-red-600 text-white' : 'hover:bg-muted'"
                        @click="typeFilter = 'expense'">
                        Expenses
                    </button>
                </div>
            </div>

            <!-- ── Ledger Table ────────────────────────────────── -->
            <div class="rounded-xl border overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                            <th class="text-left px-4 py-3 font-medium">Date</th>
                            <th class="text-left px-4 py-3 font-medium">Type</th>
                            <th class="text-left px-4 py-3 font-medium">Description</th>
                            <th class="text-left px-4 py-3 font-medium hidden sm:table-cell">Category / Method</th>
                            <th class="text-left px-4 py-3 font-medium hidden md:table-cell">Reference</th>
                            <th class="text-right px-4 py-3 font-medium">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="tx in filtered" :key="tx.id"
                            class="border-b last:border-0 hover:bg-muted/20 transition-colors">
                            <td class="px-4 py-3 text-muted-foreground text-xs whitespace-nowrap">
                                {{ formatDate(tx.date) }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="flex items-center gap-1 text-xs font-semibold"
                                    :class="tx.type === 'income' ? 'text-green-700' : 'text-red-600'">
                                    <TrendingUp v-if="tx.type === 'income'" class="h-3.5 w-3.5" />
                                    <TrendingDown v-else class="h-3.5 w-3.5" />
                                    {{ tx.type === 'income' ? 'Income' : 'Expense' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-medium max-w-[180px] truncate">{{ tx.title }}</td>
                            <td class="px-4 py-3 text-muted-foreground hidden sm:table-cell">{{ tx.category }}</td>
                            <td class="px-4 py-3 text-muted-foreground text-xs hidden md:table-cell">{{ tx.reference }}</td>
                            <td class="px-4 py-3 text-right font-bold"
                                :class="tx.type === 'income' ? 'text-green-700' : 'text-red-600'">
                                {{ tx.type === 'income' ? '+' : '-' }}{{ currency(tx.amount) }}
                            </td>
                        </tr>

                        <tr v-if="filtered.length === 0">
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-muted-foreground">
                                <ArrowUpDown class="h-8 w-8 mx-auto mb-2 opacity-30" />
                                <p>No transactions found for the selected period.</p>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot v-if="filtered.length > 0">
                        <tr class="bg-muted/30 border-t font-semibold text-sm">
                            <td colspan="5" class="px-4 py-3 text-right text-muted-foreground">
                                {{ filtered.length }} transaction{{ filtered.length !== 1 ? 's' : '' }}
                            </td>
                            <td class="px-4 py-3 text-right"
                                :class="summary.net >= 0 ? 'text-blue-700' : 'text-orange-600'">
                                Net: {{ currency(
                                    typeFilter === 'income'  ? summary.total_income :
                                    typeFilter === 'expense' ? summary.total_expenses :
                                    summary.net
                                ) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </ShopLayout>
</template>
