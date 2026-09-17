<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Bell, RefreshCcw, Wifi, WifiOff } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
];

const page = usePage();
const user = page.props.auth.user;

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    kpis: {
        totalShops: number;
        activeShops: number;
        disabledShops: number;
        pendingShops: number;
        shopChange: number;
        totalOwners: number;
        totalCustomers: number;
        newUsersMonth: number;
        usersChange: number;
        totalOrders: number;
        activeSubscriptions: number;
        expiredOrders: number;
        ordersThisMonth: number;
        ordersChange: number;
        revenueThisMonth: number;
        revenueLastMonth: number;
        revenueChange: number;
        totalRevenue: number;
        totalEmployees: number;
        activeEmployees: number;
        totalInventory: number;
        lowStockCount: number;
        outOfStockCount: number;
    };
    alerts: {
        overduePayments: string[];
        expiringShops: string[];
        inactiveShops: string[];
        pendingShops: number;
        lowStockCount: number;
        outOfStockCount: number;
    };
    charts: {
        revenue: number[];
        orders: number[];
        shops: number[];
    };
    planBreakdown: Record<string, number>;
}>();

// ─── Live state ───────────────────────────────────────────────────────────────

const liveKpis = ref({ ...props.kpis });
const isConnected = ref(false);
const isPollingLive = ref(false);
const isRefreshing = ref(false);
const lastUpdated = ref<Date | null>(null);
const pulse = ref(false);
const isLive = computed(() => isConnected.value || isPollingLive.value);

watch(
    () => props.kpis,
    (kpis) => {
        const changed = JSON.stringify(kpis) !== JSON.stringify(liveKpis.value);
        liveKpis.value = { ...kpis };
        if (changed) triggerPulse();
    },
);

function triggerPulse() {
    pulse.value = true;
    setTimeout(() => {
        pulse.value = false;
    }, 700);
}

// ─── Echo real-time ───────────────────────────────────────────────────────────

let channel: any = null;
let refreshTimer: ReturnType<typeof setInterval> | null = null;

function refreshLiveData() {
    if (isRefreshing.value) return;

    isRefreshing.value = true;
    router.reload({
        only: ['kpis', 'alerts', 'charts', 'planBreakdown'],
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isPollingLive.value = true;
            lastUpdated.value = new Date();
        },
        onError: () => {
            isPollingLive.value = false;
        },
        onFinish: () => {
            isRefreshing.value = false;
        },
    });
}

function refreshWhenVisible() {
    if (document.visibilityState === 'visible') refreshLiveData();
}

onMounted(() => {
    isPollingLive.value = true;
    refreshTimer = setInterval(refreshWhenVisible, 10000);
    document.addEventListener('visibilitychange', refreshWhenVisible);

    // @ts-ignore
    if (!window.Echo) return;
    // @ts-ignore
    channel = window.Echo.channel('admin.dashboard')
        .listen('.dashboard.updated', (data: any) => {
            if (data.kpis) liveKpis.value = { ...liveKpis.value, ...data.kpis };
            lastUpdated.value = new Date();
            triggerPulse();
        })
        .subscribed(() => {
            isConnected.value = true;
        })
        .error(() => {
            isConnected.value = false;
        });
});

onUnmounted(() => {
    if (refreshTimer) clearInterval(refreshTimer);
    document.removeEventListener('visibilitychange', refreshWhenVisible);
    // @ts-ignore
    window.Echo?.leaveChannel('admin.dashboard');
});

function manualRefresh() {
    refreshLiveData();
}

// ─── KPI cards ────────────────────────────────────────────────────────────────

const revenueCard = computed(() => ({
    title: 'Platform Revenue MTD',
    value: `₱${Number(liveKpis.value.revenueThisMonth).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`,
    change: liveKpis.value.revenueChange,
    sub: `₱${Number(liveKpis.value.totalRevenue).toLocaleString('en-PH', { minimumFractionDigits: 2 })} all-time revenue`,
}));

const revenueTrendClasses = computed(() => {
    if (revenueCard.value.change > 0)
        return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300';
    if (revenueCard.value.change < 0)
        return 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300';
    return 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300';
});

const revenueSparklineSeries = computed(() => [
    {
        name: 'Revenue',
        data: props.charts.revenue.slice(0, new Date().getMonth() + 1),
    },
]);

const revenueSparklineOptions = {
    chart: {
        type: 'area',
        sparkline: { enabled: true },
        toolbar: { show: false },
        animations: { enabled: true, speed: 450 },
        background: 'transparent',
        fontFamily: 'inherit',
    },
    stroke: {
        curve: 'straight',
        width: 3,
        lineCap: 'round',
    },
    colors: ['#64748b'],
    markers: { size: 0 },
    dataLabels: { enabled: false },
    grid: { show: false },
    yaxis: { min: 0 },
    tooltip: {
        theme: 'dark',
        x: { show: false },
        y: {
            formatter: (value: number) =>
                `₱${Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`,
            title: { formatter: () => '' },
        },
    },
};

const kpiCards = computed(() => [
    {
        title: 'Registered Shops',
        value: liveKpis.value.totalShops.toLocaleString(),
        change: liveKpis.value.shopChange,
        sub: `${liveKpis.value.activeShops} active · ${liveKpis.value.pendingShops} pending`,
    },
    {
        title: 'Active Subscriptions',
        value: liveKpis.value.activeSubscriptions.toLocaleString(),
        change: liveKpis.value.ordersChange,
        sub: `${liveKpis.value.expiredOrders} expired`,
    },
    {
        title: 'Registered Owners',
        value: liveKpis.value.totalOwners.toLocaleString(),
        change: liveKpis.value.usersChange,
        sub: `${liveKpis.value.totalCustomers} customers`,
    },
]);

// ─── Alerts ───────────────────────────────────────────────────────────────────

const systemAlerts = computed(() => {
    const list: {
        level: string;
        message: string;
        action: string | null;
    }[] = [];

    if (props.alerts.overduePayments.length > 0)
        list.push({
            level: 'critical',
            message: `${props.alerts.overduePayments.length} shop(s) have overdue subscription payments (7+ days): ${props.alerts.overduePayments.slice(0, 2).join(', ')}${props.alerts.overduePayments.length > 2 ? ` +${props.alerts.overduePayments.length - 2} more` : ''}.`,
            action: 'Manage Subscriptions',
        });

    if (props.alerts.outOfStockCount > 0)
        list.push({
            level: 'critical',
            message: `${props.alerts.outOfStockCount} inventory item(s) are completely out of stock across all shops.`,
            action: 'View Inventory',
        });

    if (props.alerts.expiringShops.length > 0)
        list.push({
            level: 'warning',
            message: `${props.alerts.expiringShops.length} subscription(s) expire within 7 days: ${props.alerts.expiringShops.slice(0, 2).join(', ')}.`,
            action: 'View Expiring',
        });

    if (props.alerts.lowStockCount > 0)
        list.push({
            level: 'warning',
            message: `${props.alerts.lowStockCount} inventory item(s) are running low on stock across all shops.`,
            action: 'View Low Stock',
        });

    if (props.alerts.inactiveShops.length > 0)
        list.push({
            level: 'warning',
            message: `${props.alerts.inactiveShops.length} shop(s) have had no activity for 21+ days — possible churn risk.`,
            action: 'View Shops',
        });

    if (props.alerts.pendingShops > 0)
        list.push({
            level: 'info',
            message: `${props.alerts.pendingShops} new shop registration(s) are awaiting your approval.`,
            action: 'Approve Shops',
        });

    if (list.length === 0)
        list.push({
            level: 'info',
            message: 'All systems normal — no alerts at this time.',
            action: null,
        });

    return list;
});

const alertStyle: Record<string, string> = {
    critical:
        'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20 text-red-800 dark:text-red-300',
    warning:
        'border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20 text-amber-800 dark:text-amber-300',
    info: 'border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300',
};

// ─── Charts ───────────────────────────────────────────────────────────────────

const MONTHS = [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'May',
    'Jun',
    'Jul',
    'Aug',
    'Sep',
    'Oct',
    'Nov',
    'Dec',
];

const revenueOptions = ref({
    chart: {
        type: 'line',
        toolbar: { show: false },
        zoom: { enabled: false },
        background: 'transparent',
        fontFamily: 'inherit',
    },
    stroke: { curve: 'smooth', width: 2.5 },
    fill: {
        type: 'gradient',
        gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.02 },
    },
    xaxis: {
        categories: MONTHS,
        labels: { style: { colors: '#9ca3af', fontSize: '11px' } },
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: {
        title: { text: 'Revenue (₱)', style: { color: '#9ca3af' } },
        labels: {
            style: { colors: '#9ca3af', fontSize: '11px' },
            formatter: (value: number) => `₱${(value / 1000).toFixed(0)}k`,
        },
    },
    colors: ['#10b981'],
    legend: { labels: { colors: '#9ca3af' } },
    tooltip: {
        theme: 'dark',
        y: {
            formatter: (value: number) =>
                `₱${Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`,
        },
    },
    grid: {
        borderColor: '#e5e7eb',
        strokeDashArray: 4,
        xaxis: { lines: { show: false } },
    },
    dataLabels: { enabled: false },
});

const revenueSeries = computed(() => [
    { name: 'Revenue (₱)', data: props.charts.revenue },
]);

// Plan breakdown donut
const donutSeries = computed(() =>
    Object.values(props.planBreakdown).map(Number),
);

const donutOptions = computed(() => ({
    chart: { type: 'donut', background: 'transparent', fontFamily: 'inherit' },
    labels: Object.keys(props.planBreakdown),
    colors: ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444'],
    legend: {
        position: 'bottom',
        labels: { colors: '#9ca3af' },
        fontSize: '12px',
        formatter: (planName: string, options: any) =>
            `${planName}: ${options.w.globals.series[options.seriesIndex]}`,
    },
    dataLabels: {
        enabled: true,
        formatter: (_percentage: number, options: any) =>
            String(options.w.config.series[options.seriesIndex]),
        style: { fontSize: '12px', fontWeight: 600 },
    },
    plotOptions: {
        pie: {
            donut: {
                size: '65%',
                labels: {
                    show: true,
                    total: {
                        show: true,
                        label: 'Total Plans',
                        color: '#9ca3af',
                        formatter: (chart: any) =>
                            String(
                                chart.globals.seriesTotals.reduce(
                                    (total: number, count: number) =>
                                        total + count,
                                    0,
                                ),
                            ),
                    },
                },
            },
        },
    },
    tooltip: { theme: 'dark' },
    stroke: { show: false },
}));

const shopGrowthOptions = ref({
    chart: {
        type: 'bar',
        toolbar: { show: false },
        background: 'transparent',
        fontFamily: 'inherit',
    },
    plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
    xaxis: {
        categories: MONTHS,
        labels: { style: { colors: '#9ca3af', fontSize: '11px' } },
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: { labels: { style: { colors: '#9ca3af', fontSize: '11px' } } },
    colors: ['#8b5cf6'],
    dataLabels: { enabled: false },
    grid: {
        borderColor: '#e5e7eb',
        strokeDashArray: 4,
        xaxis: { lines: { show: false } },
    },
    tooltip: { theme: 'dark' },
});

const shopGrowthSeries = computed(() => [
    { name: 'New Shops', data: props.charts.shops },
]);

const orderActivityOptions = ref({
    chart: {
        type: 'bar',
        toolbar: { show: false },
        background: 'transparent',
        fontFamily: 'inherit',
    },
    plotOptions: { bar: { borderRadius: 4, columnWidth: '48%' } },
    xaxis: {
        categories: MONTHS,
        labels: { style: { colors: '#9ca3af', fontSize: '11px' } },
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: {
        min: 0,
        forceNiceScale: true,
        decimalsInFloat: 0,
        title: { text: 'Orders', style: { color: '#9ca3af' } },
        labels: {
            style: { colors: '#9ca3af', fontSize: '11px' },
            formatter: (value: number) => String(Math.round(value)),
        },
    },
    colors: ['#3b82f6'],
    dataLabels: { enabled: false },
    grid: {
        borderColor: '#e5e7eb',
        strokeDashArray: 4,
        xaxis: { lines: { show: false } },
    },
    tooltip: {
        theme: 'dark',
        y: {
            formatter: (value: number) =>
                `${Math.round(value).toLocaleString('en-PH')} orders`,
        },
    },
});

const orderActivitySeries = computed(() => [
    { name: 'Orders', data: props.charts.orders },
]);

// ─── DSS Recommendations ──────────────────────────────────────────────────────

const recommendations = computed(() => [
    {
        bg: 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800',
        title: 'Grow Subscription Revenue',
        body:
            liveKpis.value.revenueChange >= 0
                ? `Revenue is up ${liveKpis.value.revenueChange}% this month. Consider introducing annual billing to lock in recurring income from your ${liveKpis.value.activeSubscriptions} active subscribers.`
                : `Revenue dropped ${Math.abs(liveKpis.value.revenueChange)}% vs last month. Review pricing or run a re-subscription campaign for the ${liveKpis.value.expiredOrders} expired accounts.`,
    },
    {
        bg: 'bg-amber-50 border-amber-200 dark:bg-amber-900/20 dark:border-amber-800',
        title: 'Re-engage Inactive Shops',
        body:
            props.alerts.inactiveShops.length > 0
                ? `${props.alerts.inactiveShops.length} shop(s) have been idle for 21+ days. Send a re-engagement email with a renewal discount before they churn permanently.`
                : 'No inactive shops detected — great platform engagement!',
    },
    {
        bg: 'bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800',
        title: 'Approve Pending Shops',
        body:
            props.kpis.pendingShops > 0
                ? `${props.kpis.pendingShops} shop(s) are waiting for approval. Faster onboarding improves owner satisfaction and speeds up revenue recognition.`
                : 'All shop registrations are up to date — no pending approvals.',
    },
    {
        bg: 'bg-purple-50 border-purple-200 dark:bg-purple-900/20 dark:border-purple-800',
        title: 'Monitor Customer Feedback',
        body: 'Feedback insights and recommendations will appear here once the customer feedback feature is available.',
    },
]);
</script>

<template>
    <Head title="Super Admin — Platform DSS" />

    <AdminLayout :breadcrumbs="breadcrumbs" title="Platform Decision Support">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Banner -->
            <div class="text-dark rounded-xl p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold">
                            Platform Overview — Welcome, {{ user.name }}
                        </h2>
                        <p class="text-dark-200 mt-1 text-sm">
                            Laundry SaaS · Admin · Decision Support System
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Connection Status -->
                        <div
                            class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold shadow-sm"
                            :class="
                                isLive
                                    ? 'bg-emerald-600 text-white'
                                    : 'bg-slate-600 text-white'
                            "
                        >
                            <component
                                :is="isLive ? Wifi : WifiOff"
                                class="h-4 w-4 shrink-0 text-white"
                            />
                            <span>{{ isLive ? 'Live' : 'Static' }}</span>
                        </div>

                        <!-- Last Updated -->
                        <div
                            v-if="lastUpdated"
                            class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm"
                        >
                            Updated {{ lastUpdated.toLocaleTimeString() }}
                        </div>

                        <!-- Refresh Button -->
                        <button
                            type="button"
                            class="flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-blue-700"
                            :disabled="isRefreshing"
                            @click="manualRefresh"
                        >
                            <RefreshCcw
                                class="h-4 w-4 shrink-0 text-white"
                                :class="{ 'animate-spin': isRefreshing }"
                            />
                            <span>{{
                                isRefreshing ? 'Updating' : 'Refresh'
                            }}</span>
                        </button>

                        <!-- Issues -->
                        <div
                            class="flex items-center gap-2 rounded-lg bg-amber-500 px-3 py-2 text-sm font-semibold text-white shadow-sm"
                        >
                            <Bell class="h-4 w-4 shrink-0 text-white" />
                            <span>
                                {{
                                    systemAlerts.filter(
                                        (a) => a.level !== 'info',
                                    ).length
                                }}
                                issues
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI Cards -->
            <div
                class="grid items-stretch gap-4 lg:grid-cols-[minmax(0,4fr)_minmax(0,5fr)]"
            >
                <Card
                    class="min-h-[310px] overflow-hidden transition-all duration-300 lg:min-h-0"
                    :class="{ 'ring-2 ring-indigo-400 ring-offset-1': pulse }"
                >
                    <CardContent
                        class="grid h-full gap-5 p-6 sm:grid-cols-[minmax(0,1fr)_minmax(150px,0.9fr)] sm:p-7"
                    >
                        <div class="flex min-w-0 flex-col justify-between">
                            <div>
                                <p
                                    class="text-sm font-semibold text-muted-foreground"
                                >
                                    {{ revenueCard.title }}
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Current calendar month
                                </p>
                            </div>

                            <div class="py-7">
                                <p
                                    class="text-4xl font-bold tracking-tight break-words tabular-nums"
                                >
                                    {{ revenueCard.value }}
                                </p>
                                <div
                                    class="mt-4 flex flex-wrap items-center gap-2"
                                >
                                    <div
                                        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                        :class="revenueTrendClasses"
                                    >
                                        {{ Math.abs(revenueCard.change) }}%
                                    </div>
                                    <span class="text-xs text-muted-foreground"
                                        >vs last month</span
                                    >
                                </div>
                            </div>

                            <div class="border-t pt-4">
                                <p class="text-xs text-muted-foreground">
                                    {{ revenueCard.sub }}
                                </p>
                            </div>
                        </div>

                        <div class="flex min-h-[140px] items-end sm:min-h-0">
                            <VueApexCharts
                                class="w-full"
                                type="line"
                                height="165"
                                :options="revenueSparklineOptions"
                                :series="revenueSparklineSeries"
                            />
                        </div>
                    </CardContent>
                </Card>

                <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-1">
                    <Card
                        v-for="(kpi, i) in kpiCards"
                        :key="i"
                        class="transition-all duration-300"
                        :class="{
                            'ring-2 ring-indigo-400 ring-offset-1': pulse,
                        }"
                    >
                        <CardContent class="h-full p-5">
                            <div class="min-w-0">
                                <p
                                    class="text-xs font-medium text-muted-foreground"
                                >
                                    {{ kpi.title }}
                                </p>
                                <div
                                    class="mt-1 flex flex-wrap items-baseline gap-x-3 gap-y-1"
                                >
                                    <p class="text-2xl font-bold tabular-nums">
                                        {{ kpi.value }}
                                    </p>
                                    <div
                                        v-if="kpi.change !== 0"
                                        class="text-xs"
                                    >
                                        <span
                                            :class="
                                                kpi.change > 0
                                                    ? 'text-green-600'
                                                    : 'text-red-500'
                                            "
                                        >
                                            {{ Math.abs(kpi.change) }}%
                                        </span>
                                    </div>
                                </div>
                                <p
                                    class="mt-1 text-xs leading-tight text-muted-foreground"
                                >
                                    {{ kpi.sub }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid gap-6 lg:grid-cols-3">
                <Card class="lg:col-span-2">
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-semibold">
                            Revenue ({{ new Date().getFullYear() }})
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <VueApexCharts
                            type="area"
                            height="260"
                            :options="revenueOptions"
                            :series="revenueSeries"
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-semibold">
                            Subscription Plans
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <VueApexCharts
                            v-if="donutSeries.length > 0"
                            type="donut"
                            height="260"
                            :options="donutOptions"
                            :series="donutSeries"
                        />
                        <div
                            v-else
                            class="flex h-64 items-center justify-center text-sm text-muted-foreground"
                        >
                            No active subscriptions
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Shop Growth + Order Activity -->
            <div class="grid gap-6 lg:grid-cols-5">
                <Card class="lg:col-span-2">
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-semibold">
                            Monthly Shop Registrations
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <VueApexCharts
                            type="bar"
                            height="240"
                            :options="shopGrowthOptions"
                            :series="shopGrowthSeries"
                        />
                    </CardContent>
                </Card>

                <Card class="lg:col-span-3">
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-semibold">
                            Order Activity
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <VueApexCharts
                            type="bar"
                            height="240"
                            :options="orderActivityOptions"
                            :series="orderActivitySeries"
                        />
                    </CardContent>
                </Card>
            </div>

            <!-- DSS Recommendations -->
            <div>
                <p
                    class="mb-3 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                >
                    DSS Recommendations
                </p>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div
                        v-for="(rec, i) in recommendations"
                        :key="i"
                        class="space-y-2 rounded-xl border p-4"
                        :class="rec.bg"
                    >
                        <p class="text-sm font-semibold">{{ rec.title }}</p>
                        <p
                            class="text-xs leading-relaxed text-muted-foreground"
                        >
                            {{ rec.body }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
