<script setup lang="ts">
import {
    DataTable,
    DataTableToolbar,
    RowActions,
    type DataTableHeader,
} from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import type { DataTableOptions } from '@/composables/useDataTable';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Eye, FileText, RefreshCcw, Search } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface PaymentItem {
    id: number;
    transaction_reference: string;
    order_public_id: string;
    shop_name: string;
    plan_name: string;
    billing_months: number;
    payment_method: string;
    amount: number;
    paid_at: string | null;
    expires_at: string | null;
}

interface Paginator {
    data: PaymentItem[];
    current_page: number;
    per_page: number;
    total: number;
}

interface Summary {
    payment_count: number;
    total_paid: number;
    latest_paid_at: string | null;
}

const props = defineProps<{
    payments: Paginator;
    summary: Summary;
    filters: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/shop/dashboard' },
    { title: 'Payment History', href: '/shop/payment-history' },
];
const search = ref(props.filters.search ?? '');
const paymentMethod = ref(props.filters.payment_method ?? '');
const loading = ref(false);
const serverOptions = ref<DataTableOptions>({
    page: props.payments.current_page,
    rowsPerPage: props.payments.per_page,
    sortBy: props.filters.sort_by ?? 'paid_at',
    sortType: props.filters.sort_direction === 'asc' ? 'asc' : 'desc',
});

const headers: DataTableHeader[] = [
    {
        text: 'Reference',
        value: 'transaction_reference',
        width: 220,
        sortable: true,
    },
    { text: 'Plan', value: 'plan_name', width: 170, sortable: true },
    { text: 'Method', value: 'payment_method', width: 160, sortable: true },
    { text: 'Amount', value: 'amount', width: 145, sortable: true },
    { text: 'Paid on', value: 'paid_at', width: 180, sortable: true },
    { text: 'Receipt', value: 'actions', width: 120, align: 'center' },
];

const tableItems = computed(() =>
    props.payments.data.map((payment) => ({ ...payment, actions: '' })),
);
const statCards = computed(() => [
    { label: 'Successful payments', value: props.summary.payment_count },
    { label: 'Total paid', value: money(props.summary.total_paid) },
    {
        label: 'Latest payment',
        value: props.summary.latest_paid_at
            ? formatDate(props.summary.latest_paid_at)
            : 'No payments',
    },
]);

function query() {
    return {
        search: search.value || undefined,
        payment_method: paymentMethod.value || undefined,
        sort_by: serverOptions.value.sortBy,
        sort_direction: serverOptions.value.sortType,
        page: serverOptions.value.page,
        per_page: serverOptions.value.rowsPerPage,
    };
}

function loadPayments() {
    router.get('/shop/payment-history', query(), {
        only: ['payments', 'summary', 'filters'],
        preserveState: true,
        preserveScroll: true,
        replace: true,
        showProgress: false,
        onStart: () => (loading.value = true),
        onFinish: () => (loading.value = false),
    });
}

function filterTable() {
    if (serverOptions.value.page !== 1) {
        serverOptions.value.page = 1;
        return;
    }
    loadPayments();
}

function resetFilters() {
    search.value = '';
    paymentMethod.value = '';
    serverOptions.value = {
        page: 1,
        rowsPerPage: 10,
        sortBy: 'paid_at',
        sortType: 'desc',
    };
    loadPayments();
}

function money(amount: number) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(amount);
}

function formatDate(date: string) {
    return new Date(date).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function methodLabel(method: string) {
    const labels: Record<string, string> = {
        gcash: 'GCash',
        maya: 'Maya',
        card: 'Card',
        grab_pay: 'GrabPay',
        dob: 'Direct Online Banking',
        billease: 'BillEase',
    };
    return labels[method] ?? method.replaceAll('_', ' ');
}

watch(
    () => [
        serverOptions.value.page,
        serverOptions.value.rowsPerPage,
        serverOptions.value.sortBy,
        serverOptions.value.sortType,
    ],
    (current, previous) => {
        if (current.some((value, index) => value !== previous[index]))
            loadPayments();
    },
);
</script>

<template>
    <Head title="Payment History" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Payment History">
        <div class="space-y-6 px-6 py-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <Card v-for="item in statCards" :key="item.label">
                    <CardContent class="p-4">
                        <p
                            class="mb-3 text-xs font-medium text-muted-foreground"
                        >
                            {{ item.label }}
                        </p>
                        <p class="text-2xl font-bold">{{ item.value }}</p>
                    </CardContent>
                </Card>
            </div>

            <DataTable
                v-model:options="serverOptions"
                :headers="headers"
                :items="tableItems"
                :total="payments.total"
                :loading="loading"
                noun="payments"
                :empty-icon="FileText"
                empty-message="No successful subscription payments found."
                :rows-items="[5, 10, 15, 20, 50]"
                :min-width="965"
                @reset="resetFilters"
            >
                <template #toolbar>
                    <DataTableToolbar
                        v-model:search="search"
                        search-placeholder="Search reference, shop, plan, or payment ID..."
                        search-label="Search payments"
                        @submit="filterTable"
                    >
                        <select
                            v-model="paymentMethod"
                            class="h-9 rounded-md border bg-background px-3 text-sm"
                            aria-label="Filter by payment method"
                            @change="filterTable"
                        >
                            <option value="">All payment methods</option>
                            <option value="gcash">GCash</option>
                            <option value="maya">Maya</option>
                            <option value="card">Card</option>
                            <option value="grab_pay">GrabPay</option>
                            <option value="dob">Direct Online Banking</option>
                            <option value="billease">BillEase</option>
                        </select>
                        <Button size="sm" @click="filterTable"
                            ><Search class="mr-1.5 h-4 w-4" /> Search</Button
                        >
                        <Button
                            size="sm"
                            variant="outline"
                            @click="resetFilters"
                        >
                            <RefreshCcw class="mr-1.5 h-4 w-4" /> Reset
                        </Button>
                    </DataTableToolbar>
                </template>

                <template #item-transaction_reference="payment">
                    <div>
                        <p class="font-mono text-sm font-semibold">
                            {{ payment.transaction_reference }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ payment.shop_name }}
                        </p>
                    </div>
                </template>
                <template #item-plan_name="payment">
                    <div>
                        <p class="font-medium">{{ payment.plan_name }}</p>
                        <p class="text-xs text-muted-foreground">
                            {{ payment.billing_months }} month{{
                                payment.billing_months === 1 ? '' : 's'
                            }}
                        </p>
                    </div>
                </template>
                <template #item-payment_method="payment">
                    <span
                        class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium whitespace-nowrap text-blue-700"
                    >
                        {{ methodLabel(payment.payment_method) }}
                    </span>
                </template>
                <template #item-amount="payment">
                    <span
                        class="font-semibold whitespace-nowrap text-emerald-700"
                        >{{ money(payment.amount) }}</span
                    >
                </template>
                <template #item-paid_at="payment">
                    <span class="whitespace-nowrap">
                        {{
                            payment.paid_at
                                ? formatDate(payment.paid_at)
                                : 'Not recorded'
                        }}
                    </span>
                </template>
                <template #item-actions="payment">
                    <RowActions
                        :label="`payment ${payment.transaction_reference}`"
                        :actions="[
                            {
                                label: 'View receipt',
                                icon: Eye,
                                onSelect: () =>
                                    router.visit(
                                        `/shop/payment-history/${payment.order_public_id}`,
                                    ),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>
    </ShopLayout>
</template>
