<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { type BreadcrumbItem, type AppPageProps } from '@/types'
import { ref, computed, onMounted } from 'vue'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
} from '@/components/ui/dialog'
import {
    AlertDialog, AlertDialogContent, AlertDialogHeader,
    AlertDialogTitle, AlertDialogDescription, AlertDialogFooter,
} from '@/components/ui/alert-dialog'
import {
    ArrowLeft, Banknote, Users, CheckCircle2, Pencil,
    Lock, Loader2, Download, RefreshCw, ShieldCheck,
} from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface Employee {
    id: number
    employee_id: string
    first_name: string
    last_name: string
    position: string
    branch_name: string | null
    salary: string | null
}

interface PayrollItem {
    id: number
    payroll_id: number
    employee_id: number
    employee: Employee
    basic_salary: string
    days_worked: number
    days_absent: number
    days_late: number
    days_half_day: number
    deductions: string
    sss_contribution: string
    philhealth_contribution: string
    pagibig_contribution: string
    withholding_tax: string
    bonuses: string
    net_pay: string
    remarks: string | null
}

interface Payroll {
    id: number
    period_label: string
    period_start: string
    period_end: string
    status: 'draft' | 'finalized'
    items: PayrollItem[]
    creator: { id: number; name: string } | null
    created_at: string
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    payroll: Payroll
}>()

// ─── RBAC ─────────────────────────────────────────────────────────────────────

const page = usePage<AppPageProps>()
const isOwner = computed(() => page.props.auth.user.role === 'owner')
const baseRoute = computed(() => isOwner.value ? '/shop' : '/staff')
const isDraft = computed(() => props.payroll.status === 'draft')

// ─── Flash toast ──────────────────────────────────────────────────────────────

onMounted(() => {
    const flashToast = page.props.toast as { type: string; message: string } | undefined
    if (!flashToast) return
    switch (flashToast.type) {
        case 'success': toast.success(flashToast.message); break
        case 'error': toast.error(flashToast.message); break
        default: toast(flashToast.message)
    }
})

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Employee Management', href: '/shop/employee' },
    { title: 'Payroll', href: '/shop/payroll' },
    { title: props.payroll.period_label, href: `/shop/payroll/${props.payroll.id}` },
]

// ─── Summary ──────────────────────────────────────────────────────────────────

const totalNetPay = computed(() =>
    props.payroll.items.reduce((sum, i) => sum + parseFloat(i.net_pay), 0)
)

const totalDeductions = computed(() =>
    props.payroll.items.reduce((sum, i) => sum + parseFloat(i.deductions), 0)
)

const totalGovContributions = computed(() =>
    props.payroll.items.reduce((sum, i) =>
        sum
        + parseFloat(i.sss_contribution)
        + parseFloat(i.philhealth_contribution)
        + parseFloat(i.pagibig_contribution)
        + parseFloat(i.withholding_tax),
        0
    )
)

const totalBonuses = computed(() =>
    props.payroll.items.reduce((sum, i) => sum + parseFloat(i.bonuses), 0)
)

// ─── Edit Item Dialog ─────────────────────────────────────────────────────────

const editItem = ref<PayrollItem | null>(null)
const isEditOpen = ref(false)
const saving = ref(false)

const editForm = ref({
    bonuses: '0',
    remarks: '',
})

function openEdit(item: PayrollItem) {
    editItem.value = item
    editForm.value = {
        bonuses: item.bonuses,
        remarks: item.remarks ?? '',
    }
    isEditOpen.value = true
}

function submitEdit() {
    if (!editItem.value) return
    saving.value = true
    router.put(`${baseRoute.value}/payroll/${editItem.value.id}/item`, editForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            isEditOpen.value = false
            editItem.value = null
        },
        onFinish: () => {
            saving.value = false
        },
    })
}

function recalculate() {
    router.post(`${baseRoute.value}/payroll/${props.payroll.id}/recalculate`, {}, {
        preserveScroll: true,
    })
}

// ─── Finalize Dialog ──────────────────────────────────────────────────────────

const isFinalizeOpen = ref(false)
const finalizing = ref(false)

function confirmFinalize() {
    finalizing.value = true
    router.post(`${baseRoute.value}/payroll/${props.payroll.id}/finalize`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            isFinalizeOpen.value = false
        },
        onFinish: () => {
            finalizing.value = false
        },
    })
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatCurrency(val: string | number | null): string {
    const num = typeof val === 'string' ? parseFloat(val) : (val ?? 0)
    return `₱${num.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
}

function formatDate(val: string): string {
    return new Date(val).toLocaleDateString('en-PH', {
        year: 'numeric', month: 'long', day: 'numeric',
    })
}

// ─── CSV Export ───────────────────────────────────────────────────────────────

function exportCSV() {
    const headers = [
        'Employee ID', 'Name', 'Position', 'Branch',
        'Basic Salary', 'Days Worked', 'Days Absent', 'Days Late', 'Half Days',
        'Att. Deductions', 'SSS', 'PhilHealth', 'Pag-IBIG', 'Withholding Tax',
        'Bonuses', 'Net Pay', 'Remarks',
    ]

    const rows = props.payroll.items.map(i => [
        i.employee.employee_id,
        `${i.employee.first_name} ${i.employee.last_name}`,
        i.employee.position,
        i.employee.branch_name ?? '',
        i.basic_salary, i.days_worked, i.days_absent, i.days_late, i.days_half_day,
        i.deductions, i.sss_contribution, i.philhealth_contribution,
        i.pagibig_contribution, i.withholding_tax,
        i.bonuses, i.net_pay,
        i.remarks ?? '',
    ])

    const csvContent = [headers, ...rows]
        .map(row => row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(','))
        .join('\n')

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `payroll_${props.payroll.period_label.replace(/\s/g, '_')}.csv`
    link.click()
    URL.revokeObjectURL(url)
}
</script>

<template>

    <Head :title="`Payroll — ${payroll.period_label}`" />

    <ShopLayout :breadcrumbs="breadcrumbs" :title="payroll.period_label">
        <div class="px-6 space-y-6">

            <!-- ── Header ──────────────────────────────────────── -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Button variant="outline" @click="router.visit(`${baseRoute}/payroll`)">
                        <ArrowLeft class="h-4 w-4 mr-2" /> Back
                    </Button>
                    <div>
                        <h2 class="text-lg font-semibold flex items-center gap-2">
                            {{ payroll.period_label }}
                            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full" :class="payroll.status === 'finalized'
                                ? 'bg-green-100 text-green-700'
                                : 'bg-amber-100 text-amber-700'">
                                {{ payroll.status === 'finalized' ? 'Finalized' : 'Draft' }}
                            </span>
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            {{ formatDate(payroll.period_start) }} – {{ formatDate(payroll.period_end) }}
                            · Created by {{ payroll.creator?.name ?? '—' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" @click="exportCSV">
                        <Download class="h-4 w-4 mr-2" /> Export CSV
                    </Button>
                    <Button v-if="isDraft" variant="outline" @click="recalculate">
                        <RefreshCw class="h-4 w-4 mr-2" /> Recalculate
                    </Button>
                    <Button v-if="isDraft" @click="isFinalizeOpen = true">
                        <Lock class="h-4 w-4 mr-2" /> Finalize
                    </Button>
                </div>
            </div>

            <!-- ── Summary Cards ───────────────────────────────── -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <Card>
                    <CardContent class="pt-4 pb-4 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                            <Users class="h-5 w-5 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ payroll.items.length }}</p>
                            <p class="text-xs text-muted-foreground">Employees</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-4 pb-4 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-red-100 flex items-center justify-center">
                            <Banknote class="h-5 w-5 text-red-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ formatCurrency(totalDeductions) }}</p>
                            <p class="text-xs text-muted-foreground">Att. Deductions</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-4 pb-4 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-orange-100 flex items-center justify-center">
                            <ShieldCheck class="h-5 w-5 text-orange-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ formatCurrency(totalGovContributions) }}</p>
                            <p class="text-xs text-muted-foreground">Gov. Contributions</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-4 pb-4 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-green-100 flex items-center justify-center">
                            <Banknote class="h-5 w-5 text-green-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ formatCurrency(totalBonuses) }}</p>
                            <p class="text-xs text-muted-foreground">Bonuses</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="sm:col-span-4">
                    <CardContent class="pt-4 pb-4 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-purple-100 flex items-center justify-center">
                            <CheckCircle2 class="h-5 w-5 text-purple-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ formatCurrency(totalNetPay) }}</p>
                            <p class="text-xs text-muted-foreground">Total Net Pay</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- ── Payroll Items Table ──────────────────────────── -->
            <div class="rounded-xl border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                                <th class="text-left px-4 py-3 font-medium">Employee</th>
                                <th class="text-right px-4 py-3 font-medium">Basic Salary</th>
                                <th class="text-center px-4 py-3 font-medium">Worked</th>
                                <th class="text-center px-4 py-3 font-medium">Absent</th>
                                <th class="text-center px-4 py-3 font-medium">Late</th>
                                <th class="text-center px-4 py-3 font-medium">½ Day</th>
                                <th class="text-right px-4 py-3 font-medium">Att. Deduct.</th>
                                <th class="text-right px-4 py-3 font-medium bg-orange-50">SSS</th>
                                <th class="text-right px-4 py-3 font-medium bg-orange-50">PhilHealth</th>
                                <th class="text-right px-4 py-3 font-medium bg-orange-50">Pag-IBIG</th>
                                <th class="text-right px-4 py-3 font-medium bg-orange-50">W. Tax</th>
                                <th class="text-right px-4 py-3 font-medium">Bonuses</th>
                                <th class="text-right px-4 py-3 font-medium">Net Pay</th>
                                <th class="text-left px-4 py-3 font-medium">Remarks</th>
                                <th v-if="isDraft" class="text-right px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in payroll.items" :key="item.id"
                                class="border-b last:border-0 hover:bg-muted/20 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-9 w-9 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                                            <span class="text-xs font-bold text-primary">
                                                {{ item.employee.first_name[0] }}{{ item.employee.last_name[0] }}
                                            </span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-medium truncate">
                                                {{ item.employee.first_name }} {{ item.employee.last_name }}
                                            </p>
                                            <p class="text-xs text-muted-foreground">
                                                {{ item.employee.position }}
                                                <span v-if="item.employee.branch_name">· {{ item.employee.branch_name }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-xs">{{ formatCurrency(item.basic_salary) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center h-6 w-8 rounded bg-green-100 text-green-700 text-xs font-semibold">
                                        {{ item.days_worked }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center h-6 w-8 rounded text-xs font-semibold"
                                        :class="item.days_absent > 0 ? 'bg-red-100 text-red-700' : 'bg-muted text-muted-foreground'">
                                        {{ item.days_absent }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center h-6 w-8 rounded text-xs font-semibold"
                                        :class="item.days_late > 0 ? 'bg-amber-100 text-amber-700' : 'bg-muted text-muted-foreground'">
                                        {{ item.days_late }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center h-6 w-8 rounded text-xs font-semibold"
                                        :class="item.days_half_day > 0 ? 'bg-blue-100 text-blue-700' : 'bg-muted text-muted-foreground'">
                                        {{ item.days_half_day }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-xs text-red-600">
                                    {{ parseFloat(item.deductions) > 0 ? `-${formatCurrency(item.deductions)}` : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-xs text-orange-600 bg-orange-50/40">
                                    -{{ formatCurrency(item.sss_contribution) }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-xs text-orange-600 bg-orange-50/40">
                                    -{{ formatCurrency(item.philhealth_contribution) }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-xs text-orange-600 bg-orange-50/40">
                                    -{{ formatCurrency(item.pagibig_contribution) }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-xs text-orange-600 bg-orange-50/40">
                                    {{ parseFloat(item.withholding_tax) > 0 ? `-${formatCurrency(item.withholding_tax)}` : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-xs text-green-600">
                                    {{ parseFloat(item.bonuses) > 0 ? `+${formatCurrency(item.bonuses)}` : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold">{{ formatCurrency(item.net_pay) }}</td>
                                <td class="px-4 py-3 text-xs text-muted-foreground max-w-[120px] truncate">
                                    {{ item.remarks ?? '—' }}
                                </td>
                                <td v-if="isDraft" class="px-4 py-3 text-right">
                                    <Button size="icon" variant="ghost" @click="openEdit(item)">
                                        <Pencil class="h-4 w-4 text-blue-500" />
                                    </Button>
                                </td>
                            </tr>

                            <tr v-if="payroll.items.length === 0">
                                <td :colspan="isDraft ? 15 : 14"
                                    class="px-4 py-12 text-center text-sm text-muted-foreground">
                                    No payroll items found.
                                </td>
                            </tr>

                            <!-- Totals row -->
                            <tr v-if="payroll.items.length > 0" class="bg-muted/30 font-semibold border-t">
                                <td class="px-4 py-3" colspan="6">Total</td>
                                <td class="px-4 py-3 text-right font-mono text-xs text-red-600">
                                    {{ totalDeductions > 0 ? `-${formatCurrency(totalDeductions)}` : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-xs text-orange-600 bg-orange-50/40" colspan="4">
                                    -{{ formatCurrency(totalGovContributions) }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-xs text-green-600">
                                    {{ totalBonuses > 0 ? `+${formatCurrency(totalBonuses)}` : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right text-base">{{ formatCurrency(totalNetPay) }}</td>
                                <td class="px-4 py-3"></td>
                                <td v-if="isDraft" class="px-4 py-3"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- ── Edit Item Dialog ────────────────────────────────── -->
        <Dialog v-model:open="isEditOpen">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>
                        Edit — {{ editItem?.employee.first_name }} {{ editItem?.employee.last_name }}
                    </DialogTitle>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Bonuses (₱)</label>
                        <Input v-model="editForm.bonuses" type="number" min="0" step="0.01" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Remarks</label>
                        <Input v-model="editForm.remarks" placeholder="Optional" />
                    </div>

                    <!-- Gov. contributions breakdown -->
                    <div class="rounded-lg border bg-orange-50/60 px-4 py-3 space-y-1.5 text-xs">
                        <p class="font-semibold text-orange-700 mb-2">Government Contributions (auto-calculated)</p>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">SSS</span>
                            <span class="font-mono">-{{ formatCurrency(editItem?.sss_contribution ?? '0') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">PhilHealth</span>
                            <span class="font-mono">-{{ formatCurrency(editItem?.philhealth_contribution ?? '0') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Pag-IBIG</span>
                            <span class="font-mono">-{{ formatCurrency(editItem?.pagibig_contribution ?? '0') }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-1.5 mt-1">
                            <span class="text-muted-foreground">Withholding Tax</span>
                            <span class="font-mono">-{{ formatCurrency(editItem?.withholding_tax ?? '0') }}</span>
                        </div>
                    </div>

                    <div class="rounded-lg border bg-muted/40 px-4 py-3 text-xs text-muted-foreground">
                        Attendance deductions (tardiness 5% of daily rate) and absent days from schedule
                        are auto-calculated and cannot be changed here. Use Recalculate to refresh all values.
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isEditOpen = false">Cancel</Button>
                    <Button :disabled="saving" @click="submitEdit">
                        <Loader2 v-if="saving" class="h-4 w-4 mr-2 animate-spin" />
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
                        Are you sure you want to finalize this payroll?
                        Once finalized, no further edits can be made to deductions, bonuses, or remarks.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <Button variant="outline" @click="isFinalizeOpen = false">Cancel</Button>
                    <Button :disabled="finalizing" @click="confirmFinalize">
                        <Loader2 v-if="finalizing" class="h-4 w-4 mr-2 animate-spin" />
                        {{ finalizing ? 'Finalizing...' : 'Finalize' }}
                    </Button>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

    </ShopLayout>
</template>
