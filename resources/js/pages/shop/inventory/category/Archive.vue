<script setup lang="ts">
import {
    DataTable,
    DataTableToolbar,
    RowActions,
    type DataTableHeader,
} from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { useDataTable } from '@/composables/useDataTable';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    ArchiveRestore,
    ArrowLeft,
    Inbox,
    Layers,
    Loader2,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

interface Category {
    id: number;
    name: string;
    description: string | null;
    deleted_at: string;
}

const { isOwner } = usePermissions();
const base = computed(() =>
    isOwner.value ? '/shop/inventory' : '/staff/inventory',
);

const props = defineProps<{ archived: Category[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: '/shop/inventory' },
    { title: 'Categories', href: '/shop/inventory/category' },
    { title: 'Archive', href: '/shop/inventory/category/archive' },
];

const archivedList = ref<Category[]>([...props.archived]);
const restoringId = ref<number | null>(null);

const headers: DataTableHeader[] = [
    { text: 'Name', value: 'name', sortable: true },
    { text: 'Description', value: 'description' },
    { text: 'Archived on', value: 'deleted_at', sortable: true, width: 160 },
    { text: 'Actions', value: 'actions', align: 'center', width: 140 },
];

const { search, options, pageItems, total, isFiltered, reset } = useDataTable(
    archivedList,
    {
        searchFields: (cat) => [cat.name, cat.description],
        sortBy: 'deleted_at',
        sortType: 'desc',
    },
);

function formatDate(dt: string) {
    return new Date(dt).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

async function restoreCategory(id: number) {
    restoringId.value = id;
    try {
        await axios.post(`${base.value}/category/${id}/restore`);
        archivedList.value = archivedList.value.filter((c) => c.id !== id);
        toast.success('Category restored successfully.', { autoClose: 3000 });
    } catch {
        toast.error('Failed to restore category.', { autoClose: 4000 });
    } finally {
        restoringId.value = null;
    }
}
</script>

<template>
    <Head title="Archived Categories" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Archived Categories">
        <div class="space-y-6 px-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Archived Categories</h2>
                    <p class="text-sm text-muted-foreground">
                        Restore categories to make them available again.
                    </p>
                </div>
                <Button
                    variant="outline"
                    @click="router.visit(`${base}/category`)"
                >
                    <ArrowLeft class="mr-2 h-4 w-4" /> Back to Categories
                </Button>
            </div>

            <DataTable
                v-model:options="options"
                :headers="headers"
                :items="pageItems"
                :total="total"
                noun="archived categories"
                :empty-icon="Inbox"
                :empty-message="
                    isFiltered
                        ? 'No archived categories match your search.'
                        : 'No archived categories.'
                "
                :resettable="isFiltered"
                :min-width="640"
                @reset="reset"
            >
                <template #toolbar>
                    <DataTableToolbar
                        v-model:search="search"
                        search-placeholder="Search name or description..."
                        search-label="Search archived categories"
                    >
                        <p
                            class="order-first flex w-full items-center gap-2 text-sm font-semibold sm:order-none sm:w-auto"
                        >
                            <Layers class="h-4 w-4 text-muted-foreground" />
                            Archived
                            <span
                                class="rounded-full bg-muted px-2 py-0.5 text-xs font-normal"
                            >
                                {{ archivedList.length }}
                            </span>
                        </p>
                    </DataTableToolbar>
                </template>

                <template #item-name="cat">
                    <span
                        class="font-medium text-muted-foreground line-through"
                    >
                        {{ cat.name }}
                    </span>
                </template>

                <template #item-description="cat">
                    <span class="text-xs text-muted-foreground">
                        {{ cat.description ?? '—' }}
                    </span>
                </template>

                <template #item-deleted_at="cat">
                    <span
                        class="text-xs whitespace-nowrap text-muted-foreground"
                    >
                        {{ formatDate(cat.deleted_at) }}
                    </span>
                </template>

                <template #item-actions="cat">
                    <RowActions
                        :label="cat.name"
                        :actions="[
                            {
                                label: 'Restore',
                                icon:
                                    restoringId === cat.id
                                        ? Loader2
                                        : ArchiveRestore,
                                disabled: restoringId === cat.id,
                                onSelect: () => restoreCategory(cat.id),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>
    </ShopLayout>
</template>
