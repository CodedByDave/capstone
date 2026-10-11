<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import {
    ArcElement,
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Title,
    Tooltip,
} from 'chart.js';
import html2pdf from 'html2pdf.js';
import {
    AlertTriangle,
    BarChart3,
    Download,
    Loader2,
    Package,
    ShieldAlert,
    TrendingUp,
    Users,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Bar, Doughnut } from 'vue-chartjs';

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    LineElement,
    PointElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
);

const { isOwner } = usePermissions();
const base = computed(() =>
    isOwner.value ? '/shop/reports' : '/staff/reports',
);

const reportContent = ref<HTMLElement | null>(null);
const downloading = ref(false);

async function downloadPDF() {
    if (!reportContent.value || downloading.value) return;
    downloading.value = true;
    try {
        await html2pdf()
            .set({
                margin: [10, 10, 10, 10],
                filename: `overview-report-${new Date().toISOString().slice(0, 10)}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true, logging: false },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
            })
            .from(reportContent.value)
            .save();
    } finally {
        downloading.value = false;
    }
}

const props = defineProps<{
    period: string;
    stats: {
        total_items: number;
        active_items: number;
        low_stock_items: number;
        out_of_stock: number;
        total_employees: number;
        total_branches: number;
        unresolved_alerts: number;
        movements_period: number;
    };
    movementTrend: { date: string; count: number }[];
    categoryBreakdown: { name: string; count: number }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reports', href: base.value },
    { title: 'Overview', href: base.value },
];

const period = ref(props.period);

function applyPeriod(val: string) {
    period.value = val;
    router.get(
        base.value,
        { period: val },
        { preserveState: true, replace: true },
    );
}

const movementChartData = computed(() => ({
    labels: props.movementTrend.map((m) => m.date),
    datasets: [
        {
            label: 'Stock Movements',
            data: props.movementTrend.map((m) => m.count),
            backgroundColor: 'rgba(59, 130, 246, 0.5)',
            borderColor: 'rgb(59, 130, 246)',
            borderWidth: 2,
            tension: 0.4,
            fill: true,
        },
    ],
}));

const categoryChartData = computed(() => ({
    labels: props.categoryBreakdown.map((c) => c.name),
    datasets: [
        {
            data: props.categoryBreakdown.map((c) => c.count),
            backgroundColor: [
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(139, 92, 246, 0.8)',
                'rgba(239, 68, 68, 0.8)',
                'rgba(20, 184, 166, 0.8)',
            ],
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
};

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom' as const } },
};
</script>

<template>
    <Head title="Reports — Overview" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Reports & Analytics">
        <div ref="reportContent" class="space-y-6 px-6">
            <!-- Header with period filter -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Overview</h2>
                    <p class="text-sm text-muted-foreground">
                        A snapshot of your shop's current performance.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Select
                        :model-value="period"
                        @update:model-value="applyPeriod"
                    >
                        <SelectTrigger class="w-36">
                            <SelectValue placeholder="Period" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="today">Today</SelectItem>
                            <SelectItem value="week">This Week</SelectItem>
                            <SelectItem value="month">This Month</SelectItem>
                            <SelectItem value="year">This Year</SelectItem>
                        </SelectContent>
                    </Select>
                    <Button
                        size="sm"
                        variant="outline"
                        :disabled="downloading"
                        @click="downloadPDF"
                        class="gap-1.5"
                    >
                        <Loader2
                            v-if="downloading"
                            class="h-3.5 w-3.5 animate-spin"
                        />
                        <Download v-else class="h-3.5 w-3.5" />
                        {{ downloading ? 'Generating…' : 'Download PDF' }}
                    </Button>
                </div>
            </div>

            <!-- Report nav -->
            <div class="flex flex-wrap gap-2">
                <Button variant="default" size="sm" @click="router.visit(base)">
                    <BarChart3 class="mr-1.5 h-4 w-4" /> Overview
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    @click="router.visit(`${base}/inventory`)"
                >
                    <Package class="mr-1.5 h-4 w-4" /> Inventory
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    @click="router.visit(`${base}/employee`)"
                >
                    <Users class="mr-1.5 h-4 w-4" /> Employees
                </Button>
            </div>

            <!-- Stats grid -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Total Items
                            </p>
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-100"
                            >
                                <Package class="h-4 w-4 text-blue-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold">
                            {{ stats.total_items }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ stats.active_items }} active
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Low Stock
                            </p>
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100"
                            >
                                <AlertTriangle class="h-4 w-4 text-amber-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-amber-600">
                            {{ stats.low_stock_items }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ stats.out_of_stock }} out of stock
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Employees
                            </p>
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-green-100"
                            >
                                <Users class="h-4 w-4 text-green-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-green-600">
                            {{ stats.total_employees }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ stats.total_branches }} branches
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Alerts
                            </p>
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-100"
                            >
                                <ShieldAlert class="h-4 w-4 text-red-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-red-600">
                            {{ stats.unresolved_alerts }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ stats.movements_period }} movements this period
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-sm">
                            <TrendingUp class="h-4 w-4 text-muted-foreground" />
                            Stock Movements (Last 7 Days)
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="h-56">
                            <Bar
                                :data="movementChartData"
                                :options="chartOptions"
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-sm">
                            <Package class="h-4 w-4 text-muted-foreground" />
                            Items by Category
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="h-56">
                            <Doughnut
                                v-if="categoryBreakdown.length > 0"
                                :data="categoryChartData"
                                :options="doughnutOptions"
                            />
                            <div
                                v-else
                                class="flex h-full items-center justify-center text-sm text-muted-foreground"
                            >
                                No categories found.
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </ShopLayout>
</template>
