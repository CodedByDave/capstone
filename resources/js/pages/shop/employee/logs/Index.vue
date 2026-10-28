<script setup lang="ts">
import {
    DataTable,
    DataTableToolbar,
    IdentityCell,
    StatusPill,
    type DataTableHeader,
    type StatusTone,
} from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type {
    DataTableOptions,
    StatusOption,
} from '@/composables/useDataTable';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    Archive,
    ArrowLeft,
    CircleDot,
    Clock,
    DollarSign,
    FileUp,
    Pencil,
    RefreshCw,
    Search,
    UserPlus,
    X,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch, type Component } from 'vue';

/* ───────── TYPES ───────── */

interface Performer {
    id: number;
    name: string;
}

interface Subject {
    id: number;
    public_id?: string;
    first_name?: string;
    last_name?: string;
    employee_id?: string;
    name?: string;
    sku?: string;
}

interface ActivityLog {
    id: number;
    module: string;
    action: string;
    subject_type: string;
    subject_id: number;
    subject: Subject | null;
    changes: Record<string, { old: string; new: string }> | null;
    performed_by: number | null;
    performer: Performer | null;
    created_at: string;
}

interface PaginatedLogs {
    data: ActivityLog[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

/* ───────── PROPS ───────── */

const page = usePage();
const isOwner = computed(() => page.props.auth.user.role === 'owner');
const baseRoute = computed(() => (isOwner.value ? '/shop' : '/staff'));

const { logs, performers, modules, filters, actionCounts } = defineProps<{
    logs: PaginatedLogs;
    actionCounts: {
        all: number;
        created: number;
        updated: number;
        archived: number;
    };
    performers: Performer[];
    modules: string[];
    filters: {
        action?: string;
        performed_by?: string;
        module?: string;
        date_from?: string;
        date_to?: string;
        search?: string;
    };
}>();

/* ───────── BREADCRUMBS ───────── */

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Employee Management', href: `${baseRoute.value}/employee` },
    { title: 'Activity Logs', href: `${baseRoute.value}/logs` },
];

/* ───────── FILTER STATE ───────── */

const search = ref(filters.search ?? '');
const action = ref(filters.action ?? '__all__');
const performedBy = ref(filters.performed_by ?? '__all__');
const moduleFilter = ref(filters.module ?? '__all__');
const dateFrom = ref(filters.date_from ?? '');
const dateTo = ref(filters.date_to ?? '');

/* ───────── FILTER FUNCTIONS ───────── */

const tableLoading = ref(false);
const options = ref<DataTableOptions>({
    page: logs.current_page,
    rowsPerPage: logs.per_page,
});

const statusOptions = computed<StatusOption[]>(() => [
    { value: '__all__', label: 'All', count: actionCounts.all },
    { value: 'created', label: 'Created', count: actionCounts.created },
    { value: 'updated', label: 'Updated', count: actionCounts.updated },
    { value: 'archived', label: 'Archived', count: actionCounts.archived },
]);

let filterTimer: ReturnType<typeof setTimeout> | undefined;

// Filters apply as they change; typing in the search box waits for a short pause.
watch([search, action, performedBy, moduleFilter, dateFrom, dateTo], () => {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(() => applyFilters(), 300);
});

onBeforeUnmount(() => clearTimeout(filterTimer));

const hasFilters = computed(
    () =>
        Boolean(filters.search) ||
        Boolean(filters.action) ||
        Boolean(filters.performed_by) ||
        Boolean(filters.module) ||
        Boolean(filters.date_from) ||
        Boolean(filters.date_to),
);

const headers: DataTableHeader[] = [
    { text: 'Date', value: 'created_at', width: 170 },
    { text: 'Module', value: 'module' },
    { text: 'Subject', value: 'subject' },
    { text: 'Action', value: 'action' },
    { text: 'Performed by', value: 'performer' },
    { text: 'Changes', value: 'changes', width: 320 },
];

watch([() => options.value.page, () => options.value.rowsPerPage], ([page]) =>
    applyFilters(page),
);

function applyFilters(currentPage = 1) {
    router.get(
        `${baseRoute.value}/logs`,
        {
            search: search.value || undefined,
            action: action.value === '__all__' ? undefined : action.value,
            performed_by:
                performedBy.value === '__all__' ? undefined : performedBy.value,
            module:
                moduleFilter.value === '__all__'
                    ? undefined
                    : moduleFilter.value,
            date_from: dateFrom.value || undefined,
            date_to: dateTo.value || undefined,
            per_page: options.value.rowsPerPage,
            page: currentPage,
        },
        {
            preserveScroll: true,
            preserveState: false,
            onStart: () => {
                tableLoading.value = true;
            },
            onFinish: () => {
                tableLoading.value = false;
            },
        },
    );
}

function clearFilters() {
    search.value = '';
    action.value = '__all__';
    performedBy.value = '__all__';
    moduleFilter.value = '__all__';
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
}

/* ───────── SUBJECT HELPERS ───────── */

function subjectLabel(log: ActivityLog): string {
    if (!log.subject) return 'Deleted Record';
    const s = log.subject;
    if (s.first_name) return `${s.first_name} ${s.last_name ?? ''}`.trim();
    if (s.name) return s.name;
    return `#${log.subject_id}`;
}

function subjectMeta(log: ActivityLog): string | null {
    if (!log.subject) return null;
    return log.subject.employee_id ?? log.subject.sku ?? null;
}

const moduleRoutes = computed<Record<string, string>>(() => ({
    Employee: `${baseRoute.value}/employee`,
    Product: `${baseRoute.value}/product`,
    Attendance: `${baseRoute.value}/attendance`,
}));

function subjectUrl(log: ActivityLog): string | null {
    if (!log.subject) return null;
    const base = moduleRoutes.value[log.module];
    return base ? `${base}/${log.subject.public_id ?? log.subject.id}` : null;
}

/* ───────── ACTION BADGES ───────── */

const actionConfig: Record<
    string,
    { label: string; tone: StatusTone; icon: Component }
> = {
    created: { label: 'Created', tone: 'success', icon: UserPlus },
    updated: { label: 'Updated', tone: 'info', icon: Pencil },
    status_changed: {
        label: 'Status Changed',
        tone: 'warning',
        icon: CircleDot,
    },
    salary_changed: {
        label: 'Pay Rate Changed',
        tone: 'info',
        icon: DollarSign,
    },
    archived: { label: 'Archived', tone: 'danger', icon: Archive },
    restored: { label: 'Restored', tone: 'success', icon: RefreshCw },
    imported: { label: 'Imported', tone: 'warning', icon: FileUp },
    clocked_in: { label: 'Clocked In', tone: 'success', icon: Clock },
    clocked_out: { label: 'Clocked Out', tone: 'neutral', icon: Clock },
    marked_present: {
        label: 'Marked Present',
        tone: 'success',
        icon: UserPlus,
    },
    marked_absent: { label: 'Marked Absent', tone: 'danger', icon: X },
};

function getConfig(a: string) {
    return (
        actionConfig[a] ?? {
            label: a,
            tone: 'neutral' as StatusTone,
            icon: Clock,
        }
    );
}

/* ───────── HELPERS ───────── */

function formatFieldName(field: string) {
    return field.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

const formatDay = (val: string) =>
    new Date(val).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });

const formatTime = (val: string) =>
    new Date(val).toLocaleTimeString('en-PH', {
        hour: 'numeric',
        minute: '2-digit',
    });

/* ───────── EXPANDED ROWS ───────── */

const expandedRows = ref<Set<number>>(new Set());

function toggleRow(id: number) {
    if (expandedRows.value.has(id)) {
        expandedRows.value.delete(id);
    } else {
        expandedRows.value.add(id);
    }
}
</script>

<template>
    <Head title="Activity Logs" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Activity Logs">
        <div class="mx-auto max-w-7xl space-y-6 px-6 pb-10">
            <!-- HEADER -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <nav
                        aria-label="Breadcrumb"
                        class="mb-2 flex flex-wrap items-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <span>Human Resource</span>
                        <span aria-hidden="true">/</span>
                        <span class="font-medium text-foreground"
                            >Activity logs</span
                        >
                    </nav>
                    <h2 class="text-2xl font-bold tracking-tight">
                        Activity logs
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Track every change across all system modules.
                    </p>
                </div>
                <Button variant="outline" class="shrink-0" as-child>
                    <Link :href="`${baseRoute}/employee`">
                        <ArrowLeft class="mr-2 h-4 w-4" /> Back to employees
                    </Link>
                </Button>
            </div>

            <!-- FILTERS -->
            <div
                class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,1.6fr)_repeat(4,minmax(0,1fr))]"
            >
                <div class="space-y-1.5">
                    <label
                        for="log-search"
                        class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                        >Search</label
                    >
                    <div class="relative">
                        <Search
                            class="pointer-events-none absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                            aria-hidden="true"
                        />
                        <Input
                            id="log-search"
                            v-model="search"
                            placeholder="Subject name..."
                            class="pl-8"
                        />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label
                        class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                        >Module</label
                    >
                    <Select
                        :model-value="moduleFilter"
                        @update:model-value="moduleFilter = $event as string"
                    >
                        <SelectTrigger class="w-full"
                            ><SelectValue placeholder="All modules"
                        /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="__all__">All modules</SelectItem>
                            <SelectItem
                                v-for="m in modules"
                                :key="m"
                                :value="m"
                                >{{ m }}</SelectItem
                            >
                        </SelectContent>
                    </Select>
                </div>

                <div class="space-y-1.5">
                    <label
                        class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                        >Performed by</label
                    >
                    <Select
                        :model-value="performedBy"
                        @update:model-value="performedBy = $event as string"
                    >
                        <SelectTrigger class="w-full"
                            ><SelectValue placeholder="All performers"
                        /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="__all__"
                                >All performers</SelectItem
                            >
                            <SelectItem
                                v-for="p in performers"
                                :key="p.id"
                                :value="String(p.id)"
                            >
                                {{ p.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="space-y-1.5">
                    <label
                        for="log-from"
                        class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                        >From</label
                    >
                    <Input id="log-from" v-model="dateFrom" type="date" />
                </div>

                <div class="space-y-1.5">
                    <label
                        for="log-to"
                        class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                        >To</label
                    >
                    <Input id="log-to" v-model="dateTo" type="date" />
                </div>
            </div>

            <!-- ACTION CHIPS -->
            <DataTableToolbar
                :status="action"
                :statuses="statusOptions"
                status-label="Filter by action"
                hide-search
                @update:status="action = $event"
            />

            <!-- TABLE -->
            <DataTable
                v-model:options="options"
                :headers="headers"
                :items="logs.data"
                :total="logs.total"
                :loading="tableLoading"
                noun="activity logs"
                :empty-icon="Clock"
                empty-message="No activity logs found."
                :resettable="hasFilters"
                :min-width="980"
                :rows-items="[10, 25, 50, 100]"
                @reset="clearFilters"
            >
                <template #toolbar>
                    <h2 class="text-base font-semibold">
                        All activity
                        <span
                            class="ml-1 text-sm font-normal text-muted-foreground"
                        >
                            {{ logs.total }} records
                        </span>
                    </h2>
                </template>

                <!-- Date -->
                <template #item-created_at="log">
                    <div class="whitespace-nowrap">
                        <p class="text-sm font-medium">
                            {{ formatDay(log.created_at) }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ formatTime(log.created_at) }}
                        </p>
                    </div>
                </template>

                <!-- Module badge -->
                <template #item-module="log">
                    <span
                        class="rounded bg-muted px-2 py-1 text-xs font-semibold"
                    >
                        {{ log.module }}
                    </span>
                </template>

                <!-- Subject -->
                <template #item-subject="log">
                    <template v-if="log.subject">
                        <button
                            v-if="subjectUrl(log)"
                            type="button"
                            class="text-left text-sm font-semibold hover:underline"
                            @click="router.visit(subjectUrl(log)!)"
                        >
                            {{ subjectLabel(log) }}
                        </button>
                        <span v-else class="text-sm font-semibold">
                            {{ subjectLabel(log) }}
                        </span>
                        <p
                            v-if="subjectMeta(log)"
                            class="font-mono text-xs text-muted-foreground"
                        >
                            {{ subjectMeta(log) }}
                        </p>
                    </template>
                    <span v-else class="text-xs text-muted-foreground italic">
                        Deleted record
                    </span>
                </template>

                <!-- Action badge -->
                <template #item-action="log">
                    <StatusPill
                        :tone="getConfig(log.action).tone"
                        :icon="getConfig(log.action).icon"
                    >
                        {{ getConfig(log.action).label }}
                    </StatusPill>
                </template>

                <!-- Performer -->
                <template #item-performer="log">
                    <IdentityCell
                        v-if="log.performer"
                        :name="log.performer.name"
                    />
                    <span v-else class="text-xs text-muted-foreground">—</span>
                </template>

                <!-- Changes toggle + details -->
                <template #item-changes="log">
                    <div
                        v-if="log.changes && Object.keys(log.changes).length"
                        class="space-y-2 py-1"
                    >
                        <Button
                            size="sm"
                            variant="ghost"
                            class="h-8 text-xs"
                            :aria-expanded="expandedRows.has(log.id)"
                            @click="toggleRow(log.id)"
                        >
                            {{ expandedRows.has(log.id) ? 'Hide' : 'View' }}
                            changes
                        </Button>
                        <dl
                            v-if="expandedRows.has(log.id)"
                            class="grid grid-cols-[auto_1fr_1fr] gap-x-3 gap-y-1 rounded-md border bg-muted/20 p-2 text-xs"
                        >
                            <template
                                v-for="(change, field) in log.changes"
                                :key="field"
                            >
                                <dt class="font-medium">
                                    {{ formatFieldName(String(field)) }}
                                </dt>
                                <dd class="text-red-600 line-through">
                                    {{ change.old ?? '—' }}
                                </dd>
                                <dd class="font-medium text-green-600">
                                    {{ change.new ?? '—' }}
                                </dd>
                            </template>
                        </dl>
                    </div>
                    <span v-else class="text-xs text-muted-foreground">—</span>
                </template>
            </DataTable>
        </div>
    </ShopLayout>
</template>
