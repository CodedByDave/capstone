<script setup lang="ts">
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
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    Archive,
    ArchiveRestore,
    Download,
    Eye,
    FileClock,
    RefreshCcw,
    Search,
    Trash2,
    Upload,
} from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import Vue3EasyDataTable, {
    type Header,
    type ServerOptions,
} from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';
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

const EasyDataTable = Vue3EasyDataTable;

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
    flash.type === 'error'
        ? toast.error(flash.message)
        : toast.success(flash.message);
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

const headers: Header[] = [
    { text: '', value: 'selection', width: 48 },
    { text: 'User', value: 'name', width: 185, sortable: true },
    { text: 'Email', value: 'email', width: 210, sortable: true },
    { text: 'Role', value: 'role', width: 125 },
    { text: 'Type', value: 'category', width: 150, sortable: true },
    { text: 'Module', value: 'module', width: 155, sortable: true },
    { text: 'Event', value: 'event', width: 135, sortable: true },
    { text: 'Context', value: 'context', width: 230 },
    { text: 'Date & Time', value: 'occurred_at', width: 190, sortable: true },
    { text: 'Actions', value: 'actions', width: 105 },
];

const serverOptions = ref<ServerOptions>({
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

const selected = ref<string[]>([]);
const selectableLogs = computed(() => props.logs.data);

function entryKey(log: AuditLog) {
    return `${log.category}:${log.record_id}`;
}

const allSelected = computed(
    () =>
        selectableLogs.value.length > 0 &&
        selectableLogs.value.every((log) =>
            selected.value.includes(entryKey(log)),
        ),
);

function toggleAll() {
    selected.value = allSelected.value
        ? []
        : selectableLogs.value.map(entryKey);
}

function toggleOne(log: AuditLog) {
    const key = entryKey(log);
    selected.value = selected.value.includes(key)
        ? selected.value.filter((selectedKey) => selectedKey !== key)
        : [...selected.value, key];
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
                const [category, id] = key.split(':');
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

const eventBadge: Record<string, string> = {
    success:
        'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300',
    failed: 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
    logout: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
};

const tableItems = computed(() =>
    props.logs.data.map((log) => ({
        ...log,
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

            <CardContent class="space-y-4">
                <div class="flex flex-wrap items-center gap-2 pt-6">
                    <div class="relative min-w-48 flex-1">
                        <Search
                            class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search users, emails, IPs, modules or shops..."
                            class="pl-8"
                            @keyup.enter="filterTable"
                        />
                    </div>

                    <Button
                        v-if="selected.length"
                        size="sm"
                        variant="outline"
                        class="border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300"
                        @click="bulkArchive"
                    >
                        <Archive class="mr-1.5 h-4 w-4" />
                        Archive ({{ selected.length }})
                    </Button>
                    <Button
                        size="sm"
                        variant="outline"
                        class="border-red-300 bg-red-50 text-red-700 hover:bg-red-100 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300"
                        @click="router.visit('/admin/audit-logs/archive')"
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
                        {{ importing ? 'Importing...' : 'Import CSV' }}
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
                        class="audit-data-table"
                        :headers="headers"
                        :items="tableItems"
                        :server-items-length="logs.total"
                        :loading="tableLoading"
                        :rows-items="[5, 10, 20, 50, 100]"
                        rows-of-page-separator-message="out of"
                        rows-per-page-message="Rows per page:"
                        buttons-pagination
                        alternating
                        must-sort
                    >
                        <template #header-selection>
                            <input
                                type="checkbox"
                                aria-label="Select all authentication logs on this page"
                                :checked="allSelected"
                                class="rounded"
                                @change="toggleAll"
                            />
                        </template>

                        <template #header-role="{ text }">
                            <div class="column-filter">
                                <span>{{ text }}</span>
                                <select
                                    v-model="role"
                                    class="column-filter-input"
                                    @click.stop
                                    @change="filterTable"
                                >
                                    <option value="all">All roles</option>
                                    <option value="super_admin">
                                        Super Admin
                                    </option>
                                    <option value="owner">Owner</option>
                                    <option value="staff">Staff</option>
                                    <option value="user">Customer</option>
                                </select>
                            </div>
                        </template>

                        <template #header-category="{ text }">
                            <div class="column-filter">
                                <span>{{ text }}</span>
                                <select
                                    v-model="category"
                                    class="column-filter-input"
                                    @click.stop
                                    @change="filterTable"
                                >
                                    <option value="all">All types</option>
                                    <option value="authentication">
                                        Authentication
                                    </option>
                                    <option value="activity">
                                        User Activity
                                    </option>
                                </select>
                            </div>
                        </template>

                        <template #header-module="{ text }">
                            <div class="column-filter">
                                <span>{{ text }}</span>
                                <select
                                    v-model="module"
                                    class="column-filter-input"
                                    @click.stop
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
                            </div>
                        </template>

                        <template #header-event="{ text }">
                            <div class="column-filter">
                                <span>{{ text }}</span>
                                <input
                                    v-model="event"
                                    class="column-filter-input"
                                    placeholder="Filter event"
                                    @click.stop
                                    @keyup.enter="filterTable"
                                />
                            </div>
                        </template>

                        <template #header-occurred_at="{ text }">
                            <div class="column-filter">
                                <span>{{ text }}</span>
                                <input
                                    v-model="date"
                                    type="date"
                                    class="column-filter-input"
                                    @click.stop
                                    @change="filterTable"
                                />
                            </div>
                        </template>

                        <template #item-selection="log">
                            <input
                                type="checkbox"
                                :checked="selected.includes(entryKey(log))"
                                :aria-label="`Select audit log ${log.record_id}`"
                                class="rounded"
                                @change="toggleOne(log)"
                            />
                        </template>

                        <template #item-name="log">
                            <span class="font-medium whitespace-nowrap">{{
                                log.name || 'Unknown user'
                            }}</span>
                        </template>
                        <template #item-email="log">
                            <span class="text-xs text-muted-foreground">{{
                                log.email || '—'
                            }}</span>
                        </template>
                        <template #item-role="log">
                            <span
                                v-if="log.role"
                                class="rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                                :class="roleBadge[log.role] ?? roleBadge.user"
                            >
                                {{ log.role.replace('_', ' ') }}
                            </span>
                            <span v-else class="text-xs text-muted-foreground"
                                >—</span
                            >
                        </template>
                        <template #item-category="log">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="
                                    log.category === 'authentication'
                                        ? 'bg-indigo-100 text-indigo-700'
                                        : 'bg-emerald-100 text-emerald-700'
                                "
                            >
                                {{
                                    log.category === 'authentication'
                                        ? 'Authentication'
                                        : 'User Activity'
                                }}
                            </span>
                        </template>
                        <template #item-module="log">
                            <span class="text-xs font-medium">{{
                                log.module
                            }}</span>
                        </template>
                        <template #item-event="log">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="
                                    eventBadge[log.event] ??
                                    'bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300'
                                "
                            >
                                {{ eventLabel(log.event) }}
                            </span>
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
                            <div class="flex items-center justify-center gap-1">
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    aria-label="View audit event"
                                    @click="
                                        router.visit(
                                            `/admin/audit-logs/${log.category}/${log.record_id}`,
                                        )
                                    "
                                >
                                    <Eye class="h-4 w-4 text-blue-500" />
                                </Button>
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    aria-label="Archive log"
                                    @click="openArchive(log)"
                                >
                                    <Trash2 class="h-4 w-4 text-amber-500" />
                                </Button>
                            </div>
                        </template>

                        <template #empty-message>
                            <div
                                class="py-10 text-center text-sm text-muted-foreground"
                            >
                                <FileClock
                                    class="mx-auto mb-2 h-10 w-10 opacity-20"
                                />
                                No audit events found.
                            </div>
                        </template>
                    </EasyDataTable>
                </div>
            </CardContent>
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
.audit-data-table {
    --easy-table-border: 0;
    --easy-table-row-border: 1px solid var(--border);
    --easy-table-header-background-color: var(--muted);
    --easy-table-header-font-color: var(--muted-foreground);
    --easy-table-header-font-size: 12px;
    --easy-table-header-height: 82px;
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
    --easy-table-body-row-height: 62px;
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

.column-filter {
    display: flex;
    min-width: 0;
    flex-direction: column;
    gap: 6px;
    text-align: left;
}

.column-filter-input {
    height: 30px;
    width: 100%;
    min-width: 95px;
    border: 1px solid var(--border);
    border-radius: 6px;
    background: var(--background);
    padding: 0 8px;
    color: var(--foreground);
    font-size: 12px;
    font-weight: 400;
    outline: none;
}

.column-filter-input:focus {
    border-color: var(--ring);
    box-shadow: 0 0 0 2px color-mix(in srgb, var(--ring) 20%, transparent);
}

:deep(.vue3-easy-data-table__main) {
    background: var(--background);
}

:deep(.vue3-easy-data-table__main table) {
    min-width: 1500px;
}
</style>
