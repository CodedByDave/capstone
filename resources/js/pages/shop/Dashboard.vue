<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import CheckoutConfirm from '@/pages/shop/CheckoutConfirm.vue'
import { dashboard } from '@/routes'
import { type BreadcrumbItem } from '@/types'
import { Head, usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
    Users, GitBranch, Package, AlertTriangle,
    ArrowUpRight, ArrowDownRight, Activity, BoxSelect,
    RefreshCcw, Clock, Lightbulb, TrendingUp, TrendingDown,
    ShieldAlert, CheckCircle2, Info
} from 'lucide-vue-next'
import { ref, computed, onMounted } from 'vue'

// ─── Types ────────────────────────────────────────────────────────────────────

interface MovementPoint { month: string; stock_in: number; stock_out: number }
interface CategoryPoint { label: string; count: number }
interface BranchEmployee { branch: string; count: number; status: string }
interface LowStockItem { name: string; sku: string; quantity: number; min_stock: number; status: string; category: string }
interface RecentMovement { item: string; sku: string; type: string; quantity: number; before: number; after: number; by: string; notes: string | null; date: string }

interface DSSInsight {
    type: 'success' | 'warning' | 'danger' | 'info'
    title: string
    message: string
}

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
        employees: { total: number; active: number; inactive: number }
        branches: { total: number; active: number }
        inventory: { total: number; low_stock: number; out_of_stock: number }
        low_stock_alerts: number
        movements: { this_month: number; change: number }
    }
    shop?: {
        shop_name?: string
        phone?: string
        block_street?: string
        municipality?: string
        barangay?: string
        postal_code?: string
        status?: string
    } | null
    movement_chart?: MovementPoint[]
    category_breakdown?: CategoryPoint[]
    employees_per_branch?: BranchEmployee[]
    low_stock_items?: LowStockItem[]
    recent_movements?: RecentMovement[]
}>()

const user = props.auth.user
const isPaid = computed(() => ['paid', 'approved'].includes(props.order?.status ?? ''))
const isApproved = computed(() =>
    props.order?.status === 'approved' && props.shop?.status !== 'disabled'
)
const showOrder = ref(false)

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url }
]

// ─── DSS Insights ─────────────────────────────────────────────────────────────

const dssInsights = computed<DSSInsight[]>(() => {
    const insights: DSSInsight[] = []
    const stats = props.stats
    const movChart = props.movement_chart ?? []
    const catData = props.category_breakdown ?? []
    const branches = props.employees_per_branch ?? []

    if (!stats) return insights

    // --- Stock alerts ---
    if (stats.inventory.out_of_stock > 0) {
        insights.push({
            type: 'danger',
            title: 'Out-of-Stock Items Detected',
            message: `${stats.inventory.out_of_stock} item(s) have zero stock remaining. Initiate emergency reorders immediately — delayed restocking risks lost sales and poor customer experience.`,
        })
    }

    if (stats.inventory.low_stock > 0 && stats.inventory.out_of_stock === 0) {
        insights.push({
            type: 'warning',
            title: 'Low Stock Warning',
            message: `${stats.inventory.low_stock} item(s) are below their minimum stock level. Review your reorder points and contact suppliers before these items run out.`,
        })
    }

    // --- Movement trend ---
    const change = stats.movements.change
    if (change >= 20) {
        insights.push({
            type: 'info',
            title: 'High Movement Spike This Month',
            message: `Inventory movements surged by ${change}% compared to last month. Verify that this is demand-driven and not caused by data entry errors or unauthorized transfers.`,
        })
    } else if (change <= -20) {
        insights.push({
            type: 'warning',
            title: 'Movement Activity Drop',
            message: `Inventory movements dropped by ${Math.abs(change)}% this month. This could indicate slowed operations, understaffing, or potential supply chain delays — worth investigating.`,
        })
    } else if (change > 0) {
        insights.push({
            type: 'success',
            title: 'Steady Inventory Activity',
            message: `Movements are up ${change}% vs last month. Operations appear consistent. Keep monitoring reorder frequency to maintain this balance.`,
        })
    }

    // --- Overstock vs inflow check ---
    if (movChart.length >= 3) {
        const last3 = movChart.slice(-3)
        const inGt = last3.every(m => m.stock_in > m.stock_out)
        if (inGt) {
            insights.push({
                type: 'info',
                title: 'Consistent Inflow Surplus',
                message: `Stock-in has exceeded stock-out for 3+ consecutive months. Review storage capacity and reorder triggers — accumulated inventory may increase holding costs.`,
            })
        }
    }

    // --- Category concentration ---
    if (catData.length > 0) {
        const total = catData.reduce((s, c) => s + c.count, 0)
        const top = [...catData].sort((a, b) => b.count - a.count)[0]
        const topPct = total > 0 ? Math.round((top.count / total) * 100) : 0
        if (topPct >= 40) {
            insights.push({
                type: 'warning',
                title: 'Category Concentration Risk',
                message: `"${top.label}" makes up ${topPct}% of your inventory. Heavy concentration in one category increases risk if demand or supply shifts. Consider diversifying your stock mix.`,
            })
        }
    }

    // --- Branch imbalance ---
    if (branches.length >= 2) {
        const counts = branches.map(b => b.count)
        const maxC = Math.max(...counts)
        const minC = Math.min(...counts)
        if (maxC > 0 && minC / maxC < 0.5) {
            const heavy = branches.find(b => b.count === maxC)
            const light = branches.find(b => b.count === minC)
            insights.push({
                type: 'info',
                title: 'Branch Staffing Imbalance',
                message: `${heavy?.branch} has ${maxC} employees while ${light?.branch} has only ${minC}. Consider redistributing staff or reviewing workload per branch to improve efficiency.`,
            })
        }
    }

    // --- All good ---
    if (stats.inventory.out_of_stock === 0 && stats.inventory.low_stock === 0) {
        insights.push({
            type: 'success',
            title: 'Inventory Fully Stocked',
            message: `All items are at or above their minimum stock levels. Great job maintaining healthy inventory — keep your reorder schedule consistent to sustain this.`,
        })
    }

    return insights
})

// ─── Insight config ───────────────────────────────────────────────────────────

const insightConfig = {
    success: {
        border: 'border-l-emerald-500',
        bg: 'bg-emerald-50 dark:bg-emerald-950/30',
        icon: CheckCircle2,
        iconCls: 'text-emerald-600',
        title: 'text-emerald-700 dark:text-emerald-400',
    },
    warning: {
        border: 'border-l-amber-500',
        bg: 'bg-amber-50 dark:bg-amber-950/30',
        icon: AlertTriangle,
        iconCls: 'text-amber-600',
        title: 'text-amber-700 dark:text-amber-400',
    },
    danger: {
        border: 'border-l-red-500',
        bg: 'bg-red-50 dark:bg-red-950/30',
        icon: ShieldAlert,
        iconCls: 'text-red-600',
        title: 'text-red-700 dark:text-red-400',
    },
    info: {
        border: 'border-l-blue-500',
        bg: 'bg-blue-50 dark:bg-blue-950/30',
        icon: Info,
        iconCls: 'text-blue-600',
        title: 'text-blue-700 dark:text-blue-400',
    },
}

// ─── Charts ───────────────────────────────────────────────────────────────────

const movementChartRef = ref<HTMLCanvasElement | null>(null)
const categoryChartRef = ref<HTMLCanvasElement | null>(null)
const branchChartRef = ref<HTMLCanvasElement | null>(null)

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

    const movData = props.movement_chart ?? []
    const catData = props.category_breakdown ?? []
    const branchData = props.employees_per_branch ?? []

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
                        backgroundColor: 'rgba(16,185,129,0.75)',
                        borderRadius: 4,
                    },
                    {
                        label: 'Stock Out',
                        data: movData.map(d => d.stock_out),
                        backgroundColor: 'rgba(239,68,68,0.75)',
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { font: { size: 11 }, boxWidth: 12, boxHeight: 12 } },
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 11 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                },
            },
        })
    }

    /* ── Category Doughnut ── */
    if (categoryChartRef.value && catData.length) {
        const colors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6', '#ec4899', '#14b8a6']
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
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 }, boxWidth: 12, boxHeight: 12 } },
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
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                },
            },
        })
    }
})

// ─── Helpers ──────────────────────────────────────────────────────────────────

const stockStatusConfig: Record<string, { label: string; cls: string }> = {
    low: { label: 'Low Stock', cls: 'bg-amber-100 text-amber-700' },
    out_of_stock: { label: 'Out of Stock', cls: 'bg-red-100 text-red-700' },
    normal: { label: 'Normal', cls: 'bg-green-100 text-green-700' },
    overstock: { label: 'Overstock', cls: 'bg-blue-100 text-blue-700' },
}

const movementTypeConfig: Record<string, { label: string; cls: string }> = {
    in: { label: 'Stock In', cls: 'bg-emerald-100 text-emerald-700' },
    out: { label: 'Stock Out', cls: 'bg-red-100 text-red-700' },
    adjust: { label: 'Adjustment', cls: 'bg-blue-100 text-blue-700' },
    transfer: { label: 'Transfer', cls: 'bg-violet-100 text-violet-700' },
}
</script>

<template>

    <Head title="Shop Dashboard" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Dashboard">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <!-- ══════════════ DISABLED ══════════════ -->
            <template v-if="props.shop?.status === 'disabled'">
                <div class="flex flex-1 items-center justify-center min-h-[60vh]">
                    <div class="text-center max-w-md">
                        <div class="h-16 w-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                            <ShieldAlert class="h-8 w-8 text-red-500" />
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                            Shop Disabled
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            Your shop has been disabled. Please contact support for assistance.
                        </p>
                    </div>
                </div>
            </template>

            <!-- ══════════════ APPROVED: Live Dashboard ══════════════ -->
            <!-- ══════════════ APPROVED: Live Dashboard ══════════════ -->
            <template v-else-if="isApproved">

                <!-- Welcome Banner -->
                <div
                    class="rounded-xl border border-border bg-gradient-to-r from-indigo-50 to-emerald-50 dark:from-neutral-800 dark:to-neutral-800 p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                            Welcome back, {{ user.name }} 👋
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            Live overview of
                            <span class="font-semibold text-gray-800 dark:text-white">{{ props.order?.shop_name
                                }}</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-muted-foreground shrink-0">
                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 font-medium capitalize">
                            {{ props.order?.subscription_plan ?? 'Active' }}
                        </span>
                        <span v-if="props.order?.expires_at">
                            Expires {{ new Date(props.order.expires_at).toLocaleDateString('en-PH', {
                                month: 'short',
                                day: 'numeric', year: 'numeric' }) }}
                        </span>
                    </div>
                </div>

                <!-- ── 1. KPI CARDS ── -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

                    <Card>
                        <CardContent class="pt-5">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Employees
                                </p>
                                <div class="h-8 w-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                                    <Users class="h-4 w-4 text-indigo-600" />
                                </div>
                            </div>
                            <p class="text-3xl font-bold">{{ props.stats?.employees.total ?? 0 }}</p>
                            <div class="flex items-center gap-2 mt-1.5">
                                <span class="text-xs text-emerald-600 font-medium">{{ props.stats?.employees.active ?? 0
                                    }} active</span>
                                <span class="text-xs text-muted-foreground">·</span>
                                <span class="text-xs text-muted-foreground">{{ props.stats?.employees.inactive ?? 0 }}
                                    inactive</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent class="pt-5">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Branches
                                </p>
                                <div class="h-8 w-8 rounded-lg bg-violet-100 flex items-center justify-center">
                                    <GitBranch class="h-4 w-4 text-violet-600" />
                                </div>
                            </div>
                            <p class="text-3xl font-bold">{{ props.stats?.branches.total ?? 0 }}</p>
                            <p class="text-xs text-emerald-600 font-medium mt-1.5">{{ props.stats?.branches.active ?? 0
                                }} active</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent class="pt-5">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Inventory
                                </p>
                                <div class="h-8 w-8 rounded-lg bg-amber-100 flex items-center justify-center">
                                    <Package class="h-4 w-4 text-amber-600" />
                                </div>
                            </div>
                            <p class="text-3xl font-bold">{{ props.stats?.inventory.total ?? 0 }}</p>
                            <div class="flex items-center gap-2 mt-1.5">
                                <span class="text-xs text-amber-600 font-medium">{{ props.stats?.inventory.low_stock ??
                                    0 }} low</span>
                                <span class="text-xs text-muted-foreground">·</span>
                                <span class="text-xs text-red-500 font-medium">{{ props.stats?.inventory.out_of_stock ??
                                    0 }} out</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent class="pt-5">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Alerts</p>
                                <div class="h-8 w-8 rounded-lg bg-red-100 flex items-center justify-center">
                                    <AlertTriangle class="h-4 w-4 text-red-600" />
                                </div>
                            </div>
                            <p class="text-3xl font-bold"
                                :class="(props.stats?.low_stock_alerts ?? 0) > 0 ? 'text-red-600' : ''">
                                {{ props.stats?.low_stock_alerts ?? 0 }}
                            </p>
                            <div class="flex items-center gap-1 mt-1.5">
                                <ArrowUpRight v-if="(props.stats?.movements.change ?? 0) >= 0"
                                    class="h-3.5 w-3.5 text-emerald-500" />
                                <ArrowDownRight v-else class="h-3.5 w-3.5 text-red-500" />
                                <span class="text-xs font-medium"
                                    :class="(props.stats?.movements.change ?? 0) >= 0 ? 'text-emerald-500' : 'text-red-500'">
                                    {{ Math.abs(props.stats?.movements.change ?? 0) }}%
                                </span>
                                <span class="text-xs text-muted-foreground">movements vs last month</span>
                            </div>
                        </CardContent>
                    </Card>

                </div>

                <!-- ── 2. CHARTS ── -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <!-- Movement Bar — spans 2 cols -->
                    <Card class="md:col-span-2">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-semibold flex items-center gap-2">
                                <Activity class="h-4 w-4 text-muted-foreground" />
                                Inventory movements (12 months)
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="(props.movement_chart?.length ?? 0) === 0"
                                class="flex items-center justify-center h-44 text-sm text-muted-foreground">
                                No movement data yet.
                            </div>
                            <div v-else class="relative h-[220px]">
                                <canvas ref="movementChartRef"></canvas>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Category Doughnut -->
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-semibold flex items-center gap-2">
                                <BoxSelect class="h-4 w-4 text-muted-foreground" />
                                Items by category
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="flex items-center justify-center">
                            <div v-if="(props.category_breakdown?.length ?? 0) === 0"
                                class="flex items-center justify-center h-44 text-sm text-muted-foreground">
                                No categories yet.
                            </div>
                            <div v-else class="relative w-full h-[220px]">
                                <canvas ref="categoryChartRef"></canvas>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Employees per Branch — full width row -->
                    <Card class="md:col-span-3">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-semibold flex items-center gap-2">
                                <Users class="h-4 w-4 text-muted-foreground" />
                                Employees per branch
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="(props.employees_per_branch?.length ?? 0) === 0"
                                class="flex items-center justify-center h-28 text-sm text-muted-foreground">
                                No branches yet.
                            </div>
                            <div v-else class="relative h-[160px]">
                                <canvas ref="branchChartRef"></canvas>
                            </div>
                        </CardContent>
                    </Card>

                </div>

                <!-- ── 3. DECISION SUPPORT CARDS ── -->
                <div v-if="dssInsights.length > 0">
                    <div class="flex items-center gap-2 mb-3">
                        <div
                            class="h-7 w-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                            <Lightbulb class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <h3 class="text-sm font-semibold">Decision support</h3>
                        <span class="text-xs text-muted-foreground">— recommendations based on your current data</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                        <div v-for="(insight, i) in dssInsights" :key="i" class="rounded-xl border-l-4 p-4 flex gap-3"
                            :class="[insightConfig[insight.type].bg, insightConfig[insight.type].border]">
                            <div class="mt-0.5 shrink-0">
                                <component :is="insightConfig[insight.type].icon" class="h-4 w-4"
                                    :class="insightConfig[insight.type].iconCls" />
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 mb-1.5">
                                    <!-- severity badge -->
                                    <span
                                        class="text-[10px] font-semibold px-2 py-0.5 rounded-full uppercase tracking-wide"
                                        :class="{
                                            'bg-emerald-100 text-emerald-700': insight.type === 'success',
                                            'bg-amber-100 text-amber-700': insight.type === 'warning',
                                            'bg-red-100 text-red-700': insight.type === 'danger',
                                            'bg-blue-100 text-blue-700': insight.type === 'info',
                                        }">
                                        {{ insight.type === 'danger' ? 'Critical' : insight.type === 'warning' ?
                                            'Warning' : insight.type === 'success' ? 'Healthy' : 'Info' }}
                                    </span>
                                </div>
                                <p class="text-xs font-semibold mb-1" :class="insightConfig[insight.type].title">
                                    {{ insight.title }}
                                </p>
                                <p class="text-xs text-muted-foreground leading-relaxed">
                                    {{ insight.message }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </template>

            <!-- ══════════════ PAID BUT NOT APPROVED: Waiting ══════════════ -->
            <template v-else-if="isPaid && !isApproved">
                <div class="flex flex-1 items-center justify-center min-h-[60vh]">
                    <div class="text-center max-w-md">
                        <div class="h-16 w-16 rounded-full bg-yellow-100 flex items-center justify-center mx-auto mb-4">
                            <Clock class="h-8 w-8 text-yellow-500" />
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                            Awaiting Admin Approval
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            Your payment has been received. Please wait while our admin reviews and approves your
                            account.
                            You'll have full access once approved.
                        </p>
                    </div>
                </div>
            </template>

            <!-- NOT PAID: Order Form -->
            <template v-else>
                <div v-if="!showOrder" class="rounded-xl bg-gray-100 dark:bg-neutral-800 p-6">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">
                        Welcome {{ user.name }} 👋
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2 max-w-2xl">
                        Complete your shop details and select the plans you need.
                    </p>
                    <Button class="mt-4" @click="showOrder = true">Get Started</Button>
                </div>
                <CheckoutConfirm v-if="showOrder" plan-name="Standard" :vat-pct="12" :billing-months="1"
                    :user="{ name: user.name, email: user.email }" :shop="props.shop ?? undefined" />
            </template>

        </div>
    </ShopLayout>
</template>
