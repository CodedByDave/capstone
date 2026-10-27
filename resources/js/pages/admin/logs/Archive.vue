<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import {
    DataTable,
    DataTableToolbar,
    IdentityCell,
    RowActions,
    StatusPill,
    type DataTableHeader,
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
import { usePaginatorTable } from '@/composables/useDataTable';
import {
    ArchiveRestore,
    ArrowLeft,
    ShieldCheck,
    Trash2,
} from 'lucide-vue-next';

// ─── Types ────────────────────────────────────────────────────────────────────

interface AuditLog {
    record_id: number;
    category: 'authentication' | 'activity';
    email: string | null;
    name: string | null;
    role: string | null;
    module: string;
    event: string;
    details: string | null;
    ip_address: string | null;
    shop_name: string | null;
    occurred_at: string;
    archived_at: string;
}

interface Paginator {
    data: AuditLog[];
    current_page: number;
    last_page: number;
    per_page?: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    logs: Paginator;
    total: number;
    filters: Record<string, string>;
}>();

// ─── Flash ────────────────────────────────────────────────────────────────────

const page = usePage();

onMounted(() => {
    const flash = page.props.toast as
        | { type: string; message: string }
        | undefined;
    if (!flash) return;
    switch (flash.type) {
        case 'success':
            toast.success(flash.message);
            break;
        case 'error':
            toast.error(flash.message);
            break;
        default:
            toast(flash.message);
    }
});

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Audit & Logs', href: '/admin/audit-logs' },
    { title: 'Archive', href: '/admin/audit-logs/archive' },
];

// ─── Filters ──────────────────────────────────────────────────────────────────

const search = ref(props.filters.search ?? '');
const category = ref(props.filters.category ?? 'all');

function applyFilters() {
    router.get(
        '/admin/audit-logs/archive',
        {
            search: search.value || undefined,
            category: category.value !== 'all' ? category.value : undefined,
        },
        { preserveState: true, replace: true },
    );
}

function setCategory(value: string) {
    category.value = value;
    applyFilters();
}

function resetFilters() {
    search.value = '';
    category.value = 'all';
    applyFilters();
}

const hasFilters = computed(
    () => Boolean(props.filters.search) || Boolean(props.filters.category),
);

const categoryOptions = [
    { value: 'all', label: 'All' },
    { value: 'authentication', label: 'Authentication' },
    { value: 'activity', label: 'User Activity' },
];

// ─── Table ────────────────────────────────────────────────────────────────────

const { options, loading } = usePaginatorTable(() => props.logs, ['logs']);

const headers: DataTableHeader[] = [
    { text: 'User', value: 'name', width: 240 },
    { text: 'Role', value: 'role' },
    { text: 'Type', value: 'category' },
    { text: 'Module / event', value: 'module' },
    { text: 'Context', value: 'context' },
    { text: 'Archived at', value: 'archived_at' },
    { text: 'Actions', value: 'actions', align: 'center', width: 120 },
];

// ─── Selection ────────────────────────────────────────────────────────────────

const selected = ref<Array<string | number>>([]);
const entryKey = (log: AuditLog) => `${log.category}:${log.record_id}`;

const tableItems = computed(() =>
    props.logs.data.map((log) => ({ ...log, entry_key: entryKey(log) })),
);

// ─── Restore ──────────────────────────────────────────────────────────────────

function restore(log: AuditLog) {
    router.post(
        `/admin/audit-logs/archive/${log.category}/${log.record_id}/restore`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => toast.success('Log restored.'),
            onError: () => toast.error('Failed to restore.'),
        },
    );
}

function bulkRestore() {
    if (!selected.value.length) return;
    router.post(
        '/admin/audit-logs/archive/bulk-restore',
        {
            entries: selected.value.map((key) => {
                const [category, id] = String(key).split(':');
                return { category, id: Number(id) };
            }),
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(`${selected.value.length} log(s) restored.`);
                selected.value = [];
            },
            onError: () => toast.error('Bulk restore failed.'),
        },
    );
}

// ─── Force Delete ─────────────────────────────────────────────────────────────

const deleteLog = ref<AuditLog | null>(null);
const deleteOpen = ref(false);

function openDelete(log: AuditLog) {
    deleteLog.value = log;
    deleteOpen.value = true;
}
function cancelDelete() {
    deleteOpen.value = false;
    setTimeout(() => {
        deleteLog.value = null;
    }, 200);
}

function confirmDelete() {
    if (!deleteLog.value) return;
    router.delete(
        `/admin/audit-logs/archive/${deleteLog.value.category}/${deleteLog.value.record_id}`,
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Log permanently deleted.');
                deleteOpen.value = false;
            },
            onError: () => toast.error('Failed to delete.'),
        },
    );
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatDate(d: string) {
    return new Date(d).toLocaleString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function context(log: AuditLog) {
    if (log.category === 'authentication') {
        return log.ip_address || log.details || 'No additional details';
    }
    return log.shop_name || 'System activity';
}

const roleBadge: Record<string, string> = {
    super_admin: 'bg-purple-100 text-purple-700',
    owner: 'bg-blue-100 text-blue-700',
    manager: 'bg-sky-100 text-sky-700',
    staff: 'bg-orange-100 text-orange-700',
    user: 'bg-gray-100 text-gray-600',
};
</script>

<template>
    <Head title="Audit & Logs — Archive" />
    <AdminLayout :breadcrumbs="breadcrumbs" title="Audit Log Archive">
        <div class="space-y-6 px-6">
            <DataTable
                v-model:options="options"
                v-model:selected="selected"
                :headers="headers"
                :items="tableItems"
                :total="logs.total"
                :loading="loading"
                item-key="entry_key"
                :row-label="(log) => `audit log ${log.record_id}`"
                selectable
                noun="archived logs"
                :empty-icon="ShieldCheck"
                empty-message="No archived logs found."
                :resettable="hasFilters"
                :min-width="1080"
                hide-rows-per-page
                @reset="resetFilters"
            >
                <template #toolbar>
                    <div class="space-y-3">
                        <div
                            class="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center"
                        >
                            <h2 class="flex items-center gap-2 font-semibold">
                                <ArchiveRestore
                                    class="h-4 w-4 text-muted-foreground"
                                />
                                Archived Audit Logs
                                <span
                                    class="ml-1 text-xs font-normal text-muted-foreground"
                                    >({{ total }} total)</span
                                >
                            </h2>
                            <Button
                                size="sm"
                                variant="outline"
                                @click="router.visit('/admin/audit-logs')"
                            >
                                <ArrowLeft class="mr-1.5 h-4 w-4" /> Back to
                                Logs
                            </Button>
                        </div>
                        <DataTableToolbar
                            v-model:search="search"
                            :status="category"
                            :statuses="categoryOptions"
                            search-placeholder="Search email or name..."
                            search-label="Search archived logs"
                            status-label="Filter by log type"
                            @update:status="setCategory"
                            @submit="applyFilters"
                        />
                    </div>
                </template>

                <template #bulk-actions>
                    <Button
                        size="sm"
                        variant="outline"
                        class="border-green-300 text-green-700 hover:bg-green-50"
                        @click="bulkRestore"
                    >
                        <ArchiveRestore class="mr-1.5 h-4 w-4" />
                        Restore ({{ selected.length }})
                    </Button>
                </template>

                <template #item-name="log">
                    <IdentityCell
                        :name="log.name ?? '—'"
                        :secondary="log.email || '—'"
                    />
                </template>

                <template #item-role="log">
                    <span
                        v-if="log.role"
                        class="rounded-full px-2 py-0.5 text-xs font-medium whitespace-nowrap capitalize"
                        :class="
                            roleBadge[log.role] ?? 'bg-gray-100 text-gray-600'
                        "
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
                    <span class="text-xs text-muted-foreground">
                        <span class="font-medium">{{ log.module }}</span>
                        <span class="block capitalize">{{
                            log.event.replace('_', ' ')
                        }}</span>
                    </span>
                </template>

                <template #item-context="log">
                    <span
                        class="text-xs whitespace-nowrap text-muted-foreground"
                    >
                        {{ context(log) }}
                    </span>
                </template>

                <template #item-archived_at="log">
                    <span
                        class="text-xs whitespace-nowrap text-muted-foreground"
                    >
                        {{ formatDate(log.archived_at) }}
                    </span>
                </template>

                <template #item-actions="log">
                    <RowActions
                        :label="`audit log ${log.record_id}`"
                        :actions="[
                            {
                                label: 'Restore',
                                icon: ArchiveRestore,
                                onSelect: () => restore(log),
                            },
                            {
                                label: 'Delete permanently',
                                icon: Trash2,
                                destructive: true,
                                onSelect: () => openDelete(log),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>

        <!-- Permanent delete confirm -->
        <AlertDialog v-model:open="deleteOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Permanently Delete Log</AlertDialogTitle>
                    <AlertDialogDescription>
                        This log will be permanently removed and cannot be
                        recovered.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <Button variant="outline" @click="cancelDelete"
                        >Cancel</Button
                    >
                    <Button variant="destructive" @click="confirmDelete"
                        >Delete Forever</Button
                    >
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AdminLayout>
</template>
