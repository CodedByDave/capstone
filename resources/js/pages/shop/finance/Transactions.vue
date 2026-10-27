<script setup lang="ts">
import {
    DataTable,
    DataTableToolbar,
    StatusPill,
    type DataTableHeader,
} from '@/components/data-table';
import { useDataTable } from '@/composables/useDataTable';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    ArrowUpDown,
    DollarSign,
    TrendingDown,
    TrendingUp,
} from 'lucide-vue-next';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Transaction {
    id: string;
    type: 'income' | 'expense';
    date: string;
    title: string;
    reference: string;
    category: string;
    amount: number;
}

interface Summary {
    total_income: number;
    total_expenses: number;
    net: number;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    transactions: Transaction[];
    summary: Summary;
    filters: { dateFrom: string; dateTo: string };
}>();

// ─── RBAC ─────────────────────────────────────────────────────────────────────

const { isOwner } = usePermissions();
const base = computed(() => (isOwner.value ? '/shop' : '/staff'));

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Finance', href: `${base.value}/finance` },
    { title: 'Transactions', href: `${base.value}/finance/transactions` },
];

// ─── Filters ──────────────────────────────────────────────────────────────────

const dateFrom = ref(props.filters.dateFrom ?? '');
const dateTo = ref(props.filters.dateTo ?? '');
const {
    search,
    status: typeFilter,
    options,
    pageItems,
    total,
    statusCounts,
    isFiltered,
    reset,
} = useDataTable(() => props.transactions, {
    searchFields: (tx) => [tx.title, tx.reference, tx.category],
    status: (tx) => tx.type,
    sortBy: 'date',
    sortType: 'desc',
});

const typeOptions = computed(() => [
    {
        value: '',
        label: 'All',
        count:
            (statusCounts.value.income ?? 0) +
            (statusCounts.value.expense ?? 0),
    },
    { value: 'income', label: 'Income', count: statusCounts.value.income ?? 0 },
    {
        value: 'expense',
        label: 'Expenses',
        count: statusCounts.value.expense ?? 0,
    },
]);

const headers: DataTableHeader[] = [
    { text: 'Date', value: 'date', sortable: true, width: 130 },
    { text: 'Type', value: 'type', sortable: true, width: 120 },
    { text: 'Description', value: 'title', sortable: true },
    { text: 'Category / method', value: 'category', sortable: true },
    { text: 'Reference', value: 'reference' },
    {
        text: 'Amount',
        value: 'amount',
        sortable: true,
        align: 'right',
        width: 150,
    },
];

function applyFilters() {
    router.get(
        `${base.value}/finance/transactions`,
        {
            date_from: dateFrom.value || undefined,
            date_to: dateTo.value || undefined,
        },
        { replace: true },
    );
}

function clearFilters() {
    const now = new Date();
    dateFrom.value = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01`;
    dateTo.value = now.toISOString().slice(0, 10);
    router.get(
        `${base.value}/finance/transactions`,
        { date_from: dateFrom.value, date_to: dateTo.value },
        { replace: true },
    );
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function currency(val: number): string {
    return `₱${val.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
}

function formatDate(val: string): string {
    return new Date(val + 'T00:00:00').toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}
</script>

<template>
    <Head title="Transactions" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Finance">
        <div class="space-y-6 px-6">
            <!-- ── Header ──────────────────────────────────────── -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2 class="text-lg font-semibold">Transactions</h2>
                    <p class="text-sm text-muted-foreground">
                        Combined ledger of income and expenses for the selected
                        period.
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="router.visit(`${base}/finance/income`)"
                    >
                        Income
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        @click="router.visit(`${base}/finance/expenses`)"
                    >
                        Expenses
                    </Button>
                </div>
            </div>

            <!-- ── Summary Cards ───────────────────────────────── -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Card>
                    <CardContent class="flex items-center gap-3 pt-4 pb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100"
                        >
                            <TrendingUp class="h-5 w-5 text-green-600" />
                        </div>
                        <div>
                            <p class="text-lg font-bold text-green-700">
                                {{ currency(summary.total_income) }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Total Income
                            </p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 pt-4 pb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100"
                        >
                            <TrendingDown class="h-5 w-5 text-red-600" />
                        </div>
                        <div>
                            <p class="text-lg font-bold text-red-600">
                                {{ currency(summary.total_expenses) }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Total Expenses
                            </p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 pt-4 pb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg"
                            :class="
                                summary.net >= 0
                                    ? 'bg-blue-100'
                                    : 'bg-orange-100'
                            "
                        >
                            <DollarSign
                                class="h-5 w-5"
                                :class="
                                    summary.net >= 0
                                        ? 'text-blue-600'
                                        : 'text-orange-600'
                                "
                            />
                        </div>
                        <div>
                            <p
                                class="text-lg font-bold"
                                :class="
                                    summary.net >= 0
                                        ? 'text-blue-700'
                                        : 'text-orange-600'
                                "
                            >
                                {{ currency(summary.net) }}
                            </p>
                            <p class="text-xs text-muted-foreground">Net</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- ── Ledger Table ────────────────────────────────── -->
            <DataTable
                v-model:options="options"
                :headers="headers"
                :items="pageItems"
                :total="total"
                noun="transactions"
                :empty-icon="ArrowUpDown"
                :empty-message="
                    isFiltered
                        ? 'No transactions match the current filters.'
                        : 'No transactions found for the selected period.'
                "
                :resettable="isFiltered"
                :min-width="820"
                @reset="reset"
            >
                <template #toolbar>
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <Input
                                v-model="dateFrom"
                                type="date"
                                class="sm:w-40"
                                aria-label="From date"
                            />
                            <span class="text-sm text-muted-foreground"
                                >to</span
                            >
                            <Input
                                v-model="dateTo"
                                type="date"
                                class="sm:w-40"
                                aria-label="To date"
                            />
                            <Button @click="applyFilters">Apply</Button>
                            <Button variant="outline" @click="clearFilters"
                                >This Month</Button
                            >
                        </div>
                        <DataTableToolbar
                            v-model:search="search"
                            v-model:status="typeFilter"
                            :statuses="typeOptions"
                            search-placeholder="Search description, reference, category..."
                            search-label="Search transactions"
                            status-label="Filter by transaction type"
                        />
                    </div>
                </template>

                <template #item-date="tx">
                    <span
                        class="text-xs whitespace-nowrap text-muted-foreground"
                    >
                        {{ formatDate(tx.date) }}
                    </span>
                </template>

                <template #item-type="tx">
                    <StatusPill
                        :tone="tx.type === 'income' ? 'success' : 'danger'"
                        :icon="tx.type === 'income' ? TrendingUp : TrendingDown"
                    >
                        {{ tx.type === 'income' ? 'Income' : 'Expense' }}
                    </StatusPill>
                </template>

                <template #item-title="tx">
                    <span class="block max-w-[220px] truncate font-medium">
                        {{ tx.title }}
                    </span>
                </template>

                <template #item-category="tx">
                    <span class="text-muted-foreground">{{ tx.category }}</span>
                </template>

                <template #item-reference="tx">
                    <span class="text-xs text-muted-foreground">
                        {{ tx.reference }}
                    </span>
                </template>

                <template #item-amount="tx">
                    <span
                        class="font-bold whitespace-nowrap"
                        :class="
                            tx.type === 'income'
                                ? 'text-green-700'
                                : 'text-red-600'
                        "
                    >
                        {{ tx.type === 'income' ? '+' : '-'
                        }}{{ currency(tx.amount) }}
                    </span>
                </template>

                <template #footer>
                    <span
                        class="font-semibold"
                        :class="
                            summary.net >= 0
                                ? 'text-blue-700'
                                : 'text-orange-600'
                        "
                    >
                        Net:
                        {{
                            currency(
                                typeFilter === 'income'
                                    ? summary.total_income
                                    : typeFilter === 'expense'
                                      ? summary.total_expenses
                                      : summary.net,
                            )
                        }}
                    </span>
                </template>
            </DataTable>
        </div>
    </ShopLayout>
</template>
