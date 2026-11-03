<script setup lang="ts">
import {
    DataTable,
    DataTableToolbar,
    RowActions,
    StatusPill,
    statusLabel,
    statusTone,
    type DataTableHeader,
} from '@/components/data-table';
import { Button } from '@/components/ui/button';
import type { DataTableOptions } from '@/composables/useDataTable';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Eye, RefreshCcw, ShieldCheck } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

interface VerificationRow {
    public_id: string;
    status: string;
    shop_name: string;
    owner_name: string | null;
    email: string | null;
    created_at: string;
}

const props = defineProps<{
    verifications: {
        data: VerificationRow[];
        current_page: number;
        per_page: number;
        total: number;
    };
    counts: {
        all: number;
        pending: number;
        approved: number;
        rejected: number;
    };
    filters: Record<string, string | number | null | undefined>;
}>();

const page = usePage();
onMounted(() => {
    const flash = page.props.toast as
        | { type: string; message: string }
        | undefined;
    if (flash)
        (flash.type === 'error' ? toast.error : toast.success)(flash.message);
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'KYC Verifications', href: '/admin/kyc-verifications' },
];

const search = ref(String(props.filters.search ?? ''));
const status = ref(String(props.filters.status ?? ''));
const tableLoading = ref(false);

const headers: DataTableHeader[] = [
    { text: 'Shop', value: 'shop', width: 240, sortable: true },
    { text: 'Owner', value: 'owner', width: 200 },
    { text: 'Status', value: 'status', width: 130, sortable: true },
    { text: 'Submitted', value: 'created_at', width: 170, sortable: true },
    { text: 'Actions', value: 'actions', width: 110, align: 'center' },
];

const statusOptions = computed(() => [
    { value: '', label: 'All', count: props.counts.all },
    { value: 'pending', label: 'Pending', count: props.counts.pending },
    { value: 'approved', label: 'Approved', count: props.counts.approved },
    { value: 'rejected', label: 'Rejected', count: props.counts.rejected },
]);

const serverOptions = ref<DataTableOptions>({
    page: props.verifications.current_page,
    rowsPerPage: props.verifications.per_page,
    sortBy: String(props.filters.sort_by ?? 'created_at'),
    sortType: props.filters.sort_direction === 'asc' ? 'asc' : 'desc',
});

const tableItems = computed(() =>
    props.verifications.data.map((v) => ({
        ...v,
        shop: v.shop_name,
        owner: v.owner_name ?? '',
        actions: '',
    })),
);

function applyFilters() {
    router.get(
        '/admin/kyc-verifications',
        {
            search: search.value || undefined,
            status: status.value || undefined,
            sort_by: serverOptions.value.sortBy,
            sort_direction: serverOptions.value.sortType,
            page: serverOptions.value.page,
            per_page: serverOptions.value.rowsPerPage,
        },
        {
            only: ['verifications', 'counts', 'filters'],
            preserveState: true,
            preserveScroll: true,
            replace: true,
            showProgress: false,
            onStart: () => (tableLoading.value = true),
            onFinish: () => (tableLoading.value = false),
        },
    );
}

// A changed filter always starts again from the first page.
function filterTable() {
    if (serverOptions.value.page !== 1) {
        serverOptions.value.page = 1;
        return;
    }
    applyFilters();
}

function setStatus(value: string) {
    status.value = value;
    filterTable();
}

function resetFilters() {
    search.value = '';
    status.value = '';
    const changed =
        serverOptions.value.page !== 1 ||
        serverOptions.value.sortBy !== 'created_at' ||
        serverOptions.value.sortType !== 'desc';
    serverOptions.value = {
        ...serverOptions.value,
        page: 1,
        sortBy: 'created_at',
        sortType: 'desc',
    };
    if (!changed) applyFilters();
}

watch(
    [
        () => serverOptions.value.page,
        () => serverOptions.value.rowsPerPage,
        () => serverOptions.value.sortBy,
        () => serverOptions.value.sortType,
    ],
    () => applyFilters(),
);

const open = (row: VerificationRow) =>
    router.visit(`/admin/kyc-verifications/${row.public_id}`);
</script>

<template>
    <Head title="KYC Verifications" />

    <AdminLayout :breadcrumbs="breadcrumbs" title="KYC Verifications">
        <div class="space-y-4 px-6">
            <div>
                <h1 class="flex items-center gap-2 text-xl font-semibold">
                    <ShieldCheck class="h-5 w-5" /> KYC Verifications
                </h1>
                <p class="text-sm text-muted-foreground">
                    Review the documents shop owners submit before their free
                    trial starts.
                </p>
            </div>

            <DataTable
                v-model:options="serverOptions"
                :headers="headers"
                :items="tableItems"
                :total="verifications.total"
                :loading="tableLoading"
                item-key="public_id"
                noun="verifications"
                :empty-icon="ShieldCheck"
                empty-message="No verifications found."
                :min-width="860"
                :selectable="false"
                @reset="resetFilters"
            >
                <template #toolbar>
                    <DataTableToolbar
                        v-model:search="search"
                        :status="status"
                        :statuses="statusOptions"
                        search-placeholder="Search shop, owner, email..."
                        search-label="Search verifications"
                        status-label="Filter by verification status"
                        @update:status="setStatus"
                        @submit="filterTable"
                    >
                        <template #actions>
                            <Button
                                size="sm"
                                variant="outline"
                                @click="resetFilters"
                            >
                                <RefreshCcw class="mr-1.5 h-4 w-4" /> Reset
                            </Button>
                        </template>
                    </DataTableToolbar>
                </template>

                <template #item-shop="row">
                    <p class="font-semibold">{{ row.shop_name }}</p>
                </template>

                <template #item-owner="row">
                    <p class="font-medium">{{ row.owner_name }}</p>
                    <p class="text-xs text-muted-foreground">{{ row.email }}</p>
                </template>

                <template #item-status="row">
                    <StatusPill :tone="statusTone(row.status)">
                        {{ statusLabel(row.status) }}
                    </StatusPill>
                </template>

                <template #item-actions="row">
                    <RowActions
                        :label="`Actions for ${row.shop_name}`"
                        :actions="[
                            {
                                label: 'Review',
                                icon: Eye,
                                onSelect: () => open(row),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
