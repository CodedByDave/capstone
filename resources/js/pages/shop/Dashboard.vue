<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import ShopOrder from '@/pages/shop/ShopOrder.vue'
import { dashboard } from '@/routes'
import { type BreadcrumbItem } from '@/types'
import { Head, usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
    Users, GitBranch, Package, AlertTriangle,
    TrendingUp, TrendingDown, ArrowUpRight, ArrowDownRight,
    Activity, BoxSelect, RefreshCcw,
} from 'lucide-vue-next'
import { ref, computed, onMounted } from 'vue'

// ─── Types ────────────────────────────────────────────────────────────────────

interface MovementPoint   { month: string; stock_in: number; stock_out: number }
interface CategoryPoint   { label: string; count: number }
interface BranchEmployee  { branch: string; count: number; status: string }
interface LowStockItem    { name: string; sku: string; quantity: number; min_stock: number; status: string; category: string }
interface RecentMovement  { item: string; sku: string; type: string; quantity: number; before: number; after: number; by: string; notes: string | null; date: string }

// ─── Props ────────────────────────────────────────────────────────────────────

const { props } = usePage<{
    auth: { user: any }
    modules: any[]
    order?: {
        status: string
        shop_name: string
        subscription_plan: string | null
        expires_at: string | null
        modules: { name: string; price: number }[]
        total_price: number
    }
    stats?: {
        employees:        { total: number; active: number; inactive: number }
        branches:         { total: number; active: number }
        inventory:        { total: number; low_stock: number; out_of_stock: number }
        low_stock_alerts: number
        movements:        { this_month: number; change: number }
    }
    movement_chart?:       MovementPoint[]
    category_breakdown?:   CategoryPoint[]
    employees_per_branch?: BranchEmployee[]
    low_stock_items?:      LowStockItem[]
    recent_movements?:     RecentMovement[]
}>()

const user    = props.auth.user
const isPaid  = computed(() => props.order?.status === 'paid')
const showOrder = ref(false)

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url }
]

// ─── Charts ───────────────────────────────────────────────────────────────────

const movementChartRef = ref<HTMLCanvasElement | null>(null)
const categoryChartRef = ref<HTMLCanvasElement | null>(null)
const branchChartRef   = ref<HTMLCanvasElement | null>(null)

function loadScript(src: string): Promise<void> {
    return new Promise((resolve) => {
        if (document.querySelector(`script[src="${src}"]`)) return resolve()
        const script = document.createElement('script')
        script.src = src
        script.onload = () => resolve()
        document.head.appendChild(script)
    })
}

onMounted(async () => {
    if (!isPaid.value) return
    await loadScript('https://cdn.jsdelivr.net/npm/chart.js')

    // @ts-ignore
    const Chart = window.Chart

    const movData   = props.movement_chart       ?? []
    const catData   = props.category_breakdown   ?? []
    const branchData= props.employees_per_branch ?? []

    /* ── Movement Bar Chart ── */
    if (movementChartRef.value && movData.length) {
        new Chart(movementChartRef.value, {
            type: 'bar',
            data: {
                labels: movData.map(d => d.month),
                datasets: [
                    {
                        label: 'Stock In',
                        data: movData.map(d => d.stock_in),
                        backgroundColor: 'rgba(16,185,129,0.7)',
                        borderRadius: 4,
                    },
                    {
                        label: 'Stock Out',
                        data: movData.map(d => d.stock_out),
                        backgroundColor: 'rgba(239,68,68,0.7)',
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } },
                },
            },
        })
    }

    /* ── Category Doughnut ── */
    if (categoryChartRef.value && catData.length) {
        const colors = ['#6366f1','#10b981','#f59e0b','#ef4444','#3b82f6','#8b5cf6','#ec4899','#14b8a6']
        new Chart(categoryChartRef.value, {
            type: 'doughnut',
            data: {
                labels: catData.map(d => d.label),
                datasets: [{
                    data: catData.map(d => d.count),
                    backgroundColor: colors.slice(0, catData.length),
                    borderWidth: 2,
                    borderColor: '#fff',
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 14, font: { size: 11 } } },
                },
                cutout: '60%',
            },
        })
    }

    /* ── Employees per Branch Bar ── */
    if (branchChartRef.value && branchData.length) {
        new Chart(branchChartRef.value, {
            type: 'bar',
            data: {
                labels: branchData.map(d => d.branch),
                datasets: [{
                    label: 'Employees',
                    data: branchData.map(d => d.count),
                    backgroundColor: branchData.map(d =>
                        d.status === 'Active' ? 'rgba(99,102,241,0.75)' : 'rgba(156,163,175,0.5)'
                    ),
                    borderRadius: 5,
                }],
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } },
                },
            },
        })
    }
})

// ─── Helpers ──────────────────────────────────────────────────────────────────

const stockStatusConfig: Record<string, { label: string; cls: string }> = {
    low:         { label: 'Low Stock',    cls: 'bg-amber-100 text-amber-700'  },
    out_of_stock:{ label: 'Out of Stock', cls: 'bg-red-100 text-red-700'      },
    normal:      { label: 'Normal',       cls: 'bg-green-100 text-green-700'  },
    overstock:   { label: 'Overstock',    cls: 'bg-blue-100 text-blue-700'    },
}

const movementTypeConfig: Record<string, { label: string; cls: string }> = {
    in:       { label: 'Stock In',    cls: 'bg-emerald-100 text-emerald-700' },
    out:      { label: 'Stock Out',   cls: 'bg-red-100 text-red-700'         },
    adjust:   { label: 'Adjustment',  cls: 'bg-blue-100 text-blue-700'       },
    transfer: { label: 'Transfer',    cls: 'bg-violet-100 text-violet-700'   },
}
</script>

<template>
    <Head title="Shop Dashboard" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Dashboard">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">

            <!-- ══════════════ PAID: Live Dashboard ══════════════ -->
            <template v-if="isPaid">

                <!-- Welcome Banner -->
                <div class="rounded-xl bg-gradient-to-r from-indigo-50 to-emerald-50 dark:from-neutral-800 dark:to-neutral-800 border border-border p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Welcome back, {{ user.name }} 👋
                        </h2>
                        <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">
                            Here's a live overview of
                            <span class="font-semibold text-gray-800 dark:text-white">{{ props.order?.shop_name }}</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-muted-foreground">
                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 font-medium capitalize">
                            {{ props.order?.subscription_plan ?? 'Active' }}
                        </span>
                        <span v-if="props.order?.expires_at" class="text-muted-foreground">
                            Expires {{ new Date(props.order.expires_at).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' }) }}
                        </span>
                    </div>
                </div>

                <!-- ── STAT CARDS ── -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

                    <!-- Employees -->
                    <Card>
                        <CardContent class="pt-5">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Employees</p>
                                <div class="h-8 w-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                                    <Users class="h-4 w-4 text-indigo-600" />
                                </div>
                            </div>
                            <p class="text-3xl font-bold">{{ props.stats?.employees.total ?? 0 }}</p>
                            <div class="flex gap-2 mt-1.5">
                                <span class="text-xs text-emerald-600 font-medium">{{ props.stats?.employees.active ?? 0 }} active</span>
                                <span class="text-xs text-muted-foreground">·</span>
                                <span class="text-xs text-muted-foreground">{{ props.stats?.employees.inactive ?? 0 }} inactive</span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Branches -->
                    <Card>
                        <CardContent class="pt-5">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Branches</p>
                                <div class="h-8 w-8 rounded-lg bg-violet-100 flex items-center justify-center">
                                    <GitBranch class="h-4 w-4 text-violet-600" />
                                </div>
                            </div>
                            <p class="text-3xl font-bold">{{ props.stats?.branches.total ?? 0 }}</p>
                            <p class="text-xs text-emerald-600 font-medium mt-1.5">{{ props.stats?.branches.active ?? 0 }} active</p>
                        </CardContent>
                    </Card>

                    <!-- Inventory -->
                    <Card>
                        <CardContent class="pt-5">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Inventory</p>
                                <div class="h-8 w-8 rounded-lg bg-amber-100 flex items-center justify-center">
                                    <Package class="h-4 w-4 text-amber-600" />
                                </div>
                            </div>
                            <p class="text-3xl font-bold">{{ props.stats?.inventory.total ?? 0 }}</p>
                            <div class="flex gap-2 mt-1.5">
                                <span class="text-xs text-amber-600 font-medium">{{ props.stats?.inventory.low_stock ?? 0 }} low</span>
                                <span class="text-xs text-muted-foreground">·</span>
                                <span class="text-xs text-red-500 font-medium">{{ props.stats?.inventory.out_of_stock ?? 0 }} out</span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Low Stock Alerts -->
                    <Card>
                        <CardContent class="pt-5">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Alerts</p>
                                <div class="h-8 w-8 rounded-lg bg-red-100 flex items-center justify-center">
                                    <AlertTriangle class="h-4 w-4 text-red-600" />
                                </div>
                            </div>
                            <p class="text-3xl font-bold" :class="(props.stats?.low_stock_alerts ?? 0) > 0 ? 'text-red-600' : ''">
                                {{ props.stats?.low_stock_alerts ?? 0 }}
                            </p>
                            <div class="flex items-center gap-1 mt-1.5">
                                <ArrowUpRight v-if="(props.stats?.movements.change ?? 0) >= 0" class="h-3.5 w-3.5 text-emerald-500" />
                                <ArrowDownRight v-else class="h-3.5 w-3.5 text-red-500" />
                                <p class="text-xs text-muted-foreground">
                                    <span :class="(props.stats?.movements.change ?? 0) >= 0 ? 'text-emerald-500' : 'text-red-500'" class="font-medium">
                                        {{ Math.abs(props.stats?.movements.change ?? 0) }}%
                                    </span>
                                    movements vs last month
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                </div>

                <!-- ── CHARTS ROW 1 ── -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <!-- Movement Bar Chart -->
                    <Card class="md:col-span-2">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-semibold flex items-center gap-2">
                                <Activity class="h-4 w-4 text-muted-foreground" />
                                Inventory Movements (12 months)
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="(props.movement_chart?.length ?? 0) === 0" class="flex items-center justify-center h-40 text-sm text-muted-foreground">
                                No movement data yet.
                            </div>
                            <canvas v-else ref="movementChartRef" height="130"></canvas>
                        </CardContent>
                    </Card>

                    <!-- Category Doughnut -->
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-semibold flex items-center gap-2">
                                <BoxSelect class="h-4 w-4 text-muted-foreground" />
                                Items by Category
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="flex items-center justify-center">
                            <div v-if="(props.category_breakdown?.length ?? 0) === 0" class="flex items-center justify-center h-40 text-sm text-muted-foreground">
                                No categories yet.
                            </div>
                            <canvas v-else ref="categoryChartRef" height="200"></canvas>
                        </CardContent>
                    </Card>

                </div>

                <!-- ── CHARTS ROW 2 + TABLES ── -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <!-- Employees per Branch -->
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-semibold flex items-center gap-2">
                                <Users class="h-4 w-4 text-muted-foreground" />
                                Employees per Branch
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="(props.employees_per_branch?.length ?? 0) === 0" class="flex items-center justify-center h-40 text-sm text-muted-foreground">
                                No branches yet.
                            </div>
                            <canvas v-else ref="branchChartRef" height="180"></canvas>
                        </CardContent>
                    </Card>

                    <!-- Low Stock Items -->
                    <Card class="md:col-span-2">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-semibold flex items-center gap-2">
                                <AlertTriangle class="h-4 w-4 text-amber-500" />
                                Low Stock Items
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="(props.low_stock_items?.length ?? 0) === 0"
                                class="flex items-center justify-center h-20 text-sm text-muted-foreground">
                                All items are sufficiently stocked. ✅
                            </div>
                            <div v-else class="rounded-lg border overflow-visible">
                                <table class="w-full text-xs">
                                    <thead>
                                        <tr class="bg-muted/40 text-muted-foreground border-b">
                                            <th class="text-left px-3 py-2 font-medium">Item</th>
                                            <th class="text-left px-3 py-2 font-medium">Category</th>
                                            <th class="text-right px-3 py-2 font-medium">Qty</th>
                                            <th class="text-right px-3 py-2 font-medium">Min</th>
                                            <th class="text-left px-3 py-2 font-medium">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in props.low_stock_items" :key="item.sku"
                                            class="border-b last:border-0 hover:bg-muted/20 transition-colors">
                                            <td class="px-3 py-2">
                                                <p class="font-medium text-foreground">{{ item.name }}</p>
                                                <p class="text-muted-foreground font-mono">{{ item.sku }}</p>
                                            </td>
                                            <td class="px-3 py-2 text-muted-foreground">{{ item.category }}</td>
                                            <td class="px-3 py-2 text-right font-bold"
                                                :class="item.quantity === 0 ? 'text-red-600' : 'text-amber-600'">
                                                {{ item.quantity }}
                                            </td>
                                            <td class="px-3 py-2 text-right text-muted-foreground">{{ item.min_stock }}</td>
                                            <td class="px-3 py-2">
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                                    :class="stockStatusConfig[item.status]?.cls ?? 'bg-gray-100 text-gray-500'">
                                                    {{ stockStatusConfig[item.status]?.label ?? item.status }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>

                </div>

                <!-- ── RECENT MOVEMENTS TABLE ── -->
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-semibold flex items-center gap-2">
                            <RefreshCcw class="h-4 w-4 text-muted-foreground" />
                            Recent Inventory Movements
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="(props.recent_movements?.length ?? 0) === 0"
                            class="flex items-center justify-center h-20 text-sm text-muted-foreground">
                            No movements recorded yet.
                        </div>
                        <div v-else class="overflow-x-auto">
                            <div class="rounded-lg border overflow-visible">
                                <table class="w-full text-xs">
                                    <thead>
                                        <tr class="bg-muted/40 text-muted-foreground border-b">
                                            <th class="text-left px-3 py-2 font-medium">Item</th>
                                            <th class="text-left px-3 py-2 font-medium">Type</th>
                                            <th class="text-right px-3 py-2 font-medium">Qty</th>
                                            <th class="text-right px-3 py-2 font-medium">Before</th>
                                            <th class="text-right px-3 py-2 font-medium">After</th>
                                            <th class="text-left px-3 py-2 font-medium">By</th>
                                            <th class="text-left px-3 py-2 font-medium">Notes</th>
                                            <th class="text-left px-3 py-2 font-medium">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(mov, i) in props.recent_movements" :key="i"
                                            class="border-b last:border-0 hover:bg-muted/20 transition-colors">
                                            <td class="px-3 py-2">
                                                <p class="font-medium text-foreground">{{ mov.item }}</p>
                                                <p class="text-muted-foreground font-mono">{{ mov.sku }}</p>
                                            </td>
                                            <td class="px-3 py-2">
                                                <span class="px-2 py-0.5 rounded-full font-medium capitalize"
                                                    :class="movementTypeConfig[mov.type]?.cls ?? 'bg-gray-100 text-gray-500'">
                                                    {{ movementTypeConfig[mov.type]?.label ?? mov.type }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 text-right font-bold"
                                                :class="mov.type === 'in' ? 'text-emerald-600' : 'text-red-600'">
                                                {{ mov.type === 'in' ? '+' : '-' }}{{ mov.quantity }}
                                            </td>
                                            <td class="px-3 py-2 text-right text-muted-foreground">{{ mov.before }}</td>
                                            <td class="px-3 py-2 text-right font-medium">{{ mov.after }}</td>
                                            <td class="px-3 py-2 text-muted-foreground">{{ mov.by }}</td>
                                            <td class="px-3 py-2 text-muted-foreground max-w-32 truncate">{{ mov.notes ?? '—' }}</td>
                                            <td class="px-3 py-2 text-muted-foreground whitespace-nowrap">{{ mov.date }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </CardContent>
                </Card>

            </template>

            <!-- ══════════════ NOT PAID: Order Form ══════════════ -->
            <template v-else>
                <div v-if="!showOrder" class="rounded-xl bg-gray-100 dark:bg-neutral-800 p-6">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">
                        Welcome {{ user.name }} 👋
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2 max-w-2xl">
                        Complete your shop details and select the modules you need. You will be redirected to PayMongo for payment.
                    </p>
                    <Button class="mt-4" @click="showOrder = true">Get Started</Button>
                </div>
                <ShopOrder v-if="showOrder" />
            </template>

        </div>
    </ShopLayout>
</template>
