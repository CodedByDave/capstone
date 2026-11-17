<script setup lang="ts">
import {
    DataTable,
    DataTableToolbar,
    IdentityCell,
    KpiTiles,
    RowActions,
    type DataTableHeader,
} from '@/components/data-table';
import ActionsMenu from '@/components/shop/ActionsMenu.vue';
import PageHeader from '@/components/shop/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { useCsvImport } from '@/composables/useCsvImport';
import type { DataTableOptions } from '@/composables/useDataTable';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import {
    Download,
    Eye,
    FileText,
    Repeat,
    Upload,
    UserPlus,
    Users,
    Wallet,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Customer {
    public_id: string;
    name: string;
    phone: string | null;
    email: string | null;
    orders_count: number;
    total_spent: number | null;
    last_order_at: string | null;
}

interface Paginator {
    data: Customer[];
    current_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    customers: Paginator;
    summary: { total: number; returning: number; new_this_month: number };
    filters: Record<string, string | number | null | undefined>;
}>();

const page = usePage<AppPageProps>();
const base = computed(() =>
    page.props.auth.user.role === 'owner'
        ? '/shop/operations/customers'
        : '/staff/operations/customers',
);

const prefix = computed(() =>
    page.props.auth.user.role === 'owner' ? '/shop' : '/staff',
);
const { can } = usePermissions();
const canCreate = computed(() => can('Operations', 'create'));

const {
    input: csvInput,
    importing,
    choose: chooseCsv,
    onFileChange: onCsvChange,
} = useCsvImport(
    () => `${prefix.value}/import/customers`,
    ['customers', 'summary'],
);

// ─── Filters and server options ────────────────────

const search = ref(String(props.filters.search ?? ''));
const segment = ref(String(props.filters.segment ?? ''));
const tableLoading = ref(false);

const serverOptions = ref<DataTableOptions>({
    page: props.customers.current_page,
    rowsPerPage: props.customers.per_page,
    sortBy: String(props.filters.sort_by ?? 'last_order'),
    sortType: props.filters.sort_direction === 'asc' ? 'asc' : 'desc',
});

function applyFilters() {
    router.get(
        base.value,
        {
            search: search.value || undefined,
            segment: segment.value || undefined,
            sort_by: serverOptions.value.sortBy,
            sort_direction: serverOptions.value.sortType,
            page: serverOptions.value.page,
            per_page: serverOptions.value.rowsPerPage,
        },
        {
            only: ['customers', 'summary', 'filters'],
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
}

// A changed filter always starts again from the first page.
const filterTable = debounce(() => {
    if (serverOptions.value.page !== 1) {
        serverOptions.value.page = 1;
        return;
    }

    applyFilters();
}, 300);

watch([search, segment], filterTable);

watch(
    [
        () => serverOptions.value.page,
        () => serverOptions.value.rowsPerPage,
        () => serverOptions.value.sortBy,
        () => serverOptions.value.sortType,
    ],
    () => applyFilters(),
);

const hasActiveFilters = computed(() => !!(search.value || segment.value));

function resetTable() {
    search.value = '';
    segment.value = '';
    serverOptions.value = {
        ...serverOptions.value,
        page: 1,
        sortBy: 'last_order',
        sortType: 'desc',
    };
}

const segmentOptions = computed(() => [
    { value: '', label: 'All', count: props.summary.total },
    { value: 'returning', label: 'Returning', count: props.summary.returning },
]);

// ─── Table ─────────────────────────────────────────

const headers: DataTableHeader[] = [
    { text: 'Customer', value: 'name', sortable: true },
    { text: 'Email', value: 'email' },
    { text: 'Orders', value: 'orders', sortable: true },
    { text: 'Total spent', value: 'spent', sortable: true },
    { text: 'Last order', value: 'last_order', sortable: true },
    { text: 'Actions', value: 'actions', width: 100, align: 'center' },
];

const tableItems = computed(() =>
    props.customers.data.map((customer) => ({
        ...customer,
        orders: customer.orders_count,
        spent: customer.total_spent ?? 0,
        last_order: customer.last_order_at,
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
</script>

<template>
    <Head title="Customers" />

    <ShopLayout title="Customers">
        <div class="space-y-6 px-6">
            <PageHeader
                title="Customers"
                description="Each customer once, with every order they placed. Customers are added when you record their first order."
            >
                <ActionsMenu
                    :items="[
                        {
                            label: 'Export CSV',
                            icon: Download,
                            href: `${prefix}/export/customers`,
                        },
                        {
                            label: importing ? 'Importing...' : 'Import CSV',
                            icon: Upload,
                            onSelect: chooseCsv,
                            disabled: importing,
                            hidden: !canCreate,
                        },
                        {
                            label: 'Import template',
                            icon: FileText,
                            href: `${prefix}/import/customers/template`,
                            hidden: !canCreate,
                        },
                    ]"
                />
                <input
                    ref="csvInput"
                    type="file"
                    accept=".csv,text/csv"
                    class="hidden"
                    @change="onCsvChange"
                />
            </PageHeader>

            <KpiTiles
                :tiles="[
                    {
                        label: 'Customers',
                        value: summary.total,
                        detail: 'Everyone who ordered',
                        icon: Users,
                    },
                    {
                        label: 'Returning',
                        value: summary.returning,
                        detail: 'Click to filter',
                        icon: Repeat,
                        active: segment === 'returning',
                        onSelect: () =>
                            (segment =
                                segment === 'returning' ? '' : 'returning'),
                    },
                    {
                        label: 'New This Month',
                        value: summary.new_this_month,
                        detail: 'First order this month',
                        icon: UserPlus,
                    },
                    {
                        label: 'This Page Spent',
                        value: formatCurrency(
                            customers.data.reduce(
                                (sum, c) => sum + Number(c.total_spent ?? 0),
                                0,
                            ),
                        ),
                        detail: 'Customers shown below',
                        icon: Wallet,
                    },
                ]"
            />

            <DataTable
                v-model:options="serverOptions"
                :headers="headers"
                :items="tableItems"
                :total="customers.total"
                :loading="tableLoading"
                :selectable="false"
                item-key="public_id"
                noun="customers"
                :empty-icon="Users"
                :empty-message="
                    hasActiveFilters
                        ? 'No customers found. Try adjusting your filters.'
                        : 'No customers yet. They appear after you record an order.'
                "
                :resettable="hasActiveFilters"
                @reset="resetTable"
            >
                <template #toolbar>
                    <DataTableToolbar
                        v-model:search="search"
                        v-model:status="segment"
                        :statuses="segmentOptions"
                        status-label="Filter customers"
                        search-placeholder="Search by name, phone or email…"
                        search-label="Search customers"
                    >
                        <Button
                            v-if="hasActiveFilters"
                            variant="ghost"
                            class="h-11 gap-1"
                            @click="resetTable"
                        >
                            <X class="h-3.5 w-3.5" /> Clear
                        </Button>
                    </DataTableToolbar>
                </template>

                <template #item-name="customer">
                    <button
                        type="button"
                        class="text-left"
                        @click="router.visit(`${base}/${customer.public_id}`)"
                    >
                        <IdentityCell
                            :name="customer.name"
                            :secondary="customer.phone"
                        />
                    </button>
                </template>

                <template #item-email="customer">
                    <span class="text-muted-foreground">{{
                        customer.email || '—'
                    }}</span>
                </template>

                <template #item-orders="customer">
                    <span class="font-medium tabular-nums">{{
                        customer.orders_count
                    }}</span>
                </template>

                <template #item-spent="customer">
                    <span class="font-medium whitespace-nowrap">{{
                        formatCurrency(customer.total_spent ?? 0)
                    }}</span>
                </template>

                <template #item-last_order="customer">
                    <span class="whitespace-nowrap text-muted-foreground">{{
                        formatDate(customer.last_order_at)
                    }}</span>
                </template>

                <template #item-actions="customer">
                    <RowActions
                        :label="`customer ${customer.name}`"
                        :actions="[
                            {
                                label: 'View orders',
                                icon: Eye,
                                onSelect: () =>
                                    router.visit(
                                        `${base}/${customer.public_id}`,
                                    ),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>
    </ShopLayout>
</template>
