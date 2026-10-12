<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowDown,
    ArrowUp,
    ArrowUpDown,
    Eye,
    FileText,
    RefreshCcw,
    Search,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import Vue3EasyDataTable, {
    type Header,
    type ServerOptions,
} from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';

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

type SortColumn =
    | 'transaction_reference'
    | 'plan_name'
    | 'payment_method'
    | 'amount'
    | 'paid_at';

const EasyDataTable = Vue3EasyDataTable;
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
const serverOptions = ref<ServerOptions>({
    page: props.payments.current_page,
    rowsPerPage: props.payments.per_page,
    sortBy: props.filters.sort_by ?? 'paid_at',
    sortType: props.filters.sort_direction === 'asc' ? 'asc' : 'desc',
});

const headers: Header[] = [
    { text: 'Reference', value: 'transaction_reference', width: 220 },
    { text: 'Plan', value: 'plan_name', width: 170 },
    { text: 'Method', value: 'payment_method', width: 160 },
    { text: 'Amount', value: 'amount', width: 145 },
    { text: 'Paid on', value: 'paid_at', width: 180 },
    { text: 'Receipt', value: 'actions', width: 120 },
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

function toggleSort(column: SortColumn) {
    if (serverOptions.value.sortBy === column) {
        serverOptions.value.sortType =
            serverOptions.value.sortType === 'asc' ? 'desc' : 'asc';
        return;
    }
    serverOptions.value.sortBy = column;
    serverOptions.value.sortType = 'asc';
}

function sortIcon(column: SortColumn) {
    if (serverOptions.value.sortBy !== column) return ArrowUpDown;
    return serverOptions.value.sortType === 'asc' ? ArrowUp : ArrowDown;
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

            <CardContent class="space-y-4">
                <div class="flex flex-wrap items-center gap-2 pt-2">
                    <div class="relative min-w-56 flex-1">
                        <Search
                            class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                        />
                        <Input
                            v-model="search"
                            class="pl-8"
                            placeholder="Search reference, shop, plan, or payment ID..."
                            @keyup.enter="filterTable"
                        />
                    </div>
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
                    <Button size="sm" variant="outline" @click="resetFilters">
                        <RefreshCcw class="mr-1.5 h-4 w-4" /> Reset
                    </Button>
                </div>

                <div class="overflow-hidden rounded-lg border">
                    <EasyDataTable
                        v-model:server-options="serverOptions"
                        class="shop-data-table"
                        :headers="headers"
                        :items="tableItems"
                        :server-items-length="payments.total"
                        :loading="loading"
                        :rows-items="[5, 10, 15, 20, 50]"
                        rows-of-page-separator-message="out of"
                        rows-per-page-message="Rows per page:"
                        buttons-pagination
                        alternating
                        must-sort
                    >
                        <template
                            v-for="column in [
                                'transaction_reference',
                                'plan_name',
                                'payment_method',
                                'amount',
                                'paid_at',
                            ] as SortColumn[]"
                            :key="column"
                            #[`header-${column}`]="{ text }"
                        >
                            <button
                                type="button"
                                class="sortable-column"
                                @click="toggleSort(column)"
                            >
                                <span>{{ text }}</span>
                                <component
                                    :is="sortIcon(column)"
                                    class="h-3.5 w-3.5"
                                    :class="{
                                        'opacity-60':
                                            serverOptions.sortBy !== column,
                                    }"
                                />
                            </button>
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
                                <p class="font-medium">
                                    {{ payment.plan_name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ payment.billing_months }} month{{
                                        payment.billing_months === 1 ? '' : 's'
                                    }}
                                </p>
                            </div>
                        </template>
                        <template #item-payment_method="payment">
                            <span
                                class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700"
                            >
                                {{ methodLabel(payment.payment_method) }}
                            </span>
                        </template>
                        <template #item-amount="payment">
                            <span class="font-semibold text-emerald-700">{{
                                money(payment.amount)
                            }}</span>
                        </template>
                        <template #item-paid_at="payment">
                            {{
                                payment.paid_at
                                    ? formatDate(payment.paid_at)
                                    : 'Not recorded'
                            }}
                        </template>
                        <template #item-actions="payment">
                            <Button as-child size="sm" variant="outline">
                                <Link
                                    :href="`/shop/payment-history/${payment.order_public_id}`"
                                >
                                    <Eye class="mr-1.5 h-4 w-4" /> Receipt
                                </Link>
                            </Button>
                        </template>
                        <template #empty-message>
                            <div
                                class="py-12 text-center text-sm text-muted-foreground"
                            >
                                <FileText
                                    class="mx-auto mb-3 h-9 w-9 opacity-40"
                                />
                                No successful subscription payments found.
                            </div>
                        </template>
                    </EasyDataTable>
                </div>
            </CardContent>
        </div>
    </ShopLayout>
</template>

<style scoped>
.shop-data-table {
    --easy-table-border: 0;
    --easy-table-row-border: 1px solid var(--border);
    --easy-table-header-background-color: var(--muted);
    --easy-table-header-font-color: var(--muted-foreground);
    --easy-table-header-font-size: 12px;
    --easy-table-header-height: 58px;
    --easy-table-header-item-padding: 10px 12px;
    --easy-table-body-row-background-color: var(--background);
    --easy-table-body-even-row-background-color: color-mix(
        in srgb,
        var(--muted) 35%,
        transparent
    );
    --easy-table-body-row-font-color: var(--foreground);
    --easy-table-body-even-row-font-color: var(--foreground);
    --easy-table-body-row-hover-background-color: color-mix(
        in srgb,
        var(--muted) 65%,
        transparent
    );
    --easy-table-body-row-hover-font-color: var(--foreground);
    --easy-table-body-row-height: 64px;
    --easy-table-body-item-padding: 10px 12px;
    --easy-table-message-font-color: var(--muted-foreground);
    --easy-table-footer-background-color: var(--background);
    --easy-table-footer-font-color: var(--muted-foreground);
    --easy-table-footer-font-size: 12px;
    --easy-table-footer-height: 56px;
    --easy-table-footer-padding: 0 16px;
    --easy-table-buttons-pagination-border: 1px solid var(--border);
    width: 100%;
}

.sortable-column {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: inherit;
    font: inherit;
}

.sortable-column:hover {
    color: var(--foreground);
}
:deep(.vue3-easy-data-table__main) {
    background: var(--background);
}
:deep(.vue3-easy-data-table__main table) {
    min-width: 965px;
}
</style>
