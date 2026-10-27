<script setup lang="ts">
import {
    DataTable,
    DataTableToolbar,
    RowActions,
    type DataTableHeader,
} from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useDataTable } from '@/composables/useDataTable';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    Check,
    Layers,
    Loader2,
    Pencil,
    Plus,
    Trash2,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

interface Category {
    id: number;
    name: string;
    description: string | null;
}

const { categories: initialCategories } = defineProps<{
    categories: Category[];
}>();

const { isOwner, can } = usePermissions();
const base = computed(() =>
    isOwner.value ? '/shop/inventory' : '/staff/inventory',
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: base.value },
    { title: 'Categories', href: `${base.value}/category` },
];

const categories = ref<Category[]>([...initialCategories]);
const showNewRow = ref(false);
const saveError = ref<string | null>(null);

// New row
const newForm = ref({ name: '', description: '' });
const savingNew = ref(false);

// Edit row
const editingId = ref<number | null>(null);
const editForm = ref({ name: '', description: '' });
const savingEdit = ref(false);
const deletingId = ref<number | null>(null);

const NEW_ROW = -1;

const headers: DataTableHeader[] = [
    { text: 'Name', value: 'name', sortable: true, width: 260 },
    { text: 'Description', value: 'description', sortable: true },
    { text: 'Actions', value: 'actions', align: 'center', width: 130 },
];

const { search, options, pageItems, total, isFiltered, reset } = useDataTable(
    categories,
    {
        searchFields: (cat) => [cat.name, cat.description],
        sortBy: 'name',
    },
);

// The inline "new category" row is shown above the current page.
const tableItems = computed<Category[]>(() =>
    showNewRow.value
        ? [{ id: NEW_ROW, name: '', description: null }, ...pageItems.value]
        : pageItems.value,
);

async function addCategory() {
    if (!newForm.value.name.trim()) {
        saveError.value = 'Name is required.';
        return;
    }

    savingNew.value = true;
    saveError.value = null;

    try {
        const res = await axios.post(`${base.value}/category`, newForm.value);
        categories.value.push(res.data);
        newForm.value = { name: '', description: '' };
        showNewRow.value = false;
        toast.success('Category added successfully.', { autoClose: 3000 });
    } catch (err: any) {
        const msg =
            err.response?.data?.message ??
            err.response?.data?.errors?.name?.[0] ??
            'Failed to add category.';
        saveError.value = msg;
        toast.error(msg, { autoClose: 4000 });
    } finally {
        savingNew.value = false;
    }
}

function startEdit(cat: Category) {
    editingId.value = cat.id;
    editForm.value = { name: cat.name, description: cat.description ?? '' };
    saveError.value = null;
}

async function saveEdit(id: number) {
    if (!editForm.value.name.trim()) {
        saveError.value = 'Name is required.';
        return;
    }

    savingEdit.value = true;
    saveError.value = null;

    try {
        const res = await axios.put(
            `${base.value}/category/${id}`,
            editForm.value,
        );
        const idx = categories.value.findIndex((c) => c.id === id);
        if (idx !== -1) categories.value[idx] = res.data;
        editingId.value = null;
        toast.success('Category updated.', { autoClose: 3000 });
    } catch (err: any) {
        const msg = err.response?.data?.message ?? 'Failed to update category.';
        saveError.value = msg;
        toast.error(msg, { autoClose: 4000 });
    } finally {
        savingEdit.value = false;
    }
}

async function deleteCategory(id: number) {
    deletingId.value = id;
    saveError.value = null;

    try {
        await axios.delete(`${base.value}/category/${id}`);
        categories.value = categories.value.filter((c) => c.id !== id);
        toast.success('Category deleted.', { autoClose: 3000 });
    } catch {
        saveError.value = 'Failed to delete category.';
        toast.error('Failed to delete category.', { autoClose: 4000 });
    } finally {
        deletingId.value = null;
    }
}
</script>

<template>
    <Head title="Inventory Categories" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Inventory Categories">
        <div class="space-y-6 px-6">
            <div
                v-if="saveError"
                role="alert"
                class="flex items-center justify-between rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700"
            >
                <span>{{ saveError }}</span>
                <button
                    type="button"
                    aria-label="Dismiss error"
                    @click="saveError = null"
                >
                    <X
                        class="ml-2 h-3.5 w-3.5 text-red-400 hover:text-red-600"
                    />
                </button>
            </div>

            <DataTable
                v-model:options="options"
                :headers="headers"
                :items="tableItems"
                :total="total"
                noun="categories"
                :empty-icon="Layers"
                :empty-message="
                    isFiltered
                        ? 'No categories match your search.'
                        : 'No categories yet. Click “Add Category” to create one.'
                "
                :resettable="isFiltered"
                :min-width="640"
                @reset="reset"
            >
                <template #toolbar>
                    <DataTableToolbar
                        v-model:search="search"
                        search-placeholder="Search name or description..."
                        search-label="Search categories"
                    >
                        <h2
                            class="order-first flex w-full items-center gap-2 font-semibold sm:w-auto"
                        >
                            <Layers class="h-4 w-4 text-muted-foreground" />
                            Categories
                        </h2>
                        <template #actions>
                            <Button
                                v-if="
                                    isOwner ||
                                    can('Inventory Management', 'archive')
                                "
                                size="sm"
                                variant="outline"
                                class="bg-red-500 text-white hover:bg-red-700 hover:text-white"
                                @click="
                                    router.visit(`${base}/category/archive`)
                                "
                            >
                                <Trash2 class="mr-1.5 h-4 w-4" /> Archive
                            </Button>
                            <Button
                                v-if="
                                    (isOwner ||
                                        can(
                                            'Inventory Management',
                                            'create',
                                        )) &&
                                    !showNewRow
                                "
                                size="sm"
                                @click="
                                    showNewRow = true;
                                    saveError = null;
                                "
                            >
                                <Plus class="mr-1.5 h-4 w-4" /> Add Category
                            </Button>
                        </template>
                    </DataTableToolbar>
                </template>

                <template #item-name="cat">
                    <Input
                        v-if="cat.id === NEW_ROW"
                        v-model="newForm.name"
                        placeholder="Category name"
                        aria-label="New category name"
                        class="h-8 text-sm"
                        @keyup.enter="addCategory"
                    />
                    <Input
                        v-else-if="editingId === cat.id"
                        v-model="editForm.name"
                        :aria-label="`Name for ${cat.name}`"
                        class="h-8 text-sm"
                        @keyup.enter="saveEdit(cat.id)"
                    />
                    <span v-else class="font-medium">{{ cat.name }}</span>
                </template>

                <template #item-description="cat">
                    <Input
                        v-if="cat.id === NEW_ROW"
                        v-model="newForm.description"
                        placeholder="Optional description"
                        aria-label="New category description"
                        class="h-8 text-sm"
                        @keyup.enter="addCategory"
                    />
                    <Input
                        v-else-if="editingId === cat.id"
                        v-model="editForm.description"
                        :aria-label="`Description for ${cat.name}`"
                        class="h-8 text-sm"
                        @keyup.enter="saveEdit(cat.id)"
                    />
                    <span v-else class="text-xs text-muted-foreground">
                        {{ cat.description ?? '—' }}
                    </span>
                </template>

                <template #item-actions="cat">
                    <div
                        v-if="cat.id === NEW_ROW"
                        class="flex items-center justify-center gap-1"
                    >
                        <Button
                            size="sm"
                            class="h-8 w-8 bg-green-500 p-0 text-white hover:bg-green-600"
                            aria-label="Save new category"
                            :disabled="savingNew"
                            @click="addCategory"
                        >
                            <Loader2
                                v-if="savingNew"
                                class="h-3 w-3 animate-spin"
                            />
                            <Check v-else class="h-3 w-3" />
                        </Button>
                        <Button
                            size="sm"
                            variant="ghost"
                            class="h-8 w-8 p-0"
                            aria-label="Cancel new category"
                            @click="
                                showNewRow = false;
                                newForm = {
                                    name: '',
                                    description: '',
                                };
                                saveError = null;
                            "
                        >
                            <X class="h-3 w-3" />
                        </Button>
                    </div>
                    <div
                        v-else-if="editingId === cat.id"
                        class="flex items-center justify-center gap-1"
                    >
                        <Button
                            size="sm"
                            class="h-8 w-8 bg-green-500 p-0 text-white hover:bg-green-600"
                            :aria-label="`Save ${cat.name}`"
                            :disabled="savingEdit"
                            @click="saveEdit(cat.id)"
                        >
                            <Loader2
                                v-if="savingEdit"
                                class="h-3 w-3 animate-spin"
                            />
                            <Check v-else class="h-3 w-3" />
                        </Button>
                        <Button
                            size="sm"
                            variant="ghost"
                            class="h-8 w-8 p-0"
                            :aria-label="`Cancel editing ${cat.name}`"
                            @click="
                                editingId = null;
                                saveError = null;
                            "
                        >
                            <X class="h-3 w-3" />
                        </Button>
                    </div>
                    <RowActions
                        v-else
                        :label="cat.name"
                        :actions="[
                            {
                                label: 'Edit',
                                icon: Pencil,
                                show:
                                    isOwner ||
                                    can('Inventory Management', 'update'),
                                onSelect: () => startEdit(cat),
                            },
                            {
                                label: 'Archive',
                                icon: deletingId === cat.id ? Loader2 : Trash2,
                                destructive: true,
                                disabled: deletingId === cat.id,
                                show:
                                    isOwner ||
                                    can('Inventory Management', 'archive'),
                                onSelect: () => deleteCategory(cat.id),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>
    </ShopLayout>
</template>
