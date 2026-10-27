<script setup lang="ts">
import {
    DataTable,
    DataTableToolbar,
    StatusPill,
    statusLabel,
    statusTone,
    type DataTableHeader,
} from '@/components/data-table';
import { usePaginatorTable } from '@/composables/useDataTable';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { TrendingUp } from 'lucide-vue-next';

// ─── Types ────────────────────────────────────────────────────────────────────

interface IncomeEntry {
    id: number;
    order_number: string;
    customer_name: string;
    service: { id: number; service_name: string } | null;
    amount_paid: string;
    total_amount: string;
    payment_status: string;
    payment_method: string | null;
    paid_at: string | null;
    created_at: string;
}

interface PaginatedIncome {
    data: IncomeEntry[];
    current_page: number;
    last_page: number;
    per_page?: number;
    total: number;
    from: number | null;
    to: number | null;
}

interface Stats {
    total_income: number;
    this_month_income: number;
    total_count: number;
    unpaid_count: number;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    income: PaginatedIncome;
    stats: Stats;
    filters: {
        dateFrom?: string | null;
        dateTo?: string | null;
        method?: string | null;
    };
}>();

// ─── RBAC ─────────────────────────────────────────────────────────────────────

const { isOwner } = usePermissions();
const base = computed(() => (isOwner.value ? '/shop' : '/staff'));

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Finance', href: `${base.value}/finance` },
    { title: 'Income', href: `${base.value}/finance/income` },
];

// ─── Filters ──────────────────────────────────────────────────────────────────

const dateFrom = ref(props.filters.dateFrom ?? '');
const dateTo = ref(props.filters.dateTo ?? '');
const method = ref(props.filters.method ?? '');

function applyFilters() {
    router.get(
        `${base.value}/finance/income`,
        {
            date_from: dateFrom.value || undefined,
            date_to: dateTo.value || undefined,
            method: method.value || undefined,
        },
        { replace: true },
    );
}

function clearFilters() {
    dateFrom.value = '';
    dateTo.value = '';
    method.value = '';
    router.get(`${base.value}/finance/income`, {}, { replace: true });
}

// ─── Table ────────────────────────────────────────────────────────────────────

const { options, loading } = usePaginatorTable(() => props.income, ['income']);

const hasFilters = computed(
    () =>
        Boolean(props.filters.dateFrom) ||
        Boolean(props.filters.dateTo) ||
        Boolean(props.filters.method),
);

const headers: DataTableHeader[] = [
    { text: 'Order #', value: 'order_number' },
    { text: 'Customer', value: 'customer_name' },
    { text: 'Service', value: 'service' },
    { text: 'Method', value: 'payment_method' },
    { text: 'Date paid', value: 'paid_at' },
    { text: 'Status', value: 'payment_status' },
    { text: 'Amount', value: 'amount_paid', align: 'right', width: 140 },
];

// ─── Helpers ──────────────────────────────────────────────────────────────────

function currency(val: string | number | null): string {
    const num = typeof val === 'string' ? parseFloat(val) : (val ?? 0);
    return `₱${num.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
}

function formatDate(val: string | null): string {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function methodLabel(val: string | null): string {
    const map: Record<string, string> = {
        cash: 'Cash',
        gcash: 'GCash',
        maya: 'Maya',
    };
    return val ? (map[val] ?? val) : '—';
}
</script>

<template>
    <Head title="Income" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Finance">
        <div class="space-y-6 px-6">
            <!-- ── Header ──────────────────────────────────────── -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2 class="text-lg font-semibold">Income</h2>
                    <p class="text-sm text-muted-foreground">
                        Revenue collected from customer orders.
                    </p>
                </div>
                <Button
                    variant="outline"
                    @click="router.visit(`${base}/finance/transactions`)"
                >
                    View Transactions
                </Button>
            </div>

            <!-- ── Stats ───────────────────────────────────────── -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <Card>
                    <CardContent class="flex items-center gap-3 pt-4 pb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100"
                        >
                            <TrendingUp class="h-5 w-5 text-green-600" />
                        </div>
                        <div>
                            <p class="text-lg font-bold">
                                {{ currency(stats.total_income) }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Total Collected
                            </p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 pt-4 pb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100"
                        >
                            <TrendingUp class="h-5 w-5 text-emerald-600" />
                        </div>
                        <div>
                            <p class="text-lg font-bold">
                                {{ currency(stats.this_month_income) }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                This Month
                            </p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-4 pb-4">
                        <p class="text-2xl font-bold">
                            {{ stats.total_count }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            Paid Transactions
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-4 pb-4">
                        <p class="text-2xl font-bold text-amber-600">
                            {{ stats.unpaid_count }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            Unpaid Orders
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- ── Table ───────────────────────────────────────── -->
            <DataTable
                v-model:options="options"
                :headers="headers"
                :items="income.data"
                :total="income.total"
                :loading="loading"
                noun="income records"
                :empty-icon="TrendingUp"
                empty-message="No income records found for the selected filters."
                :resettable="hasFilters"
                :min-width="900"
                hide-rows-per-page
                @reset="clearFilters"
            >
                <template #toolbar>
                    <DataTableToolbar hide-search>
                        <Input
                            v-model="dateFrom"
                            type="date"
                            class="sm:w-40"
                            placeholder="From"
                            aria-label="From date"
                        />
                        <Input
                            v-model="dateTo"
                            type="date"
                            class="sm:w-40"
                            placeholder="To"
                            aria-label="To date"
                        />
                        <Select v-model="method">
                            <SelectTrigger
                                class="sm:w-40"
                                aria-label="Filter by payment method"
                            >
                                <SelectValue placeholder="All methods" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All methods</SelectItem>
                                <SelectItem value="cash">Cash</SelectItem>
                                <SelectItem value="gcash">GCash</SelectItem>
                                <SelectItem value="maya">Maya</SelectItem>
                            </SelectContent>
                        </Select>
                        <Button @click="applyFilters">Filter</Button>
                        <Button variant="outline" @click="clearFilters"
                            >Clear</Button
                        >
                    </DataTableToolbar>
                </template>

                <template #item-order_number="entry">
                    <span class="font-mono text-xs text-muted-foreground">
                        {{ entry.order_number }}
                    </span>
                </template>

                <template #item-customer_name="entry">
                    <span class="font-medium">{{ entry.customer_name }}</span>
                </template>

                <template #item-service="entry">
                    <span class="text-muted-foreground">
                        {{ entry.service?.service_name ?? '—' }}
                    </span>
                </template>

                <template #item-payment_method="entry">
                    <span
                        class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium whitespace-nowrap text-blue-700"
                    >
                        {{ methodLabel(entry.payment_method) }}
                    </span>
                </template>

                <template #item-paid_at="entry">
                    <span class="whitespace-nowrap text-muted-foreground">
                        {{ formatDate(entry.paid_at) }}
                    </span>
                </template>

                <template #item-payment_status="entry">
                    <StatusPill :tone="statusTone(entry.payment_status)">
                        {{ statusLabel(entry.payment_status) }}
                    </StatusPill>
                </template>

                <template #item-amount_paid="entry">
                    <span
                        class="font-semibold whitespace-nowrap text-green-700"
                    >
                        {{ currency(entry.amount_paid) }}
                    </span>
                </template>
            </DataTable>
        </div>
    </ShopLayout>
</template>
