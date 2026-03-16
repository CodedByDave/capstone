<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import { ref, computed, onMounted } from 'vue'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

// shadcn
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import {
    AlertDialog, AlertDialogContent, AlertDialogHeader,
    AlertDialogTitle, AlertDialogDescription, AlertDialogFooter,
} from '@/components/ui/alert-dialog'

// icons
import {
    Building2, Users, CheckCircle2, XCircle,
    Eye, Pencil, Trash2, Plus, ChevronLeft, ChevronRight,
    Download, MapPin, Phone, Mail, User, CalendarDays, Trash,
} from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface AuditUser { id: number; name: string }

interface Branch {
    id: number
    branch_code: string
    name: string
    phone: string | null
    email: string | null
    manager_name: string | null
    address: string | null
    opened_at: string | null
    status: 'Active' | 'Inactive'
    employees_count: number
    created_at: string
    updated_at: string
    creator: AuditUser | null
    updater: AuditUser | null
}

interface PaginatedBranches {
    data: Branch[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
}

// ─── Props ────────────────────────────────────────────────────────────────────

const { branches, stats } = defineProps<{
    branches: PaginatedBranches
    stats: { total: number; active: number; inactive: number }
}>()

// ─── Flash ────────────────────────────────────────────────────────────────────

const page = usePage()

onMounted(() => {
    const f = page.props.toast as { type: string; message: string } | undefined
    if (!f) return
    if (f.type === 'success') toast.success(f.message)
    else if (f.type === 'error') toast.error(f.message)
    else toast(f.message)
})

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Employee Management', href: '/shop/employee' },
    { title: 'Branch Management', href: '/shop/branch' },
]

// ─── Filters ─────────────────────────────────────────────────────────────────

const searchQuery  = ref('')
const statusFilter = ref('all')

const filtered = computed(() =>
    branches.data.filter((b) => {
        const q = searchQuery.value.toLowerCase()
        const matchSearch =
            b.name.toLowerCase().includes(q) ||
            b.branch_code.toLowerCase().includes(q) ||
            (b.manager_name ?? '').toLowerCase().includes(q) ||
            (b.address ?? '').toLowerCase().includes(q)
        const matchStatus = statusFilter.value === 'all' || b.status === statusFilter.value
        return matchSearch && matchStatus
    })
)

// ─── Pagination ───────────────────────────────────────────────────────────────

function goToPage(p: number) {
    router.get('/shop/branch', { page: p }, { preserveScroll: true, preserveState: true })
}

const visiblePages = computed(() => {
    const pages: number[] = []
    const delta = 2
    for (
        let i = Math.max(1, branches.current_page - delta);
        i <= Math.min(branches.last_page, branches.current_page + delta);
        i++
    ) pages.push(i)
    return pages
})

// ─── Helpers ──────────────────────────────────────────────────────────────────

const fmtDate = (v: string | null) =>
    v ? new Date(v).toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' }) : '—'

// ─── CSV Export ───────────────────────────────────────────────────────────────

function exportCSV() {
    const headers = ['branch_code', 'name', 'phone', 'email', 'manager_name', 'address', 'opened_at', 'status', 'employees', 'added_by', 'modified_by']
    const rows = filtered.value.map(b => [
        b.branch_code, b.name, b.phone ?? '', b.email ?? '',
        b.manager_name ?? '', b.address ?? '', b.opened_at ?? '',
        b.status, b.employees_count,
        b.creator?.name ?? '', b.updater?.name ?? '',
    ])
    const csv = [headers, ...rows]
        .map(r => r.map(c => `"${String(c).replace(/"/g, '""')}"`).join(','))
        .join('\n')
    const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }))
    const a   = document.createElement('a')
    a.href     = url
    a.download = `branches_${new Date().toISOString().slice(0, 10)}.csv`
    a.click()
    URL.revokeObjectURL(url)
}

// ─── View Dialog ──────────────────────────────────────────────────────────────

const viewBranch = ref<Branch | null>(null)
const isViewOpen = ref(false)
function openView(b: Branch) { viewBranch.value = b; isViewOpen.value = true }

// ─── Archive Dialog ───────────────────────────────────────────────────────────

const archiveBranch = ref<Branch | null>(null)
const isArchiveOpen = ref(false)

function openArchive(b: Branch) { archiveBranch.value = b; isArchiveOpen.value = true }

function cancelArchive() {
    isArchiveOpen.value = false
    setTimeout(() => { archiveBranch.value = null }, 200)
}

function confirmArchive() {
    if (!archiveBranch.value) return
    router.delete(`/shop/branch/${archiveBranch.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { archiveBranch.value = null },
        onError:   () => { toast.error('Failed to archive branch.') },
    })
    isArchiveOpen.value = false
}
</script>

<template>
    <Head title="Branch Management" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Branch Management">

        <!-- ── Stats ──────────────────────────────────────────────────────── -->
        <div class="grid gap-4 md:grid-cols-3 mb-6">
            <Card>
                <CardHeader class="flex flex-row justify-between items-center pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">Total Branches</CardTitle>
                    <Building2 class="h-4 w-4 text-blue-500" />
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-bold text-blue-600">{{ stats.total }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="flex flex-row justify-between items-center pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">Active</CardTitle>
                    <CheckCircle2 class="h-4 w-4 text-green-500" />
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-bold text-green-600">{{ stats.active }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="flex flex-row justify-between items-center pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">Inactive</CardTitle>
                    <XCircle class="h-4 w-4 text-red-400" />
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-bold text-red-500">{{ stats.inactive }}</p>
                </CardContent>
            </Card>
        </div>

        <!-- ── Table Card ─────────────────────────────────────────────────── -->
        <Card>
            <CardHeader class="flex flex-row justify-between items-center gap-2 flex-wrap">
                <CardTitle>Branch List</CardTitle>
                <div class="flex items-center gap-2 flex-wrap">
                    <Input v-model="searchQuery" placeholder="Search name, code, manager..." class="w-56" />
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
                    <Button variant="outline" @click="exportCSV"
                        class="bg-blue-500 text-white hover:bg-blue-600 hover:text-white transition">
                        <Download class="h-4 w-4 mr-1.5" /> Export CSV
                    </Button>
                    <Button variant="outline" @click="router.visit('/shop/branch/archive')"
                        class="bg-red-500 text-white hover:bg-red-600 hover:text-white transition">
                        <Trash class="h-4 w-4 mr-1.5" /> Archive
                    </Button>
                    <Button @click="router.visit('/shop/branch/create')">
                        <Plus class="h-4 w-4 mr-1.5" /> Add Branch
                    </Button>
                </div>
            </CardHeader>

            <CardContent>
                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Code</TableHead>
                                <TableHead>Branch Name</TableHead>
                                <TableHead>Manager</TableHead>
                                <TableHead>Phone</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Address</TableHead>
                                <TableHead>Opened</TableHead>
                                <TableHead>Employees</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Added By</TableHead>
                                <TableHead>Modified By</TableHead>
                                <TableHead class="text-center">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="filtered.length === 0">
                                <TableCell colspan="12" class="text-center text-muted-foreground py-10">
                                    No branches found.
                                </TableCell>
                            </TableRow>
                            <TableRow v-for="b in filtered" :key="b.id">
                                <TableCell class="font-mono text-xs whitespace-nowrap">{{ b.branch_code }}</TableCell>
                                <TableCell class="font-medium whitespace-nowrap">{{ b.name }}</TableCell>
                                <TableCell class="whitespace-nowrap">{{ b.manager_name ?? '—' }}</TableCell>
                                <TableCell class="whitespace-nowrap">{{ b.phone ?? '—' }}</TableCell>
                                <TableCell class="whitespace-nowrap">{{ b.email ?? '—' }}</TableCell>
                                <TableCell class="max-w-[200px]">
                                    <span class="block truncate text-xs text-muted-foreground" :title="b.address ?? ''">
                                        {{ b.address ?? '—' }}
                                    </span>
                                </TableCell>
                                <TableCell class="whitespace-nowrap">{{ fmtDate(b.opened_at) }}</TableCell>
                                <TableCell>
                                    <span class="inline-flex items-center gap-1">
                                        <Users class="h-3 w-3 text-muted-foreground" />
                                        {{ b.employees_count }}
                                    </span>
                                </TableCell>
                                <TableCell>
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full text-white whitespace-nowrap"
                                        :class="b.status === 'Active' ? 'bg-green-500' : 'bg-red-500'">
                                        {{ b.status }}
                                    </span>
                                </TableCell>
                                <TableCell class="whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-medium">{{ b.creator?.name ?? '—' }}</span>
                                        <span class="text-xs text-muted-foreground">{{ fmtDate(b.created_at) }}</span>
                                    </div>
                                </TableCell>
                                <TableCell class="whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-medium">{{ b.updater?.name ?? '—' }}</span>
                                        <span class="text-xs text-muted-foreground">{{ fmtDate(b.updated_at) }}</span>
                                    </div>
                                </TableCell>
                                <TableCell class="text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <Button size="icon" variant="ghost" @click="openView(b)">
                                            <Eye class="h-4 w-4 text-blue-500" />
                                        </Button>
                                        <Button size="icon" variant="ghost"
                                            @click="router.visit(`/shop/branch/${b.id}/edit`)">
                                            <Pencil class="h-4 w-4 text-green-500" />
                                        </Button>
                                        <Button size="icon" variant="ghost" @click="openArchive(b)">
                                            <Trash2 class="h-4 w-4 text-red-500" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>

        <!-- ── Pagination ─────────────────────────────────────────────────── -->
        <div class="flex items-center justify-between px-4 py-3 border-t">
            <p class="text-xs text-muted-foreground">
                Showing {{ branches.from ?? 0 }}–{{ branches.to ?? 0 }} of {{ branches.total }} branches
            </p>
            <div class="flex items-center gap-1">
                <Button size="icon" variant="outline" :disabled="branches.current_page === 1"
                    @click="goToPage(branches.current_page - 1)">
                    <ChevronLeft class="h-4 w-4" />
                </Button>
                <Button v-if="branches.current_page > 3" size="icon" variant="outline" @click="goToPage(1)">
                    1
                </Button>
                <span v-if="branches.current_page > 3" class="text-xs text-muted-foreground px-1">…</span>
                <Button v-for="p in visiblePages" :key="p" size="icon"
                    :variant="p === branches.current_page ? 'default' : 'outline'" @click="goToPage(p)">
                    {{ p }}
                </Button>
                <span v-if="branches.current_page < branches.last_page - 2"
                    class="text-xs text-muted-foreground px-1">…</span>
                <Button v-if="branches.current_page < branches.last_page - 2" size="icon" variant="outline"
                    @click="goToPage(branches.last_page)">
                    {{ branches.last_page }}
                </Button>
                <Button size="icon" variant="outline" :disabled="branches.current_page === branches.last_page"
                    @click="goToPage(branches.current_page + 1)">
                    <ChevronRight class="h-4 w-4" />
                </Button>
            </div>
        </div>

        <!-- ── View Dialog ────────────────────────────────────────────────── -->
        <Dialog v-model:open="isViewOpen">
            <DialogContent class="max-w-lg">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <Building2 class="h-5 w-5 text-blue-500" />
                        {{ viewBranch?.name }}
                        <span class="ml-auto px-2 py-0.5 text-xs font-semibold rounded-full text-white"
                            :class="viewBranch?.status === 'Active' ? 'bg-green-500' : 'bg-red-500'">
                            {{ viewBranch?.status }}
                        </span>
                    </DialogTitle>
                </DialogHeader>
                <div v-if="viewBranch" class="space-y-4 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-xs bg-muted px-2 py-0.5 rounded">{{ viewBranch.branch_code }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-0.5">
                            <p class="text-xs text-muted-foreground uppercase tracking-wide">Manager</p>
                            <p class="flex items-center gap-1.5 font-medium">
                                <User class="h-3.5 w-3.5 text-muted-foreground" />
                                {{ viewBranch.manager_name ?? '—' }}
                            </p>
                        </div>
                        <div class="space-y-0.5">
                            <p class="text-xs text-muted-foreground uppercase tracking-wide">Employees</p>
                            <p class="flex items-center gap-1.5 font-medium">
                                <Users class="h-3.5 w-3.5 text-muted-foreground" />
                                {{ viewBranch.employees_count }}
                            </p>
                        </div>
                        <div class="space-y-0.5">
                            <p class="text-xs text-muted-foreground uppercase tracking-wide">Phone</p>
                            <p class="flex items-center gap-1.5 font-medium">
                                <Phone class="h-3.5 w-3.5 text-muted-foreground" />
                                {{ viewBranch.phone ?? '—' }}
                            </p>
                        </div>
                        <div class="space-y-0.5">
                            <p class="text-xs text-muted-foreground uppercase tracking-wide">Email</p>
                            <p class="flex items-center gap-1.5 font-medium">
                                <Mail class="h-3.5 w-3.5 text-muted-foreground" />
                                {{ viewBranch.email ?? '—' }}
                            </p>
                        </div>
                        <div class="space-y-0.5 col-span-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-wide">Address</p>
                            <p class="flex items-start gap-1.5 font-medium">
                                <MapPin class="h-3.5 w-3.5 text-muted-foreground mt-0.5 shrink-0" />
                                {{ viewBranch.address ?? '—' }}
                            </p>
                        </div>
                        <div class="space-y-0.5">
                            <p class="text-xs text-muted-foreground uppercase tracking-wide">Opened</p>
                            <p class="flex items-center gap-1.5 font-medium">
                                <CalendarDays class="h-3.5 w-3.5 text-muted-foreground" />
                                {{ fmtDate(viewBranch.opened_at) }}
                            </p>
                        </div>
                    </div>
                    <div class="border-t pt-3 grid grid-cols-2 gap-3 text-xs text-muted-foreground">
                        <div>
                            <p class="font-medium text-foreground">Added by</p>
                            <p>{{ viewBranch.creator?.name ?? '—' }}</p>
                            <p>{{ fmtDate(viewBranch.created_at) }}</p>
                        </div>
                        <div>
                            <p class="font-medium text-foreground">Modified by</p>
                            <p>{{ viewBranch.updater?.name ?? '—' }}</p>
                            <p>{{ fmtDate(viewBranch.updated_at) }}</p>
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isViewOpen = false">Close</Button>
                    <Button @click="router.visit(`/shop/branch/${viewBranch?.id}/edit`); isViewOpen = false">
                        <Pencil class="h-4 w-4 mr-1.5" /> Edit
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- ── Archive Confirm ────────────────────────────────────────────── -->
        <AlertDialog v-model:open="isArchiveOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Archive Branch</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to archive
                        <strong>{{ archiveBranch?.name }}</strong>?
                        It will be moved to the archive and removed from employee dropdowns.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <Button variant="outline" @click="cancelArchive">Cancel</Button>
                    <Button variant="destructive" @click="confirmArchive">Archive</Button>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

    </ShopLayout>
</template>
