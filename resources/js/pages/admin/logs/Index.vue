<script setup lang="ts">
import {
    DataTable,
    DataTableToolbar,
    IdentityCell,
    KpiTiles,
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
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { DataTableOptions } from '@/composables/useDataTable';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    Archive,
    ArchiveRestore,
    ArrowUpDown,
    ChevronDown,
    Download,
    Eye,
    FileClock,
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
    super_admin: 'bg-purple-500/15 text-purple-600 dark:text-purple-300',
    owner: 'bg-blue-500/15 text-blue-600 dark:text-blue-300',
    manager: 'bg-cyan-500/15 text-cyan-600 dark:text-cyan-300',
    staff: 'bg-muted text-foreground',
    supplier: 'bg-muted text-foreground',
    user: 'bg-muted text-muted-foreground',
};

const statCards = computed(() => [
    { label: 'Total events', value: props.stats.total },
    { label: 'Authentication', value: props.stats.authentication },
    { label: 'User activities', value: props.stats.activities },
    { label: 'Failed logins', value: props.stats.failed },
    { label: 'Archived', value: props.stats.archived },
]);

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
        <div class="mx-auto w-full max-w-[1280px] space-y-6 px-6">
            <!-- Header -->
            <header class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        Audit &amp; logs
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Sign-ins and user activity recorded across the platform.
                    </p>
                </div>

                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button
                            variant="success-outline"
                            class="h-11 gap-2"
                            :disabled="importing"
                        >
                            <ArrowUpDown class="h-4 w-4" aria-hidden="true" />
                            {{ importing ? 'Importing...' : 'Import / export' }}
                            <ChevronDown class="h-4 w-4" aria-hidden="true" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-48">
                        <DropdownMenuItem @select="exportCsv">
                            <Download class="mr-2 h-4 w-4" />
                            Export CSV
                        </DropdownMenuItem>
                        <DropdownMenuItem @select="chooseCsvFile">
                            <Upload class="mr-2 h-4 w-4" />
                            Import CSV
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem
                            @select="router.visit('/admin/audit-logs/archive')"
                        >
                            <ArchiveRestore class="mr-2 h-4 w-4" />
                            Archived logs
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
                <input
                    ref="importInput"
                    type="file"
                    accept=".csv,text/csv"
                    class="hidden"
                    @change="importCsv"
                />
            </header>

            <!-- Stats -->
            <KpiTiles :tiles="statCards" />

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
                            :resettable="
                                !!search ||
                                category !== 'all' ||
                                role !== 'all' ||
                                module !== 'all' ||
                                !!event ||
                                !!date
                            "
                            @reset="resetFilters"
                            v-model:search="search"
                            :status="category"
                            :statuses="categoryOptions"
                            search-placeholder="Search users, emails, IPs, modules or shops..."
                            search-label="Search audit logs"
                            status-label="Filter by log type"
                            @update:status="setCategory"
                            @submit="filterTable"
                        />

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
                        variant="warning-outline"
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
                        class="rounded-md px-2 py-0.5 text-xs font-medium whitespace-nowrap capitalize"
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
    height: 44px;
    min-width: 140px;
    border: 1px solid var(--border);
    border-radius: 8px;
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
