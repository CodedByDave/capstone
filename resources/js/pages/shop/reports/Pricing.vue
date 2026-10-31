<script setup lang="ts">
import {
    DataTable,
    StatusPill,
    type DataTableHeader,
    type StatusTone,
} from '@/components/data-table';
import ReportNav from '@/components/shop/ReportNav.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
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
    AlertTriangle,
    CheckCircle2,
    Download,
    Info,
    Loader2,
    ShieldAlert,
    Tag,
} from 'lucide-vue-next';
import { computed } from 'vue';

interface ServiceRow {
    id: number;
    name: string;
    pricing_unit: string;
    price: number;
    supply_cost: number;
    cost_complete: boolean;
    margin: number;
    margin_percent: number | null;
    orders: number;
    status: 'below_cost' | 'low_margin' | 'healthy' | 'no_supplies';
    suggested_price: number | null;
}

const props = defineProps<{
    period: string;
    targets: { low_margin_percent: number; target_margin_percent: number };
    stats: {
        services: number;
        below_cost: number;
        low_margin: number;
        healthy: number;
    };
    services: ServiceRow[];
}>();

const { isOwner } = usePermissions();
const {
    content: reportContent,
    downloading,
    download: downloadPdf,
} = useReportPdf('pricing-report');
const base = computed(() =>
    isOwner.value ? '/shop/reports' : '/staff/reports',
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reports', href: base.value },
    { title: 'Pricing', href: `${base.value}/pricing` },
];

function applyPeriod(value: unknown) {
    router.get(
        `${base.value}/pricing`,
        { period: String(value) },
        { preserveState: true, replace: true },
    );
}

function peso(value: number): string {
    return `₱${value.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

const headers: DataTableHeader[] = [
    { text: 'Service', value: 'name', sortable: true },
    { text: 'Price', value: 'price', sortable: true, align: 'right' },
    {
        text: 'Supply cost',
        value: 'supply_cost',
        sortable: true,
        align: 'right',
    },
    {
        text: 'Margin',
        value: 'margin_percent',
        sortable: true,
        align: 'right',
    },
    { text: 'Orders', value: 'orders', sortable: true, align: 'right' },
    { text: 'Status', value: 'status', sortable: true },
    {
        text: 'Suggested price',
        value: 'suggested_price',
        sortable: true,
        align: 'right',
    },
];

// Worst margin first; services without a supply list have no margin and sort last.
const { options, pageItems, total } = useDataTable(() => props.services, {
    sortBy: 'margin_percent',
    sortType: 'asc',
    rowsPerPage: 100,
});

const STATUS: Record<
    ServiceRow['status'],
    { label: string; tone: StatusTone }
> = {
    below_cost: { label: 'Below cost', tone: 'danger' },
    low_margin: { label: 'Low margin', tone: 'warning' },
    healthy: { label: 'Healthy', tone: 'success' },
    no_supplies: { label: 'No supplies set', tone: 'neutral' },
};
</script>

<template>
    <Head title="Reports — Pricing" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Reports & Analytics">
        <div ref="reportContent" class="space-y-6 px-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Pricing Report</h2>
                    <p class="text-sm text-muted-foreground">
                        Each service's price against the supplies it uses.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Select
                        :model-value="period"
                        @update:model-value="applyPeriod"
                    >
                        <SelectTrigger
                            class="w-36"
                            aria-label="Order volume period"
                        >
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

            <ReportNav active="pricing" />

            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Services
                            </p>
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100"
                            >
                                <Tag class="h-4 w-4 text-blue-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold">{{ stats.services }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Below cost
                            </p>
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-100"
                            >
                                <ShieldAlert class="h-4 w-4 text-red-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-red-600">
                            {{ stats.below_cost }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Low margin
                            </p>
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100"
                            >
                                <AlertTriangle class="h-4 w-4 text-amber-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-amber-600">
                            {{ stats.low_margin }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            under {{ targets.low_margin_percent }}%
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Healthy
                            </p>
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-100"
                            >
                                <CheckCircle2 class="h-4 w-4 text-green-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-green-600">
                            {{ stats.healthy }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <p
                class="flex items-start gap-2 text-xs text-muted-foreground"
                role="note"
            >
                <Info class="mt-0.5 h-3.5 w-3.5 shrink-0" aria-hidden="true" />
                Cost covers supplies only (from each service's supply list and
                the item unit price); labor and overhead are not included.
                Suggested prices aim for a
                {{ targets.target_margin_percent }}% margin.
            </p>

            <DataTable
                v-model:options="options"
                :headers="headers"
                :items="pageItems"
                :total="total"
                item-key="id"
                noun="services"
                empty-message="No active services yet."
                :resettable="false"
                hide-footer
                hide-rows-per-page
            >
                <template #item-name="service">
                    <p class="font-medium">{{ service.name }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ service.pricing_unit }}
                    </p>
                </template>
                <template #item-price="service">{{
                    peso(service.price)
                }}</template>
                <template #item-supply_cost="service">
                    <span v-if="service.status === 'no_supplies'">—</span>
                    <span v-else>
                        {{ peso(service.supply_cost) }}
                        <span
                            v-if="!service.cost_complete"
                            class="block text-xs text-amber-700"
                            >some items have no unit price</span
                        >
                    </span>
                </template>
                <template #item-margin_percent="service">
                    <span v-if="service.status === 'no_supplies'">—</span>
                    <span
                        v-else
                        :class="service.margin < 0 ? 'text-red-600' : ''"
                    >
                        {{ peso(service.margin) }}
                        <span class="block text-xs text-muted-foreground">
                            {{
                                service.margin_percent === null
                                    ? '—'
                                    : `${service.margin_percent}%`
                            }}
                        </span>
                    </span>
                </template>
                <template #item-status="service">
                    <StatusPill
                        :tone="
                            STATUS[service.status as ServiceRow['status']].tone
                        "
                    >
                        {{
                            STATUS[service.status as ServiceRow['status']].label
                        }}
                    </StatusPill>
                </template>
                <template #item-suggested_price="service">
                    <span
                        v-if="
                            service.suggested_price !== null &&
                            (service.status === 'below_cost' ||
                                service.status === 'low_margin')
                        "
                        class="font-medium"
                    >
                        {{ peso(service.suggested_price) }}
                    </span>
                    <span v-else class="text-muted-foreground">—</span>
                </template>
            </DataTable>
        </div>
    </ShopLayout>
</template>
