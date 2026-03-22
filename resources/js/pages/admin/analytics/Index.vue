<script setup lang="ts">
import { computed, ref } from 'vue'
import AdminLayout from '@/layouts/admin/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import {
    Store, Users, ShoppingBag, TrendingUp,
    ArrowUpRight, ArrowDownRight, BarChart2, Calendar
} from 'lucide-vue-next'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard',  href: '/admin/dashboard' },
    { title: 'Analytics Report', href: '/admin/analytics' },
]

/* ---------------- PROPS ---------------- */
interface RevenuePoint { month: string; amount: number }
interface OrderPoint { month: string; count: number }
interface RegistrationPoint { month: string; count: number }
interface TopShop {
    rank: number
    name: string
    location: string
    revenue: number
    orders: number
    growth: number
}

const props = defineProps<{
    stats: {
        total_shops: { value: number; change: number }
        total_customers: { value: number; change: number }
        total_revenue: { value: number; change: number }
        total_orders: { value: number; change: number }
    }
    revenue_chart: RevenuePoint[]
    orders_chart: OrderPoint[]
    registrations_chart: RegistrationPoint[]
    top_shops: TopShop[]
}>()

/* ---------------- HELPERS ---------------- */
const fmt = (n: number) =>
    n >= 1_000_000 ? `₱${(n / 1_000_000).toFixed(2)}M`
        : n >= 1_000 ? `₱${(n / 1_000).toFixed(1)}K`
            : `₱${n}`

const fmtNum = (n: number) =>
    n >= 1_000 ? `${(n / 1_000).toFixed(1)}K` : String(n)

/* ---------------- LINE CHART (Revenue) ---------------- */
const LINE_W = 600
const LINE_H = 180
const LP = { top: 16, right: 16, bottom: 32, left: 52 }

const revMax = computed(() =>
    props.revenue_chart.length
        ? Math.max(...props.revenue_chart.map(d => d.amount)) * 1.15
        : 1
)

const revPoints = computed(() => {
    const data = props.revenue_chart
    const n = data.length
    if (n < 2) return []
    return data.map((d, i) => ({
        x: LP.left + (i / (n - 1)) * (LINE_W - LP.left - LP.right),
        y: LINE_H - LP.bottom - (d.amount / revMax.value) * (LINE_H - LP.top - LP.bottom),
        ...d,
    }))
})

const revPath = computed(() =>
    revPoints.value.map((p, i) => `${i === 0 ? 'M' : 'L'}${p.x.toFixed(1)},${p.y.toFixed(1)}`).join(' ')
)

const revArea = computed(() => {
    const pts = revPoints.value
    if (!pts.length) return ''
    const base = LINE_H - LP.bottom
    return `${revPath.value} L${pts[pts.length - 1].x.toFixed(1)},${base} L${pts[0].x.toFixed(1)},${base} Z`
})

const revYTicks = computed(() =>
    [0, 0.25, 0.5, 0.75, 1].map(t => ({
        val: revMax.value * t,
        y: LINE_H - LP.bottom - t * (LINE_H - LP.top - LP.bottom),
    }))
)

/* ---------------- BAR CHART (Orders) ---------------- */
const BAR_W = 600
const BAR_H = 180
const BP = { top: 16, right: 16, bottom: 32, left: 48 }

const ordMax = computed(() =>
    props.orders_chart.length
        ? Math.max(...props.orders_chart.map(d => d.count)) * 1.15
        : 1
)

const bars = computed(() => {
    const data = props.orders_chart
    const n = data.length
    const innerW = BAR_W - BP.left - BP.right
    const slotW = innerW / (n || 1)
    const barW = slotW * 0.55
    return data.map((d, i) => {
        const barH = (d.count / ordMax.value) * (BAR_H - BP.top - BP.bottom)
        return {
            x: BP.left + i * slotW + (slotW - barW) / 2,
            y: BAR_H - BP.bottom - barH,
            w: barW,
            h: barH,
            ...d,
        }
    })
})

/* ---------------- AREA CHART (Registrations) ---------------- */
const REG_W = 600
const REG_H = 160
const RP = { top: 16, right: 16, bottom: 32, left: 48 }

const regMax = computed(() =>
    props.registrations_chart.length
        ? Math.max(...props.registrations_chart.map(d => d.count)) * 1.15
        : 1
)

const regPoints = computed(() => {
    const data = props.registrations_chart
    const n = data.length
    if (n < 2) return []
    return data.map((d, i) => ({
        x: RP.left + (i / (n - 1)) * (REG_W - RP.left - RP.right),
        y: REG_H - RP.bottom - (d.count / regMax.value) * (REG_H - RP.top - RP.bottom),
        ...d,
    }))
})

const regPath = computed(() =>
    regPoints.value.map((p, i) => `${i === 0 ? 'M' : 'L'}${p.x.toFixed(1)},${p.y.toFixed(1)}`).join(' ')
)

const regArea = computed(() => {
    const pts = regPoints.value
    if (!pts.length) return ''
    const base = REG_H - RP.bottom
    return `${regPath.value} L${pts[pts.length - 1].x.toFixed(1)},${base} L${pts[0].x.toFixed(1)},${base} Z`
})

/* ---------------- TOOLTIP ---------------- */
const tooltip = ref<{ x: number; y: number; label: string; value: string } | null>(null)

function showTooltip(e: MouseEvent, label: string, value: string) {
    const svgEl = (e.currentTarget as SVGElement).closest('svg')!
    const pt = svgEl.createSVGPoint()
    pt.x = e.clientX
    pt.y = e.clientY
    const svgPt = pt.matrixTransform(svgEl.getScreenCTM()!.inverse())
    tooltip.value = { x: svgPt.x, y: svgPt.y - 14, label, value }
}

function hideTooltip() { tooltip.value = null }
</script>

<template>

    <Head title="Analytics" />

    <AdminLayout :breadcrumbs="breadcrumbs" title="Analytics">
        <div class="min-h-screen bg-background text-foreground px-4 sm:px-6 lg:px-8 py-8 max-w-[1400px] mx-auto">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Analytics Overview</h1>
                    <p class="text-sm text-muted-foreground mt-0.5 flex items-center gap-1.5">
                        <Calendar class="h-3.5 w-3.5" />
                        Platform-wide performance summary
                    </p>
                </div>
            </div>

            <!-- ── STAT CARDS ── -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

                <div class="rounded-xl border border-border bg-card p-5 flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Total
                            Shops</span>
                        <div class="h-8 w-8 rounded-lg bg-blue-500/10 flex items-center justify-center">
                            <Store class="h-4 w-4 text-blue-500" />
                        </div>
                    </div>
                    <div>
                        <p class="text-3xl font-bold tracking-tight">{{ fmtNum(stats.total_shops.value) }}</p>
                        <p class="text-xs mt-1 flex items-center gap-0.5"
                            :class="stats.total_shops.change >= 0 ? 'text-emerald-500' : 'text-red-500'">
                            <ArrowUpRight v-if="stats.total_shops.change >= 0" class="h-3.5 w-3.5" />
                            <ArrowDownRight v-else class="h-3.5 w-3.5" />
                            {{ Math.abs(stats.total_shops.change) }}% vs last month
                        </p>
                    </div>
                </div>

                <div class="rounded-xl border border-border bg-card p-5 flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <span
                            class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Customers</span>
                        <div class="h-8 w-8 rounded-lg bg-violet-500/10 flex items-center justify-center">
                            <Users class="h-4 w-4 text-violet-500" />
                        </div>
                    </div>
                    <div>
                        <p class="text-3xl font-bold tracking-tight">{{ fmtNum(stats.total_customers.value) }}</p>
                        <p class="text-xs mt-1 flex items-center gap-0.5"
                            :class="stats.total_customers.change >= 0 ? 'text-emerald-500' : 'text-red-500'">
                            <ArrowUpRight v-if="stats.total_customers.change >= 0" class="h-3.5 w-3.5" />
                            <ArrowDownRight v-else class="h-3.5 w-3.5" />
                            {{ Math.abs(stats.total_customers.change) }}% vs last month
                        </p>
                    </div>
                </div>

                <div class="rounded-xl border border-border bg-card p-5 flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Revenue</span>
                        <div class="h-8 w-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                            <TrendingUp class="h-4 w-4 text-emerald-500" />
                        </div>
                    </div>
                    <div>
                        <p class="text-3xl font-bold tracking-tight">{{ fmt(stats.total_revenue.value) }}</p>
                        <p class="text-xs mt-1 flex items-center gap-0.5"
                            :class="stats.total_revenue.change >= 0 ? 'text-emerald-500' : 'text-red-500'">
                            <ArrowUpRight v-if="stats.total_revenue.change >= 0" class="h-3.5 w-3.5" />
                            <ArrowDownRight v-else class="h-3.5 w-3.5" />
                            {{ Math.abs(stats.total_revenue.change) }}% vs last month
                        </p>
                    </div>
                </div>

                <div class="rounded-xl border border-border bg-card p-5 flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Orders</span>
                        <div class="h-8 w-8 rounded-lg bg-amber-500/10 flex items-center justify-center">
                            <ShoppingBag class="h-4 w-4 text-amber-500" />
                        </div>
                    </div>
                    <div>
                        <p class="text-3xl font-bold tracking-tight">{{ fmtNum(stats.total_orders.value) }}</p>
                        <p class="text-xs mt-1 flex items-center gap-0.5"
                            :class="stats.total_orders.change >= 0 ? 'text-emerald-500' : 'text-red-500'">
                            <ArrowUpRight v-if="stats.total_orders.change >= 0" class="h-3.5 w-3.5" />
                            <ArrowDownRight v-else class="h-3.5 w-3.5" />
                            {{ Math.abs(stats.total_orders.change) }}% vs last month
                        </p>
                    </div>
                </div>

            </div>

            <!-- ── CHARTS ROW 1 ── -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

                <!-- Revenue Line Chart -->
                <div class="rounded-xl border border-border bg-card p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-sm font-semibold">Revenue Over Time</h2>
                            <p class="text-xs text-muted-foreground">Monthly platform revenue (paid)</p>
                        </div>
                        <TrendingUp class="h-4 w-4 text-emerald-500" />
                    </div>
                    <div class="w-full overflow-x-auto">
                        <svg :viewBox="`0 0 ${LINE_W} ${LINE_H}`" class="w-full" style="min-width:280px">
                            <defs>
                                <linearGradient id="revGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#10b981" stop-opacity="0.25" />
                                    <stop offset="100%" stop-color="#10b981" stop-opacity="0" />
                                </linearGradient>
                            </defs>
                            <g v-for="tick in revYTicks" :key="tick.y">
                                <line :x1="LP.left" :y1="tick.y" :x2="LINE_W - LP.right" :y2="tick.y"
                                    stroke="currentColor" stroke-opacity="0.07" stroke-width="1" />
                                <text :x="LP.left - 4" :y="tick.y + 4" text-anchor="end" font-size="9"
                                    class="fill-muted-foreground">
                                    {{ tick.val >= 1000 ? `${(tick.val / 1000).toFixed(0)}K` : tick.val.toFixed(0) }}
                                </text>
                            </g>
                            <path :d="revArea" fill="url(#revGrad)" />
                            <path :d="revPath" fill="none" stroke="#10b981" stroke-width="2" stroke-linejoin="round"
                                stroke-linecap="round" />
                            <g v-for="pt in revPoints" :key="pt.month">
                                <circle :cx="pt.x" :cy="pt.y" r="3.5" fill="#10b981" stroke="white" stroke-width="1.5"
                                    class="cursor-pointer" @mouseenter="showTooltip($event, pt.month, fmt(pt.amount))"
                                    @mouseleave="hideTooltip" />
                                <text :x="pt.x" :y="LINE_H - LP.bottom + 14" text-anchor="middle" font-size="9"
                                    class="fill-muted-foreground">
                                    {{ pt.month }}
                                </text>
                            </g>
                            <g v-if="tooltip">
                                <rect :x="tooltip.x - 28" :y="tooltip.y - 22" width="56" height="22" rx="4"
                                    fill="hsl(var(--card))" stroke="hsl(var(--border))" stroke-width="1" />
                                <text :x="tooltip.x" :y="tooltip.y - 7" text-anchor="middle" font-size="9"
                                    font-weight="600" class="fill-foreground">
                                    {{ tooltip.value }}
                                </text>
                            </g>
                        </svg>
                    </div>
                </div>

                <!-- Orders Bar Chart -->
                <div class="rounded-xl border border-border bg-card p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-sm font-semibold">Orders Over Time</h2>
                            <p class="text-xs text-muted-foreground">Monthly paid transaction volume</p>
                        </div>
                        <BarChart2 class="h-4 w-4 text-amber-500" />
                    </div>
                    <div class="w-full overflow-x-auto">
                        <svg :viewBox="`0 0 ${BAR_W} ${BAR_H}`" class="w-full" style="min-width:280px">
                            <defs>
                                <linearGradient id="barGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#f59e0b" stop-opacity="0.9" />
                                    <stop offset="100%" stop-color="#f59e0b" stop-opacity="0.4" />
                                </linearGradient>
                            </defs>
                            <line v-for="t in [0.25, 0.5, 0.75, 1]" :key="t" :x1="BP.left"
                                :y1="BAR_H - BP.bottom - t * (BAR_H - BP.top - BP.bottom)" :x2="BAR_W - BP.right"
                                :y2="BAR_H - BP.bottom - t * (BAR_H - BP.top - BP.bottom)" stroke="currentColor"
                                stroke-opacity="0.07" stroke-width="1" />
                            <g v-for="b in bars" :key="b.month">
                                <rect :x="b.x" :y="b.y" :width="b.w" :height="b.h" fill="url(#barGrad)" rx="3"
                                    class="cursor-pointer hover:opacity-80 transition-opacity"
                                    @mouseenter="showTooltip($event, b.month, fmtNum(b.count) + ' orders')"
                                    @mouseleave="hideTooltip" />
                                <text :x="b.x + b.w / 2" :y="BAR_H - BP.bottom + 14" text-anchor="middle" font-size="9"
                                    class="fill-muted-foreground">
                                    {{ b.month }}
                                </text>
                            </g>
                            <text v-for="t in [0, 0.5, 1]" :key="t" :x="BP.left - 4"
                                :y="BAR_H - BP.bottom - t * (BAR_H - BP.top - BP.bottom) + 4" text-anchor="end"
                                font-size="9" class="fill-muted-foreground">
                                {{ ((ordMax * t) / 1000).toFixed(1) }}K
                            </text>
                            <g v-if="tooltip">
                                <rect :x="tooltip.x - 36" :y="tooltip.y - 22" width="72" height="22" rx="4"
                                    fill="hsl(var(--card))" stroke="hsl(var(--border))" stroke-width="1" />
                                <text :x="tooltip.x" :y="tooltip.y - 7" text-anchor="middle" font-size="9"
                                    font-weight="600" class="fill-foreground">
                                    {{ tooltip.value }}
                                </text>
                            </g>
                        </svg>
                    </div>
                </div>

            </div>

            <!-- ── CHARTS ROW 2 ── -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

                <!-- Registrations Area Chart -->
                <div class="rounded-xl border border-border bg-card p-5 lg:col-span-2">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-sm font-semibold">New Registrations</h2>
                            <p class="text-xs text-muted-foreground">New customers per month</p>
                        </div>
                        <Users class="h-4 w-4 text-violet-500" />
                    </div>
                    <div class="w-full overflow-x-auto">
                        <svg :viewBox="`0 0 ${REG_W} ${REG_H}`" class="w-full" style="min-width:240px">
                            <defs>
                                <linearGradient id="regGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#8b5cf6" stop-opacity="0.3" />
                                    <stop offset="100%" stop-color="#8b5cf6" stop-opacity="0" />
                                </linearGradient>
                            </defs>
                            <line v-for="t in [0.33, 0.66, 1]" :key="t" :x1="RP.left"
                                :y1="REG_H - RP.bottom - t * (REG_H - RP.top - RP.bottom)" :x2="REG_W - RP.right"
                                :y2="REG_H - RP.bottom - t * (REG_H - RP.top - RP.bottom)" stroke="currentColor"
                                stroke-opacity="0.07" stroke-width="1" />
                            <path :d="regArea" fill="url(#regGrad)" />
                            <path :d="regPath" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linejoin="round"
                                stroke-linecap="round" />
                            <g v-for="pt in regPoints" :key="pt.month">
                                <circle :cx="pt.x" :cy="pt.y" r="3" fill="#8b5cf6" stroke="white" stroke-width="1.5"
                                    class="cursor-pointer"
                                    @mouseenter="showTooltip($event, pt.month, pt.count + ' new')"
                                    @mouseleave="hideTooltip" />
                                <text :x="pt.x" :y="REG_H - RP.bottom + 14" text-anchor="middle" font-size="9"
                                    class="fill-muted-foreground">
                                    {{ pt.month }}
                                </text>
                            </g>
                            <g v-if="tooltip">
                                <rect :x="tooltip.x - 26" :y="tooltip.y - 22" width="52" height="22" rx="4"
                                    fill="hsl(var(--card))" stroke="hsl(var(--border))" stroke-width="1" />
                                <text :x="tooltip.x" :y="tooltip.y - 7" text-anchor="middle" font-size="9"
                                    font-weight="600" class="fill-foreground">
                                    {{ tooltip.value }}
                                </text>
                            </g>
                        </svg>
                    </div>
                </div>

                <!-- Top Performing Shops -->
                <div class="rounded-xl border border-border bg-card p-5 lg:col-span-3">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-sm font-semibold">Top Performing Shops</h2>
                            <p class="text-xs text-muted-foreground">Ranked by total paid revenue</p>
                        </div>
                        <Store class="h-4 w-4 text-blue-500" />
                    </div>

                    <div class="space-y-1">
                        <div
                            class="grid grid-cols-12 text-xs font-medium text-muted-foreground pb-2 border-b border-border px-1">
                            <span class="col-span-1">#</span>
                            <span class="col-span-5">Shop</span>
                            <span class="col-span-3 text-right">Revenue</span>
                            <span class="col-span-2 text-right">Orders</span>
                            <span class="col-span-1 text-right">△</span>
                        </div>

                        <div v-for="shop in top_shops" :key="shop.rank"
                            class="grid grid-cols-12 items-center py-2.5 px-1 rounded-lg hover:bg-muted/50 transition-colors">
                            <span class="col-span-1">
                                <span :class="[
                                    'inline-flex h-5 w-5 items-center justify-center rounded text-xs font-bold',
                                    shop.rank === 1 ? 'bg-amber-400/20 text-amber-500' :
                                        shop.rank === 2 ? 'bg-slate-400/20 text-slate-500' :
                                            shop.rank === 3 ? 'bg-orange-400/20 text-orange-500' :
                                                'bg-muted text-muted-foreground'
                                ]">{{ shop.rank }}</span>
                            </span>
                            <div class="col-span-5 min-w-0">
                                <p class="font-medium text-xs leading-tight truncate">{{ shop.name }}</p>
                                <p class="text-xs text-muted-foreground truncate">{{ shop.location }}</p>
                            </div>
                            <span class="col-span-3 text-right text-xs font-semibold">{{ fmt(shop.revenue) }}</span>
                            <span class="col-span-2 text-right text-xs text-muted-foreground">{{ fmtNum(shop.orders)
                                }}</span>
                            <span class="col-span-1 flex justify-end">
                                <span :class="[
                                    'text-xs font-medium flex items-center gap-0.5',
                                    shop.growth >= 0 ? 'text-emerald-500' : 'text-red-500'
                                ]">
                                    <ArrowUpRight v-if="shop.growth >= 0" class="h-3 w-3" />
                                    <ArrowDownRight v-else class="h-3 w-3" />
                                    {{ Math.abs(shop.growth) }}%
                                </span>
                            </span>
                        </div>

                        <div v-if="!top_shops.length" class="text-center text-xs text-muted-foreground py-6">
                            No shop data available yet.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
