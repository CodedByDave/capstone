<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import CheckoutConfirm from '@/pages/shop/CheckoutConfirm.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, usePage, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import {
    Users, GitBranch, Package, AlertTriangle,
    ArrowUpRight, ArrowDownRight, Activity, BoxSelect,
    RefreshCcw, Clock, Lightbulb, TrendingUp, TrendingDown,
    ShieldAlert, CheckCircle2, Info, XCircle, Mail, RotateCcw,
    ShoppingBag, BarChart2, Flame, Percent, Loader2, CreditCard,
} from 'lucide-vue-next'
import { ref, computed, onMounted, reactive } from 'vue'

// ─── Types ────────────────────────────────────────────────────────────────────

interface MovementPoint  { month: string; stock_in: number; stock_out: number }
interface CategoryPoint  { label: string; count: number }
interface BranchEmployee { branch: string; count: number; status: string }
interface LowStockItem   { name: string; sku: string; quantity: number; min_stock: number; status: string; category: string }
interface RecentMovement { item: string; sku: string; type: string; quantity: number; before: number; after: number; by: string; notes: string | null; date: string }

interface MonthlyOrderPoint {
    month: string
    month_key: string
    total_orders: number
    completed: number
    revenue: number
}
interface ServicePop {
    name: string
    order_count: number
    revenue: number
}
interface OrderStats {
    total: number
    completed: number
    pending: number
    in_progress: number
    completion_rate: number
    total_revenue: number
    avg_order_value: number
    this_month: number
    last_month: number
    orders_change: number
}
interface OrderStatusDist {
    pending: number
    in_progress: number
    completed: number
}

interface DSSInsight {
    type: 'success' | 'warning' | 'danger' | 'info'
    title: string
    message: string
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/shop/dashboard' },
]

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
        is_trial: boolean
        trial_days_left: number | null
    }
    pending_order?: {
        plan_name: string
        billing_months: number
        total_price: number
        created_at: string
    } | null
    approved_order?: {
        plan_name: string
        total_price: number
    } | null
    rejected_order?: {
        id: number
        shop_name: string
        rejection_reason: string | null
        total_price: number
        email: string
    } | null
    expired_order?: {
        plan_name: string | null
        expires_at: string | null
        is_trial: boolean
    } | null
    has_used_trial?: boolean
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
    order_stats?: OrderStats
    monthly_orders?: MonthlyOrderPoint[]
    service_popularity?: ServicePop[]
    order_status_dist?: OrderStatusDist
}>()

const user       = props.auth.user
const isPaid     = computed(() => ['paid', 'approved'].includes(props.order?.status ?? ''))
const isApproved = computed(() =>
    ['approved', 'paid'].includes(props.order?.status ?? '') && (props.shop?.status ?? 'active') !== 'disabled'
)
const isPending         = computed(() => !!props.pending_order)
const isApprovedNonTrial = computed(() => !!props.approved_order)
const isRejected = computed(() => !!props.rejected_order)
const isExpired  = computed(() => !!props.expired_order)
const showOrder       = ref(false)
const showTrialConfirm = ref(false)

// ─── Payment form (approved non-trial) ───────────────────────────────────────

const PAYMENT_METHODS = [
    { key: 'gcash',    label: 'GCash',              icon: 'https://upload.wikimedia.org/wikipedia/commons/5/52/GCash_logo.svg',                          note: 'Pay via GCash mobile wallet',       tag: 'Most Popular' },
    { key: 'maya',     label: 'Maya',               icon: 'https://upload.wikimedia.org/wikipedia/commons/e/e6/Maya_logo.svg',                           note: 'Pay via Maya (PayMaya) wallet' },
    { key: 'card',     label: 'Credit / Debit Card', icon: 'https://upload.wikimedia.org/wikipedia/commons/9/98/Visa_Inc._logo_%282005%E2%80%932014%29.svg', note: 'Visa, Mastercard, JCB' },
    { key: 'grab_pay', label: 'GrabPay',            icon: 'https://upload.wikimedia.org/wikipedia/commons/f/f6/Grab_Logo.svg',                           note: 'Pay via GrabPay wallet' },
    { key: 'dob',      label: 'Online Banking',      icon: 'https://upload.wikimedia.org/wikipedia/commons/4/49/BDO_Unibank_%28logo%29.svg',              note: 'BDO, BPI, UnionBank, Metrobank' },
    { key: 'billease', label: 'BillEase',            icon: 'https://logobase.net/wp-content/uploads/2025/08/BillEase-Logo-1.webp',                        note: 'Buy now, pay later',                tag: 'Installment' },
]
const payForm = reactive({ payment_method: '', submitting: false })

// ─── DSS Insights ─────────────────────────────────────────────────────────────

const dssInsights = computed<DSSInsight[]>(() => {
    const insights: DSSInsight[] = []
    const stats     = props.stats
    const movChart  = props.movement_chart ?? []
    const catData   = props.category_breakdown ?? []
    const branches  = props.employees_per_branch ?? []
    const orderStats = props.order_stats
    const orders12  = props.monthly_orders ?? []

    if (!stats) return insights

    // ── Order completion rate ──
    if (orderStats) {
        if (orderStats.total > 0) {
            if (orderStats.completion_rate < 50) {
                insights.push({
                    type: 'danger',
                    title: 'Low Order Completion Rate',
                    message: `Only ${orderStats.completion_rate}% of orders have been completed. Review your processing workflow and identify bottlenecks causing delays — unfinished orders risk customer dissatisfaction.`,
                })
            } else if (orderStats.completion_rate >= 85) {
                insights.push({
                    type: 'success',
                    title: 'Excellent Completion Rate',
                    message: `Your order completion rate is ${orderStats.completion_rate}%. This reflects strong operational efficiency and good customer satisfaction — keep up the consistent workflow.`,
                })
            }
        }

        // ── Pending backlog ──
        if (orderStats.pending > 5) {
            insights.push({
                type: 'warning',
                title: 'High Pending Order Backlog',
                message: `${orderStats.pending} orders are currently queued. A large backlog can increase turnaround times and frustrate customers. Consider assigning more staff or extending operating hours.`,
            })
        }

        // ── Orders this month change ──
        if (orderStats.orders_change >= 25) {
            insights.push({
                type: 'info',
                title: 'Order Volume Surge This Month',
                message: `Orders increased by ${orderStats.orders_change}% compared to last month. Ensure your team capacity and supply levels can sustain this demand spike.`,
            })
        } else if (orderStats.orders_change <= -25 && orderStats.last_month > 0) {
            insights.push({
                type: 'warning',
                title: 'Order Volume Drop This Month',
                message: `Orders dropped by ${Math.abs(orderStats.orders_change)}% compared to last month. Consider launching promotions or loyalty incentives to re-engage customers.`,
            })
        }
    }

    // ── Peak & slow months ──
    if (orders12.length >= 3) {
        const nonZero = orders12.filter(d => d.total_orders > 0)
        if (nonZero.length >= 2) {
            const maxOrders = Math.max(...nonZero.map(d => d.total_orders))
            const peakMonths = nonZero.filter(d => d.total_orders >= maxOrders * 0.75)
            const slowMonths = nonZero.filter(d => d.total_orders <= maxOrders * 0.30)

            if (peakMonths.length > 0 && peakMonths.length < orders12.length) {
                insights.push({
                    type: 'info',
                    title: `Peak Laundry Season: ${peakMonths.map(m => m.month).join(', ')}`,
                    message: `These months have the highest laundry demand. Pre-stock chemicals, ensure all machines are serviced, and schedule extra staffing to maximise throughput during peak periods.`,
                })
            }
            if (slowMonths.length > 0 && slowMonths.length < orders12.length) {
                insights.push({
                    type: 'warning',
                    title: `Slow Months Detected: ${slowMonths.map(m => m.month).join(', ')}`,
                    message: `Order volume dips significantly in these months. Consider targeted promos, referral discounts, or bundle deals to sustain revenue during off-peak seasons.`,
                })
            }

            // ── Revenue growth (last 2 months) ──
            if (orders12.length >= 2) {
                const last2 = orders12.filter(d => d.total_orders > 0 || d.revenue > 0).slice(-2)
                if (last2.length === 2 && last2[0].revenue > 0) {
                    const revChange = ((last2[1].revenue - last2[0].revenue) / last2[0].revenue) * 100
                    if (revChange >= 20) {
                        insights.push({
                            type: 'success',
                            title: 'Revenue Growing',
                            message: `Revenue grew by ${revChange.toFixed(1)}% from ${last2[0].month} to ${last2[1].month}. Sustain this by maintaining service quality and exploring upsell opportunities.`,
                        })
                    } else if (revChange <= -20) {
                        insights.push({
                            type: 'warning',
                            title: 'Revenue Declining',
                            message: `Revenue dropped by ${Math.abs(revChange).toFixed(1)}% from ${last2[0].month} to ${last2[1].month}. Investigate if this is seasonal or if customer retention issues need to be addressed.`,
                        })
                    }
                }
            }
        }
    }

    // ── Stock alerts ──
    if (stats.inventory.out_of_stock > 0) {
        insights.push({
            type: 'danger',
            title: 'Out-of-Stock Items Detected',
            message: `${stats.inventory.out_of_stock} item(s) have zero stock remaining. Initiate emergency reorders immediately — delayed restocking risks disrupting active laundry orders.`,
        })
    }
    if (stats.inventory.low_stock > 0 && stats.inventory.out_of_stock === 0) {
        insights.push({
            type: 'warning',
            title: 'Low Stock Warning',
            message: `${stats.inventory.low_stock} item(s) are below their minimum stock level. Review your reorder points and contact suppliers before these items run out.`,
        })
    }

    // ── Movement trend ──
    const change = stats.movements.change
    if (change >= 20) {
        insights.push({
            type: 'info',
            title: 'High Inventory Movement Spike',
            message: `Inventory movements surged by ${change}% compared to last month. Verify this is demand-driven and not caused by data entry errors or unauthorised transfers.`,
        })
    } else if (change <= -20) {
        insights.push({
            type: 'warning',
            title: 'Inventory Movement Drop',
            message: `Inventory movements dropped by ${Math.abs(change)}% this month. This could indicate slowed operations, understaffing, or supply chain delays.`,
        })
    }

    // ── Overstock inflow check ──
    if (movChart.length >= 3) {
        const last3 = movChart.slice(-3)
        if (last3.every(m => m.stock_in > m.stock_out)) {
            insights.push({
                type: 'info',
                title: 'Consistent Inflow Surplus',
                message: `Stock-in has exceeded stock-out for 3+ consecutive months. Review storage capacity and reorder triggers — excess inventory may increase holding costs.`,
            })
        }
    }

    // ── Category concentration ──
    if (catData.length > 0) {
        const total = catData.reduce((s, c) => s + c.count, 0)
        const top   = [...catData].sort((a, b) => b.count - a.count)[0]
        const topPct = total > 0 ? Math.round((top.count / total) * 100) : 0
        if (topPct >= 40) {
            insights.push({
                type: 'warning',
                title: 'Category Concentration Risk',
                message: `"${top.label}" makes up ${topPct}% of your inventory. Heavy concentration increases risk if demand or supply shifts — consider diversifying your stock mix.`,
            })
        }
    }

    // ── Branch staffing imbalance ──
    if (branches.length >= 2) {
        const counts = branches.map(b => b.count)
        const maxC   = Math.max(...counts)
        const minC   = Math.min(...counts)
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

    // ── All stocked ──
    if (stats.inventory.out_of_stock === 0 && stats.inventory.low_stock === 0) {
        insights.push({
            type: 'success',
            title: 'Inventory Fully Stocked',
            message: `All items are at or above their minimum stock levels. Keep your reorder schedule consistent to sustain this.`,
        })
    }

    return insights
})

// ─── Insight config ───────────────────────────────────────────────────────────

const insightConfig = {
    success: { border: 'border-l-emerald-500', bg: 'bg-emerald-50 dark:bg-emerald-950/30', icon: CheckCircle2,  iconCls: 'text-emerald-600', title: 'text-emerald-700 dark:text-emerald-400' },
    warning: { border: 'border-l-amber-500',   bg: 'bg-amber-50 dark:bg-amber-950/30',     icon: AlertTriangle, iconCls: 'text-amber-600',   title: 'text-amber-700 dark:text-amber-400' },
    danger:  { border: 'border-l-red-500',     bg: 'bg-red-50 dark:bg-red-950/30',         icon: ShieldAlert,   iconCls: 'text-red-600',     title: 'text-red-700 dark:text-red-400' },
    info:    { border: 'border-l-blue-500',    bg: 'bg-blue-50 dark:bg-blue-950/30',       icon: Info,          iconCls: 'text-blue-600',    title: 'text-blue-700 dark:text-blue-400' },
}

// ─── Chart refs ───────────────────────────────────────────────────────────────

const ordersChartRef    = ref<HTMLCanvasElement | null>(null)
const revenueChartRef   = ref<HTMLCanvasElement | null>(null)
const serviceChartRef   = ref<HTMLCanvasElement | null>(null)
const orderStatusRef    = ref<HTMLCanvasElement | null>(null)
const movementChartRef  = ref<HTMLCanvasElement | null>(null)
const categoryChartRef  = ref<HTMLCanvasElement | null>(null)
const branchChartRef    = ref<HTMLCanvasElement | null>(null)

function loadScript(src: string): Promise<void> {
    return new Promise((resolve) => {
        if (document.querySelector(`script[src="${src}"]`)) return resolve()
        const script    = document.createElement('script')
        script.src      = src
        script.onload   = () => resolve()
        document.head.appendChild(script)
    })
}

onMounted(async () => {
    if (!isPaid.value) return
    await loadScript('https://cdn.jsdelivr.net/npm/chart.js')
    // @ts-ignore
    const Chart = window.Chart

    const orders12   = props.monthly_orders    ?? []
    const services   = props.service_popularity ?? []
    const statusDist = props.order_status_dist  ?? { pending: 0, in_progress: 0, completed: 0 }
    const movData    = props.movement_chart     ?? []
    const catData    = props.category_breakdown ?? []
    const branchData = props.employees_per_branch ?? []

    // Peak threshold — months >= 75% of max are peak (orange)
    const maxOrders    = Math.max(...orders12.map(d => d.total_orders), 1)
    const peakThreshold = maxOrders * 0.75

    /* ── Monthly Orders Bar (peak highlighted) ── */
    if (ordersChartRef.value && orders12.length) {
        new Chart(ordersChartRef.value, {
            type: 'bar',
            data: {
                labels: orders12.map(d => d.month),
                datasets: [{
                    label: 'Orders',
                    data: orders12.map(d => d.total_orders),
                    backgroundColor: orders12.map(d =>
                        d.total_orders >= peakThreshold && d.total_orders > 0
                            ? 'rgba(234,88,12,0.85)'
                            : 'rgba(99,102,241,0.70)'
                    ),
                    borderRadius: 5,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            afterLabel: (ctx: any) => {
                                const v = orders12[ctx.dataIndex]
                                return v.total_orders >= peakThreshold && v.total_orders > 0 ? '🔥 Peak Month' : ''
                            },
                        },
                    },
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                },
            },
        })
    }

    /* ── Revenue Trend Line ── */
    if (revenueChartRef.value) {
        new Chart(revenueChartRef.value, {
            type: 'line',
            data: {
                labels: orders12.map(d => d.month),
                datasets: [{
                    label: 'Revenue (₱)',
                    data: orders12.map(d => d.revenue),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.10)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#10b981',
                    pointHoverRadius: 6,
                }],
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
                            callback: (v: any) => `₱${Number(v).toLocaleString('en-PH')}`,
                        },
                        grid: { color: 'rgba(0,0,0,0.05)' },
                    },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                },
            },
        })
    }

    /* ── Service Popularity Horizontal Bar ── */
    if (serviceChartRef.value && services.length) {
        const palette = ['#6366f1','#10b981','#f59e0b','#ef4444','#3b82f6','#8b5cf6']
        new Chart(serviceChartRef.value, {
            type: 'bar',
            data: {
                labels: services.map(s => s.name),
                datasets: [{
                    label: 'Orders',
                    data: services.map(s => s.order_count),
                    backgroundColor: services.map((_, i) => palette[i % palette.length]),
                    borderRadius: 4,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.05)' } },
                    y: { grid: { display: false }, ticks: { font: { size: 10 } } },
                },
            },
        })
    }

    /* ── Order Status Donut ── */
    if (orderStatusRef.value) {
        new Chart(orderStatusRef.value, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'In Progress', 'Completed'],
                datasets: [{
                    data: [statusDist.pending, statusDist.in_progress, statusDist.completed],
                    backgroundColor: ['#f59e0b','#3b82f6','#10b981'],
                    borderWidth: 2,
                    borderColor: '#fff',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 10, font: { size: 10 }, boxWidth: 10, boxHeight: 10 } },
                },
                cutout: '60%',
            },
        })
    }

    /* ── Inventory Movement Bar ── */
    if (movementChartRef.value && movData.length) {
        new Chart(movementChartRef.value, {
            type: 'bar',
            data: {
                labels: movData.map(d => d.month),
                datasets: [
                    { label: 'Stock In',  data: movData.map(d => d.stock_in),  backgroundColor: 'rgba(16,185,129,0.75)', borderRadius: 4 },
                    { label: 'Stock Out', data: movData.map(d => d.stock_out), backgroundColor: 'rgba(239,68,68,0.75)',  borderRadius: 4 },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { font: { size: 11 }, boxWidth: 12, boxHeight: 12 } } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 11 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
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
                datasets: [{ data: catData.map(d => d.count), backgroundColor: colors.slice(0, catData.length), borderWidth: 2, borderColor: '#fff' }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 }, boxWidth: 12, boxHeight: 12 } } },
                cutout: '60%',
            },
        })
    }

    /* ── Employees per Branch ── */
    if (branchChartRef.value && branchData.length) {
        new Chart(branchChartRef.value, {
            type: 'bar',
            data: {
                labels: branchData.map(d => d.branch),
                datasets: [{
                    label: 'Employees',
                    data: branchData.map(d => d.count),
                    backgroundColor: branchData.map(d => d.status === 'Active' ? 'rgba(99,102,241,0.75)' : 'rgba(156,163,175,0.5)'),
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
    low:          { label: 'Low Stock',   cls: 'bg-amber-100 text-amber-700' },
    out_of_stock: { label: 'Out of Stock', cls: 'bg-red-100 text-red-700' },
    normal:       { label: 'Normal',      cls: 'bg-green-100 text-green-700' },
    overstock:    { label: 'Overstock',   cls: 'bg-blue-100 text-blue-700' },
}

const movementTypeConfig: Record<string, { label: string; cls: string }> = {
    in:       { label: 'Stock In',    cls: 'bg-emerald-100 text-emerald-700' },
    out:      { label: 'Stock Out',   cls: 'bg-red-100 text-red-700' },
    adjust:   { label: 'Adjustment', cls: 'bg-blue-100 text-blue-700' },
    transfer: { label: 'Transfer',   cls: 'bg-violet-100 text-violet-700' },
}

function formatPeso(v: number) {
    return v.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function proceedToPay() {
    if (!payForm.payment_method || payForm.submitting) return
    payForm.submitting = true

    // Use a real form POST so the server's external redirect (PayMongo) is followed correctly.
    // Inertia's router.post intercepts redirects via XHR and cannot navigate to external URLs.
    const form   = document.createElement('form')
    form.method  = 'POST'
    form.action  = '/shop/payment/pay'

    const csrf      = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
    const csrfField = document.createElement('input')
    csrfField.type  = 'hidden'
    csrfField.name  = '_token'
    csrfField.value = csrf
    form.appendChild(csrfField)

    const pmField   = document.createElement('input')
    pmField.type    = 'hidden'
    pmField.name    = 'payment_method'
    pmField.value   = payForm.payment_method
    form.appendChild(pmField)

    document.body.appendChild(form)
    form.submit()
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
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Shop Disabled</h2>
                        <p class="text-sm text-muted-foreground">
                            Your shop has been disabled. Please contact support for assistance.
                        </p>
                    </div>
                </div>
            </template>

            <!-- ══════════════ PENDING REVIEW ══════════════ -->
            <template v-else-if="isPending">
                <div class="flex flex-1 items-center justify-center min-h-[60vh] px-4">
                    <div class="w-full max-w-md text-center">
                        <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-4">
                            <Clock class="h-8 w-8 text-blue-500 animate-pulse" />
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">Order Under Review</h2>
                        <p class="text-sm text-muted-foreground mb-6">
                            Your <span class="font-semibold text-foreground">{{ props.pending_order?.plan_name }}</span> plan order is being reviewed by our admin team.
                            You'll be notified once it's approved.
                        </p>
                        <div class="rounded-xl border border-blue-200 bg-blue-50 dark:bg-blue-950/30 p-4 text-left space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Plan</span>
                                <span class="font-medium">{{ props.pending_order?.plan_name }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Duration</span>
                                <span class="font-medium">{{ props.pending_order?.billing_months }} month{{ (props.pending_order?.billing_months ?? 1) > 1 ? 's' : '' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Total</span>
                                <span class="font-semibold text-blue-700">₱{{ Number(props.pending_order?.total_price ?? 0).toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Submitted</span>
                                <span class="font-medium">{{ props.pending_order?.created_at }}</span>
                            </div>
                        </div>
                        <p class="text-xs text-muted-foreground mt-4">No payment is collected until your order is approved.</p>
                    </div>
                </div>
            </template>

            <!-- ══════════════ APPROVED — PAY NOW ══════════════ -->
            <template v-else-if="isApprovedNonTrial">
                <div class="flex flex-1 items-center justify-center min-h-[60vh] px-4">
                    <div class="w-full max-w-md">
                        <div class="text-center mb-6">
                            <div class="h-16 w-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                                <CheckCircle2 class="h-8 w-8 text-emerald-500" />
                            </div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Order Approved!</h2>
                            <p class="text-sm text-muted-foreground mt-1">
                                Complete your payment to activate your
                                <span class="font-semibold text-foreground">{{ props.approved_order?.plan_name }}</span> plan.
                            </p>
                        </div>

                        <div class="rounded-xl border bg-white dark:bg-muted p-5 space-y-4 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">Select Payment Method</p>
                            <div class="space-y-2">
                                <button
                                    v-for="method in PAYMENT_METHODS"
                                    :key="method.key"
                                    type="button"
                                    class="w-full flex items-center gap-3 p-3 rounded-xl border-2 text-left transition"
                                    :class="payForm.payment_method === method.key
                                        ? 'border-emerald-500 bg-emerald-50'
                                        : 'border-gray-200 hover:border-gray-300'"
                                    @click="payForm.payment_method = method.key"
                                >
                                    <img :src="method.icon" :alt="method.label" class="h-6 w-10 object-contain shrink-0" />
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium flex items-center gap-2"
                                            :class="payForm.payment_method === method.key ? 'text-emerald-700' : 'text-gray-700'">
                                            {{ method.label }}
                                            <span v-if="method.tag" class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700">{{ method.tag }}</span>
                                        </p>
                                        <p class="text-xs text-muted-foreground">{{ method.note }}</p>
                                    </div>
                                    <CheckCircle2 v-if="payForm.payment_method === method.key" class="h-4 w-4 text-emerald-500 shrink-0" />
                                </button>
                            </div>

                            <div class="border-t pt-3 flex justify-between text-sm font-semibold">
                                <span>Total due</span>
                                <span class="text-emerald-700">₱{{ Number(props.approved_order?.total_price ?? 0).toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}</span>
                            </div>

                            <Button
                                class="w-full gap-2 bg-emerald-600 hover:bg-emerald-700"
                                :disabled="!payForm.payment_method || payForm.submitting"
                                @click="proceedToPay"
                            >
                                <Loader2 v-if="payForm.submitting" class="h-4 w-4 animate-spin" />
                                <CreditCard v-else class="h-4 w-4" />
                                {{ payForm.submitting ? 'Redirecting...' : 'Pay Now' }}
                            </Button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- ══════════════ REJECTED ══════════════ -->
            <template v-else-if="isRejected">
                <CheckoutConfirm
                    v-if="showOrder"
                    plan-name="Standard" :vat-pct="12" :billing-months="1"
                    :user="{ name: user.name, email: user.email }"
                    :shop="props.shop ?? undefined"
                />
                <div v-else class="flex flex-1 items-center justify-center min-h-[60vh] px-4">
                    <div class="w-full max-w-lg">
                        <div class="flex flex-col items-center text-center mb-6">
                            <div class="h-16 w-16 rounded-full bg-red-100 flex items-center justify-center mb-4">
                                <XCircle class="h-8 w-8 text-red-500" />
                            </div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Order Rejected</h2>
                            <p class="text-sm text-muted-foreground mt-1">
                                Your subscription plan order for
                                <span class="font-medium text-foreground">{{ props.rejected_order?.shop_name }}</span>
                                was not approved.
                            </p>
                        </div>
                        <div class="rounded-xl border border-red-200 bg-red-50 dark:bg-red-950/30 dark:border-red-800 p-4 mb-4">
                            <p class="text-xs font-semibold text-red-600 uppercase tracking-wide mb-1">Reason for rejection</p>
                            <p class="text-sm text-red-700 dark:text-red-300 leading-relaxed">
                                {{ props.rejected_order?.rejection_reason ?? 'No specific reason was provided.' }}
                            </p>
                        </div>
                        <div class="rounded-xl border border-blue-200 bg-blue-50 dark:bg-blue-950/30 dark:border-blue-800 p-4 mb-6">
                            <div class="flex items-start gap-3">
                                <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                                    <Mail class="h-4 w-4 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-blue-700 mb-0.5">A notification has been sent to your email</p>
                                    <p class="text-xs text-blue-600 leading-relaxed">
                                        Details have been sent to <span class="font-medium">{{ props.rejected_order?.email }}</span>.
                                        No payment was collected. You may re-apply after addressing the reason above.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <Button class="w-full" @click="showOrder = true">Place a New Order</Button>
                        <p class="text-xs text-center text-muted-foreground mt-4">
                            If you believe this was a mistake, please contact our support team.
                        </p>
                    </div>
                </div>
            </template>

            <!-- ══════════════ EXPIRED ══════════════ -->
            <template v-else-if="isExpired">
                <div class="flex flex-1 items-center justify-center min-h-[60vh] px-4">
                    <div class="w-full max-w-md text-center">

                        <!-- Trial expired -->
                        <template v-if="props.expired_order?.is_trial">
                            <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-4">
                                <Clock class="h-8 w-8 text-blue-500" />
                            </div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">Your free trial has ended</h2>
                            <p class="text-sm text-muted-foreground mb-1">
                                Your 7-day free trial has expired.
                            </p>
                            <p v-if="props.expired_order?.expires_at" class="text-xs text-muted-foreground mb-6">
                                Ended on {{ new Date(props.expired_order.expires_at).toLocaleDateString('en-PH', { month: 'long', day: 'numeric', year: 'numeric' }) }}
                            </p>
                            <p v-else class="mb-6" />
                            <div class="rounded-xl border border-blue-200 bg-blue-50 dark:bg-blue-950/30 p-4 mb-6 text-left">
                                <p class="text-sm text-blue-700 leading-relaxed">
                                    Your shop is currently <span class="font-semibold">inactive</span> and hidden from customers.
                                    Choose a plan to continue using LaundryHub.
                                </p>
                            </div>
                            <a href="/shop/upgrade">
                                <Button class="w-full gap-2">Choose a Plan</Button>
                            </a>
                        </template>

                        <!-- Paid plan expired -->
                        <template v-else>
                            <div class="h-16 w-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
                                <RotateCcw class="h-8 w-8 text-amber-500" />
                            </div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">Subscription Expired</h2>
                            <p class="text-sm text-muted-foreground mb-1">
                                Your <span class="font-semibold text-foreground">{{ props.expired_order?.plan_name ?? 'plan' }}</span> subscription has expired.
                            </p>
                            <p v-if="props.expired_order?.expires_at" class="text-xs text-muted-foreground mb-6">
                                Expired on {{ new Date(props.expired_order.expires_at).toLocaleDateString('en-PH', { month: 'long', day: 'numeric', year: 'numeric' }) }}
                            </p>
                            <p v-else class="mb-6" />
                            <div class="rounded-xl border border-amber-200 bg-amber-50 dark:bg-amber-950/30 p-4 mb-6 text-left">
                                <p class="text-sm text-amber-700 leading-relaxed">
                                    Your shop is currently <span class="font-semibold">inactive</span> and hidden from customers.
                                    Renew your plan to restore full access.
                                </p>
                            </div>
                            <a href="/shop/upgrade">
                                <Button class="w-full gap-2"><RotateCcw class="h-4 w-4" /> Renew Plan</Button>
                            </a>
                            <p class="text-xs text-muted-foreground mt-4">You can also upgrade to a higher plan if needed.</p>
                        </template>

                    </div>
                </div>
            </template>

            <!-- ══════════════ APPROVED: Live Dashboard ══════════════ -->
            <template v-else-if="isApproved">

                <!-- Trial Banner -->
                <div v-if="props.order?.is_trial"
                    class="rounded-xl border px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                    :class="(props.order.trial_days_left ?? 0) <= 1
                        ? 'border-red-200 bg-red-50'
                        : (props.order.trial_days_left ?? 0) <= 3
                            ? 'border-amber-200 bg-amber-50'
                            : 'border-blue-200 bg-blue-50'"
                >
                    <div class="flex items-center gap-3">
                        <Clock class="h-5 w-5 shrink-0"
                            :class="(props.order.trial_days_left ?? 0) <= 1 ? 'text-red-500' : (props.order.trial_days_left ?? 0) <= 3 ? 'text-amber-500' : 'text-blue-500'" />
                        <div>
                            <p class="text-sm font-semibold"
                                :class="(props.order.trial_days_left ?? 0) <= 1 ? 'text-red-800' : (props.order.trial_days_left ?? 0) <= 3 ? 'text-amber-800' : 'text-blue-800'">
                                <span v-if="(props.order.trial_days_left ?? 0) === 0">Your free trial expires today</span>
                                <span v-else>{{ props.order.trial_days_left }} day{{ props.order.trial_days_left === 1 ? '' : 's' }} left in your free trial</span>
                            </p>
                            <p class="text-xs mt-0.5"
                                :class="(props.order.trial_days_left ?? 0) <= 1 ? 'text-red-600' : (props.order.trial_days_left ?? 0) <= 3 ? 'text-amber-600' : 'text-blue-600'">
                                Subscribe now to keep your shop running after the trial ends.
                            </p>
                        </div>
                    </div>
                    <Button size="sm" @click="router.visit('/shop/upgrade')"
                        :class="(props.order.trial_days_left ?? 0) <= 1
                            ? 'bg-red-600 hover:bg-red-700 text-white'
                            : (props.order.trial_days_left ?? 0) <= 3
                                ? 'bg-amber-500 hover:bg-amber-600 text-white'
                                : 'bg-blue-600 hover:bg-blue-700 text-white'"
                        class="shrink-0 border-0">
                        Subscribe Now
                    </Button>
                </div>

                <!-- Welcome Banner -->
                <div class="rounded-xl border border-border bg-gradient-to-r from-indigo-50 to-emerald-50 dark:from-neutral-800 dark:to-neutral-800 p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                            Welcome back, {{ user.name }} 👋
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            Live overview of <span class="font-semibold text-gray-800 dark:text-white">{{ props.order?.shop_name }}</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-muted-foreground shrink-0">
                        <span class="px-2.5 py-1 rounded-full font-medium capitalize"
                            :class="props.order?.is_trial ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700'">
                            {{ props.order?.is_trial ? 'Free Trial' : (props.order?.subscription_plan ?? 'Active') }}
                        </span>
                        <span v-if="props.order?.expires_at">
                            Expires {{ new Date(props.order.expires_at).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' }) }}
                        </span>
                    </div>
                </div>

                <!-- ── 1. ORDER KPI CARDS ── -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-3 flex items-center gap-2">
                        <ShoppingBag class="h-3.5 w-3.5" /> Laundry Operations
                    </p>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

                        <Card>
                            <CardContent class="pt-5">
                                <div class="flex items-center justify-between mb-3">
                                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Orders</p>
                                    <div class="h-8 w-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                                        <ShoppingBag class="h-4 w-4 text-indigo-600" />
                                    </div>
                                </div>
                                <p class="text-3xl font-bold">{{ props.order_stats?.total ?? 0 }}</p>
                                <div class="flex items-center gap-1 mt-1.5">
                                    <ArrowUpRight v-if="(props.order_stats?.orders_change ?? 0) >= 0" class="h-3.5 w-3.5 text-emerald-500" />
                                    <ArrowDownRight v-else class="h-3.5 w-3.5 text-red-500" />
                                    <span class="text-xs font-medium" :class="(props.order_stats?.orders_change ?? 0) >= 0 ? 'text-emerald-500' : 'text-red-500'">
                                        {{ Math.abs(props.order_stats?.orders_change ?? 0) }}%
                                    </span>
                                    <span class="text-xs text-muted-foreground">vs last month</span>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardContent class="pt-5">
                                <div class="flex items-center justify-between mb-3">
                                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Revenue</p>
                                    <div class="h-8 w-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                                        <TrendingUp class="h-4 w-4 text-emerald-600" />
                                    </div>
                                </div>
                                <p class="text-2xl font-bold">₱{{ formatPeso(props.order_stats?.total_revenue ?? 0) }}</p>
                                <p class="text-xs text-muted-foreground mt-1.5">
                                    Avg ₱{{ formatPeso(props.order_stats?.avg_order_value ?? 0) }} / order
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardContent class="pt-5">
                                <div class="flex items-center justify-between mb-3">
                                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Completion Rate</p>
                                    <div class="h-8 w-8 rounded-lg bg-violet-100 flex items-center justify-center">
                                        <Percent class="h-4 w-4 text-violet-600" />
                                    </div>
                                </div>
                                <p class="text-3xl font-bold" :class="(props.order_stats?.completion_rate ?? 0) >= 75 ? 'text-emerald-600' : 'text-amber-600'">
                                    {{ props.order_stats?.completion_rate ?? 0 }}%
                                </p>
                                <p class="text-xs text-muted-foreground mt-1.5">
                                    {{ props.order_stats?.completed ?? 0 }} of {{ props.order_stats?.total ?? 0 }} completed
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardContent class="pt-5">
                                <div class="flex items-center justify-between mb-3">
                                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Pending Orders</p>
                                    <div class="h-8 w-8 rounded-lg flex items-center justify-center"
                                        :class="(props.order_stats?.pending ?? 0) > 5 ? 'bg-amber-100' : 'bg-blue-100'">
                                        <Flame class="h-4 w-4" :class="(props.order_stats?.pending ?? 0) > 5 ? 'text-amber-600' : 'text-blue-600'" />
                                    </div>
                                </div>
                                <p class="text-3xl font-bold" :class="(props.order_stats?.pending ?? 0) > 5 ? 'text-amber-600' : ''">
                                    {{ props.order_stats?.pending ?? 0 }}
                                </p>
                                <p class="text-xs text-muted-foreground mt-1.5">
                                    {{ props.order_stats?.in_progress ?? 0 }} in progress
                                </p>
                            </CardContent>
                        </Card>

                    </div>
                </div>

                <!-- ── 2. ORDER CHARTS ROW 1: Monthly Orders + Order Status ── -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <!-- Monthly Orders Bar with peak highlighting -->
                    <Card class="md:col-span-2">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-semibold flex items-center gap-2">
                                <BarChart2 class="h-4 w-4 text-muted-foreground" />
                                Monthly Order Volume
                                <span class="ml-auto flex items-center gap-1 text-xs font-normal text-muted-foreground">
                                    <span class="inline-block h-2.5 w-2.5 rounded-sm bg-orange-500 opacity-85"></span> Peak &nbsp;
                                    <span class="inline-block h-2.5 w-2.5 rounded-sm bg-indigo-500 opacity-70"></span> Normal
                                </span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="(props.monthly_orders?.every(d => d.total_orders === 0))"
                                class="flex items-center justify-center h-48 text-sm text-muted-foreground">
                                No order data yet.
                            </div>
                            <div v-else class="relative h-[220px]">
                                <canvas ref="ordersChartRef"></canvas>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Order Status Donut -->
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-semibold flex items-center gap-2">
                                <ShoppingBag class="h-4 w-4 text-muted-foreground" />
                                Order Status
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="flex items-center justify-center">
                            <div v-if="(props.order_stats?.total ?? 0) === 0"
                                class="flex items-center justify-center h-48 text-sm text-muted-foreground">
                                No orders yet.
                            </div>
                            <div v-else class="relative w-full h-[220px]">
                                <canvas ref="orderStatusRef"></canvas>
                            </div>
                        </CardContent>
                    </Card>

                </div>

                <!-- ── 3. ORDER CHARTS ROW 2: Revenue Trend + Service Popularity ── -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <!-- Revenue Trend Line -->
                    <Card class="md:col-span-2">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-semibold flex items-center gap-2">
                                <TrendingUp class="h-4 w-4 text-muted-foreground" />
                                Monthly Revenue Trend (12 months)
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="(props.monthly_orders?.every(d => d.revenue === 0))"
                                class="flex items-center justify-center h-48 text-sm text-muted-foreground">
                                No revenue data yet.
                            </div>
                            <div v-else class="relative h-[220px]">
                                <canvas ref="revenueChartRef"></canvas>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Service Popularity -->
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-semibold flex items-center gap-2">
                                <Activity class="h-4 w-4 text-muted-foreground" />
                                Service Popularity
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="flex items-center justify-center">
                            <div v-if="(props.service_popularity?.length ?? 0) === 0"
                                class="flex items-center justify-center h-48 text-sm text-muted-foreground">
                                No service data yet.
                            </div>
                            <div v-else class="relative w-full h-[220px]">
                                <canvas ref="serviceChartRef"></canvas>
                            </div>
                        </CardContent>
                    </Card>

                </div>

                <!-- ── 4. DSS INSIGHT CARDS ── -->
                <div v-if="dssInsights.length > 0">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="h-7 w-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                            <Lightbulb class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <h3 class="text-sm font-semibold">Decision Support</h3>
                        <span class="text-xs text-muted-foreground">— recommendations based on your business data</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                        <div v-for="(insight, i) in dssInsights" :key="i"
                            class="rounded-xl border-l-4 p-4 flex gap-3"
                            :class="[insightConfig[insight.type].bg, insightConfig[insight.type].border]">
                            <div class="mt-0.5 shrink-0">
                                <component :is="insightConfig[insight.type].icon" class="h-4 w-4"
                                    :class="insightConfig[insight.type].iconCls" />
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 mb-1.5">
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full uppercase tracking-wide"
                                        :class="{
                                            'bg-emerald-100 text-emerald-700': insight.type === 'success',
                                            'bg-amber-100 text-amber-700':     insight.type === 'warning',
                                            'bg-red-100 text-red-700':         insight.type === 'danger',
                                            'bg-blue-100 text-blue-700':       insight.type === 'info',
                                        }">
                                        {{ insight.type === 'danger' ? 'Critical' : insight.type === 'warning' ? 'Warning' : insight.type === 'success' ? 'Healthy' : 'Info' }}
                                    </span>
                                </div>
                                <p class="text-xs font-semibold mb-1" :class="insightConfig[insight.type].title">
                                    {{ insight.title }}
                                </p>
                                <p class="text-xs text-muted-foreground leading-relaxed">{{ insight.message }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- paid not approved: Waiting -->
            <template v-else-if="isPaid && !isApproved">
                <div class="flex flex-1 items-center justify-center min-h-[60vh]">
                    <div class="text-center max-w-md">
                        <div class="h-16 w-16 rounded-full bg-yellow-100 flex items-center justify-center mx-auto mb-4">
                            <Clock class="h-8 w-8 text-yellow-500" />
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Awaiting Admin Approval</h2>
                        <p class="text-sm text-muted-foreground">
                            Your payment has been received. Please wait while our admin reviews and approves your account.
                            You'll have full access once approved.
                        </p>
                    </div>
                </div>
            </template>

            <!-- NOT PAID: Choose path -->
            <template v-else>
                <!-- Choice screen -->
                <div v-if="!showOrder" class="flex flex-1 items-start justify-center min-h-[60vh] px-4 pt-8">
                    <div class="w-full max-w-2xl">
                        <div class="text-center mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome, {{ user.name }} 👋</h2>
                            <p class="text-sm text-muted-foreground mt-2">Choose how you'd like to get started with LaundryHub.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Free Trial card (hidden if already used) -->
                            <button v-if="!props.has_used_trial"
                                class="group relative flex flex-col items-start gap-3 rounded-2xl border-2 border-blue-200 bg-blue-50 dark:bg-blue-950/30 dark:border-blue-800 p-6 text-left transition hover:border-blue-400 hover:shadow-md focus:outline-none"
                                @click="showTrialConfirm = true"
                            >
                                <div class="h-12 w-12 rounded-xl bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                                    <Lightbulb class="h-6 w-6 text-blue-600" />
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">Start Free Trial</p>
                                    <p class="text-sm text-muted-foreground mt-1 leading-relaxed">
                                        Try all features free for 7 days — no payment required.
                                    </p>
                                </div>
                                <span class="absolute top-4 right-4 text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300">FREE</span>
                            </button>

                            <!-- Subscribe card -->
                            <button
                                class="group flex flex-col items-start gap-3 rounded-2xl border-2 border-emerald-200 bg-emerald-50 dark:bg-emerald-950/30 dark:border-emerald-800 p-6 text-left transition hover:border-emerald-400 hover:shadow-md focus:outline-none"
                                :class="props.has_used_trial ? 'sm:col-span-2' : ''"
                                @click="showOrder = true"
                            >
                                <div class="h-12 w-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center">
                                    <CreditCard class="h-6 w-6 text-emerald-600" />
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">Subscribe to a Plan</p>
                                    <p class="text-sm text-muted-foreground mt-1 leading-relaxed">
                                        Choose Basic, Standard, or Premium and submit your application.
                                    </p>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <CheckoutConfirm v-if="showOrder" plan-name="Standard" :vat-pct="12" :billing-months="1"
                    :user="{ name: user.name, email: user.email }" :shop="props.shop ?? undefined" />
            </template>

        </div>

        <!-- Free Trial Confirmation Dialog -->
        <Dialog v-model:open="showTrialConfirm">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <div class="h-12 w-12 rounded-xl bg-blue-100 flex items-center justify-center mb-3">
                        <Lightbulb class="h-6 w-6 text-blue-600" />
                    </div>
                    <DialogTitle class="text-lg">Start Your Free Trial?</DialogTitle>
                    <DialogDescription class="text-sm leading-relaxed mt-1">
                        You're about to activate a <span class="font-semibold text-foreground">7-day free trial</span> with access to all LaundryHub features — no payment required.
                        <br /><br />
                        Please note that you can only use the free trial <span class="font-semibold text-foreground">once</span>. After it expires, you'll need to subscribe to continue using the platform.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2 sm:gap-0 mt-2">
                    <Button variant="outline" @click="showTrialConfirm = false">Cancel</Button>
                    <Button class="bg-blue-600 hover:bg-blue-700 text-white" @click="showTrialConfirm = false; router.visit('/trial')">
                        <Lightbulb class="h-4 w-4 mr-1.5" /> Activate Free Trial
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

    </ShopLayout>
</template>
