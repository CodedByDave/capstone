<script setup lang="ts">
import {
    DataTable,
    DataTableToolbar,
    IdentityCell,
    RowActions,
    StatusPill,
    type DataTableHeader,
    type StatusTone,
} from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import type { DataTableOptions } from '@/composables/useDataTable';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Download, Eye, Inbox, RefreshCcw, Upload, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
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
const headers: DataTableHeader[] = [
    { text: 'Reported by', value: 'reporter', width: 230, sortable: true },
    { text: 'Issue', value: 'subject', width: 320, sortable: true },
    { text: 'Category', value: 'category', width: 165, sortable: true },
    { text: 'Priority', value: 'priority', width: 140, sortable: true },
    { text: 'Status', value: 'status', width: 150, sortable: true },
    { text: 'Reported', value: 'created_at', width: 180, sortable: true },
    { text: 'Action', value: 'actions', width: 90, align: 'center' },
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
const serverOptions = ref<DataTableOptions>({
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
const statusOptions = computed(() => [
    { value: '', label: 'All', count: props.stats.total },
    { value: 'open', label: 'Open', count: props.stats.open },
    {
        value: 'in_progress',
        label: 'In progress',
        count: props.stats.in_progress,
    },
    { value: 'resolved', label: 'Resolved', count: props.stats.resolved },
    {
        value: 'closed',
        label: 'Closed',
        count: Math.max(
            0,
            props.stats.total -
                props.stats.open -
                props.stats.in_progress -
                props.stats.resolved,
        ),
    },
]);

function setStatus(value: string) {
    status.value = value;
    filterTable();
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
const statusTone: Record<string, StatusTone> = {
    open: 'info',
    in_progress: 'warning',
    resolved: 'success',
    closed: 'neutral',
};
const priorityTone: Record<string, StatusTone> = {
    low: 'neutral',
    medium: 'info',
    high: 'warning',
    critical: 'danger',
};
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

            <DataTable
                v-model:options="serverOptions"
                :headers="headers"
                :items="reports.data"
                :total="reports.total"
                :loading="tableLoading"
                item-key="public_id"
                noun="issue reports"
                :empty-icon="Inbox"
                empty-message="No issue reports found. New user reports will appear here for review."
                :min-width="1220"
                @reset="resetFilters"
            >
                <template #toolbar>
                    <DataTableToolbar
                        v-model:search="search"
                        :status="status"
                        :statuses="statusOptions"
                        search-placeholder="Search issue, reporter, or email..."
                        search-label="Search issue reports"
                        status-label="Filter by status"
                        @update:status="setStatus"
                        @submit="filterTable"
                    >
                        <select
                            v-model="category"
                            class="filter-input"
                            aria-label="Filter by category"
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
                        <select
                            v-model="priority"
                            class="filter-input"
                            aria-label="Filter by priority"
                            @change="filterTable"
                        >
                            <option value="">All priorities</option>
                            <option value="critical">Critical</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>

                        <template #actions>
                            <Button
                                size="sm"
                                variant="outline"
                                @click="resetFilters"
                                ><RefreshCcw
                                    class="mr-1.5 h-4 w-4"
                                />Reset</Button
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
                        </template>
                    </DataTableToolbar>
                </template>

                <template #item-reporter="report">
                    <IdentityCell
                        :name="report.reporter?.name ?? 'Guest user'"
                        :secondary="
                            report.reporter?.email ?? 'No email available'
                        "
                    />
                </template>
                <template #item-subject="report">
                    <div class="max-w-[300px]">
                        <p class="truncate font-medium">{{ report.subject }}</p>
                        <p class="mt-1 truncate text-xs text-muted-foreground">
                            {{ report.description }}
                        </p>
                    </div>
                </template>
                <template #item-category="report">
                    <span
                        :class="[
                            'inline-flex rounded-full px-2.5 py-1 text-xs font-medium whitespace-nowrap',
                            categoryClass(report.category),
                        ]"
                        >{{ label(report.category) }}</span
                    >
                </template>
                <template #item-priority="report">
                    <StatusPill
                        :tone="priorityTone[report.priority] ?? 'neutral'"
                    >
                        {{ label(report.priority) }}
                    </StatusPill>
                </template>
                <template #item-status="report">
                    <StatusPill :tone="statusTone[report.status] ?? 'neutral'">
                        {{ label(report.status) }}
                    </StatusPill>
                </template>
                <template #item-created_at="report">
                    <span
                        class="text-xs whitespace-nowrap text-muted-foreground"
                        >{{ formatDate(report.created_at) }}</span
                    >
                </template>
                <template #item-actions="report">
                    <RowActions
                        :label="`issue report ${report.subject}`"
                        :actions="[
                            {
                                label: 'View and update',
                                icon: Eye,
                                onSelect: () => openReport(report),
                            },
                        ]"
                    />
                </template>
            </DataTable>
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
.filter-input,
.form-control {
    height: 36px;
    width: 100%;
    border: 1px solid var(--border);
    border-radius: 6px;
    background: var(--background);
    padding: 0 8px;
    color: var(--foreground);
    font-size: 13px;
    font-weight: 400;
    outline: none;
}
.filter-input {
    width: auto;
    min-width: 140px;
}
.filter-input:focus,
.form-control:focus {
    border-color: var(--ring);
    box-shadow: 0 0 0 2px color-mix(in srgb, var(--ring) 20%, transparent);
}
</style>
