<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import CheckoutConfirm from '@/pages/shop/CheckoutConfirm.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import {
    AlertTriangle,
    CheckCircle2,
    Clock,
    CreditCard,
    Lightbulb,
    Loader2,
    Mail,
    PackageX,
    RefreshCcw,
    RotateCcw,
    ShieldAlert,
    XCircle,
} from 'lucide-vue-next';
import { computed, onMounted, reactive, ref } from 'vue';

// ─── Types ────────────────────────────────────────────────────────────────────

interface MovementPoint {
    month: string;
    stock_in: number;
    stock_out: number;
}
interface CategoryPoint {
    label: string;
    count: number;
}
interface LowStockItem {
    name: string;
    sku: string;
    quantity: number;
    min_stock: number;
    status: string;
    category: string;
}
interface RecentMovement {
    item: string;
    sku: string;
    type: string;
    quantity: number;
    before: number;
    after: number;
    by: string;
    notes: string | null;
    date: string;
}

interface MonthlyOrderPoint {
    month: string;
    month_key: string;
    total_orders: number;
    completed: number;
    revenue: number;
}
interface ServicePop {
    name: string;
    order_count: number;
    revenue: number;
}
interface OrderStats {
    total: number;
    completed: number;
    pending: number;
    in_progress: number;
    completion_rate: number;
    total_revenue: number;
    avg_order_value: number;
    this_month: number;
    last_month: number;
    orders_change: number;
}
interface DailySummary {
    period?: Period;
    revenue_today: number;
    revenue_change: number | null;
    orders_today: number;
    orders_vs_yesterday: number;
    open_orders: number;
    overdue_orders: number;
    unpaid_balance: number;
    unpaid_orders: number;
    daily_revenue: { label: string; revenue: number }[];
    due_today: {
        order_number: string;
        customer: string;
        service: string | null;
        due_at: string;
        overdue: boolean;
    }[];
    busiest_day: { day: string; share: number } | null;
    total_orders: number;
}
type Period = 'today' | '7d' | 'month';
interface AttentionCard {
    key: string;
    tone: 'urgent' | 'heads_up' | 'tip' | 'ok';
    title: string;
    message: string;
    rule?: string;
    href?: string;
    action?: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/shop/dashboard' },
];

// ─── Props ────────────────────────────────────────────────────────────────────

// Component props stay reactive across Inertia visits; destructuring usePage()
// would freeze them at the first page load.
const props = defineProps<{
    auth: { user: any };
    modules: any[];
    order?: {
        status: string;
        shop_name: string;
        subscription_plan: string | null;
        expires_at: string | null;
        modules: { name: string; price: number }[];
        total_price: number;
        is_trial: boolean;
        trial_days_left: number | null;
    };
    pending_order?: {
        plan_name: string;
        billing_months: number;
        total_price: number;
        created_at: string;
    } | null;
    approved_order?: {
        plan_name: string;
        total_price: number;
    } | null;
    rejected_order?: {
        id: number;
        shop_name: string;
        rejection_reason: string | null;
        total_price: number;
        email: string;
        resubmissions_used: number;
        max_resubmissions: number;
        resubmissions_remaining: number;
        can_resubmit: boolean;
    } | null;
    expired_order?: {
        plan_name: string | null;
        expires_at: string | null;
        is_trial: boolean;
    } | null;
    has_used_trial?: boolean;
    stats?: {
        employees: { total: number; active: number; inactive: number };
        inventory: { total: number; low_stock: number; out_of_stock: number };
        low_stock_alerts: number;
        movements: { this_month: number; change: number };
    };
    shop?: {
        shop_name?: string;
        phone?: string;
        block_street?: string;
        municipality?: string;
        barangay?: string;
        postal_code?: string;
        status?: string;
    } | null;
    movement_chart?: MovementPoint[];
    category_breakdown?: CategoryPoint[];
    low_stock_items?: LowStockItem[];
    recent_movements?: RecentMovement[];
    order_stats?: OrderStats;
    daily?: DailySummary;
    period?: Period;
    monthly_orders?: MonthlyOrderPoint[];
    service_popularity?: ServicePop[];
    agreement: {
        public_id: string;
        title: string;
        version: string;
        content: string;
        effective_at: string;
        accepted: boolean;
        acceptance?: {
            business_name: string;
            signer_name: string;
            signer_role: string;
            accepted_at: string;
            signature_url?: string | null;
        } | null;
    };
}>();

const user = props.auth.user;
const isPaid = computed(() =>
    ['paid', 'approved'].includes(props.order?.status ?? ''),
);
const isApproved = computed(
    () =>
        ['approved', 'paid'].includes(props.order?.status ?? '') &&
        (props.shop?.status ?? 'active') !== 'disabled',
);
const isPending = computed(() => !!props.pending_order);
const isApprovedNonTrial = computed(() => !!props.approved_order);
const isRejected = computed(() => !!props.rejected_order);
const canResubmit = computed(
    () => props.rejected_order?.can_resubmit !== false,
);
const isExpired = computed(() => !!props.expired_order);
const showOrder = ref(false);
const showTrialConfirm = ref(false);
const isRefreshing = ref(false);

// ─── Payment form (approved non-trial) ───────────────────────────────────────

const PAYMENT_METHODS = [
    {
        key: 'gcash',
        label: 'GCash',
        icon: 'https://upload.wikimedia.org/wikipedia/commons/5/52/GCash_logo.svg',
        note: 'Pay via GCash mobile wallet',
        tag: 'Most Popular',
    },
    {
        key: 'maya',
        label: 'Maya',
        icon: 'https://upload.wikimedia.org/wikipedia/commons/e/e6/Maya_logo.svg',
        note: 'Pay via Maya (PayMaya) wallet',
    },
    {
        key: 'card',
        label: 'Credit / Debit Card',
        icon: 'https://upload.wikimedia.org/wikipedia/commons/9/98/Visa_Inc._logo_%282005%E2%80%932014%29.svg',
        note: 'Visa, Mastercard, JCB',
    },
    {
        key: 'grab_pay',
        label: 'GrabPay',
        icon: 'https://upload.wikimedia.org/wikipedia/commons/f/f6/Grab_Logo.svg',
        note: 'Pay via GrabPay wallet',
    },
    {
        key: 'dob',
        label: 'Online Banking',
        icon: 'https://upload.wikimedia.org/wikipedia/commons/4/49/BDO_Unibank_%28logo%29.svg',
        note: 'BDO, BPI, UnionBank, Metrobank',
    },
    {
        key: 'billease',
        label: 'BillEase',
        icon: 'https://logobase.net/wp-content/uploads/2025/08/BillEase-Logo-1.webp',
        note: 'Buy now, pay later',
        tag: 'Installment',
    },
];
const payForm = reactive({ payment_method: '', submitting: false });

// ─── Needs attention + KPIs ───────────────────────────────────────────────────

const PERIOD_OPTIONS: { value: Period; label: string }[] = [
    { value: 'today', label: 'Today' },
    { value: '7d', label: '7 days' },
    { value: 'month', label: 'This month' },
];
const INSIGHTS_MIN_ORDERS = 20;
// The selected period always mirrors what the server computed the figures for.
const period = computed<Period>(
    () => props.daily?.period ?? props.period ?? 'today',
);
// Dismissed cards stay hidden for the rest of the day, per browser.
const DISMISS_STORAGE_KEY = `shop-dashboard-dismissed:${props.auth.user.id}`;
const todayKey = () => new Date().toLocaleDateString('en-CA');

function loadDismissed(): string[] {
    try {
        const saved = JSON.parse(
            localStorage.getItem(DISMISS_STORAGE_KEY) ?? 'null',
        );
        return saved?.date === todayKey() && Array.isArray(saved.keys)
            ? saved.keys
            : [];
    } catch {
        return [];
    }
}

const dismissed = reactive(new Set<string>(loadDismissed()));

function dismissCard(key: string) {
    dismissed.add(key);
    try {
        localStorage.setItem(
            DISMISS_STORAGE_KEY,
            JSON.stringify({ date: todayKey(), keys: [...dismissed] }),
        );
    } catch {
        // Storage unavailable: the card stays hidden until the next reload.
    }
}
const greeting = computed(() => {
    const hour = new Date().getHours();
    return hour < 12
        ? 'Good morning'
        : hour < 18
          ? 'Good afternoon'
          : 'Good evening';
});

function setPeriod(value: Period) {
    if (value === period.value) return;
    router.get(
        '/shop/dashboard',
        { period: value },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
}

const ATTENTION_TONES = {
    urgent: {
        label: 'Urgent',
        icon: AlertTriangle,
        badge: 'bg-red-100 text-red-700',
    },
    heads_up: {
        label: 'Heads up',
        icon: PackageX,
        badge: 'bg-amber-100 text-amber-700',
    },
    tip: {
        label: 'Tip',
        icon: Lightbulb,
        badge: 'bg-blue-100 text-blue-700',
    },
    ok: {
        label: 'All clear',
        icon: CheckCircle2,
        badge: 'bg-emerald-100 text-emerald-700',
    },
} as const;

const attentionCards = computed<AttentionCard[]>(() => {
    const daily = props.daily;
    const inventory = props.stats?.inventory;
    const cards: AttentionCard[] = [];

    if (daily && daily.overdue_orders > 0) {
        cards.push({
            key: 'overdue',
            tone: 'urgent',
            title: `${daily.overdue_orders} order${daily.overdue_orders === 1 ? ' is' : 's are'} overdue`,
            message: `${daily.overdue_orders} of ${daily.open_orders} open orders are past their due time.`,
            rule: 'open order past due time',
            href: '/shop/operations/orders',
            action: 'View orders',
        });
    }

    if (daily && daily.unpaid_balance > 0) {
        cards.push({
            key: 'unpaid',
            tone: 'heads_up',
            title: `₱${formatPeso(daily.unpaid_balance)} not yet collected`,
            message: `${daily.unpaid_orders} order${daily.unpaid_orders === 1 ? ' still has' : 's still have'} a balance.`,
            rule: 'order with balance due',
            href: '/shop/operations/orders?payment_status=unpaid',
            action: 'View unpaid',
        });
    }

    const stockIssues =
        (inventory?.out_of_stock ?? 0) + (inventory?.low_stock ?? 0);
    if (stockIssues > 0) {
        cards.push({
            key: 'stock',
            tone: 'heads_up',
            title: `${stockIssues} suppl${stockIssues === 1 ? 'y needs' : 'ies need'} restocking`,
            message: `${inventory?.out_of_stock ?? 0} out of stock and ${inventory?.low_stock ?? 0} below minimum level.`,
            rule: 'stock at or below minimum level',
            href: '/shop/inventory',
            action: 'Reorder',
        });
    }

    if (daily?.busiest_day) {
        cards.push({
            key: 'busiest',
            tone: 'tip',
            title: `${daily.busiest_day.day} are your busiest`,
            message: `${daily.busiest_day.share}% of orders in the last 8 weeks came on ${daily.busiest_day.day}. Plan staff around it.`,
            rule: 'orders grouped by day of week',
        });
    }

    if (cards.length === 0) {
        cards.push({
            key: 'clear',
            tone: 'ok',
            title: 'Nothing needs attention',
            message:
                'No overdue orders, unpaid balances, or stock issues right now.',
        });
    }

    return cards.slice(0, 4);
});

const visibleAttentionCards = computed(() =>
    attentionCards.value.filter((c) => !dismissed.has(c.key)),
);

const PERIOD_LABELS: Record<Period, string> = {
    today: 'today',
    '7d': 'last 7 days',
    month: 'this month',
};
const PREVIOUS_LABELS: Record<Period, string> = {
    today: 'yesterday',
    '7d': 'previous 7 days',
    month: 'previous period',
};

const kpiCards = computed(() => {
    const daily = props.daily;
    const change = daily?.revenue_change ?? null;
    const diff = daily?.orders_vs_yesterday ?? 0;
    // Label the figures with the period the server computed them for.
    const shown = period.value;

    return [
        {
            label: `Revenue ${PERIOD_LABELS[shown]}`,
            value: `₱${formatPeso(daily?.revenue_today ?? 0)}`,
            hint:
                change === null
                    ? 'No earlier payments to compare'
                    : `${change >= 0 ? '↑' : '↓'} ${Math.abs(change)}% vs ${PREVIOUS_LABELS[shown]}`,
            hintClass:
                change === null
                    ? undefined
                    : change >= 0
                      ? 'text-emerald-600'
                      : 'text-red-600',
        },
        {
            label: `Orders ${PERIOD_LABELS[shown]}`,
            value: String(daily?.orders_today ?? 0),
            hint:
                diff === 0
                    ? `Same as ${PREVIOUS_LABELS[shown]}`
                    : `${Math.abs(diff)} ${diff > 0 ? 'more' : 'fewer'} than ${PREVIOUS_LABELS[shown]}`,
        },
        {
            label: 'Open orders',
            value: String(daily?.open_orders ?? 0),
            hint: `${daily?.overdue_orders ?? 0} overdue`,
            hintClass:
                (daily?.overdue_orders ?? 0) > 0 ? 'text-red-600' : undefined,
        },
        {
            label: 'Unpaid balance',
            value: `₱${formatPeso(daily?.unpaid_balance ?? 0)}`,
            hint: `${daily?.unpaid_orders ?? 0} order${daily?.unpaid_orders === 1 ? '' : 's'}`,
        },
    ];
});

const dailyRevenue = computed(() => props.daily?.daily_revenue ?? []);
const maxDailyRevenue = computed(() =>
    Math.max(...dailyRevenue.value.map((d) => d.revenue), 1),
);

// ─── Chart refs────────────────────────────────────────────────────────────────

const ordersChartRef = ref<HTMLCanvasElement | null>(null);
const revenueChartRef = ref<HTMLCanvasElement | null>(null);
const serviceChartRef = ref<HTMLCanvasElement | null>(null);
const movementChartRef = ref<HTMLCanvasElement | null>(null);
const categoryChartRef = ref<HTMLCanvasElement | null>(null);

onMounted(() => {
    if (!isPaid.value) return;

    const orders12 = props.monthly_orders ?? [];
    const services = props.service_popularity ?? [];
    const movData = props.movement_chart ?? [];
    const catData = props.category_breakdown ?? [];

    // Peak threshold — months >= 75% of max are peak (orange)
    const maxOrders = Math.max(...orders12.map((d) => d.total_orders), 1);
    const peakThreshold = maxOrders * 0.75;

    /* ── Monthly Orders Bar (peak highlighted) ── */
    if (ordersChartRef.value && orders12.length) {
        new Chart(ordersChartRef.value, {
            type: 'bar',
            data: {
                labels: orders12.map((d) => d.month),
                datasets: [
                    {
                        label: 'Orders',
                        data: orders12.map((d) => d.total_orders),
                        backgroundColor: orders12.map((d) =>
                            d.total_orders >= peakThreshold &&
                            d.total_orders > 0
                                ? 'rgba(234,88,12,0.85)'
                                : 'rgba(99,102,241,0.70)',
                        ),
                        borderRadius: 5,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            afterLabel: (ctx: any) => {
                                const v = orders12[ctx.dataIndex];
                                return v.total_orders >= peakThreshold &&
                                    v.total_orders > 0
                                    ? '🔥 Peak Month'
                                    : '';
                            },
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { size: 11 } },
                        grid: { color: 'rgba(0,0,0,0.05)' },
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } },
                    },
                },
            },
        });
    }

    /* ── Revenue Trend Line ── */
    if (revenueChartRef.value) {
        new Chart(revenueChartRef.value, {
            type: 'line',
            data: {
                labels: orders12.map((d) => d.month),
                datasets: [
                    {
                        label: 'Revenue (₱)',
                        data: orders12.map((d) => d.revenue),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.10)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#10b981',
                        pointHoverRadius: 6,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx: any) =>
                                `₱${Number(ctx.raw).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`,
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { size: 10 },
                            callback: (v: any) =>
                                `₱${Number(v).toLocaleString('en-PH')}`,
                        },
                        grid: { color: 'rgba(0,0,0,0.05)' },
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } },
                    },
                },
            },
        });
    }

    /* ── Service Popularity Horizontal Bar ── */
    if (serviceChartRef.value && services.length) {
        const palette = [
            '#6366f1',
            '#10b981',
            '#f59e0b',
            '#ef4444',
            '#3b82f6',
            '#8b5cf6',
        ];
        new Chart(serviceChartRef.value, {
            type: 'bar',
            data: {
                labels: services.map((s) => s.name),
                datasets: [
                    {
                        label: 'Orders',
                        data: services.map((s) => s.order_count),
                        backgroundColor: services.map(
                            (_, i) => palette[i % palette.length],
                        ),
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { size: 10 } },
                        grid: { color: 'rgba(0,0,0,0.05)' },
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } },
                    },
                },
            },
        });
    }

    /* ── Inventory Movement Bar ── */
    if (movementChartRef.value && movData.length) {
        new Chart(movementChartRef.value, {
            type: 'bar',
            data: {
                labels: movData.map((d) => d.month),
                datasets: [
                    {
                        label: 'Stock In',
                        data: movData.map((d) => d.stock_in),
                        backgroundColor: 'rgba(16,185,129,0.75)',
                        borderRadius: 4,
                    },
                    {
                        label: 'Stock Out',
                        data: movData.map((d) => d.stock_out),
                        backgroundColor: 'rgba(239,68,68,0.75)',
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { size: 11 },
                            boxWidth: 12,
                            boxHeight: 12,
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: { font: { size: 11 } },
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } },
                    },
                },
            },
        });
    }

    /* ── Category Doughnut ── */
    if (categoryChartRef.value && catData.length) {
        const colors = [
            '#6366f1',
            '#10b981',
            '#f59e0b',
            '#ef4444',
            '#3b82f6',
            '#8b5cf6',
            '#ec4899',
            '#14b8a6',
        ];
        new Chart(categoryChartRef.value, {
            type: 'doughnut',
            data: {
                labels: catData.map((d) => d.label),
                datasets: [
                    {
                        data: catData.map((d) => d.count),
                        backgroundColor: colors.slice(0, catData.length),
                        borderWidth: 2,
                        borderColor: '#fff',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 12,
                            font: { size: 11 },
                            boxWidth: 12,
                            boxHeight: 12,
                        },
                    },
                },
                cutout: '60%',
            },
        });
    }
});

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatPeso(v: number) {
    return v.toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function refreshDashboard() {
    if (isRefreshing.value) return;

    isRefreshing.value = true;
    router.reload({
        data: { period: period.value },
        only: [
            'period',
            'stats',
            'movement_chart',
            'category_breakdown',
            'low_stock_items',
            'recent_movements',
            'order_stats',
            'monthly_orders',
            'service_popularity',
            'daily',
        ],
        preserveState: false,
        onFinish: () => {
            isRefreshing.value = false;
        },
    });
}

function proceedToPay() {
    if (!payForm.payment_method || payForm.submitting) return;
    payForm.submitting = true;

    // Use a real form POST so the server's external redirect (PayMongo) is followed correctly.
    // Inertia's router.post intercepts redirects via XHR and cannot navigate to external URLs.
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/shop/payment/pay';

    const csrf =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';
    const csrfField = document.createElement('input');
    csrfField.type = 'hidden';
    csrfField.name = '_token';
    csrfField.value = csrf;
    form.appendChild(csrfField);

    const pmField = document.createElement('input');
    pmField.type = 'hidden';
    pmField.name = 'payment_method';
    pmField.value = payForm.payment_method;
    form.appendChild(pmField);

    document.body.appendChild(form);
    form.submit();
}
</script>

<template>
    <Head title="Shop Dashboard" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Dashboard">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <!-- ══════════════ DISABLED ══════════════ -->
            <template v-if="props.shop?.status === 'disabled'">
                <div
                    class="flex min-h-[60vh] flex-1 items-center justify-center"
                >
                    <div class="max-w-md text-center">
                        <div
                            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100"
                        >
                            <ShieldAlert class="h-8 w-8 text-red-500" />
                        </div>
                        <h2
                            class="mb-2 text-xl font-semibold text-gray-900 dark:text-white"
                        >
                            Shop Disabled
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            Your shop has been disabled. Please contact support
                            for assistance.
                        </p>
                    </div>
                </div>
            </template>

            <!-- ══════════════ PENDING REVIEW ══════════════ -->
            <template v-else-if="isPending">
                <div
                    class="flex min-h-[60vh] flex-1 items-center justify-center px-4"
                >
                    <div class="w-full max-w-md text-center">
                        <div
                            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-blue-100"
                        >
                            <Clock
                                class="h-8 w-8 animate-pulse text-blue-500"
                            />
                        </div>
                        <h2
                            class="mb-1 text-xl font-semibold text-gray-900 dark:text-white"
                        >
                            Order Under Review
                        </h2>
                        <p class="mb-6 text-sm text-muted-foreground">
                            Your
                            <span class="font-semibold text-foreground">{{
                                props.pending_order?.plan_name
                            }}</span>
                            plan order is being reviewed by our admin team.
                            You'll be notified once it's approved.
                        </p>
                        <div
                            class="space-y-2 rounded-xl border border-blue-200 bg-blue-50 p-4 text-left dark:bg-blue-950/30"
                        >
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Plan</span>
                                <span class="font-medium">{{
                                    props.pending_order?.plan_name
                                }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground"
                                    >Duration</span
                                >
                                <span class="font-medium"
                                    >{{
                                        props.pending_order?.billing_months
                                    }}
                                    month{{
                                        (props.pending_order?.billing_months ??
                                            1) > 1
                                            ? 's'
                                            : ''
                                    }}</span
                                >
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Total</span>
                                <span class="font-semibold text-blue-700"
                                    >₱{{
                                        Number(
                                            props.pending_order?.total_price ??
                                                0,
                                        ).toLocaleString('en-PH', {
                                            minimumFractionDigits: 2,
                                        })
                                    }}</span
                                >
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground"
                                    >Submitted</span
                                >
                                <span class="font-medium">{{
                                    props.pending_order?.created_at
                                }}</span>
                            </div>
                        </div>
                        <p class="mt-4 text-xs text-muted-foreground">
                            No payment is collected until your order is
                            approved.
                        </p>
                    </div>
                </div>
            </template>

            <!-- ══════════════ APPROVED — PAY NOW ══════════════ -->
            <template v-else-if="isApprovedNonTrial">
                <div
                    class="flex min-h-[60vh] flex-1 items-center justify-center px-4"
                >
                    <div class="w-full max-w-md">
                        <div class="mb-6 text-center">
                            <div
                                class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100"
                            >
                                <CheckCircle2
                                    class="h-8 w-8 text-emerald-500"
                                />
                            </div>
                            <h2
                                class="text-xl font-semibold text-gray-900 dark:text-white"
                            >
                                Order Approved!
                            </h2>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Complete your payment to activate your
                                <span class="font-semibold text-foreground">{{
                                    props.approved_order?.plan_name
                                }}</span>
                                plan.
                            </p>
                        </div>

                        <div
                            class="space-y-4 rounded-xl border bg-white p-5 shadow-sm dark:bg-muted"
                        >
                            <p
                                class="text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                            >
                                Select Payment Method
                            </p>
                            <div class="space-y-2">
                                <button
                                    v-for="method in PAYMENT_METHODS"
                                    :key="method.key"
                                    type="button"
                                    class="flex w-full items-center gap-3 rounded-xl border-2 p-3 text-left transition"
                                    :class="
                                        payForm.payment_method === method.key
                                            ? 'border-emerald-500 bg-emerald-50'
                                            : 'border-gray-200 hover:border-gray-300'
                                    "
                                    @click="payForm.payment_method = method.key"
                                >
                                    <img
                                        :src="method.icon"
                                        :alt="method.label"
                                        class="h-6 w-10 shrink-0 object-contain"
                                    />
                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="flex items-center gap-2 text-sm font-medium"
                                            :class="
                                                payForm.payment_method ===
                                                method.key
                                                    ? 'text-emerald-700'
                                                    : 'text-gray-700'
                                            "
                                        >
                                            {{ method.label }}
                                            <span
                                                v-if="method.tag"
                                                class="rounded-full bg-emerald-100 px-1.5 py-0.5 text-[10px] font-bold text-emerald-700"
                                                >{{ method.tag }}</span
                                            >
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ method.note }}
                                        </p>
                                    </div>
                                    <CheckCircle2
                                        v-if="
                                            payForm.payment_method ===
                                            method.key
                                        "
                                        class="h-4 w-4 shrink-0 text-emerald-500"
                                    />
                                </button>
                            </div>

                            <div
                                class="flex justify-between border-t pt-3 text-sm font-semibold"
                            >
                                <span>Total due</span>
                                <span class="text-emerald-700"
                                    >₱{{
                                        Number(
                                            props.approved_order?.total_price ??
                                                0,
                                        ).toLocaleString('en-PH', {
                                            minimumFractionDigits: 2,
                                        })
                                    }}</span
                                >
                            </div>

                            <Button
                                class="w-full gap-2 bg-emerald-600 hover:bg-emerald-700"
                                :disabled="
                                    !payForm.payment_method ||
                                    payForm.submitting
                                "
                                @click="proceedToPay"
                            >
                                <Loader2
                                    v-if="payForm.submitting"
                                    class="h-4 w-4 animate-spin"
                                />
                                {{
                                    payForm.submitting
                                        ? 'Redirecting...'
                                        : 'Pay Now'
                                }}
                            </Button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- ══════════════ REJECTED ══════════════ -->
            <template v-else-if="isRejected">
                <CheckoutConfirm
                    v-if="showOrder && canResubmit"
                    plan-name="Standard"
                    :vat-pct="12"
                    :billing-months="1"
                    :user="{ name: user.name, email: user.email }"
                    :shop="props.shop ?? undefined"
                    :agreement="props.agreement"
                />
                <div
                    v-else
                    class="flex min-h-[60vh] flex-1 items-center justify-center px-4"
                >
                    <div class="w-full max-w-lg">
                        <div
                            class="mb-6 flex flex-col items-center text-center"
                        >
                            <div
                                class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100"
                            >
                                <XCircle class="h-8 w-8 text-red-500" />
                            </div>
                            <h2
                                class="text-xl font-semibold text-gray-900 dark:text-white"
                            >
                                Order Rejected
                            </h2>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Your subscription plan order for
                                <span class="font-medium text-foreground">{{
                                    props.rejected_order?.shop_name
                                }}</span>
                                was not approved.
                            </p>
                        </div>
                        <div
                            class="mb-4 rounded-xl border p-4"
                            :class="
                                canResubmit
                                    ? 'border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-950/30'
                                    : 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950/30'
                            "
                        >
                            <p
                                class="text-sm font-semibold"
                                :class="
                                    canResubmit
                                        ? 'text-amber-700 dark:text-amber-300'
                                        : 'text-red-700 dark:text-red-300'
                                "
                            >
                                {{
                                    canResubmit
                                        ? `${props.rejected_order?.resubmissions_remaining} of ${props.rejected_order?.max_resubmissions} resubmissions remaining`
                                        : 'Resubmission limit reached'
                                }}
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{
                                    canResubmit
                                        ? 'Review the rejection reason and correct the application before submitting again.'
                                        : `You have used all ${props.rejected_order?.max_resubmissions} resubmissions. Please contact support for assistance.`
                                }}
                            </p>
                        </div>
                        <div
                            class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-950/30"
                        >
                            <p
                                class="mb-1 text-xs font-semibold tracking-wide text-red-600 uppercase"
                            >
                                Reason for rejection
                            </p>
                            <p
                                class="text-sm leading-relaxed text-red-700 dark:text-red-300"
                            >
                                {{
                                    props.rejected_order?.rejection_reason ??
                                    'No specific reason was provided.'
                                }}
                            </p>
                        </div>
                        <div
                            class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-950/30"
                        >
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-100"
                                >
                                    <Mail class="h-4 w-4 text-blue-600" />
                                </div>
                                <div>
                                    <p
                                        class="mb-0.5 text-sm font-semibold text-blue-700"
                                    >
                                        A notification has been sent to your
                                        email
                                    </p>
                                    <p
                                        class="text-xs leading-relaxed text-blue-600"
                                    >
                                        Details have been sent to
                                        <span class="font-medium">{{
                                            props.rejected_order?.email
                                        }}</span
                                        >. No payment was collected.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <Button
                            v-if="canResubmit"
                            class="w-full"
                            @click="showOrder = true"
                            >Place a New Order</Button
                        >
                        <p
                            class="mt-4 text-center text-xs text-muted-foreground"
                        >
                            If you believe this was a mistake, please contact
                            our support team.
                        </p>
                    </div>
                </div>
            </template>

            <!-- ══════════════ EXPIRED ══════════════ -->
            <template v-else-if="isExpired">
                <div
                    class="flex min-h-[60vh] flex-1 items-center justify-center px-4"
                >
                    <div class="w-full max-w-md text-center">
                        <!-- Trial expired -->
                        <template v-if="props.expired_order?.is_trial">
                            <div
                                class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-blue-100"
                            >
                                <Clock class="h-8 w-8 text-blue-500" />
                            </div>
                            <h2
                                class="mb-1 text-xl font-semibold text-gray-900 dark:text-white"
                            >
                                Your free trial has ended
                            </h2>
                            <p class="mb-1 text-sm text-muted-foreground">
                                Your 7-day free trial has expired.
                            </p>
                            <p
                                v-if="props.expired_order?.expires_at"
                                class="mb-6 text-xs text-muted-foreground"
                            >
                                Ended on
                                {{
                                    new Date(
                                        props.expired_order.expires_at,
                                    ).toLocaleDateString('en-PH', {
                                        month: 'long',
                                        day: 'numeric',
                                        year: 'numeric',
                                    })
                                }}
                            </p>
                            <p v-else class="mb-6" />
                            <div
                                class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-4 text-left dark:bg-blue-950/30"
                            >
                                <p
                                    class="text-sm leading-relaxed text-blue-700"
                                >
                                    Your shop is currently
                                    <span class="font-semibold">inactive</span>
                                    and hidden from customers. Choose a plan to
                                    continue using LaundryHub.
                                </p>
                            </div>
                            <a href="/shop/upgrade">
                                <Button class="w-full gap-2"
                                    >Choose a Plan</Button
                                >
                            </a>
                        </template>

                        <!-- Paid plan expired -->
                        <template v-else>
                            <div
                                class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-amber-100"
                            >
                                <RotateCcw class="h-8 w-8 text-amber-500" />
                            </div>
                            <h2
                                class="mb-1 text-xl font-semibold text-gray-900 dark:text-white"
                            >
                                Subscription Expired
                            </h2>
                            <p class="mb-1 text-sm text-muted-foreground">
                                Your
                                <span class="font-semibold text-foreground">{{
                                    props.expired_order?.plan_name ?? 'plan'
                                }}</span>
                                subscription has expired.
                            </p>
                            <p
                                v-if="props.expired_order?.expires_at"
                                class="mb-6 text-xs text-muted-foreground"
                            >
                                Expired on
                                {{
                                    new Date(
                                        props.expired_order.expires_at,
                                    ).toLocaleDateString('en-PH', {
                                        month: 'long',
                                        day: 'numeric',
                                        year: 'numeric',
                                    })
                                }}
                            </p>
                            <p v-else class="mb-6" />
                            <div
                                class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-left dark:bg-amber-950/30"
                            >
                                <p
                                    class="text-sm leading-relaxed text-amber-700"
                                >
                                    Your shop is currently
                                    <span class="font-semibold">inactive</span>
                                    and hidden from customers. Renew your plan
                                    to restore full access.
                                </p>
                            </div>
                            <a href="/shop/upgrade">
                                <Button class="w-full gap-2">
                                    <RotateCcw class="h-4 w-4" /> Renew Plan
                                </Button>
                            </a>
                            <p class="mt-4 text-xs text-muted-foreground">
                                You can also upgrade to a higher plan if needed.
                            </p>
                        </template>
                    </div>
                </div>
            </template>

            <!-- ══════════════ APPROVED: Live Dashboard ══════════════ -->
            <template v-else-if="isApproved">
                <!-- Header -->
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                >
                    <div>
                        <h2
                            class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
                        >
                            {{ props.order?.shop_name ?? 'Shop Dashboard' }}
                        </h2>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ greeting }}, {{ user.name }}.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            v-if="props.order?.is_trial"
                            class="rounded-full px-2.5 py-1 text-xs font-semibold"
                            :class="
                                (props.order.trial_days_left ?? 0) <= 1
                                    ? 'bg-red-100 text-red-700'
                                    : (props.order.trial_days_left ?? 0) <= 3
                                      ? 'bg-amber-100 text-amber-700'
                                      : 'bg-blue-100 text-blue-700'
                            "
                        >
                            Free trial ·
                            {{ props.order.trial_days_left ?? 0 }} day{{
                                props.order.trial_days_left === 1 ? '' : 's'
                            }}
                            left
                        </span>
                        <span
                            v-else
                            class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 capitalize"
                        >
                            {{ props.order?.subscription_plan ?? 'Active' }}
                        </span>
                        <Button
                            v-if="props.order?.is_trial"
                            size="sm"
                            @click="router.visit('/shop/upgrade')"
                        >
                            Subscribe
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            class="gap-2"
                            :disabled="isRefreshing"
                            @click="refreshDashboard"
                        >
                            <RefreshCcw
                                class="h-3.5 w-3.5"
                                :class="{ 'animate-spin': isRefreshing }"
                            />
                            {{ isRefreshing ? 'Refreshing' : 'Refresh' }}
                        </Button>
                    </div>
                </div>

                <!-- Needs attention today -->
                <section aria-labelledby="attention-heading">
                    <div
                        class="mb-3 flex flex-wrap items-center justify-between gap-2"
                    >
                        <p
                            id="attention-heading"
                            class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                        >
                            Needs attention today
                        </p>
                        <div
                            class="inline-flex rounded-lg border bg-white p-0.5 dark:bg-card"
                            role="group"
                            aria-label="Period"
                        >
                            <button
                                v-for="option in PERIOD_OPTIONS"
                                :key="option.value"
                                type="button"
                                class="rounded-md px-3 py-1 text-xs font-medium transition focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="
                                    period === option.value
                                        ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900'
                                        : 'text-muted-foreground hover:text-foreground'
                                "
                                :aria-pressed="period === option.value"
                                @click="setPeriod(option.value)"
                            >
                                {{ option.label }}
                            </button>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <div
                            v-for="card in visibleAttentionCards"
                            :key="card.key"
                            class="flex flex-col rounded-xl border bg-white p-4 shadow-sm dark:bg-card"
                        >
                            <span
                                class="inline-flex w-fit items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                :class="ATTENTION_TONES[card.tone].badge"
                            >
                                <component
                                    :is="ATTENTION_TONES[card.tone].icon"
                                    class="h-3 w-3"
                                />
                                {{ ATTENTION_TONES[card.tone].label }}
                            </span>
                            <p class="mt-3 text-sm font-semibold">
                                {{ card.title }}
                            </p>
                            <p
                                class="mt-1 text-xs leading-relaxed text-muted-foreground"
                            >
                                {{ card.message }}
                            </p>
                            <p
                                v-if="card.rule"
                                class="mt-3 flex-1 text-[11px] text-muted-foreground"
                            >
                                Rule: {{ card.rule }}
                            </p>
                            <div v-else class="flex-1" />
                            <div class="mt-4 flex items-center gap-3">
                                <Link
                                    v-if="card.href"
                                    :href="card.href"
                                    class="inline-flex items-center rounded-md border px-3 py-1.5 text-xs font-medium transition hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    {{ card.action }}
                                </Link>
                                <button
                                    v-if="card.key !== 'clear'"
                                    type="button"
                                    class="text-xs text-muted-foreground hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                    @click="dismissCard(card.key)"
                                >
                                    Dismiss
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Expense insights progress -->
                <div
                    v-if="
                        (props.daily?.total_orders ?? 0) < INSIGHTS_MIN_ORDERS
                    "
                    class="flex flex-col gap-3 rounded-xl border bg-white px-5 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:bg-card"
                >
                    <div>
                        <p class="text-sm font-semibold">Expense insights</p>
                        <p class="text-xs text-muted-foreground">
                            Waiting for more data. Needs
                            {{ INSIGHTS_MIN_ORDERS }} orders,
                            {{ props.daily?.total_orders ?? 0 }} so far.
                        </p>
                    </div>
                    <div
                        class="h-1.5 w-full overflow-hidden rounded-full bg-gray-200 sm:w-1/2 dark:bg-muted"
                        role="progressbar"
                        :aria-valuenow="props.daily?.total_orders ?? 0"
                        :aria-valuemax="INSIGHTS_MIN_ORDERS"
                    >
                        <div
                            class="h-full rounded-full bg-blue-500"
                            :style="{
                                width: `${Math.min(100, ((props.daily?.total_orders ?? 0) / INSIGHTS_MIN_ORDERS) * 100)}%`,
                            }"
                        />
                    </div>
                </div>

                <!-- KPI cards -->
                <section
                    class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
                    aria-label="Today's numbers"
                >
                    <div
                        v-for="kpi in kpiCards"
                        :key="kpi.label"
                        class="rounded-xl border bg-white p-5 shadow-sm dark:bg-card"
                    >
                        <p class="text-xs font-medium text-muted-foreground">
                            {{ kpi.label }}
                        </p>
                        <p
                            class="mt-2 text-2xl font-bold tracking-tight tabular-nums"
                        >
                            {{ kpi.value }}
                        </p>
                        <p
                            class="mt-1 text-xs"
                            :class="kpi.hintClass ?? 'text-muted-foreground'"
                        >
                            {{ kpi.hint }}
                        </p>
                    </div>
                </section>

                <!-- Daily revenue + Due today -->
                <div class="grid gap-4 lg:grid-cols-3">
                    <Card class="lg:col-span-2">
                        <CardHeader class="pb-2">
                            <CardTitle
                                class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                                >Daily revenue, last 7 days</CardTitle
                            >
                        </CardHeader>
                        <CardContent>
                            <div
                                v-if="
                                    dailyRevenue.every((d) => d.revenue === 0)
                                "
                                class="flex h-44 items-center justify-center text-sm text-muted-foreground"
                            >
                                No payments received this week.
                            </div>
                            <div v-else class="flex h-44 items-end gap-3">
                                <div
                                    v-for="(day, i) in dailyRevenue"
                                    :key="i"
                                    class="flex h-full flex-1 flex-col justify-end gap-2"
                                    :title="`${day.label}: ₱${formatPeso(day.revenue)}`"
                                >
                                    <div
                                        class="w-full rounded-t-md"
                                        :class="
                                            i === dailyRevenue.length - 1
                                                ? 'bg-blue-500'
                                                : 'bg-gray-200 dark:bg-muted'
                                        "
                                        :style="{
                                            height: `${Math.max(4, (day.revenue / maxDailyRevenue) * 100)}%`,
                                        }"
                                    />
                                    <span
                                        class="text-center text-[11px] text-muted-foreground"
                                        >{{ day.label }}</span
                                    >
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle
                                class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                                >Due today</CardTitle
                            >
                        </CardHeader>
                        <CardContent>
                            <div
                                v-if="
                                    (props.daily?.due_today.length ?? 0) === 0
                                "
                                class="flex h-44 items-center justify-center text-sm text-muted-foreground"
                            >
                                Nothing is due today.
                            </div>
                            <ul v-else class="divide-y">
                                <li
                                    v-for="order in props.daily?.due_today"
                                    :key="order.order_number"
                                    class="flex items-center justify-between gap-3 py-2.5 first:pt-0 last:pb-0"
                                >
                                    <div class="min-w-0">
                                        <p
                                            class="truncate text-sm font-semibold"
                                        >
                                            #{{ order.order_number }}
                                            {{ order.customer }}
                                        </p>
                                        <p
                                            class="truncate text-xs text-muted-foreground"
                                        >
                                            {{ order.service ?? 'Laundry' }}
                                        </p>
                                    </div>
                                    <span
                                        class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                        :class="
                                            order.overdue
                                                ? 'bg-red-100 text-red-700'
                                                : 'bg-blue-100 text-blue-700'
                                        "
                                        >{{ order.due_at }}</span
                                    >
                                </li>
                            </ul>
                        </CardContent>
                    </Card>
                </div>
            </template>

            <!-- paid not approved: Waiting -->
            <template v-else-if="isPaid && !isApproved">
                <div
                    class="flex min-h-[60vh] flex-1 items-center justify-center"
                >
                    <div class="max-w-md text-center">
                        <div
                            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100"
                        >
                            <Clock class="h-8 w-8 text-yellow-500" />
                        </div>
                        <h2
                            class="mb-2 text-xl font-semibold text-gray-900 dark:text-white"
                        >
                            Awaiting Admin Approval
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            Your payment has been received. Please wait while
                            our admin reviews and approves your account. You'll
                            have full access once approved.
                        </p>
                    </div>
                </div>
            </template>

            <!-- NOT PAID: Choose path -->
            <template v-else>
                <!-- Choice screen -->
                <div
                    v-if="!showOrder"
                    class="flex min-h-[60vh] flex-1 items-start justify-center px-4 pt-8"
                >
                    <div class="w-full max-w-2xl">
                        <div class="mb-8 text-center">
                            <h2
                                class="text-2xl font-bold text-gray-900 dark:text-white"
                            >
                                Welcome, {{ user.name }}
                            </h2>
                            <p class="mt-2 text-sm text-muted-foreground">
                                Choose how you'd like to get started with
                                LaundryHub.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <!-- Free Trial card (hidden if already used) -->
                            <button
                                v-if="!props.has_used_trial"
                                class="group relative flex flex-col items-start gap-3 rounded-2xl border-2 border-blue-200 bg-blue-50 p-6 text-left transition hover:border-blue-400 hover:shadow-md focus:outline-none dark:border-blue-800 dark:bg-blue-950/30"
                                @click="showTrialConfirm = true"
                            >
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/50"
                                >
                                    <Lightbulb class="h-6 w-6 text-blue-600" />
                                </div>
                                <div>
                                    <p
                                        class="font-semibold text-gray-900 dark:text-white"
                                    >
                                        Start Free Trial
                                    </p>
                                    <p
                                        class="mt-1 text-sm leading-relaxed text-muted-foreground"
                                    >
                                        Try all features free for 7 days — no
                                        payment required.
                                    </p>
                                </div>
                                <span
                                    class="absolute top-4 right-4 rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-900 dark:text-blue-300"
                                    >FREE</span
                                >
                            </button>

                            <!-- Subscribe card -->
                            <button
                                class="group flex flex-col items-start gap-3 rounded-2xl border-2 border-emerald-200 bg-emerald-50 p-6 text-left transition hover:border-emerald-400 hover:shadow-md focus:outline-none dark:border-emerald-800 dark:bg-emerald-950/30"
                                :class="
                                    props.has_used_trial ? 'sm:col-span-2' : ''
                                "
                                @click="showOrder = true"
                            >
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/50"
                                >
                                    <CreditCard
                                        class="h-6 w-6 text-emerald-600"
                                    />
                                </div>
                                <div>
                                    <p
                                        class="font-semibold text-gray-900 dark:text-white"
                                    >
                                        Subscribe to a Plan
                                    </p>
                                    <p
                                        class="mt-1 text-sm leading-relaxed text-muted-foreground"
                                    >
                                        Choose Basic, Standard, or Premium and
                                        submit your application.
                                    </p>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <CheckoutConfirm
                    v-if="showOrder"
                    plan-name="Standard"
                    :vat-pct="12"
                    :billing-months="1"
                    :user="{ name: user.name, email: user.email }"
                    :shop="props.shop ?? undefined"
                    :agreement="props.agreement"
                />
            </template>
        </div>

        <!-- Free Trial Confirmation Dialog -->
        <Dialog v-model:open="showTrialConfirm">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <div
                        class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100"
                    >
                        <Lightbulb class="h-6 w-6 text-blue-600" />
                    </div>
                    <DialogTitle class="text-lg"
                        >Start Your Free Trial?</DialogTitle
                    >
                    <DialogDescription class="mt-1 text-sm leading-relaxed">
                        You're about to activate a
                        <span class="font-semibold text-foreground"
                            >7-day free trial</span
                        >
                        with access to all LaundryHub features — no payment
                        required.
                        <br /><br />
                        Please note that you can only use the free trial
                        <span class="font-semibold text-foreground">once</span>.
                        After it expires, you'll need to subscribe to continue
                        using the platform.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="mt-2 gap-2 sm:gap-0">
                    <Button variant="outline" @click="showTrialConfirm = false"
                        >Cancel</Button
                    >
                    <Button
                        class="bg-blue-600 text-white hover:bg-blue-700"
                        @click="
                            showTrialConfirm = false;
                            router.visit('/trial');
                        "
                    >
                        <Lightbulb class="mr-1.5 h-4 w-4" /> Activate Free Trial
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </ShopLayout>
</template>
