<script setup lang="ts">
import ActionsMenu from '@/components/shop/ActionsMenu.vue';
import PageHeader from '@/components/shop/PageHeader.vue';
import { useUndoToast } from '@/composables/useUndoToast';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import {
    BulkActionButton,
    DataTable,
    KpiTiles,
    RowActions,
    StatusPill,
    type DataTableHeader,
} from '@/components/data-table';
import { Button } from '@/components/ui/button';
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
    Archive,
    Banknote,
    CheckCircle2,
    Clock,
    Download,
    Eye,
    FileText,
    Loader2,
    Plus,
    RotateCcw,
    Settings2,
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
    showArchived: boolean;
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

// ─── Archive ──────────────────────────────────────────────────────────────────

const selected = ref<Array<string | number>>([]);

const canArchive = computed(() => hasPermission('Staff Management', 'archive'));

function toggleArchived() {
    router.get(
        `${baseRoute.value}/payroll`,
        { archived: props.showArchived ? undefined : 1 },
        { preserveState: false, replace: true },
    );
}

function restorePayroll(p: Payroll) {
    router.post(
        `${baseRoute.value}/payroll/${p.public_id}/restore`,
        {},
        { preserveScroll: true },
    );
}

const { undoable } = useUndoToast();

// Archiving can be undone, so it happens at once and a toast offers Undo for a few seconds.
function archivePayroll(p: Payroll) {
    router.delete(`${baseRoute.value}/payroll/${p.public_id}`, {
        preserveScroll: true,
        onSuccess: () =>
            undoable(`Payroll ${p.period_label} archived.`, () =>
                router.post(
                    `${baseRoute.value}/payroll/${p.public_id}/restore`,
                    {},
                    { preserveScroll: true },
                ),
            ),
    });
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
    { text: 'Period', value: 'period_label', sortable: true },
    { text: 'Date range', value: 'period_range', sortable: true },
    { text: 'Employees', value: 'items_count', sortable: true },
    { text: 'Total net pay', value: 'items_sum_net_pay', sortable: true },
    { text: 'Status', value: 'status', sortable: true },
    { text: 'Created by', value: 'creator', sortable: true },
    { text: 'Actions', value: 'actions', align: 'center', width: 110 },
];
</script>

<template>
    <Head :title="showArchived ? 'Archived Payroll' : 'Payroll'" />

    <ShopLayout
        :breadcrumbs="breadcrumbs"
        :title="showArchived ? 'Archived Payroll' : 'Payroll'"
    >
        <div class="space-y-6 px-6">
            <!-- ── Header ──────────────────────────────────────── -->
            <PageHeader
                title="Payroll Management"
                description="Generate and manage employee payroll based on attendance records."
            >
                <Button
                    v-if="showArchived && canArchive"
                    variant="back"
                    class="h-11"
                    @click="toggleArchived"
                >
                    <Archive class="mr-2 h-4 w-4" />
                    Back to Active
                </Button>
                <ActionsMenu
                    v-if="!showArchived"
                    :items="[
                        {
                            label: 'Export CSV',
                            icon: Download,
                            href: `${baseRoute}/export/payroll`,
                        },
                        {
                            label: 'Payroll settings',
                            icon: Settings2,
                            onSelect: openSettings,
                            hidden: !hasPermission('Staff Management', 'update'),
                        },
                        {
                            label: 'Archived payrolls',
                            icon: Archive,
                            onSelect: toggleArchived,
                            separator: true,
                            hidden: !canArchive,
                        },
                    ]"
                />
                <Button v-if="!showArchived" class="h-11" @click="openGenerate">
                    <Plus class="mr-2 h-4 w-4" />
                    Generate Payroll
                </Button>
            </PageHeader>

            <!-- ── Stats ───────────────────────────────────────── -->
            <KpiTiles
                :tiles="[
                    {
                        label: 'Total',
                        value: stats.total_payrolls,
                        icon: FileText,
                    },
                    { label: 'Draft', value: stats.draft, icon: Clock },
                    {
                        label: 'Finalized',
                        value: stats.finalized,
                        icon: CheckCircle2,
                    },
                    {
                        label: 'Latest Total',
                        value: formatCurrency(stats.latest_total),
                        icon: Banknote,
                    },
                ]"
            />

            <!-- ── Archived Banner ─────────────────────────────── -->
            <div
                v-if="showArchived"
                class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3"
            >
                <Archive class="h-5 w-5 shrink-0 text-amber-600" />
                <div>
                    <p class="text-sm font-semibold text-amber-800">
                        Viewing Archived Payroll
                    </p>
                    <p class="text-xs text-amber-700">
                        Archived payroll records are kept with their employee
                        pay lines. Restore one to view or edit it again.
                    </p>
                </div>
            </div>

            <!-- ── Table ───────────────────────────────────────── -->
            <DataTable
                v-model:options="options"
                v-model:selected="selected"
                item-key="public_id"
                :headers="headers"
                :items="payrolls.data"
                :total="payrolls.total"
                :loading="loading"
                noun="payroll records"
                :empty-icon="showArchived ? Archive : Banknote"
                :empty-message="
                    showArchived
                        ? 'No archived payroll records.'
                        : 'No payroll records yet. Click “Generate Payroll” to create one.'
                "
                :resettable="false"
                :min-width="900"
                hide-rows-per-page
            >
                <template #bulk-actions>
                    <BulkActionButton
                        v-if="canArchive"
                        resource="payroll"
                        :action="showArchived ? 'restore' : 'archive'"
                        :ids="selected"
                        noun="payroll records"
                        @done="selected = []"
                    />
                </template>

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
                                show: !showArchived,
                                onSelect: () =>
                                    router.visit(
                                        `${baseRoute}/payroll/${p.public_id}`,
                                    ),
                            },
                            {
                                label: 'Archive',
                                icon: Archive,
                                destructive: true,
                                show:
                                    !showArchived &&
                                    canArchive &&
                                    p.status === 'draft',
                                onSelect: () => archivePayroll(p),
                            },
                            {
                                label: 'Restore',
                                icon: RotateCcw,
                                show: showArchived && canArchive,
                                onSelect: () => restorePayroll(p),
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
    </ShopLayout>
</template>
