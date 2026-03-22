<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue'
import { dashboard } from '@/routes'
import { type BreadcrumbItem } from '@/types'
import { Head, usePage, router } from '@inertiajs/vue3'

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
    Store, Users, CreditCard, AlertTriangle, CheckCircle2,
    ArrowUp, ArrowDown, TrendingUp, ShieldAlert, Bell,
    Activity, Zap, BarChart3, PieChart,
    UserX, BadgeDollarSign, RefreshCcw, Clock,
    Package, Wifi, WifiOff,
} from 'lucide-vue-next'
import VueApexCharts from 'vue3-apexcharts'
import { ref, computed, onMounted, onUnmounted } from 'vue'

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
]

const page = usePage()
const user = page.props.auth.user

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    kpis: {
        totalShops: number
        activeShops: number
        disabledShops: number
        pendingShops: number
        shopChange: number
        totalOwners: number
        totalStaff: number
        totalCustomers: number
        newUsersMonth: number
        usersChange: number
        totalOrders: number
        activeSubscriptions: number
        expiredOrders: number
        ordersThisMonth: number
        ordersChange: number
        revenueThisMonth: number
        revenueLastMonth: number
        revenueChange: number
        totalRevenue: number
        totalEmployees: number
        activeEmployees: number
        totalInventory: number
        lowStockCount: number
        outOfStockCount: number
    }
    alerts: {
        overduePayments: string[]
        expiringShops: string[]
        inactiveShops: string[]
        pendingShops: number
        lowStockCount: number
        outOfStockCount: number
    }
    charts: {
        revenue: number[]
        orders: number[]
        shops: number[]
    }
    planBreakdown: Record<string, number>
    shops: {
        name: string
        owner: string
        plan: string
        status: string
        expiry: string
        revenue: string
        is_expiring: boolean
    }[]
}>()

// ─── Live state ───────────────────────────────────────────────────────────────

const liveKpis  = ref({ ...props.kpis })
const isConnected = ref(false)
const lastUpdated = ref<Date | null>(null)
const pulse       = ref(false)

function triggerPulse() {
    pulse.value = true
    setTimeout(() => { pulse.value = false }, 700)
}

// ─── Echo real-time ───────────────────────────────────────────────────────────

let channel: any = null

onMounted(() => {
    // @ts-ignore
    if (!window.Echo) return
    // @ts-ignore
    channel = window.Echo.channel('admin.dashboard')
        .listen('.dashboard.updated', (data: any) => {
            if (data.kpis) liveKpis.value = { ...liveKpis.value, ...data.kpis }
            if (data.donutSeries) donutSeries.value = data.donutSeries
            lastUpdated.value = new Date()
            triggerPulse()
        })
        .subscribed(() => { isConnected.value = true  })
        .error(()       => { isConnected.value = false })
})

onUnmounted(() => {
    // @ts-ignore
    window.Echo?.leaveChannel('admin.dashboard')
})

function manualRefresh() {
    router.reload({ only: ['kpis', 'alerts', 'charts', 'planBreakdown', 'shops'] })
}

// ─── KPI cards ────────────────────────────────────────────────────────────────

const kpiCards = computed(() => [
    {
        title: 'Registered Shops',
        value: liveKpis.value.totalShops.toLocaleString(),
        change: liveKpis.value.shopChange,
        icon: Store,
        color: 'blue',
        sub: `${liveKpis.value.activeShops} active · ${liveKpis.value.pendingShops} pending`,
        invertColor: false,
    },
    {
        title: 'Active Subscriptions',
        value: liveKpis.value.activeSubscriptions.toLocaleString(),
        change: liveKpis.value.ordersChange,
        icon: CreditCard,
        color: 'emerald',
        sub: `${liveKpis.value.expiredOrders} expired`,
        invertColor: false,
    },
    {
        title: 'Platform Revenue MTD',
        value: `₱${Number(liveKpis.value.revenueThisMonth).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`,
        change: liveKpis.value.revenueChange,
        icon: BadgeDollarSign,
        color: 'green',
        sub: `Total all-time: ₱${Number(liveKpis.value.totalRevenue).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`,
        invertColor: false,
    },
    {
        title: 'Registered Owners',
        value: liveKpis.value.totalOwners.toLocaleString(),
        change: liveKpis.value.usersChange,
        icon: Users,
        color: 'purple',
        sub: `${liveKpis.value.totalCustomers} customers · ${liveKpis.value.totalStaff} staff`,
        invertColor: false,
    },
])

const colorMap: Record<string, { icon: string; bg: string }> = {
    blue:   { icon: 'text-blue-600',    bg: 'bg-blue-100 dark:bg-blue-900/40'       },
    emerald:{ icon: 'text-emerald-600', bg: 'bg-emerald-100 dark:bg-emerald-900/40' },
    green:  { icon: 'text-green-600',   bg: 'bg-green-100 dark:bg-green-900/40'     },
    purple: { icon: 'text-purple-600',  bg: 'bg-purple-100 dark:bg-purple-900/40'   },
    red:    { icon: 'text-red-600',     bg: 'bg-red-100 dark:bg-red-900/40'         },
    sky:    { icon: 'text-sky-600',     bg: 'bg-sky-100 dark:bg-sky-900/40'         },
    orange: { icon: 'text-orange-600',  bg: 'bg-orange-100 dark:bg-orange-900/40'   },
}

// ─── Alerts ───────────────────────────────────────────────────────────────────

const systemAlerts = computed(() => {
    const list: { level: string; icon: any; message: string; action: string | null }[] = []

    if (props.alerts.overduePayments.length > 0)
        list.push({
            level: 'critical', icon: ShieldAlert,
            message: `${props.alerts.overduePayments.length} shop(s) have overdue subscription payments (7+ days): ${props.alerts.overduePayments.slice(0, 2).join(', ')}${props.alerts.overduePayments.length > 2 ? ` +${props.alerts.overduePayments.length - 2} more` : ''}.`,
            action: 'Manage Subscriptions',
        })

    if (props.alerts.outOfStockCount > 0)
        list.push({
            level: 'critical', icon: Package,
            message: `${props.alerts.outOfStockCount} inventory item(s) are completely out of stock across all shops.`,
            action: 'View Inventory',
        })

    if (props.alerts.expiringShops.length > 0)
        list.push({
            level: 'warning', icon: Clock,
            message: `${props.alerts.expiringShops.length} subscription(s) expire within 7 days: ${props.alerts.expiringShops.slice(0, 2).join(', ')}.`,
            action: 'View Expiring',
        })

    if (props.alerts.lowStockCount > 0)
        list.push({
            level: 'warning', icon: AlertTriangle,
            message: `${props.alerts.lowStockCount} inventory item(s) are running low on stock across all shops.`,
            action: 'View Low Stock',
        })

    if (props.alerts.inactiveShops.length > 0)
        list.push({
            level: 'warning', icon: UserX,
            message: `${props.alerts.inactiveShops.length} shop(s) have had no activity for 21+ days — possible churn risk.`,
            action: 'View Shops',
        })

    if (props.alerts.pendingShops > 0)
        list.push({
            level: 'info', icon: CheckCircle2,
            message: `${props.alerts.pendingShops} new shop registration(s) are awaiting your approval.`,
            action: 'Approve Shops',
        })

    if (list.length === 0)
        list.push({
            level: 'info', icon: CheckCircle2,
            message: 'All systems normal — no alerts at this time.',
            action: null,
        })

    return list
})

const alertStyle: Record<string, string> = {
    critical: 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20 text-red-800 dark:text-red-300',
    warning:  'border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20 text-amber-800 dark:text-amber-300',
    info:     'border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300',
}

const alertIconStyle: Record<string, string> = {
    critical: 'text-red-500',
    warning:  'text-amber-500',
    info:     'text-blue-500',
}

// ─── Charts ───────────────────────────────────────────────────────────────────

const MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']

const revenueOptions = ref({
    chart:      { type: 'area', toolbar: { show: false }, zoom: { enabled: false }, background: 'transparent', fontFamily: 'inherit' },
    stroke:     { curve: 'smooth', width: 2 },
    fill:       { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.02 } },
    xaxis:      { categories: MONTHS, labels: { style: { colors: '#9ca3af', fontSize: '11px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
    yaxis:      { labels: { style: { colors: '#9ca3af', fontSize: '11px' }, formatter: (v: number) => `₱${(v/1000).toFixed(0)}k` } },
    colors:     ['#10b981', '#3b82f6'],
    legend:     { labels: { colors: '#9ca3af' } },
    tooltip:    { theme: 'dark', y: { formatter: (v: number) => `₱${Number(v).toLocaleString('en-PH', { minimumFractionDigits: 2 })}` } },
    grid:       { borderColor: '#e5e7eb', strokeDashArray: 4, xaxis: { lines: { show: false } } },
    dataLabels: { enabled: false },
})

const revenueSeries = ref([
    { name: 'Revenue (₱)',    data: props.charts.revenue },
    { name: 'Orders Count',  data: props.charts.orders  },
])

// Plan breakdown donut
const planLabels  = computed(() => Object.keys(props.planBreakdown))
const donutSeries = ref(Object.values(props.planBreakdown).map(Number))

const donutOptions = ref({
    chart:      { type: 'donut', background: 'transparent', fontFamily: 'inherit' },
    labels:     planLabels.value.length ? planLabels.value : ['No Data'],
    colors:     ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444'],
    legend:     { position: 'bottom', labels: { colors: '#9ca3af' }, fontSize: '12px' },
    dataLabels: { style: { fontSize: '11px' } },
    plotOptions: {
        pie: {
            donut: {
                size: '65%',
                labels: {
                    show: true,
                    total: {
                        show: true,
                        label: 'Active Subs',
                        color: '#9ca3af',
                        formatter: () => String(props.kpis.activeSubscriptions),
                    },
                },
            },
        },
    },
    tooltip: { theme: 'dark' },
    stroke:  { show: false },
})

const shopGrowthOptions = ref({
    chart:       { type: 'bar', toolbar: { show: false }, background: 'transparent', fontFamily: 'inherit' },
    plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
    xaxis:       { categories: MONTHS, labels: { style: { colors: '#9ca3af', fontSize: '11px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
    yaxis:       { labels: { style: { colors: '#9ca3af', fontSize: '11px' } } },
    colors:      ['#8b5cf6'],
    dataLabels:  { enabled: false },
    grid:        { borderColor: '#e5e7eb', strokeDashArray: 4, xaxis: { lines: { show: false } } },
    tooltip:     { theme: 'dark' },
})

const shopGrowthSeries = ref([
    { name: 'New Shops', data: props.charts.shops },
])

// ─── Shops table ──────────────────────────────────────────────────────────────

const statusBadge: Record<string, string> = {
    active:   'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    pending:  'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    disabled: 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
    inactive: 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
}

// ─── DSS Recommendations ──────────────────────────────────────────────────────

const recommendations = computed(() => [
    {
        icon: TrendingUp, color: 'text-green-600',
        bg: 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800',
        title: 'Grow Subscription Revenue',
        body: liveKpis.value.revenueChange >= 0
            ? `Revenue is up ${liveKpis.value.revenueChange}% this month. Consider introducing annual billing to lock in recurring income from your ${liveKpis.value.activeSubscriptions} active subscribers.`
            : `Revenue dropped ${Math.abs(liveKpis.value.revenueChange)}% vs last month. Review pricing or run a re-subscription campaign for the ${liveKpis.value.expiredOrders} expired accounts.`,
    },
    {
        icon: Zap, color: 'text-amber-600',
        bg: 'bg-amber-50 border-amber-200 dark:bg-amber-900/20 dark:border-amber-800',
        title: 'Re-engage Inactive Shops',
        body: props.alerts.inactiveShops.length > 0
            ? `${props.alerts.inactiveShops.length} shop(s) have been idle for 21+ days. Send a re-engagement email with a renewal discount before they churn permanently.`
            : 'No inactive shops detected — great platform engagement!',
    },
    {
        icon: BarChart3, color: 'text-blue-600',
        bg: 'bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800',
        title: 'Approve Pending Shops',
        body: props.kpis.pendingShops > 0
            ? `${props.kpis.pendingShops} shop(s) are waiting for approval. Faster onboarding improves owner satisfaction and speeds up revenue recognition.`
            : 'All shop registrations are up to date — no pending approvals.',
    },
    {
        icon: PieChart, color: 'text-purple-600',
        bg: 'bg-purple-50 border-purple-200 dark:bg-purple-900/20 dark:border-purple-800',
        title: 'Monitor Inventory Health',
        body: liveKpis.value.lowStockCount > 0
            ? `${liveKpis.value.lowStockCount} low-stock and ${liveKpis.value.outOfStockCount} out-of-stock items detected across shops. Notify shop owners to restock to avoid service disruptions.`
            : 'Inventory levels are healthy across all registered shops.',
    },
])
</script>

<template>
    <Head title="Super Admin — Platform DSS" />

    <AdminLayout :breadcrumbs="breadcrumbs" title="Platform Decision Support">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">

            <!-- Banner -->
            <div class="rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 p-6 text-white shadow-lg">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h2 class="text-2xl font-bold">Platform Overview — Welcome, {{ user.name }}</h2>
                        <p class="text-indigo-200 mt-1 text-sm">Laundry SaaS · Admin · Decision Support System</p>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <div
                            class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium"
                            :class="isConnected ? 'bg-green-500/20 text-green-200' : 'bg-white/10 text-white/60'"
                        >
                            <component :is="isConnected ? Wifi : WifiOff" class="h-3.5 w-3.5" />
                            {{ isConnected ? 'Live' : 'Static' }}
                        </div>
                        <div v-if="lastUpdated" class="text-xs text-indigo-200">
                            Updated {{ lastUpdated.toLocaleTimeString() }}
                        </div>
                        <button
                            class="flex items-center gap-1.5 bg-white/10 hover:bg-white/20 rounded-lg px-3 py-2 text-xs font-medium transition-colors"
                            @click="manualRefresh"
                        >
                            <RefreshCcw class="h-3.5 w-3.5" /> Refresh
                        </button>
                        <div class="flex items-center gap-2 bg-white/10 rounded-lg px-3 py-2 text-sm font-medium">
                            <Bell class="h-4 w-4" />
                            {{ systemAlerts.filter(a => a.level !== 'info').length }} issues
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid gap-4 grid-cols-2 md:grid-cols-2 xl:grid-cols-4">
                <Card
                    v-for="(kpi, i) in kpiCards" :key="i"
                    class="transition-all duration-300"
                    :class="{ 'ring-2 ring-indigo-400 ring-offset-1': pulse }"
                >
                    <CardContent class="pt-5 pb-4 px-4">
                        <div class="flex items-start justify-between mb-3">
                            <p class="text-xs font-medium text-muted-foreground leading-tight pr-2">{{ kpi.title }}</p>
                            <div
                                class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0"
                                :class="[colorMap[kpi.color].bg, colorMap[kpi.color].icon]"
                            >
                                <component :is="kpi.icon" class="h-4 w-4" />
                            </div>
                        </div>
                        <p class="text-2xl font-bold tabular-nums">{{ kpi.value }}</p>
                        <div v-if="kpi.change !== 0" class="flex items-center gap-1 mt-1 text-xs">
                            <component
                                :is="kpi.change > 0 ? ArrowUp : ArrowDown"
                                class="h-3 w-3"
                                :class="kpi.invertColor
                                    ? (kpi.change > 0 ? 'text-red-500' : 'text-green-500')
                                    : (kpi.change > 0 ? 'text-green-500' : 'text-red-500')"
                            />
                            <span
                                :class="kpi.invertColor
                                    ? (kpi.change > 0 ? 'text-red-500' : 'text-green-600')
                                    : (kpi.change > 0 ? 'text-green-600' : 'text-red-500')"
                            >
                                {{ Math.abs(kpi.change) }}%
                            </span>
                            <span class="text-muted-foreground">vs last month</span>
                        </div>
                        <p class="text-xs text-muted-foreground mt-1.5 leading-tight">{{ kpi.sub }}</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Alerts -->
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-3">Platform Alerts</p>
                <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                    <div
                        v-for="(alert, i) in systemAlerts" :key="i"
                        class="flex items-start gap-3 rounded-xl border px-4 py-3"
                        :class="alertStyle[alert.level]"
                    >
                        <component :is="alert.icon" class="h-4 w-4 mt-0.5 shrink-0" :class="alertIconStyle[alert.level]" />
                        <div class="flex-1 min-w-0">
                            <p class="text-xs leading-snug">{{ alert.message }}</p>
                            <button v-if="alert.action" class="mt-1.5 text-xs font-semibold underline underline-offset-2 opacity-80 hover:opacity-100">
                                {{ alert.action }} →
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid gap-6 lg:grid-cols-3">
                <Card class="lg:col-span-2">
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-semibold flex items-center gap-2">
                            <BadgeDollarSign class="h-4 w-4 text-emerald-500" />
                            Revenue & Orders ({{ new Date().getFullYear() }})
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <VueApexCharts type="area" height="260" :options="revenueOptions" :series="revenueSeries" />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-semibold flex items-center gap-2">
                            <PieChart class="h-4 w-4 text-blue-500" />
                            Subscription Plans
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <VueApexCharts
                            v-if="donutSeries.length > 0"
                            type="donut" height="260"
                            :options="donutOptions"
                            :series="donutSeries"
                        />
                        <div v-else class="flex items-center justify-center h-64 text-sm text-muted-foreground">
                            No active subscriptions
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Shop Growth + Tenant Table -->
            <div class="grid gap-6 lg:grid-cols-5">
                <Card class="lg:col-span-2">
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-semibold flex items-center gap-2">
                            <Store class="h-4 w-4 text-purple-500" />
                            Monthly Shop Registrations
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <VueApexCharts type="bar" height="240" :options="shopGrowthOptions" :series="shopGrowthSeries" />
                    </CardContent>
                </Card>

                <Card class="lg:col-span-3">
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-semibold flex items-center gap-2">
                            <BarChart3 class="h-4 w-4 text-indigo-500" />
                            Tenant Shops (Latest 10)
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="rounded-lg border overflow-hidden">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                                        <th class="text-left px-4 py-2.5 font-medium">Shop</th>
                                        <th class="text-left px-4 py-2.5 font-medium">Owner</th>
                                        <th class="text-left px-4 py-2.5 font-medium">Plan</th>
                                        <th class="text-left px-4 py-2.5 font-medium">Status</th>
                                        <th class="text-left px-4 py-2.5 font-medium">Expiry</th>
                                        <th class="text-left px-4 py-2.5 font-medium">Fee</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(shop, i) in props.shops" :key="i"
                                        class="border-b last:border-0 hover:bg-muted/20 transition-colors"
                                        :class="{ 'bg-amber-50/50 dark:bg-amber-900/10': shop.is_expiring }"
                                    >
                                        <td class="px-4 py-2.5 font-medium text-xs whitespace-nowrap">{{ shop.name }}</td>
                                        <td class="px-4 py-2.5 text-xs text-muted-foreground whitespace-nowrap">{{ shop.owner }}</td>
                                        <td class="px-4 py-2.5 text-xs">{{ shop.plan }}</td>
                                        <td class="px-4 py-2.5">
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                                                :class="statusBadge[shop.status] ?? statusBadge['inactive']"
                                            >
                                                {{ shop.status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2.5 text-xs whitespace-nowrap" :class="shop.is_expiring ? 'text-amber-600 font-medium' : 'text-muted-foreground'">
                                            {{ shop.expiry }}
                                            <span v-if="shop.is_expiring" class="ml-1 text-amber-500">⚠</span>
                                        </td>
                                        <td class="px-4 py-2.5 text-xs font-medium">{{ shop.revenue }}</td>
                                    </tr>
                                    <tr v-if="props.shops.length === 0">
                                        <td colspan="6" class="px-4 py-8 text-center text-xs text-muted-foreground">No shops found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- DSS Recommendations -->
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-3">DSS Recommendations</p>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div
                        v-for="(rec, i) in recommendations" :key="i"
                        class="rounded-xl border p-4 space-y-2"
                        :class="rec.bg"
                    >
                        <div class="flex items-center gap-2">
                            <component :is="rec.icon" class="h-4 w-4 shrink-0" :class="rec.color" />
                            <p class="text-sm font-semibold">{{ rec.title }}</p>
                        </div>
                        <p class="text-xs text-muted-foreground leading-relaxed">{{ rec.body }}</p>
                    </div>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
