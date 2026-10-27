<script setup lang="ts">
import {
    DataTable,
    RowActions,
    StatusPill,
    statusLabel,
    type DataTableHeader,
} from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { usePaginatorTable } from '@/composables/useDataTable';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { Loader2, Pencil, Plus, Trash2, Truck } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

interface Supplier {
    id: number;
    name: string;
    contact_person: string | null;
    email: string | null;
    phone: string | null;
    address: string | null;
    status: 'active' | 'inactive';
    notes: string | null;
    inventory_count?: number;
}

interface Paginator {
    data: Supplier[];
    current_page: number;
    last_page: number;
    per_page?: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{ suppliers: Paginator }>();

const { isOwner, can } = usePermissions();
const base = computed(() =>
    isOwner.value ? '/shop/supplier' : '/staff/supplier',
);
const inventoryBase = computed(() =>
    isOwner.value ? '/shop/inventory' : '/staff/inventory',
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: inventoryBase.value },
    { title: 'Suppliers', href: base.value },
];

const supplierList = ref([...props.suppliers.data]);
const deletingId = ref<number | null>(null);

watch(
    () => props.suppliers.data,
    (data) => {
        supplierList.value = [...data];
    },
);

const { options, loading } = usePaginatorTable(
    () => props.suppliers,
    ['suppliers'],
);
const total = computed(
    () =>
        props.suppliers.total -
        (props.suppliers.data.length - supplierList.value.length),
);

const headers: DataTableHeader[] = [
    { text: 'Supplier', value: 'name' },
    { text: 'Contact person', value: 'contact_person' },
    { text: 'Phone', value: 'phone' },
    { text: 'Email address', value: 'email' },
    { text: 'Items', value: 'inventory_count' },
    { text: 'Status', value: 'status' },
    { text: 'Actions', value: 'actions', align: 'center', width: 120 },
];

async function deleteSupplier(id: number) {
    deletingId.value = id;
    try {
        await axios.delete(`${base.value}/${id}`);
        supplierList.value = supplierList.value.filter((s) => s.id !== id);
        toast.success('Supplier archived.', { autoClose: 3000 });
    } catch {
        toast.error('Failed to archive supplier.', { autoClose: 4000 });
    } finally {
        deletingId.value = null;
    }
}
</script>

<template>
    <Head title="Suppliers" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Suppliers">
        <div class="space-y-6 px-6">
            <DataTable
                v-model:options="options"
                :headers="headers"
                :items="supplierList"
                :total="total"
                :loading="loading"
                noun="suppliers"
                :empty-icon="Truck"
                empty-message="No suppliers yet."
                :resettable="false"
                :min-width="900"
                hide-rows-per-page
            >
                <template #toolbar>
                    <div
                        class="flex flex-wrap items-center justify-between gap-2"
                    >
                        <h2 class="flex items-center gap-2 font-semibold">
                            <Truck class="h-4 w-4 text-muted-foreground" />
                            Supplier List
                        </h2>
                        <div class="flex flex-wrap gap-2">
                            <Button
                                v-if="
                                    isOwner ||
                                    can('Inventory Management', 'archive')
                                "
                                size="sm"
                                variant="outline"
                                class="bg-red-500 text-white hover:bg-red-700 hover:text-white"
                                @click="router.visit(`${base}/archive`)"
                            >
                                <Trash2 class="mr-1.5 h-4 w-4" /> Archive
                            </Button>
                            <Button
                                v-if="
                                    isOwner ||
                                    can('Inventory Management', 'create')
                                "
                                size="sm"
                                @click="router.visit(`${base}/create`)"
                            >
                                <Plus class="mr-1.5 h-4 w-4" /> Add Supplier
                            </Button>
                        </div>
                    </div>
                </template>

                <template #item-name="s">
                    <p class="font-medium">{{ s.name }}</p>
                </template>

                <template #item-contact_person="s">
                    <span class="text-xs text-muted-foreground">
                        {{ s.contact_person ?? '' }}
                    </span>
                </template>

                <template #item-phone="s">
                    <span
                        class="text-xs whitespace-nowrap text-muted-foreground"
                    >
                        {{ s.phone ?? '' }}
                    </span>
                </template>

                <template #item-email="s">
                    <span class="text-xs text-muted-foreground">
                        {{ s.email ?? '' }}
                    </span>
                </template>

                <template #item-inventory_count="s">
                    <span class="whitespace-nowrap text-muted-foreground">
                        {{ s.inventory_count ?? 0 }} items
                    </span>
                </template>

                <template #item-status="s">
                    <StatusPill
                        :tone="s.status === 'active' ? 'success' : 'neutral'"
                    >
                        {{ statusLabel(s.status) }}
                    </StatusPill>
                </template>

                <template #item-actions="s">
                    <RowActions
                        :label="s.name"
                        :actions="[
                            {
                                label: 'Edit',
                                icon: Pencil,
                                show:
                                    isOwner ||
                                    can('Inventory Management', 'update'),
                                onSelect: () =>
                                    router.visit(`${base}/${s.id}/edit`),
                            },
                            {
                                label: 'Archive',
                                icon: deletingId === s.id ? Loader2 : Trash2,
                                destructive: true,
                                disabled: deletingId === s.id,
                                show:
                                    isOwner ||
                                    can('Inventory Management', 'archive'),
                                onSelect: () => deleteSupplier(s.id),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>
    </ShopLayout>
</template>
