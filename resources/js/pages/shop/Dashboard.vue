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
import { Head, router, usePage } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import {
    CheckCircle2,
    Clock,
    CreditCard,
    Lightbulb,
    Loader2,
    Mail,
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
interface BranchEmployee {
    branch: string;
    count: number;
    status: string;
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
interface DSSInsight {
    type: 'success' | 'warning' | 'danger' | 'info';
    title: string;
    message: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/shop/dashboard' },
];

// ─── Props ────────────────────────────────────────────────────────────────────

const { props } = usePage<{
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
        branches: { total: number; active: number };
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
    employees_per_branch?: BranchEmployee[];
    low_stock_items?: LowStockItem[];
    recent_movements?: RecentMovement[];
    order_stats?: OrderStats;
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

// ─── DSS Insights ─────────────────────────────────────────────────────────────

const dssRecommendations = computed<DSSInsight[]>(() => {
    const stats = props.stats;
    const orders = props.order_stats;
    const openOrders = (orders?.pending ?? 0) + (orders?.in_progress ?? 0);

    const orderRecommendation: DSSInsight =
        !orders || orders.total === 0
            ? {
                  type: 'info',
                  title: 'Build Your Order Pipeline',
                  message:
                      'No orders are recorded yet. Confirm that staff can create and update orders so this dashboard can guide daily operations.',
              }
            : orders.pending > 5
              ? {
                    type: 'warning',
                    title: 'Reduce the Pending Backlog',
                    message: `${orders.pending} orders are waiting to be processed. Prioritize older orders and assign enough staff to keep turnaround times under control.`,
                }
              : orders.completion_rate < 50
                ? {
                      type: 'danger',
                      title: 'Improve Order Completion',
                      message: `Only ${orders.completion_rate}% of orders are complete. Review unfinished orders and remove the workflow bottlenecks delaying customers.`,
                  }
                : {
                      type: 'success',
                      title: 'Keep Orders Moving',
                      message: `The shop has a ${orders.completion_rate}% completion rate with ${openOrders} active order(s). Maintain the current processing pace.`,
                  };

    const revenueRecommendation: DSSInsight =
        !orders || orders.total === 0
            ? {
                  type: 'info',
                  title: 'Establish a Revenue Baseline',
                  message:
                      'Revenue guidance will become more useful after the first completed orders. Record every payment consistently from the start.',
              }
            : orders.last_month > 0 && orders.orders_change < 0
              ? {
                    type: 'warning',
                    title: 'Recover Monthly Demand',
                    message: `Order volume is down ${Math.abs(orders.orders_change)}% from last month. Use a focused promotion or customer follow-up to rebuild demand.`,
                }
              : orders.orders_change > 0
                ? {
                      type: 'success',
                      title: 'Sustain Revenue Growth',
                      message: `Orders increased by ${orders.orders_change}% this month. Protect that growth through reliable service and relevant add-ons.`,
                  }
                : {
                      type: 'info',
                      title: 'Grow Average Order Value',
                      message: `Average order value is ${formatCurrency(orders.avg_order_value)}. Consider practical bundles or service add-ons instead of broad discounts.`,
                  };

    const outOfStock = stats?.inventory.out_of_stock ?? 0;
    const lowStock = stats?.inventory.low_stock ?? 0;
    const inventoryRecommendation: DSSInsight =
        outOfStock > 0
            ? {
                  type: 'danger',
                  title: 'Restock Critical Items',
                  message: `${outOfStock} item(s) are out of stock. Replenish supplies needed by active orders before accepting work that depends on them.`,
              }
            : lowStock > 0
              ? {
                    type: 'warning',
                    title: 'Reorder Low-Stock Supplies',
                    message: `${lowStock} item(s) are below their minimum level. Reorder them before stock shortages interrupt shop operations.`,
                }
              : {
                    type: 'success',
                    title: 'Maintain Inventory Readiness',
                    message:
                        'Inventory is currently above minimum levels. Keep reorder points and routine stock checks up to date.',
                };

    const inactiveEmployees = stats?.employees.inactive ?? 0;
    const activeEmployees = stats?.employees.active ?? 0;
    const activeBranches = stats?.branches.active ?? 0;
    const totalBranches = stats?.branches.total ?? 0;
    const workforceRecommendation: DSSInsight =
        inactiveEmployees > 0 && openOrders > 0
            ? {
                  type: 'warning',
                  title: 'Align Staff With Workload',
                  message: `${inactiveEmployees} employee(s) are inactive while ${openOrders} order(s) remain open. Review shift coverage and task assignments.`,
              }
            : activeBranches < totalBranches
              ? {
                    type: 'warning',
                    title: 'Review Branch Availability',
                    message: `${activeBranches} of ${totalBranches} branches are active. Confirm whether inactive locations need staffing or operational attention.`,
                }
              : {
                    type: 'info',
                    title: 'Balance Team Capacity',
                    message: `${activeEmployees} active employee(s) are supporting ${activeBranches} active branch(es) and ${openOrders} open order(s). Adjust assignments as demand changes.`,
                };

    return [
        orderRecommendation,
        revenueRecommendation,
        inventoryRecommendation,
        workforceRecommendation,
    ];
});

// ─── Chart refs ────────────────────────────────────────────────────────────────

const ordersChartRef = ref<HTMLCanvasElement | null>(null);
const revenueChartRef = ref<HTMLCanvasElement | null>(null);
const serviceChartRef = ref<HTMLCanvasElement | null>(null);
const movementChartRef = ref<HTMLCanvasElement | null>(null);
const categoryChartRef = ref<HTMLCanvasElement | null>(null);
const branchChartRef = ref<HTMLCanvasElement | null>(null);

onMounted(() => {
    if (!isPaid.value) return;

    const orders12 = props.monthly_orders ?? [];
    const services = props.service_popularity ?? [];
    const movData = props.movement_chart ?? [];
    const catData = props.category_breakdown ?? [];
    const branchData = props.employees_per_branch ?? [];

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

    /* ── Employees per Branch ── */
    if (branchChartRef.value && branchData.length) {
        new Chart(branchChartRef.value, {
            type: 'bar',
            data: {
                labels: branchData.map((d) => d.branch),
                datasets: [
                    {
                        label: 'Employees',
                        data: branchData.map((d) => d.count),
                        backgroundColor: branchData.map((d) =>
                            d.status === 'Active'
                                ? 'rgba(99,102,241,0.75)'
                                : 'rgba(156,163,175,0.5)',
                        ),
                        borderRadius: 5,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
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
        only: [
            'stats',
            'movement_chart',
            'category_breakdown',
            'employees_per_branch',
            'low_stock_items',
            'recent_movements',
            'order_stats',
            'monthly_orders',
            'service_popularity',
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
                <!-- Trial Banner -->
                <div
                    v-if="props.order?.is_trial"
                    class="flex flex-col gap-3 rounded-xl border px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                    :class="
                        (props.order.trial_days_left ?? 0) <= 1
                            ? 'border-red-200 bg-red-50'
                            : (props.order.trial_days_left ?? 0) <= 3
                              ? 'border-amber-200 bg-amber-50'
                              : 'border-blue-200 bg-blue-50'
                    "
                >
                    <div class="flex items-center gap-3">
                        <Clock
                            class="h-5 w-5 shrink-0"
                            :class="
                                (props.order.trial_days_left ?? 0) <= 1
                                    ? 'text-red-500'
                                    : (props.order.trial_days_left ?? 0) <= 3
                                      ? 'text-amber-500'
                                      : 'text-blue-500'
                            "
                        />
                        <div>
                            <p
                                class="text-sm font-semibold"
                                :class="
                                    (props.order.trial_days_left ?? 0) <= 1
                                        ? 'text-red-800'
                                        : (props.order.trial_days_left ?? 0) <=
                                            3
                                          ? 'text-amber-800'
                                          : 'text-blue-800'
                                "
                            >
                                <span
                                    v-if="
                                        (props.order.trial_days_left ?? 0) === 0
                                    "
                                    >Your free trial expires today</span
                                >
                                <span v-else
                                    >{{ props.order.trial_days_left }} day{{
                                        props.order.trial_days_left === 1
                                            ? ''
                                            : 's'
                                    }}
                                    left in your free trial</span
                                >
                            </p>
                            <p
                                class="mt-0.5 text-xs"
                                :class="
                                    (props.order.trial_days_left ?? 0) <= 1
                                        ? 'text-red-600'
                                        : (props.order.trial_days_left ?? 0) <=
                                            3
                                          ? 'text-amber-600'
                                          : 'text-blue-600'
                                "
                            >
                                Subscribe now to keep your shop running after
                                the trial ends.
                            </p>
                        </div>
                    </div>
                    <Button
                        size="sm"
                        @click="router.visit('/shop/upgrade')"
                        :class="
                            (props.order.trial_days_left ?? 0) <= 1
                                ? 'bg-red-600 text-white hover:bg-red-700'
                                : (props.order.trial_days_left ?? 0) <= 3
                                  ? 'bg-amber-500 text-white hover:bg-amber-600'
                                  : 'bg-blue-600 text-white hover:bg-blue-700'
                        "
                        class="shrink-0 border-0"
                    >
                        Subscribe Now
                    </Button>
                </div>

                <!-- Welcome Banner -->

                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2
                            class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
                        >
                            {{ props.order?.shop_name ?? 'Shop Dashboard' }}
                        </h2>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Welcome back, {{ user.name }}. Here is your business
                            overview.
                        </p>
                    </div>
                    <div
                        class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground"
                    >
                        <span
                            class="rounded-full px-2.5 py-1 font-medium capitalize"
                            :class="
                                props.order?.is_trial
                                    ? 'bg-blue-100 text-blue-700'
                                    : 'bg-emerald-100 text-emerald-700'
                            "
                        >
                            {{
                                props.order?.is_trial
                                    ? 'Free Trial'
                                    : (props.order?.subscription_plan ??
                                      'Active')
                            }}
                        </span>
                        <span v-if="props.order?.expires_at">
                            Expires
                            {{
                                new Date(
                                    props.order.expires_at,
                                ).toLocaleDateString('en-PH', {
                                    month: 'short',
                                    day: 'numeric',
                                    year: 'numeric',
                                })
                            }}
                        </span>
                        <Button
                            variant="outline"
                            size="sm"
                            class="ml-1 gap-2"
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

                <!-- Compact business overview -->
                <Card>
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <CardTitle class="text-sm font-semibold"
                                    >Business Overview</CardTitle
                                >
                                <p class="mt-1 text-xs text-muted-foreground">
                                    The numbers that need attention today.
                                </p>
                            </div>
                            <span class="text-xs text-muted-foreground"
                                >This month</span
                            >
                        </div>
                    </CardHeader>
                    <CardContent
                        class="grid gap-5 pt-3 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        <div>
                            <p
                                class="text-xs font-medium text-muted-foreground"
                            >
                                Total Revenue
                            </p>
                            <p
                                class="mt-1 text-2xl font-bold tracking-tight tabular-nums"
                            >
                                ₱{{
                                    formatPeso(
                                        props.order_stats?.total_revenue ?? 0,
                                    )
                                }}
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                ₱{{
                                    formatPeso(
                                        props.order_stats?.avg_order_value ?? 0,
                                    )
                                }}
                                average order
                            </p>
                        </div>
                        <div
                            class="border-t pt-4 sm:border-t-0 sm:border-l sm:pt-0 sm:pl-5"
                        >
                            <p
                                class="text-xs font-medium text-muted-foreground"
                            >
                                Orders
                            </p>
                            <div class="mt-1 flex items-baseline gap-2">
                                <p class="text-2xl font-bold tabular-nums">
                                    {{ props.order_stats?.this_month ?? 0 }}
                                </p>
                                <span
                                    class="text-xs font-semibold"
                                    :class="
                                        (props.order_stats?.orders_change ??
                                            0) >= 0
                                            ? 'text-emerald-600'
                                            : 'text-red-500'
                                    "
                                >
                                    {{
                                        (props.order_stats?.orders_change ??
                                            0) >= 0
                                            ? '+'
                                            : ''
                                    }}{{
                                        props.order_stats?.orders_change ?? 0
                                    }}%
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ props.order_stats?.total ?? 0 }} all-time
                            </p>
                        </div>
                        <div
                            class="border-t pt-4 lg:border-t-0 lg:border-l lg:pt-0 lg:pl-5"
                        >
                            <p
                                class="text-xs font-medium text-muted-foreground"
                            >
                                Open Orders
                            </p>
                            <p class="mt-1 text-2xl font-bold tabular-nums">
                                {{
                                    (props.order_stats?.pending ?? 0) +
                                    (props.order_stats?.in_progress ?? 0)
                                }}
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ props.order_stats?.pending ?? 0 }} pending ·
                                {{ props.order_stats?.in_progress ?? 0 }}
                                processing
                            </p>
                        </div>
                        <div
                            class="border-t pt-4 lg:border-t-0 lg:border-l lg:pt-0 lg:pl-5"
                        >
                            <p
                                class="text-xs font-medium text-muted-foreground"
                            >
                                Completion Rate
                            </p>
                            <p class="mt-1 text-2xl font-bold tabular-nums">
                                {{ props.order_stats?.completion_rate ?? 0 }}%
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ props.order_stats?.completed ?? 0 }}
                                completed
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Revenue chart -->
                <div>
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-semibold"
                                >Monthly Revenue</CardTitle
                            >
                        </CardHeader>
                        <CardContent>
                            <div
                                v-if="
                                    props.monthly_orders?.every(
                                        (item) => item.revenue === 0,
                                    )
                                "
                                class="flex h-56 items-center justify-center text-sm text-muted-foreground"
                            >
                                No revenue data yet.
                            </div>
                            <div v-else class="relative h-56">
                                <canvas ref="revenueChartRef"></canvas>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Operational snapshot -->
                <div class="grid gap-4 lg:grid-cols-3">
                    <Card class="lg:col-span-2">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-semibold"
                                >Operational Snapshot</CardTitle
                            >
                        </CardHeader>
                        <CardContent class="grid gap-5 pt-3 sm:grid-cols-2">
                            <div>
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <span class="text-sm text-muted-foreground"
                                        >Employees</span
                                    >
                                    <span class="font-semibold tabular-nums">{{
                                        props.stats?.employees.total ?? 0
                                    }}</span>
                                </div>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    {{ props.stats?.employees.active ?? 0 }}
                                    active
                                </p>
                            </div>
                            <div>
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <span class="text-sm text-muted-foreground"
                                        >Branches</span
                                    >
                                    <span class="font-semibold tabular-nums">{{
                                        props.stats?.branches.total ?? 0
                                    }}</span>
                                </div>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    {{ props.stats?.branches.active ?? 0 }}
                                    active
                                </p>
                            </div>
                            <div class="border-t pt-4">
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <span class="text-sm text-muted-foreground"
                                        >Inventory Items</span
                                    >
                                    <span class="font-semibold tabular-nums">{{
                                        props.stats?.inventory.total ?? 0
                                    }}</span>
                                </div>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    {{ props.stats?.movements.this_month ?? 0 }}
                                    movements this month
                                </p>
                            </div>
                            <div class="border-t pt-4">
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <span class="text-sm text-muted-foreground"
                                        >Stock Attention</span
                                    >
                                    <span
                                        class="font-semibold tabular-nums"
                                        :class="
                                            (props.stats?.inventory.low_stock ??
                                                0) +
                                                (props.stats?.inventory
                                                    .out_of_stock ?? 0) >
                                            0
                                                ? 'text-amber-600'
                                                : 'text-emerald-600'
                                        "
                                    >
                                        {{
                                            (props.stats?.inventory.low_stock ??
                                                0) +
                                            (props.stats?.inventory
                                                .out_of_stock ?? 0)
                                        }}
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    {{
                                        props.stats?.inventory.out_of_stock ?? 0
                                    }}
                                    out of stock
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-semibold"
                                >Low Stock</CardTitle
                            >
                        </CardHeader>
                        <CardContent>
                            <div
                                v-if="
                                    (props.low_stock_items?.length ?? 0) === 0
                                "
                                class="flex h-36 items-center justify-center text-sm text-muted-foreground"
                            >
                                Stock levels are healthy.
                            </div>
                            <div v-else class="divide-y">
                                <div
                                    v-for="item in props.low_stock_items?.slice(
                                        0,
                                        4,
                                    )"
                                    :key="item.sku"
                                    class="flex items-center justify-between gap-3 py-2.5 first:pt-0 last:pb-0"
                                >
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium">
                                            {{ item.name }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ item.sku }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p
                                            class="text-sm font-semibold tabular-nums"
                                        >
                                            {{ item.quantity }}
                                        </p>
                                        <p
                                            class="text-[10px] text-muted-foreground"
                                        >
                                            min {{ item.min_stock }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- DSS recommendations -->
                <div v-if="dssRecommendations.length > 0">
                    <p
                        class="mb-3 text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                    >
                        DSS Recommendations
                    </p>
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <div
                            v-for="(insight, index) in dssRecommendations"
                            :key="index"
                            class="space-y-2 rounded-xl border p-4"
                            :class="{
                                'border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-900/20':
                                    insight.type === 'success',
                                'border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20':
                                    insight.type === 'warning',
                                'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20':
                                    insight.type === 'danger',
                                'border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20':
                                    insight.type === 'info',
                            }"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-sm font-semibold">
                                    {{ insight.title }}
                                </p>
                                <span
                                    class="shrink-0 rounded-full bg-white/70 px-2 py-0.5 text-[10px] font-semibold uppercase dark:bg-black/20"
                                >
                                    {{
                                        insight.type === 'danger'
                                            ? 'critical'
                                            : insight.type
                                    }}
                                </span>
                            </div>
                            <p
                                class="text-xs leading-relaxed text-muted-foreground"
                            >
                                {{ insight.message }}
                            </p>
                        </div>
                    </div>
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
