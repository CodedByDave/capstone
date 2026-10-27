<script setup lang="ts">
import RatingStars from '@/components/RatingStars.vue';
import {
    DataTable,
    DataTableToolbar,
    IdentityCell,
    type DataTableHeader,
} from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import type { DataTableOptions } from '@/composables/useDataTable';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { RefreshCcw, Star } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface RatingRow {
    order_number: string;
    customer_name: string;
    rating: number;
    comment: string | null;
    created_at: string;
}

interface Paginator {
    data: RatingRow[];
    total: number;
    per_page: number;
    current_page: number;
}

interface Summary {
    average: number | null;
    count: number;
    distribution: Record<number, number>;
}

const props = defineProps<{
    summary: Summary;
    ratings: Paginator;
    filters: Record<string, string | number | undefined>;
}>();

const { isOwner } = usePermissions();
const base = isOwner.value
    ? '/shop/operations/ratings'
    : '/staff/operations/ratings';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Operations',
        href: isOwner.value
            ? '/shop/operations/orders'
            : '/staff/operations/orders',
    },
    { title: 'Ratings', href: base },
];

const search = ref(String(props.filters.search ?? ''));
const stars = ref(String(props.filters.stars ?? ''));
const tableLoading = ref(false);

const headers: DataTableHeader[] = [
    { text: 'Order', value: 'order_number', width: 170, sortable: true },
    { text: 'Customer', value: 'customer', width: 210, sortable: true },
    { text: 'Rating', value: 'rating', width: 150, sortable: true },
    { text: 'Comment', value: 'comment' },
    { text: 'Date', value: 'created_at', width: 150, sortable: true },
];

const starOptions = computed(() => [
    { value: '', label: 'All', count: props.summary.count },
    ...[5, 4, 3, 2, 1].map((n) => ({
        value: String(n),
        label: `${n} ${n === 1 ? 'star' : 'stars'}`,
        count: props.summary.distribution[n] ?? 0,
    })),
]);

const hasFilters = computed(() => search.value !== '' || stars.value !== '');

function setStars(value: string) {
    stars.value = value;
    filterTable();
}

const serverOptions = ref<DataTableOptions>({
    page: props.ratings.current_page,
    rowsPerPage: props.ratings.per_page,
    sortBy: String(props.filters.sort_by ?? 'created_at'),
    sortType: props.filters.sort_direction === 'asc' ? 'asc' : 'desc',
});

const tableItems = computed(() =>
    props.ratings.data.map((row) => ({ ...row, customer: row.customer_name })),
);

const fiveStarShare = computed(() =>
    props.summary.count > 0
        ? Math.round(
              ((props.summary.distribution[5] ?? 0) / props.summary.count) *
                  100,
          )
        : 0,
);

const statCards = computed(() => [
    {
        label: 'Average rating',
        value:
            props.summary.average !== null
                ? `${props.summary.average.toFixed(1)} / 5`
                : '—',
    },
    { label: 'Total ratings', value: props.summary.count },
    {
        label: '5-star ratings',
        value: `${props.summary.distribution[5] ?? 0} (${fiveStarShare.value}%)`,
    },
    {
        label: 'Needs attention (1–2 stars)',
        value:
            (props.summary.distribution[1] ?? 0) +
            (props.summary.distribution[2] ?? 0),
    },
]);

function query() {
    return {
        search: search.value || undefined,
        stars: stars.value || undefined,
        sort_by: serverOptions.value.sortBy,
        sort_direction: serverOptions.value.sortType,
        page: serverOptions.value.page,
        per_page: serverOptions.value.rowsPerPage,
    };
}

function applyFilters() {
    router.get(base, query(), {
        only: ['ratings', 'filters', 'summary'],
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
    stars.value = '';

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
</script>

<template>
    <Head title="Customer Ratings" />
    <ShopLayout title="Customer Ratings" :breadcrumbs="breadcrumbs">
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

            <DataTable
                v-model:options="serverOptions"
                :headers="headers"
                :items="tableItems"
                :total="ratings.total"
                :loading="tableLoading"
                item-key="order_number"
                noun="ratings"
                :empty-icon="Star"
                :empty-message="
                    hasFilters
                        ? 'No ratings match the current filters.'
                        : 'No ratings yet. Customers can rate an order once it is completed.'
                "
                :resettable="hasFilters"
                :min-width="760"
                @reset="resetFilters"
            >
                <template #toolbar>
                    <DataTableToolbar
                        v-model:search="search"
                        :status="stars"
                        :statuses="starOptions"
                        search-placeholder="Search order, customer or comment..."
                        search-label="Search ratings"
                        status-label="Filter by stars"
                        @update:status="setStars"
                        @submit="filterTable"
                    >
                        <Button
                            size="sm"
                            variant="outline"
                            class="border-slate-300 bg-slate-50 text-slate-700 hover:bg-slate-100 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-300"
                            @click="resetFilters"
                        >
                            <RefreshCcw class="mr-1.5 h-4 w-4" />
                            Reset
                        </Button>
                    </DataTableToolbar>
                </template>

                <template #item-order_number="row">
                    <span class="font-mono text-xs">{{
                        row.order_number
                    }}</span>
                </template>
                <template #item-customer="row">
                    <IdentityCell :name="row.customer_name" />
                </template>
                <template #item-rating="row">
                    <span class="inline-flex items-center gap-2">
                        <RatingStars :value="row.rating" />
                        <span class="text-xs font-medium">{{
                            row.rating
                        }}</span>
                    </span>
                </template>
                <template #item-comment="row">
                    <p v-if="row.comment" class="line-clamp-2 py-1 text-sm">
                        {{ row.comment }}
                    </p>
                    <span v-else class="text-xs text-muted-foreground"
                        >No comment</span
                    >
                </template>
                <template #item-created_at="row">
                    <span
                        class="text-xs whitespace-nowrap text-muted-foreground"
                        >{{ formatDate(row.created_at) }}</span
                    >
                </template>
            </DataTable>
            <p class="flex items-center gap-1.5 text-xs text-muted-foreground">
                <Star class="h-3.5 w-3.5" aria-hidden="true" />
                Customers see only your average rating. Comments are visible to
                your shop only.
            </p>
        </div>
    </ShopLayout>
</template>
