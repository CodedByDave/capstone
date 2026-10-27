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
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import type { DataTableOptions } from '@/composables/useDataTable';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    Archive,
    ArchiveRestore,
    Download,
    Eye,
    FileClock,
    RefreshCcw,
    Trash2,
    Upload,
} from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

interface AuditLog {
    record_id: number;
    category: 'authentication' | 'activity';
    user_id: number | null;
    user_public_id: string | null;
    name: string | null;
    email: string | null;
    role: string | null;
    module: string;
    event: string;
    details: string | null;
    ip_address: string | null;
    user_agent: string | null;
    shop_name: string | null;
    occurred_at: string;
    archivable: number | boolean;
}

interface Paginator {
    data: AuditLog[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    logs: Paginator;
    stats: {
        total: number;
        authentication: number;
        activities: number;
        failed: number;
        archived: number;
    };
    modules: string[];
    filters: Record<string, string>;
}>();

const page = usePage();

onMounted(() => {
    const flash = page.props.toast as
        | { type: string; message: string }
        | undefined;
    if (!flash) return;
    if (flash.type === 'error') {
        toast.error(flash.message);
        return;
    }

    toast.success(flash.message);
});

const search = ref(props.filters.search ?? '');
const category = ref(props.filters.category ?? 'all');
const role = ref(props.filters.role ?? 'all');
const module = ref(props.filters.module ?? 'all');
const event = ref(props.filters.event ?? '');
const date = ref(props.filters.date ?? '');
const tableLoading = ref(false);
const importing = ref(false);
const importInput = ref<HTMLInputElement | null>(null);

const headers: DataTableHeader[] = [
    { text: 'User', value: 'name', width: 200, sortable: true },
    { text: 'Email', value: 'email', width: 210, sortable: true },
    { text: 'Role', value: 'role', width: 125 },
    { text: 'Type', value: 'category', width: 150, sortable: true },
    { text: 'Module', value: 'module', width: 155, sortable: true },
    { text: 'Event', value: 'event', width: 135, sortable: true },
    { text: 'Context', value: 'context', width: 230 },
    { text: 'Date & time', value: 'occurred_at', width: 190, sortable: true },
    { text: 'Actions', value: 'actions', width: 105, align: 'center' },
];

const categoryOptions = computed(() => [
    { value: 'all', label: 'All', count: props.stats.total },
    {
        value: 'authentication',
        label: 'Authentication',
        count: props.stats.authentication,
    },
    {
        value: 'activity',
        label: 'User Activity',
        count: props.stats.activities,
    },
]);

function setCategory(value: string) {
    category.value = value;
    filterTable();
}

const serverOptions = ref<DataTableOptions>({
    page: props.logs.current_page,
    rowsPerPage: props.logs.per_page,
    sortBy: props.filters.sort_by ?? 'occurred_at',
    sortType: props.filters.sort_direction === 'asc' ? 'asc' : 'desc',
});

function queryParams() {
    return {
        search: search.value || undefined,
        category: category.value !== 'all' ? category.value : undefined,
        role: role.value !== 'all' ? role.value : undefined,
        module: module.value !== 'all' ? module.value : undefined,
        event: event.value || undefined,
        date: date.value || undefined,
        sort_by: serverOptions.value.sortBy,
        sort_direction: serverOptions.value.sortType,
        page: serverOptions.value.page,
        per_page: serverOptions.value.rowsPerPage,
    };
}

function applyFilters() {
    router.get('/admin/audit-logs', queryParams(), {
        only: ['logs', 'filters'],
        preserveState: true,
        preserveScroll: true,
        replace: true,
        showProgress: false,
        onStart: () => (tableLoading.value = true),
        onFinish: () => (tableLoading.value = false),
    });
}

function filterTable() {
    if (serverOptions.value.page === 1) {
        applyFilters();
    } else {
        serverOptions.value.page = 1;
    }
}

function resetFilters() {
    search.value = '';
    category.value = 'all';
    role.value = 'all';
    module.value = 'all';
    event.value = '';
    date.value = '';
    serverOptions.value = {
        page: 1,
        rowsPerPage: 20,
        sortBy: 'occurred_at',
        sortType: 'desc',
    };
}

watch(serverOptions, () => applyFilters(), { deep: true });

const selected = ref<Array<string | number>>([]);

function entryKey(log: AuditLog) {
    return `${log.category}:${log.record_id}`;
}

const archiveLog = ref<AuditLog | null>(null);
const archiveOpen = ref(false);

function openArchive(log: AuditLog) {
    archiveLog.value = log;
    archiveOpen.value = true;
}

function cancelArchive() {
    archiveOpen.value = false;
    archiveLog.value = null;
}

function confirmArchive() {
    if (!archiveLog.value) return;
    router.delete(
        `/admin/audit-logs/${archiveLog.value.category}/${archiveLog.value.record_id}`,
        {
            preserveScroll: true,
            onSuccess: () => {
                archiveOpen.value = false;
                archiveLog.value = null;
            },
            onError: () => toast.error('Failed to archive the log.'),
        },
    );
}

function bulkArchive() {
    if (!selected.value.length) return;
    router.post(
        '/admin/audit-logs/bulk-archive',
        {
            entries: selected.value.map((key) => {
                const [category, id] = String(key).split(':');
                return { category, id: Number(id) };
            }),
        },
        {
            preserveScroll: true,
            onSuccess: () => (selected.value = []),
            onError: () => toast.error('Bulk archive failed.'),
        },
    );
}

function exportCsv() {
    const params = new URLSearchParams();
    const exportFilters = queryParams();
    Object.entries(exportFilters).forEach(([key, value]) => {
        if (value !== undefined && !['page', 'per_page'].includes(key)) {
            params.set(key, String(value));
        }
    });
    window.location.href = `/admin/audit-logs/export?${params.toString()}`;
}

function chooseCsvFile() {
    importInput.value?.click();
}

function importCsv(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    router.post(
        '/admin/audit-logs/import',
        { file },
        {
            forceFormData: true,
            preserveScroll: true,
            onStart: () => (importing.value = true),
            onError: (errors) =>
                toast.error(
                    String(
                        errors.file ||
                            'The audit backup could not be imported.',
                    ),
                ),
            onFinish: () => {
                importing.value = false;
                input.value = '';
            },
        },
    );
}

function formatDate(value: string) {
    return new Intl.DateTimeFormat('en-PH', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function parseAgent(userAgent: string | null) {
    if (!userAgent) return null;
    if (/Edg/i.test(userAgent)) return 'Edge';
    if (/Chrome/i.test(userAgent)) return 'Chrome';
    if (/Firefox/i.test(userAgent)) return 'Firefox';
    if (/Safari/i.test(userAgent)) return 'Safari';
    return 'Other browser';
}

function context(log: AuditLog) {
    if (log.category === 'authentication') {
        return (
            [log.ip_address, parseAgent(log.user_agent), log.details]
                .filter(Boolean)
                .join(' · ') || 'No additional details'
        );
    }

    const parts = [log.shop_name];
    if (log.details) {
        try {
            const changes = JSON.parse(log.details);
            if (changes?.target_user) {
                const target = changes.target_user;
                parts.push(
                    `Target: ${target.name}${target.email ? ` (${target.email})` : ''}`,
                );
            } else if (changes?.result) {
                parts.push(
                    `Result: ${Object.entries(changes.result)
                        .map(([key, value]) => `${key} ${value}`)
                        .join(', ')}`,
                );
            } else {
                const fields = Object.keys(changes ?? {}).slice(0, 3);
                if (fields.length) parts.push(`Changed: ${fields.join(', ')}`);
            }
        } catch {
            parts.push(log.details);
        }
    }
    return parts.filter(Boolean).join(' · ') || 'System activity';
}

function eventLabel(value: string) {
    return value
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
}

const roleBadge: Record<string, string> = {
    super_admin:
        'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300',
    owner: 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
    manager: 'bg-cyan-100 text-cyan-700 dark:bg-cyan-950 dark:text-cyan-300',
    staff: 'bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-300',
    user: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
};

const eventTone: Record<string, StatusTone> = {
    success: 'success',
    failed: 'danger',
    logout: 'neutral',
};

const tableItems = computed(() =>
    props.logs.data.map((log) => ({
        ...log,
        entry_key: entryKey(log),
        context: context(log),
        actions: '',
    })),
);
</script>

<template>
    <Head title="Audit & Logs" />
    <AdminLayout title="Audit & Logs">
        <div class="space-y-6 px-6">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-5">
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Total Events
                            </p>
                        </div>
                        <p class="text-3xl font-bold">
                            {{ stats.total.toLocaleString() }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Authentication
                            </p>
                        </div>
                        <p class="text-3xl font-bold text-indigo-600">
                            {{ stats.authentication.toLocaleString() }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                User Activities
                            </p>
                        </div>
                        <p class="text-3xl font-bold text-emerald-600">
                            {{ stats.activities.toLocaleString() }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Failed Logins
                            </p>
                        </div>
                        <p class="text-3xl font-bold text-red-600">
                            {{ stats.failed.toLocaleString() }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Archived
                            </p>
                        </div>
                        <p class="text-3xl font-bold text-amber-600">
                            {{ stats.archived.toLocaleString() }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <DataTable
                v-model:options="serverOptions"
                v-model:selected="selected"
                :headers="headers"
                :items="tableItems"
                :total="logs.total"
                :loading="tableLoading"
                item-key="entry_key"
                :row-label="(log) => `audit log ${log.record_id}`"
                :rows-items="[5, 10, 20, 50, 100]"
                selectable
                noun="audit logs"
                :empty-icon="FileClock"
                empty-message="No audit events found."
                :min-width="1500"
                @reset="resetFilters"
            >
                <template #toolbar>
                    <div class="space-y-3">
                        <DataTableToolbar
                            v-model:search="search"
                            :status="category"
                            :statuses="categoryOptions"
                            search-placeholder="Search users, emails, IPs, modules or shops..."
                            search-label="Search audit logs"
                            status-label="Filter by log type"
                            @update:status="setCategory"
                            @submit="filterTable"
                        >
                            <template #actions>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    class="border-red-300 bg-red-50 text-red-700 hover:bg-red-100 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300"
                                    @click="
                                        router.visit(
                                            '/admin/audit-logs/archive',
                                        )
                                    "
                                >
                                    <ArchiveRestore class="mr-1.5 h-4 w-4" />
                                    Archive
                                </Button>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    class="border-slate-300 bg-slate-50 text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-300"
                                    @click="resetFilters"
                                >
                                    <RefreshCcw class="mr-1.5 h-4 w-4" />
                                    Reset
                                </Button>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    class="border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300"
                                    @click="exportCsv"
                                >
                                    <Download class="mr-1.5 h-4 w-4" />
                                    Export CSV
                                </Button>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    class="border-blue-300 bg-blue-50 text-blue-700 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300"
                                    :disabled="importing"
                                    @click="chooseCsvFile"
                                >
                                    <Upload class="mr-1.5 h-4 w-4" />
                                    {{
                                        importing
                                            ? 'Importing...'
                                            : 'Import CSV'
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

                        <div class="flex flex-wrap items-center gap-2">
                            <select
                                v-model="role"
                                class="filter-input"
                                aria-label="Filter by role"
                                @change="filterTable"
                            >
                                <option value="all">All roles</option>
                                <option value="super_admin">Super Admin</option>
                                <option value="owner">Owner</option>
                                <option value="staff">Staff</option>
                                <option value="user">Customer</option>
                            </select>
                            <select
                                v-model="module"
                                class="filter-input"
                                aria-label="Filter by module"
                                @change="filterTable"
                            >
                                <option value="all">All modules</option>
                                <option value="Authentication">
                                    Authentication
                                </option>
                                <option
                                    v-for="item in modules"
                                    :key="item"
                                    :value="item"
                                >
                                    {{ item }}
                                </option>
                            </select>
                            <input
                                v-model="event"
                                class="filter-input"
                                placeholder="Filter event"
                                aria-label="Filter by event"
                                @keyup.enter="filterTable"
                            />
                            <input
                                v-model="date"
                                type="date"
                                class="filter-input"
                                aria-label="Filter by date"
                                @change="filterTable"
                            />
                        </div>
                    </div>
                </template>

                <template #bulk-actions>
                    <Button
                        size="sm"
                        variant="outline"
                        class="border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300"
                        @click="bulkArchive"
                    >
                        <Archive class="mr-1.5 h-4 w-4" />
                        Archive ({{ selected.length }})
                    </Button>
                </template>

                <template #item-name="log">
                    <IdentityCell :name="log.name || 'Unknown user'" />
                </template>
                <template #item-email="log">
                    <span class="text-xs text-muted-foreground">{{
                        log.email || '—'
                    }}</span>
                </template>
                <template #item-role="log">
                    <span
                        v-if="log.role"
                        class="rounded-full px-2 py-0.5 text-xs font-medium whitespace-nowrap capitalize"
                        :class="roleBadge[log.role] ?? roleBadge.user"
                    >
                        {{ log.role.replace('_', ' ') }}
                    </span>
                    <span v-else class="text-xs text-muted-foreground">—</span>
                </template>
                <template #item-category="log">
                    <StatusPill
                        :tone="
                            log.category === 'authentication'
                                ? 'info'
                                : 'success'
                        "
                    >
                        {{
                            log.category === 'authentication'
                                ? 'Authentication'
                                : 'User Activity'
                        }}
                    </StatusPill>
                </template>
                <template #item-module="log">
                    <span class="text-xs font-medium">{{ log.module }}</span>
                </template>
                <template #item-event="log">
                    <StatusPill :tone="eventTone[log.event] ?? 'info'">
                        {{ eventLabel(log.event) }}
                    </StatusPill>
                </template>
                <template #item-context="log">
                    <span
                        class="line-clamp-2 text-xs text-muted-foreground"
                        :title="log.context"
                        >{{ log.context }}</span
                    >
                </template>
                <template #item-occurred_at="log">
                    <span
                        class="text-xs whitespace-nowrap text-muted-foreground"
                        >{{ formatDate(log.occurred_at) }}</span
                    >
                </template>
                <template #item-actions="log">
                    <RowActions
                        :label="`audit event ${log.record_id}`"
                        :actions="[
                            {
                                label: 'View details',
                                icon: Eye,
                                onSelect: () =>
                                    router.visit(
                                        `/admin/audit-logs/${log.category}/${log.record_id}`,
                                    ),
                            },
                            {
                                label: 'Archive',
                                icon: Trash2,
                                destructive: true,
                                onSelect: () => openArchive(log),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>

        <AlertDialog v-model:open="archiveOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle
                        >Archive Authentication Log</AlertDialogTitle
                    >
                    <AlertDialogDescription>
                        This authentication event will move to the archive and
                        can be restored later.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <Button variant="outline" @click="cancelArchive"
                        >Cancel</Button
                    >
                    <Button variant="destructive" @click="confirmArchive"
                        >Archive</Button
                    >
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AdminLayout>
</template>

<style scoped>
.filter-input {
    height: 36px;
    min-width: 140px;
    border: 1px solid var(--border);
    border-radius: 6px;
    background: var(--background);
    padding: 0 10px;
    color: var(--foreground);
    font-size: 13px;
    outline: none;
}

.filter-input:focus-visible {
    border-color: var(--ring);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--ring) 25%, transparent);
}
</style>
