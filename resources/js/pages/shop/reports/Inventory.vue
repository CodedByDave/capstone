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
    FileDown,
    Layers,
    Loader2,
    Package,
    TrendingUp,
    Truck,
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
                filename: `inventory-report-${new Date().toISOString().slice(0, 10)}.pdf`,
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

function exportCSV() {
    const date = new Date().toISOString().slice(0, 10);

    // Top stocked
    const stockHeaders = ['Item', 'Quantity', 'Unit', 'Min Stock', 'Status'];
    const stockRows = props.topStocked.map((i) => [
        i.name,
        i.quantity,
        i.unit,
        i.min_stock,
        'In Stock',
    ]);

    // Low stock
    const lowHeaders = ['Item', 'Quantity', 'Unit', 'Min Stock', 'Status'];
    const lowRows = props.lowStockItems.map((i) => [
        i.name,
        i.quantity,
        i.unit,
        i.min_stock,
        i.quantity === 0 ? 'Out of Stock' : 'Low Stock',
    ]);

    const escape = (v: unknown) => `"${String(v).replace(/"/g, '""')}"`;

    const lines = [
        ['Inventory Report', date],
        [],
        ['--- Top Stocked Items ---'],
        stockHeaders.map(escape).join(','),
        ...stockRows.map((r) => r.map(escape).join(',')),
        [],
        ['--- Low Stock Items ---'],
        lowHeaders.map(escape).join(','),
        ...lowRows.map((r) => r.map(escape).join(',')),
    ].map((r) => (Array.isArray(r) ? r.join(',') : r));

    const blob = new Blob([lines.join('\n')], {
        type: 'text/csv;charset=utf-8;',
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `inventory-report-${date}.csv`;
    a.click();
    URL.revokeObjectURL(url);
}

const props = defineProps<{
    period: string;
    stats: {
        total_items: number;
        active_items: number;
        inactive_items: number;
        low_stock: number;
        out_of_stock: number;
    };
    topStocked: {
        id: number;
        name: string;
        quantity: number;
        unit: string;
        min_stock: number;
    }[];
    lowStockItems: {
        id: number;
        name: string;
        quantity: number;
        unit: string;
        min_stock: number;
    }[];
    movementsByType: { type: string; count: number }[];
    movementTrend: { date: string; restock: number; usage: number }[];
    categoryBreakdown: { name: string; count: number; quantity: number }[];
    supplierBreakdown: { name: string; count: number }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reports', href: base.value },
    { title: 'Inventory', href: `${base.value}/inventory` },
];

const period = ref(props.period);

function applyPeriod(val: string) {
    period.value = val;
    router.get(
        `${base.value}/inventory`,
        { period: val },
        { preserveState: true, replace: true },
    );
}

const movementTrendData = computed(() => ({
    labels: props.movementTrend.map((m) => m.date),
    datasets: [
        {
            label: 'Restock',
            data: props.movementTrend.map((m) => m.restock),
            backgroundColor: 'rgba(16, 185, 129, 0.7)',
            borderColor: 'rgb(16, 185, 129)',
            borderWidth: 2,
        },
        {
            label: 'Usage',
            data: props.movementTrend.map((m) => m.usage),
            backgroundColor: 'rgba(239, 68, 68, 0.7)',
            borderColor: 'rgb(239, 68, 68)',
            borderWidth: 2,
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

const movementTypeData = computed(() => ({
    labels: props.movementsByType.map((m) => m.type),
    datasets: [
        {
            data: props.movementsByType.map((m) => m.count),
            backgroundColor: [
                'rgba(16, 185, 129, 0.8)',
                'rgba(59, 130, 246, 0.8)',
                'rgba(139, 92, 246, 0.8)',
                'rgba(20, 184, 166, 0.8)',
                'rgba(239, 68, 68, 0.8)',
            ],
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: true, position: 'top' as const } },
};

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom' as const } },
};
</script>

<template>
    <Head title="Reports — Inventory" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Inventory Report">
        <div ref="reportContent" class="space-y-6 px-6">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-lg font-semibold">Inventory Report</h2>
                    <p class="text-sm text-muted-foreground">
                        Stock levels, movements and category breakdown.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Select
                        :model-value="period"
                        @update:model-value="applyPeriod"
                    >
                        <SelectTrigger class="w-36"
                            ><SelectValue placeholder="Period"
                        /></SelectTrigger>
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
                        @click="exportCSV"
                        class="gap-1.5"
                    >
                        <FileDown class="h-3.5 w-3.5" /> Export CSV
                    </Button>
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
                <Button variant="outline" size="sm" @click="router.visit(base)">
                    <BarChart3 class="mr-1.5 h-4 w-4" /> Overview
                </Button>
                <Button
                    variant="default"
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

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
                <Card>
                    <CardContent class="pt-5">
                        <p
                            class="mb-2 text-xs font-medium tracking-widest text-muted-foreground uppercase"
                        >
                            Total
                        </p>
                        <p class="text-3xl font-bold">
                            {{ stats.total_items }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <p
                            class="mb-2 text-xs font-medium tracking-widest text-muted-foreground uppercase"
                        >
                            Active
                        </p>
                        <p class="text-3xl font-bold text-green-600">
                            {{ stats.active_items }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <p
                            class="mb-2 text-xs font-medium tracking-widest text-muted-foreground uppercase"
                        >
                            Inactive
                        </p>
                        <p class="text-3xl font-bold text-gray-500">
                            {{ stats.inactive_items }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <p
                            class="mb-2 text-xs font-medium tracking-widest text-muted-foreground uppercase"
                        >
                            Low Stock
                        </p>
                        <p class="text-3xl font-bold text-amber-600">
                            {{ stats.low_stock }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <p
                            class="mb-2 text-xs font-medium tracking-widest text-muted-foreground uppercase"
                        >
                            Out of Stock
                        </p>
                        <p class="text-3xl font-bold text-red-600">
                            {{ stats.out_of_stock }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts row 1 -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-sm">
                            <TrendingUp class="h-4 w-4 text-muted-foreground" />
                            Movement Trend (Last 7 Days)
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="h-56">
                            <Bar
                                :data="movementTrendData"
                                :options="chartOptions"
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-sm">
                            <Layers class="h-4 w-4 text-muted-foreground" /> By
                            Category
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
                                No categories.
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts row 2 -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-sm">
                            <Truck class="h-4 w-4 text-muted-foreground" />
                            Movement Types
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="h-56">
                            <Doughnut
                                v-if="movementsByType.length > 0"
                                :data="movementTypeData"
                                :options="doughnutOptions"
                            />
                            <div
                                v-else
                                class="flex h-full items-center justify-center text-sm text-muted-foreground"
                            >
                                No movements.
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-sm">
                            <AlertTriangle class="h-4 w-4 text-amber-500" /> Low
                            Stock Items
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <div
                                v-for="item in lowStockItems"
                                :key="item.id"
                                class="flex items-center justify-between border-b py-1 text-sm last:border-0"
                            >
                                <span class="font-medium">{{ item.name }}</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-muted-foreground"
                                        >min: {{ item.min_stock }}</span
                                    >
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="
                                            item.quantity === 0
                                                ? 'bg-red-100 text-red-700'
                                                : 'bg-amber-100 text-amber-700'
                                        "
                                    >
                                        {{ item.quantity }} {{ item.unit }}
                                    </span>
                                </div>
                            </div>
                            <p
                                v-if="lowStockItems.length === 0"
                                class="py-4 text-center text-sm text-muted-foreground"
                            >
                                No low stock items.
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Top stocked -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-sm">
                        <Package class="h-4 w-4 text-muted-foreground" /> Top
                        Stocked Items
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-hidden rounded-lg border">
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="border-b bg-muted/40 text-xs text-muted-foreground"
                                >
                                    <th class="px-4 py-3 text-left font-medium">
                                        Item
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Quantity
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Min Stock
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in topStocked"
                                    :key="item.id"
                                    class="border-b last:border-0"
                                >
                                    <td class="px-4 py-3 font-medium">
                                        {{ item.name }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ item.quantity }} {{ item.unit }}
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ item.min_stock }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700"
                                        >
                                            In Stock
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="topStocked.length === 0">
                                    <td
                                        colspan="4"
                                        class="px-4 py-8 text-center text-sm text-muted-foreground"
                                    >
                                        No items.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </ShopLayout>
</template>
