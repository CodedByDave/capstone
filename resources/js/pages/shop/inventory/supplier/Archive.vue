<script setup lang="ts">
import {
    BulkActionButton,
    DataTable,
    type DataTableHeader,
    RowActions,
} from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { usePaginatorTable } from '@/composables/useDataTable';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    ArchiveRestore,
    ArrowLeft,
    Inbox,
    Loader2,
    Mail,
    Phone,
    Truck,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

interface Supplier {
    public_id: string;
    name: string;
    contact_person: string | null;
    email: string | null;
    phone: string | null;
    address: string | null;
    status: string;
    inventory_count?: number;
    deleted_at: string;
}

interface Paginator {
    data: Supplier[];
    current_page: number;
    last_page: number;
    per_page?: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{ archived: Paginator }>();

const { isOwner } = usePermissions();
const base = computed(() =>
    isOwner.value ? '/shop/supplier' : '/staff/supplier',
);
const inventoryBase = computed(() =>
    isOwner.value ? '/shop/inventory' : '/staff/inventory',
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: inventoryBase.value },
    { title: 'Suppliers', href: base.value },
    { title: 'Archive', href: `${base.value}/archive` },
];

const restoringId = ref<string | null>(null);
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
    { text: 'Supplier', value: 'name', sortable: true },
    { text: 'Contact', value: 'contact', sortable: true },
    { text: 'Items', value: 'inventory_count', sortable: true },
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

async function restoreSupplier(id: string) {
    restoringId.value = id;
    try {
        await axios.post(`${base.value}/${id}/restore`);
        archivedList.value = archivedList.value.filter(
            (s) => s.public_id !== id,
        );
        toast.success('Supplier restored successfully.', { autoClose: 3000 });
    } catch {
        toast.error('Failed to restore supplier.', { autoClose: 4000 });
    } finally {
        restoringId.value = null;
    }
}

const selected = ref<Array<string | number>>([]);
</script>

<template>
    <Head title="Archived Suppliers" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Archived Suppliers">
        <div class="space-y-6 px-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Archived Suppliers</h2>
                    <p class="text-sm text-muted-foreground">
                        Restore suppliers to make them available again.
                    </p>
                </div>
                <Button variant="outline" @click="router.visit(base)">
                    <ArrowLeft class="mr-2 h-4 w-4" /> Back to Suppliers
                </Button>
            </div>

            <DataTable
                v-model:options="options"
                v-model:selected="selected"
                :headers="headers"
                :items="archivedList"
                item-key="public_id"
                :total="total"
                :loading="loading"
                noun="archived suppliers"
                :empty-icon="Inbox"
                empty-message="No archived suppliers."
                :resettable="false"
                :min-width="760"
                hide-rows-per-page
            >
                <template #bulk-actions>
                    <BulkActionButton
                        resource="supplier"
                        action="restore"
                        :ids="selected"
                        noun="archived suppliers"
                        @done="selected = []"
                    />
                </template>

                <template #toolbar>
                    <h2 class="flex items-center gap-2 font-semibold">
                        <Truck class="h-4 w-4 text-muted-foreground" />
                        Archived
                        <span
                            class="rounded-full bg-muted px-2 py-0.5 text-xs font-normal"
                        >
                            {{ archived.total }}
                        </span>
                    </h2>
                </template>

                <template #item-name="s">
                    <p class="font-medium text-muted-foreground line-through">
                        {{ s.name }}
                    </p>
                    <p
                        v-if="s.contact_person"
                        class="text-xs text-muted-foreground"
                    >
                        {{ s.contact_person }}
                    </p>
                </template>

                <template #item-contact="s">
                    <div class="space-y-0.5">
                        <div
                            v-if="s.phone"
                            class="flex items-center gap-1 text-xs text-muted-foreground"
                        >
                            <Phone class="h-3 w-3" />
                            {{ s.phone }}
                        </div>
                        <div
                            v-if="s.email"
                            class="flex items-center gap-1 text-xs text-muted-foreground"
                        >
                            <Mail class="h-3 w-3" />
                            {{ s.email }}
                        </div>
                    </div>
                </template>

                <template #item-inventory_count="s">
                    <span
                        class="text-xs whitespace-nowrap text-muted-foreground"
                    >
                        {{ s.inventory_count ?? 0 }} items
                    </span>
                </template>

                <template #item-deleted_at="s">
                    <span
                        class="text-xs whitespace-nowrap text-muted-foreground"
                    >
                        {{ formatDate(s.deleted_at) }}
                    </span>
                </template>

                <template #item-actions="s">
                    <RowActions
                        :label="s.name"
                        :actions="[
                            {
                                label: 'Restore',
                                icon:
                                    restoringId === s.public_id
                                        ? Loader2
                                        : ArchiveRestore,
                                disabled: restoringId === s.public_id,
                                onSelect: () => restoreSupplier(s.public_id),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>
    </ShopLayout>
</template>
