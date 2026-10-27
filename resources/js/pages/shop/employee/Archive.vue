<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// shadcn components
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
import { useDataTable } from '@/composables/useDataTable';

// icons
import { Archive, ArchiveRestore, ArrowLeft } from 'lucide-vue-next';

// AlertDialog
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

// ─── Types ────────────────────────────────────────────────────────────────────

interface ArchivedEmployee {
    id: number;
    employee_id_ref: number;
    employee_public_id: string | null;
    employee_id: string;
    first_name: string;
    last_name: string;
    position: string;
    hire_date: string;
    status: 'Active' | 'Inactive' | 'Archived';
    archived_at: string;
    original_created_at: string | null;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const { archived } = defineProps<{ archived: ArchivedEmployee[] }>();

const page = usePage();
const isOwner = computed(() => page.props.auth.user.role === 'owner');
const baseRoute = computed(() => (isOwner.value ? '/shop' : '/staff'));

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Employee Management', href: `${baseRoute.value}/employee` },
    { title: 'Archive', href: `${baseRoute.value}/employee/archive` },
];

// ─── Table ────────────────────────────────────────────────────────────────────

const {
    search: searchQuery,
    status: statusFilter,
    options,
    pageItems,
    total,
    statusCounts,
    isFiltered,
    reset,
} = useDataTable(() => archived, {
    searchFields: (emp) => [
        `${emp.first_name} ${emp.last_name}`,
        emp.employee_id,
        emp.position,
    ],
    status: (emp) => emp.status,
    sortValue: (emp, column) =>
        column === 'name'
            ? `${emp.first_name} ${emp.last_name}`
            : emp[column as keyof ArchivedEmployee],
    sortBy: 'archived_at',
    sortType: 'desc',
});

const statusOptions = computed(() => [
    { value: '', label: 'All', count: archived.length },
    ...(['Active', 'Inactive', 'Archived'] as const)
        .filter((value) => statusCounts.value[value])
        .map((value) => ({
            value,
            label: value,
            count: statusCounts.value[value],
        })),
]);

const headers: DataTableHeader[] = [
    { text: 'Employee ID', value: 'employee_id', sortable: true },
    { text: 'Name', value: 'name', sortable: true },
    { text: 'Position', value: 'position', sortable: true },
    { text: 'Hire date', value: 'hire_date', sortable: true },
    { text: 'Status', value: 'status', sortable: true },
    { text: 'Archived on', value: 'archived_at', sortable: true },
    { text: 'Actions', value: 'actions', align: 'center', width: 100 },
];

function employeeStatusTone(status: string): StatusTone {
    if (status === 'Active') return 'success';
    if (status === 'Inactive') return 'danger';
    return 'warning';
}

// ─── Selected rows ────────────────────────────────────────────────────────────

const selected = ref<Array<string | number>>([]);

// ─── Helpers ──────────────────────────────────────────────────────────────────

const formatDate = (val: string) =>
    new Date(val).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });

// ─── Single restore ───────────────────────────────────────────────────────────

function restore(employeePublicId: string | null) {
    if (!employeePublicId) return;
    router.post(
        `${baseRoute.value}/employee/${employeePublicId}/restore`,
        {},
        { preserveScroll: true },
    );
}

// ─── Bulk restore ─────────────────────────────────────────────────────────────

const showBulkConfirm = ref(false);

function confirmBulk() {
    if (selected.value.length === 0) return;
    showBulkConfirm.value = true;
}

function executeBulkRestore() {
    showBulkConfirm.value = false;
    router.post(
        `${baseRoute.value}/employee/bulk-restore`,
        { ids: selected.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                selected.value = [];
            },
        },
    );
}
</script>

<template>
    <Head title="Archived Employees" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Archived Employees">
        <div class="space-y-6 px-6">
            <!-- Back button -->
            <div>
                <Button
                    type="button"
                    variant="outline"
                    @click="router.visit(`${baseRoute}/employee`)"
                >
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Employees
                </Button>
            </div>

            <DataTable
                v-model:options="options"
                v-model:selected="selected"
                :headers="headers"
                :items="pageItems"
                :total="total"
                item-key="employee_id_ref"
                :row-label="(emp) => `${emp.first_name} ${emp.last_name}`"
                selectable
                noun="archived employees"
                :empty-icon="Archive"
                empty-message="No archived employees found."
                :resettable="isFiltered"
                :min-width="980"
                @reset="reset"
            >
                <template #toolbar>
                    <DataTableToolbar
                        v-model:search="searchQuery"
                        v-model:status="statusFilter"
                        :statuses="statusOptions"
                        search-placeholder="Search archived..."
                        search-label="Search archived employees"
                        status-label="Filter by employee status"
                    >
                        <h2
                            class="order-first flex w-full items-center gap-2 font-semibold sm:w-auto"
                        >
                            <Archive class="h-5 w-5 text-muted-foreground" />
                            Archived Employees
                            <span
                                class="rounded-full bg-muted px-2 py-0.5 text-xs font-normal text-muted-foreground"
                            >
                                {{ archived.length }} total
                            </span>
                        </h2>
                    </DataTableToolbar>
                </template>

                <template #bulk-actions>
                    <Button
                        type="button"
                        size="sm"
                        variant="outline"
                        class="border-green-300 text-green-600 hover:bg-green-50"
                        @click="confirmBulk"
                    >
                        <ArchiveRestore class="mr-1.5 h-4 w-4" />
                        Restore Selected
                    </Button>
                </template>

                <template #item-employee_id="emp">
                    <span class="font-mono text-xs whitespace-nowrap">{{
                        emp.employee_id
                    }}</span>
                </template>

                <template #item-name="emp">
                    <IdentityCell
                        :name="`${emp.first_name} ${emp.last_name}`"
                    />
                </template>

                <template #item-position="emp">
                    <span class="whitespace-nowrap">{{ emp.position }}</span>
                </template>

                <template #item-hire_date="emp">
                    <span class="whitespace-nowrap">{{
                        formatDate(emp.hire_date)
                    }}</span>
                </template>

                <template #item-status="emp">
                    <StatusPill :tone="employeeStatusTone(emp.status)">
                        {{ emp.status }}
                    </StatusPill>
                </template>

                <template #item-archived_at="emp">
                    <span
                        class="text-sm whitespace-nowrap text-muted-foreground"
                    >
                        {{ formatDate(emp.archived_at) }}
                    </span>
                </template>

                <template #item-actions="emp">
                    <RowActions
                        :label="`${emp.first_name} ${emp.last_name}`"
                        :actions="[
                            {
                                label: 'Restore',
                                icon: ArchiveRestore,
                                onSelect: () => restore(emp.employee_public_id),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>

        <!-- Bulk restore confirm dialog -->
        <AlertDialog
            :open="showBulkConfirm"
            @update:open="
                (val) => {
                    if (!val) showBulkConfirm = false;
                }
            "
        >
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle
                        >Restore Selected Employees</AlertDialogTitle
                    >
                    <AlertDialogDescription>
                        Are you sure you want to restore
                        <strong>{{ selected.length }}</strong> employee(s)? They
                        will be moved back to the active employee list.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <Button variant="outline" @click="showBulkConfirm = false"
                        >Cancel</Button
                    >
                    <Button @click="executeBulkRestore">Restore</Button>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </ShopLayout>
</template>
