<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import {
    DataTable,
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
import { Input } from '@/components/ui/input';
import { usePaginatorTable } from '@/composables/useDataTable';
import {
    Banknote,
    CheckCircle2,
    Clock,
    Eye,
    FileText,
    Loader2,
    Plus,
    Settings2,
    Trash2,
} from 'lucide-vue-next';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Payroll {
    id: number;
    public_id: string;
    period_label: string;
    period_start: string;
    period_end: string;
    status: 'draft' | 'finalized';
    items_count: number;
    items_sum_net_pay: string | null;
    creator: { id: number; name: string } | null;
    created_at: string;
}

interface PaginatedPayrolls {
    data: Payroll[];
    current_page: number;
    last_page: number;
    per_page?: number;
    total: number;
    from: number | null;
    to: number | null;
}

interface Stats {
    total_payrolls: number;
    draft: number;
    finalized: number;
    latest_total: number;
}

interface PayrollSettings {
    deduct_sss: boolean;
    deduct_philhealth: boolean;
    deduct_pagibig: boolean;
    deduct_withholding_tax: boolean;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    payrolls: PaginatedPayrolls;
    stats: Stats;
    settings: PayrollSettings;
}>();

// ─── RBAC ─────────────────────────────────────────────────────────────────────

const page = usePage<AppPageProps>();
const isOwner = computed(() => page.props.auth.user.role === 'owner');
const baseRoute = computed(() => (isOwner.value ? '/shop' : '/staff'));

const permissions = computed(() => page.props.auth.user.permissions ?? {});
function hasPermission(module: string, action: string): boolean {
    if (isOwner.value) return true;
    return permissions.value[module]?.includes(action) ?? false;
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
        default:
            toast(flashToast.message);
    }
});

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Employee Management', href: '/shop/employee' },
    { title: 'Payroll', href: '/shop/payroll' },
];

// ─── Generate Dialog ──────────────────────────────────────────────────────────

const isGenerateOpen = ref(false);
const generating = ref(false);
const errors = computed(() => page.props.errors as Record<string, string>);

const form = ref({
    period_label: '',
    period_start: '',
    period_end: '',
});

function openGenerate() {
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth();

    // Default to 1st–15th or 16th–end
    if (now.getDate() <= 15) {
        form.value.period_start = `${year}-${String(month + 1).padStart(2, '0')}-01`;
        form.value.period_end = `${year}-${String(month + 1).padStart(2, '0')}-15`;
    } else {
        form.value.period_start = `${year}-${String(month + 1).padStart(2, '0')}-16`;
        const lastDay = new Date(year, month + 1, 0).getDate();
        form.value.period_end = `${year}-${String(month + 1).padStart(2, '0')}-${lastDay}`;
    }

    form.value.period_label = formatPeriodLabel(
        form.value.period_start,
        form.value.period_end,
    );
    isGenerateOpen.value = true;
}

function formatPeriodLabel(start: string, end: string): string {
    const s = new Date(start + 'T00:00:00');
    const e = new Date(end + 'T00:00:00');
    const monthName = s.toLocaleDateString('en-PH', { month: 'long' });
    return `${monthName} ${s.getDate()}–${e.getDate()}, ${s.getFullYear()}`;
}

function onPeriodChange() {
    if (form.value.period_start && form.value.period_end) {
        form.value.period_label = formatPeriodLabel(
            form.value.period_start,
            form.value.period_end,
        );
    }
}

function submitGenerate() {
    generating.value = true;
    router.post(`${baseRoute.value}/payroll`, form.value, {
        preserveScroll: true,
        onSuccess: () => {
            isGenerateOpen.value = false;
        },
        onFinish: () => {
            generating.value = false;
        },
    });
}

// ─── Delete Dialog ────────────────────────────────────────────────────────────

const payrollToDelete = ref<Payroll | null>(null);
const isDeleteOpen = ref(false);

function openDelete(p: Payroll) {
    payrollToDelete.value = p;
    isDeleteOpen.value = true;
}

function confirmDelete() {
    if (!payrollToDelete.value) return;
    router.delete(
        `${baseRoute.value}/payroll/${payrollToDelete.value.public_id}`,
        {
            preserveScroll: true,
            onSuccess: () => {
                isDeleteOpen.value = false;
                payrollToDelete.value = null;
            },
        },
    );
}

// ─── Settings Dialog ──────────────────────────────────────────────────────────

const isSettingsOpen = ref(false);
const savingSettings = ref(false);

const settingsForm = ref({ ...props.settings });

function openSettings() {
    settingsForm.value = { ...props.settings };
    isSettingsOpen.value = true;
}

function submitSettings() {
    savingSettings.value = true;
    router.put(`${baseRoute.value}/payroll/settings`, settingsForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            isSettingsOpen.value = false;
        },
        onFinish: () => {
            savingSettings.value = false;
        },
    });
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatCurrency(val: string | number | null): string {
    const num = typeof val === 'string' ? parseFloat(val) : (val ?? 0);
    return `₱${num.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
}

function formatDate(val: string): string {
    return new Date(val).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

// ─── Table ────────────────────────────────────────────────────────────────────

const { options, loading } = usePaginatorTable(
    () => props.payrolls,
    ['payrolls'],
);

const headers: DataTableHeader[] = [
    { text: 'Period', value: 'period_label' },
    { text: 'Date range', value: 'period_range' },
    { text: 'Employees', value: 'items_count' },
    { text: 'Total net pay', value: 'items_sum_net_pay' },
    { text: 'Status', value: 'status' },
    { text: 'Created by', value: 'creator' },
    { text: 'Actions', value: 'actions', align: 'center', width: 110 },
];
</script>

<template>
    <Head title="Payroll" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Payroll">
        <div class="space-y-6 px-6">
            <!-- ── Header ──────────────────────────────────────── -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2 class="text-lg font-semibold">Payroll Management</h2>
                    <p class="text-sm text-muted-foreground">
                        Generate and manage employee payroll based on attendance
                        records.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        v-if="hasPermission('HRM', 'update')"
                        variant="outline"
                        @click="openSettings"
                    >
                        <Settings2 class="mr-2 h-4 w-4" /> Settings
                    </Button>
                    <Button @click="openGenerate">
                        <Plus class="mr-2 h-4 w-4" />
                        Generate Payroll
                    </Button>
                </div>
            </div>

            <!-- ── Stats ───────────────────────────────────────── -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <Card>
                    <CardContent class="flex items-center gap-3 pt-4 pb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100"
                        >
                            <FileText class="h-5 w-5 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">
                                {{ stats.total_payrolls }}
                            </p>
                            <p class="text-xs text-muted-foreground">Total</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 pt-4 pb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100"
                        >
                            <Clock class="h-5 w-5 text-amber-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.draft }}</p>
                            <p class="text-xs text-muted-foreground">Draft</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 pt-4 pb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100"
                        >
                            <CheckCircle2 class="h-5 w-5 text-green-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">
                                {{ stats.finalized }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Finalized
                            </p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 pt-4 pb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100"
                        >
                            <Banknote class="h-5 w-5 text-purple-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">
                                {{ formatCurrency(stats.latest_total) }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Latest Total
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- ── Table ───────────────────────────────────────── -->
            <DataTable
                v-model:options="options"
                :headers="headers"
                :items="payrolls.data"
                :total="payrolls.total"
                :loading="loading"
                noun="payroll records"
                :empty-icon="Banknote"
                empty-message="No payroll records yet. Click “Generate Payroll” to create one."
                :resettable="false"
                :min-width="900"
                hide-rows-per-page
            >
                <template #item-period_label="p">
                    <span class="font-medium">{{ p.period_label }}</span>
                </template>

                <template #item-period_range="p">
                    <span
                        class="text-xs whitespace-nowrap text-muted-foreground"
                    >
                        {{ formatDate(p.period_start) }} –
                        {{ formatDate(p.period_end) }}
                    </span>
                </template>

                <template #item-items_sum_net_pay="p">
                    <span class="font-semibold whitespace-nowrap">
                        {{ formatCurrency(p.items_sum_net_pay) }}
                    </span>
                </template>

                <template #item-status="p">
                    <StatusPill
                        :tone="p.status === 'finalized' ? 'success' : 'warning'"
                    >
                        {{ p.status === 'finalized' ? 'Finalized' : 'Draft' }}
                    </StatusPill>
                </template>

                <template #item-creator="p">
                    <span class="text-xs text-muted-foreground">
                        {{ p.creator?.name ?? '—' }}
                    </span>
                </template>

                <template #item-actions="p">
                    <RowActions
                        :label="`payroll for ${p.period_label}`"
                        :actions="[
                            {
                                label: 'View',
                                icon: Eye,
                                onSelect: () =>
                                    router.visit(
                                        `${baseRoute}/payroll/${p.public_id}`,
                                    ),
                            },
                            {
                                label: 'Delete',
                                icon: Trash2,
                                destructive: true,
                                show: p.status === 'draft',
                                onSelect: () => openDelete(p),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>

        <!-- ── Generate Dialog ─────────────────────────────────── -->
        <Dialog v-model:open="isGenerateOpen">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle>Generate Payroll</DialogTitle>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Period Label</label>
                        <Input
                            v-model="form.period_label"
                            placeholder="e.g. April 1-15, 2026"
                            :class="{ 'border-red-500': errors.period_label }"
                        />
                        <p
                            v-if="errors.period_label"
                            class="text-xs text-red-500"
                        >
                            {{ errors.period_label }}
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-sm font-medium"
                                >Start Date</label
                            >
                            <Input
                                v-model="form.period_start"
                                type="date"
                                @change="onPeriodChange"
                                :class="{
                                    'border-red-500': errors.period_start,
                                }"
                            />
                            <p
                                v-if="errors.period_start"
                                class="text-xs text-red-500"
                            >
                                {{ errors.period_start }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-medium">End Date</label>
                            <Input
                                v-model="form.period_end"
                                type="date"
                                @change="onPeriodChange"
                                :class="{ 'border-red-500': errors.period_end }"
                            />
                            <p
                                v-if="errors.period_end"
                                class="text-xs text-red-500"
                            >
                                {{ errors.period_end }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="rounded-lg border bg-muted/40 px-4 py-3 text-xs text-muted-foreground"
                    >
                        Payroll will be auto-computed from attendance records
                        within the selected period. Employees with no attendance
                        data will show 0 days worked.
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isGenerateOpen = false"
                        >Cancel</Button
                    >
                    <Button :disabled="generating" @click="submitGenerate">
                        <Loader2
                            v-if="generating"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        {{ generating ? 'Generating...' : 'Generate' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- ── Settings Dialog ────────────────────────────────── -->
        <Dialog v-model:open="isSettingsOpen">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Payroll Deduction Settings</DialogTitle>
                </DialogHeader>
                <div class="space-y-4">
                    <p class="text-sm text-muted-foreground">
                        Choose which government deductions apply to your
                        employees' payroll.
                    </p>
                    <div class="space-y-3">
                        <label class="flex cursor-pointer items-center gap-3">
                            <input
                                type="checkbox"
                                v-model="settingsForm.deduct_sss"
                                class="h-4 w-4 rounded border-gray-300 accent-primary"
                            />
                            <div>
                                <p class="text-sm font-medium">SSS</p>
                                <p class="text-xs text-muted-foreground">
                                    4.5% of Monthly Salary Credit
                                </p>
                            </div>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3">
                            <input
                                type="checkbox"
                                v-model="settingsForm.deduct_philhealth"
                                class="h-4 w-4 rounded border-gray-300 accent-primary"
                            />
                            <div>
                                <p class="text-sm font-medium">PhilHealth</p>
                                <p class="text-xs text-muted-foreground">
                                    2.5% of monthly-equivalent basic pay
                                </p>
                            </div>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3">
                            <input
                                type="checkbox"
                                v-model="settingsForm.deduct_pagibig"
                                class="h-4 w-4 rounded border-gray-300 accent-primary"
                            />
                            <div>
                                <p class="text-sm font-medium">Pag-IBIG</p>
                                <p class="text-xs text-muted-foreground">
                                    1–2% of monthly-equivalent pay (max ₱100)
                                </p>
                            </div>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3">
                            <input
                                type="checkbox"
                                v-model="settingsForm.deduct_withholding_tax"
                                class="h-4 w-4 rounded border-gray-300 accent-primary"
                            />
                            <div>
                                <p class="text-sm font-medium">
                                    Withholding Tax
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    BIR TRAIN Law semi-monthly brackets
                                </p>
                            </div>
                        </label>
                    </div>
                    <div
                        class="rounded-lg border bg-muted/40 px-4 py-3 text-xs text-muted-foreground"
                    >
                        Changes apply to newly generated or recalculated
                        payrolls only.
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isSettingsOpen = false"
                        >Cancel</Button
                    >
                    <Button :disabled="savingSettings" @click="submitSettings">
                        <Loader2
                            v-if="savingSettings"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        {{ savingSettings ? 'Saving...' : 'Save Settings' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- ── Delete Dialog ───────────────────────────────────── -->
        <AlertDialog v-model:open="isDeleteOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Delete Payroll</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to delete the payroll for
                        <strong>{{ payrollToDelete?.period_label }}</strong
                        >? This action cannot be undone.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <Button variant="outline" @click="isDeleteOpen = false"
                        >Cancel</Button
                    >
                    <Button variant="destructive" @click="confirmDelete"
                        >Delete</Button
                    >
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </ShopLayout>
</template>
