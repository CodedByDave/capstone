<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import Vue3EasyDataTable, {
    type Header,
    type ServerOptions,
} from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

// shadcn
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
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
// icons
import {
    ArrowDown,
    ArrowUp,
    ArrowUpDown,
    Download,
    Eye,
    Pencil,
    Plus,
    RefreshCcw,
    Search,
    Trash,
    Trash2,
    Users,
} from 'lucide-vue-next';

// ─── Types ────────────────────────────────────────────────────────────────────

interface AuditUser {
    id: number;
    name: string;
}

interface Branch {
    id: number;
    branch_code: string;
    name: string;
    phone: string | null;
    email: string | null;
    manager_name: string | null;
    address: string | null;
    opened_at: string | null;
    status: 'Active' | 'Inactive';
    employees_count: number;
    created_at: string;
    updated_at: string;
    creator: AuditUser | null;
    updater: AuditUser | null;
}

interface PaginatedBranches {
    data: Branch[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

type BranchSortKey =
    | 'branch_code'
    | 'name'
    | 'address'
    | 'phone'
    | 'email'
    | 'manager_name'
    | 'employees_count'
    | 'opened_at'
    | 'status'
    | 'creator_name'
    | 'updater_name';

const EasyDataTable = Vue3EasyDataTable;

const sortableHeaders: { text: string; value: BranchSortKey; width: number }[] =
    [
        { text: 'Branch Code', value: 'branch_code', width: 145 },
        { text: 'Branch Name', value: 'name', width: 180 },
        { text: 'Location', value: 'address', width: 260 },
        { text: 'Contact No.', value: 'phone', width: 155 },
        { text: 'Email', value: 'email', width: 220 },
        { text: 'Manager', value: 'manager_name', width: 175 },
        { text: 'Employees', value: 'employees_count', width: 130 },
        { text: 'Opened', value: 'opened_at', width: 155 },
        { text: 'Status', value: 'status', width: 120 },
        { text: 'Added By', value: 'creator_name', width: 175 },
        { text: 'Modified By', value: 'updater_name', width: 175 },
    ];

const headers: Header[] = [
    ...sortableHeaders,
    { text: 'Actions', value: 'actions', width: 145 },
];

// ─── Props ────────────────────────────────────────────────────────────────────

const { branches, stats, filters } = defineProps<{
    branches: PaginatedBranches;
    stats: { total: number; active: number; inactive: number };
    filters: Record<string, string | number>;
}>();

// ─── Flash ────────────────────────────────────────────────────────────────────

const page = usePage();

onMounted(() => {
    const f = page.props.toast as { type: string; message: string } | undefined;
    if (!f) return;
    if (f.type === 'success') toast.success(f.message);
    else if (f.type === 'error') toast.error(f.message);
    else toast(f.message);
});

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Employee Management', href: '/shop/employee' },
    { title: 'Branch Management', href: '/shop/branch' },
];

// ─── Table state ─────────────────────────────────────────────────────────────

const searchQuery = ref(String(filters.search ?? ''));
const statusFilter = ref(String(filters.status ?? 'all') || 'all');
const tableLoading = ref(false);
const indexUrl = computed(() => page.url.split('?')[0]);

const serverOptions = ref<ServerOptions>({
    page: branches.current_page,
    rowsPerPage: branches.per_page,
    sortBy: String(filters.sort_by ?? 'branch_code'),
    sortType: filters.sort_direction === 'desc' ? 'desc' : 'asc',
});

const statCards = computed(() => [
    { label: 'Total branches', value: stats.total },
    { label: 'Active branches', value: stats.active },
    { label: 'Inactive branches', value: stats.inactive },
]);

const tableItems = computed(() =>
    branches.data.map((branch) => ({
        ...branch,
        creator_name: branch.creator?.name ?? '—',
        updater_name: branch.updater?.name ?? '—',
        actions: '',
    })),
);

function query() {
    return {
        search: searchQuery.value || undefined,
        status: statusFilter.value === 'all' ? undefined : statusFilter.value,
        sort_by: serverOptions.value.sortBy,
        sort_direction: serverOptions.value.sortType,
        page: serverOptions.value.page,
        per_page: serverOptions.value.rowsPerPage,
    };
}

function applyFilters() {
    router.get(indexUrl.value, query(), {
        only: ['branches', 'filters'],
        preserveState: true,
        preserveScroll: true,
        replace: true,
        showProgress: false,
        onStart: () => {
            tableLoading.value = true;
        },
        onFinish: () => {
            tableLoading.value = false;
        },
    });
}

function filterTable() {
    if (serverOptions.value.page !== 1) {
        serverOptions.value.page = 1;
        return;
    }

    applyFilters();
}

function resetFilters() {
    searchQuery.value = '';
    statusFilter.value = 'all';

    const optionsChanged =
        serverOptions.value.page !== 1 ||
        serverOptions.value.sortBy !== 'branch_code' ||
        serverOptions.value.sortType !== 'asc';

    serverOptions.value.page = 1;
    serverOptions.value.sortBy = 'branch_code';
    serverOptions.value.sortType = 'asc';

    if (!optionsChanged) applyFilters();
}

function toggleSort(key: BranchSortKey) {
    if (serverOptions.value.sortBy === key) {
        serverOptions.value.sortType =
            serverOptions.value.sortType === 'asc' ? 'desc' : 'asc';
        return;
    }

    serverOptions.value.sortBy = key;
    serverOptions.value.sortType = 'asc';
}

function sortIcon(key: BranchSortKey) {
    if (serverOptions.value.sortBy !== key) return ArrowUpDown;

    return serverOptions.value.sortType === 'asc' ? ArrowUp : ArrowDown;
}

watch(
    [
        () => serverOptions.value.page,
        () => serverOptions.value.rowsPerPage,
        () => serverOptions.value.sortBy,
        () => serverOptions.value.sortType,
    ],
    () => applyFilters(),
);

// ─── Helpers ──────────────────────────────────────────────────────────────────

const fmtDate = (v: string | null) =>
    v
        ? new Date(v).toLocaleDateString('en-PH', {
              year: 'numeric',
              month: 'long',
              day: 'numeric',
          })
        : '—';

// ─── CSV Export ───────────────────────────────────────────────────────────────

function exportCSV() {
    const headers = [
        'branch_code',
        'name',
        'phone',
        'email',
        'manager_name',
        'address',
        'opened_at',
        'status',
        'employees',
        'added_by',
        'modified_by',
    ];
    const rows = branches.data.map((b) => [
        b.branch_code,
        b.name,
        b.phone ?? '',
        b.email ?? '',
        b.manager_name ?? '',
        b.address ?? '',
        b.opened_at ?? '',
        b.status,
        b.employees_count,
        b.creator?.name ?? '',
        b.updater?.name ?? '',
    ]);
    const csv = [headers, ...rows]
        .map((r) =>
            r.map((c) => `"${String(c).replace(/"/g, '""')}"`).join(','),
        )
        .join('\n');
    const url = URL.createObjectURL(
        new Blob([csv], { type: 'text/csv;charset=utf-8;' }),
    );
    const a = document.createElement('a');
    a.href = url;
    a.download = `branches_${new Date().toISOString().slice(0, 10)}.csv`;
    a.click();
    URL.revokeObjectURL(url);
}

// ─── View Dialog ──────────────────────────────────────────────────────────────

const viewBranch = ref<Branch | null>(null);
const isViewOpen = ref(false);
function openView(b: Branch) {
    viewBranch.value = b;
    isViewOpen.value = true;
}

// ─── Archive Dialog ───────────────────────────────────────────────────────────

const archiveBranch = ref<Branch | null>(null);
const isArchiveOpen = ref(false);

function openArchive(b: Branch) {
    archiveBranch.value = b;
    isArchiveOpen.value = true;
}

function cancelArchive() {
    isArchiveOpen.value = false;
    setTimeout(() => {
        archiveBranch.value = null;
    }, 200);
}

function confirmArchive() {
    if (!archiveBranch.value) return;
    router.delete(`/shop/branch/${archiveBranch.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            archiveBranch.value = null;
        },
        onError: () => {
            toast.error('Failed to archive branch.');
        },
    });
    isArchiveOpen.value = false;
}
</script>

<template>
    <Head title="Branch Management" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Branch Management">
        <div class="space-y-6 px-6">
            <!-- Compact KPIs -->
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                <Card v-for="item in statCards" :key="item.label">
                    <CardContent class="p-4">
                        <p
                            class="mb-3 text-xs font-medium text-muted-foreground"
                        >
                            {{ item.label }}
                        </p>
                        <p class="text-2xl font-bold">{{ item.value }}</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Branch table -->
            <CardContent class="space-y-4">
                <div class="flex flex-wrap items-center gap-2 pt-6">
                    <div class="relative min-w-48 flex-1">
                        <Search
                            class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                        />
                        <Input
                            v-model="searchQuery"
                            placeholder="Search branch, code, contact..."
                            class="pl-8"
                            @keyup.enter="filterTable"
                        />
                    </div>

                    <Select
                        v-model="statusFilter"
                        @update:model-value="filterTable"
                    >
                        <SelectTrigger class="w-36">
                            <SelectValue placeholder="Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All statuses</SelectItem>
                            <SelectItem value="Active">Active</SelectItem>
                            <SelectItem value="Inactive">Inactive</SelectItem>
                        </SelectContent>
                    </Select>

                    <Button
                        size="sm"
                        variant="outline"
                        class="border-slate-300 bg-slate-50 text-slate-700 hover:bg-slate-100 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-300"
                        @click="resetFilters"
                    >
                        <RefreshCcw class="mr-1.5 h-4 w-4" />
                        Reset
                    </Button>

                    <Button
                        size="sm"
                        variant="outline"
                        class="border-red-300 bg-red-50 text-red-700 hover:bg-red-100 hover:text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/40"
                        @click="router.visit('/shop/branch/archive')"
                    >
                        <Trash class="mr-1.5 h-4 w-4" />
                        Archive
                    </Button>

                    <Button
                        size="sm"
                        variant="outline"
                        class="border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 hover:text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300"
                        @click="exportCSV"
                    >
                        <Download class="mr-1.5 h-4 w-4" />
                        Export CSV
                    </Button>

                    <Button
                        size="sm"
                        variant="outline"
                        @click="router.visit('/shop/branch/create')"
                        class="border-blue-300 bg-blue-50 text-blue-700 hover:bg-blue-100 hover:text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/40"
                    >
                        <Plus class="mr-1.5 h-4 w-4" />
                        Add Branch
                    </Button>
                </div>

                <div class="overflow-hidden rounded-lg border">
                    <EasyDataTable
                        v-model:server-options="serverOptions"
                        class="branch-data-table"
                        :headers="headers"
                        :items="tableItems"
                        :server-items-length="branches.total"
                        :loading="tableLoading"
                        :rows-items="[5, 10, 15, 20, 50, 100]"
                        rows-of-page-separator-message="out of"
                        rows-per-page-message="Rows per page:"
                        buttons-pagination
                        alternating
                        must-sort
                    >
                        <template
                            v-for="header in sortableHeaders"
                            :key="header.value"
                            #[`header-${header.value}`]
                        >
                            <button
                                type="button"
                                class="sortable-column"
                                :aria-label="`Sort by ${header.text}`"
                                @click="toggleSort(header.value)"
                            >
                                <span>{{ header.text }}</span>
                                <component
                                    :is="sortIcon(header.value)"
                                    class="h-3.5 w-3.5"
                                    :class="{
                                        'opacity-60':
                                            serverOptions.sortBy !==
                                            header.value,
                                    }"
                                />
                            </button>
                        </template>

                        <template #item-branch_code="branch">
                            <span class="font-mono text-xs whitespace-nowrap">
                                {{ branch.branch_code }}
                            </span>
                        </template>

                        <template #item-name="branch">
                            <span class="font-medium whitespace-nowrap">
                                {{ branch.name }}
                            </span>
                        </template>

                        <template #item-address="branch">
                            <p
                                class="max-w-64 truncate"
                                :title="branch.address ?? ''"
                            >
                                {{ branch.address ?? 'No address' }}
                            </p>
                        </template>

                        <template #item-phone="branch">
                            <span class="whitespace-nowrap">
                                {{ branch.phone ?? '—' }}
                            </span>
                        </template>

                        <template #item-email="branch">
                            <span class="whitespace-nowrap">
                                {{ branch.email ?? '—' }}
                            </span>
                        </template>

                        <template #item-manager_name="branch">
                            <span class="whitespace-nowrap">
                                {{ branch.manager_name ?? '—' }}
                            </span>
                        </template>

                        <template #item-employees_count="branch">
                            <span
                                class="inline-flex items-center gap-1 whitespace-nowrap"
                            >
                                <Users
                                    class="h-3.5 w-3.5 text-muted-foreground"
                                />
                                {{ branch.employees_count }}
                            </span>
                        </template>

                        <template #item-opened_at="branch">
                            <span
                                class="text-xs whitespace-nowrap text-muted-foreground"
                            >
                                {{ fmtDate(branch.opened_at) }}
                            </span>
                        </template>

                        <template #item-status="branch">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium whitespace-nowrap"
                                :class="
                                    branch.status === 'Active'
                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                                        : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
                                "
                            >
                                {{ branch.status }}
                            </span>
                        </template>

                        <template #item-creator_name="branch">
                            <div>
                                <p
                                    class="text-xs font-medium whitespace-nowrap"
                                >
                                    {{ branch.creator_name }}
                                </p>
                                <p
                                    class="text-xs whitespace-nowrap text-muted-foreground"
                                >
                                    {{ fmtDate(branch.created_at) }}
                                </p>
                            </div>
                        </template>

                        <template #item-updater_name="branch">
                            <div>
                                <p
                                    class="text-xs font-medium whitespace-nowrap"
                                >
                                    {{ branch.updater_name }}
                                </p>
                                <p
                                    class="text-xs whitespace-nowrap text-muted-foreground"
                                >
                                    {{ fmtDate(branch.updated_at) }}
                                </p>
                            </div>
                        </template>

                        <template #item-actions="branch">
                            <div class="flex items-center justify-center gap-1">
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    aria-label="View branch"
                                    @click="openView(branch)"
                                >
                                    <Eye class="h-4 w-4 text-blue-500" />
                                </Button>
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    aria-label="Edit branch"
                                    @click="
                                        router.visit(
                                            `/shop/branch/${branch.id}/edit`,
                                        )
                                    "
                                >
                                    <Pencil class="h-4 w-4 text-slate-500" />
                                </Button>
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    aria-label="Archive branch"
                                    @click="openArchive(branch)"
                                >
                                    <Trash2 class="h-4 w-4 text-amber-500" />
                                </Button>
                            </div>
                        </template>

                        <template #empty-message>
                            <div
                                class="py-10 text-center text-sm text-muted-foreground"
                            >
                                No branches match the selected filters.
                            </div>
                        </template>
                    </EasyDataTable>
                </div>
            </CardContent>
        </div>

        <!-- ── View Dialog ────────────────────────────────────────────────── -->
        <Dialog v-model:open="isViewOpen">
            <DialogContent class="max-w-lg">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        {{ viewBranch?.name }}
                        <span
                            class="ml-auto rounded-full px-2 py-0.5 text-xs font-semibold text-white"
                            :class="
                                viewBranch?.status === 'Active'
                                    ? 'bg-green-500'
                                    : 'bg-red-500'
                            "
                        >
                            {{ viewBranch?.status }}
                        </span>
                    </DialogTitle>
                </DialogHeader>
                <div v-if="viewBranch" class="space-y-4 text-sm">
                    <div class="flex items-center gap-2">
                        <span
                            class="rounded bg-muted px-2 py-0.5 font-mono text-xs"
                            >{{ viewBranch.branch_code }}</span
                        >
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-0.5">
                            <p
                                class="text-xs tracking-wide text-muted-foreground uppercase"
                            >
                                Manager
                            </p>
                            <p class="flex items-center gap-1.5 font-medium">
                                {{ viewBranch.manager_name ?? '—' }}
                            </p>
                        </div>
                        <div class="space-y-0.5">
                            <p
                                class="text-xs tracking-wide text-muted-foreground uppercase"
                            >
                                Employees
                            </p>
                            <p class="flex items-center gap-1.5 font-medium">
                                {{ viewBranch.employees_count }}
                            </p>
                        </div>
                        <div class="space-y-0.5">
                            <p
                                class="text-xs tracking-wide text-muted-foreground uppercase"
                            >
                                Phone
                            </p>
                            <p class="flex items-center gap-1.5 font-medium">
                                {{ viewBranch.phone ?? '—' }}
                            </p>
                        </div>
                        <div class="space-y-0.5">
                            <p
                                class="text-xs tracking-wide text-muted-foreground uppercase"
                            >
                                Email
                            </p>
                            <p class="flex items-center gap-1.5 font-medium">
                                {{ viewBranch.email ?? '—' }}
                            </p>
                        </div>
                        <div class="col-span-2 space-y-0.5">
                            <p
                                class="text-xs tracking-wide text-muted-foreground uppercase"
                            >
                                Address
                            </p>
                            <p class="flex items-start gap-1.5 font-medium">
                                {{ viewBranch.address ?? '—' }}
                            </p>
                        </div>
                        <div class="space-y-0.5">
                            <p
                                class="text-xs tracking-wide text-muted-foreground uppercase"
                            >
                                Opened
                            </p>
                            <p class="flex items-center gap-1.5 font-medium">
                                {{ fmtDate(viewBranch.opened_at) }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="grid grid-cols-2 gap-3 border-t pt-3 text-xs text-muted-foreground"
                    >
                        <div>
                            <p class="font-medium text-foreground">Added by</p>
                            <p>{{ viewBranch.creator?.name ?? '—' }}</p>
                            <p>{{ fmtDate(viewBranch.created_at) }}</p>
                        </div>
                        <div>
                            <p class="font-medium text-foreground">
                                Modified by
                            </p>
                            <p>{{ viewBranch.updater?.name ?? '—' }}</p>
                            <p>{{ fmtDate(viewBranch.updated_at) }}</p>
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isViewOpen = false"
                        >Close</Button
                    >
                    <Button
                        @click="
                            router.visit(`/shop/branch/${viewBranch?.id}/edit`);
                            isViewOpen = false;
                        "
                        variant="outline"
                        class="border-blue-300 bg-blue-50 text-blue-700 hover:bg-blue-100 hover:text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/40"
                        >Edit
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- ── Archive Confirm ────────────────────────────────────────────── -->
        <AlertDialog v-model:open="isArchiveOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Archive Branch</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to archive
                        <strong>{{ archiveBranch?.name }}</strong
                        >? It will be moved to the archive and removed from
                        employee dropdowns.
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
    </ShopLayout>
</template>

<style scoped>
.branch-data-table {
    --easy-table-border: 0;
    --easy-table-row-border: 1px solid var(--border);
    --easy-table-header-background-color: var(--muted);
    --easy-table-header-font-color: var(--muted-foreground);
    --easy-table-header-font-size: 12px;
    --easy-table-header-height: 52px;
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

.sortable-column {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: inherit;
    font: inherit;
}

.sortable-column:hover {
    color: var(--foreground);
}

:deep(.vue3-easy-data-table__main) {
    background: var(--background);
}

:deep(.vue3-easy-data-table__main table) {
    min-width: 1900px;
}
</style>
