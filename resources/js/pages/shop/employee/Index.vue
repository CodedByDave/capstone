<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
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
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { DataTableOptions } from '@/composables/useDataTable';
import {
    CheckCircle2,
    Download,
    Eye,
    FileWarning,
    Loader2,
    Pencil,
    RefreshCcw,
    Trash,
    Trash2,
    Upload,
    UserPlus,
    Users,
    X,
} from 'lucide-vue-next';

// ─── Types ────────────────────────────────────────────────────────────────────

interface AuditUser {
    id: number;
    name: string;
}

interface EmploymentTypeOption {
    value: string;
    label: string;
}

interface Employee {
    id: number;
    public_id: string;
    user_id: number;
    shop_id: number | null;
    employee_id: string;
    first_name: string;
    last_name: string;
    email: string;
    phone: string | null;
    address: string | null;
    position: string;
    employment_type: string | null;
    pay_rate: string | null;
    pay_basis: string;
    hire_date: string;
    status: 'Active' | 'Inactive';
    created_at: string;
    updated_at: string;
    creator: AuditUser | null;
    updater: AuditUser | null;
}

interface PaginatedEmployees {
    data: Employee[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const { employees, stats, employment_types, pay_bases, filters } = defineProps<{
    employees: PaginatedEmployees;
    stats: {
        total: number;
        active: number;
        new_this_month: number;
        inactive: number;
    };
    employment_types: EmploymentTypeOption[];
    pay_bases: EmploymentTypeOption[];
    filters: Record<string, string | number>;
}>();

// ─── RBAC ─────────────────────────────────────────────────────────────────────

const page = usePage<AppPageProps>();
const user = computed(() => page.props.auth.user);
const isOwner = computed(() => user.value.role === 'owner');
const headers: DataTableHeader[] = [
    { text: 'Employee ID', value: 'employee_id', width: 130, sortable: true },
    { text: 'Name', value: 'full_name', width: 220, sortable: true },
    { text: 'Position', value: 'position', width: 140, sortable: true },
    {
        text: 'Employment type',
        value: 'employment_type',
        width: 150,
        sortable: true,
    },
    { text: 'Status', value: 'status', width: 110, sortable: true },
    { text: 'Actions', value: 'actions', width: 130, align: 'center' },
];
const permissions = computed(() => user.value.permissions ?? {});

function can(module: string, action: string): boolean {
    if (isOwner.value) return true;
    return permissions.value[module]?.includes(action) ?? false;
}

// Route helpers — staff uses /staff/* prefix
const baseRoute = computed(() => (isOwner.value ? '/shop' : '/staff'));

function employeeUrl(id: string, suffix = ''): string {
    return `${baseRoute.value}/employee/${id}${suffix}`;
}

// ─── Flash toast ──────────────────────────────────────────────────────────────

onMounted(() => {
    const flashToast = page.props.toast as
        | { type: string; message: string }
        | undefined;
    if (!flashToast) return;

    switch (flashToast.type) {
        case 'success':
            toast.success(flashToast.message);
            break;
        case 'error':
            toast.error(flashToast.message);
            break;
        case 'warning':
            toast.warning(flashToast.message);
            break;
        default:
            toast(flashToast.message);
    }
});

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Employee Management', href: `${baseRoute.value}/employee` },
];

// ─── Table state ─────────────────────────────────────────────────────────────

const searchQuery = ref(String(filters.search ?? ''));
const statusFilter = ref(String(filters.status ?? 'all') || 'all');
const employmentTypeFilter = ref(
    String(filters.employment_type ?? 'all') || 'all',
);
const tableLoading = ref(false);

const serverOptions = ref<DataTableOptions>({
    page: employees.current_page,
    rowsPerPage: employees.per_page,
    sortBy: String(filters.sort_by ?? 'employee_id'),
    sortType: filters.sort_direction === 'desc' ? 'desc' : 'asc',
});

const statCards = computed(() => [
    { label: 'Total employees', value: stats.total },
    { label: 'Active employees', value: stats.active },
    { label: 'Inactive employees', value: stats.inactive },
    { label: 'New this month', value: stats.new_this_month },
]);

const statusOptions = computed(() => [
    { value: 'all', label: 'All', count: stats.total },
    { value: 'Active', label: 'Active', count: stats.active },
    { value: 'Inactive', label: 'Inactive', count: stats.inactive },
]);

function setStatus(value: string) {
    statusFilter.value = value;
    filterTable();
}

const tableItems = computed(() =>
    employees.data.map((employee) => ({
        ...employee,
        full_name: `${employee.first_name} ${employee.last_name}`,
        creator_name: employee.creator?.name ?? '—',
        updater_name: employee.updater?.name ?? '—',
        actions: '',
    })),
);

function tableQuery() {
    return {
        search: searchQuery.value || undefined,
        status: statusFilter.value === 'all' ? undefined : statusFilter.value,
        employment_type:
            employmentTypeFilter.value === 'all'
                ? undefined
                : employmentTypeFilter.value,
        sort_by: serverOptions.value.sortBy,
        sort_direction: serverOptions.value.sortType,
        page: serverOptions.value.page,
        per_page: serverOptions.value.rowsPerPage,
    };
}

function applyFilters() {
    router.get(`${baseRoute.value}/employee`, tableQuery(), {
        only: ['employees', 'filters'],
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
    employmentTypeFilter.value = 'all';

    const optionsChanged =
        serverOptions.value.page !== 1 ||
        serverOptions.value.sortBy !== 'employee_id' ||
        serverOptions.value.sortType !== 'asc';

    serverOptions.value.page = 1;
    serverOptions.value.sortBy = 'employee_id';
    serverOptions.value.sortType = 'asc';

    if (!optionsChanged) applyFilters();
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

// ─── Archive ──────────────────────────────────────────────────────────────────

const employeeToArchive = ref<Employee | null>(null);
const isArchiveOpen = ref(false);

function openArchiveDialog(emp: Employee) {
    employeeToArchive.value = emp;
    isArchiveOpen.value = true;
}

function cancelArchive() {
    isArchiveOpen.value = false;
    setTimeout(() => {
        employeeToArchive.value = null;
    }, 200);
}

function confirmArchive() {
    if (!employeeToArchive.value) return;
    router.delete(employeeUrl(employeeToArchive.value.public_id), {
        preserveScroll: true,
        onSuccess: () => {
            employeeToArchive.value = null;
            toast.success('Employee archived successfully.');
        },
        onError: () => toast.error('Failed to archive employee.'),
    });
    isArchiveOpen.value = false;
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

const formatPayRate = (val: string | null) =>
    val
        ? `₱${parseFloat(val).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
        : '—';

const formatDate = (val: string) =>
    new Date(val).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });

const employmentTypeLabel = (value: string | null) =>
    value
        ? (employment_types.find((type) => type.value === value)?.label ??
          value)
        : 'Not set';

const payBasisLabel = (value: string) =>
    pay_bases.find((basis) => basis.value === value)?.label ?? value;

// ─── CSV Export ───────────────────────────────────────────────────────────────

function exportCSV() {
    const headers = [
        'employee_id',
        'first_name',
        'last_name',
        'phone',
        'address',
        'position',
        'employment_type',
        'hire_date',
        'pay_rate',
        'pay_basis',
        'status',
        'added_by',
        'last_modified_by',
    ];

    const rows = employees.data.map((emp) => [
        emp.employee_id,
        emp.first_name,
        emp.last_name,
        emp.phone ?? '',
        emp.address ?? '',
        emp.position,
        employmentTypeLabel(emp.employment_type),
        emp.hire_date,
        emp.pay_rate ?? '',
        payBasisLabel(emp.pay_basis),
        emp.status,
        emp.creator?.name ?? '',
        emp.updater?.name ?? '',
    ]);

    const csvContent = [headers, ...rows]
        .map((row) =>
            row
                .map((cell) => `"${String(cell).replace(/"/g, '""')}"`)
                .join(','),
        )
        .join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `employees_${new Date().toISOString().slice(0, 10)}.csv`;
    link.click();
    URL.revokeObjectURL(url);
}

// ─── CSV Import ───────────────────────────────────────────────────────────────

const isImportOpen = ref(false);
const importFile = ref<File | null>(null);
const importErrors = ref<string[]>([]);
const importSuccess = ref(false);
const importing = ref(false);
const isDragging = ref(false);
const fileInputRef = ref<HTMLInputElement | null>(null);

function openImport() {
    importFile.value = null;
    importErrors.value = [];
    importSuccess.value = false;
    isImportOpen.value = true;
}

function onFileChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file?.name.endsWith('.csv')) {
        importFile.value = file;
        importErrors.value = [];
    } else {
        importErrors.value = ['Please select a valid .csv file.'];
        importFile.value = null;
    }
}

function onDrop(e: DragEvent) {
    isDragging.value = false;
    const file = e.dataTransfer?.files?.[0];
    if (file?.name.endsWith('.csv')) {
        importFile.value = file;
        importErrors.value = [];
    } else {
        importErrors.value = ['Please drop a valid .csv file.'];
    }
}

function clearFile() {
    importFile.value = null;
    importErrors.value = [];
    if (fileInputRef.value) fileInputRef.value.value = '';
}

async function submitImport() {
    if (!importFile.value) return;

    importing.value = true;
    importErrors.value = [];
    importSuccess.value = false;

    const formData = new FormData();
    formData.append('csv_file', importFile.value);

    try {
        const response = await fetch('/shop/employee/import', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN':
                    (
                        document.querySelector(
                            'meta[name="csrf-token"]',
                        ) as HTMLMetaElement
                    )?.content ?? '',
                Accept: 'application/json',
            },
            body: formData,
        });

        const data = await response.json();

        if (!response.ok) {
            importErrors.value = Array.isArray(data.errors)
                ? data.errors
                : data.message
                  ? [data.message]
                  : ['An unknown error occurred.'];
            toast.error('Import failed. Please fix the errors and try again.');
        } else {
            importSuccess.value = true;
            importFile.value = null;
            toast.success('Employees imported successfully!');
            setTimeout(() => {
                router.reload({ only: ['employees', 'stats'] });
                isImportOpen.value = false;
                importSuccess.value = false;
            }, 1500);
        }
    } catch {
        importErrors.value = [
            'Failed to connect to the server. Please try again.',
        ];
        toast.error('Failed to connect to the server.');
    } finally {
        importing.value = false;
    }
}
</script>

<template>
    <Head title="Employee Management" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Employee List">
        <div class="space-y-6 px-6">
            <!-- Compact KPIs -->
            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
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

            <!-- Employee table -->
            <DataTable
                v-model:options="serverOptions"
                :headers="headers"
                :items="tableItems"
                :total="employees.total"
                :loading="tableLoading"
                noun="employees"
                :empty-icon="Users"
                empty-message="No employees match the selected filters."
                :min-width="1000"
                @reset="resetFilters"
            >
                <template #toolbar>
                    <DataTableToolbar
                        v-model:search="searchQuery"
                        :status="statusFilter"
                        :statuses="statusOptions"
                        search-placeholder="Search employee, ID, contact..."
                        search-label="Search employees"
                        status-label="Filter by employee status"
                        @update:status="setStatus"
                        @submit="filterTable"
                    >
                        <Select
                            v-model="employmentTypeFilter"
                            @update:model-value="filterTable"
                        >
                            <SelectTrigger
                                class="w-44"
                                aria-label="Filter by employment type"
                            >
                                <SelectValue placeholder="Employment type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all"
                                    >All employment types</SelectItem
                                >
                                <SelectItem
                                    v-for="type in employment_types"
                                    :key="type.value"
                                    :value="type.value"
                                >
                                    {{ type.label }}
                                </SelectItem>
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

                        <template #actions>
                            <Button
                                v-if="can('HRM', 'view')"
                                size="sm"
                                variant="outline"
                                class="bg-green-600 text-white hover:bg-green-700 dark:bg-green-600 dark:text-white dark:hover:bg-green-700"
                                @click="exportCSV"
                            >
                                <Download class="mr-1.5 h-4 w-4" />
                                Export CSV
                            </Button>

                            <Button
                                v-if="isOwner"
                                size="sm"
                                variant="outline"
                                class="bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 dark:text-white dark:hover:bg-blue-700"
                                @click="openImport"
                            >
                                <Upload class="mr-1.5 h-4 w-4" />
                                Import CSV
                            </Button>

                            <Button
                                v-if="isOwner"
                                size="sm"
                                variant="outline"
                                class="bg-red-600 text-white hover:bg-red-700 dark:bg-red-600 dark:text-white dark:hover:bg-red-700"
                                @click="router.visit('/shop/employee/archive')"
                            >
                                <Trash class="mr-1.5 h-4 w-4" />
                                Archive
                            </Button>

                            <Button
                                v-if="can('HRM', 'create')"
                                size="sm"
                                @click="
                                    router.visit(`${baseRoute}/employee/create`)
                                "
                                variant="outline"
                                class="bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 dark:text-white dark:hover:bg-blue-700"
                            >
                                <UserPlus class="mr-1.5 h-4 w-4" />
                                Add Employee
                            </Button>
                        </template>
                    </DataTableToolbar>
                </template>

                <template #item-employee_id="employee">
                    <span class="font-mono text-xs whitespace-nowrap">
                        {{ employee.employee_id }}
                    </span>
                </template>

                <template #item-full_name="employee">
                    <IdentityCell
                        :name="employee.full_name"
                        :secondary="employee.email"
                    />
                </template>

                <template #item-position="employee">
                    <span class="whitespace-nowrap">
                        {{ employee.position }}
                    </span>
                </template>

                <template #item-employment_type="employee">
                    <span
                        class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium whitespace-nowrap text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
                    >
                        {{ employmentTypeLabel(employee.employment_type) }}
                    </span>
                </template>

                <template #item-status="employee">
                    <StatusPill
                        :tone="
                            employee.status === 'Active' ? 'success' : 'neutral'
                        "
                    >
                        {{ employee.status }}
                    </StatusPill>
                </template>

                <template #item-actions="employee">
                    <RowActions
                        :label="employee.full_name"
                        :actions="[
                            {
                                label: 'View',
                                icon: Eye,
                                show: can('HRM', 'view'),
                                onSelect: () =>
                                    router.visit(
                                        employeeUrl(employee.public_id),
                                    ),
                            },
                            {
                                label: 'Edit',
                                icon: Pencil,
                                show: can('HRM', 'update'),
                                onSelect: () =>
                                    router.visit(
                                        employeeUrl(
                                            employee.public_id,
                                            '/edit',
                                        ),
                                    ),
                            },
                            {
                                label: 'Archive',
                                icon: Trash2,
                                destructive: true,
                                show: can('HRM', 'archive'),
                                onSelect: () => openArchiveDialog(employee),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>

        <!-- Import Dialog -->
        <Dialog v-model:open="isImportOpen">
            <DialogContent class="max-w-lg">
                <DialogHeader>
                    <DialogTitle>Import Employees via CSV</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 text-sm">
                    <div
                        class="rounded-lg border bg-muted/40 px-4 py-3 text-xs leading-relaxed text-muted-foreground"
                    >
                        <p class="mb-1 font-semibold text-foreground">
                            Required CSV columns (in order):
                        </p>
                        <code class="block font-mono break-all">
                            employee_id, first_name, last_name, phone, address,
                            position, employment_type, hire_date, pay_rate,
                            pay_basis, status
                        </code>
                        <p class="mt-2">
                            <span class="font-medium">hire_date</span>:
                            <code>YYYY-MM-DD</code>
                            &nbsp;|&nbsp;
                            <span class="font-medium">status</span>:
                            <code>Active</code> or <code>Inactive</code>
                        </p>
                        <p class="mt-1">
                            <span class="font-medium">employment_type</span>:
                            <code>full_time</code>, <code>part_time</code>,
                            <code>contractual</code>, <code>probationary</code>,
                            or <code>seasonal</code>
                        </p>
                        <a
                            href="/shop/employee/import-template"
                            class="mt-2 inline-flex items-center gap-1 font-medium text-primary hover:underline"
                        >
                            <Download class="h-3 w-3" /> Download blank template
                        </a>
                    </div>

                    <div
                        class="relative cursor-pointer rounded-xl border-2 border-dashed transition-colors"
                        :class="
                            isDragging
                                ? 'border-primary bg-primary/5'
                                : 'border-muted-foreground/30 hover:border-primary/50'
                        "
                        @dragover.prevent="isDragging = true"
                        @dragleave="isDragging = false"
                        @drop.prevent="onDrop"
                        @click="fileInputRef?.click()"
                    >
                        <input
                            ref="fileInputRef"
                            type="file"
                            accept=".csv"
                            class="hidden"
                            @change="onFileChange"
                        />
                        <div
                            class="flex flex-col items-center justify-center px-4 py-8 text-center select-none"
                        >
                            <template v-if="!importFile">
                                <Upload
                                    class="mb-2 h-8 w-8 text-muted-foreground"
                                />
                                <p class="font-medium text-foreground">
                                    Click to browse or drag & drop
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Only .csv files are accepted
                                </p>
                            </template>
                            <template v-else>
                                <div
                                    class="flex items-center gap-2 text-green-600"
                                >
                                    <CheckCircle2 class="h-5 w-5" />
                                    <span class="font-medium">{{
                                        importFile.name
                                    }}</span>
                                </div>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    {{ (importFile.size / 1024).toFixed(1) }} KB
                                </p>
                                <button
                                    class="mt-2 flex items-center gap-1 text-xs text-red-500 hover:underline"
                                    @click.stop="clearFile"
                                >
                                    <X class="h-3 w-3" /> Remove file
                                </button>
                            </template>
                        </div>
                    </div>

                    <div
                        v-if="importErrors.length"
                        class="rounded-lg border border-red-200 bg-red-50 px-4 py-3"
                    >
                        <div
                            class="mb-2 flex items-center gap-2 font-semibold text-red-700"
                        >
                            <FileWarning class="h-4 w-4 flex-shrink-0" />
                            Import failed — please fix the following:
                        </div>
                        <ul class="space-y-1">
                            <li
                                v-for="(error, i) in importErrors"
                                :key="i"
                                class="flex items-start gap-1.5 text-xs text-red-600"
                            >
                                <span class="mt-0.5 flex-shrink-0">•</span
                                >{{ error }}
                            </li>
                        </ul>
                    </div>
                </div>

                <DialogFooter class="mt-2">
                    <Button variant="outline" @click="isImportOpen = false"
                        >Cancel</Button
                    >
                    <Button
                        :disabled="!importFile || importing"
                        @click="submitImport"
                    >
                        <Loader2
                            v-if="importing"
                            class="mr-1.5 h-4 w-4 animate-spin"
                        />
                        <Upload v-else class="mr-1.5 h-4 w-4" />
                        {{ importing ? 'Importing...' : 'Import' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Archive Confirm Dialog -->
        <AlertDialog v-model:open="isArchiveOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Remove Employee</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to remove
                        <strong
                            >{{ employeeToArchive?.first_name }}
                            {{ employeeToArchive?.last_name }}</strong
                        >? This will move them to the archive.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <Button variant="outline" @click="cancelArchive"
                        >Cancel</Button
                    >
                    <Button variant="destructive" @click="confirmArchive"
                        >Remove</Button
                    >
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </ShopLayout>
</template>
