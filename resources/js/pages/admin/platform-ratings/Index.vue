<script setup lang="ts">
import RatingStars from '@/components/RatingStars.vue';
import SortableHeader from '@/components/SortableHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { RefreshCcw, Search } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import Vue3EasyDataTable, {
    type Header,
    type ServerOptions,
} from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';

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

const EasyDataTable = Vue3EasyDataTable;
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

type SortColumn = 'shop' | 'owner' | 'rating' | 'created_at';

const headers: Header[] = [
    { text: 'Shop', value: 'shop', width: 210 },
    { text: 'Owner', value: 'owner', width: 220 },
    { text: 'Rating', value: 'rating', width: 150 },
    { text: 'Comment', value: 'comment' },
    { text: 'Date', value: 'created_at', width: 150 },
];

const serverOptions = ref<ServerOptions>({
    page: props.ratings.current_page,
    rowsPerPage: props.ratings.per_page,
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

            <CardContent class="space-y-4">
                <div class="flex flex-wrap items-center gap-2 pt-6">
                    <div class="relative min-w-48 flex-1">
                        <Search
                            class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search shop, owner, email or comment..."
                            class="pl-8"
                            @keyup.enter="filterTable"
                        />
                    </div>

                    <label class="sr-only" for="platform-stars-filter"
                        >Filter by stars</label
                    >
                    <select
                        id="platform-stars-filter"
                        v-model="stars"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                        @change="filterTable"
                    >
                        <option value="">All ratings</option>
                        <option
                            v-for="n in [5, 4, 3, 2, 1]"
                            :key="n"
                            :value="String(n)"
                        >
                            {{ n }} {{ n === 1 ? 'star' : 'stars' }}
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
                        class="platform-ratings-table"
                        :headers="headers"
                        :items="tableItems"
                        :server-items-length="ratings.total"
                        :loading="tableLoading"
                        :rows-items="[5, 10, 15, 20, 50, 100]"
                        rows-of-page-separator-message="out of"
                        rows-per-page-message="Rows per page:"
                        empty-message="No platform ratings yet. Shop owners are asked after they complete an order."
                        buttons-pagination
                        alternating
                        must-sort
                    >
                        <template #header-shop="{ text }">
                            <SortableHeader
                                :label="text"
                                column="shop"
                                :sort-by="sortBy"
                                :sort-type="sortType"
                                @toggle="toggleSort('shop')"
                            />
                        </template>
                        <template #header-owner="{ text }">
                            <SortableHeader
                                :label="text"
                                column="owner"
                                :sort-by="sortBy"
                                :sort-type="sortType"
                                @toggle="toggleSort('owner')"
                            />
                        </template>
                        <template #header-rating="{ text }">
                            <SortableHeader
                                :label="text"
                                column="rating"
                                :sort-by="sortBy"
                                :sort-type="sortType"
                                @toggle="toggleSort('rating')"
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

                        <template #item-shop="row">
                            <span class="font-medium">{{ row.shop_name }}</span>
                        </template>
                        <template #item-owner="row">
                            <div class="py-1">
                                <p class="text-sm">{{ row.owner_name }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ row.owner_email }}
                                </p>
                            </div>
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
                            <p
                                v-if="row.comment"
                                class="line-clamp-2 py-1 text-sm"
                            >
                                {{ row.comment }}
                            </p>
                            <span v-else class="text-xs text-muted-foreground"
                                >No comment</span
                            >
                        </template>
                        <template #item-created_at="row">
                            <span class="text-xs text-muted-foreground">{{
                                formatDate(row.created_at)
                            }}</span>
                        </template>
                    </EasyDataTable>
                </div>
            </CardContent>
        </div>
    </AdminLayout>
</template>

<style scoped>
.platform-ratings-table {
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
    --easy-table-body-row-height: 60px;
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
    min-width: 820px;
}
</style>
