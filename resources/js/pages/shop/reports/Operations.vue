<script setup lang="ts">
import { DataTable, type DataTableHeader } from '@/components/data-table';
import ReportNav from '@/components/shop/ReportNav.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useDataTable } from '@/composables/useDataTable';
import { usePermissions } from '@/composables/usePermissions';
import { useReportPdf } from '@/composables/useReportPdf';
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
    Tooltip,
} from 'chart.js';
import {
    CheckCircle2,
    ClipboardList,
    Clock,
    Download,
    Loader2,
    XCircle,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Bar, Doughnut } from 'vue-chartjs';

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    ArcElement,
    Tooltip,
    Legend,
);

const props = defineProps<{
    period: string;
    stats: {
        total: number;
        completed: number;
        cancelled: number;
        completion_rate: number | null;
        cancellation_rate: number | null;
        avg_turnaround_hours: number | null;
    };
    statusBreakdown: { status: string; count: number }[];
    services: { name: string; orders: number; revenue: number }[];
    weekdayDistribution: { day: string; count: number }[];
}>();

const { isOwner } = usePermissions();
const {
    content: reportContent,
    downloading,
    download: downloadPdf,
} = useReportPdf('operations-report');
const base = computed(() =>
    isOwner.value ? '/shop/reports' : '/staff/reports',
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reports', href: base.value },
    { title: 'Operations', href: `${base.value}/operations` },
];

function applyPeriod(value: unknown) {
    router.get(
        `${base.value}/operations`,
        { period: String(value) },
        { preserveState: true, replace: true },
    );
}

function peso(value: number): string {
    return `₱${value.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function percent(value: number | null): string {
    return value === null ? '—' : `${value}%`;
}

const headers: DataTableHeader[] = [
    { text: 'Service', value: 'name', sortable: true },
    { text: 'Orders', value: 'orders', sortable: true, align: 'right' },
    { text: 'Sales', value: 'revenue', sortable: true, align: 'right' },
];

const { options, pageItems, total } = useDataTable(() => props.services, {
    sortBy: 'revenue',
    sortType: 'desc',
    rowsPerPage: 100,
});

const STATUS_COLORS: Record<string, string> = {
    pending: 'rgba(245, 158, 11, 0.8)',
    in_progress: 'rgba(59, 130, 246, 0.8)',
    completed: 'rgba(16, 185, 129, 0.8)',
    picked_up: 'rgba(20, 184, 166, 0.8)',
    cancelled: 'rgba(239, 68, 68, 0.8)',
};

const statusData = computed(() => ({
    labels: props.statusBreakdown.map((s) => s.status.replace('_', ' ')),
    datasets: [
        {
            data: props.statusBreakdown.map((s) => s.count),
            backgroundColor: props.statusBreakdown.map(
                (s) => STATUS_COLORS[s.status] ?? 'rgba(107, 114, 128, 0.8)',
            ),
        },
    ],
}));

const weekdayData = computed(() => ({
    labels: props.weekdayDistribution.map((d) => d.day),
    datasets: [
        {
            label: 'Orders',
            data: props.weekdayDistribution.map((d) => d.count),
            backgroundColor: 'rgba(59, 130, 246, 0.7)',
        },
    ],
}));

const barOptions = {
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
    <Head title="Reports — Operations" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Reports & Analytics">
        <div ref="reportContent" class="space-y-6 px-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Operations Report</h2>
                    <p class="text-sm text-muted-foreground">
                        Order processing and service performance for the period.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Select
                        :model-value="period"
                        @update:model-value="applyPeriod"
                    >
                        <SelectTrigger class="w-36" aria-label="Report period">
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
                        class="gap-1.5"
                        :disabled="downloading"
                        @click="downloadPdf"
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

            <ReportNav active="operations" />

            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Orders
                            </p>
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100"
                            >
                                <ClipboardList class="h-4 w-4 text-blue-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold">{{ stats.total }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Completed
                            </p>
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-100"
                            >
                                <CheckCircle2 class="h-4 w-4 text-green-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-green-600">
                            {{ stats.completed }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ percent(stats.completion_rate) }} completion rate
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Cancelled
                            </p>
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-100"
                            >
                                <XCircle class="h-4 w-4 text-red-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-red-600">
                            {{ stats.cancelled }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ percent(stats.cancellation_rate) }} cancellation
                            rate
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Avg Turnaround
                            </p>
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100"
                            >
                                <Clock class="h-4 w-4 text-amber-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold">
                            {{
                                stats.avg_turnaround_hours === null
                                    ? '—'
                                    : `${stats.avg_turnaround_hours} h`
                            }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            order placed to completed
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm">Orders by Status</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="h-56">
                            <Doughnut
                                v-if="statusBreakdown.length"
                                :data="statusData"
                                :options="doughnutOptions"
                            />
                            <div
                                v-else
                                class="flex h-full items-center justify-center text-sm text-muted-foreground"
                            >
                                No orders in this period.
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm"
                            >Orders by Day of Week</CardTitle
                        >
                    </CardHeader>
                    <CardContent>
                        <div class="h-56">
                            <Bar :data="weekdayData" :options="barOptions" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <DataTable
                v-model:options="options"
                :headers="headers"
                :items="pageItems"
                :total="total"
                item-key="name"
                noun="services"
                empty-message="No orders in this period."
                :resettable="false"
                hide-footer
                hide-rows-per-page
                compact
            >
                <template #item-revenue="{ revenue }">{{
                    peso(revenue)
                }}</template>
            </DataTable>
        </div>
    </ShopLayout>
</template>
