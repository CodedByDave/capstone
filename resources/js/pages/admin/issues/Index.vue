<script setup lang="ts">
import SortableTableHeader from '@/components/management/SortableTableHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Download, Eye, RefreshCcw, Search, Upload, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import Vue3EasyDataTable, {
    type Header,
    type ServerOptions,
} from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

interface IssueReport {
    id: number;
    public_id: string;
    subject: string;
    description: string;
    category: string;
    priority: string;
    status: string;
    page_url: string | null;
    browser: string | null;
    admin_notes: string | null;
    resolved_at: string | null;
    created_at: string;
    reporter: { id: number; name: string; email: string; role: string } | null;
}

interface Paginator {
    data: IssueReport[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

type SortColumn =
    | 'reporter'
    | 'subject'
    | 'category'
    | 'priority'
    | 'status'
    | 'created_at';

const EasyDataTable = Vue3EasyDataTable;
const props = defineProps<{
    reports: Paginator;
    stats: {
        total: number;
        open: number;
        in_progress: number;
        resolved: number;
        critical: number;
    };
    filters: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Issue Reports', href: '/admin/issue-reports' },
];
const headers: Header[] = [
    { text: 'Reported by', value: 'reporter', width: 215 },
    { text: 'Issue', value: 'subject', width: 320 },
    { text: 'Category', value: 'category', width: 165 },
    { text: 'Priority', value: 'priority', width: 140 },
    { text: 'Status', value: 'status', width: 150 },
    { text: 'Reported', value: 'created_at', width: 180 },
    { text: 'Action', value: 'actions', width: 90 },
];
const statCards = [
    { label: 'Total reports', value: props.stats.total },
    { label: 'Open', value: props.stats.open },
    { label: 'In progress', value: props.stats.in_progress },
    { label: 'Resolved', value: props.stats.resolved },
    { label: 'Critical', value: props.stats.critical },
] as const;

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const priority = ref(props.filters.priority ?? '');
const category = ref(props.filters.category ?? '');
const tableLoading = ref(false);
const importing = ref(false);
const importInput = ref<HTMLInputElement | null>(null);
const selectedReport = ref<IssueReport | null>(null);
const editStatus = ref('open');
const editPriority = ref('medium');
const adminNotes = ref('');
const saving = ref(false);
const serverOptions = ref<ServerOptions>({
    page: props.reports.current_page,
    rowsPerPage: props.reports.per_page,
    sortBy: props.filters.sort_by ?? 'created_at',
    sortType: props.filters.sort_direction === 'asc' ? 'asc' : 'desc',
});

function query() {
    return {
        search: search.value || undefined,
        status: status.value || undefined,
        priority: priority.value || undefined,
        category: category.value || undefined,
        sort_by: serverOptions.value.sortBy,
        sort_direction: serverOptions.value.sortType,
        page: serverOptions.value.page,
        per_page: serverOptions.value.rowsPerPage,
    };
}

function applyFilters() {
    router.get('/admin/issue-reports', query(), {
        only: ['reports', 'filters'],
        preserveState: true,
        preserveScroll: true,
        replace: true,
        showProgress: false,
        onStart: () => (tableLoading.value = true),
        onFinish: () => (tableLoading.value = false),
    });
}
function filterTable() {
    if (serverOptions.value.page !== 1) serverOptions.value.page = 1;
    else applyFilters();
}
function resetFilters() {
    search.value = '';
    status.value = '';
    priority.value = '';
    category.value = '';
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
    applyFilters,
);
function toggleSort(column: SortColumn) {
    if (serverOptions.value.sortBy === column) {
        serverOptions.value.sortType =
            serverOptions.value.sortType === 'asc' ? 'desc' : 'asc';
    } else {
        serverOptions.value.sortBy = column;
        serverOptions.value.sortType = 'asc';
    }
}
function isSorted(column: SortColumn) {
    return serverOptions.value.sortBy === column;
}
function sortDirection(): 'asc' | 'desc' {
    return serverOptions.value.sortType === 'asc' ? 'asc' : 'desc';
}

function exportCsv() {
    const params = new URLSearchParams();
    Object.entries(query()).forEach(([key, value]) => {
        if (value !== undefined && value !== null)
            params.set(key, String(value));
    });
    window.location.assign(`/admin/issue-reports/export?${params.toString()}`);
}
function importCsv(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;
    router.post(
        '/admin/issue-reports/import',
        { file },
        {
            forceFormData: true,
            preserveScroll: true,
            onStart: () => (importing.value = true),
            onSuccess: () => toast.success('Issue report backup restored.'),
            onError: (errors) =>
                toast.error(
                    String(errors.file || 'The CSV could not be imported.'),
                ),
            onFinish: () => {
                importing.value = false;
                input.value = '';
            },
        },
    );
}
function openReport(report: IssueReport) {
    selectedReport.value = report;
    editStatus.value = report.status;
    editPriority.value = report.priority;
    adminNotes.value = report.admin_notes ?? '';
}
function updateReport() {
    if (!selectedReport.value) return;
    router.patch(
        `/admin/issue-reports/${selectedReport.value.public_id}`,
        {
            status: editStatus.value,
            priority: editPriority.value,
            admin_notes: adminNotes.value || null,
        },
        {
            preserveScroll: true,
            onStart: () => (saving.value = true),
            onSuccess: () => {
                selectedReport.value = null;
                toast.success('Issue report updated.');
            },
            onError: () =>
                toast.error('The issue report could not be updated.'),
            onFinish: () => (saving.value = false),
        },
    );
}
const label = (value: string) =>
    value.replaceAll('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase());
const formatDate = (value: string | null) =>
    value
        ? new Intl.DateTimeFormat('en-PH', {
              month: 'short',
              day: 'numeric',
              year: 'numeric',
              hour: 'numeric',
              minute: '2-digit',
          }).format(new Date(value))
        : '—';
const statusClass = (value: string) =>
    ({
        open: 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300',
        in_progress:
            'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300',
        resolved:
            'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300',
        closed: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    })[value];
const priorityClass = (value: string) =>
    ({
        low: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        medium: 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300',
        high: 'bg-orange-100 text-orange-700 dark:bg-orange-950/50 dark:text-orange-300',
        critical:
            'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300',
    })[value];
const categoryClass = (value: string) =>
    ({
        bug: 'bg-red-50 text-red-700 dark:bg-red-950/30 dark:text-red-300',
        account:
            'bg-violet-50 text-violet-700 dark:bg-violet-950/30 dark:text-violet-300',
        billing:
            'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300',
        feature_request:
            'bg-cyan-50 text-cyan-700 dark:bg-cyan-950/30 dark:text-cyan-300',
        general:
            'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    })[value];
</script>

<template>
    <Head title="Issue Reports" />
    <AdminLayout :breadcrumbs="breadcrumbs" title="Issue Reports">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="px-1">
                <h2 class="text-2xl font-bold tracking-tight">Issue Reports</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Review user-reported bugs, account concerns, and platform
                    feedback.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
                <Card v-for="item in statCards" :key="item.label">
                    <CardContent class="p-4">
                        <p
                            class="mb-3 text-xs font-medium text-muted-foreground"
                        >
                            {{ item.label }}
                        </p>
                        <p class="text-2xl font-bold">
                            {{ item.value.toLocaleString() }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <section class="space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative min-w-56 flex-1">
                        <Search
                            class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                        />
                        <Input
                            v-model="search"
                            class="pl-8"
                            placeholder="Search issue, reporter, or email..."
                            @keyup.enter="filterTable"
                        />
                    </div>
                    <Button size="sm" variant="outline" @click="resetFilters"
                        ><RefreshCcw class="mr-1.5 h-4 w-4" />Reset</Button
                    >
                    <Button
                        size="sm"
                        variant="outline"
                        class="border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300"
                        @click="exportCsv"
                    >
                        <Download class="mr-1.5 h-4 w-4" />Export CSV
                    </Button>
                    <Button
                        size="sm"
                        variant="outline"
                        class="border-blue-300 bg-blue-50 text-blue-700 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300"
                        :disabled="importing"
                        @click="importInput?.click()"
                    >
                        <Upload class="mr-1.5 h-4 w-4" />{{
                            importing ? 'Importing...' : 'Import CSV'
                        }}
                    </Button>
                    <input
                        ref="importInput"
                        type="file"
                        accept=".csv,text/csv"
                        class="hidden"
                        @change="importCsv"
                    />
                </div>

                <div class="overflow-hidden rounded-lg border">
                    <EasyDataTable
                        v-model:server-options="serverOptions"
                        class="issue-data-table"
                        :headers="headers"
                        :items="reports.data"
                        :server-items-length="reports.total"
                        :loading="tableLoading"
                        :rows-items="[5, 10, 15, 20, 50, 100]"
                        rows-of-page-separator-message="out of"
                        rows-per-page-message="Rows per page:"
                        buttons-pagination
                        alternating
                        must-sort
                    >
                        <template #header-reporter
                            ><SortableTableHeader
                                label="Reported by"
                                :active="isSorted('reporter')"
                                :direction="sortDirection()"
                                @sort="toggleSort('reporter')"
                        /></template>
                        <template #header-subject
                            ><SortableTableHeader
                                label="Issue"
                                :active="isSorted('subject')"
                                :direction="sortDirection()"
                                @sort="toggleSort('subject')"
                        /></template>
                        <template #header-category>
                            <SortableTableHeader
                                label="Category"
                                :active="isSorted('category')"
                                :direction="sortDirection()"
                                @sort="toggleSort('category')"
                            >
                                <select
                                    v-model="category"
                                    class="column-filter-input"
                                    @click.stop
                                    @change="filterTable"
                                >
                                    <option value="">All categories</option>
                                    <option value="bug">Bug</option>
                                    <option value="account">Account</option>
                                    <option value="billing">Billing</option>
                                    <option value="feature_request">
                                        Feature request
                                    </option>
                                    <option value="general">General</option>
                                </select>
                            </SortableTableHeader>
                        </template>
                        <template #header-priority>
                            <SortableTableHeader
                                label="Priority"
                                :active="isSorted('priority')"
                                :direction="sortDirection()"
                                @sort="toggleSort('priority')"
                            >
                                <select
                                    v-model="priority"
                                    class="column-filter-input"
                                    @click.stop
                                    @change="filterTable"
                                >
                                    <option value="">All priorities</option>
                                    <option value="critical">Critical</option>
                                    <option value="high">High</option>
                                    <option value="medium">Medium</option>
                                    <option value="low">Low</option>
                                </select>
                            </SortableTableHeader>
                        </template>
                        <template #header-status>
                            <SortableTableHeader
                                label="Status"
                                :active="isSorted('status')"
                                :direction="sortDirection()"
                                @sort="toggleSort('status')"
                            >
                                <select
                                    v-model="status"
                                    class="column-filter-input"
                                    @click.stop
                                    @change="filterTable"
                                >
                                    <option value="">All statuses</option>
                                    <option value="open">Open</option>
                                    <option value="in_progress">
                                        In progress
                                    </option>
                                    <option value="resolved">Resolved</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </SortableTableHeader>
                        </template>
                        <template #header-created_at
                            ><SortableTableHeader
                                label="Reported"
                                :active="isSorted('created_at')"
                                :direction="sortDirection()"
                                @sort="toggleSort('created_at')"
                        /></template>

                        <template #item-reporter="report"
                            ><p class="font-medium">
                                {{ report.reporter?.name ?? 'Guest user' }}
                            </p>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                {{
                                    report.reporter?.email ??
                                    'No email available'
                                }}
                            </p></template
                        >
                        <template #item-subject="report"
                            ><div class="max-w-[300px]">
                                <p class="truncate font-medium">
                                    {{ report.subject }}
                                </p>
                                <p
                                    class="mt-1 truncate text-xs text-muted-foreground"
                                >
                                    {{ report.description }}
                                </p>
                            </div></template
                        >
                        <template #item-category="report"
                            ><span
                                :class="[
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                                    categoryClass(report.category),
                                ]"
                                >{{ label(report.category) }}</span
                            ></template
                        >
                        <template #item-priority="report"
                            ><span
                                :class="[
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                    priorityClass(report.priority),
                                ]"
                                >{{ label(report.priority) }}</span
                            ></template
                        >
                        <template #item-status="report"
                            ><span
                                :class="[
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                    statusClass(report.status),
                                ]"
                                >{{ label(report.status) }}</span
                            ></template
                        >
                        <template #item-created_at="report"
                            ><span class="text-xs text-muted-foreground">{{
                                formatDate(report.created_at)
                            }}</span></template
                        >
                        <template #item-actions="report"
                            ><Button
                                size="icon"
                                variant="ghost"
                                aria-label="View issue report"
                                @click="openReport(report)"
                                ><Eye class="h-4 w-4" /></Button
                        ></template>
                        <template #empty-message
                            ><div class="py-10 text-center">
                                <p class="font-medium">
                                    No issue reports found
                                </p>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    New user reports will appear here for
                                    review.
                                </p>
                            </div></template
                        >
                    </EasyDataTable>
                </div>
            </section>
        </div>

        <Teleport to="body">
            <div
                v-if="selectedReport"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/55 p-4"
                @click.self="!saving && (selectedReport = null)"
            >
                <div
                    class="max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-xl border bg-background shadow-2xl"
                >
                    <div
                        class="flex items-start justify-between gap-4 border-b p-6"
                    >
                        <div>
                            <p
                                class="text-xs font-semibold tracking-wider text-blue-600 uppercase dark:text-blue-400"
                            >
                                Issue details
                            </p>
                            <h2 class="mt-1 text-xl font-semibold">
                                {{ selectedReport.subject }}
                            </h2>
                        </div>
                        <Button
                            size="icon"
                            variant="ghost"
                            @click="selectedReport = null"
                            ><X class="h-4 w-4"
                        /></Button>
                    </div>
                    <div class="space-y-6 p-6">
                        <div class="grid gap-4 text-sm sm:grid-cols-2">
                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Reporter
                                </p>
                                <p class="mt-1 font-medium">
                                    {{
                                        selectedReport.reporter?.name ??
                                        'Guest user'
                                    }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{
                                        selectedReport.reporter?.email ??
                                        'No email available'
                                    }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Submitted
                                </p>
                                <p class="mt-1 font-medium">
                                    {{ formatDate(selectedReport.created_at) }}
                                </p>
                            </div>
                        </div>
                        <div class="border-y py-5">
                            <p
                                class="text-xs font-medium text-muted-foreground"
                            >
                                Description
                            </p>
                            <p
                                class="mt-2 text-sm leading-6 whitespace-pre-wrap"
                            >
                                {{ selectedReport.description }}
                            </p>
                        </div>
                        <dl class="grid gap-4 text-sm sm:grid-cols-2">
                            <div>
                                <dt class="text-xs text-muted-foreground">
                                    Affected page
                                </dt>
                                <dd class="mt-1 font-medium break-all">
                                    {{
                                        selectedReport.page_url ??
                                        'Not provided'
                                    }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-muted-foreground">
                                    Browser / device
                                </dt>
                                <dd class="mt-1 font-medium">
                                    {{
                                        selectedReport.browser ?? 'Not provided'
                                    }}
                                </dd>
                            </div>
                        </dl>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="space-y-2 text-sm font-medium"
                                ><span>Status</span
                                ><select
                                    v-model="editStatus"
                                    class="form-control"
                                >
                                    <option value="open">Open</option>
                                    <option value="in_progress">
                                        In progress
                                    </option>
                                    <option value="resolved">Resolved</option>
                                    <option value="closed">Closed</option>
                                </select></label
                            ><label class="space-y-2 text-sm font-medium"
                                ><span>Priority</span
                                ><select
                                    v-model="editPriority"
                                    class="form-control"
                                >
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select></label
                            >
                        </div>
                        <label class="block space-y-2 text-sm font-medium"
                            ><span>Internal notes</span
                            ><textarea
                                v-model="adminNotes"
                                rows="4"
                                class="form-control min-h-24 resize-y py-2"
                                placeholder="Add investigation or resolution notes..."
                            />
                        </label>
                    </div>
                    <div class="flex justify-end gap-2 border-t p-4">
                        <Button
                            variant="outline"
                            :disabled="saving"
                            @click="selectedReport = null"
                            >Cancel</Button
                        ><Button :disabled="saving" @click="updateReport">{{
                            saving ? 'Saving...' : 'Save changes'
                        }}</Button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>

<style scoped>
.issue-data-table {
    --easy-table-border: 0;
    --easy-table-row-border: 1px solid var(--border);
    --easy-table-header-background-color: var(--muted);
    --easy-table-header-font-color: var(--muted-foreground);
    --easy-table-header-font-size: 12px;
    --easy-table-header-height: 92px;
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
    --easy-table-body-row-height: 64px;
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
.column-filter-input,
.form-control {
    height: 30px;
    width: 100%;
    min-width: 90px;
    border: 1px solid var(--border);
    border-radius: 6px;
    background: var(--background);
    padding: 0 8px;
    color: var(--foreground);
    font-size: 11px;
    font-weight: 400;
    outline: none;
}
.form-control {
    height: 36px;
    font-size: 13px;
}
.column-filter-input:focus,
.form-control:focus {
    border-color: var(--ring);
    box-shadow: 0 0 0 2px color-mix(in srgb, var(--ring) 20%, transparent);
}
:deep(.vue3-easy-data-table__main) {
    background: var(--background);
}
:deep(.vue3-easy-data-table__main table) {
    min-width: 1220px;
}
</style>
