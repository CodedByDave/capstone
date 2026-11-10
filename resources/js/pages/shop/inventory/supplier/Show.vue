<script setup lang="ts">
import {
    DataTable,
    StatusPill,
    statusLabel,
    type DataTableHeader,
} from '@/components/data-table';
import PriceListStatusPill, {
    type PriceListStatus,
} from '@/components/supplier/PriceListStatusPill.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { useDataTable } from '@/composables/useDataTable';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    ArrowLeft,
    FileImage,
    Loader2,
    Mail,
    Package,
    Pencil,
    Phone,
    ScanText,
    Truck,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

interface SupplyItem {
    public_id: string;
    name: string;
    unit: string | null;
    stock_quantity: number;
    unit_price: number;
    updated_at: string | null;
}

interface MatchResult {
    item: string;
    name: string;
    expected_price: number;
    found_price: number | null;
    result: 'matched' | 'price_mismatch' | 'not_found';
}

const props = defineProps<{
    supplier: {
        public_id: string;
        name: string;
        contact_person: string | null;
        email: string | null;
        phone: string | null;
        address: string | null;
        lead_time_days: number | null;
        status: string;
        has_account: boolean;
        inventory_count: number;
        items: SupplyItem[];
        latest_price_list: {
            public_id: string;
            status: PriceListStatus;
            photo_url: string;
            checked_at: string | null;
            matched_count: number;
            item_count: number;
            results: MatchResult[];
        } | null;
    };
}>();

const { isOwner, can } = usePermissions();
const base = computed(() =>
    isOwner.value ? '/shop/supplier' : '/staff/supplier',
);
const canUpdate = computed(
    () => isOwner.value || can('Inventory Management', 'update'),
);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Inventory',
        href: isOwner.value ? '/shop/inventory' : '/staff/inventory',
    },
    { title: 'Suppliers', href: base.value },
    {
        title: props.supplier.name,
        href: `${base.value}/${props.supplier.public_id}`,
    },
];

const statCards = computed(() => [
    { label: 'Supplies listed', value: props.supplier.items.length },
    {
        label: 'Out of stock',
        value: props.supplier.items.filter((i) => i.stock_quantity <= 0).length,
    },
    { label: 'Your linked products', value: props.supplier.inventory_count },
]);

/* ─── Supplier catalog (all rows are loaded, so the table filters in the browser) ─── */

const headers: DataTableHeader[] = [
    { text: 'Supply', value: 'name', sortable: true },
    { text: 'Unit', value: 'unit', sortable: true },
    {
        text: 'In stock',
        value: 'stock_quantity',
        sortable: true,
        align: 'right',
    },
    { text: 'Unit price', value: 'unit_price', sortable: true, align: 'right' },
    { text: 'Price list', value: 'check' },
];

const resultByItem = computed(() => {
    const map: Record<string, MatchResult['result']> = {};
    props.supplier.latest_price_list?.results.forEach((row) => {
        map[row.item] = row.result;
    });
    return map;
});

const {
    search: catalogSearch,
    options: catalogOptions,
    pageItems,
    total: catalogTotal,
    reset: resetCatalog,
} = useDataTable(() => props.supplier.items, {
    searchFields: (item) => [item.name, item.unit],
    sortBy: 'name',
});

const resultLabels: Record<MatchResult['result'], string> = {
    matched: 'Matches',
    price_mismatch: 'Different price',
    not_found: 'Not on photo',
};
const resultTones = {
    matched: 'success',
    price_mismatch: 'warning',
    not_found: 'danger',
} as const;

const peso = new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
});
const quantity = new Intl.NumberFormat('en-PH', { maximumFractionDigits: 2 });

function formatDate(value: string | null) {
    if (!value) return '—';
    return new Date(value).toLocaleString('en-PH', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}

const inviting = ref(false);

async function invite() {
    inviting.value = true;
    try {
        await axios.post(`${base.value}/${props.supplier.public_id}/invite`);
        toast.success('Login invitation sent.', { autoClose: 3000 });
        if (!props.supplier.has_account) router.reload({ only: ['supplier'] });
    } catch (error) {
        const message = axios.isAxiosError(error)
            ? error.response?.data?.message
            : null;
        toast.error(message ?? 'Failed to send the invitation.', {
            autoClose: 4000,
        });
    } finally {
        inviting.value = false;
    }
}
</script>

<template>
    <Head :title="supplier.name" />
    <ShopLayout :breadcrumbs="breadcrumbs" :title="supplier.name">
        <div class="space-y-6 px-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="flex items-center gap-2 text-xl font-semibold">
                        <Truck class="h-5 w-5 text-muted-foreground" />
                        {{ supplier.name }}
                        <StatusPill
                            :tone="
                                supplier.status === 'active'
                                    ? 'success'
                                    : 'neutral'
                            "
                        >
                            {{ statusLabel(supplier.status) }}
                        </StatusPill>
                    </h1>
                    <p
                        class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted-foreground"
                    >
                        <span v-if="supplier.contact_person">{{
                            supplier.contact_person
                        }}</span>
                        <span
                            v-if="supplier.email"
                            class="flex items-center gap-1"
                            ><Mail class="h-3.5 w-3.5" />{{
                                supplier.email
                            }}</span
                        >
                        <span
                            v-if="supplier.phone"
                            class="flex items-center gap-1"
                            ><Phone class="h-3.5 w-3.5" />{{
                                supplier.phone
                            }}</span
                        >
                        <StatusPill
                            :tone="supplier.has_account ? 'success' : 'neutral'"
                        >
                            {{
                                supplier.has_account ? 'Has login' : 'No login'
                            }}
                        </StatusPill>
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="router.visit(base)"
                    >
                        <ArrowLeft class="mr-1.5 h-4 w-4" /> Back
                    </Button>
                    <Button
                        v-if="canUpdate"
                        variant="outline"
                        size="sm"
                        :disabled="inviting"
                        @click="invite"
                    >
                        <Loader2
                            v-if="inviting"
                            class="mr-1.5 h-4 w-4 animate-spin"
                        />
                        <Mail v-else class="mr-1.5 h-4 w-4" />
                        {{
                            supplier.has_account
                                ? 'Resend login invite'
                                : 'Send login invite'
                        }}
                    </Button>
                    <Button
                        v-if="canUpdate"
                        size="sm"
                        @click="
                            router.visit(`${base}/${supplier.public_id}/edit`)
                        "
                    >
                        <Pencil class="mr-1.5 h-4 w-4" /> Edit
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                <Card v-for="item in statCards" :key="item.label">
                    <CardContent class="p-4">
                        <p
                            class="mb-3 text-xs font-medium text-muted-foreground"
                        >
                            {{ item.label }}
                        </p>
                        <p class="text-2xl font-bold">{{ item.value }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4">
                        <p
                            class="mb-3 text-xs font-medium text-muted-foreground"
                        >
                            Price list check
                        </p>
                        <PriceListStatusPill
                            :status="supplier.latest_price_list?.status"
                        />
                    </CardContent>
                </Card>
            </div>

            <DataTable
                v-model:options="catalogOptions"
                :headers="headers"
                :items="pageItems"
                :total="catalogTotal"
                item-key="public_id"
                noun="supplies"
                :empty-icon="Package"
                empty-message="This supplier has not listed any supplies yet."
                :min-width="520"
                :selectable="false"
                @reset="resetCatalog"
            >
                <template #toolbar>
                    <div
                        class="flex flex-wrap items-center justify-between gap-2"
                    >
                        <h2 class="flex items-center gap-2 font-semibold">
                            <Package class="h-4 w-4 text-muted-foreground" />
                            Supplier catalog
                        </h2>
                        <label class="sr-only" for="catalog-search"
                            >Search supplies</label
                        >
                        <input
                            id="catalog-search"
                            v-model="catalogSearch"
                            type="search"
                            placeholder="Search supply or unit..."
                            class="h-11 w-full rounded-md border bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring/50 sm:w-64"
                        />
                    </div>
                </template>

                <template #item-name="item">
                    <p class="font-semibold">{{ item.name }}</p>
                </template>
                <template #item-unit="item">
                    <span class="text-muted-foreground">{{
                        item.unit || '—'
                    }}</span>
                </template>
                <template #item-stock_quantity="item">
                    <span
                        class="tabular-nums"
                        :class="item.stock_quantity <= 0 ? 'text-red-600' : ''"
                    >
                        {{ quantity.format(item.stock_quantity) }}
                    </span>
                </template>
                <template #item-unit_price="item">
                    <span class="font-medium tabular-nums">{{
                        peso.format(item.unit_price)
                    }}</span>
                </template>
                <template #item-check="item">
                    <StatusPill
                        v-if="resultByItem[item.public_id]"
                        :tone="resultTones[resultByItem[item.public_id]]"
                    >
                        {{ resultLabels[resultByItem[item.public_id]] }}
                    </StatusPill>
                    <StatusPill v-else tone="neutral">Not checked</StatusPill>
                </template>
            </DataTable>

            <section
                class="rounded-[14px] border bg-background p-4 sm:p-6"
                aria-labelledby="price-list-heading"
            >
                <h2
                    id="price-list-heading"
                    class="flex items-center gap-2 font-semibold"
                >
                    <ScanText class="h-4 w-4 text-muted-foreground" />
                    Latest price list
                </h2>
                <div
                    v-if="supplier.latest_price_list"
                    class="mt-4 grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,2fr)]"
                >
                    <a
                        :href="supplier.latest_price_list.photo_url"
                        target="_blank"
                        rel="noopener"
                        class="block overflow-hidden rounded-lg border bg-muted focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    >
                        <img
                            :src="supplier.latest_price_list.photo_url"
                            :alt="`Price list uploaded by ${supplier.name}`"
                            class="max-h-80 w-full object-contain"
                        />
                    </a>
                    <div class="min-w-0 space-y-2 text-sm">
                        <PriceListStatusPill
                            :status="supplier.latest_price_list.status"
                        />
                        <p class="text-muted-foreground">
                            {{ supplier.latest_price_list.matched_count }} of
                            {{ supplier.latest_price_list.item_count }} supplies
                            match the photo · checked
                            {{
                                formatDate(
                                    supplier.latest_price_list.checked_at,
                                )
                            }}
                        </p>
                        <p
                            v-if="
                                supplier.latest_price_list.status !== 'verified'
                            "
                            class="text-muted-foreground"
                        >
                            OCR can misread photos. Compare the photo with the
                            catalog before relying on these prices.
                        </p>
                    </div>
                </div>
                <div
                    v-else
                    class="mt-4 flex flex-col items-center gap-2 rounded-lg border border-dashed py-10 text-center text-sm text-muted-foreground"
                >
                    <FileImage class="h-8 w-8" aria-hidden="true" />
                    This supplier has not uploaded a price list yet.
                </div>
            </section>
        </div>
    </ShopLayout>
</template>
