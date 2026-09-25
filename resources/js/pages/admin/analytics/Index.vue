<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import {
    ArrowDownRight,
    ArrowUpRight,
    BarChart3,
    ReceiptText,
    ShoppingBag,
    Store,
    TrendingUp,
    UserPlus,
} from 'lucide-vue-next';
import { computed } from 'vue';

interface RevenuePoint {
    month: string;
    amount: number;
}

interface CountPoint {
    month: string;
    count: number;
}

interface TopShop {
    rank: number;
    name: string;
    location: string;
    revenue: number;
    orders: number;
    growth: number;
}

const props = defineProps<{
    stats: {
        total_shops: { value: number; change: number };
        total_customers: { value: number; change: number };
        total_revenue: { value: number; change: number };
        total_orders: { value: number; change: number };
    };
    revenue_chart: RevenuePoint[];
    orders_chart: CountPoint[];
    registrations_chart: CountPoint[];
    top_shops: TopShop[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Analytics Overview', href: '/admin/analytics' },
];

const formatCurrency = (value: number) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value);

const formatCompactCurrency = (value: number) => {
    if (value >= 1_000_000) return `₱${(value / 1_000_000).toFixed(2)}M`;
    if (value >= 1_000) return `₱${(value / 1_000).toFixed(1)}K`;
    return `₱${value.toLocaleString('en-PH')}`;
};

const formatNumber = (value: number) =>
    new Intl.NumberFormat('en-PH', { notation: 'compact' }).format(value);

const statCards = computed(() => [
    {
        label: 'Total shops',
        value: formatNumber(props.stats.total_shops.value),
    },
    {
        label: 'Customers',
        value: formatNumber(props.stats.total_customers.value),
    },
    {
        label: 'Collected revenue',
        value: formatCompactCurrency(props.stats.total_revenue.value),
    },
    {
        label: 'Paid orders',
        value: formatNumber(props.stats.total_orders.value),
    },
]);

const reportRevenue = computed(() =>
    props.revenue_chart.reduce((sum, point) => sum + point.amount, 0),
);
const reportOrders = computed(() =>
    props.orders_chart.reduce((sum, point) => sum + point.count, 0),
);
const reportRegistrations = computed(() =>
    props.registrations_chart.reduce((sum, point) => sum + point.count, 0),
);
const averageOrderValue = computed(() =>
    reportOrders.value ? reportRevenue.value / reportOrders.value : 0,
);
const strongestRevenueMonth = computed(() => {
    const strongest = props.revenue_chart.reduce<RevenuePoint | null>(
        (best, point) => (!best || point.amount > best.amount ? point : best),
        null,
    );

    return strongest && strongest.amount > 0 ? strongest : null;
});

const LINE_W = 760;
const LINE_H = 260;
const LP = { top: 22, right: 24, bottom: 38, left: 64 };
const revenueMax = computed(
    () =>
        Math.max(1, ...props.revenue_chart.map((point) => point.amount)) * 1.12,
);
const revenuePoints = computed(() => {
    const count = props.revenue_chart.length;
    if (!count) return [];

    return props.revenue_chart.map((point, index) => ({
        ...point,
        x:
            LP.left +
            (index / Math.max(count - 1, 1)) * (LINE_W - LP.left - LP.right),
        y:
            LINE_H -
            LP.bottom -
            (point.amount / revenueMax.value) * (LINE_H - LP.top - LP.bottom),
    }));
});
const revenuePath = computed(() =>
    revenuePoints.value
        .map(
            (point, index) =>
                `${index === 0 ? 'M' : 'L'}${point.x.toFixed(1)},${point.y.toFixed(1)}`,
        )
        .join(' '),
);
const revenueArea = computed(() => {
    if (!revenuePoints.value.length) return '';
    const baseline = LINE_H - LP.bottom;
    const first = revenuePoints.value[0];
    const last = revenuePoints.value[revenuePoints.value.length - 1];
    return `${revenuePath.value} L${last.x},${baseline} L${first.x},${baseline} Z`;
});
const revenueTicks = computed(() =>
    [0, 0.25, 0.5, 0.75, 1].map((ratio) => ({
        value: revenueMax.value * ratio,
        y: LINE_H - LP.bottom - ratio * (LINE_H - LP.top - LP.bottom),
    })),
);

const BAR_W = 600;
const BAR_H = 210;
const BP = { top: 18, right: 18, bottom: 36, left: 44 };
const ordersMax = computed(
    () => Math.max(1, ...props.orders_chart.map((point) => point.count)) * 1.12,
);
const orderBars = computed(() => {
    const count = props.orders_chart.length || 1;
    const slotWidth = (BAR_W - BP.left - BP.right) / count;
    const width = Math.min(28, slotWidth * 0.58);

    return props.orders_chart.map((point, index) => {
        const height =
            (point.count / ordersMax.value) * (BAR_H - BP.top - BP.bottom);
        return {
            ...point,
            x: BP.left + index * slotWidth + (slotWidth - width) / 2,
            y: BAR_H - BP.bottom - height,
            width,
            height,
        };
    });
});

const REG_W = 600;
const REG_H = 210;
const RP = { top: 18, right: 18, bottom: 36, left: 44 };
const registrationsMax = computed(
    () =>
        Math.max(1, ...props.registrations_chart.map((point) => point.count)) *
        1.12,
);
const registrationPoints = computed(() => {
    const count = props.registrations_chart.length;
    if (!count) return [];

    return props.registrations_chart.map((point, index) => ({
        ...point,
        x:
            RP.left +
            (index / Math.max(count - 1, 1)) * (REG_W - RP.left - RP.right),
        y:
            REG_H -
            RP.bottom -
            (point.count / registrationsMax.value) *
                (REG_H - RP.top - RP.bottom),
    }));
});
const registrationsPath = computed(() =>
    registrationPoints.value
        .map(
            (point, index) =>
                `${index === 0 ? 'M' : 'L'}${point.x.toFixed(1)},${point.y.toFixed(1)}`,
        )
        .join(' '),
);
const registrationsArea = computed(() => {
    if (!registrationPoints.value.length) return '';
    const baseline = REG_H - RP.bottom;
    const first = registrationPoints.value[0];
    const last = registrationPoints.value[registrationPoints.value.length - 1];
    return `${registrationsPath.value} L${last.x},${baseline} L${first.x},${baseline} Z`;
});
</script>

<template>
    <Head title="Analytics Overview" />

    <AdminLayout :breadcrumbs="breadcrumbs" title="Analytics">
        <div class="mx-auto max-w-[1500px] space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="flex flex-wrap items-center justify-between gap-4 px-1 py-2"
            >
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">
                        Analytics Overview
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Platform performance, subscription revenue, and shop
                        growth
                    </p>
                </div>
                <div
                    class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white shadow-sm"
                >
                    Last 12 months
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                <Card v-for="item in statCards" :key="item.label">
                    <CardContent class="p-4">
                        <p
                            class="mb-3 text-xs font-medium text-muted-foreground"
                        >
                            {{ item.label }}
                        </p>
                        <p class="text-2xl font-bold">
                            {{ item.value }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <section class="grid gap-4 xl:grid-cols-[minmax(0,2fr)_340px]">
                <article class="rounded-xl border bg-card p-5 shadow-sm sm:p-6">
                    <div
                        class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div>
                            <p
                                class="text-xs font-semibold tracking-wider text-emerald-600 uppercase dark:text-emerald-400"
                            >
                                Revenue trend
                            </p>
                            <h2 class="mt-1 text-lg font-semibold">
                                Collected subscription revenue
                            </h2>
                            <p class="mt-1 text-xs text-muted-foreground">
                                Successful payments and paid legacy
                                subscriptions
                            </p>
                        </div>
                        <div class="sm:text-right">
                            <p class="text-xs text-muted-foreground">
                                12-month total
                            </p>
                            <p
                                class="text-xl font-bold text-emerald-600 dark:text-emerald-400"
                            >
                                {{ formatCurrency(reportRevenue) }}
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <svg
                            :viewBox="`0 0 ${LINE_W} ${LINE_H}`"
                            class="min-w-[640px]"
                            role="img"
                            aria-label="Revenue for the last 12 months"
                        >
                            <defs>
                                <linearGradient
                                    id="analyticsRevenue"
                                    x1="0"
                                    y1="0"
                                    x2="0"
                                    y2="1"
                                >
                                    <stop
                                        offset="0%"
                                        stop-color="#10b981"
                                        stop-opacity="0.28"
                                    />
                                    <stop
                                        offset="100%"
                                        stop-color="#10b981"
                                        stop-opacity="0"
                                    />
                                </linearGradient>
                            </defs>
                            <g v-for="tick in revenueTicks" :key="tick.y">
                                <line
                                    :x1="LP.left"
                                    :x2="LINE_W - LP.right"
                                    :y1="tick.y"
                                    :y2="tick.y"
                                    stroke="currentColor"
                                    stroke-opacity="0.08"
                                />
                                <text
                                    :x="LP.left - 8"
                                    :y="tick.y + 4"
                                    text-anchor="end"
                                    font-size="10"
                                    class="fill-muted-foreground"
                                >
                                    {{ formatCompactCurrency(tick.value) }}
                                </text>
                            </g>
                            <path
                                :d="revenueArea"
                                fill="url(#analyticsRevenue)"
                            />
                            <path
                                :d="revenuePath"
                                fill="none"
                                stroke="#10b981"
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                            <g
                                v-for="point in revenuePoints"
                                :key="point.month"
                            >
                                <circle
                                    :cx="point.x"
                                    :cy="point.y"
                                    r="4"
                                    fill="#10b981"
                                    stroke="white"
                                    stroke-width="2"
                                >
                                    <title>
                                        {{ point.month }}:
                                        {{ formatCurrency(point.amount) }}
                                    </title>
                                </circle>
                                <text
                                    :x="point.x"
                                    :y="LINE_H - LP.bottom + 20"
                                    text-anchor="middle"
                                    font-size="10"
                                    class="fill-muted-foreground"
                                >
                                    {{ point.month }}
                                </text>
                            </g>
                        </svg>
                    </div>
                </article>

                <aside class="rounded-xl border bg-card p-5 shadow-sm sm:p-6">
                    <div class="flex items-center gap-3">
                        <div
                            class="rounded-lg bg-blue-500/10 p-2 text-blue-600 dark:text-blue-400"
                        >
                            <TrendingUp class="h-5 w-5" />
                        </div>
                        <div>
                            <h2 class="font-semibold">Business pulse</h2>
                            <p class="text-xs text-muted-foreground">
                                Key signals from this period
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 divide-y border-y">
                        <div
                            class="flex items-center justify-between gap-4 py-4"
                        >
                            <div class="flex items-center gap-3">
                                <ReceiptText class="h-4 w-4 text-emerald-500" />
                                <span class="text-sm text-muted-foreground"
                                    >Average order</span
                                >
                            </div>
                            <span class="text-sm font-semibold">{{
                                formatCurrency(averageOrderValue)
                            }}</span>
                        </div>
                        <div
                            class="flex items-center justify-between gap-4 py-4"
                        >
                            <div class="flex items-center gap-3">
                                <ShoppingBag class="h-4 w-4 text-amber-500" />
                                <span class="text-sm text-muted-foreground"
                                    >Paid orders</span
                                >
                            </div>
                            <span class="text-sm font-semibold">{{
                                reportOrders.toLocaleString()
                            }}</span>
                        </div>
                        <div
                            class="flex items-center justify-between gap-4 py-4"
                        >
                            <div class="flex items-center gap-3">
                                <UserPlus class="h-4 w-4 text-violet-500" />
                                <span class="text-sm text-muted-foreground"
                                    >New customers</span
                                >
                            </div>
                            <span class="text-sm font-semibold">{{
                                reportRegistrations.toLocaleString()
                            }}</span>
                        </div>
                        <div
                            class="flex items-center justify-between gap-4 py-4"
                        >
                            <div class="flex items-center gap-3">
                                <TrendingUp class="h-4 w-4 text-blue-500" />
                                <span class="text-sm text-muted-foreground"
                                    >Strongest month</span
                                >
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold">
                                    {{ strongestRevenueMonth?.month ?? '—' }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{
                                        formatCompactCurrency(
                                            strongestRevenueMonth?.amount ?? 0,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </aside>
            </section>

            <section class="grid gap-4 lg:grid-cols-2">
                <article class="rounded-xl border bg-card p-5 shadow-sm sm:p-6">
                    <div class="mb-4 flex items-start justify-between gap-4">
                        <div>
                            <h2 class="font-semibold">Paid orders</h2>
                            <p class="mt-1 text-xs text-muted-foreground">
                                Monthly completed subscription volume
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-amber-500/10 p-2 text-amber-600 dark:text-amber-400"
                        >
                            <BarChart3 class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <svg
                            :viewBox="`0 0 ${BAR_W} ${BAR_H}`"
                            class="min-w-[520px]"
                            role="img"
                            aria-label="Paid orders for the last 12 months"
                        >
                            <line
                                v-for="ratio in [0, 0.25, 0.5, 0.75, 1]"
                                :key="ratio"
                                :x1="BP.left"
                                :x2="BAR_W - BP.right"
                                :y1="
                                    BAR_H -
                                    BP.bottom -
                                    ratio * (BAR_H - BP.top - BP.bottom)
                                "
                                :y2="
                                    BAR_H -
                                    BP.bottom -
                                    ratio * (BAR_H - BP.top - BP.bottom)
                                "
                                stroke="currentColor"
                                stroke-opacity="0.08"
                            />
                            <g v-for="bar in orderBars" :key="bar.month">
                                <rect
                                    :x="bar.x"
                                    :y="bar.y"
                                    :width="bar.width"
                                    :height="bar.height"
                                    rx="4"
                                    fill="#f59e0b"
                                    opacity="0.82"
                                >
                                    <title>
                                        {{ bar.month }}: {{ bar.count }} paid
                                        orders
                                    </title>
                                </rect>
                                <text
                                    :x="bar.x + bar.width / 2"
                                    :y="BAR_H - BP.bottom + 18"
                                    text-anchor="middle"
                                    font-size="9"
                                    class="fill-muted-foreground"
                                >
                                    {{ bar.month }}
                                </text>
                            </g>
                        </svg>
                    </div>
                </article>

                <article class="rounded-xl border bg-card p-5 shadow-sm sm:p-6">
                    <div class="mb-4 flex items-start justify-between gap-4">
                        <div>
                            <h2 class="font-semibold">Customer growth</h2>
                            <p class="mt-1 text-xs text-muted-foreground">
                                New customer registrations by month
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-violet-500/10 p-2 text-violet-600 dark:text-violet-400"
                        >
                            <UserPlus class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <svg
                            :viewBox="`0 0 ${REG_W} ${REG_H}`"
                            class="min-w-[520px]"
                            role="img"
                            aria-label="Customer registrations for the last 12 months"
                        >
                            <defs>
                                <linearGradient
                                    id="analyticsRegistrations"
                                    x1="0"
                                    y1="0"
                                    x2="0"
                                    y2="1"
                                >
                                    <stop
                                        offset="0%"
                                        stop-color="#8b5cf6"
                                        stop-opacity="0.28"
                                    />
                                    <stop
                                        offset="100%"
                                        stop-color="#8b5cf6"
                                        stop-opacity="0"
                                    />
                                </linearGradient>
                            </defs>
                            <line
                                v-for="ratio in [0, 0.25, 0.5, 0.75, 1]"
                                :key="ratio"
                                :x1="RP.left"
                                :x2="REG_W - RP.right"
                                :y1="
                                    REG_H -
                                    RP.bottom -
                                    ratio * (REG_H - RP.top - RP.bottom)
                                "
                                :y2="
                                    REG_H -
                                    RP.bottom -
                                    ratio * (REG_H - RP.top - RP.bottom)
                                "
                                stroke="currentColor"
                                stroke-opacity="0.08"
                            />
                            <path
                                :d="registrationsArea"
                                fill="url(#analyticsRegistrations)"
                            />
                            <path
                                :d="registrationsPath"
                                fill="none"
                                stroke="#8b5cf6"
                                stroke-width="2.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                            <g
                                v-for="point in registrationPoints"
                                :key="point.month"
                            >
                                <circle
                                    :cx="point.x"
                                    :cy="point.y"
                                    r="3.5"
                                    fill="#8b5cf6"
                                    stroke="white"
                                    stroke-width="1.5"
                                >
                                    <title>
                                        {{ point.month }}: {{ point.count }} new
                                        customers
                                    </title>
                                </circle>
                                <text
                                    :x="point.x"
                                    :y="REG_H - RP.bottom + 18"
                                    text-anchor="middle"
                                    font-size="9"
                                    class="fill-muted-foreground"
                                >
                                    {{ point.month }}
                                </text>
                            </g>
                        </svg>
                    </div>
                </article>
            </section>

            <section class="rounded-xl border bg-card shadow-sm">
                <div
                    class="flex flex-col gap-2 border-b px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                >
                    <div>
                        <h2 class="font-semibold">Top-performing shops</h2>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Ranked by successfully collected subscription
                            revenue
                        </p>
                    </div>
                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-full bg-blue-500/10 px-3 py-1.5 text-xs font-medium text-blue-600 dark:text-blue-400"
                    >
                        <Store class="h-3.5 w-3.5" />
                        Top {{ top_shops.length }} shops
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-sm">
                        <thead
                            class="bg-muted/60 text-xs text-muted-foreground"
                        >
                            <tr>
                                <th
                                    class="w-20 px-6 py-3 text-left font-medium"
                                >
                                    Rank
                                </th>
                                <th class="px-4 py-3 text-left font-medium">
                                    Shop
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    Revenue
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    Paid orders
                                </th>
                                <th class="px-6 py-3 text-right font-medium">
                                    Monthly growth
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="shop in top_shops"
                                :key="shop.rank"
                                class="transition-colors hover:bg-muted/40"
                            >
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold',
                                            shop.rank === 1
                                                ? 'bg-amber-400/20 text-amber-600 dark:text-amber-400'
                                                : shop.rank === 2
                                                  ? 'bg-slate-400/20 text-slate-600 dark:text-slate-300'
                                                  : shop.rank === 3
                                                    ? 'bg-orange-400/20 text-orange-600 dark:text-orange-400'
                                                    : 'bg-muted text-muted-foreground',
                                        ]"
                                    >
                                        {{ shop.rank }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-medium">{{ shop.name }}</p>
                                    <p
                                        class="mt-0.5 text-xs text-muted-foreground"
                                    >
                                        {{ shop.location }}
                                    </p>
                                </td>
                                <td class="px-4 py-4 text-right font-semibold">
                                    {{ formatCurrency(shop.revenue) }}
                                </td>
                                <td
                                    class="px-4 py-4 text-right text-muted-foreground"
                                >
                                    {{ shop.orders.toLocaleString() }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span
                                        :class="[
                                            'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold',
                                            shop.growth >= 0
                                                ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                                : 'bg-red-500/10 text-red-600 dark:text-red-400',
                                        ]"
                                    >
                                        <ArrowUpRight
                                            v-if="shop.growth >= 0"
                                            class="h-3.5 w-3.5"
                                        />
                                        <ArrowDownRight
                                            v-else
                                            class="h-3.5 w-3.5"
                                        />
                                        {{ Math.abs(shop.growth) }}%
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!top_shops.length">
                                <td
                                    colspan="5"
                                    class="px-6 py-12 text-center text-sm text-muted-foreground"
                                >
                                    No paid shop activity is available for this
                                    period.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>
