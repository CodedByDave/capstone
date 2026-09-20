<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
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
import {
    Archive,
    ArchiveRestore,
    Download,
    Eye,
    RefreshCcw,
    Search,
    Trash2,
    Upload,
    User,
    Users,
} from 'lucide-vue-next';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Shop {
    id: number;
    shop_name: string;
}

interface UserItem {
    id: number;
    public_id: string;
    name: string;
    email: string;
    role: string;
    email_verified_at: string | null;
    shop_id: number | null;
    shop: Shop | null;
    created_at: string;
}

interface Paginator {
    data: UserItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

// Keep a script-level reference so the import organizer preserves the
// component used by the template.
const EasyDataTable = Vue3EasyDataTable;

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    users: Paginator;
    stats: {
        total: number;
        owners: number;
        users: number;
        verified: number;
    };
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
    { title: 'Users', href: '/admin/users' },
];

// ─── Filters ──────────────────────────────────────────────────────────────────

const search = ref(props.filters.search ?? '');
const role = ref(props.filters.role ?? 'all');
const verified = ref(props.filters.verified ?? 'all');
const tableLoading = ref(false);

const headers: Header[] = [
    { text: '', value: 'selection', width: 48 },
    { text: 'Name', value: 'name', width: 180, sortable: true },
    { text: 'Email', value: 'email', width: 220, sortable: true },
    { text: 'Role', value: 'role', width: 140 },
    { text: 'Shop', value: 'shop_name', width: 180, sortable: true },
    { text: 'Account', value: 'account', width: 140 },
    { text: 'Joined', value: 'created_at', width: 160, sortable: true },
    { text: 'Actions', value: 'actions', width: 110 },
];

const serverOptions = ref<ServerOptions>({
    page: props.users.current_page,
    rowsPerPage: props.users.per_page,
    sortBy: props.filters.sort_by ?? 'created_at',
    sortType: props.filters.sort_direction === 'asc' ? 'asc' : 'desc',
});

function applyFilters() {
    router.get(
        '/admin/users',
        {
            search: search.value || undefined,
            role: role.value !== 'all' ? role.value : undefined,
            verified: verified.value !== 'all' ? verified.value : undefined,
            sort_by: serverOptions.value.sortBy,
            sort_direction: serverOptions.value.sortType,
            page: serverOptions.value.page,
            per_page: serverOptions.value.rowsPerPage,
        },
        {
            only: ['users', 'filters'],
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
        },
    );
}

function filterTable() {
    if (serverOptions.value.page !== 1) {
        serverOptions.value.page = 1;
        return;
    }

    applyFilters();
}

function resetFilters() {
    search.value = '';
    role.value = 'all';
    verified.value = 'all';
    const sortChanged =
        serverOptions.value.page !== 1 ||
        serverOptions.value.sortBy !== 'created_at' ||
        serverOptions.value.sortType !== 'desc';

    serverOptions.value.page = 1;
    serverOptions.value.sortBy = 'created_at';
    serverOptions.value.sortType = 'desc';

    if (!sortChanged) applyFilters();
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

const importInput = ref<HTMLInputElement | null>(null);
const importing = ref(false);

function exportCsv() {
    window.location.assign('/admin/users/export');
}

function chooseCsvFile() {
    importInput.value?.click();
}

function importCsv(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (!file) return;

    importing.value = true;
    router.post(
        '/admin/users/import',
        { file },
        {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: (responsePage) => {
                const flash = responsePage.props.toast as
                    | { type: string; message: string }
                    | undefined;
                toast.success(
                    flash?.message ?? 'User CSV imported successfully.',
                );
            },
            onError: (errors) => {
                toast.error(
                    typeof errors.file === 'string'
                        ? errors.file
                        : 'Unable to import the CSV file.',
                );
            },
            onFinish: () => {
                importing.value = false;
                input.value = '';
            },
        },
    );
}

// ─── Selection ────────────────────────────────────────────────────────────────

const selected = ref<number[]>([]);
const allSelected = computed(
    () =>
        props.users.data.length > 0 &&
        props.users.data.every((u) => selected.value.includes(u.id)),
);

function toggleAll() {
    allSelected.value
        ? (selected.value = [])
        : (selected.value = props.users.data.map((u) => u.id));
}

function toggleOne(id: number) {
    selected.value.includes(id)
        ? (selected.value = selected.value.filter((i) => i !== id))
        : selected.value.push(id);
}

// ─── Archive single ───────────────────────────────────────────────────────────

const archiveId = ref<number | null>(null);
const archiveName = ref('');
const archiveOpen = ref(false);

function openArchive(user: UserItem) {
    archiveId.value = user.id;
    archiveName.value = user.name;
    archiveOpen.value = true;
}

function cancelArchive() {
    archiveOpen.value = false;
    setTimeout(() => {
        archiveId.value = null;
        archiveName.value = '';
    }, 200);
}

function confirmArchive() {
    if (!archiveId.value) return;
    router.delete(`/admin/users/${archiveId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('User archived.');
            archiveOpen.value = false;
        },
        onError: () => toast.error('Failed to archive user.'),
    });
}

// ─── Bulk archive ─────────────────────────────────────────────────────────────

function bulkArchive() {
    if (!selected.value.length) return;
    router.post(
        '/admin/users/bulk-archive',
        { ids: selected.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(`${selected.value.length} user(s) archived.`);
                selected.value = [];
            },
            onError: () => toast.error('Bulk archive failed.'),
        },
    );
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

const roleBadge: Record<string, string> = {
    super_admin: 'bg-purple-100 text-purple-700',
    owner: 'bg-blue-100 text-blue-700',
    user: 'bg-gray-100 text-gray-600',
};

const tableItems = computed(() =>
    props.users.data.map((user) => ({
        ...user,
        shop_name: user.shop?.shop_name ?? '—',
        account: user.email_verified_at ? 'Verified' : 'Unverified',
        actions: '',
    })),
);
</script>

<template>
    <Head title="Users" />
    <AdminLayout :breadcrumbs="breadcrumbs" title="User Management">
        <div class="space-y-6 px-6">
            <!-- Stats -->
            <div class="grid grid-cols-1 gap-5 md:grid-cols-4">
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Total Users
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
                                Total Owners
                            </p>
                        </div>
                        <p class="text-3xl font-bold text-blue-600">
                            {{ stats.owners.toLocaleString() }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Total Normal Users
                            </p>
                        </div>
                        <p class="text-3xl font-bold text-orange-600">
                            {{ stats.users.toLocaleString() }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <p
                                class="text-xs font-medium tracking-widest text-muted-foreground uppercase"
                            >
                                Total Verified Users
                            </p>
                        </div>
                        <p class="text-3xl font-bold text-green-600">
                            {{ stats.verified.toLocaleString() }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Table card -->
            <CardContent class="space-y-4">
                <div class="flex flex-wrap items-center gap-2 pt-6">
                    <div class="relative min-w-48 flex-1">
                        <Search
                            class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search name or email..."
                            class="pl-8"
                            @keyup.enter="filterTable"
                        />
                    </div>

                    <Button
                        v-if="selected.length > 0"
                        size="sm"
                        variant="outline"
                        class="border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100 hover:text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300 dark:hover:bg-amber-900/40"
                        @click="bulkArchive"
                    >
                        <Archive class="mr-1.5 h-4 w-4" />
                        Archive ({{ selected.length }})
                    </Button>

                    <Button
                        size="sm"
                        variant="outline"
                        class="border-red-300 bg-red-50 text-red-700 hover:bg-red-100 hover:text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/40"
                        @click="router.visit('/admin/users/archive')"
                    >
                        <ArchiveRestore class="mr-1.5 h-4 w-4" />
                        Archive
                    </Button>

                    <Button
                        size="sm"
                        variant="outline"
                        class="border-slate-300 bg-slate-50 text-slate-700 hover:bg-slate-100 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-300 dark:hover:bg-slate-800"
                        @click="resetFilters"
                    >
                        <RefreshCcw class="mr-1.5 h-4 w-4" />
                        Reset
                    </Button>

                    <Button
                        size="sm"
                        variant="outline"
                        class="border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 hover:text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300 dark:hover:bg-emerald-900/40"
                        @click="exportCsv"
                    >
                        <Download class="mr-1.5 h-4 w-4" />
                        Export CSV
                    </Button>

                    <Button
                        size="sm"
                        variant="outline"
                        class="border-blue-300 bg-blue-50 text-blue-700 hover:bg-blue-100 hover:text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/40"
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

                <!-- Table -->
                <div class="overflow-hidden rounded-lg border">
                    <EasyDataTable
                        v-model:server-options="serverOptions"
                        class="user-data-table"
                        :headers="headers"
                        :items="tableItems"
                        :server-items-length="users.total"
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
                                aria-label="Select all users on this page"
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
                                    aria-label="Filter by role"
                                    @click.stop
                                    @change="filterTable"
                                >
                                    <option value="all">All roles</option>
                                    <option value="owner">Owner</option>
                                    <option value="user">User</option>
                                </select>
                            </div>
                        </template>

                        <template #header-account="{ text }">
                            <div class="column-filter">
                                <span>{{ text }}</span>
                                <select
                                    v-model="verified"
                                    class="column-filter-input"
                                    aria-label="Filter by account status"
                                    @click.stop
                                    @change="filterTable"
                                >
                                    <option value="all">All accounts</option>
                                    <option value="yes">Verified</option>
                                    <option value="no">Unverified</option>
                                </select>
                            </div>
                        </template>

                        <template #item-selection="u">
                            <input
                                type="checkbox"
                                :checked="selected.includes(u.id)"
                                :aria-label="`Select ${u.name}`"
                                class="rounded"
                                @change="toggleOne(u.id)"
                            />
                        </template>

                        <template #item-name="u">
                            <span class="font-medium whitespace-nowrap">{{
                                u.name
                            }}</span>
                        </template>

                        <template #item-email="u">
                            <span class="text-xs text-muted-foreground">{{
                                u.email
                            }}</span>
                        </template>

                        <template #item-role="u">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                                :class="
                                    roleBadge[u.role] ??
                                    'bg-gray-100 text-gray-600'
                                "
                            >
                                {{ u.role.replace('_', ' ') }}
                            </span>
                        </template>

                        <template #item-shop_name="u">
                            <span class="text-xs text-muted-foreground">{{
                                u.shop_name
                            }}</span>
                        </template>

                        <template #item-account="u">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="
                                    u.email_verified_at
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-red-100 text-red-600'
                                "
                            >
                                {{ u.account }}
                            </span>
                        </template>

                        <template #item-created_at="u">
                            <span
                                class="text-xs whitespace-nowrap text-muted-foreground"
                            >
                                {{ formatDate(u.created_at) }}
                            </span>
                        </template>

                        <template #item-actions="u">
                            <div class="flex items-center justify-center gap-1">
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    aria-label="View user"
                                    @click="
                                        router.visit(
                                            `/admin/users/${u.public_id}`,
                                        )
                                    "
                                >
                                    <Eye class="h-4 w-4 text-blue-500" />
                                </Button>
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    aria-label="Archive user"
                                    @click="openArchive(u)"
                                >
                                    <Trash2 class="h-4 w-4 text-amber-500" />
                                </Button>
                            </div>
                        </template>

                        <template #empty-message>
                            <div
                                class="py-10 text-center text-sm text-muted-foreground"
                            >
                                <Users
                                    class="mx-auto mb-2 h-10 w-10 opacity-20"
                                />
                                No users found.
                            </div>
                        </template>
                    </EasyDataTable>
                </div>
            </CardContent>
        </div>

        <!-- Archive confirm -->
        <AlertDialog v-model:open="archiveOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Archive User</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to archive
                        <strong>{{ archiveName }}</strong
                        >? They will lose access but can be restored later.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <Button variant="outline" @click="cancelArchive"
                        >Cancel</Button
                    >
                    <Button variant="outline" @click="confirmArchive" class="border-red-300 bg-red-50 text-red-700 hover:bg-red-100 hover:text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/40"
                        >Archive</Button
                    >
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AdminLayout>
</template>

<style scoped>
.user-data-table {
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
    --easy-table-body-row-height: 58px;
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
    min-width: 90px;
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
    min-width: 1120px;
}
</style>
