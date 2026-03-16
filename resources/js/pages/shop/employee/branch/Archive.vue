<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import { type BreadcrumbItem } from '@/types'
import { ref, computed, onMounted } from 'vue'

// shadcn
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

// icons
import { ArchiveRestore, ArrowLeft, Archive, Building2 } from 'lucide-vue-next'

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

interface ArchivedBranch {
    id: number
    branch_code: string
    name: string
    phone: string | null
    email: string | null
    manager_name: string | null
    address: string | null
    opened_at: string | null
    status: 'Active' | 'Inactive'
    deleted_at: string
}

// ─── Props ────────────────────────────────────────────────────────────────────

const { branches } = defineProps<{
    branches: { data: ArchivedBranch[]; total: number }
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
    { title: 'Archive', href: '/shop/branch/archive' },
]

// ─── Search ───────────────────────────────────────────────────────────────────

const searchQuery = ref('')

const filtered = computed(() =>
    branches.data.filter((b) => {
        const q = searchQuery.value.toLowerCase()
        return (
            b.name.toLowerCase().includes(q) ||
            b.branch_code.toLowerCase().includes(q) ||
            (b.manager_name ?? '').toLowerCase().includes(q) ||
            (b.address ?? '').toLowerCase().includes(q)
        )
    })
)

// ─── Helpers ──────────────────────────────────────────────────────────────────

const formatDate = (val: string | null) =>
    val
        ? new Date(val).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })
        : '—'

// ─── Restore ─────────────────────────────────────────────────────────────────

const branchToRestore  = ref<ArchivedBranch | null>(null)
const isRestoreOpen    = ref(false)

function openRestore(branch: ArchivedBranch) {
    branchToRestore.value = branch
    isRestoreOpen.value   = true
}

function cancelRestore() {
    isRestoreOpen.value = false
    setTimeout(() => { branchToRestore.value = null }, 200)
}

function confirmRestore() {
    if (!branchToRestore.value) return
    isRestoreOpen.value = false
    router.post(`/shop/branch/${branchToRestore.value.id}/restore`, {}, {
        preserveScroll: true,
        onSuccess: () => { branchToRestore.value = null },
    })
}
</script>

<template>
    <Head title="Archived Branches" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Archived Branches">
        <div class="px-6 space-y-6">

            <!-- Back button -->
            <div>
                <Button type="button" variant="outline" @click="router.visit('/shop/branch')">
                    <ArrowLeft class="h-4 w-4 mr-2" />
                    Back to Branches
                </Button>
            </div>

            <Card>
                <CardHeader class="flex flex-row justify-between items-center gap-2 flex-wrap">
                    <div class="flex items-center gap-2">
                        <Archive class="h-5 w-5 text-muted-foreground" />
                        <CardTitle>Archived Branches</CardTitle>
                        <span class="text-xs text-muted-foreground bg-muted px-2 py-0.5 rounded-full">
                            {{ branches.total }} total
                        </span>
                    </div>
                    <Input v-model="searchQuery" placeholder="Search archived branches..." class="w-52" />
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
                                    <TableHead>Status</TableHead>
                                    <TableHead>Archived On</TableHead>
                                    <TableHead class="text-center">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="filtered.length === 0">
                                    <TableCell colspan="10" class="text-center text-muted-foreground py-12">
                                        <div class="flex flex-col items-center gap-2">
                                            <Archive class="h-8 w-8 text-muted-foreground/40" />
                                            <p>No archived branches found.</p>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-for="b in filtered" :key="b.id" class="opacity-75">
                                    <TableCell class="font-mono text-xs whitespace-nowrap">{{ b.branch_code }}</TableCell>
                                    <TableCell class="font-medium whitespace-nowrap">{{ b.name }}</TableCell>
                                    <TableCell class="whitespace-nowrap">{{ b.manager_name ?? '—' }}</TableCell>
                                    <TableCell class="whitespace-nowrap">{{ b.phone ?? '—' }}</TableCell>
                                    <TableCell class="whitespace-nowrap">{{ b.email ?? '—' }}</TableCell>
                                    <TableCell class="max-w-[180px]">
                                        <span class="block truncate text-xs text-muted-foreground"
                                            :title="b.address ?? ''">
                                            {{ b.address ?? '—' }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="whitespace-nowrap">{{ formatDate(b.opened_at) }}</TableCell>
                                    <TableCell>
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full text-white"
                                            :class="b.status === 'Active' ? 'bg-green-500' : 'bg-red-500'">
                                            {{ b.status }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="whitespace-nowrap text-sm text-muted-foreground">
                                        {{ formatDate(b.deleted_at) }}
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Button type="button" size="icon" variant="ghost"
                                            title="Restore branch" @click="openRestore(b)">
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

        <!-- Restore confirm dialog -->
        <AlertDialog :open="isRestoreOpen" @update:open="val => { if (!val) cancelRestore() }">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Restore Branch</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to restore
                        <strong>{{ branchToRestore?.name }}</strong>?
                        It will be moved back to the active branch list and appear in employee dropdowns.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <Button variant="outline" @click="cancelRestore">Cancel</Button>
                    <Button @click="confirmRestore">Restore</Button>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

    </ShopLayout>
</template>
