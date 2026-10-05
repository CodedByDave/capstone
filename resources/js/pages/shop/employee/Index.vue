<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
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
import {
    ArrowDown,
    ArrowUp,
    ArrowUpDown,
    CheckCircle2,
    Download,
    Eye,
    FileWarning,
    Loader2,
    Pencil,
    RefreshCcw,
    Search,
    Trash,
    Trash2,
    Upload,
    UserPlus,
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
    branch_name: string | null;
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

type EmployeeSortKey =
    | 'employee_id'
    | 'full_name'
    | 'position'
    | 'employment_type'
    | 'branch_name'
    | 'phone'
    | 'email'
    | 'address'
    | 'hire_date'
    | 'pay_rate'
    | 'pay_basis'
    | 'status'
    | 'creator_name'
    | 'updater_name';

const EasyDataTable = Vue3EasyDataTable;

const sortableHeaders: {
    text: string;
    value: EmployeeSortKey;
    width: number;
}[] = [
    { text: 'Employee ID', value: 'employee_id', width: 145 },
    { text: 'Name', value: 'full_name', width: 190 },
    { text: 'Position', value: 'position', width: 150 },
    { text: 'Employment Type', value: 'employment_type', width: 165 },
    { text: 'Branch', value: 'branch_name', width: 175 },
    { text: 'Phone', value: 'phone', width: 145 },
    { text: 'Email', value: 'email', width: 220 },
    { text: 'Address', value: 'address', width: 240 },
    { text: 'Hire Date', value: 'hire_date', width: 155 },
    { text: 'Pay Rate', value: 'pay_rate', width: 145 },
    { text: 'Pay Basis', value: 'pay_basis', width: 135 },
    { text: 'Status', value: 'status', width: 120 },
    { text: 'Added By', value: 'creator_name', width: 175 },
    { text: 'Modified By', value: 'updater_name', width: 175 },
];

// ─── Props ────────────────────────────────────────────────────────────────────

const { employees, stats, branch_names, employment_types, pay_bases, filters } =
    defineProps<{
        employees: PaginatedEmployees;
        stats: {
            total: number;
            active: number;
            new_this_month: number;
            inactive: number;
        };
        branch_names: string[];
        employment_types: EmploymentTypeOption[];
        pay_bases: EmploymentTypeOption[];
        filters: Record<string, string | number>;
    }>();

// ─── RBAC ─────────────────────────────────────────────────────────────────────

const page = usePage<AppPageProps>();
const user = computed(() => page.props.auth.user);
const isOwner = computed(() => user.value.role === 'owner');
const hasMultipleLocations = computed(() =>
    Boolean((page.props as any).shopCapabilities?.multiple_locations),
);
const headers = computed<Header[]>(() => [
    ...sortableHeaders.filter(
        (header) =>
            hasMultipleLocations.value || header.value !== 'branch_name',
    ),
    { text: 'Actions', value: 'actions', width: 145 },
]);
const permissions = computed(() => user.value.permissions ?? {});

function can(module: string, action: string): boolean {
    if (isOwner.value) return true;
    return permissions.value[module]?.includes(action) ?? false;
}

// Route helpers — staff uses /staff/* prefix
const baseRoute = computed(() => (isOwner.value ? '/shop' : '/staff'));

function employeeUrl(id: number, suffix = ''): string {
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
const branchFilter = ref(String(filters.branch ?? 'all') || 'all');
const employmentTypeFilter = ref(
    String(filters.employment_type ?? 'all') || 'all',
);
const tableLoading = ref(false);

const serverOptions = ref<ServerOptions>({
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
        branch: branchFilter.value === 'all' ? undefined : branchFilter.value,
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
    branchFilter.value = 'all';
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

function toggleSort(key: EmployeeSortKey) {
    if (serverOptions.value.sortBy === key) {
        serverOptions.value.sortType =
            serverOptions.value.sortType === 'asc' ? 'desc' : 'asc';
        return;
    }

    serverOptions.value.sortBy = key;
    serverOptions.value.sortType = 'asc';
}

function sortIcon(key: EmployeeSortKey) {
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
    router.delete(employeeUrl(employeeToArchive.value.id), {
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
        'branch_name',
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
        emp.branch_name ?? '',
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
            <CardContent class="space-y-4">
                <div class="flex flex-wrap items-center gap-2 pt-6">
                    <div class="relative min-w-48 flex-1">
                        <Search
                            class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                        />
                        <Input
                            v-model="searchQuery"
                            placeholder="Search employee, ID, contact..."
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

                    <Select
                        v-model="employmentTypeFilter"
                        @update:model-value="filterTable"
                    >
                        <SelectTrigger class="w-44">
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

                    <Select
                        v-if="hasMultipleLocations"
                        v-model="branchFilter"
                        @update:model-value="filterTable"
                    >
                        <SelectTrigger class="w-44">
                            <SelectValue placeholder="Branch" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All branches</SelectItem>
                            <SelectItem
                                v-for="branch in branch_names"
                                :key="branch"
                                :value="branch"
                            >
                                {{ branch }}
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

                    <Button
                        v-if="can('HRM', 'view')"
                        size="sm"
                        variant="outline"
                        class="border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 hover:text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300"
                        @click="exportCSV"
                    >
                        <Download class="mr-1.5 h-4 w-4" />
                        Export CSV
                    </Button>

                    <Button
                        v-if="isOwner"
                        size="sm"
                        variant="outline"
                        class="border-blue-300 bg-blue-50 text-blue-700 hover:bg-blue-100 hover:text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/40"
                        @click="openImport"
                    >
                        <Upload class="mr-1.5 h-4 w-4" />
                        Import CSV
                    </Button>

                    <Button
                        v-if="isOwner"
                        size="sm"
                        variant="outline"
                        class="border-red-300 bg-red-50 text-red-700 hover:bg-red-100 hover:text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/40"
                        @click="router.visit('/shop/employee/archive')"
                    >
                        <Trash class="mr-1.5 h-4 w-4" />
                        Archive
                    </Button>

                    <Button
                        v-if="can('HRM', 'create')"
                        size="sm"
                        @click="router.visit(`${baseRoute}/employee/create`)"
                        variant="outline"
                        class="border-blue-300 bg-blue-50 text-blue-700 hover:bg-blue-300 hover:text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/40"
                    >
                        <UserPlus class="mr-1.5 h-4 w-4" />
                        Add Employee
                    </Button>
                </div>

                <div class="overflow-hidden rounded-lg border">
                    <EasyDataTable
                        v-model:server-options="serverOptions"
                        class="employee-data-table"
                        :headers="headers"
                        :items="tableItems"
                        :server-items-length="employees.total"
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

                        <template #item-employee_id="employee">
                            <span class="font-mono text-xs whitespace-nowrap">
                                {{ employee.employee_id }}
                            </span>
                        </template>

                        <template #item-full_name="employee">
                            <span class="font-medium whitespace-nowrap">
                                {{ employee.full_name }}
                            </span>
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
                                {{
                                    employmentTypeLabel(
                                        employee.employment_type,
                                    )
                                }}
                            </span>
                        </template>

                        <template #item-branch_name="employee">
                            <span class="whitespace-nowrap">
                                {{ employee.branch_name ?? '—' }}
                            </span>
                        </template>

                        <template #item-phone="employee">
                            <span class="whitespace-nowrap">
                                {{ employee.phone ?? '—' }}
                            </span>
                        </template>

                        <template #item-email="employee">
                            <span class="whitespace-nowrap">
                                {{ employee.email ?? '—' }}
                            </span>
                        </template>

                        <template #item-address="employee">
                            <p
                                class="max-w-60 truncate text-xs text-muted-foreground"
                                :title="employee.address ?? ''"
                            >
                                {{ employee.address ?? '—' }}
                            </p>
                        </template>

                        <template #item-hire_date="employee">
                            <span class="whitespace-nowrap">
                                {{ formatDate(employee.hire_date) }}
                            </span>
                        </template>

                        <template #item-pay_rate="employee">
                            <span class="whitespace-nowrap">
                                {{ formatPayRate(employee.pay_rate) }}
                            </span>
                        </template>

                        <template #item-pay_basis="employee">
                            <span class="whitespace-nowrap">
                                {{ payBasisLabel(employee.pay_basis) }}
                            </span>
                        </template>

                        <template #item-status="employee">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium whitespace-nowrap"
                                :class="
                                    employee.status === 'Active'
                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                                        : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
                                "
                            >
                                {{ employee.status }}
                            </span>
                        </template>

                        <template #item-creator_name="employee">
                            <div>
                                <p
                                    class="text-xs font-medium whitespace-nowrap"
                                >
                                    {{ employee.creator_name }}
                                </p>
                                <p
                                    class="text-xs whitespace-nowrap text-muted-foreground"
                                >
                                    {{ formatDate(employee.created_at) }}
                                </p>
                            </div>
                        </template>

                        <template #item-updater_name="employee">
                            <div>
                                <p
                                    class="text-xs font-medium whitespace-nowrap"
                                >
                                    {{ employee.updater_name }}
                                </p>
                                <p
                                    class="text-xs whitespace-nowrap text-muted-foreground"
                                >
                                    {{ formatDate(employee.updated_at) }}
                                </p>
                            </div>
                        </template>

                        <template #item-actions="employee">
                            <div class="flex items-center justify-center gap-1">
                                <Button
                                    v-if="can('HRM', 'view')"
                                    size="icon"
                                    variant="ghost"
                                    aria-label="View employee"
                                    @click="
                                        router.visit(employeeUrl(employee.id))
                                    "
                                >
                                    <Eye class="h-4 w-4 text-blue-500" />
                                </Button>
                                <Button
                                    v-if="can('HRM', 'update')"
                                    size="icon"
                                    variant="ghost"
                                    aria-label="Edit employee"
                                    @click="
                                        router.visit(
                                            employeeUrl(employee.id, '/edit'),
                                        )
                                    "
                                >
                                    <Pencil class="h-4 w-4 text-slate-500" />
                                </Button>
                                <Button
                                    v-if="can('HRM', 'archive')"
                                    size="icon"
                                    variant="ghost"
                                    aria-label="Archive employee"
                                    @click="openArchiveDialog(employee)"
                                >
                                    <Trash2 class="h-4 w-4 text-amber-500" />
                                </Button>
                                <span
                                    v-if="
                                        !can('HRM', 'view') &&
                                        !can('HRM', 'update') &&
                                        !can('HRM', 'archive')
                                    "
                                    class="text-xs text-muted-foreground"
                                >
                                    —
                                </span>
                            </div>
                        </template>

                        <template #empty-message>
                            <div
                                class="py-10 text-center text-sm text-muted-foreground"
                            >
                                No employees match the selected filters.
                            </div>
                        </template>
                    </EasyDataTable>
                </div>
            </CardContent>
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
                            branch_name, position, employment_type, hire_date,
                            pay_rate, pay_basis, status
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

<style scoped>
.employee-data-table {
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
    min-width: 2050px;
}
</style>
