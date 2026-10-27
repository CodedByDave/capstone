import { router } from '@inertiajs/vue3';
import {
    computed,
    ref,
    toValue,
    watch,
    type MaybeRefOrGetter,
    type Ref,
} from 'vue';
import type { ServerOptions, SortType } from 'vue3-easy-data-table';

/** Paging and sorting state shared by every data table (client- or server-driven). */
export type DataTableOptions = ServerOptions & {
    sortBy?: string;
    sortType?: SortType;
};

export interface StatusOption {
    value: string;
    label: string;
    count?: number;
}

/**
 * Toggle the sort of a column: a new column sorts ascending, the active column flips direction.
 * The page always returns to 1 so the first results of the new order are shown.
 */
export function toggleSortOption(
    options: Ref<DataTableOptions>,
    column: string,
): void {
    const current = options.value;
    const sortType: SortType =
        current.sortBy === column && current.sortType === 'asc'
            ? 'desc'
            : 'asc';

    options.value = { ...current, sortBy: column, sortType, page: 1 };
}

interface UseDataTableConfig<T> {
    /** Text values searched by the toolbar search box. */
    searchFields?: (item: T) => Array<string | number | null | undefined>;
    /** The status used by the status filter chips. */
    status?: (item: T) => string | null | undefined;
    /** Extra reactive filters (date ranges, selects) applied before search. */
    filter?: (item: T) => boolean;
    /** The comparable value of a column; defaults to the item's property. */
    sortValue?: (item: T, column: string) => unknown;
    rowsPerPage?: number;
    sortBy?: string;
    sortType?: SortType;
}

function compareValues(a: unknown, b: unknown): number {
    if (a === b) return 0;
    if (a === null || a === undefined || a === '') return 1;
    if (b === null || b === undefined || b === '') return -1;
    if (typeof a === 'number' && typeof b === 'number') return a - b;

    return String(a).localeCompare(String(b), undefined, {
        numeric: true,
        sensitivity: 'base',
    });
}

/**
 * Client-side search, status filter, sort, and paging for small lists that are fully loaded.
 * Backend-paginated tables keep their own server flow and use only `toggleSortOption`.
 */
export function useDataTable<T>(
    source: MaybeRefOrGetter<T[]>,
    config: UseDataTableConfig<T> = {},
) {
    const defaultSortBy = config.sortBy;
    const defaultSortType: SortType = config.sortType ?? 'asc';
    const defaultRows = config.rowsPerPage ?? 10;

    const search = ref('');
    const status = ref('');
    const options = ref<DataTableOptions>({
        page: 1,
        rowsPerPage: defaultRows,
        sortBy: defaultSortBy,
        sortType: defaultSortType,
    });

    const searched = computed(() => {
        const term = search.value.trim().toLowerCase();

        return toValue(source).filter((item) => {
            if (config.filter && !config.filter(item)) return false;
            if (!term || !config.searchFields) return true;

            return config.searchFields(item).some((value) =>
                String(value ?? '')
                    .toLowerCase()
                    .includes(term),
            );
        });
    });

    const statusCounts = computed(() => {
        const counts: Record<string, number> = {};
        if (!config.status) return counts;

        searched.value.forEach((item) => {
            const key = config.status?.(item) ?? '';
            counts[key] = (counts[key] ?? 0) + 1;
        });

        return counts;
    });

    const filtered = computed(() => {
        if (!status.value || !config.status) return searched.value;

        return searched.value.filter(
            (item) => config.status?.(item) === status.value,
        );
    });

    const sorted = computed(() => {
        const column = options.value.sortBy;
        if (!column) return filtered.value;

        const direction = options.value.sortType === 'desc' ? -1 : 1;
        const valueOf =
            config.sortValue ??
            ((item: T, key: string) => (item as Record<string, unknown>)[key]);

        return [...filtered.value].sort(
            (a, b) =>
                compareValues(valueOf(a, column), valueOf(b, column)) *
                direction,
        );
    });

    const total = computed(() => filtered.value.length);

    const pageItems = computed(() => {
        const { page, rowsPerPage } = options.value;
        const start = (page - 1) * rowsPerPage;

        return sorted.value.slice(start, start + rowsPerPage);
    });

    const isFiltered = computed(
        () =>
            search.value.trim() !== '' ||
            status.value !== '' ||
            options.value.sortBy !== defaultSortBy ||
            options.value.sortType !== defaultSortType,
    );

    watch(
        [
            search,
            status,
            () => options.value.sortBy,
            () => options.value.sortType,
            () => options.value.rowsPerPage,
            searched,
        ],
        () => {
            if (options.value.page !== 1) {
                options.value = { ...options.value, page: 1 };
            }
        },
    );

    watch(total, (count) => {
        const lastPage = Math.max(
            1,
            Math.ceil(count / options.value.rowsPerPage),
        );
        if (options.value.page > lastPage) {
            options.value = { ...options.value, page: lastPage };
        }
    });

    function toggleSort(column: string) {
        toggleSortOption(options, column);
    }

    function reset() {
        search.value = '';
        status.value = '';
        options.value = {
            page: 1,
            rowsPerPage: options.value.rowsPerPage,
            sortBy: defaultSortBy,
            sortType: defaultSortType,
        };
    }

    return {
        search,
        status,
        options,
        statusCounts,
        filtered,
        sorted,
        pageItems,
        total,
        isFiltered,
        toggleSort,
        reset,
    };
}

interface PaginatorLike {
    current_page: number;
    per_page?: number;
    data: unknown[];
}

/**
 * Paging for a backend paginator that only accepts a `page` query parameter. Changing the page
 * reloads the Inertia page with the current query string, so the backend keeps owning paging.
 */
export function usePaginatorTable(
    paginator: () => PaginatorLike,
    only?: string[],
) {
    const loading = ref(false);
    const options = ref<DataTableOptions>({
        page: paginator().current_page,
        rowsPerPage:
            paginator().per_page ?? Math.max(paginator().data.length, 1),
    });

    watch(paginator, (current) => {
        options.value = {
            ...options.value,
            page: current.current_page,
            rowsPerPage: current.per_page ?? options.value.rowsPerPage,
        };
    });

    watch(
        () => options.value.page,
        (page) => {
            if (page === paginator().current_page) return;

            const params = Object.fromEntries(
                new URLSearchParams(window.location.search),
            );
            router.get(
                window.location.pathname,
                { ...params, page },
                {
                    only,
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                    onStart: () => {
                        loading.value = true;
                    },
                    onFinish: () => {
                        loading.value = false;
                    },
                },
            );
        },
    );

    return { options, loading };
}
