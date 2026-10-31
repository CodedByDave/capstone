<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
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
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { useDataTable } from '@/composables/useDataTable';
import {
    AlertCircle,
    ArrowLeft,
    Banknote,
    CheckCircle2,
    ChevronDown,
    Download,
    Loader2,
    Lock,
    Pencil,
    RefreshCw,
    ShieldCheck,
    Users,
} from 'lucide-vue-next';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Employee {
    id: number;
    employee_id: string;
    first_name: string;
    last_name: string;
    position: string;
    pay_rate: string | null;
    pay_basis: string;
}

interface PayrollItem {
    id: number;
    public_id: string;
    payroll_id: number;
    employee_id: number;
    employee: Employee;
    basic_salary: string;
    pay_rate: string;
    pay_basis: string;
    days_worked: number;
    days_absent: number;
    days_late: number;
    days_half_day: number;
    deductions: string;
    sss_contribution: string;
    philhealth_contribution: string;
    pagibig_contribution: string;
    withholding_tax: string;
    bonuses: string;
    net_pay: string;
    remarks: string | null;
}

interface Payroll {
    id: number;
    public_id: string;
    period_label: string;
    period_start: string;
    period_end: string;
    status: 'draft' | 'finalized';
    items: PayrollItem[];
    creator: { id: number; name: string } | null;
    created_at: string;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    payroll: Payroll;
}>();

// ─── RBAC ─────────────────────────────────────────────────────────────────────

const page = usePage<AppPageProps>();
const isOwner = computed(() => page.props.auth.user.role === 'owner');
const baseRoute = computed(() => (isOwner.value ? '/shop' : '/staff'));
const isDraft = computed(() => props.payroll.status === 'draft');

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
        default:
            toast(flashToast.message);
    }
});

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Employee Management', href: '/shop/employee' },
    { title: 'Payroll', href: '/shop/payroll' },
    {
        title: props.payroll.period_label,
        href: `/shop/payroll/${props.payroll.public_id}`,
    },
];

// ─── Summary ──────────────────────────────────────────────────────────────────

const totalNetPay = computed(() =>
    props.payroll.items.reduce((sum, i) => sum + parseFloat(i.net_pay), 0),
);

const totalDeductions = computed(() =>
    props.payroll.items.reduce((sum, i) => sum + parseFloat(i.deductions), 0),
);

const totalGovContributions = computed(() =>
    props.payroll.items.reduce(
        (sum, i) =>
            sum +
            parseFloat(i.sss_contribution) +
            parseFloat(i.philhealth_contribution) +
            parseFloat(i.pagibig_contribution) +
            parseFloat(i.withholding_tax),
        0,
    ),
);

const totalBonuses = computed(() =>
    props.payroll.items.reduce((sum, i) => sum + parseFloat(i.bonuses), 0),
);

const totalGross = computed(() =>
    Math.max(
        0,
        totalNetPay.value +
            totalDeductions.value +
            totalGovContributions.value -
            totalBonuses.value,
    ),
);

const noAttendanceCount = computed(
    () =>
        props.payroll.items.filter(
            (i) => i.days_worked === 0 && i.days_half_day === 0,
        ).length,
);

function itemDeductions(item: PayrollItem): number {
    return (
        parseFloat(item.deductions) +
        parseFloat(item.sss_contribution) +
        parseFloat(item.philhealth_contribution) +
        parseFloat(item.pagibig_contribution) +
        parseFloat(item.withholding_tax)
    );
}

function breakdownLines(item: PayrollItem) {
    return [
        { label: 'Attendance', value: item.deductions },
        { label: 'SSS', value: item.sss_contribution },
        { label: 'PhilHealth', value: item.philhealth_contribution },
        { label: 'Pag-IBIG', value: item.pagibig_contribution },
        { label: 'Withholding tax', value: item.withholding_tax },
    ].filter((l) => parseFloat(l.value) > 0);
}

const expanded = ref<Set<number>>(new Set());
function toggleBreakdown(id: number) {
    const next = new Set(expanded.value);
    if (!next.delete(id)) next.add(id);
    expanded.value = next;
}

// ─── Items table ──────────────────────────────────────────────────────────────

const numericColumns = new Set([
    'pay_rate',
    'deductions',
    'sss_contribution',
    'philhealth_contribution',
    'pagibig_contribution',
    'withholding_tax',
    'bonuses',
    'net_pay',
]);

const { search, options, pageItems, total, isFiltered, reset } = useDataTable(
    () => props.payroll.items,
    {
        searchFields: (item) => [
            `${item.employee.first_name} ${item.employee.last_name}`,
            item.employee.employee_id,
            item.employee.position,
        ],
        sortValue: (item, column) => {
            if (column === 'employee_name') {
                return `${item.employee.first_name} ${item.employee.last_name}`;
            }
            if (column === 'deductions') return itemDeductions(item);
            const value = item[column as keyof PayrollItem];

            return numericColumns.has(column)
                ? parseFloat(String(value))
                : value;
        },
        sortBy: 'employee_name',
        rowsPerPage: 20,
    },
);

const headers = computed<DataTableHeader[]>(() => [
    { text: 'Employee', value: 'employee_name', sortable: true, width: 220 },
    { text: 'Pay rate', value: 'pay_rate', sortable: true },
    { text: 'Attendance (days)', value: 'attendance' },
    { text: 'Bonuses', value: 'bonuses', sortable: true, align: 'right' },
    { text: 'Deductions', value: 'deductions', sortable: true, align: 'right' },
    { text: 'Net pay', value: 'net_pay', sortable: true, align: 'right' },
    ...(isDraft.value
        ? [
              {
                  text: 'Actions',
                  value: 'actions',
                  align: 'center' as const,
                  width: 90,
              },
          ]
        : []),
]);

// ─── Edit Item Dialog ─────────────────────────────────────────────────────────

const editItem = ref<PayrollItem | null>(null);
const isEditOpen = ref(false);
const saving = ref(false);

const editForm = ref({
    bonuses: '0',
    remarks: '',
});

function openEdit(item: PayrollItem) {
    editItem.value = item;
    editForm.value = {
        bonuses: item.bonuses,
        remarks: item.remarks ?? '',
    };
    isEditOpen.value = true;
}

function submitEdit() {
    if (!editItem.value) return;
    saving.value = true;
    router.put(
        `${baseRoute.value}/payroll/${editItem.value.public_id}/item`,
        editForm.value,
        {
            preserveScroll: true,
            onSuccess: () => {
                isEditOpen.value = false;
                editItem.value = null;
            },
            onFinish: () => {
                saving.value = false;
            },
        },
    );
}

function recalculate() {
    router.post(
        `${baseRoute.value}/payroll/${props.payroll.public_id}/recalculate`,
        {},
        {
            preserveScroll: true,
        },
    );
}

// ─── Finalize Dialog ──────────────────────────────────────────────────────────

const isFinalizeOpen = ref(false);
const finalizing = ref(false);

function confirmFinalize() {
    finalizing.value = true;
    router.post(
        `${baseRoute.value}/payroll/${props.payroll.public_id}/finalize`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                isFinalizeOpen.value = false;
            },
            onFinish: () => {
                finalizing.value = false;
            },
        },
    );
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatCurrency(val: string | number | null): string {
    const num = typeof val === 'string' ? parseFloat(val) : (val ?? 0);
    return `₱${num.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
}

function formatCutoff(val: string): string {
    return new Date(val + 'T00:00:00').toLocaleDateString('en-PH', {
        month: 'long',
        day: 'numeric',
    });
}

const payBasisLabels: Record<string, string> = {
    monthly: 'Monthly',
    daily: 'Daily',
    hourly: 'Hourly',
    per_shift: 'Per Shift',
    fixed_contract: 'Fixed Contract',
};

// ─── CSV Export ───────────────────────────────────────────────────────────────

function exportCSV() {
    const headers = [
        'Employee ID',
        'Name',
        'Position',
        'Pay Rate',
        'Pay Basis',
        'Monthly Equivalent',
        'Days Worked',
        'Days Absent',
        'Days Late',
        'Half Days',
        'Att. Deductions',
        'SSS',
        'PhilHealth',
        'Pag-IBIG',
        'Withholding Tax',
        'Bonuses',
        'Net Pay',
        'Remarks',
    ];

    const rows = props.payroll.items.map((i) => [
        i.employee.employee_id,
        `${i.employee.first_name} ${i.employee.last_name}`,
        i.employee.position,
        i.pay_rate,
        payBasisLabels[i.pay_basis] ?? i.pay_basis,
        i.basic_salary,
        i.days_worked,
        i.days_absent,
        i.days_late,
        i.days_half_day,
        i.deductions,
        i.sss_contribution,
        i.philhealth_contribution,
        i.pagibig_contribution,
        i.withholding_tax,
        i.bonuses,
        i.net_pay,
        i.remarks ?? '',
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
    link.download = `payroll_${props.payroll.period_label.replace(/\s/g, '_')}.csv`;
    link.click();
    URL.revokeObjectURL(url);
}
</script>

<template>
    <Head :title="`Payroll — ${payroll.period_label}`" />

    <ShopLayout :breadcrumbs="breadcrumbs" :title="payroll.period_label">
        <div class="space-y-6 px-6">
            <!-- ── Header ──────────────────────────────────────── -->
            <div class="space-y-3">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-md text-sm text-muted-foreground hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    @click="router.visit(`${baseRoute}/payroll`)"
                >
                    <ArrowLeft class="h-4 w-4" /> Payroll
                </button>
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                >
                    <div class="space-y-1.5">
                        <h2
                            class="flex flex-wrap items-center gap-3 text-3xl font-bold tracking-tight"
                        >
                            {{ payroll.period_label }}
                            <span
                                class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold"
                                :class="
                                    payroll.status === 'finalized'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-amber-100 text-amber-700'
                                "
                            >
                                {{
                                    payroll.status === 'finalized'
                                        ? 'Finalized'
                                        : 'Draft'
                                }}
                            </span>
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            Created by {{ payroll.creator?.name ?? '—' }} ·
                            {{ payroll.items.length }}
                            {{
                                payroll.items.length === 1
                                    ? 'employee'
                                    : 'employees'
                            }}
                            · Cutoff {{ formatCutoff(payroll.period_end) }}
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <Button variant="outline" @click="exportCSV">
                            <Download class="mr-2 h-4 w-4" /> Export CSV
                        </Button>
                        <Button
                            v-if="isDraft"
                            variant="outline"
                            @click="recalculate"
                        >
                            <RefreshCw class="mr-2 h-4 w-4" /> Recalculate
                        </Button>
                        <Button v-if="isDraft" @click="isFinalizeOpen = true">
                            <Lock class="mr-2 h-4 w-4" /> Finalize
                        </Button>
                    </div>
                </div>
            </div>

            <!-- ── Summary strip ───────────────────────────────── -->
            <dl
                class="grid grid-cols-2 divide-x divide-y border-y sm:grid-cols-5 sm:divide-y-0"
            >
                <div class="col-span-2 space-y-2 p-5 sm:col-span-1">
                    <dt
                        class="flex items-center gap-1.5 text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        <CheckCircle2 class="h-3.5 w-3.5" /> Total net pay
                    </dt>
                    <dd class="font-mono text-3xl font-bold text-primary">
                        {{ formatCurrency(totalNetPay) }}
                    </dd>
                </div>
                <div class="space-y-2 p-5">
                    <dt
                        class="flex items-center gap-1.5 text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        <Users class="h-3.5 w-3.5" /> Employees
                    </dt>
                    <dd class="font-mono text-xl font-bold">
                        {{ payroll.items.length }}
                    </dd>
                </div>
                <div class="space-y-2 p-5">
                    <dt
                        class="flex items-center gap-1.5 text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        <Banknote class="h-3.5 w-3.5" /> Att. deductions
                    </dt>
                    <dd class="font-mono text-xl font-bold">
                        {{ formatCurrency(totalDeductions) }}
                    </dd>
                </div>
                <div class="space-y-2 p-5">
                    <dt
                        class="flex items-center gap-1.5 text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        <ShieldCheck class="h-3.5 w-3.5" /> Gov. contributions
                    </dt>
                    <dd class="font-mono text-xl font-bold">
                        {{ formatCurrency(totalGovContributions) }}
                    </dd>
                </div>
                <div class="space-y-2 p-5">
                    <dt
                        class="flex items-center gap-1.5 text-xs font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        <Banknote class="h-3.5 w-3.5" /> Bonuses
                    </dt>
                    <dd class="font-mono text-xl font-bold">
                        {{ formatCurrency(totalBonuses) }}
                    </dd>
                </div>
            </dl>

            <!-- ── No-attendance notice ────────────────────────── -->
            <div
                v-if="isDraft && noAttendanceCount > 0"
                class="flex items-center gap-3 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900"
                role="status"
            >
                <AlertCircle class="h-4 w-4 shrink-0 text-amber-600" />
                <p>
                    {{ noAttendanceCount }}
                    {{
                        noAttendanceCount === 1
                            ? 'employee has'
                            : 'employees have'
                    }}
                    no worked days recorded, so net pay is
                    {{ formatCurrency(0) }} for now. Figures update until you
                    finalize.
                </p>
            </div>

            <!-- ── Employees table ─────────────────────────────── -->
            <section class="space-y-3">
                <h3 class="text-lg font-semibold">Employees</h3>
                <DataTable
                    v-model:options="options"
                    :headers="headers"
                    :items="pageItems"
                    :total="total"
                    noun="payroll items"
                    :empty-icon="Users"
                    :empty-message="
                        isFiltered
                            ? 'No payroll items match your search.'
                            : 'No payroll items found.'
                    "
                    :resettable="isFiltered"
                    :min-width="900"
                    @reset="reset"
                >
                    <template #toolbar>
                        <DataTableToolbar
                            v-model:search="search"
                            search-placeholder="Search name or position"
                            search-label="Search payroll items"
                        />
                    </template>

                    <template #item-employee_name="item">
                        <IdentityCell
                            :name="`${item.employee.first_name} ${item.employee.last_name}`"
                            :secondary="item.employee.position"
                        />
                    </template>

                    <template #item-pay_rate="item">
                        <p class="font-mono text-sm font-semibold">
                            {{ formatCurrency(item.pay_rate) }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{
                                payBasisLabels[item.pay_basis] ?? item.pay_basis
                            }}
                        </p>
                    </template>

                    <template #item-attendance="item">
                        <div
                            class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs whitespace-nowrap text-muted-foreground"
                        >
                            <span
                                >Worked
                                <b class="text-foreground">{{
                                    item.days_worked
                                }}</b></span
                            >
                            <span
                                >Absent
                                <b
                                    :class="
                                        item.days_absent > 0
                                            ? 'text-amber-600'
                                            : 'text-foreground'
                                    "
                                    >{{ item.days_absent }}</b
                                ></span
                            >
                            <span
                                >Late
                                <b
                                    :class="
                                        item.days_late > 0
                                            ? 'text-amber-600'
                                            : 'text-foreground'
                                    "
                                    >{{ item.days_late }}</b
                                ></span
                            >
                            <span
                                >½ day
                                <b class="text-foreground">{{
                                    item.days_half_day
                                }}</b></span
                            >
                        </div>
                    </template>

                    <template #item-bonuses="item">
                        <span
                            class="font-mono text-sm whitespace-nowrap"
                            :class="
                                parseFloat(item.bonuses) > 0
                                    ? 'text-green-600'
                                    : 'text-muted-foreground'
                            "
                        >
                            {{
                                parseFloat(item.bonuses) > 0
                                    ? `+${formatCurrency(item.bonuses)}`
                                    : '—'
                            }}
                        </span>
                        <p
                            v-if="item.remarks"
                            class="max-w-[140px] truncate text-xs text-muted-foreground"
                        >
                            {{ item.remarks }}
                        </p>
                    </template>

                    <template #item-deductions="item">
                        <div class="text-right">
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 rounded font-mono text-sm whitespace-nowrap text-red-500 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :aria-expanded="expanded.has(item.id)"
                                :aria-label="`Deduction breakdown for ${item.employee.first_name} ${item.employee.last_name}`"
                                @click="toggleBreakdown(item.id)"
                            >
                                {{
                                    itemDeductions(item) > 0
                                        ? `-${formatCurrency(itemDeductions(item))}`
                                        : '—'
                                }}
                                <ChevronDown
                                    class="h-3.5 w-3.5 text-muted-foreground transition-transform"
                                    :class="{
                                        'rotate-180': expanded.has(item.id),
                                    }"
                                />
                            </button>
                            <dl
                                v-if="expanded.has(item.id)"
                                class="mt-2 space-y-1 text-xs"
                            >
                                <div
                                    v-for="line in breakdownLines(item)"
                                    :key="line.label"
                                    class="flex justify-between gap-4"
                                >
                                    <dt class="text-muted-foreground">
                                        {{ line.label }}
                                    </dt>
                                    <dd class="font-mono">
                                        -{{ formatCurrency(line.value) }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </template>

                    <template #item-net_pay="item">
                        <span
                            class="font-mono text-sm font-bold whitespace-nowrap"
                        >
                            {{ formatCurrency(item.net_pay) }}
                        </span>
                    </template>

                    <template #item-actions="item">
                        <RowActions
                            :label="`${item.employee.first_name} ${item.employee.last_name}`"
                            :actions="[
                                {
                                    label: 'Edit bonuses and remarks',
                                    icon: Pencil,
                                    onSelect: () => openEdit(item),
                                },
                            ]"
                        />
                    </template>

                    <template #footer>
                        <span class="font-mono">
                            Gross {{ formatCurrency(totalGross) }} · Deductions
                            {{
                                formatCurrency(
                                    totalDeductions + totalGovContributions,
                                )
                            }}
                            · Net {{ formatCurrency(totalNetPay) }}
                        </span>
                    </template>
                </DataTable>
            </section>
        </div>

        <!-- ── Edit Item Dialog ────────────────────────────────── -->
        <Dialog v-model:open="isEditOpen">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>
                        Edit — {{ editItem?.employee.first_name }}
                        {{ editItem?.employee.last_name }}
                    </DialogTitle>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Bonuses (₱)</label>
                        <Input
                            v-model="editForm.bonuses"
                            type="number"
                            min="0"
                            step="0.01"
                        />
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Remarks</label>
                        <Input
                            v-model="editForm.remarks"
                            placeholder="Optional"
                        />
                    </div>

                    <!-- Gov. contributions breakdown -->
                    <div
                        class="space-y-1.5 rounded-lg border bg-orange-50/60 px-4 py-3 text-xs"
                    >
                        <p class="mb-2 font-semibold text-orange-700">
                            Government Contributions (auto-calculated)
                        </p>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">SSS</span>
                            <span class="font-mono"
                                >-{{
                                    formatCurrency(
                                        editItem?.sss_contribution ?? '0',
                                    )
                                }}</span
                            >
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground"
                                >PhilHealth</span
                            >
                            <span class="font-mono"
                                >-{{
                                    formatCurrency(
                                        editItem?.philhealth_contribution ??
                                            '0',
                                    )
                                }}</span
                            >
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Pag-IBIG</span>
                            <span class="font-mono"
                                >-{{
                                    formatCurrency(
                                        editItem?.pagibig_contribution ?? '0',
                                    )
                                }}</span
                            >
                        </div>
                        <div class="mt-1 flex justify-between border-t pt-1.5">
                            <span class="text-muted-foreground"
                                >Withholding Tax</span
                            >
                            <span class="font-mono"
                                >-{{
                                    formatCurrency(
                                        editItem?.withholding_tax ?? '0',
                                    )
                                }}</span
                            >
                        </div>
                    </div>

                    <div
                        class="rounded-lg border bg-muted/40 px-4 py-3 text-xs text-muted-foreground"
                    >
                        Attendance deductions (tardiness 5% of daily rate) and
                        absent days from schedule are auto-calculated and cannot
                        be changed here. Use Recalculate to refresh all values.
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isEditOpen = false"
                        >Cancel</Button
                    >
                    <Button :disabled="saving" @click="submitEdit">
                        <Loader2
                            v-if="saving"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        {{ saving ? 'Saving...' : 'Save' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- ── Finalize Dialog ─────────────────────────────────── -->
        <AlertDialog v-model:open="isFinalizeOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Finalize Payroll</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to finalize this payroll? Once
                        finalized, no further edits can be made to deductions,
                        bonuses, or remarks.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <Button variant="outline" @click="isFinalizeOpen = false"
                        >Cancel</Button
                    >
                    <Button :disabled="finalizing" @click="confirmFinalize">
                        <Loader2
                            v-if="finalizing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        {{ finalizing ? 'Finalizing...' : 'Finalize' }}
                    </Button>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </ShopLayout>
</template>
