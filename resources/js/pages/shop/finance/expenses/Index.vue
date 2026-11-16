<script setup lang="ts">
import {
    BulkActionButton,
    DataTable,
    DataTableToolbar,
    KpiTiles,
    RowActions,
    type DataTableHeader,
} from '@/components/data-table';
import ActionsMenu from '@/components/shop/ActionsMenu.vue';
import PageHeader from '@/components/shop/PageHeader.vue';
import { useCsvImport } from '@/composables/useCsvImport';
import { usePaginatorTable } from '@/composables/useDataTable';
import { usePermissions } from '@/composables/usePermissions';
import { useUndoToast } from '@/composables/useUndoToast';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import { Button } from '@/components/ui/button';
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
    Archive,
    Download,
    Loader2,
    Pencil,
    Plus,
    Tag,
    Trash2,
    TrendingDown,
    Upload,
} from 'lucide-vue-next';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Category {
    id: number;
    name: string;
    description: string | null;
}

interface Expense {
    id: number;
    public_id: string;
    title: string;
    amount: string;
    expense_date: string;
    description: string | null;
    reference_number: string | null;
    category: Category | null;
    creator: { id: number; name: string } | null;
}

interface PaginatedExpenses {
    data: Expense[];
    current_page: number;
    last_page: number;
    per_page?: number;
    total: number;
    from: number | null;
    to: number | null;
}

interface Stats {
    total_expenses: number;
    this_month_expenses: number;
    total_count: number;
    this_month_count: number;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    expenses: PaginatedExpenses;
    stats: Stats;
    categories: Category[];
    filters: {
        search?: string;
        category_id?: string;
        date_from?: string;
        date_to?: string;
    };
}>();

// ─── RBAC ─────────────────────────────────────────────────────────────────────

const { isOwner, can } = usePermissions();
const base = computed(() => (isOwner.value ? '/shop' : '/staff'));

const {
    input: csvInput,
    importing,
    choose: chooseCsv,
    onFileChange: onCsvChange,
} = useCsvImport(() => `${base.value}/import/expenses`);

// ─── Flash toast ──────────────────────────────────────────────────────────────

const page = usePage<AppPageProps>();

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
    { title: 'Finance', href: `${base.value}/finance` },
    { title: 'Expenses', href: `${base.value}/finance/expenses` },
];

// ─── Filters ──────────────────────────────────────────────────────────────────

const search = ref(props.filters.search ?? '');
const categoryId = ref(props.filters.category_id ?? 'all');
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');

function applyFilters() {
    const catId =
        categoryId.value && categoryId.value !== 'all'
            ? categoryId.value
            : undefined;
    router.get(
        `${base.value}/finance/expenses`,
        {
            search: search.value || undefined,
            category_id: catId,
            date_from: dateFrom.value || undefined,
            date_to: dateTo.value || undefined,
        },
        { replace: true },
    );
}

function clearFilters() {
    search.value = '';
    categoryId.value = 'all';
    dateFrom.value = '';
    dateTo.value = '';
    router.get(`${base.value}/finance/expenses`, {}, { replace: true });
}

// ─── Create Dialog ────────────────────────────────────────────────────────────

const isCreateOpen = ref(false);
const creating = ref(false);
const createErrors = computed(
    () => page.props.errors as Record<string, string>,
);

const createForm = ref({
    title: '',
    amount: '',
    expense_date: new Date().toISOString().slice(0, 10),
    category_id: 'none',
    description: '',
    reference_number: '',
});

function openCreate() {
    createForm.value = {
        title: '',
        amount: '',
        expense_date: new Date().toISOString().slice(0, 10),
        category_id: 'none',
        description: '',
        reference_number: '',
    };
    isCreateOpen.value = true;
}

function submitCreate() {
    creating.value = true;
    const payload = {
        ...createForm.value,
        category_id:
            createForm.value.category_id === 'none'
                ? ''
                : createForm.value.category_id,
    };
    router.post(`${base.value}/finance/expenses`, payload, {
        preserveScroll: true,
        onSuccess: () => {
            isCreateOpen.value = false;
        },
        onFinish: () => {
            creating.value = false;
        },
    });
}

// ─── Edit Dialog ──────────────────────────────────────────────────────────────

const isEditOpen = ref(false);
const editing = ref(false);
const editingExpense = ref<Expense | null>(null);

const editForm = ref({
    title: '',
    amount: '',
    expense_date: '',
    category_id: 'none',
    description: '',
    reference_number: '',
});

function openEdit(exp: Expense) {
    editingExpense.value = exp;
    editForm.value = {
        title: exp.title,
        amount: exp.amount,
        expense_date: exp.expense_date,
        category_id: exp.category?.id?.toString() ?? 'none',
        description: exp.description ?? '',
        reference_number: exp.reference_number ?? '',
    };
    isEditOpen.value = true;
}

function submitEdit() {
    if (!editingExpense.value) return;
    editing.value = true;
    const payload = {
        ...editForm.value,
        category_id:
            editForm.value.category_id === 'none'
                ? ''
                : editForm.value.category_id,
    };
    router.put(
        `${base.value}/finance/expenses/${editingExpense.value.public_id}`,
        payload,
        {
            preserveScroll: true,
            onSuccess: () => {
                isEditOpen.value = false;
            },
            onFinish: () => {
                editing.value = false;
            },
        },
    );
}

// ─── Delete Dialog ────────────────────────────────────────────────────────────

const { undoable } = useUndoToast();

// Archiving can be undone, so it happens at once and a toast offers Undo for a few seconds.
function archiveExpense(exp: Expense) {
    router.delete(`${base.value}/finance/expenses/${exp.public_id}`, {
        preserveScroll: true,
        onSuccess: () =>
            undoable(`${exp.title} archived.`, () =>
                router.post(
                    `${base.value}/finance/expenses/${exp.public_id}/restore`,
                    {},
                    { preserveScroll: true },
                ),
            ),
    });
}

// ─── Category Dialog ──────────────────────────────────────────────────────────

const isCategoryOpen = ref(false);
const savingCategory = ref(false);
const categoryForm = ref({ name: '', description: '' });
const categoryErrors = computed(
    () => page.props.errors as Record<string, string>,
);

function openCategories() {
    categoryForm.value = { name: '', description: '' };
    isCategoryOpen.value = true;
}

function submitCategory() {
    savingCategory.value = true;
    router.post(`${base.value}/finance/categories`, categoryForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            categoryForm.value = { name: '', description: '' };
        },
        onFinish: () => {
            savingCategory.value = false;
        },
    });
}

function deleteCategory(id: number) {
    router.delete(`${base.value}/finance/categories/${id}`, {
        preserveScroll: true,
    });
}

// ─── Table ────────────────────────────────────────────────────────────────────

const { options, loading } = usePaginatorTable(
    () => props.expenses,
    ['expenses'],
);

const hasFilters = computed(
    () =>
        Boolean(props.filters.search) ||
        Boolean(props.filters.category_id) ||
        Boolean(props.filters.date_from) ||
        Boolean(props.filters.date_to),
);

const headers: DataTableHeader[] = [
    { text: 'Title', value: 'title', sortable: true },
    { text: 'Category', value: 'category', sortable: true },
    { text: 'Date', value: 'expense_date', sortable: true, width: 140 },
    { text: 'Recorded by', value: 'creator', sortable: true },
    {
        text: 'Amount',
        value: 'amount',
        sortable: true,
        align: 'right',
        width: 140,
    },
    { text: 'Actions', value: 'actions', align: 'center', width: 120 },
];

// ─── Helpers ──────────────────────────────────────────────────────────────────

function currency(val: string | number | null): string {
    const num = typeof val === 'string' ? parseFloat(val) : (val ?? 0);
    return `₱${num.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
}

function formatDate(val: string): string {
    return new Date(val + 'T00:00:00').toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

const selected = ref<Array<string | number>>([]);
</script>

<template>
    <Head title="Expenses" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Finance">
        <div class="space-y-6 px-6">
            <!-- ── Header ──────────────────────────────────────── -->
            <PageHeader
                title="Expense Management"
                description="Track and manage all operational expenses for your shop."
            >
                <ActionsMenu
                    label="Import / export"
                    :items="[
                        {
                            label: 'Export CSV',
                            icon: Download,
                            href: `${isOwner ? '/shop' : '/staff'}/export/expenses`,
                        },
                        {
                            label: importing ? 'Importing...' : 'Import CSV',
                            icon: Upload,
                            onSelect: chooseCsv,
                            disabled: importing,
                            hidden: !(
                                can('Finance Management', 'create') || isOwner
                            ),
                        },
                        {
                            label: 'Categories',
                            icon: Tag,
                            onSelect: openCategories,
                        },
                        {
                            label: 'Archived expenses',
                            icon: Archive,
                            onSelect: () =>
                                router.visit(
                                    `${base}/finance/expenses/archive`,
                                ),
                            separator: true,
                            hidden: !(
                                can('Finance Management', 'archive') || isOwner
                            ),
                        },
                    ]"
                />
                <Button
                    v-if="can('Finance Management', 'create') || isOwner"
                    class="h-11"
                    @click="openCreate"
                >
                    <Plus class="mr-2 h-4 w-4" /> Add Expense
                </Button>
                <input
                    ref="csvInput"
                    type="file"
                    accept=".csv,text/csv"
                    class="hidden"
                    @change="onCsvChange"
                />
            </PageHeader>

            <!-- ── Stats ───────────────────────────────────────── -->
            <KpiTiles
                :tiles="[
                    {
                        label: 'Total Expenses',
                        value: currency(stats.total_expenses),
                        icon: TrendingDown,
                    },
                    {
                        label: 'This Month',
                        value: currency(stats.this_month_expenses),
                        icon: TrendingDown,
                    },
                    { label: 'Total Records', value: stats.total_count },
                    { label: 'This Month', value: stats.this_month_count },
                ]"
            />

            <!-- ── Table ───────────────────────────────────────── -->
            <DataTable
                v-model:options="options"
                v-model:selected="selected"
                item-key="public_id"
                :headers="headers"
                :items="expenses.data"
                :total="expenses.total"
                :loading="loading"
                noun="expenses"
                :empty-icon="TrendingDown"
                :empty-message="
                    hasFilters
                        ? 'No expenses match the current filters.'
                        : 'No expenses found. Click “Add Expense” to record one.'
                "
                :resettable="hasFilters"
                :min-width="960"
                hide-rows-per-page
                @reset="clearFilters"
            >
                <template #bulk-actions>
                    <BulkActionButton
                        v-if="can('Finance Management', 'archive') || isOwner"
                        resource="expense"
                        action="archive"
                        :ids="selected"
                        noun="expenses"
                        @done="selected = []"
                    />
                </template>

                <template #toolbar>
                    <DataTableToolbar
                        :resettable="hasFilters"
                        @reset="clearFilters"
                        v-model:search="search"
                        search-placeholder="Search title or reference..."
                        search-label="Search expenses"
                        @submit="applyFilters"
                    >
                        <Select v-model="categoryId">
                            <SelectTrigger
                                class="sm:w-48"
                                aria-label="Filter by category"
                            >
                                <SelectValue placeholder="All categories" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all"
                                    >All categories</SelectItem
                                >
                                <SelectItem
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    :value="cat.id.toString()"
                                >
                                    {{ cat.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Input
                            v-model="dateFrom"
                            type="date"
                            class="sm:w-40"
                            aria-label="From date"
                        />
                        <Input
                            v-model="dateTo"
                            type="date"
                            class="sm:w-40"
                            aria-label="To date"
                        />
                        <Button @click="applyFilters">Filter</Button>
                        <Button variant="outline" @click="clearFilters"
                            >Clear</Button
                        >
                    </DataTableToolbar>
                </template>

                <template #item-title="exp">
                    <p class="font-medium">{{ exp.title }}</p>
                    <p
                        v-if="exp.description"
                        class="max-w-[160px] truncate text-xs text-muted-foreground"
                    >
                        {{ exp.description }}
                    </p>
                    <p
                        v-if="exp.reference_number"
                        class="text-xs text-muted-foreground tabular-nums"
                    >
                        Ref: {{ exp.reference_number }}
                    </p>
                </template>

                <template #item-category="exp">
                    <span class="text-muted-foreground">
                        {{ exp.category?.name ?? '—' }}
                    </span>
                </template>

                <template #item-expense_date="exp">
                    <span class="whitespace-nowrap text-muted-foreground">
                        {{ formatDate(exp.expense_date) }}
                    </span>
                </template>

                <template #item-creator="exp">
                    <span class="text-xs text-muted-foreground">
                        {{ exp.creator?.name ?? '—' }}
                    </span>
                </template>

                <template #item-amount="exp">
                    <span class="font-semibold whitespace-nowrap text-red-600">
                        {{ currency(exp.amount) }}
                    </span>
                </template>

                <template #item-actions="exp">
                    <RowActions
                        :label="exp.title"
                        :actions="[
                            {
                                label: 'Edit',
                                icon: Pencil,
                                show:
                                    can('Finance Management', 'update') ||
                                    isOwner,
                                onSelect: () => openEdit(exp),
                            },
                            {
                                label: 'Archive',
                                icon: Trash2,
                                destructive: true,
                                show:
                                    can('Finance Management', 'archive') ||
                                    isOwner,
                                onSelect: () => archiveExpense(exp),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>

        <!-- ── Create Dialog ────────────────────────────────────── -->
        <Dialog v-model:open="isCreateOpen">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle>Add Expense</DialogTitle>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium"
                            >Title <span class="text-red-500">*</span></label
                        >
                        <Input
                            v-model="createForm.title"
                            placeholder="e.g. Monthly Rent"
                            :class="{ 'border-red-500': createErrors.title }"
                        />
                        <p
                            v-if="createErrors.title"
                            class="text-xs text-red-500"
                        >
                            {{ createErrors.title }}
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-sm font-medium"
                                >Amount (₱)
                                <span class="text-red-500">*</span></label
                            >
                            <Input
                                v-model="createForm.amount"
                                type="number"
                                min="0.01"
                                step="0.01"
                                placeholder="0.00"
                                :class="{
                                    'border-red-500': createErrors.amount,
                                }"
                            />
                            <p
                                v-if="createErrors.amount"
                                class="text-xs text-red-500"
                            >
                                {{ createErrors.amount }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-medium"
                                >Date <span class="text-red-500">*</span></label
                            >
                            <Input
                                v-model="createForm.expense_date"
                                type="date"
                                :class="{
                                    'border-red-500': createErrors.expense_date,
                                }"
                            />
                            <p
                                v-if="createErrors.expense_date"
                                class="text-xs text-red-500"
                            >
                                {{ createErrors.expense_date }}
                            </p>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Category</label>
                        <Select v-model="createForm.category_id">
                            <SelectTrigger>
                                <SelectValue
                                    placeholder="Select category (optional)"
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none"
                                    >No category</SelectItem
                                >
                                <SelectItem
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    :value="cat.id.toString()"
                                >
                                    {{ cat.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Reference No.</label>
                        <Input
                            v-model="createForm.reference_number"
                            placeholder="e.g. OR-001 (optional)"
                        />
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Description</label>
                        <Input
                            v-model="createForm.description"
                            placeholder="Additional notes (optional)"
                        />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isCreateOpen = false"
                        >Cancel</Button
                    >
                    <Button :disabled="creating" @click="submitCreate">
                        <Loader2
                            v-if="creating"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
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
                        <label class="text-sm font-medium"
                            >Title <span class="text-red-500">*</span></label
                        >
                        <Input
                            v-model="editForm.title"
                            placeholder="e.g. Monthly Rent"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-sm font-medium"
                                >Amount (₱)
                                <span class="text-red-500">*</span></label
                            >
                            <Input
                                v-model="editForm.amount"
                                type="number"
                                min="0.01"
                                step="0.01"
                                placeholder="0.00"
                            />
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-medium"
                                >Date <span class="text-red-500">*</span></label
                            >
                            <Input
                                v-model="editForm.expense_date"
                                type="date"
                            />
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Category</label>
                        <Select v-model="editForm.category_id">
                            <SelectTrigger>
                                <SelectValue
                                    placeholder="Select category (optional)"
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none"
                                    >No category</SelectItem
                                >
                                <SelectItem
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    :value="cat.id.toString()"
                                >
                                    {{ cat.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Reference No.</label>
                        <Input
                            v-model="editForm.reference_number"
                            placeholder="e.g. OR-001 (optional)"
                        />
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Description</label>
                        <Input
                            v-model="editForm.description"
                            placeholder="Additional notes (optional)"
                        />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isEditOpen = false"
                        >Cancel</Button
                    >
                    <Button :disabled="editing" @click="submitEdit">
                        <Loader2
                            v-if="editing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        {{ editing ? 'Saving...' : 'Update' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
        <!-- ── Categories Dialog ────────────────────────────────── -->
        <Dialog v-model:open="isCategoryOpen">
            <DialogContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Manage Categories</DialogTitle>
                </DialogHeader>
                <div class="space-y-4">
                    <!-- Add new category -->
                    <div
                        v-if="can('Finance Management', 'create') || isOwner"
                        class="space-y-2"
                    >
                        <p class="text-sm font-medium">Add Category</p>
                        <Input
                            v-model="categoryForm.name"
                            placeholder="Category name"
                            :class="{
                                'border-red-500': categoryErrors.category_name,
                            }"
                        />
                        <p
                            v-if="categoryErrors.category_name"
                            class="text-xs text-red-500"
                        >
                            {{ categoryErrors.category_name }}
                        </p>
                        <Input
                            v-model="categoryForm.description"
                            placeholder="Description (optional)"
                        />
                        <Button
                            size="sm"
                            :disabled="
                                savingCategory || !categoryForm.name.trim()
                            "
                            @click="submitCategory"
                        >
                            <Loader2
                                v-if="savingCategory"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            Add
                        </Button>
                    </div>

                    <div
                        v-if="categories.length"
                        class="space-y-2 border-t pt-4"
                    >
                        <p class="text-sm font-medium">Existing Categories</p>
                        <ul class="max-h-48 space-y-1 overflow-y-auto">
                            <li
                                v-for="cat in categories"
                                :key="cat.id"
                                class="flex items-center justify-between rounded px-2 py-1.5 hover:bg-muted/50"
                            >
                                <div>
                                    <p class="text-sm font-medium">
                                        {{ cat.name }}
                                    </p>
                                    <p
                                        v-if="cat.description"
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{ cat.description }}
                                    </p>
                                </div>
                                <Button
                                    v-if="
                                        can('Finance Management', 'archive') ||
                                        isOwner
                                    "
                                    size="icon"
                                    variant="ghost"
                                    @click="deleteCategory(cat.id)"
                                >
                                    <Trash2 class="h-3.5 w-3.5 text-red-500" />
                                </Button>
                            </li>
                        </ul>
                    </div>

                    <p
                        v-else
                        class="py-4 text-center text-sm text-muted-foreground"
                    >
                        No categories yet.
                    </p>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isCategoryOpen = false"
                        >Close</Button
                    >
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </ShopLayout>
</template>
