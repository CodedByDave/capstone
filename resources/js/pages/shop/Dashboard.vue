<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import ShopOrder from '@/pages/shop/ShopOrder.vue'
import { dashboard } from '@/routes'
import { type BreadcrumbItem } from '@/types'
import { Head, usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { CheckCircle2, Users, ShoppingBag, TrendingUp } from 'lucide-vue-next'
import { ref, computed, onMounted } from 'vue'

// -------------------- Breadcrumbs --------------------
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url }
]

// -------------------- Inertia props --------------------
const { props } = usePage<{
    auth: { user: any }
    modules: any[]
    order?: {
        status: string
        shop_name: string
        modules: { name: string; price: number }[]
        total_price: number
    }
}>()

const user = props.auth.user
const isPaid = computed(() => props.order?.status === 'paid')
const showOrder = ref(false)

// -------------------- Mock stat cards --------------------
const statCards = [
    {
        title: 'Total Customers Today',
        value: '128',
        change: '+12% from yesterday',
        positive: true,
        icon: Users,
        color: 'bg-blue-500',
    },
    {
        title: 'Orders This Week',
        value: '47',
        change: '+5% from last week',
        positive: true,
        icon: ShoppingBag,
        color: 'bg-violet-500',
    },
    {
        title: 'Revenue This Month',
        value: '₱38,400',
        change: '-3% from last month',
        positive: false,
        icon: TrendingUp,
        color: 'bg-emerald-500',
    },
]

// -------------------- Charts --------------------
const lineChartRef = ref<HTMLCanvasElement | null>(null)
const pieChartRef = ref<HTMLCanvasElement | null>(null)

onMounted(async () => {
    if (!isPaid.value) return

    // Dynamically load Chart.js from CDN
    await loadScript('https://cdn.jsdelivr.net/npm/chart.js')

    // @ts-ignore
    const Chart = window.Chart

    // Line chart — weekly customers
    if (lineChartRef.value) {
        new Chart(lineChartRef.value, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [
                    {
                        label: 'Customers',
                        data: [65, 82, 74, 91, 110, 128, 95],
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99,102,241,0.1)',
                        borderWidth: 2,
                        pointBackgroundColor: '#6366f1',
                        pointRadius: 4,
                        tension: 0.4,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                    },
                    x: {
                        grid: { display: false },
                    },
                },
            },
        })
    }

    // Pie chart — module usage
    if (pieChartRef.value) {
        const moduleNames = props.order?.modules?.map(m => m.name) ?? ['No Modules']
        const moduleColors = [
            '#6366f1', '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6'
        ]
        const mockData = moduleNames.map((_, i) => Math.floor(20 + Math.random() * 60))

        new Chart(pieChartRef.value, {
            type: 'doughnut',
            data: {
                labels: moduleNames,
                datasets: [
                    {
                        data: mockData,
                        backgroundColor: moduleColors.slice(0, moduleNames.length),
                        borderWidth: 2,
                        borderColor: '#fff',
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 16,
                            font: { size: 12 },
                        },
                    },
                },
                cutout: '60%',
            },
        })
    }
})

function loadScript(src: string): Promise<void> {
    return new Promise((resolve) => {
        if (document.querySelector(`script[src="${src}"]`)) return resolve()
        const script = document.createElement('script')
        script.src = src
        script.onload = () => resolve()
        document.head.appendChild(script)
    })
}
</script>

<template>
    <Head title="Shop Dashboard" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Dashboard">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">

            <!-- ===================== PAID: DSS Dashboard ===================== -->
            <template v-if="isPaid">

                <!-- Welcome -->
                <div class="rounded-xl bg-gray-100 dark:bg-neutral-800 p-6">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">
                        Welcome back, {{ user.name }} 👋
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Here's an overview of <span class="font-semibold text-gray-800 dark:text-white">{{ props.order?.shop_name }}</span> today.
                    </p>
                </div>

                <!-- Stat Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <Card v-for="card in statCards" :key="card.title">
                        <CardContent class="p-5 flex items-center gap-4">
                            <div :class="[card.color, 'w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0']">
                                <component :is="card.icon" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ card.title }}</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ card.value }}</p>
                                <p :class="card.positive ? 'text-green-600' : 'text-red-500'" class="text-xs mt-0.5">
                                    {{ card.change }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <!-- Line Chart -->
                    <Card class="md:col-span-2">
                        <CardHeader>
                            <CardTitle class="text-base">Weekly Customer Traffic</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <canvas ref="lineChartRef" height="120"></canvas>
                        </CardContent>
                    </Card>

                    <!-- Pie / Doughnut Chart -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">Module Usage</CardTitle>
                        </CardHeader>
                        <CardContent class="flex items-center justify-center">
                            <canvas ref="pieChartRef" height="220"></canvas>
                        </CardContent>
                    </Card>

                </div>

            </template>

            <!-- ===================== NOT PAID: Order Form ===================== -->
            <template v-else>
                <div v-if="!showOrder" class="rounded-xl bg-gray-100 dark:bg-neutral-800 p-6">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">
                        Welcome {{ user.name }} 👋
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2 max-w-2xl">
                        Complete your shop details and select the modules you need. You will be redirected to PayMongo for payment.
                    </p>
                    <Button class="mt-4" @click="showOrder = true">Get Started</Button>
                </div>

                <ShopOrder v-if="showOrder" />
            </template>

        </div>
    </ShopLayout>
</template>
