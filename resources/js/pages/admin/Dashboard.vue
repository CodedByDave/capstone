<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

import type { ApexOptions } from 'apexcharts';
import { ArrowRight, Bell, RefreshCcw, Wifi, WifiOff } from 'lucide-vue-next';
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
    lastUpdated.value = new Date();
    refreshTimer = setInterval(refreshWhenVisible, 10000);
    document.addEventListener('visibilitychange', refreshWhenVisible);

    // @ts-expect-error Echo is registered globally by the bootstrap module.
    if (!window.Echo) return;
    // @ts-expect-error Echo is registered globally by the bootstrap module.
    window.Echo.channel('admin.dashboard')
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
    // @ts-expect-error Echo is registered globally by the bootstrap module.
    window.Echo?.leaveChannel('admin.dashboard');
});

function manualRefresh() {
    refreshLiveData();
}

// ─── Formatting ───────────────────────────────────────────────────────────────

const peso = (value: number, decimals = 2) =>
    `₱${Number(value).toLocaleString('en-PH', { minimumFractionDigits: decimals, maximumFractionDigits: decimals })}`;

const signed = (value: number) =>
    `${value > 0 ? '+' : value < 0 ? '−' : ''}${Math.abs(value)}%`;

const trendClass = (value: number) =>
    value > 0
        ? 'text-emerald-500'
        : value < 0
          ? 'text-red-500'
          : 'text-muted-foreground';

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

const year = new Date().getFullYear();

// ─── Alerts (drive the issue count) ───────────────────────────────────────────

const issueCount = computed(() => {
    const a = props.alerts;
    return (
        (a.overduePayments.length > 0 ? 1 : 0) +
        (a.outOfStockCount > 0 ? 1 : 0) +
        (a.expiringShops.length > 0 ? 1 : 0) +
        (a.lowStockCount > 0 ? 1 : 0) +
        (a.inactiveShops.length > 0 ? 1 : 0) +
        (a.pendingShops > 0 ? 1 : 0)
    );
});

// ─── Needs your attention ─────────────────────────────────────────────────────

const attention = computed(() => {
    const k = liveKpis.value;
    const inactive = props.alerts.inactiveShops.length;

    return [
        {
            label: 'Revenue',
            title: 'Grow subscription revenue',
            body:
                k.revenueChange >= 0
                    ? `Revenue is up ${k.revenueChange}% this month. Consider introducing annual billing to lock in recurring income from your ${k.activeSubscriptions} active subscribers.`
                    : `Revenue dropped ${Math.abs(k.revenueChange)}% vs last month. Review pricing or run a re-subscription campaign for the ${k.expiredOrders} expired accounts.`,
            action:
                k.expiredOrders > 0
                    ? 'View expired accounts'
                    : 'View subscriptions',
            href: '/admin/orders',
        },
        {
            label: k.pendingShops > 0 ? 'Onboarding' : 'All clear',
            title: 'Approve pending shops',
            body:
                k.pendingShops > 0
                    ? `${k.pendingShops} shops are waiting for approval. Faster onboarding improves owner satisfaction and speeds up revenue recognition.`
                    : 'All shop registrations are up to date — no pending approvals.',
            action:
                k.pendingShops > 0
                    ? `Review ${k.pendingShops} shops`
                    : 'View shops',
            href: '/admin/shop',
        },
        {
            label: inactive > 0 ? 'Churn risk' : 'All clear',
            title: 'Re-engage inactive shops',
            body:
                inactive > 0
                    ? `${inactive} shop(s) have been idle for 21+ days. Send a re-engagement email with a renewal discount before they churn permanently.`
                    : 'No inactive shops detected. Platform engagement is strong.',
            action: 'View shop activity',
            href: '/admin/shop',
        },
        {
            label: 'Coming soon',
            title: 'Monitor customer feedback',
            body: 'Feedback insights and recommendations will appear here once the customer feedback feature is available.',
            action: 'Learn more',
            href: '/admin/platform-ratings',
        },
    ];
});

// ─── Chart styling shared by every chart ──────────────────────────────────────

const AXIS = '#8b93a1';
const GRID = 'rgba(148, 163, 184, 0.22)';

const baseChart = {
    toolbar: { show: false },
    zoom: { enabled: false },
    background: 'transparent',
    fontFamily: 'inherit',
};

const xAxis = {
    categories: MONTHS,
    labels: { style: { colors: AXIS, fontSize: '11px' } },
    axisBorder: { show: false },
    axisTicks: { show: false },
    tooltip: { enabled: false },
};

// Revenue line
const revenuePeak = computed(() => {
    const data = props.charts.revenue;
    const max = Math.max(0, ...data);
    return max > 0 ? { month: MONTHS[data.indexOf(max)], value: max } : null;
});

const revenueOptions: ApexOptions = {
    chart: { ...baseChart, type: 'area' },
    stroke: { curve: 'smooth', width: 2 },
    fill: {
        type: 'gradient',
        gradient: { shadeIntensity: 1, opacityFrom: 0.28, opacityTo: 0.02 },
    },
    xaxis: xAxis,
    yaxis: {
        min: 0,
        labels: {
            style: { colors: AXIS, fontSize: '11px' },
            formatter: (value: number) =>
                value === 0 ? '₱0' : `₱${Math.round(value / 1000)}k`,
        },
    },
    colors: ['#60a5fa'],
    markers: { size: 0, hover: { size: 4 } },
    grid: {
        borderColor: GRID,
        strokeDashArray: 2,
        xaxis: { lines: { show: false } },
    },
    dataLabels: { enabled: false },
    tooltip: {
        theme: 'dark',
        y: { formatter: (value: number) => peso(value) },
    },
};

const revenueSeries = computed(() => [
    { name: 'Revenue', data: props.charts.revenue },
]);

// Subscription plans donut
const PLAN_COLORS = ['#34d399', '#3b82f6', '#a78bfa', '#f59e0b', '#ef4444'];

const plans = computed(() => {
    const entries = Object.entries(props.planBreakdown).map(
        ([name, count]) => ({ name, count: Number(count) }),
    );
    const total = entries.reduce((sum, plan) => sum + plan.count, 0);

    return {
        total,
        rows: entries.map((plan, index) => ({
            ...plan,
            color: PLAN_COLORS[index % PLAN_COLORS.length],
            percent: total > 0 ? Math.round((plan.count / total) * 100) : 0,
        })),
    };
});

const donutSeries = computed(() => plans.value.rows.map((plan) => plan.count));

const donutOptions = computed<ApexOptions>(() => ({
    chart: { ...baseChart, type: 'donut' },
    labels: plans.value.rows.map((plan) => plan.name),
    colors: plans.value.rows.map((plan) => plan.color),
    legend: { show: false },
    dataLabels: { enabled: false },
    stroke: { show: false },
    plotOptions: {
        pie: {
            donut: {
                size: '74%',
                labels: {
                    show: true,
                    name: {
                        show: true,
                        color: AXIS,
                        fontSize: '12px',
                        offsetY: 16,
                    },
                    value: {
                        show: true,
                        fontSize: '28px',
                        fontWeight: 700,
                        offsetY: -14,
                    },
                    total: {
                        show: true,
                        label: 'total plans',
                        color: AXIS,
                        fontSize: '12px',
                        formatter: () => String(plans.value.total),
                    },
                },
            },
        },
    },
    tooltip: { theme: 'dark' },
}));

// Bar charts (value label above each non-zero bar)
function barOptions(
    color: string,
    unit: string,
    columnWidth: string,
): ApexOptions {
    return {
        chart: { ...baseChart, type: 'bar' },
        plotOptions: {
            bar: {
                borderRadius: 4,
                columnWidth,
                dataLabels: { position: 'top' },
            },
        },
        dataLabels: {
            enabled: true,
            offsetY: -20,
            formatter: (value: number) => (value > 0 ? String(value) : ''),
            style: { fontSize: '11px', colors: [AXIS] },
        },
        xaxis: xAxis,
        yaxis: { show: false, min: 0 },
        colors: [color],
        grid: { show: false },
        states: { hover: { filter: { type: 'none' } } },
        tooltip: {
            theme: 'dark',
            y: { formatter: (value: number) => `${value} ${unit}` },
        },
    };
}

const shopGrowthOptions = barOptions('#a78bfa', 'new shops', '55%');
const orderActivityOptions = barOptions('#3b82f6', 'orders', '48%');

const shopGrowthSeries = computed(() => [
    { name: 'New shops', data: props.charts.shops },
]);

const orderActivitySeries = computed(() => [
    { name: 'Orders', data: props.charts.orders },
]);

const ordersTotal = computed(() =>
    props.charts.orders.reduce((sum, count) => sum + count, 0),
);
</script>

<template>
    <Head title="Super Admin — Platform DSS" />

    <AdminLayout :breadcrumbs="breadcrumbs" title="Platform Decision Support">
        <div class="mx-auto flex w-full max-w-[1280px] flex-col gap-6 p-6">
            <!-- Header -->
            <header class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        Platform overview
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Welcome, {{ user.name }} · Laundry SaaS decision support
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <span
                        class="inline-flex h-9 items-center gap-2 rounded-lg border px-3 text-sm font-medium"
                        :class="
                            isLive
                                ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-500'
                                : 'border-border text-muted-foreground'
                        "
                    >
                        <component
                            :is="isLive ? Wifi : WifiOff"
                            class="h-3.5 w-3.5"
                            aria-hidden="true"
                        />
                        {{ isLive ? 'Live' : 'Static' }}
                    </span>

                    <span
                        class="inline-flex h-9 items-center gap-2 rounded-lg border px-3 text-sm font-medium"
                        :class="
                            issueCount > 0
                                ? 'border-amber-500/40 bg-amber-500/10 text-amber-500'
                                : 'border-border text-muted-foreground'
                        "
                    >
                        <Bell class="h-3.5 w-3.5" aria-hidden="true" />
                        {{ issueCount }}
                        {{ issueCount === 1 ? 'issue' : 'issues' }}
                    </span>

                    <span
                        v-if="lastUpdated"
                        class="text-sm text-muted-foreground"
                    >
                        Updated
                        {{
                            lastUpdated.toLocaleTimeString([], {
                                hour: 'numeric',
                                minute: '2-digit',
                            })
                        }}
                    </span>

                    <button
                        type="button"
                        class="inline-flex h-9 items-center gap-2 rounded-lg border bg-card px-3.5 text-sm font-semibold transition-colors hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none disabled:opacity-60"
                        :disabled="isRefreshing"
                        @click="manualRefresh"
                    >
                        <RefreshCcw
                            class="h-3.5 w-3.5"
                            :class="{ 'animate-spin': isRefreshing }"
                            aria-hidden="true"
                        />
                        Refresh
                    </button>
                </div>
            </header>

            <!-- Needs your attention -->
            <section aria-labelledby="attention-heading">
                <h2
                    id="attention-heading"
                    class="mb-3 text-xs font-semibold tracking-[0.12em] text-muted-foreground uppercase"
                >
                    Needs your attention
                </h2>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article
                        v-for="item in attention"
                        :key="item.title"
                        class="flex flex-col rounded-2xl border bg-card p-5"
                    >
                        <p
                            class="text-[11px] font-semibold tracking-[0.12em] text-muted-foreground uppercase"
                        >
                            {{ item.label }}
                        </p>
                        <h3 class="mt-3 text-base font-semibold">
                            {{ item.title }}
                        </h3>
                        <p
                            class="mt-2 flex-1 text-sm leading-relaxed text-muted-foreground"
                        >
                            {{ item.body }}
                        </p>
                        <Link
                            :href="item.href"
                            class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-blue-500 hover:underline focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        >
                            {{ item.action }}
                            <ArrowRight
                                class="h-3.5 w-3.5"
                                aria-hidden="true"
                            />
                        </Link>
                    </article>
                </div>
            </section>

            <!-- KPIs -->
            <section
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)]"
            >
                <div
                    class="rounded-2xl border bg-card p-5 transition-shadow duration-300 sm:col-span-2 lg:col-span-1"
                    :class="{ 'ring-2 ring-blue-400/60': pulse }"
                >
                    <p
                        class="text-[11px] font-semibold tracking-[0.12em] text-muted-foreground uppercase"
                    >
                        Platform revenue · month to date
                    </p>
                    <p
                        class="mt-3 text-3xl font-bold tracking-tight tabular-nums"
                    >
                        {{ peso(liveKpis.revenueThisMonth) }}
                    </p>
                    <p class="mt-2 text-xs text-muted-foreground">
                        <span
                            class="font-semibold"
                            :class="trendClass(liveKpis.revenueChange)"
                            >{{ signed(liveKpis.revenueChange) }}</span
                        >
                        vs last month · {{ peso(liveKpis.totalRevenue) }}
                        all-time
                    </p>
                </div>

                <div
                    class="rounded-2xl border bg-card p-5 transition-shadow duration-300"
                    :class="{ 'ring-2 ring-blue-400/60': pulse }"
                >
                    <p
                        class="text-[11px] font-semibold tracking-[0.12em] text-muted-foreground uppercase"
                    >
                        Registered shops
                    </p>
                    <p class="mt-3 text-3xl font-bold tabular-nums">
                        {{ liveKpis.totalShops.toLocaleString() }}
                    </p>
                    <p class="mt-2 text-xs text-muted-foreground">
                        {{ liveKpis.activeShops }} active ·
                        <span class="text-amber-500"
                            >{{ liveKpis.pendingShops }} pending</span
                        >
                    </p>
                </div>

                <div
                    class="rounded-2xl border bg-card p-5 transition-shadow duration-300"
                    :class="{ 'ring-2 ring-blue-400/60': pulse }"
                >
                    <p
                        class="text-[11px] font-semibold tracking-[0.12em] text-muted-foreground uppercase"
                    >
                        Active subscriptions
                    </p>
                    <p class="mt-3 text-3xl font-bold tabular-nums">
                        {{ liveKpis.activeSubscriptions.toLocaleString() }}
                    </p>
                    <p class="mt-2 text-xs text-muted-foreground">
                        <span class="text-red-500"
                            >{{ liveKpis.expiredOrders }} expired</span
                        >
                        ·
                        <span :class="trendClass(liveKpis.ordersChange)">{{
                            signed(liveKpis.ordersChange)
                        }}</span>
                    </p>
                </div>

                <div
                    class="rounded-2xl border bg-card p-5 transition-shadow duration-300"
                    :class="{ 'ring-2 ring-blue-400/60': pulse }"
                >
                    <p
                        class="text-[11px] font-semibold tracking-[0.12em] text-muted-foreground uppercase"
                    >
                        Registered owners
                    </p>
                    <p class="mt-3 text-3xl font-bold tabular-nums">
                        {{ liveKpis.totalOwners.toLocaleString() }}
                    </p>
                    <p class="mt-2 text-xs text-muted-foreground">
                        {{ liveKpis.totalCustomers }} customers ·
                        <span :class="trendClass(liveKpis.usersChange)">{{
                            signed(liveKpis.usersChange)
                        }}</span>
                    </p>
                </div>
            </section>

            <!-- Revenue + plans -->
            <section
                class="grid gap-4 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]"
            >
                <div class="rounded-2xl border bg-card p-5">
                    <div
                        class="flex flex-wrap items-baseline justify-between gap-2"
                    >
                        <h2 class="text-base font-semibold">
                            Revenue {{ year }}
                        </h2>
                        <p
                            v-if="revenuePeak"
                            class="text-xs text-muted-foreground"
                        >
                            Peak: {{ revenuePeak.month }} ·
                            {{ peso(revenuePeak.value, 0) }}
                        </p>
                    </div>
                    <VueApexCharts
                        type="area"
                        height="280"
                        :options="revenueOptions"
                        :series="revenueSeries"
                    />
                </div>

                <div class="flex flex-col rounded-2xl border bg-card p-5">
                    <h2 class="text-base font-semibold">Subscription plans</h2>
                    <template v-if="plans.total > 0">
                        <VueApexCharts
                            class="mt-2"
                            type="donut"
                            height="230"
                            :options="donutOptions"
                            :series="donutSeries"
                        />
                        <ul class="mt-auto space-y-2.5 pt-2 text-sm">
                            <li
                                v-for="plan in plans.rows"
                                :key="plan.name"
                                class="flex items-center justify-between gap-3"
                            >
                                <span class="flex items-center gap-2.5">
                                    <span
                                        class="h-2.5 w-2.5 rounded-sm"
                                        :style="{ backgroundColor: plan.color }"
                                        aria-hidden="true"
                                    />
                                    {{ plan.name }}
                                </span>
                                <span class="font-semibold tabular-nums">
                                    {{ plan.count }} · {{ plan.percent }}%
                                </span>
                            </li>
                        </ul>
                    </template>
                    <div
                        v-else
                        class="flex flex-1 items-center justify-center py-16 text-sm text-muted-foreground"
                    >
                        No active subscriptions
                    </div>
                </div>
            </section>

            <!-- Registrations + orders -->
            <section class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-2xl border bg-card p-5">
                    <div
                        class="flex flex-wrap items-baseline justify-between gap-2"
                    >
                        <h2 class="text-base font-semibold">
                            Monthly shop registrations
                        </h2>
                        <p class="text-xs text-muted-foreground">
                            {{ liveKpis.totalShops }} total
                        </p>
                    </div>
                    <VueApexCharts
                        type="bar"
                        height="240"
                        :options="shopGrowthOptions"
                        :series="shopGrowthSeries"
                    />
                </div>

                <div class="rounded-2xl border bg-card p-5">
                    <div
                        class="flex flex-wrap items-baseline justify-between gap-2"
                    >
                        <h2 class="text-base font-semibold">Order activity</h2>
                        <p class="text-xs text-muted-foreground">
                            {{ ordersTotal }} orders in {{ year }}
                        </p>
                    </div>
                    <VueApexCharts
                        type="bar"
                        height="240"
                        :options="orderActivityOptions"
                        :series="orderActivitySeries"
                    />
                </div>
            </section>
        </div>
    </AdminLayout>
</template>
