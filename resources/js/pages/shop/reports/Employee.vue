<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Users, Building2, Calendar, TrendingUp, BarChart3, Package } from 'lucide-vue-next'
import { usePermissions } from '@/composables/usePermissions'
import {
    Chart as ChartJS, CategoryScale, LinearScale, BarElement,
    LineElement, PointElement, ArcElement, Title, Tooltip, Legend,
} from 'chart.js'
import { Bar, Doughnut } from 'vue-chartjs'

ChartJS.register(CategoryScale, LinearScale, BarElement, LineElement, PointElement, ArcElement, Title, Tooltip, Legend)

const { isOwner } = usePermissions()
const base = computed(() => isOwner.value ? '/shop/reports' : '/staff/reports')

const props = defineProps<{
    period: string
    stats: {
        total_employees: number
        total_branches: number
        new_employees: number
        scheduled_week: number
    }
    employeesByBranch: { name: string; count: number }[]
    employeeGrowth:    { month: string; count: number }[]
    recentEmployees:   { name: string; email: string; branch: string; created_at: string }[]
    employeesByRole:   { role: string; count: number }[]
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reports',   href: base.value },
    { title: 'Employees', href: `${base.value}/employee` },
]

const period = ref(props.period)

function applyPeriod(val: string) {
    period.value = val
    router.get(`${base.value}/employee`, { period: val }, { preserveState: true, replace: true })
}

const branchChartData = computed(() => ({
    labels: props.employeesByBranch.map(b => b.name),
    datasets: [{
        label: 'Employees',
        data: props.employeesByBranch.map(b => b.count),
        backgroundColor: 'rgba(59, 130, 246, 0.7)',
        borderColor: 'rgb(59, 130, 246)',
        borderWidth: 2,
    }],
}))

const growthChartData = computed(() => ({
    labels: props.employeeGrowth.map(g => g.month),
    datasets: [{
        label: 'New Employees',
        data: props.employeeGrowth.map(g => g.count),
        backgroundColor: 'rgba(16, 185, 129, 0.5)',
        borderColor: 'rgb(16, 185, 129)',
        borderWidth: 2,
        tension: 0.4,
        fill: true,
    }],
}))

const roleChartData = computed(() => ({
    labels: props.employeesByRole.map(r => r.role),
    datasets: [{
        data: props.employeesByRole.map(r => r.count),
        backgroundColor: [
            'rgba(59, 130, 246, 0.8)', 'rgba(16, 185, 129, 0.8)',
            'rgba(245, 158, 11, 0.8)', 'rgba(139, 92, 246, 0.8)',
            'rgba(239, 68, 68, 0.8)',
        ],
    }],
}))

const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
}

const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
}

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom' as const } },
}
</script>

<template>
    <Head title="Reports — Employees" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Employee Report">
        <div class="px-6 space-y-6">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Employee Report</h2>
                    <p class="text-sm text-muted-foreground">Headcount, branches and growth overview.</p>
                </div>
                <Select :model-value="period" @update:model-value="applyPeriod">
                    <SelectTrigger class="w-36"><SelectValue placeholder="Period" /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="today">Today</SelectItem>
                        <SelectItem value="week">This Week</SelectItem>
                        <SelectItem value="month">This Month</SelectItem>
                        <SelectItem value="year">This Year</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Report nav -->
            <div class="flex gap-2 flex-wrap">
                <Button variant="outline" size="sm" @click="router.visit(base)">
                    <BarChart3 class="h-4 w-4 mr-1.5" /> Overview
                </Button>
                <Button variant="outline" size="sm" @click="router.visit(`${base}/inventory`)">
                    <Package class="h-4 w-4 mr-1.5" /> Inventory
                </Button>
                <Button variant="default" size="sm" @click="router.visit(`${base}/employee`)">
                    <Users class="h-4 w-4 mr-1.5" /> Employees
                </Button>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Total</p>
                            <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                                <Users class="h-4 w-4 text-blue-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold">{{ stats.total_employees }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Branches</p>
                            <div class="h-8 w-8 rounded-lg bg-purple-100 flex items-center justify-center shrink-0">
                                <Building2 class="h-4 w-4 text-purple-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-purple-600">{{ stats.total_branches }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">New This Period</p>
                            <div class="h-8 w-8 rounded-lg bg-green-100 flex items-center justify-center shrink-0">
                                <TrendingUp class="h-4 w-4 text-green-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-green-600">{{ stats.new_employees }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Scheduled</p>
                            <div class="h-8 w-8 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                                <Calendar class="h-4 w-4 text-amber-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-amber-600">{{ stats.scheduled_week }}</p>
                        <p class="text-xs text-muted-foreground mt-1">this week</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts row 1 -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm flex items-center gap-2">
                            <Building2 class="h-4 w-4 text-muted-foreground" /> Employees by Branch
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="h-56">
                            <Bar v-if="employeesByBranch.length > 0" :data="branchChartData" :options="barOptions" />
                            <div v-else class="h-full flex items-center justify-center text-sm text-muted-foreground">No branches.</div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm flex items-center gap-2">
                            <Users class="h-4 w-4 text-muted-foreground" /> Employees by Role
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="h-56">
                            <Doughnut v-if="employeesByRole.length > 0" :data="roleChartData" :options="doughnutOptions" />
                            <div v-else class="h-full flex items-center justify-center text-sm text-muted-foreground">No roles assigned.</div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Growth chart -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm flex items-center gap-2">
                        <TrendingUp class="h-4 w-4 text-muted-foreground" /> Employee Growth (Last 6 Months)
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="h-56">
                        <Bar :data="growthChartData" :options="lineOptions" />
                    </div>
                </CardContent>
            </Card>

            <!-- Recent employees -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm flex items-center gap-2">
                        <Users class="h-4 w-4 text-muted-foreground" /> Recently Added Employees
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="rounded-lg border overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                                    <th class="text-left px-4 py-3 font-medium">Name</th>
                                    <th class="text-left px-4 py-3 font-medium">Email</th>
                                    <th class="text-left px-4 py-3 font-medium">Branch</th>
                                    <th class="text-left px-4 py-3 font-medium">Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="e in recentEmployees" :key="e.email" class="border-b last:border-0">
                                    <td class="px-4 py-3 font-medium">{{ e.name }}</td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground">{{ e.email }}</td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground">{{ e.branch }}</td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground">{{ e.created_at }}</td>
                                </tr>
                                <tr v-if="recentEmployees.length === 0">
                                    <td colspan="4" class="px-4 py-8 text-center text-muted-foreground text-sm">No employees yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

        </div>
    </ShopLayout>
</template>
