<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { type BreadcrumbItem, type AppPageProps } from '@/types'
import { ref, computed, onMounted } from 'vue'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import {
    Eye, Pencil, Trash2,
    Users, UserCheck, UserPlus, BadgeCheck,
    Upload, Download, FileWarning, X, CheckCircle2, Loader2, Trash,
    Building2, ChevronLeft, ChevronRight,
} from 'lucide-vue-next'
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
} from '@/components/ui/dialog'
import {
    AlertDialog, AlertDialogContent, AlertDialogHeader,
    AlertDialogTitle, AlertDialogDescription, AlertDialogFooter,
} from '@/components/ui/alert-dialog'

// ─── Types ────────────────────────────────────────────────────────────────────

interface AuditUser {
    id: number
    name: string
}

interface Employee {
    id: number
    user_id: number
    shop_id: number | null
    employee_id: string
    first_name: string
    last_name: string
    email: string
    phone: string | null
    address: string | null
    position: string
    branch_name: string | null
    hire_date: string
    salary: string | null
    status: 'Active' | 'Inactive'
    created_at: string
    updated_at: string
    creator: AuditUser | null
    updater: AuditUser | null
}

interface PaginatedEmployees {
    data: Employee[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
}

// ─── Props ────────────────────────────────────────────────────────────────────

const { employees, stats } = defineProps<{
    employees: PaginatedEmployees
    stats: {
        total: number
        active: number
        new_this_month: number
        inactive: number
    }
}>()

// ─── RBAC ─────────────────────────────────────────────────────────────────────

const page = usePage<AppPageProps>()
const user = computed(() => page.props.auth.user)
const isOwner = computed(() => user.value.role === 'owner')
const permissions = computed(() => user.value.permissions ?? {})

function can(module: string, action: string): boolean {
    if (isOwner.value) return true
    return permissions.value[module]?.includes(action) ?? false
}

// Route helpers — staff uses /staff/* prefix
const baseRoute = computed(() => isOwner.value ? '/shop' : '/staff')

function employeeUrl(id: number, suffix = ''): string {
    return `${baseRoute.value}/employee/${id}${suffix}`
}

// ─── Flash toast ──────────────────────────────────────────────────────────────

onMounted(() => {

    const flashToast = page.props.toast as { type: string; message: string } | undefined
    if (!flashToast) return

    switch (flashToast.type) {
        case 'success': toast.success(flashToast.message); break
        case 'error': toast.error(flashToast.message); break
        case 'warning': toast.warning(flashToast.message); break
        default: toast(flashToast.message)
    }
})

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Employee Management', href: `${baseRoute.value}/employee` },
]

// ─── Filters ──────────────────────────────────────────────────────────────────

const searchQuery = ref('')
const statusFilter = ref('all')

const filteredEmployees = computed(() =>
    employees.data.filter((emp) => {
        const fullName = `${emp.first_name} ${emp.last_name}`.toLowerCase()
        const matchesSearch =
            fullName.includes(searchQuery.value.toLowerCase()) ||
            emp.employee_id.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            emp.position.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            (emp.phone ?? '').toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            (emp.branch_name ?? '').toLowerCase().includes(searchQuery.value.toLowerCase())

        const matchesStatus =
            statusFilter.value === 'all' || emp.status === statusFilter.value

        return matchesSearch && matchesStatus
    })
)

// ─── Pagination ───────────────────────────────────────────────────────────────

function goToPage(p: number) {
    router.get(`${baseRoute.value}/employee`, { page: p }, { preserveScroll: true, preserveState: true })
}

const visiblePages = computed(() => {
    const total = employees.last_page
    const current = employees.current_page
    const pages: number[] = []
    for (let i = Math.max(1, current - 2); i <= Math.min(total, current + 2); i++) {
        pages.push(i)
    }
    return pages
})

// ─── Archive ──────────────────────────────────────────────────────────────────

const employeeToArchive = ref<Employee | null>(null)
const isArchiveOpen = ref(false)

function openArchiveDialog(emp: Employee) {
    employeeToArchive.value = emp
    isArchiveOpen.value = true
}

function cancelArchive() {
    isArchiveOpen.value = false
    setTimeout(() => { employeeToArchive.value = null }, 200)
}

function confirmArchive() {
    if (!employeeToArchive.value) return
    router.delete(`/shop/employee/${employeeToArchive.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            employeeToArchive.value = null
            toast.success('Employee archived successfully.')
        },
        onError: () => toast.error('Failed to archive employee.'),
    })
    isArchiveOpen.value = false
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

const formatSalary = (val: string | null) =>
    val ? `₱${parseFloat(val).toLocaleString('en-PH', { minimumFractionDigits: 2 })}` : '—'

const formatDate = (val: string) =>
    new Date(val).toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' })

// ─── CSV Export ───────────────────────────────────────────────────────────────

function exportCSV() {
    const headers = [
        'employee_id', 'first_name', 'last_name', 'phone',
        'address', 'branch_name', 'position', 'hire_date', 'salary', 'status',
        'added_by', 'last_modified_by',
    ]

    const rows = filteredEmployees.value.map((emp) => [
        emp.employee_id, emp.first_name, emp.last_name,
        emp.phone ?? '', emp.address ?? '', emp.branch_name ?? '',
        emp.position, emp.hire_date, emp.salary ?? '', emp.status,
        emp.creator?.name ?? '', emp.updater?.name ?? '',
    ])

    const csvContent = [headers, ...rows]
        .map((row) => row.map((cell) => `"${String(cell).replace(/"/g, '""')}"`).join(','))
        .join('\n')

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `employees_${new Date().toISOString().slice(0, 10)}.csv`
    link.click()
    URL.revokeObjectURL(url)
}

// ─── CSV Import ───────────────────────────────────────────────────────────────

const isImportOpen = ref(false)
const importFile = ref<File | null>(null)
const importErrors = ref<string[]>([])
const importSuccess = ref(false)
const importing = ref(false)
const isDragging = ref(false)
const fileInputRef = ref<HTMLInputElement | null>(null)

function openImport() {
    importFile.value = null
    importErrors.value = []
    importSuccess.value = false
    isImportOpen.value = true
}

function onFileChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0]
    if (file?.name.endsWith('.csv')) {
        importFile.value = file
        importErrors.value = []
    } else {
        importErrors.value = ['Please select a valid .csv file.']
        importFile.value = null
    }
}

function onDrop(e: DragEvent) {
    isDragging.value = false
    const file = e.dataTransfer?.files?.[0]
    if (file?.name.endsWith('.csv')) {
        importFile.value = file
        importErrors.value = []
    } else {
        importErrors.value = ['Please drop a valid .csv file.']
    }
}

function clearFile() {
    importFile.value = null
    importErrors.value = []
    if (fileInputRef.value) fileInputRef.value.value = ''
}

async function submitImport() {
    if (!importFile.value) return

    importing.value = true
    importErrors.value = []
    importSuccess.value = false

    const formData = new FormData()
    formData.append('csv_file', importFile.value)

    try {
        const response = await fetch('/shop/employee/import', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
                'Accept': 'application/json',
            },
            body: formData,
        })

        const data = await response.json()

        if (!response.ok) {
            importErrors.value = Array.isArray(data.errors)
                ? data.errors
                : (data.message ? [data.message] : ['An unknown error occurred.'])
            toast.error('Import failed. Please fix the errors and try again.')
        } else {
            importSuccess.value = true
            importFile.value = null
            toast.success('Employees imported successfully!')
            setTimeout(() => {
                router.reload({ only: ['employees', 'stats'] })
                isImportOpen.value = false
                importSuccess.value = false
            }, 1500)
        }
    } catch {
        importErrors.value = ['Failed to connect to the server. Please try again.']
        toast.error('Failed to connect to the server.')
    } finally {
        importing.value = false
    }
}
</script>

<template>

    <Head title="Employee Management" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Employee List">

        <!-- Stats -->
        <div class="grid gap-4 md:grid-cols-4 mb-6">
            <Card>
                <CardHeader class="flex flex-row justify-between items-center pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">Total Employees</CardTitle>
                    <Users class="h-4 w-4 text-blue-500" />
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-bold text-blue-600">{{ stats.total }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="flex flex-row justify-between items-center pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">Active</CardTitle>
                    <UserCheck class="h-4 w-4 text-green-500" />
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-bold text-green-600">{{ stats.active }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="flex flex-row justify-between items-center pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">Inactive</CardTitle>
                    <BadgeCheck class="h-4 w-4 text-red-400" />
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-bold text-red-500">{{ stats.inactive }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="flex flex-row justify-between items-center pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">New This Month</CardTitle>
                    <UserPlus class="h-4 w-4 text-purple-500" />
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-bold text-purple-600">{{ stats.new_this_month }}</p>
                </CardContent>
            </Card>
        </div>

        <!-- Table -->
        <Card>
            <CardHeader class="flex flex-row justify-between items-center gap-2 flex-wrap">
                <CardTitle>Employee List</CardTitle>
                <div class="flex items-center gap-2 flex-nowrap">
                    <Input v-model="searchQuery" placeholder="Search name, ID, position..." class="w-56" />
                    <Select v-model="statusFilter">
                        <SelectTrigger class="w-28">
                            <SelectValue placeholder="Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All</SelectItem>
                            <SelectItem value="Active">Active</SelectItem>
                            <SelectItem value="Inactive">Inactive</SelectItem>
                        </SelectContent>
                    </Select>

                    <!-- Export — view permission -->
                    <Button v-if="can('HRM', 'view')" variant="outline"
                        class="bg-blue-500 text-white hover:bg-blue-600 hover:text-white hover:shadow-md transition duration-200"
                        @click="exportCSV">
                        <Download class="h-4 w-4 mr-1.5" /> Export CSV
                    </Button>

                    <!-- Import — owner only -->
                    <Button v-if="isOwner" variant="outline"
                        class="bg-green-500 text-white hover:bg-green-600 hover:shadow-md hover:text-white transition duration-200"
                        @click="openImport">
                        <Upload class="h-4 w-4 mr-1.5" /> Import CSV
                    </Button>

                    <!-- Archive list — owner only -->
                    <Button v-if="isOwner" variant="outline"
                        class="bg-red-500 text-white hover:bg-red-600 hover:shadow-md hover:text-white transition duration-200"
                        @click="router.visit('/shop/employee/archive')">
                        <Trash class="h-4 w-4 mr-1.5" /> Archive
                    </Button>

                    <!-- Add Employee — create permission -->
                    <Button v-if="can('HRM', 'create')" class="bg-gray-900 text-white hover:bg-gray-800 transition"
                        @click="router.visit(`${baseRoute}/employee/create`)">
                        <UserPlus class="h-4 w-4 mr-1.5" /> Add Employee
                    </Button>
                </div>
            </CardHeader>

            <CardContent>
                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Employee ID</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Position</TableHead>
                                <TableHead>Branch</TableHead>
                                <TableHead>Phone</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Address</TableHead>
                                <TableHead>Hire Date</TableHead>
                                <TableHead>Salary</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Added By</TableHead>
                                <TableHead>Modified By</TableHead>
                                <TableHead class="text-center">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="filteredEmployees.length === 0">
                                <TableCell colspan="13" class="text-center text-muted-foreground py-10">
                                    No employees found.
                                </TableCell>
                            </TableRow>

                            <TableRow v-for="emp in filteredEmployees" :key="emp.id">
                                <TableCell class="font-mono text-xs whitespace-nowrap">{{ emp.employee_id }}</TableCell>
                                <TableCell class="font-medium whitespace-nowrap">{{ emp.first_name }} {{ emp.last_name
                                    }}</TableCell>
                                <TableCell class="whitespace-nowrap">{{ emp.position }}</TableCell>
                                <TableCell class="whitespace-nowrap">{{ emp.branch_name ?? '—' }}</TableCell>
                                <TableCell class="whitespace-nowrap">{{ emp.phone ?? '—' }}</TableCell>
                                <TableCell class="whitespace-nowrap">{{ emp.email ?? '—' }}</TableCell>
                                <TableCell class="max-w-[200px]">
                                    <span class="block truncate text-xs text-muted-foreground"
                                        :title="emp.address ?? ''">
                                        {{ emp.address ?? '—' }}
                                    </span>
                                </TableCell>
                                <TableCell class="whitespace-nowrap">{{ formatDate(emp.hire_date) }}</TableCell>
                                <TableCell class="whitespace-nowrap">{{ formatSalary(emp.salary) }}</TableCell>
                                <TableCell>
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full text-white whitespace-nowrap"
                                        :class="{
                                            'bg-green-500': emp.status === 'Active',
                                            'bg-red-500': emp.status === 'Inactive',
                                        }">
                                        {{ emp.status }}
                                    </span>
                                </TableCell>
                                <TableCell class="whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-medium">{{ emp.creator?.name ?? '—' }}</span>
                                        <span class="text-xs text-muted-foreground">{{ formatDate(emp.created_at)
                                            }}</span>
                                    </div>
                                </TableCell>
                                <TableCell class="whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-medium">{{ emp.updater?.name ?? '—' }}</span>
                                        <span class="text-xs text-muted-foreground">{{ formatDate(emp.updated_at)
                                            }}</span>
                                    </div>
                                </TableCell>

                                <!-- Row actions — each gated by RBAC key matching moduleActionMap exactly -->
                                <TableCell class="text-center">
                                    <div class="flex items-center justify-center gap-1">

                                        <!-- View — key: 'view' -->
                                        <Button v-if="can('HRM', 'view')" size="icon" variant="ghost"
                                            @click="router.visit(employeeUrl(emp.id))">
                                            <Eye class="h-4 w-4 text-blue-500" />
                                        </Button>

                                        <!-- Edit — key: 'update' (matches moduleActionMap) -->
                                        <Button v-if="can('HRM', 'update')" size="icon" variant="ghost"
                                            @click="router.visit(employeeUrl(emp.id, '/edit'))">
                                            <Pencil class="h-4 w-4 text-green-500" />
                                        </Button>

                                        <!-- Archive — key: 'archive' (matches moduleActionMap) -->
                                        <Button v-if="can('HRM', 'archive')" size="icon" variant="ghost"
                                            @click="openArchiveDialog(emp)">
                                            <Trash2 class="h-4 w-4 text-red-500" />
                                        </Button>

                                        <!-- No actions fallback -->
                                        <span
                                            v-if="!can('HRM', 'view') && !can('HRM', 'update') && !can('HRM', 'archive')"
                                            class="text-xs text-muted-foreground">
                                            —
                                        </span>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-4 py-3 border-t">
            <p class="text-xs text-muted-foreground">
                Showing {{ employees.from ?? 0 }}–{{ employees.to ?? 0 }} of {{ employees.total }} employees
            </p>
            <div class="flex items-center gap-1">
                <Button size="icon" variant="outline" :disabled="employees.current_page === 1"
                    @click="goToPage(employees.current_page - 1)">
                    <ChevronLeft class="h-4 w-4" />
                </Button>
                <Button v-if="employees.current_page > 3" size="icon" variant="outline" @click="goToPage(1)">
                    1
                </Button>
                <span v-if="employees.current_page > 3" class="text-xs text-muted-foreground px-1">…</span>
                <Button v-for="p in visiblePages" :key="p" size="icon"
                    :variant="p === employees.current_page ? 'default' : 'outline'" @click="goToPage(p)">
                    {{ p }}
                </Button>
                <span v-if="employees.current_page < employees.last_page - 2"
                    class="text-xs text-muted-foreground px-1">…</span>
                <Button v-if="employees.current_page < employees.last_page - 2" size="icon" variant="outline"
                    @click="goToPage(employees.last_page)">
                    {{ employees.last_page }}
                </Button>
                <Button size="icon" variant="outline" :disabled="employees.current_page === employees.last_page"
                    @click="goToPage(employees.current_page + 1)">
                    <ChevronRight class="h-4 w-4" />
                </Button>
            </div>
        </div>

        <!-- Import Dialog -->
        <Dialog v-model:open="isImportOpen">
            <DialogContent class="max-w-lg">
                <DialogHeader>
                    <DialogTitle>Import Employees via CSV</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 text-sm">
                    <div class="rounded-lg border bg-muted/40 px-4 py-3 text-xs text-muted-foreground leading-relaxed">
                        <p class="font-semibold text-foreground mb-1">Required CSV columns (in order):</p>
                        <code class="block font-mono break-all">
                            employee_id, first_name, last_name, phone, address, branch_name, position, hire_date, salary, status
                        </code>
                        <p class="mt-2">
                            <span class="font-medium">hire_date</span>: <code>YYYY-MM-DD</code>
                            &nbsp;|&nbsp;
                            <span class="font-medium">status</span>: <code>Active</code> or <code>Inactive</code>
                        </p>
                        <a href="/shop/employee/import-template"
                            class="inline-flex items-center gap-1 mt-2 text-primary hover:underline font-medium">
                            <Download class="w-3 h-3" /> Download blank template
                        </a>
                    </div>

                    <div class="relative border-2 border-dashed rounded-xl transition-colors cursor-pointer"
                        :class="isDragging ? 'border-primary bg-primary/5' : 'border-muted-foreground/30 hover:border-primary/50'"
                        @dragover.prevent="isDragging = true" @dragleave="isDragging = false" @drop.prevent="onDrop"
                        @click="fileInputRef?.click()">
                        <input ref="fileInputRef" type="file" accept=".csv" class="hidden" @change="onFileChange" />
                        <div class="flex flex-col items-center justify-center py-8 px-4 text-center select-none">
                            <template v-if="!importFile">
                                <Upload class="w-8 h-8 text-muted-foreground mb-2" />
                                <p class="font-medium text-foreground">Click to browse or drag & drop</p>
                                <p class="text-xs text-muted-foreground mt-1">Only .csv files are accepted</p>
                            </template>
                            <template v-else>
                                <div class="flex items-center gap-2 text-green-600">
                                    <CheckCircle2 class="w-5 h-5" />
                                    <span class="font-medium">{{ importFile.name }}</span>
                                </div>
                                <p class="text-xs text-muted-foreground mt-1">{{ (importFile.size / 1024).toFixed(1) }}
                                    KB</p>
                                <button class="mt-2 flex items-center gap-1 text-xs text-red-500 hover:underline"
                                    @click.stop="clearFile">
                                    <X class="w-3 h-3" /> Remove file
                                </button>
                            </template>
                        </div>
                    </div>

                    <div v-if="importErrors.length" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                        <div class="flex items-center gap-2 text-red-700 font-semibold mb-2">
                            <FileWarning class="w-4 h-4 flex-shrink-0" />
                            Import failed — please fix the following:
                        </div>
                        <ul class="space-y-1">
                            <li v-for="(error, i) in importErrors" :key="i"
                                class="text-xs text-red-600 flex items-start gap-1.5">
                                <span class="mt-0.5 flex-shrink-0">•</span>{{ error }}
                            </li>
                        </ul>
                    </div>
                </div>

                <DialogFooter class="mt-2">
                    <Button variant="outline" @click="isImportOpen = false">Cancel</Button>
                    <Button :disabled="!importFile || importing" @click="submitImport">
                        <Loader2 v-if="importing" class="w-4 h-4 mr-1.5 animate-spin" />
                        <Upload v-else class="w-4 h-4 mr-1.5" />
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
                        <strong>{{ employeeToArchive?.first_name }} {{ employeeToArchive?.last_name }}</strong>?
                        This will move them to the archive.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <Button variant="outline" @click="cancelArchive">Cancel</Button>
                    <Button variant="destructive" @click="confirmArchive">Remove</Button>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

    </ShopLayout>
</template>
