<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    ArchiveRestore,
    ArrowLeft,
    Search,
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

// ─── Selection ────────────────────────────────────────────────────────────────

const selected = ref<string[]>([]);
const entryKey = (log: AuditLog) => `${log.category}:${log.record_id}`;
const allSelected = computed(
    () =>
        props.logs.data.length > 0 &&
        props.logs.data.every((log) => selected.value.includes(entryKey(log))),
);

function toggleAll() {
    allSelected.value
        ? (selected.value = [])
        : (selected.value = props.logs.data.map(entryKey));
}

function toggleOne(log: AuditLog) {
    const key = entryKey(log);
    selected.value.includes(key)
        ? (selected.value = selected.value.filter((item) => item !== key))
        : selected.value.push(key);
}

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
                const [category, id] = key.split(':');
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
            <Card>
                <CardHeader class="pb-3">
                    <div
                        class="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center"
                    >
                        <CardTitle class="flex items-center gap-2">
                            <ArchiveRestore
                                class="h-4 w-4 text-muted-foreground"
                            />
                            Archived Audit Logs
                            <span
                                class="ml-1 text-xs font-normal text-muted-foreground"
                                >({{ total }} total)</span
                            >
                        </CardTitle>
                        <div class="flex flex-wrap gap-2">
                            <Button
                                v-if="selected.length > 0"
                                size="sm"
                                variant="outline"
                                class="border-green-300 text-green-700 hover:bg-green-50"
                                @click="bulkRestore"
                            >
                                <ArchiveRestore class="mr-1.5 h-4 w-4" />
                                Restore ({{ selected.length }})
                            </Button>
                            <Button
                                size="sm"
                                variant="outline"
                                @click="router.visit('/admin/audit-logs')"
                            >
                                <ArrowLeft class="mr-1.5 h-4 w-4" /> Back to
                                Logs
                            </Button>
                        </div>
                    </div>
                </CardHeader>

                <CardContent class="space-y-4">
                    <!-- Filters -->
                    <div class="flex flex-wrap gap-2">
                        <div class="relative min-w-48 flex-1">
                            <Search
                                class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                            />
                            <Input
                                v-model="search"
                                placeholder="Search email or name..."
                                class="pl-8"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <Select
                            v-model="category"
                            @update:model-value="applyFilters"
                        >
                            <SelectTrigger class="w-36">
                                <SelectValue placeholder="Type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Types</SelectItem>
                                <SelectItem value="authentication"
                                    >Authentication</SelectItem
                                >
                                <SelectItem value="activity"
                                    >User Activity</SelectItem
                                >
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Table -->
                    <div class="overflow-hidden rounded-lg border">
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="border-b bg-muted/40 text-xs text-muted-foreground"
                                >
                                    <th class="w-8 px-4 py-3">
                                        <input
                                            type="checkbox"
                                            :checked="allSelected"
                                            @change="toggleAll"
                                            class="rounded"
                                        />
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        User
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Role
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Type
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Module / Event
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Context
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Archived At
                                    </th>
                                    <th
                                        class="px-4 py-3 text-center font-medium"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="log in logs.data"
                                    :key="entryKey(log)"
                                    class="border-b transition-colors last:border-0 hover:bg-muted/20"
                                    :class="{
                                        'bg-muted/10': selected.includes(
                                            entryKey(log),
                                        ),
                                    }"
                                >
                                    <td class="px-4 py-3">
                                        <input
                                            type="checkbox"
                                            :checked="
                                                selected.includes(entryKey(log))
                                            "
                                            @change="toggleOne(log)"
                                            class="rounded"
                                        />
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium">
                                            {{ log.name ?? '—' }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ log.email || '—' }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            v-if="log.role"
                                            class="rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                                            :class="
                                                roleBadge[log.role] ??
                                                'bg-gray-100 text-gray-600'
                                            "
                                        >
                                            {{ log.role.replace('_', ' ') }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-xs text-muted-foreground"
                                            >—</span
                                        >
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                                            :class="
                                                log.category ===
                                                'authentication'
                                                    ? 'bg-indigo-100 text-indigo-700'
                                                    : 'bg-emerald-100 text-emerald-700'
                                            "
                                        >
                                            {{
                                                log.category ===
                                                'authentication'
                                                    ? 'Authentication'
                                                    : 'User Activity'
                                            }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-4 py-3 text-xs text-muted-foreground"
                                    >
                                        <span class="font-medium">{{
                                            log.module
                                        }}</span>
                                        <span class="block capitalize">{{
                                            log.event.replace('_', ' ')
                                        }}</span>
                                    </td>
                                    <td
                                        class="px-4 py-3 text-xs whitespace-nowrap text-muted-foreground"
                                    >
                                        {{ context(log) }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-xs whitespace-nowrap text-muted-foreground"
                                    >
                                        {{ formatDate(log.archived_at) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div
                                            class="flex items-center justify-center gap-1"
                                        >
                                            <Button
                                                size="icon"
                                                variant="ghost"
                                                @click="restore(log)"
                                            >
                                                <ArchiveRestore
                                                    class="h-4 w-4 text-green-500"
                                                />
                                            </Button>
                                            <Button
                                                size="icon"
                                                variant="ghost"
                                                @click="openDelete(log)"
                                            >
                                                <Trash2
                                                    class="h-4 w-4 text-red-400"
                                                />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="logs.data.length === 0">
                                    <td
                                        colspan="8"
                                        class="px-4 py-12 text-center text-sm text-muted-foreground"
                                    >
                                        <ShieldCheck
                                            class="mx-auto mb-2 h-10 w-10 opacity-20"
                                        />
                                        No archived logs found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="logs.last_page > 1"
                        class="flex items-center justify-between pt-2"
                    >
                        <p class="text-xs text-muted-foreground">
                            Showing {{ logs.data.length }} of
                            {{ logs.total }} logs
                        </p>
                        <div class="flex gap-1">
                            <Button
                                v-for="link in logs.links"
                                :key="link.label"
                                size="sm"
                                :variant="link.active ? 'default' : 'outline'"
                                :disabled="!link.url"
                                class="h-7 min-w-7 text-xs"
                                @click="link.url && router.visit(link.url)"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>
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
