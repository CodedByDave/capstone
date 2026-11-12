<script setup lang="ts">
import {
    BulkActionButton,
    DataTable,
    RowActions,
    StatusPill,
    statusLabel,
    type DataTableHeader,
} from '@/components/data-table';
import ExportCsvButton from '@/components/shop/ExportCsvButton.vue';
import PriceListStatusPill, {
    type PriceListStatus,
} from '@/components/supplier/PriceListStatusPill.vue';
import { Button } from '@/components/ui/button';
import { usePaginatorTable } from '@/composables/useDataTable';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    Archive,
    Eye,
    Loader2,
    Mail,
    Pencil,
    Plus,
    Trash2,
    Truck,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

interface Supplier {
    public_id: string;
    user_id: number | null;
    name: string;
    contact_person: string | null;
    email: string | null;
    phone: string | null;
    address: string | null;
    status: 'active' | 'inactive';
    notes: string | null;
    inventory_count?: number;
    items_count?: number;
    latest_price_list?: { status: PriceListStatus } | null;
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
const deletingId = ref<string | null>(null);
const invitingId = ref<string | null>(null);

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
    { text: 'Supplier', value: 'name', sortable: true },
    { text: 'Contact person', value: 'contact_person', sortable: true },
    { text: 'Phone', value: 'phone', sortable: true },
    { text: 'Email address', value: 'email', sortable: true },
    { text: 'Supplies', value: 'items_count', sortable: true },
    { text: 'Price list', value: 'price_list' },
    { text: 'Login', value: 'account' },
    { text: 'Status', value: 'status', sortable: true },
    { text: 'Actions', value: 'actions', align: 'center', width: 120 },
];

async function deleteSupplier(id: string) {
    deletingId.value = id;
    try {
        await axios.delete(`${base.value}/${id}`);
        supplierList.value = supplierList.value.filter(
            (s) => s.public_id !== id,
        );
        toast.success('Supplier archived.', { autoClose: 3000 });
    } catch {
        toast.error('Failed to archive supplier.', { autoClose: 4000 });
    } finally {
        deletingId.value = null;
    }
}

async function inviteSupplier(supplier: Supplier) {
    invitingId.value = supplier.public_id;
    try {
        await axios.post(`${base.value}/${supplier.public_id}/invite`);
        toast.success(`Login invitation sent to ${supplier.name}.`, {
            autoClose: 3000,
        });
        if (!supplier.user_id) router.reload({ only: ['suppliers'] });
    } catch (error) {
        const message = axios.isAxiosError(error)
            ? error.response?.data?.message
            : null;
        toast.error(message ?? 'Failed to send the invitation.', {
            autoClose: 4000,
        });
    } finally {
        invitingId.value = null;
    }
}

const selected = ref<Array<string | number>>([]);
</script>

<template>
    <Head title="Suppliers" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Suppliers">
        <div class="space-y-6 px-6">
            <DataTable
                v-model:options="options"
                v-model:selected="selected"
                :headers="headers"
                :items="supplierList"
                item-key="public_id"
                :total="total"
                :loading="loading"
                noun="suppliers"
                :empty-icon="Truck"
                empty-message="No suppliers yet."
                :resettable="false"
                :min-width="1100"
                hide-rows-per-page
            >
                <template #bulk-actions>
                    <BulkActionButton
                        v-if="can('Inventory Management', 'archive')"
                        resource="supplier"
                        action="archive"
                        :ids="selected"
                        noun="suppliers"
                        @done="selected = []"
                    />
                </template>

                <template #toolbar>
                    <div
                        class="flex flex-wrap items-center justify-between gap-2"
                    >
                        <h2 class="flex items-center gap-2 font-semibold">
                            <Truck class="h-4 w-4 text-muted-foreground" />
                            Supplier List
                        </h2>
                        <div class="flex flex-wrap gap-2">
                            <ExportCsvButton dataset="suppliers" />
                            <Button
                                v-if="
                                    isOwner ||
                                    can('Inventory Management', 'archive')
                                "
                                size="sm"
                                variant="warning-outline"
                                @click="router.visit(`${base}/archive`)"
                            >
                                <Archive class="mr-1.5 h-4 w-4" /> Archive
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
                    <button
                        type="button"
                        class="text-left font-medium hover:underline focus-visible:underline focus-visible:outline-none"
                        @click="router.visit(`${base}/${s.public_id}`)"
                    >
                        {{ s.name }}
                    </button>
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

                <template #item-items_count="s">
                    <span class="whitespace-nowrap text-muted-foreground">
                        {{ s.items_count ?? 0 }} listed
                    </span>
                </template>

                <template #item-price_list="s">
                    <PriceListStatusPill
                        :status="s.latest_price_list?.status"
                    />
                </template>

                <template #item-account="s">
                    <StatusPill :tone="s.user_id ? 'success' : 'neutral'">
                        {{ s.user_id ? 'Has login' : 'No login' }}
                    </StatusPill>
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
                                label: 'View',
                                icon: Eye,
                                onSelect: () =>
                                    router.visit(`${base}/${s.public_id}`),
                            },
                            {
                                label: s.user_id
                                    ? 'Resend login invite'
                                    : 'Send login invite',
                                icon:
                                    invitingId === s.public_id ? Loader2 : Mail,
                                disabled: invitingId === s.public_id,
                                show:
                                    isOwner ||
                                    can('Inventory Management', 'update'),
                                onSelect: () => inviteSupplier(s),
                            },
                            {
                                label: 'Edit',
                                icon: Pencil,
                                show:
                                    isOwner ||
                                    can('Inventory Management', 'update'),
                                onSelect: () =>
                                    router.visit(`${base}/${s.public_id}/edit`),
                            },
                            {
                                label: 'Archive',
                                icon:
                                    deletingId === s.public_id
                                        ? Loader2
                                        : Trash2,
                                destructive: true,
                                disabled: deletingId === s.public_id,
                                show:
                                    isOwner ||
                                    can('Inventory Management', 'archive'),
                                onSelect: () => deleteSupplier(s.public_id),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </div>
    </ShopLayout>
</template>
