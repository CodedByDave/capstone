<script setup lang="ts">
import SortableHeader from '@/components/SortableHeader.vue';
import { Button } from '@/components/ui/button';
import type { DataTableOptions } from '@/composables/useDataTable';
import {
    ChevronLeft,
    ChevronRight,
    RefreshCcw,
    SearchX,
} from 'lucide-vue-next';
import {
    computed,
    nextTick,
    onMounted,
    onUpdated,
    ref,
    useSlots,
    watch,
    type Component,
} from 'vue';
import Vue3EasyDataTable, {
    type Header,
    type Item,
} from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';

export interface DataTableHeader {
    text: string;
    value: string;
    sortable?: boolean;
    width?: number;
    align?: 'left' | 'center' | 'right';
}

/**
 * The shared data table: an EasyDataTable inside a bordered card, styled after the admin Shop
 * Management table. Sorting, filtering, and paging are driven by `options` (backend flow or the
 * `useDataTable` composable); EasyDataTable only renders the current page.
 */
defineOptions({ inheritAttrs: false });

const props = withDefaults(
    defineProps<{
        headers: DataTableHeader[];
        items: Item[];
        total?: number;
        loading?: boolean;
        rowsItems?: number[];
        hideFooter?: boolean;
        hideRowsPerPage?: boolean;
        compact?: boolean;
        minWidth?: number;
        itemKey?: string;
        selectable?: boolean;
        rowLabel?: (item: Item) => string;
        noun?: string;
        emptyMessage?: string;
        emptyIcon?: Component;
        resettable?: boolean;
        rowClass?: (item: Item) => string;
        cardClass?: string;
    }>(),
    {
        total: undefined,
        loading: false,
        rowsItems: () => [5, 10, 15, 20, 50, 100],
        hideFooter: false,
        hideRowsPerPage: false,
        compact: false,
        minWidth: undefined,
        itemKey: 'id',
        selectable: false,
        rowLabel: undefined,
        noun: 'records',
        emptyMessage: undefined,
        emptyIcon: undefined,
        resettable: true,
        rowClass: undefined,
        cardClass: undefined,
    },
);

const options = defineModel<DataTableOptions>('options', {
    default: () => ({ page: 1, rowsPerPage: 10 }),
});
const selected = defineModel<Array<string | number>>('selected', {
    default: () => [],
});

const emit = defineEmits<{ reset: [] }>();

const EasyDataTable = Vue3EasyDataTable;
const slots = useSlots();
const root = ref<HTMLElement | null>(null);

const SELECT_COLUMN = '__select';

const displayHeaders = computed<DataTableHeader[]>(() =>
    props.selectable
        ? [{ text: '', value: SELECT_COLUMN, width: 48 }, ...props.headers]
        : props.headers,
);

// EasyDataTable must not sort on its own: sorting runs through `options`.
const easyHeaders = computed<Header[]>(() =>
    displayHeaders.value.map(({ text, value, width }) =>
        width ? { text, value, width } : { text, value },
    ),
);

const alignByColumn = computed(() => {
    const map: Record<string, string> = {};
    displayHeaders.value.forEach((header) => {
        if (header.align && header.align !== 'left') {
            map[header.value] = `dt-align-${header.align}`;
        }
    });

    return map;
});

const totalCount = computed(() => props.total ?? props.items.length);

const serverOptions = computed(() =>
    props.hideFooter
        ? {
              ...options.value,
              page: 1,
              rowsPerPage: Math.max(props.items.length, 1),
          }
        : options.value,
);

const lastPage = computed(() =>
    Math.max(1, Math.ceil(totalCount.value / options.value.rowsPerPage)),
);
const rangeStart = computed(() =>
    totalCount.value === 0
        ? 0
        : (options.value.page - 1) * options.value.rowsPerPage + 1,
);
const rangeEnd = computed(() =>
    Math.min(options.value.page * options.value.rowsPerPage, totalCount.value),
);

const rowsPerPageChoices = computed(() =>
    [...new Set([...props.rowsItems, options.value.rowsPerPage])].sort(
        (a, b) => a - b,
    ),
);

const pageNumbers = computed<Array<number | 'gap'>>(() => {
    const last = lastPage.value;
    const current = options.value.page;
    if (last <= 7) return Array.from({ length: last }, (_, i) => i + 1);

    const pages = new Set([1, last, current - 1, current, current + 1]);
    const ordered = [...pages]
        .filter((page) => page >= 1 && page <= last)
        .sort((a, b) => a - b);
    const result: Array<number | 'gap'> = [];
    ordered.forEach((page, index) => {
        if (index > 0 && page - ordered[index - 1] > 1) result.push('gap');
        result.push(page);
    });

    return result;
});

function setOptions(next: Partial<DataTableOptions>) {
    options.value = { ...options.value, ...next };
}

function goToPage(page: number) {
    if (page < 1 || page > lastPage.value || page === options.value.page)
        return;
    setOptions({ page });
}

function setRowsPerPage(event: Event) {
    const rowsPerPage = Number((event.target as HTMLSelectElement).value);
    setOptions({ rowsPerPage, page: 1 });
}

function toggleSort(column: string) {
    const sortType =
        options.value.sortBy === column && options.value.sortType === 'asc'
            ? 'desc'
            : 'asc';
    setOptions({ sortBy: column, sortType, page: 1 });
}

function keyOf(item: Item): string | number {
    return item[props.itemKey] as string | number;
}

const pageKeys = computed(() => props.items.map(keyOf));
const allOnPageSelected = computed(
    () =>
        pageKeys.value.length > 0 &&
        pageKeys.value.every((key) => selected.value.includes(key)),
);
const someOnPageSelected = computed(
    () =>
        !allOnPageSelected.value &&
        pageKeys.value.some((key) => selected.value.includes(key)),
);

function toggleAllOnPage() {
    if (allOnPageSelected.value) {
        selected.value = selected.value.filter(
            (key) => !pageKeys.value.includes(key),
        );
        return;
    }
    selected.value = [...new Set([...selected.value, ...pageKeys.value])];
}

function toggleRow(item: Item) {
    const key = keyOf(item);
    selected.value = selected.value.includes(key)
        ? selected.value.filter((selectedKey) => selectedKey !== key)
        : [...selected.value, key];
}

function rowName(item: Item): string {
    return props.rowLabel?.(item) ?? String(keyOf(item));
}

function rowClassName(item: Item): string {
    const classes = [props.rowClass?.(item) ?? ''];
    if (props.selectable && selected.value.includes(keyOf(item))) {
        classes.push('dt-row-selected');
    }

    return classes.join(' ').trim();
}

function headerClassName(header: Header): string {
    return alignByColumn.value[header.value] ?? '';
}

function bodyItemClassName(column: string): string {
    return alignByColumn.value[column] ?? '';
}

const reservedSlots = ['toolbar', 'bulk-actions', 'empty', 'footer'];

function passthroughSlots(): string[] {
    const headerSlots = new Set(
        displayHeaders.value.map((header) => `header-${header.value}`),
    );

    return Object.keys(slots).filter(
        (name) =>
            !reservedSlots.includes(name) &&
            !headerSlots.has(name) &&
            name !== `item-${SELECT_COLUMN}`,
    );
}

function syncAriaSort() {
    const cells = root.value?.querySelectorAll('thead th');
    if (!cells) return;

    displayHeaders.value.forEach((header, index) => {
        const cell = cells[index];
        if (!cell) return;
        if (!header.sortable) {
            cell.removeAttribute('aria-sort');
            return;
        }
        const active = options.value.sortBy === header.value;
        cell.setAttribute(
            'aria-sort',
            active
                ? options.value.sortType === 'desc'
                    ? 'descending'
                    : 'ascending'
                : 'none',
        );
    });
}

watch(
    [
        () => options.value.sortBy,
        () => options.value.sortType,
        displayHeaders,
        () => props.items,
    ],
    () => nextTick(syncAriaSort),
);
onMounted(syncAriaSort);
onUpdated(syncAriaSort);
</script>

<template>
    <div
        ref="root"
        class="overflow-hidden rounded-[14px] border bg-card text-card-foreground"
        :class="cardClass"
    >
        <div v-if="$slots.toolbar" class="border-b p-4 sm:px-5">
            <slot name="toolbar" />
        </div>

        <div
            v-if="selectable && selected.length > 0"
            role="region"
            aria-label="Bulk actions"
            class="flex flex-wrap items-center gap-2 border-b bg-primary/5 px-4 py-2 text-sm"
        >
            <span class="font-medium">{{ selected.length }} selected</span>
            <slot name="bulk-actions" :selected="selected" />
            <Button
                variant="ghost"
                size="sm"
                class="ml-auto"
                @click="selected = []"
            >
                Clear
            </Button>
        </div>

        <EasyDataTable
            v-bind="$attrs"
            class="app-data-table"
            :class="{ 'app-data-table--compact': compact }"
            :style="{
                '--dt-min-width': minWidth ? `${minWidth}px` : '100%',
            }"
            :headers="easyHeaders"
            :items="items"
            :server-items-length="hideFooter ? items.length : totalCount"
            :server-options="serverOptions"
            :loading="loading"
            :rows-items="rowsPerPageChoices"
            :body-row-class-name="rowClassName"
            :header-item-class-name="headerClassName"
            :body-item-class-name="bodyItemClassName"
            hide-footer
        >
            <template
                v-for="header in displayHeaders"
                #[`header-${header.value}`]="headerProps"
            >
                <input
                    v-if="header.value === SELECT_COLUMN"
                    :key="`select-${header.value}`"
                    type="checkbox"
                    class="dt-checkbox"
                    :aria-label="`Select all ${noun} on this page`"
                    :checked="allOnPageSelected"
                    :indeterminate="someOnPageSelected"
                    @change="toggleAllOnPage"
                />
                <slot
                    v-else
                    :key="`header-${header.value}`"
                    :name="`header-${header.value}`"
                    v-bind="headerProps"
                    :sort-by="options.sortBy"
                    :sort-type="options.sortType"
                    :toggle-sort="() => toggleSort(header.value)"
                >
                    <SortableHeader
                        v-if="header.sortable"
                        :label="header.text"
                        :column="header.value"
                        :sort-by="options.sortBy"
                        :sort-type="options.sortType"
                        @toggle="toggleSort(header.value)"
                    />
                    <span v-else>{{ header.text }}</span>
                </slot>
            </template>

            <template v-if="selectable" #[`item-${SELECT_COLUMN}`]="item">
                <input
                    type="checkbox"
                    class="dt-checkbox"
                    :aria-label="`Select ${rowName(item)}`"
                    :checked="selected.includes(keyOf(item))"
                    @change="toggleRow(item)"
                />
            </template>

            <template v-for="name in passthroughSlots()" #[name]="slotProps">
                <slot :name="name" v-bind="slotProps ?? {}" />
            </template>

            <template #empty-message>
                <slot name="empty">
                    <div
                        class="flex flex-col items-center gap-2 py-10 text-center text-sm text-muted-foreground"
                    >
                        <component
                            :is="emptyIcon ?? SearchX"
                            class="h-8 w-8 opacity-60"
                            aria-hidden="true"
                        />
                        <p>
                            {{
                                emptyMessage ??
                                `No ${noun} match the current filters.`
                            }}
                        </p>
                        <Button
                            v-if="resettable"
                            variant="outline"
                            size="sm"
                            class="mt-1"
                            @click="emit('reset')"
                        >
                            <RefreshCcw class="mr-1.5 h-4 w-4" />
                            Reset filters
                        </Button>
                    </div>
                </slot>
            </template>
        </EasyDataTable>

        <div
            v-if="!hideFooter && totalCount > 0"
            class="flex flex-wrap items-center justify-between gap-3 border-t px-5 py-4 text-sm text-muted-foreground"
        >
            <p aria-live="polite">
                Showing {{ rangeStart }}–{{ rangeEnd }} of {{ totalCount }}
            </p>
            <div class="flex flex-wrap items-center gap-3">
                <slot name="footer" />
                <label v-if="!hideRowsPerPage" class="flex items-center gap-2">
                    <span>Rows per page</span>
                    <select
                        class="dt-select"
                        :value="options.rowsPerPage"
                        @change="setRowsPerPage"
                    >
                        <option
                            v-for="choice in rowsPerPageChoices"
                            :key="choice"
                            :value="choice"
                        >
                            {{ choice }}
                        </option>
                    </select>
                </label>
                <nav aria-label="Pagination" class="flex items-center gap-1">
                    <button
                        type="button"
                        class="dt-page-button"
                        aria-label="Previous page"
                        :disabled="options.page <= 1"
                        @click="goToPage(options.page - 1)"
                    >
                        <ChevronLeft class="h-4 w-4" aria-hidden="true" />
                    </button>
                    <template
                        v-for="(page, index) in pageNumbers"
                        :key="`${page}-${index}`"
                    >
                        <span
                            v-if="page === 'gap'"
                            class="px-1"
                            aria-hidden="true"
                            >…</span
                        >
                        <button
                            v-else
                            type="button"
                            class="dt-page-button"
                            :class="{
                                'dt-page-button--active': page === options.page,
                            }"
                            :aria-label="`Page ${page}`"
                            :aria-current="
                                page === options.page ? 'page' : undefined
                            "
                            @click="goToPage(page)"
                        >
                            {{ page }}
                        </button>
                    </template>
                    <button
                        type="button"
                        class="dt-page-button"
                        aria-label="Next page"
                        :disabled="options.page >= lastPage"
                        @click="goToPage(options.page + 1)"
                    >
                        <ChevronRight class="h-4 w-4" aria-hidden="true" />
                    </button>
                </nav>
            </div>
        </div>
    </div>
</template>

<style scoped>
.app-data-table {
    --easy-table-border: 0;
    --easy-table-row-border: 1px solid var(--border);
    --easy-table-header-background-color: color-mix(
        in srgb,
        var(--muted) 45%,
        var(--card)
    );
    --easy-table-header-font-color: var(--muted-foreground);
    --easy-table-header-font-size: 11px;
    --easy-table-header-height: 48px;
    --easy-table-header-item-padding: 8px 16px;
    --easy-table-body-row-background-color: var(--card);
    --easy-table-body-even-row-background-color: color-mix(
        in srgb,
        var(--muted) 35%,
        var(--card)
    );
    --easy-table-body-row-font-color: var(--foreground);
    --easy-table-body-even-row-font-color: var(--foreground);
    --easy-table-body-row-hover-background-color: color-mix(
        in srgb,
        var(--muted) 40%,
        var(--card)
    );
    --easy-table-body-row-hover-font-color: var(--foreground);
    --easy-table-body-row-height: 64px;
    --easy-table-body-row-font-size: 14px;
    --easy-table-body-item-padding: 12px 16px;
    --easy-table-message-font-color: var(--muted-foreground);
    --easy-table-loading-mask-background-color: var(--card);
    width: 100%;
}

.app-data-table--compact {
    --easy-table-body-row-height: 48px;
    --easy-table-body-item-padding: 8px 16px;
}

.app-data-table :deep(.vue3-easy-data-table__main) {
    background: var(--card);
}

.app-data-table :deep(.vue3-easy-data-table__main table) {
    min-width: var(--dt-min-width);
}

.app-data-table :deep(thead th) {
    font-weight: 600;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    white-space: nowrap;
}

.app-data-table :deep(.dt-align-right),
.app-data-table :deep(thead th.dt-align-right .header) {
    justify-content: flex-end;
    text-align: right;
}

.app-data-table :deep(.dt-align-center),
.app-data-table :deep(thead th.dt-align-center .header) {
    justify-content: center;
    text-align: center;
}

.app-data-table :deep(tr.dt-row-selected td) {
    background-color: color-mix(in srgb, var(--primary) 8%, var(--card));
}

.dt-checkbox {
    height: 16px;
    width: 16px;
    cursor: pointer;
    border-radius: 4px;
    accent-color: var(--primary);
}

.dt-checkbox:focus-visible,
.dt-select:focus-visible,
.dt-page-button:focus-visible {
    outline: 2px solid color-mix(in srgb, var(--ring) 60%, transparent);
    outline-offset: 2px;
}

.dt-select {
    height: 32px;
    border: 1px solid var(--border);
    border-radius: 6px;
    background: var(--background);
    padding: 0 6px;
    color: var(--foreground);
}

.dt-page-button {
    display: inline-flex;
    height: 40px;
    min-width: 40px;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--background);
    padding: 0 8px;
    color: var(--foreground);
    font-variant-numeric: tabular-nums;
}

.dt-page-button:hover:not(:disabled) {
    background: var(--muted);
}

.dt-page-button:disabled {
    cursor: not-allowed;
    opacity: 0.45;
}

.dt-page-button--active {
    border-color: var(--primary);
    background: var(--primary);
    color: var(--primary-foreground);
}

.dt-page-button--active:hover:not(:disabled) {
    background: var(--primary);
}

@media (max-width: 640px) {
    .dt-page-button {
        min-width: 44px;
        height: 44px;
    }
}
</style>
