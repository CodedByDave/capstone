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
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LinearScale,
    Tooltip,
} from 'chart.js';
import {
    Banknote,
    Download,
    Loader2,
    Percent,
    Receipt,
    TrendingDown,
    TrendingUp,
    Wallet,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend);

const props = defineProps<{
    period: string;
    stats: {
        revenue: number;
        expenses: number;
        net: number;
        margin: number | null;
    };
    monthlyTrend: { month: string; revenue: number; expenses: number }[];
    expenseCategories: { name: string; total: number; share: number }[];
}>();

const { isOwner } = usePermissions();
const {
    content: reportContent,
    downloading,
    download: downloadPdf,
} = useReportPdf('finance-report');
const base = computed(() =>
    isOwner.value ? '/shop/reports' : '/staff/reports',
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reports', href: base.value },
    { title: 'Finance', href: `${base.value}/finance` },
];

function applyPeriod(value: unknown) {
    router.get(
        `${base.value}/finance`,
        { period: String(value) },
        { preserveState: true, replace: true },
    );
}

function peso(value: number): string {
    return `₱${value.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

const headers: DataTableHeader[] = [
    { text: 'Expense Category', value: 'name', sortable: true },
    { text: 'Spent', value: 'total', sortable: true, align: 'right' },
    { text: 'Share', value: 'share', sortable: true, align: 'right' },
];

const { options, pageItems, total } = useDataTable(
    () => props.expenseCategories,
    { sortBy: 'total', sortType: 'desc', rowsPerPage: 100 },
);

const trendData = computed(() => ({
    labels: props.monthlyTrend.map((m) => m.month),
    datasets: [
        {
            label: 'Sales',
            data: props.monthlyTrend.map((m) => m.revenue),
            backgroundColor: 'rgba(16, 185, 129, 0.7)',
        },
        {
            label: 'Expenses',
            data: props.monthlyTrend.map((m) => m.expenses),
            backgroundColor: 'rgba(239, 68, 68, 0.7)',
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom' as const } },
};
</script>

<template>
    <Head title="Reports — Finance" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Reports & Analytics">
        <div ref="reportContent" class="space-y-6 px-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Finance Report</h2>
                    <p class="text-sm text-muted-foreground">
                        Sales collected against expenses for the period.
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

            <ReportNav active="finance" />

            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Sales
                            </p>
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-100"
                            >
                                <Banknote class="h-4 w-4 text-green-600" />
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-green-600">
                            {{ peso(stats.revenue) }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Expenses
                            </p>
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-100"
                            >
                                <Receipt class="h-4 w-4 text-red-600" />
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-red-600">
                            {{ peso(stats.expenses) }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Net Income
                            </p>
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100"
                            >
                                <Wallet class="h-4 w-4 text-blue-600" />
                            </div>
                        </div>
                        <p
                            class="flex items-center gap-1.5 text-2xl font-bold"
                            :class="stats.net < 0 ? 'text-red-600' : ''"
                        >
                            <component
                                :is="stats.net < 0 ? TrendingDown : TrendingUp"
                                class="h-5 w-5"
                                aria-hidden="true"
                            />
                            {{ peso(stats.net) }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Margin
                            </p>
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100"
                            >
                                <Percent class="h-4 w-4 text-amber-600" />
                            </div>
                        </div>
                        <p class="text-2xl font-bold">
                            {{
                                stats.margin === null ? '—' : `${stats.margin}%`
                            }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-sm"
                            >Sales vs Expenses (Last 6 Months)</CardTitle
                        >
                    </CardHeader>
                    <CardContent>
                        <div class="h-64">
                            <Bar :data="trendData" :options="chartOptions" />
                        </div>
                    </CardContent>
                </Card>

                <DataTable
                    v-model:options="options"
                    :headers="headers"
                    :items="pageItems"
                    :total="total"
                    item-key="name"
                    noun="categories"
                    empty-message="No expenses recorded for this period."
                    :resettable="false"
                    hide-footer
                    hide-rows-per-page
                    compact
                >
                    <template #item-total="{ total }">{{
                        peso(total)
                    }}</template>
                    <template #item-share="{ share }">{{ share }}%</template>
                </DataTable>
            </div>
        </div>
    </ShopLayout>
</template>
