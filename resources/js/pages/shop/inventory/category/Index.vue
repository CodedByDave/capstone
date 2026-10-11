<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
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
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle class="flex items-center gap-2">
                            <Layers class="h-4 w-4 text-muted-foreground" />
                            Categories
                        </CardTitle>
                        <div class="flex gap-2">
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
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Inline error -->
                    <div
                        v-if="saveError"
                        class="mb-3 flex items-center justify-between rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700"
                    >
                        <span>{{ saveError }}</span>
                        <button @click="saveError = null">
                            <X
                                class="ml-2 h-3.5 w-3.5 text-red-400 hover:text-red-600"
                            />
                        </button>
                    </div>

                    <div class="overflow-hidden rounded-lg border">
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="border-b bg-muted/40 text-xs text-muted-foreground"
                                >
                                    <th
                                        class="w-1/3 px-4 py-3 text-left font-medium"
                                    >
                                        Name
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Description
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right font-medium"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- New row -->
                                <tr
                                    v-if="showNewRow"
                                    class="border-b bg-blue-50/50"
                                >
                                    <td class="px-3 py-2">
                                        <Input
                                            v-model="newForm.name"
                                            placeholder="Category name"
                                            class="h-8 text-sm"
                                            @keyup.enter="addCategory"
                                        />
                                    </td>
                                    <td class="px-3 py-2">
                                        <Input
                                            v-model="newForm.description"
                                            placeholder="Optional description"
                                            class="h-8 text-sm"
                                            @keyup.enter="addCategory"
                                        />
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <div
                                            class="flex items-center justify-end gap-1"
                                        >
                                            <Button
                                                size="sm"
                                                class="h-7 w-7 bg-green-500 p-0 text-white hover:bg-green-600"
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
                                                class="h-7 w-7 p-0"
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
                                    </td>
                                </tr>

                                <!-- Existing rows -->
                                <template
                                    v-for="cat in categories"
                                    :key="cat.id"
                                >
                                    <!-- View mode -->
                                    <tr
                                        v-if="editingId !== cat.id"
                                        class="border-b transition-colors last:border-0 hover:bg-muted/20"
                                    >
                                        <td class="px-4 py-3 font-medium">
                                            {{ cat.name }}
                                        </td>
                                        <td
                                            class="px-4 py-3 text-xs text-muted-foreground"
                                        >
                                            {{ cat.description ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div
                                                class="flex items-center justify-end gap-1"
                                            >
                                                <Button
                                                    v-if="
                                                        isOwner ||
                                                        can(
                                                            'Inventory Management',
                                                            'update',
                                                        )
                                                    "
                                                    size="icon"
                                                    variant="ghost"
                                                    @click="startEdit(cat)"
                                                >
                                                    <Pencil
                                                        class="h-3.5 w-3.5 text-blue-500"
                                                    />
                                                </Button>
                                                <Button
                                                    v-if="
                                                        isOwner ||
                                                        can(
                                                            'Inventory Management',
                                                            'archive',
                                                        )
                                                    "
                                                    size="icon"
                                                    variant="ghost"
                                                    :disabled="
                                                        deletingId === cat.id
                                                    "
                                                    @click="
                                                        deleteCategory(cat.id)
                                                    "
                                                >
                                                    <Loader2
                                                        v-if="
                                                            deletingId ===
                                                            cat.id
                                                        "
                                                        class="h-3.5 w-3.5 animate-spin"
                                                    />
                                                    <Trash2
                                                        v-else
                                                        class="h-3.5 w-3.5 text-red-400"
                                                    />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit mode -->
                                    <tr
                                        v-else
                                        class="border-b bg-amber-50/50 last:border-0"
                                    >
                                        <td class="px-3 py-2">
                                            <Input
                                                v-model="editForm.name"
                                                class="h-8 text-sm"
                                                @keyup.enter="saveEdit(cat.id)"
                                            />
                                        </td>
                                        <td class="px-3 py-2">
                                            <Input
                                                v-model="editForm.description"
                                                class="h-8 text-sm"
                                                @keyup.enter="saveEdit(cat.id)"
                                            />
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <div
                                                class="flex items-center justify-end gap-1"
                                            >
                                                <Button
                                                    size="sm"
                                                    class="h-7 w-7 bg-green-500 p-0 text-white hover:bg-green-600"
                                                    :disabled="savingEdit"
                                                    @click="saveEdit(cat.id)"
                                                >
                                                    <Loader2
                                                        v-if="savingEdit"
                                                        class="h-3 w-3 animate-spin"
                                                    />
                                                    <Check
                                                        v-else
                                                        class="h-3 w-3"
                                                    />
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="ghost"
                                                    class="h-7 w-7 p-0"
                                                    @click="
                                                        editingId = null;
                                                        saveError = null;
                                                    "
                                                >
                                                    <X class="h-3 w-3" />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>

                                <!-- Empty state -->
                                <tr
                                    v-if="
                                        categories.length === 0 && !showNewRow
                                    "
                                >
                                    <td
                                        colspan="3"
                                        class="px-4 py-10 text-center text-sm text-muted-foreground"
                                    >
                                        <Layers
                                            class="mx-auto mb-2 h-8 w-8 opacity-20"
                                        />
                                        No categories yet. Click "Add Category"
                                        to create one.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </ShopLayout>
</template>
