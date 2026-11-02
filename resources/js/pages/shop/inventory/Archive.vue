<script setup lang="ts">
import {
    DataTable,
    type DataTableHeader,
    RowActions,
} from '@/components/data-table';
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { usePaginatorTable } from '@/composables/useDataTable';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    Archive,
    ArchiveRestore,
    ArrowLeft,
    Inbox,
    Loader2,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

interface ArchivedItem {
    id: number;
    name: string;
    unit: string;
    quantity: number;
    category: { id: number; name: string } | null;
    supplier: { id: number; name: string } | null;
    deleted_at: string;
}

interface Paginator {
    data: ArchivedItem[];
    current_page: number;
    last_page: number;
    per_page?: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{ archived: Paginator }>();

const { isOwner } = usePermissions();
const base = computed(() =>
    isOwner.value ? '/shop/inventory' : '/staff/inventory',
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: base.value },
    { title: 'Archive', href: `${base.value}/archive` },
];

const restoringId = ref<number | null>(null);
const itemToRestore = ref<ArchivedItem | null>(null);
const isRestoreOpen = ref(false);
const archivedList = ref([...props.archived.data]);

watch(
    () => props.archived.data,
    (data) => {
        archivedList.value = [...data];
    },
);

const { options, loading } = usePaginatorTable(
    () => props.archived,
    ['archived'],
);
const total = computed(
    () =>
        props.archived.total -
        (props.archived.data.length - archivedList.value.length),
);

const headers: DataTableHeader[] = [
    { text: 'Product', value: 'name', sortable: true },
    { text: 'Category', value: 'category', sortable: true },
    { text: 'Supplier', value: 'supplier', sortable: true },
    { text: 'Stock', value: 'quantity', sortable: true },
    { text: 'Archived on', value: 'deleted_at', width: 150, sortable: true },
    { text: 'Actions', value: 'actions', align: 'center', width: 140 },
];

function formatDate(dt: string) {
    return new Date(dt).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function openRestore(item: ArchivedItem) {
    itemToRestore.value = item;
    isRestoreOpen.value = true;
}

async function confirmRestore() {
    const item = itemToRestore.value;
    if (!item) return;
    isRestoreOpen.value = false;
    restoringId.value = item.id;
    try {
        await axios.post(`${base.value}/${item.id}/restore`);
        archivedList.value = archivedList.value.filter((i) => i.id !== item.id);
        toast.success('Product restored successfully.', { autoClose: 3000 });
    } catch {
        toast.error('Failed to restore product.', { autoClose: 4000 });
    } finally {
        restoringId.value = null;
        itemToRestore.value = null;
    }
}
</script>

<template>
    <Head title="Archived Products" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Archived Products">
        <div class="space-y-6 px-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Archived Products</h2>
                    <p class="text-sm text-muted-foreground">
                        Restore products to make them available again.
                    </p>
                </div>
                <Button variant="outline" @click="router.visit(base)">
                    <ArrowLeft class="mr-2 h-4 w-4" /> Back to Inventory
                </Button>
            </div>

            <DataTable
                v-model:options="options"
                :headers="headers"
                :items="archivedList"
                :total="total"
                :loading="loading"
                noun="archived products"
                :empty-icon="Inbox"
                empty-message="No archived products."
                :resettable="false"
                :min-width="860"
                hide-rows-per-page
            >
                <template #toolbar>
                    <h2 class="flex items-center gap-2 font-semibold">
                        <Archive class="h-4 w-4 text-muted-foreground" />
                        Archived
                        <span
                            class="rounded-full bg-muted px-2 py-0.5 text-xs font-normal"
                        >
                            {{ archived.total }}
                        </span>
                    </h2>
                </template>

                <template #item-name="item">
                    <p class="font-medium text-muted-foreground line-through">
                        {{ item.name }}
                    </p>
                    <p class="text-xs text-muted-foreground">{{ item.unit }}</p>
                </template>

                <template #item-category="item">
                    <span class="text-xs text-muted-foreground">
                        {{ item.category?.name ?? '—' }}
                    </span>
                </template>

                <template #item-supplier="item">
                    <span class="text-xs text-muted-foreground">
                        {{ item.supplier?.name ?? '—' }}
                    </span>
                </template>

                <template #item-quantity="item">
                    <span class="text-xs text-muted-foreground">
                        {{ item.quantity }} {{ item.unit }}
                    </span>
                </template>

                <template #item-deleted_at="item">
                    <span
                        class="text-xs whitespace-nowrap text-muted-foreground"
                    >
                        {{ formatDate(item.deleted_at) }}
                    </span>
                </template>

                <template #item-actions="item">
                    <RowActions
                        :label="item.name"
                        :actions="[
                            {
                                label: 'Restore',
                                icon:
                                    restoringId === item.id
                                        ? Loader2
                                        : ArchiveRestore,
                                disabled: restoringId === item.id,
                                onSelect: () => openRestore(item),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>

        <AlertDialog v-model:open="isRestoreOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Restore Product</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to restore
                        <strong>{{ itemToRestore?.name }}</strong
                        >? It will appear in the inventory list again.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <Button variant="outline" @click="isRestoreOpen = false"
                        >Cancel</Button
                    >
                    <Button @click="confirmRestore">Restore</Button>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </ShopLayout>
</template>
