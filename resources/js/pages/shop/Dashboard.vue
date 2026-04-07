<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, usePage } from '@inertiajs/vue3'
import { type AppPageProps } from '@/types'
import { computed } from 'vue'

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
    Users, TrendingUp, TrendingDown, Minus,
    CalendarCheck, Banknote, AlertTriangle,
    Clock, Lightbulb, CheckCircle2, Info, XCircle,
    ArrowUpRight, ArrowDownRight,
} from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface KPIs {
    total_employees: number
    employee_change: number
    attendance_rate: number
    attendance_change: number
    absenteeism_rate: number
    late_rate: number
    payroll_cost: number
    payroll_change: number
    cost_per_employee: number
    half_day_count: number
    absent_count: number
    late_count: number
    present_count: number
}

interface TrendPoint {
    date: string
    label: string
    present: number
    absent: number
    late: number
    half_day: number
}

interface PayrollPoint {
    label: string
    net_pay: number
    deductions: number
    bonuses: number
    employees: number
}

interface EmployeePerformance {
    id: number
    name: string
    position: string
    branch: string | null
    total: number
    present: number
    absent: number
    late: number
    rate: number
}

interface Insight {
    type: 'success' | 'warning' | 'danger' | 'info'
    title: string
    message: string
    action: string
}

interface Shop {
    id: number
    shop_name: string
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    kpis: KPIs
    attendanceTrend: TrendPoint[]
    payrollTrend: PayrollPoint[]
    performance: EmployeePerformance[]
    insights: Insight[]
    shop: Shop
}>()

const page = usePage<AppPageProps>()
const isOwner = computed(() => page.props.auth.user.role === 'owner')

// ─── Chart Helpers ────────────────────────────────────────────────────────────

function formatCurrency(val: number): string {
    return `₱${val.toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`
}

// Attendance chart dimensions
const attChartWidth = 700
const attChartHeight = 200
const attPadding = { top: 20, right: 20, bottom: 30, left: 35 }

const attMaxVal = computed(() => {
    if (!props.attendanceTrend.length) return 10
    const max = Math.max(...props.attendanceTrend.map(d => d.present + d.absent + d.late + d.half_day))
    return Math.max(max, 1)
})

function attBarX(index: number): number {
    const usable = attChartWidth - attPadding.left - attPadding.right
    const barWidth = usable / props.attendanceTrend.length
    return attPadding.left + (index * barWidth) + (barWidth * 0.15)
}

function attBarWidth(): number {
    const usable = attChartWidth - attPadding.left - attPadding.right
    return (usable / Math.max(props.attendanceTrend.length, 1)) * 0.7
}

function attBarHeight(val: number): number {
    const usable = attChartHeight - attPadding.top - attPadding.bottom
    return (val / attMaxVal.value) * usable
}

function attBarY(val: number): number {
    return attChartHeight - attPadding.bottom - attBarHeight(val)
}

// Payroll chart
const payChartWidth = 700
const payChartHeight = 200
const payPadding = { top: 20, right: 20, bottom: 30, left: 55 }

const payMaxVal = computed(() => {
    if (!props.payrollTrend.length) return 10000
    return Math.max(...props.payrollTrend.map(d => d.net_pay)) * 1.15
})

function payLinePoints(): string {
    if (!props.payrollTrend.length) return ''
    const usableW = payChartWidth - payPadding.left - payPadding.right
    const usableH = payChartHeight - payPadding.top - payPadding.bottom

    return props.payrollTrend.map((d, i) => {
        const x = payPadding.left + (i / Math.max(props.payrollTrend.length - 1, 1)) * usableW
        const y = payChartHeight - payPadding.bottom - (d.net_pay / payMaxVal.value) * usableH
        return `${x},${y}`
    }).join(' ')
}

function payDotX(index: number): number {
    const usableW = payChartWidth - payPadding.left - payPadding.right
    return payPadding.left + (index / Math.max(props.payrollTrend.length - 1, 1)) * usableW
}

function payDotY(val: number): number {
    const usableH = payChartHeight - payPadding.top - payPadding.bottom
    return payChartHeight - payPadding.bottom - (val / payMaxVal.value) * usableH
}

// Y-axis labels
const attYLabels = computed(() => {
    const steps = 4
    const labels = []
    for (let i = 0; i <= steps; i++) {
        labels.push(Math.round((attMaxVal.value / steps) * i))
    }
    return labels
})

const payYLabels = computed(() => {
    const steps = 4
    const labels = []
    for (let i = 0; i <= steps; i++) {
        const val = (payMaxVal.value / steps) * i
        labels.push(val >= 1000 ? `₱${(val / 1000).toFixed(0)}k` : `₱${val.toFixed(0)}`)
    }
    return labels
})

// Insight config
const insightConfig: Record<string, { icon: any; bg: string; border: string; text: string }> = {
    success: { icon: CheckCircle2, bg: 'bg-green-50', border: 'border-green-200', text: 'text-green-800' },
    warning: { icon: AlertTriangle, bg: 'bg-amber-50', border: 'border-amber-200', text: 'text-amber-800' },
    danger:  { icon: XCircle, bg: 'bg-red-50', border: 'border-red-200', text: 'text-red-800' },
    info:    { icon: Info, bg: 'bg-blue-50', border: 'border-blue-200', text: 'text-blue-800' },
}

// Performance color
function perfColor(rate: number): string {
    if (rate >= 90) return 'bg-green-500'
    if (rate >= 75) return 'bg-amber-500'
    return 'bg-red-500'
}

function perfTextColor(rate: number): string {
    if (rate >= 90) return 'text-green-700'
    if (rate >= 75) return 'text-amber-700'
    return 'text-red-700'
}
</script>

<template>
    <Head title="Dashboard" />

    <ShopLayout title="Dashboard">
        <div class="px-6 space-y-6">

            <!-- ── Header ──────────────────────────────────────── -->
            <div>
                <h2 class="text-lg font-semibold">Decision Support Dashboard</h2>
                <p class="text-sm text-muted-foreground">
                    Key performance indicators and insights for <span class="font-medium text-foreground">{{ shop.shop_name }}</span>.
                </p>
            </div>

            <!-- ── KPI Cards ───────────────────────────────────── -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

                <!-- Active Employees -->
                <Card>
                    <CardContent class="pt-5 pb-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                <Users class="h-5 w-5 text-blue-600" />
                            </div>
                            <span v-if="kpis.employee_change !== 0"
                                class="flex items-center gap-0.5 text-xs font-semibold"
                                :class="kpis.employee_change > 0 ? 'text-green-600' : 'text-red-600'">
                                <ArrowUpRight v-if="kpis.employee_change > 0" class="h-3 w-3" />
                                <ArrowDownRight v-else class="h-3 w-3" />
                                {{ Math.abs(kpis.employee_change) }}
                            </span>
                        </div>
                        <p class="text-2xl font-bold">{{ kpis.total_employees }}</p>
                        <p class="text-xs text-muted-foreground mt-0.5">Active Employees</p>
                    </CardContent>
                </Card>

                <!-- Attendance Rate -->
                <Card>
                    <CardContent class="pt-5 pb-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="h-10 w-10 rounded-lg flex items-center justify-center"
                                :class="kpis.attendance_rate >= 90 ? 'bg-green-100' : kpis.attendance_rate >= 75 ? 'bg-amber-100' : 'bg-red-100'">
                                <CalendarCheck class="h-5 w-5"
                                    :class="kpis.attendance_rate >= 90 ? 'text-green-600' : kpis.attendance_rate >= 75 ? 'text-amber-600' : 'text-red-600'" />
                            </div>
                            <span v-if="kpis.attendance_change !== 0"
                                class="flex items-center gap-0.5 text-xs font-semibold"
                                :class="kpis.attendance_change > 0 ? 'text-green-600' : 'text-red-600'">
                                <ArrowUpRight v-if="kpis.attendance_change > 0" class="h-3 w-3" />
                                <ArrowDownRight v-else class="h-3 w-3" />
                                {{ Math.abs(kpis.attendance_change) }}%
                            </span>
                        </div>
                        <p class="text-2xl font-bold">{{ kpis.attendance_rate }}%</p>
                        <p class="text-xs text-muted-foreground mt-0.5">Attendance Rate</p>
                    </CardContent>
                </Card>

                <!-- Tardiness Rate -->
                <Card>
                    <CardContent class="pt-5 pb-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="h-10 w-10 rounded-lg flex items-center justify-center"
                                :class="kpis.late_rate <= 5 ? 'bg-green-100' : kpis.late_rate <= 15 ? 'bg-amber-100' : 'bg-red-100'">
                                <Clock class="h-5 w-5"
                                    :class="kpis.late_rate <= 5 ? 'text-green-600' : kpis.late_rate <= 15 ? 'text-amber-600' : 'text-red-600'" />
                            </div>
                            <span class="text-xs text-muted-foreground">{{ kpis.late_count }} late</span>
                        </div>
                        <p class="text-2xl font-bold">{{ kpis.late_rate }}%</p>
                        <p class="text-xs text-muted-foreground mt-0.5">Tardiness Rate</p>
                    </CardContent>
                </Card>

                <!-- Payroll Cost -->
                <Card>
                    <CardContent class="pt-5 pb-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="h-10 w-10 rounded-lg bg-purple-100 flex items-center justify-center">
                                <Banknote class="h-5 w-5 text-purple-600" />
                            </div>
                            <span v-if="kpis.payroll_change !== 0"
                                class="flex items-center gap-0.5 text-xs font-semibold"
                                :class="kpis.payroll_change > 0 ? 'text-red-600' : 'text-green-600'">
                                <ArrowUpRight v-if="kpis.payroll_change > 0" class="h-3 w-3" />
                                <ArrowDownRight v-else class="h-3 w-3" />
                                {{ Math.abs(kpis.payroll_change) }}%
                            </span>
                        </div>
                        <p class="text-2xl font-bold">{{ formatCurrency(kpis.payroll_cost) }}</p>
                        <p class="text-xs text-muted-foreground mt-0.5">Payroll This Month</p>
                    </CardContent>
                </Card>

                <!-- Absenteeism Rate -->
                <Card>
                    <CardContent class="pt-5 pb-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="h-10 w-10 rounded-lg flex items-center justify-center"
                                :class="kpis.absenteeism_rate <= 5 ? 'bg-green-100' : kpis.absenteeism_rate <= 10 ? 'bg-amber-100' : 'bg-red-100'">
                                <XCircle class="h-5 w-5"
                                    :class="kpis.absenteeism_rate <= 5 ? 'text-green-600' : kpis.absenteeism_rate <= 10 ? 'text-amber-600' : 'text-red-600'" />
                            </div>
                            <span class="text-xs text-muted-foreground">{{ kpis.absent_count }} absent</span>
                        </div>
                        <p class="text-2xl font-bold">{{ kpis.absenteeism_rate }}%</p>
                        <p class="text-xs text-muted-foreground mt-0.5">Absenteeism Rate</p>
                    </CardContent>
                </Card>

                <!-- Cost Per Employee -->
                <Card>
                    <CardContent class="pt-5 pb-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="h-10 w-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                                <Banknote class="h-5 w-5 text-indigo-600" />
                            </div>
                        </div>
                        <p class="text-2xl font-bold">{{ formatCurrency(kpis.cost_per_employee) }}</p>
                        <p class="text-xs text-muted-foreground mt-0.5">Avg. Cost / Employee</p>
                    </CardContent>
                </Card>
            </div>

            <!-- ── Charts Row ──────────────────────────────────── -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Attendance Trend Chart -->
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-semibold">Attendance Trend (Last 14 Days)</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="attendanceTrend.length === 0" class="text-center py-10 text-sm text-muted-foreground">
                            No attendance data available.
                        </div>
                        <svg v-else :viewBox="`0 0 ${attChartWidth} ${attChartHeight}`" class="w-full h-auto">
                            <!-- Y-axis grid lines -->
                            <template v-for="(label, i) in attYLabels" :key="'ay-' + i">
                                <line
                                    :x1="attPadding.left" :x2="attChartWidth - attPadding.right"
                                    :y1="attChartHeight - attPadding.bottom - ((i / (attYLabels.length - 1)) * (attChartHeight - attPadding.top - attPadding.bottom))"
                                    :y2="attChartHeight - attPadding.bottom - ((i / (attYLabels.length - 1)) * (attChartHeight - attPadding.top - attPadding.bottom))"
                                    stroke="currentColor" class="text-muted/30" stroke-dasharray="4" />
                                <text
                                    :x="attPadding.left - 5"
                                    :y="attChartHeight - attPadding.bottom - ((i / (attYLabels.length - 1)) * (attChartHeight - attPadding.top - attPadding.bottom)) + 4"
                                    text-anchor="end" class="fill-muted-foreground text-[9px]">
                                    {{ label }}
                                </text>
                            </template>

                            <!-- Stacked bars -->
                            <template v-for="(d, i) in attendanceTrend" :key="'att-' + i">
                                <!-- Present (green) -->
                                <rect :x="attBarX(i)" :y="attBarY(d.present + d.late + d.absent + d.half_day)"
                                    :width="attBarWidth()" :height="attBarHeight(d.present)"
                                    rx="2" class="fill-green-500/80" />
                                <!-- Late (amber) -->
                                <rect :x="attBarX(i)" :y="attBarY(d.late + d.absent + d.half_day)"
                                    :width="attBarWidth()" :height="attBarHeight(d.late)"
                                    rx="0" class="fill-amber-500/80" />
                                <!-- Absent (red) -->
                                <rect :x="attBarX(i)" :y="attBarY(d.absent + d.half_day)"
                                    :width="attBarWidth()" :height="attBarHeight(d.absent)"
                                    rx="0" class="fill-red-500/80" />
                                <!-- Half Day (blue) -->
                                <rect :x="attBarX(i)" :y="attBarY(d.half_day)"
                                    :width="attBarWidth()" :height="attBarHeight(d.half_day)"
                                    rx="0" class="fill-blue-500/80" />

                                <!-- X label -->
                                <text v-if="i % 2 === 0"
                                    :x="attBarX(i) + attBarWidth() / 2"
                                    :y="attChartHeight - 8"
                                    text-anchor="middle" class="fill-muted-foreground text-[8px]">
                                    {{ d.label }}
                                </text>
                            </template>
                        </svg>

                        <!-- Legend -->
                        <div class="flex items-center justify-center gap-4 mt-2 text-xs text-muted-foreground">
                            <span class="flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-sm bg-green-500" /> Present</span>
                            <span class="flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-sm bg-amber-500" /> Late</span>
                            <span class="flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-sm bg-red-500" /> Absent</span>
                            <span class="flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-sm bg-blue-500" /> Half Day</span>
                        </div>

                        <!-- Decision Note -->
                        <div class="mt-3 rounded-lg border bg-muted/30 px-3 py-2 text-xs text-muted-foreground">
                            <Lightbulb class="inline h-3 w-3 mr-1 -mt-0.5 text-amber-500" />
                            <strong>Decision Note:</strong> Look for patterns — consistent absences on specific days may indicate scheduling issues.
                            A healthy shop targets 90%+ attendance. Spikes in tardiness may warrant shift time adjustments.
                        </div>
                    </CardContent>
                </Card>

                <!-- Payroll Trend Chart -->
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-semibold">Payroll Cost Trend</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="payrollTrend.length === 0" class="text-center py-10 text-sm text-muted-foreground">
                            No finalized payroll data available.
                        </div>
                        <svg v-else :viewBox="`0 0 ${payChartWidth} ${payChartHeight}`" class="w-full h-auto">
                            <!-- Y-axis -->
                            <template v-for="(label, i) in payYLabels" :key="'py-' + i">
                                <line
                                    :x1="payPadding.left" :x2="payChartWidth - payPadding.right"
                                    :y1="payChartHeight - payPadding.bottom - ((i / (payYLabels.length - 1)) * (payChartHeight - payPadding.top - payPadding.bottom))"
                                    :y2="payChartHeight - payPadding.bottom - ((i / (payYLabels.length - 1)) * (payChartHeight - payPadding.top - payPadding.bottom))"
                                    stroke="currentColor" class="text-muted/30" stroke-dasharray="4" />
                                <text
                                    :x="payPadding.left - 5"
                                    :y="payChartHeight - payPadding.bottom - ((i / (payYLabels.length - 1)) * (payChartHeight - payPadding.top - payPadding.bottom)) + 4"
                                    text-anchor="end" class="fill-muted-foreground text-[9px]">
                                    {{ label }}
                                </text>
                            </template>

                            <!-- Area fill -->
                            <polygon
                                :points="`${payPadding.left},${payChartHeight - payPadding.bottom} ${payLinePoints()} ${payChartWidth - payPadding.right},${payChartHeight - payPadding.bottom}`"
                                class="fill-purple-500/10" />

                            <!-- Line -->
                            <polyline :points="payLinePoints()" fill="none" stroke-width="2.5"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="stroke-purple-500" />

                            <!-- Dots + labels -->
                            <template v-for="(d, i) in payrollTrend" :key="'pd-' + i">
                                <circle :cx="payDotX(i)" :cy="payDotY(d.net_pay)" r="4"
                                    class="fill-purple-500 stroke-white" stroke-width="2" />
                                <text :x="payDotX(i)" :y="payDotY(d.net_pay) - 10"
                                    text-anchor="middle" class="fill-foreground text-[8px] font-semibold">
                                    {{ formatCurrency(d.net_pay) }}
                                </text>
                                <text :x="payDotX(i)" :y="payChartHeight - 8"
                                    text-anchor="middle" class="fill-muted-foreground text-[7px]">
                                    {{ d.label.length > 12 ? d.label.slice(0, 12) + '…' : d.label }}
                                </text>
                            </template>
                        </svg>

                        <!-- Decision Note -->
                        <div class="mt-3 rounded-lg border bg-muted/30 px-3 py-2 text-xs text-muted-foreground">
                            <Lightbulb class="inline h-3 w-3 mr-1 -mt-0.5 text-amber-500" />
                            <strong>Decision Note:</strong> Monitor payroll trends against revenue. If payroll costs rise faster than income,
                            consider optimizing schedules or reviewing overtime. A stable or declining cost-per-employee indicates efficiency.
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- ── Employee Performance Table ──────────────────── -->
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-semibold">Employee Attendance Performance (This Month)</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="performance.length === 0" class="text-center py-10 text-sm text-muted-foreground">
                        No employee data available.
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-xs text-muted-foreground border-b">
                                    <th class="text-left px-4 py-2 font-medium">Employee</th>
                                    <th class="text-center px-4 py-2 font-medium">Present</th>
                                    <th class="text-center px-4 py-2 font-medium">Absent</th>
                                    <th class="text-center px-4 py-2 font-medium">Late</th>
                                    <th class="text-center px-4 py-2 font-medium">Rate</th>
                                    <th class="text-left px-4 py-2 font-medium w-[30%]">Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="emp in performance" :key="emp.id"
                                    class="border-b last:border-0 hover:bg-muted/20">
                                    <td class="px-4 py-2.5">
                                        <p class="font-medium">{{ emp.name }}</p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ emp.position }}
                                            <span v-if="emp.branch">· {{ emp.branch }}</span>
                                        </p>
                                    </td>
                                    <td class="text-center px-4 py-2.5">
                                        <span class="inline-flex items-center justify-center h-6 w-8 rounded bg-green-100 text-green-700 text-xs font-semibold">
                                            {{ emp.present }}
                                        </span>
                                    </td>
                                    <td class="text-center px-4 py-2.5">
                                        <span class="inline-flex items-center justify-center h-6 w-8 rounded text-xs font-semibold"
                                            :class="emp.absent > 0 ? 'bg-red-100 text-red-700' : 'bg-muted text-muted-foreground'">
                                            {{ emp.absent }}
                                        </span>
                                    </td>
                                    <td class="text-center px-4 py-2.5">
                                        <span class="inline-flex items-center justify-center h-6 w-8 rounded text-xs font-semibold"
                                            :class="emp.late > 0 ? 'bg-amber-100 text-amber-700' : 'bg-muted text-muted-foreground'">
                                            {{ emp.late }}
                                        </span>
                                    </td>
                                    <td class="text-center px-4 py-2.5">
                                        <span class="text-xs font-bold" :class="perfTextColor(emp.rate)">
                                            {{ emp.rate }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 h-2 rounded-full bg-muted overflow-hidden">
                                                <div class="h-full rounded-full transition-all" :class="perfColor(emp.rate)"
                                                    :style="{ width: `${emp.rate}%` }" />
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Decision Note -->
                    <div class="mt-3 rounded-lg border bg-muted/30 px-3 py-2 text-xs text-muted-foreground">
                        <Lightbulb class="inline h-3 w-3 mr-1 -mt-0.5 text-amber-500" />
                        <strong>Decision Note:</strong> Employees below 75% attendance may need a check-in.
                        Consider whether absenteeism is due to scheduling conflicts, personal issues, or low engagement.
                        Employees with 95%+ attendance could be candidates for recognition or incentive programs.
                    </div>
                </CardContent>
            </Card>

            <!-- ── AI-Driven Insights ──────────────────────────── -->
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-semibold flex items-center gap-2">
                        <Lightbulb class="h-4 w-4 text-amber-500" />
                        Decision Support Insights
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-3">
                    <div v-for="(insight, i) in insights" :key="i"
                        class="rounded-lg border px-4 py-3"
                        :class="[insightConfig[insight.type].bg, insightConfig[insight.type].border]">
                        <div class="flex items-start gap-3">
                            <component :is="insightConfig[insight.type].icon"
                                class="h-5 w-5 mt-0.5 flex-shrink-0"
                                :class="insightConfig[insight.type].text" />
                            <div class="flex-1">
                                <p class="text-sm font-semibold" :class="insightConfig[insight.type].text">
                                    {{ insight.title }}
                                </p>
                                <p class="text-xs mt-0.5" :class="insightConfig[insight.type].text + '/80'">
                                    {{ insight.message }}
                                </p>
                                <div class="mt-2 flex items-start gap-1.5">
                                    <ArrowUpRight class="h-3 w-3 mt-0.5 flex-shrink-0" :class="insightConfig[insight.type].text" />
                                    <p class="text-xs font-medium" :class="insightConfig[insight.type].text">
                                        Recommended Action: {{ insight.action }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

        </div>
    </ShopLayout>
</template>
