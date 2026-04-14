<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import { type BreadcrumbItem, type AppPageProps } from '@/types'
import { usePermissions } from '@/composables/usePermissions'

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
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
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select'
import {
    TrendingDown, Plus, Pencil, Trash2, Loader2,
    ChevronLeft, ChevronRight, Tag, Archive,
} from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface Category {
    id: number
    name: string
    description: string | null
}

interface Expense {
    id: number
    title: string
    amount: string
    expense_date: string
    description: string | null
    reference_number: string | null
    category: Category | null
    creator: { id: number; name: string } | null
}

interface PaginatedExpenses {
    data: Expense[]
    current_page: number
    last_page: number
    total: number
    from: number | null
    to: number | null
}

interface Stats {
    total_expenses: number
    this_month_expenses: number
    total_count: number
    this_month_count: number
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    expenses: PaginatedExpenses
    stats: Stats
    categories: Category[]
    filters: {
        search?: string
        category_id?: string
        date_from?: string
        date_to?: string
    }
}>()

// ─── RBAC ─────────────────────────────────────────────────────────────────────

const { isOwner, can } = usePermissions()
const base = computed(() => isOwner.value ? '/shop' : '/staff')

// ─── Flash toast ──────────────────────────────────────────────────────────────

const page = usePage<AppPageProps>()

onMounted(() => {
    const flashToast = page.props.toast as { type: string; message: string } | undefined
    if (!flashToast) return
    switch (flashToast.type) {
        case 'success': toast.success(flashToast.message); break
        case 'error':   toast.error(flashToast.message);   break
        default:        toast(flashToast.message)
    }
})

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Finance', href: `${base.value}/finance` },
    { title: 'Expenses', href: `${base.value}/finance/expenses` },
]

// ─── Filters ──────────────────────────────────────────────────────────────────

const search      = ref(props.filters.search ?? '')
const categoryId  = ref(props.filters.category_id ?? 'all')
const dateFrom    = ref(props.filters.date_from ?? '')
const dateTo      = ref(props.filters.date_to ?? '')

function applyFilters() {
    const catId = (categoryId.value && categoryId.value !== 'all') ? categoryId.value : undefined
    router.get(
        `${base.value}/finance/expenses`,
        {
            search:      search.value || undefined,
            category_id: catId,
            date_from:   dateFrom.value || undefined,
            date_to:     dateTo.value || undefined,
        },
        { replace: true },
    )
}

function clearFilters() {
    search.value     = ''
    categoryId.value = 'all'
    dateFrom.value   = ''
    dateTo.value     = ''
    router.get(`${base.value}/finance/expenses`, {}, { replace: true })
}

// ─── Create Dialog ────────────────────────────────────────────────────────────

const isCreateOpen = ref(false)
const creating     = ref(false)
const createErrors = computed(() => page.props.errors as Record<string, string>)

const createForm = ref({
    title:            '',
    amount:           '',
    expense_date:     new Date().toISOString().slice(0, 10),
    category_id:      'none',
    description:      '',
    reference_number: '',
})

function openCreate() {
    createForm.value = {
        title:            '',
        amount:           '',
        expense_date:     new Date().toISOString().slice(0, 10),
        category_id:      'none',
        description:      '',
        reference_number: '',
    }
    isCreateOpen.value = true
}

function submitCreate() {
    creating.value = true
    const payload = { ...createForm.value, category_id: createForm.value.category_id === 'none' ? '' : createForm.value.category_id }
    router.post(`${base.value}/finance/expenses`, payload, {
        preserveScroll: true,
        onSuccess: () => { isCreateOpen.value = false },
        onFinish: () => { creating.value = false },
    })
}

// ─── Edit Dialog ──────────────────────────────────────────────────────────────

const isEditOpen    = ref(false)
const editing       = ref(false)
const editingExpense = ref<Expense | null>(null)

const editForm = ref({
    title:            '',
    amount:           '',
    expense_date:     '',
    category_id:      'none',
    description:      '',
    reference_number: '',
})

function openEdit(exp: Expense) {
    editingExpense.value = exp
    editForm.value = {
        title:            exp.title,
        amount:           exp.amount,
        expense_date:     exp.expense_date,
        category_id:      exp.category?.id?.toString() ?? 'none',
        description:      exp.description ?? '',
        reference_number: exp.reference_number ?? '',
    }
    isEditOpen.value = true
}

function submitEdit() {
    if (!editingExpense.value) return
    editing.value = true
    const payload = { ...editForm.value, category_id: editForm.value.category_id === 'none' ? '' : editForm.value.category_id }
    router.put(`${base.value}/finance/expenses/${editingExpense.value.id}`, payload, {
        preserveScroll: true,
        onSuccess: () => { isEditOpen.value = false },
        onFinish: () => { editing.value = false },
    })
}

// ─── Delete Dialog ────────────────────────────────────────────────────────────

const isDeleteOpen    = ref(false)
const deletingExpense = ref<Expense | null>(null)

function openDelete(exp: Expense) {
    deletingExpense.value = exp
    isDeleteOpen.value    = true
}

function confirmDelete() {
    if (!deletingExpense.value) return
    router.delete(`${base.value}/finance/expenses/${deletingExpense.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteOpen.value    = false
            deletingExpense.value = null
        },
    })
}

// ─── Category Dialog ──────────────────────────────────────────────────────────

const isCategoryOpen  = ref(false)
const savingCategory  = ref(false)
const categoryForm    = ref({ name: '', description: '' })
const categoryErrors  = computed(() => page.props.errors as Record<string, string>)

function openCategories() {
    categoryForm.value  = { name: '', description: '' }
    isCategoryOpen.value = true
}

function submitCategory() {
    savingCategory.value = true
    router.post(`${base.value}/finance/categories`, categoryForm.value, {
        preserveScroll: true,
        onSuccess: () => { categoryForm.value = { name: '', description: '' } },
        onFinish: () => { savingCategory.value = false },
    })
}

function deleteCategory(id: number) {
    router.delete(`${base.value}/finance/categories/${id}`, { preserveScroll: true })
}

// ─── Pagination ───────────────────────────────────────────────────────────────

function goToPage(p: number) {
    router.get(
        `${base.value}/finance/expenses`,
        { page: p, ...props.filters },
        { preserveScroll: true },
    )
}

const visiblePages = computed(() => {
    const pages: number[] = []
    const current = props.expenses.current_page
    const total   = props.expenses.last_page
    for (let i = Math.max(1, current - 2); i <= Math.min(total, current + 2); i++) {
        pages.push(i)
    }
    return pages
})

// ─── Helpers ──────────────────────────────────────────────────────────────────

function currency(val: string | number | null): string {
    const num = typeof val === 'string' ? parseFloat(val) : (val ?? 0)
    return `₱${num.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
}

function formatDate(val: string): string {
    return new Date(val + 'T00:00:00').toLocaleDateString('en-PH', {
        year: 'numeric', month: 'short', day: 'numeric',
    })
}
</script>

<template>
    <Head title="Expenses" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Finance">
        <div class="px-6 space-y-6">

            <!-- ── Header ──────────────────────────────────────── -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold">Expense Management</h2>
                    <p class="text-sm text-muted-foreground">
                        Track and manage all operational expenses for your shop.
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <Button variant="outline" size="sm" @click="openCategories">
                        <Tag class="h-4 w-4 mr-2" /> Categories
                    </Button>
                    <Button v-if="can('Finance Management', 'archive') || isOwner" variant="outline" size="sm"
                        @click="router.visit(`${base}/finance/expenses/archive`)">
                        <Archive class="h-4 w-4 mr-2" /> Archive
                    </Button>
                    <Button v-if="can('Finance Management', 'create') || isOwner" @click="openCreate">
                        <Plus class="h-4 w-4 mr-2" /> Add Expense
                    </Button>
                </div>
            </div>

            <!-- ── Stats ───────────────────────────────────────── -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <Card>
                    <CardContent class="pt-4 pb-4 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-red-100 flex items-center justify-center">
                            <TrendingDown class="h-5 w-5 text-red-600" />
                        </div>
                        <div>
                            <p class="text-lg font-bold">{{ currency(stats.total_expenses) }}</p>
                            <p class="text-xs text-muted-foreground">Total Expenses</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-4 pb-4 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-orange-100 flex items-center justify-center">
                            <TrendingDown class="h-5 w-5 text-orange-600" />
                        </div>
                        <div>
                            <p class="text-lg font-bold">{{ currency(stats.this_month_expenses) }}</p>
                            <p class="text-xs text-muted-foreground">This Month</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-4 pb-4">
                        <p class="text-2xl font-bold">{{ stats.total_count }}</p>
                        <p class="text-xs text-muted-foreground">Total Records</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-4 pb-4">
                        <p class="text-2xl font-bold">{{ stats.this_month_count }}</p>
                        <p class="text-xs text-muted-foreground">This Month</p>
                    </CardContent>
                </Card>
            </div>

            <!-- ── Filters ─────────────────────────────────────── -->
            <div class="flex flex-col sm:flex-row gap-2 flex-wrap">
                <Input v-model="search" placeholder="Search title..." class="sm:w-48" @keyup.enter="applyFilters" />
                <Select v-model="categoryId">
                    <SelectTrigger class="sm:w-48">
                        <SelectValue placeholder="All categories" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All categories</SelectItem>
                        <SelectItem v-for="cat in categories" :key="cat.id" :value="cat.id.toString()">
                            {{ cat.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Input v-model="dateFrom" type="date" class="sm:w-40" />
                <Input v-model="dateTo" type="date" class="sm:w-40" />
                <Button @click="applyFilters">Filter</Button>
                <Button variant="outline" @click="clearFilters">Clear</Button>
            </div>

            <!-- ── Table ───────────────────────────────────────── -->
            <div class="rounded-xl border overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                            <th class="text-left px-4 py-3 font-medium">Title</th>
                            <th class="text-left px-4 py-3 font-medium hidden sm:table-cell">Category</th>
                            <th class="text-left px-4 py-3 font-medium hidden md:table-cell">Date</th>
                            <th class="text-left px-4 py-3 font-medium hidden lg:table-cell">Reference</th>
                            <th class="text-left px-4 py-3 font-medium hidden lg:table-cell">Recorded By</th>
                            <th class="text-right px-4 py-3 font-medium">Amount</th>
                            <th class="text-right px-4 py-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="exp in expenses.data" :key="exp.id"
                            class="border-b last:border-0 hover:bg-muted/20 transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ exp.title }}</p>
                                <p v-if="exp.description" class="text-xs text-muted-foreground truncate max-w-[160px]">
                                    {{ exp.description }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground hidden sm:table-cell">
                                {{ exp.category?.name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground hidden md:table-cell">
                                {{ formatDate(exp.expense_date) }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground hidden lg:table-cell">
                                {{ exp.reference_number ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-muted-foreground hidden lg:table-cell">
                                {{ exp.creator?.name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-red-600">
                                {{ currency(exp.amount) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <Button v-if="can('Finance Management', 'update') || isOwner"
                                        size="icon" variant="ghost" @click="openEdit(exp)">
                                        <Pencil class="h-4 w-4 text-blue-500" />
                                    </Button>
                                    <Button v-if="can('Finance Management', 'archive') || isOwner"
                                        size="icon" variant="ghost" @click="openDelete(exp)">
                                        <Trash2 class="h-4 w-4 text-red-500" />
                                    </Button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="expenses.data.length === 0">
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-muted-foreground">
                                <TrendingDown class="h-8 w-8 mx-auto mb-2 opacity-30" />
                                <p>No expenses found. Click "Add Expense" to record one.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ── Pagination ──────────────────────────────────── -->
            <div v-if="expenses.last_page > 1" class="flex items-center justify-between px-4 py-3">
                <p class="text-xs text-muted-foreground">
                    Showing {{ expenses.from }}–{{ expenses.to }} of {{ expenses.total }}
                </p>
                <div class="flex items-center gap-1">
                    <Button size="icon" variant="outline" :disabled="expenses.current_page === 1"
                        @click="goToPage(expenses.current_page - 1)">
                        <ChevronLeft class="h-4 w-4" />
                    </Button>
                    <Button v-for="p in visiblePages" :key="p" size="icon"
                        :variant="p === expenses.current_page ? 'default' : 'outline'"
                        @click="goToPage(p)">
                        {{ p }}
                    </Button>
                    <Button size="icon" variant="outline" :disabled="expenses.current_page === expenses.last_page"
                        @click="goToPage(expenses.current_page + 1)">
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                </div>
            </div>

        </div>

        <!-- ── Create Dialog ────────────────────────────────────── -->
        <Dialog v-model:open="isCreateOpen">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle>Add Expense</DialogTitle>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Title <span class="text-red-500">*</span></label>
                        <Input v-model="createForm.title" placeholder="e.g. Monthly Rent"
                            :class="{ 'border-red-500': createErrors.title }" />
                        <p v-if="createErrors.title" class="text-xs text-red-500">{{ createErrors.title }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-sm font-medium">Amount (₱) <span class="text-red-500">*</span></label>
                            <Input v-model="createForm.amount" type="number" min="0.01" step="0.01" placeholder="0.00"
                                :class="{ 'border-red-500': createErrors.amount }" />
                            <p v-if="createErrors.amount" class="text-xs text-red-500">{{ createErrors.amount }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-medium">Date <span class="text-red-500">*</span></label>
                            <Input v-model="createForm.expense_date" type="date"
                                :class="{ 'border-red-500': createErrors.expense_date }" />
                            <p v-if="createErrors.expense_date" class="text-xs text-red-500">{{ createErrors.expense_date }}</p>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Category</label>
                        <Select v-model="createForm.category_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select category (optional)" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">No category</SelectItem>
                                <SelectItem v-for="cat in categories" :key="cat.id" :value="cat.id.toString()">
                                    {{ cat.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Reference No.</label>
                        <Input v-model="createForm.reference_number" placeholder="e.g. OR-001 (optional)" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Description</label>
                        <Input v-model="createForm.description" placeholder="Additional notes (optional)" />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isCreateOpen = false">Cancel</Button>
                    <Button :disabled="creating" @click="submitCreate">
                        <Loader2 v-if="creating" class="h-4 w-4 mr-2 animate-spin" />
                        {{ creating ? 'Saving...' : 'Save' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- ── Edit Dialog ──────────────────────────────────────── -->
        <Dialog v-model:open="isEditOpen">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle>Edit Expense</DialogTitle>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Title <span class="text-red-500">*</span></label>
                        <Input v-model="editForm.title" placeholder="e.g. Monthly Rent" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-sm font-medium">Amount (₱) <span class="text-red-500">*</span></label>
                            <Input v-model="editForm.amount" type="number" min="0.01" step="0.01" placeholder="0.00" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-medium">Date <span class="text-red-500">*</span></label>
                            <Input v-model="editForm.expense_date" type="date" />
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Category</label>
                        <Select v-model="editForm.category_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select category (optional)" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">No category</SelectItem>
                                <SelectItem v-for="cat in categories" :key="cat.id" :value="cat.id.toString()">
                                    {{ cat.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Reference No.</label>
                        <Input v-model="editForm.reference_number" placeholder="e.g. OR-001 (optional)" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Description</label>
                        <Input v-model="editForm.description" placeholder="Additional notes (optional)" />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isEditOpen = false">Cancel</Button>
                    <Button :disabled="editing" @click="submitEdit">
                        <Loader2 v-if="editing" class="h-4 w-4 mr-2 animate-spin" />
                        {{ editing ? 'Saving...' : 'Update' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- ── Delete Dialog ────────────────────────────────────── -->
        <AlertDialog v-model:open="isDeleteOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Archive Expense</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to archive
                        <strong>{{ deletingExpense?.title }}</strong>?
                        It can be restored from the archive.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <Button variant="outline" @click="isDeleteOpen = false">Cancel</Button>
                    <Button variant="destructive" @click="confirmDelete">Archive</Button>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <!-- ── Categories Dialog ────────────────────────────────── -->
        <Dialog v-model:open="isCategoryOpen">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Manage Categories</DialogTitle>
                </DialogHeader>
                <div class="space-y-4">
                    <!-- Add new category -->
                    <div v-if="can('Finance Management', 'create') || isOwner" class="space-y-2">
                        <p class="text-sm font-medium">Add Category</p>
                        <Input v-model="categoryForm.name" placeholder="Category name"
                            :class="{ 'border-red-500': categoryErrors.category_name }" />
                        <p v-if="categoryErrors.category_name" class="text-xs text-red-500">
                            {{ categoryErrors.category_name }}
                        </p>
                        <Input v-model="categoryForm.description" placeholder="Description (optional)" />
                        <Button size="sm" :disabled="savingCategory || !categoryForm.name.trim()"
                            @click="submitCategory">
                            <Loader2 v-if="savingCategory" class="h-4 w-4 mr-2 animate-spin" />
                            Add
                        </Button>
                    </div>

                    <div v-if="categories.length" class="border-t pt-4 space-y-2">
                        <p class="text-sm font-medium">Existing Categories</p>
                        <ul class="space-y-1 max-h-48 overflow-y-auto">
                            <li v-for="cat in categories" :key="cat.id"
                                class="flex items-center justify-between px-2 py-1.5 rounded hover:bg-muted/50">
                                <div>
                                    <p class="text-sm font-medium">{{ cat.name }}</p>
                                    <p v-if="cat.description" class="text-xs text-muted-foreground">{{ cat.description }}</p>
                                </div>
                                <Button v-if="can('Finance Management', 'archive') || isOwner"
                                    size="icon" variant="ghost" @click="deleteCategory(cat.id)">
                                    <Trash2 class="h-3.5 w-3.5 text-red-500" />
                                </Button>
                            </li>
                        </ul>
                    </div>

                    <p v-else class="text-sm text-muted-foreground text-center py-4">
                        No categories yet.
                    </p>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isCategoryOpen = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

    </ShopLayout>
</template>
