<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue'
import { dashboard } from '@/routes'
import { type BreadcrumbItem } from '@/types'
import { Head, usePage } from '@inertiajs/vue3'

// ShadCN Card + icons
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Users, ShoppingCart, DollarSign, ArrowUp, ArrowDown } from 'lucide-vue-next'
//import VueApexCharts from 'vue3-apexcharts'
import { ref } from 'vue'

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url }
]

// Access the logged-in user
const { props } = usePage()
const user = props.auth.user

// Dashboard stats (mock)
const stats = [
    { title: 'Total Users', value: 1250, change: 12, trend: 'up', icon: Users },
    { title: 'Orders Today', value: 320, change: 5, trend: 'down', icon: ShoppingCart },
    { title: 'Revenue', value: '$12,450', change: 20, trend: 'up', icon: DollarSign },
]

// ApexCharts line chart (mock)
const chartOptions = ref({
    chart: { id: 'revenue', toolbar: { show: false }, zoom: { enabled: false }, background: 'transparent' },
    stroke: { curve: 'smooth', width: 3 },
    xaxis: { categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'], labels: { style: { colors: '#9ca3af' } } },
    yaxis: { labels: { style: { colors: '#9ca3af' } } },
    colors: ['#3b82f6'],
    tooltip: { theme: 'dark' },
    grid: { borderColor: '#374151', strokeDashArray: 4 }
})
const chartSeries = ref([{ name: 'Revenue', data: [4500, 6000, 8000, 7000, 9000, 10000, 12000] }])
</script>

<template>

    <Head title="Admin Dashboard" />

    <AdminLayout :breadcrumbs="breadcrumbs" title="Dashboard">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">

            <!-- Welcome message -->
            <div class="rounded-xl bg-gray-100 dark:bg-neutral-800 p-6">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Welcome {{ user.name }}!
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Here's your dashboard overview
                </p>
            </div>

            <!-- Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card v-for="(stat, index) in stats" :key="index"
                    class="relative border-sidebar-border dark:border-sidebar-border/70 p-4">
                    <!-- Icon top-right -->
                    <component :is="stat.icon" class="absolute top-4 right-4 w-6 h-6 text-blue-500" />
                    <CardHeader class="p-0">
                        <CardTitle class="text-lg font-semibold">{{ stat.title }}</CardTitle>
                    </CardHeader>
                    <CardContent class="p-0 mt-2 flex flex-col gap-2">
                        <span class="text-2xl font-bold">{{ stat.value }}</span>
                        <div class="flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                            <component :is="stat.trend === 'up' ? ArrowUp : ArrowDown" class="w-4 h-4"
                                :class="stat.trend === 'up' ? 'text-green-500' : 'text-red-500'" />
                            <span>{{ stat.change }}% from last month</span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- ApexCharts line chart -->
            <Card class="border-sidebar-border dark:border-sidebar-border/70 mt-4">
                <CardHeader>
                    <CardTitle>Revenue Overview</CardTitle>
                </CardHeader>
                <CardContent>
                    <VueApexCharts type="line" height="350" :options="chartOptions" :series="chartSeries" />
                </CardContent>
            </Card>

        </div>
    </AdminLayout>
</template>
