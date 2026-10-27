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
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { RefreshCcw, Star } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface RatingRow {
    public_id: string;
    shop_name: string;
    owner_name: string;
    owner_email: string;
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

interface Stats {
    average: number | null;
    count: number;
    this_month: number;
    distribution: Record<number, number>;
}

const props = defineProps<{
    stats: Stats;
    ratings: Paginator;
    filters: Record<string, string | number | undefined>;
}>();

const base = '/admin/platform-ratings';
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Platform Ratings', href: base },
];

const search = ref(String(props.filters.search ?? ''));
const stars = ref(String(props.filters.stars ?? ''));
const tableLoading = ref(false);

const headers: DataTableHeader[] = [
    { text: 'Shop', value: 'shop', width: 210, sortable: true },
    { text: 'Owner', value: 'owner', width: 240, sortable: true },
    { text: 'Rating', value: 'rating', width: 150, sortable: true },
    { text: 'Comment', value: 'comment' },
    { text: 'Date', value: 'created_at', width: 150, sortable: true },
];

const starOptions = computed(() => [
    { value: '', label: 'All', count: props.stats.count },
    ...[5, 4, 3, 2, 1].map((n) => ({
        value: String(n),
        label: `${n} ${n === 1 ? 'star' : 'stars'}`,
        count: props.stats.distribution[n] ?? 0,
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
    props.ratings.data.map((row) => ({
        ...row,
        shop: row.shop_name,
        owner: row.owner_name,
    })),
);

const statCards = computed(() => [
    {
        label: 'Average rating',
        value:
            props.stats.average !== null
                ? `${props.stats.average.toFixed(1)} / 5`
                : '—',
    },
    { label: 'Total ratings', value: props.stats.count },
    { label: 'This month', value: props.stats.this_month },
    {
        label: 'Needs attention (1–2 stars)',
        value:
            (props.stats.distribution[1] ?? 0) +
            (props.stats.distribution[2] ?? 0),
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
        only: ['ratings', 'filters', 'stats'],
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
    <Head title="Platform Ratings" />
    <AdminLayout :breadcrumbs="breadcrumbs" title="Platform Ratings">
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
                item-key="public_id"
                noun="ratings"
                :empty-icon="Star"
                :empty-message="
                    hasFilters
                        ? 'No platform ratings match the current filters.'
                        : 'No platform ratings yet. Shop owners are asked after they complete an order.'
                "
                :resettable="hasFilters"
                :min-width="820"
                @reset="resetFilters"
            >
                <template #toolbar>
                    <DataTableToolbar
                        v-model:search="search"
                        :status="stars"
                        :statuses="starOptions"
                        search-placeholder="Search shop, owner, email or comment..."
                        search-label="Search platform ratings"
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

                <template #item-shop="row">
                    <span class="font-medium">{{ row.shop_name }}</span>
                </template>
                <template #item-owner="row">
                    <IdentityCell
                        :name="row.owner_name"
                        :secondary="row.owner_email"
                    />
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
        </div>
    </AdminLayout>
</template>
