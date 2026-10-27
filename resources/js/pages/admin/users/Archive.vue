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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { usePaginatorTable } from '@/composables/useDataTable';
import { ArchiveRestore, ArrowLeft, Trash2, Users } from 'lucide-vue-next';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Shop {
    id: number;
    shop_name: string;
}
interface UserItem {
    id: number;
    name: string;
    email: string;
    role: string;
    email_verified_at: string | null;
    shop: Shop | null;
    created_at: string;
    deleted_at: string;
}

interface Paginator {
    data: UserItem[];
    current_page: number;
    last_page: number;
    per_page?: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    users: Paginator;
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
    { title: 'Users', href: '/admin/users' },
    { title: 'Archive', href: '/admin/users/archive' },
];

// ─── Filters ──────────────────────────────────────────────────────────────────

const search = ref(props.filters.search ?? '');
const role = ref(props.filters.role ?? 'all');

function applyFilters() {
    router.get(
        '/admin/users/archive',
        {
            search: search.value || undefined,
            role: role.value !== 'all' ? role.value : undefined,
        },
        { preserveState: true, replace: true },
    );
}

function resetFilters() {
    search.value = '';
    role.value = 'all';
    applyFilters();
}

const hasFilters = computed(
    () => Boolean(props.filters.search) || Boolean(props.filters.role),
);

// ─── Table ────────────────────────────────────────────────────────────────────

const { options, loading } = usePaginatorTable(() => props.users, ['users']);

const headers: DataTableHeader[] = [
    { text: 'Name', value: 'name', width: 200 },
    { text: 'Email', value: 'email' },
    { text: 'Role', value: 'role' },
    { text: 'Shop', value: 'shop' },
    { text: 'Joined', value: 'created_at' },
    { text: 'Archived at', value: 'deleted_at' },
    { text: 'Actions', value: 'actions', align: 'center', width: 120 },
];

// ─── Selection ────────────────────────────────────────────────────────────────

const selected = ref<Array<string | number>>([]);

// ─── Restore ──────────────────────────────────────────────────────────────────

function restore(id: number) {
    router.post(
        `/admin/users/archive/${id}/restore`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => toast.success('User restored.'),
            onError: () => toast.error('Failed to restore.'),
        },
    );
}

function bulkRestore() {
    if (!selected.value.length) return;
    router.post(
        '/admin/users/archive/bulk-restore',
        { ids: selected.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(`${selected.value.length} user(s) restored.`);
                selected.value = [];
            },
            onError: () => toast.error('Bulk restore failed.'),
        },
    );
}

// ─── Force Delete ─────────────────────────────────────────────────────────────

const deleteId = ref<number | null>(null);
const deleteName = ref('');
const deleteOpen = ref(false);

function openDelete(u: UserItem) {
    deleteId.value = u.id;
    deleteName.value = u.name;
    deleteOpen.value = true;
}

function cancelDelete() {
    deleteOpen.value = false;
    setTimeout(() => {
        deleteId.value = null;
        deleteName.value = '';
    }, 200);
}

function confirmDelete() {
    if (!deleteId.value) return;
    router.delete(`/admin/users/archive/${deleteId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('User permanently deleted.');
            deleteOpen.value = false;
        },
        onError: () => toast.error('Failed to delete.'),
    });
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
    manager: 'bg-sky-100 text-sky-700',
    staff: 'bg-orange-100 text-orange-700',
    user: 'bg-gray-100 text-gray-600',
};
</script>

<template>
    <Head title="Users — Archive" />
    <AdminLayout :breadcrumbs="breadcrumbs" title="Users Archive">
        <div class="space-y-6 px-6">
            <DataTable
                v-model:options="options"
                v-model:selected="selected"
                :headers="headers"
                :items="users.data"
                :total="users.total"
                :loading="loading"
                :row-label="(u) => u.name"
                selectable
                noun="archived users"
                :empty-icon="Users"
                empty-message="No archived users found."
                :resettable="hasFilters"
                :min-width="1000"
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
                                Archived Users
                                <span
                                    class="ml-1 text-xs font-normal text-muted-foreground"
                                    >({{ total }} total)</span
                                >
                            </h2>
                            <Button
                                size="sm"
                                variant="outline"
                                @click="router.visit('/admin/users')"
                            >
                                <ArrowLeft class="mr-1.5 h-4 w-4" /> Back to
                                Users
                            </Button>
                        </div>
                        <DataTableToolbar
                            v-model:search="search"
                            search-placeholder="Search name or email..."
                            search-label="Search archived users"
                            @submit="applyFilters"
                        >
                            <Select
                                v-model="role"
                                @update:model-value="applyFilters"
                            >
                                <SelectTrigger
                                    class="w-36"
                                    aria-label="Filter by role"
                                >
                                    <SelectValue placeholder="Role" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all"
                                        >All Roles</SelectItem
                                    >
                                    <SelectItem value="owner">Owner</SelectItem>
                                    <SelectItem value="manager"
                                        >Manager</SelectItem
                                    >
                                    <SelectItem value="staff">Staff</SelectItem>
                                    <SelectItem value="user"
                                        >Customer</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                        </DataTableToolbar>
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

                <template #item-name="u">
                    <IdentityCell :name="u.name" />
                </template>

                <template #item-email="u">
                    <span class="text-xs text-muted-foreground">{{
                        u.email
                    }}</span>
                </template>

                <template #item-role="u">
                    <span
                        class="rounded-full px-2 py-0.5 text-xs font-medium whitespace-nowrap capitalize"
                        :class="
                            roleBadge[u.role] ?? 'bg-gray-100 text-gray-600'
                        "
                    >
                        {{ u.role.replace('_', ' ') }}
                    </span>
                </template>

                <template #item-shop="u">
                    <span class="text-xs text-muted-foreground">
                        {{ u.shop?.shop_name ?? '—' }}
                    </span>
                </template>

                <template #item-created_at="u">
                    <span
                        class="text-xs whitespace-nowrap text-muted-foreground"
                    >
                        {{ formatDate(u.created_at) }}
                    </span>
                </template>

                <template #item-deleted_at="u">
                    <span
                        class="text-xs whitespace-nowrap text-muted-foreground"
                    >
                        {{ formatDate(u.deleted_at) }}
                    </span>
                </template>

                <template #item-actions="u">
                    <RowActions
                        :label="u.name"
                        :actions="[
                            {
                                label: 'Restore',
                                icon: ArchiveRestore,
                                onSelect: () => restore(u.id),
                            },
                            {
                                label: 'Delete permanently',
                                icon: Trash2,
                                destructive: true,
                                onSelect: () => openDelete(u),
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
                    <AlertDialogTitle>Permanently Delete User</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to permanently delete
                        <strong>{{ deleteName }}</strong
                        >? This cannot be undone.
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
