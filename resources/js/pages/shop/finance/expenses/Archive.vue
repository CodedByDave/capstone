<script setup lang="ts">
import {
    DataTable,
    type DataTableHeader,
    RowActions,
} from '@/components/data-table';
import { usePaginatorTable } from '@/composables/useDataTable';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import { Button } from '@/components/ui/button';
import { RotateCcw, TrendingDown } from 'lucide-vue-next';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Expense {
    id: number;
    public_id: string;
    title: string;
    amount: string;
    expense_date: string;
    description: string | null;
    category: { id: number; name: string } | null;
    creator: { id: number; name: string } | null;
    deleted_at: string;
}

interface PaginatedExpenses {
    data: Expense[];
    current_page: number;
    last_page: number;
    per_page?: number;
    total: number;
    from: number | null;
    to: number | null;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{ expenses: PaginatedExpenses }>();

// ─── RBAC ─────────────────────────────────────────────────────────────────────

const { isOwner } = usePermissions();
const base = computed(() => (isOwner.value ? '/shop' : '/staff'));

// ─── Flash toast ──────────────────────────────────────────────────────────────

const page = usePage<AppPageProps>();

onMounted(() => {
    const flashToast = page.props.toast as
        | { type: string; message: string }
        | undefined;
    if (!flashToast) return;
    switch (flashToast.type) {
        case 'success':
            toast.success(flashToast.message);
            break;
        case 'error':
            toast.error(flashToast.message);
            break;
        default:
            toast(flashToast.message);
    }
});

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Finance', href: `${base.value}/finance` },
    { title: 'Expenses', href: `${base.value}/finance/expenses` },
    { title: 'Archive', href: `${base.value}/finance/expenses/archive` },
];

// ─── Actions ──────────────────────────────────────────────────────────────────

function restore(publicId: string) {
    router.post(
        `${base.value}/finance/expenses/${publicId}/restore`,
        {},
        { preserveScroll: true },
    );
}

// ─── Table ────────────────────────────────────────────────────────────────────

const { options, loading } = usePaginatorTable(
    () => props.expenses,
    ['expenses'],
);

const headers: DataTableHeader[] = [
    { text: 'Title', value: 'title' },
    { text: 'Category', value: 'category' },
    { text: 'Date', value: 'expense_date', width: 140 },
    { text: 'Amount', value: 'amount', align: 'right', width: 140 },
    { text: 'Actions', value: 'actions', align: 'center', width: 100 },
];

// ─── Helpers ──────────────────────────────────────────────────────────────────

function currency(val: string | number | null): string {
    const num = typeof val === 'string' ? parseFloat(val) : (val ?? 0);
    return `₱${num.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
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
    <Head title="Expense Archive" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Finance">
        <div class="space-y-6 px-6">
            <!-- ── Header ──────────────────────────────────────── -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Expense Archive</h2>
                    <p class="text-sm text-muted-foreground">
                        Archived expenses. Restore any record to bring it back.
                    </p>
                </div>
                <Button
                    variant="outline"
                    @click="router.visit(`${base}/finance/expenses`)"
                >
                    Back to Expenses
                </Button>
            </div>

            <!-- ── Table ───────────────────────────────────────── -->
            <DataTable
                v-model:options="options"
                :headers="headers"
                :items="expenses.data"
                :total="expenses.total"
                :loading="loading"
                noun="archived expenses"
                :empty-icon="TrendingDown"
                empty-message="No archived expenses."
                :resettable="false"
                :min-width="720"
                hide-rows-per-page
            >
                <template #item-title="exp">
                    <p class="font-medium">{{ exp.title }}</p>
                    <p
                        v-if="exp.description"
                        class="text-xs text-muted-foreground"
                    >
                        {{ exp.description }}
                    </p>
                </template>

                <template #item-category="exp">
                    <span class="text-muted-foreground">
                        {{ exp.category?.name ?? '—' }}
                    </span>
                </template>

                <template #item-expense_date="exp">
                    <span class="whitespace-nowrap text-muted-foreground">
                        {{ formatDate(exp.expense_date) }}
                    </span>
                </template>

                <template #item-amount="exp">
                    <span class="font-semibold whitespace-nowrap text-red-600">
                        {{ currency(exp.amount) }}
                    </span>
                </template>

                <template #item-actions="exp">
                    <RowActions
                        :label="exp.title"
                        :actions="[
                            {
                                label: 'Restore',
                                icon: RotateCcw,
                                onSelect: () => restore(exp.public_id),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>
    </ShopLayout>
</template>
