<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import { ref, computed } from 'vue'

// shadcn components
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

// icons
import { ArchiveRestore, ArrowLeft, Archive } from 'lucide-vue-next'

// AlertDialog
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogFooter,
} from '@/components/ui/alert-dialog'

// ─── Types ────────────────────────────────────────────────────────────────────

interface ArchivedEmployee {
    id: number
    employee_id_ref: number
    employee_id: string
    first_name: string
    last_name: string
    position: string
    branch_name: string | null
    hire_date: string
    status: 'Active' | 'Inactive' | 'Archived'
    archived_at: string
    original_created_at: string | null
}

// ─── Props ────────────────────────────────────────────────────────────────────

const { archived } = defineProps<{ archived: ArchivedEmployee[] }>()

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Employee Management', href: '/shop/employee' },
    { title: 'Archive', href: '/shop/employee/archive' },
]

// ─── Search ───────────────────────────────────────────────────────────────────

const searchQuery = ref('')

const filtered = computed(() =>
    archived.filter((emp) => {
        const fullName = `${emp.first_name} ${emp.last_name}`.toLowerCase()
        const q = searchQuery.value.toLowerCase()
        return (
            fullName.includes(q) ||
            emp.employee_id.toLowerCase().includes(q) ||
            emp.position.toLowerCase().includes(q) ||
            (emp.branch_name ?? '').toLowerCase().includes(q)
        )
    })
)

// ─── Selected rows ────────────────────────────────────────────────────────────

const selected = ref<number[]>([])

const allSelected = computed(() =>
    filtered.value.length > 0 && filtered.value.every(e => selected.value.includes(e.employee_id_ref))
)

function toggleAll() {
    if (allSelected.value) {
        selected.value = []
    } else {
        selected.value = filtered.value.map(e => e.employee_id_ref)
    }
}

function toggleOne(id: number) {
    if (selected.value.includes(id)) {
        selected.value = selected.value.filter(i => i !== id)
    } else {
        selected.value.push(id)
    }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

const formatDate = (val: string) =>
    new Date(val).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })

// ─── Single restore ───────────────────────────────────────────────────────────

function restore(employeeIdRef: number) {
    router.post(`/shop/employee/${employeeIdRef}/restore`, {}, { preserveScroll: true })
}

// ─── Bulk restore ─────────────────────────────────────────────────────────────

const showBulkConfirm = ref(false)

function confirmBulk() {
    if (selected.value.length === 0) return
    showBulkConfirm.value = true
}

function executeBulkRestore() {
    showBulkConfirm.value = false
    router.post(
        '/shop/employee/bulk-restore',
        { ids: selected.value },
        {
            preserveScroll: true,
            onSuccess: () => { selected.value = [] },
        }
    )
}
</script>

<template>

    <Head title="Archived Employees" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Archived Employees">

        <div class="px-6 space-y-6">

            <!-- Back button -->
            <div>
                <Button type="button" variant="outline" @click="router.visit('/shop/employee')">
                    <ArrowLeft class="h-4 w-4 mr-2" />
                    Back to Employees
                </Button>
            </div>

            <Card>
                <CardHeader class="flex flex-row justify-between items-center gap-2 flex-wrap">
                    <div class="flex items-center gap-2">
                        <Archive class="h-5 w-5 text-muted-foreground" />
                        <CardTitle>Archived Employees</CardTitle>
                        <span class="text-xs text-muted-foreground bg-muted px-2 py-0.5 rounded-full">
                            {{ archived.length }} total
                        </span>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <Input v-model="searchQuery" placeholder="Search archived..." class="w-52" />

                        <template v-if="selected.length > 0">
                            <span class="text-xs text-muted-foreground">{{ selected.length }} selected</span>
                            <Button
                                type="button"
                                variant="outline"
                                class="text-green-600 border-green-300 hover:bg-green-50"
                                @click="confirmBulk"
                            >
                                <ArchiveRestore class="h-4 w-4 mr-1.5" />
                                Restore Selected
                            </Button>
                        </template>
                    </div>
                </CardHeader>

                <CardContent>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-10">
                                        <input
                                            type="checkbox"
                                            :checked="allSelected"
                                            @change="toggleAll"
                                            class="rounded border-gray-300"
                                        />
                                    </TableHead>
                                    <TableHead>Employee ID</TableHead>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Position</TableHead>
                                    <TableHead>Branch</TableHead>
                                    <TableHead>Hire Date</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Archived On</TableHead>
                                    <TableHead class="text-center">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="filtered.length === 0">
                                    <TableCell colspan="9" class="text-center text-muted-foreground py-12">
                                        <div class="flex flex-col items-center gap-2">
                                            <Archive class="h-8 w-8 text-muted-foreground/40" />
                                            <p>No archived employees found.</p>
                                        </div>
                                    </TableCell>
                                </TableRow>

                                <TableRow v-for="emp in filtered" :key="emp.id" class="opacity-75">
                                    <TableCell>
                                        <input
                                            type="checkbox"
                                            :checked="selected.includes(emp.employee_id_ref)"
                                            @change="toggleOne(emp.employee_id_ref)"
                                            class="rounded border-gray-300"
                                        />
                                    </TableCell>
                                    <TableCell class="font-mono text-xs whitespace-nowrap">{{ emp.employee_id }}</TableCell>
                                    <TableCell class="font-medium whitespace-nowrap">{{ emp.first_name }} {{ emp.last_name }}</TableCell>
                                    <TableCell class="whitespace-nowrap">{{ emp.position }}</TableCell>
                                    <TableCell class="whitespace-nowrap">{{ emp.branch_name ?? '—' }}</TableCell>
                                    <TableCell class="whitespace-nowrap">{{ formatDate(emp.hire_date) }}</TableCell>
                                    <TableCell>
                                        <span
                                            class="px-2 py-0.5 text-xs font-semibold rounded-full text-white"
                                            :class="{
                                                'bg-green-500': emp.status === 'Active',
                                                'bg-red-500': emp.status === 'Inactive',
                                                'bg-amber-500': emp.status === 'Archived',
                                            }"
                                        >
                                            {{ emp.status }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="whitespace-nowrap text-sm text-muted-foreground">
                                        {{ formatDate(emp.archived_at) }}
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Button
                                            type="button"
                                            size="icon"
                                            variant="ghost"
                                            title="Restore employee"
                                            @click="restore(emp.employee_id_ref)"
                                        >
                                            <ArchiveRestore class="h-4 w-4 text-green-500" />
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

        </div>

        <!-- Bulk restore confirm dialog -->
        <AlertDialog :open="showBulkConfirm" @update:open="val => { if (!val) showBulkConfirm = false }">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Restore Selected Employees</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to restore <strong>{{ selected.length }}</strong> employee(s)?
                        They will be moved back to the active employee list.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <Button variant="outline" @click="showBulkConfirm = false">Cancel</Button>
                    <Button @click="executeBulkRestore">Restore</Button>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

    </ShopLayout>
</template>
