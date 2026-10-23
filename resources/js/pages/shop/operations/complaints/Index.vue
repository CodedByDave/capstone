<script setup lang="ts">
import ComplaintStatusBadge from '@/components/ComplaintStatusBadge.vue';
import SortableHeader from '@/components/SortableHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Eye,
    Loader2,
    RefreshCcw,
    Search,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import Vue3EasyDataTable, {
    type Header,
    type ServerOptions,
} from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';

interface ComplaintRow {
    public_id: string;
    order_number: string;
    customer_name: string;
    type: string;
    type_label: string;
    description: string;
    status: string;
    shop_reply: string | null;
    created_at: string;
    replied_at: string | null;
    photos: { public_id: string; url: string }[];
}

interface Paginator {
    data: ComplaintRow[];
    total: number;
    per_page: number;
    current_page: number;
}

const EasyDataTable = Vue3EasyDataTable;
const props = defineProps<{
    stats: {
        total: number;
        open: number;
        under_review: number;
        resolved: number;
    };
    complaints: Paginator;
    types: Record<string, string>;
    filters: Record<string, string | number | undefined>;
}>();

const { isOwner, can } = usePermissions();
const base = isOwner.value
    ? '/shop/operations/complaints'
    : '/staff/operations/complaints';
const canRespond = computed(() => isOwner.value || can('Operations', 'manage'));

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Operations',
        href: isOwner.value
            ? '/shop/operations/orders'
            : '/staff/operations/orders',
    },
    { title: 'Complaints', href: base },
];

const search = ref(String(props.filters.search ?? ''));
const status = ref(String(props.filters.status ?? ''));
const type = ref(String(props.filters.type ?? ''));
const tableLoading = ref(false);

type SortColumn =
    | 'order_number'
    | 'customer'
    | 'type'
    | 'status'
    | 'created_at';

const headers: Header[] = [
    { text: 'Order', value: 'order_number', width: 150 },
    { text: 'Customer', value: 'customer', width: 170 },
    { text: 'Issue', value: 'type', width: 170 },
    { text: 'Description', value: 'description' },
    { text: 'Status', value: 'status', width: 140 },
    { text: 'Reported', value: 'created_at', width: 130 },
    { text: 'Action', value: 'action', width: 100 },
];

const serverOptions = ref<ServerOptions>({
    page: props.complaints.current_page,
    rowsPerPage: props.complaints.per_page,
    sortBy: String(props.filters.sort_by ?? 'created_at'),
    sortType: props.filters.sort_direction === 'asc' ? 'asc' : 'desc',
});

// Plain strings for the header component; the table library types these more loosely.
const sortBy = computed(() => String(serverOptions.value.sortBy));
const sortType = computed<'asc' | 'desc'>(() =>
    serverOptions.value.sortType === 'asc' ? 'asc' : 'desc',
);

function toggleSort(column: SortColumn) {
    if (serverOptions.value.sortBy === column) {
        serverOptions.value.sortType =
            serverOptions.value.sortType === 'asc' ? 'desc' : 'asc';
        return;
    }

    serverOptions.value.sortBy = column;
    serverOptions.value.sortType = 'asc';
}

const tableItems = computed(() =>
    props.complaints.data.map((row) => ({
        ...row,
        customer: row.customer_name,
    })),
);

const statCards = computed(() => [
    { label: 'Total complaints', value: props.stats.total },
    { label: 'Open', value: props.stats.open },
    { label: 'Under review', value: props.stats.under_review },
    { label: 'Resolved', value: props.stats.resolved },
]);

function query() {
    return {
        search: search.value || undefined,
        status: status.value || undefined,
        type: type.value || undefined,
        sort_by: serverOptions.value.sortBy,
        sort_direction: serverOptions.value.sortType,
        page: serverOptions.value.page,
        per_page: serverOptions.value.rowsPerPage,
    };
}

function applyFilters() {
    router.get(base, query(), {
        only: ['complaints', 'filters', 'stats'],
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
    });
}

function filterTable() {
    if (serverOptions.value.page !== 1) {
        serverOptions.value.page = 1;
        return;
    }

    applyFilters();
}

function resetFilters() {
    search.value = '';
    status.value = '';
    type.value = '';

    const optionsChanged =
        serverOptions.value.page !== 1 ||
        serverOptions.value.sortBy !== 'created_at' ||
        serverOptions.value.sortType !== 'desc';

    serverOptions.value.page = 1;
    serverOptions.value.sortBy = 'created_at';
    serverOptions.value.sortType = 'desc';

    if (!optionsChanged) applyFilters();
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

function formatDate(iso: string) {
    return new Date(iso).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

// ─── Detail and reply ─────────────────────────────────────────────────────────

const selectedId = ref<string | null>(null);
const selected = computed(
    () =>
        props.complaints.data.find(
            (row) => row.public_id === selectedId.value,
        ) ?? null,
);
const detailOpen = computed({
    get: () => selected.value !== null,
    set: (value: boolean) => {
        if (!value) selectedId.value = null;
    },
});
const isClosed = computed(
    () =>
        selected.value !== null &&
        !['open', 'under_review'].includes(selected.value.status),
);

const form = useForm({ status: 'under_review', reply: '' });

function openComplaint(row: ComplaintRow) {
    form.reset();
    form.clearErrors();
    form.status = row.status === 'open' ? 'under_review' : row.status;
    form.reply = row.shop_reply ?? '';
    selectedId.value = row.public_id;
}

function submitResponse() {
    if (!selected.value) return;

    form.patch(`${base}/${selected.value.public_id}`, {
        preserveScroll: true,
        onSuccess: (page) => {
            const flash = page.props.toast as
                | { type: string; message: string }
                | undefined;

            if (flash?.type === 'error') return;

            selectedId.value = null;
        },
    });
}
</script>

<template>
    <Head title="Customer Complaints" />
    <ShopLayout title="Customer Complaints" :breadcrumbs="breadcrumbs">
        <div class="space-y-6 px-6">
            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
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
                <div class="flex flex-wrap items-center gap-2 pt-6">
                    <div class="relative min-w-48 flex-1">
                        <Search
                            class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search order, customer or description..."
                            class="pl-8"
                            @keyup.enter="filterTable"
                        />
                    </div>

                    <label class="sr-only" for="complaints-status-filter"
                        >Filter by status</label
                    >
                    <select
                        id="complaints-status-filter"
                        v-model="status"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                        @change="filterTable"
                    >
                        <option value="">All statuses</option>
                        <option value="open">Open</option>
                        <option value="under_review">Under review</option>
                        <option value="resolved">Resolved</option>
                        <option value="rejected">Closed</option>
                    </select>

                    <label class="sr-only" for="complaints-type-filter"
                        >Filter by issue</label
                    >
                    <select
                        id="complaints-type-filter"
                        v-model="type"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                        @change="filterTable"
                    >
                        <option value="">All issues</option>
                        <option
                            v-for="(label, value) in types"
                            :key="value"
                            :value="value"
                        >
                            {{ label }}
                        </option>
                    </select>

                    <Button
                        size="sm"
                        variant="outline"
                        class="border-slate-300 bg-slate-50 text-slate-700 hover:bg-slate-100 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-300"
                        @click="resetFilters"
                    >
                        <RefreshCcw class="mr-1.5 h-4 w-4" />
                        Reset
                    </Button>
                </div>

                <div class="overflow-hidden rounded-lg border">
                    <EasyDataTable
                        v-model:server-options="serverOptions"
                        class="complaints-data-table"
                        :headers="headers"
                        :items="tableItems"
                        :server-items-length="complaints.total"
                        :loading="tableLoading"
                        :rows-items="[5, 10, 15, 20, 50, 100]"
                        rows-of-page-separator-message="out of"
                        rows-per-page-message="Rows per page:"
                        empty-message="No complaints yet. Customers can report a problem once an order is completed."
                        buttons-pagination
                        alternating
                        must-sort
                    >
                        <template #header-order_number="{ text }">
                            <SortableHeader
                                :label="text"
                                column="order_number"
                                :sort-by="sortBy"
                                :sort-type="sortType"
                                @toggle="toggleSort('order_number')"
                            />
                        </template>
                        <template #header-customer="{ text }">
                            <SortableHeader
                                :label="text"
                                column="customer"
                                :sort-by="sortBy"
                                :sort-type="sortType"
                                @toggle="toggleSort('customer')"
                            />
                        </template>
                        <template #header-type="{ text }">
                            <SortableHeader
                                :label="text"
                                column="type"
                                :sort-by="sortBy"
                                :sort-type="sortType"
                                @toggle="toggleSort('type')"
                            />
                        </template>
                        <template #header-status="{ text }">
                            <SortableHeader
                                :label="text"
                                column="status"
                                :sort-by="sortBy"
                                :sort-type="sortType"
                                @toggle="toggleSort('status')"
                            />
                        </template>
                        <template #header-created_at="{ text }">
                            <SortableHeader
                                :label="text"
                                column="created_at"
                                :sort-by="sortBy"
                                :sort-type="sortType"
                                @toggle="toggleSort('created_at')"
                            />
                        </template>

                        <template #item-order_number="row">
                            <span class="font-mono text-xs">{{
                                row.order_number
                            }}</span>
                        </template>
                        <template #item-type="row">
                            <span
                                class="inline-flex items-center gap-1.5 text-sm"
                            >
                                <AlertTriangle
                                    class="h-3.5 w-3.5 text-amber-500"
                                    aria-hidden="true"
                                />
                                {{ row.type_label }}
                            </span>
                        </template>
                        <template #item-description="row">
                            <p class="line-clamp-2 py-1 text-sm">
                                {{ row.description }}
                            </p>
                        </template>
                        <template #item-status="row">
                            <ComplaintStatusBadge :status="row.status" />
                        </template>
                        <template #item-created_at="row">
                            <span class="text-xs text-muted-foreground">{{
                                formatDate(row.created_at)
                            }}</span>
                        </template>
                        <template #item-action="row">
                            <Button
                                size="sm"
                                variant="outline"
                                @click="openComplaint(row)"
                            >
                                <Eye
                                    class="mr-1.5 h-4 w-4"
                                    aria-hidden="true"
                                />
                                View
                            </Button>
                        </template>
                    </EasyDataTable>
                </div>
            </CardContent>
        </div>

        <Dialog v-model:open="detailOpen">
            <DialogContent
                v-if="selected"
                class="max-h-[90vh] max-w-lg overflow-y-auto"
            >
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        {{ selected.type_label }}
                        <ComplaintStatusBadge :status="selected.status" />
                    </DialogTitle>
                    <DialogDescription>
                        Order #{{ selected.order_number }} ·
                        {{ selected.customer_name }} ·
                        {{ formatDate(selected.created_at) }}
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-2">
                    <p class="text-sm whitespace-pre-line">
                        {{ selected.description }}
                    </p>

                    <div
                        v-if="selected.photos.length"
                        class="flex flex-wrap gap-2"
                    >
                        <a
                            v-for="photo in selected.photos"
                            :key="photo.public_id"
                            :href="photo.url"
                            target="_blank"
                            rel="noopener"
                        >
                            <img
                                :src="photo.url"
                                alt="Photo attached by the customer"
                                class="h-24 w-24 rounded-lg border object-cover"
                                loading="lazy"
                            />
                        </a>
                    </div>

                    <div
                        v-if="isClosed || !canRespond"
                        class="rounded-lg bg-muted p-3 text-sm"
                    >
                        <template v-if="selected.shop_reply">
                            <p class="mb-1 text-xs font-semibold uppercase">
                                Your reply
                            </p>
                            {{ selected.shop_reply }}
                        </template>
                        <p v-else class="text-muted-foreground">
                            No reply yet.
                        </p>
                        <p
                            v-if="isClosed"
                            class="mt-2 text-xs text-muted-foreground"
                        >
                            This report is closed and can no longer be changed.
                        </p>
                    </div>

                    <div v-else class="space-y-3">
                        <div class="space-y-1.5">
                            <label
                                for="complaint-status"
                                class="text-sm font-medium"
                                >Status</label
                            >
                            <select
                                id="complaint-status"
                                v-model="form.status"
                                class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm"
                            >
                                <option value="under_review">
                                    Under review
                                </option>
                                <option value="resolved">Resolved</option>
                                <option value="rejected">
                                    Close without action
                                </option>
                            </select>
                            <p
                                v-if="form.errors.status"
                                class="text-xs text-red-500"
                                role="alert"
                            >
                                {{ form.errors.status }}
                            </p>
                        </div>
                        <div class="space-y-1.5">
                            <label
                                for="complaint-reply"
                                class="text-sm font-medium"
                            >
                                Reply to the customer
                                <span
                                    v-if="form.status === 'under_review'"
                                    class="font-normal text-muted-foreground"
                                    >(optional)</span
                                >
                            </label>
                            <Textarea
                                id="complaint-reply"
                                v-model="form.reply"
                                rows="4"
                                maxlength="1000"
                                placeholder="Explain what you will do or what happened"
                            />
                            <div class="flex justify-between text-xs">
                                <span class="text-red-500" role="alert">{{
                                    form.errors.reply
                                }}</span>
                                <span class="text-muted-foreground"
                                    >{{ form.reply.length }}/1000</span
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <DialogFooter class="gap-2 sm:gap-2">
                    <Button variant="outline" @click="selectedId = null">
                        Close
                    </Button>
                    <Button
                        v-if="!isClosed && canRespond"
                        :disabled="form.processing"
                        @click="submitResponse"
                    >
                        <Loader2
                            v-if="form.processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        {{ form.processing ? 'Sending…' : 'Send response' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </ShopLayout>
</template>

<style scoped>
.complaints-data-table {
    --easy-table-border: 0;
    --easy-table-row-border: 1px solid var(--border);
    --easy-table-header-background-color: var(--muted);
    --easy-table-header-font-color: var(--muted-foreground);
    --easy-table-header-font-size: 12px;
    --easy-table-header-height: 52px;
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
    --easy-table-body-row-height: 56px;
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

:deep(.vue3-easy-data-table__main) {
    background: var(--background);
}

:deep(.vue3-easy-data-table__main table) {
    min-width: 900px;
}
</style>
