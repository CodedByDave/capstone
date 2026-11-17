<script setup lang="ts">
import {
    DataTable,
    IdentityCell,
    KpiTiles,
    RowActions,
    StatusPill,
    statusTone,
    type DataTableHeader,
} from '@/components/data-table';
import PageHeader from '@/components/shop/PageHeader.vue';
import { Button } from '@/components/ui/button';
import type { DataTableOptions } from '@/composables/useDataTable';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowLeft,
    CalendarClock,
    Eye,
    Mail,
    Package,
    Phone,
    Wallet,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface OrderRow {
    payment_public_id: string;
    order_number: string;
    order_type?: 'laundry' | 'products';
    status: string;
    payment_status: string;
    total_amount: string;
    created_at: string;
    service?: { service_name: string } | null;
}

interface Paginator {
    data: OrderRow[];
    current_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    customer: {
        public_id: string;
        name: string;
        phone: string | null;
        email: string | null;
        created_at: string;
    };
    stats: {
        orders: number;
        spent: number;
        unpaid: number;
        last_order_at: string | null;
    };
    orders: Paginator;
}>();

const page = usePage<AppPageProps>();
const isOwner = computed(() => page.props.auth.user.role === 'owner');
const customersBase = computed(() =>
    isOwner.value ? '/shop/operations/customers' : '/staff/operations/customers',
);
const orderBase = computed(() =>
    isOwner.value ? '/shop/operations/orders' : '/staff/operations/orders',
);

// ─── Table ─────────────────────────────────────────

const tableLoading = ref(false);
const serverOptions = ref<DataTableOptions>({
    page: props.orders.current_page,
    rowsPerPage: props.orders.per_page,
});

watch(
    () => serverOptions.value.page,
    () => {
        router.get(
            `${customersBase.value}/${props.customer.public_id}`,
            { page: serverOptions.value.page },
            {
                only: ['orders'],
                preserveState: true,
                preserveScroll: true,
                replace: true,
                showProgress: false,
                onStart: () => {
                    tableLoading.value = true;
                },
                onFinish: () => {
                    tableLoading.value = false;
                },
            },
        );
    },
);

const headers: DataTableHeader[] = [
    { text: 'Order #', value: 'order_number' },
    { text: 'Service', value: 'service' },
    { text: 'Status', value: 'status' },
    { text: 'Payment', value: 'payment_status' },
    { text: 'Total', value: 'total' },
    { text: 'Date', value: 'created_at' },
    { text: 'Actions', value: 'actions', width: 100, align: 'center' },
];

const tableItems = computed(() =>
    props.orders.data.map((order) => ({
        ...order,
        service:
            order.order_type === 'products'
                ? 'Retail products'
                : (order.service?.service_name ?? '—'),
        total: order.total_amount,
        actions: '',
    })),
);

const formatCurrency = (value: string | number) =>
    `₱${Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;

const formatDate = (date: string | null) =>
    date
        ? new Date(date).toLocaleDateString('en-PH', {
              month: 'short',
              day: 'numeric',
              year: 'numeric',
          })
        : '—';

const formatLabel = (s: string) =>
    s.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());

const orderStatusTone = (s: string) =>
    s === 'picked_up' ? 'neutral' : statusTone(s);
</script>

<template>
    <Head :title="customer.name" />

    <ShopLayout :title="customer.name">
        <div class="space-y-6 px-6">
            <PageHeader
                :title="customer.name"
                :description="`Customer since ${formatDate(customer.created_at)}`"
            >
                <Button
                    variant="back"
                    class="h-11 gap-1.5"
                    @click="router.visit(customersBase)"
                >
                    <ArrowLeft class="h-4 w-4" aria-hidden="true" />
                    Back to Customers
                </Button>
            </PageHeader>

            <div
                class="flex flex-wrap items-center gap-x-8 gap-y-2 rounded-xl border bg-card p-5"
            >
                <IdentityCell :name="customer.name" :secondary="null" />
                <p class="flex items-center gap-2 text-sm">
                    <Phone
                        class="h-4 w-4 text-muted-foreground"
                        aria-hidden="true"
                    />
                    {{ customer.phone || 'No phone on file' }}
                </p>
                <p class="flex items-center gap-2 text-sm">
                    <Mail
                        class="h-4 w-4 text-muted-foreground"
                        aria-hidden="true"
                    />
                    {{ customer.email || 'No email on file' }}
                </p>
            </div>

            <KpiTiles
                :tiles="[
                    {
                        label: 'Orders',
                        value: stats.orders,
                        detail: 'Cancelled orders left out',
                        icon: Package,
                    },
                    {
                        label: 'Total Spent',
                        value: formatCurrency(stats.spent),
                        detail: 'Including tax',
                        icon: Wallet,
                    },
                    {
                        label: 'Unpaid Balance',
                        value: formatCurrency(Math.max(stats.unpaid, 0)),
                        detail: 'Still to collect',
                        icon: AlertCircle,
                    },
                    {
                        label: 'Last Order',
                        value: formatDate(stats.last_order_at),
                        detail: 'Most recent visit',
                        icon: CalendarClock,
                    },
                ]"
            />

            <DataTable
                v-model:options="serverOptions"
                :headers="headers"
                :items="tableItems"
                :total="orders.total"
                :loading="tableLoading"
                :selectable="false"
                item-key="payment_public_id"
                noun="orders"
                :empty-icon="Package"
                empty-message="This customer has no orders yet."
                :resettable="false"
                hide-rows-per-page
            >
                <template #item-order_number="order">
                    <button
                        type="button"
                        class="font-medium text-blue-600 hover:text-blue-800 hover:underline"
                        @click="
                            router.visit(
                                `${orderBase}/${order.payment_public_id}`,
                            )
                        "
                    >
                        {{ order.order_number }}
                    </button>
                </template>

                <template #item-status="order">
                    <StatusPill :tone="orderStatusTone(order.status)">
                        {{ formatLabel(order.status) }}
                    </StatusPill>
                </template>

                <template #item-payment_status="order">
                    <StatusPill :tone="statusTone(order.payment_status)">
                        {{ formatLabel(order.payment_status) }}
                    </StatusPill>
                </template>

                <template #item-total="order">
                    <span class="font-medium whitespace-nowrap">{{
                        formatCurrency(order.total_amount)
                    }}</span>
                </template>

                <template #item-created_at="order">
                    <span class="whitespace-nowrap text-muted-foreground">{{
                        formatDate(order.created_at)
                    }}</span>
                </template>

                <template #item-actions="order">
                    <RowActions
                        :label="`order ${order.order_number}`"
                        :actions="[
                            {
                                label: 'View',
                                icon: Eye,
                                onSelect: () =>
                                    router.visit(
                                        `${orderBase}/${order.payment_public_id}`,
                                    ),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>
    </ShopLayout>
</template>
