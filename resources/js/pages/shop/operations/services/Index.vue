<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    serviceCategories,
    serviceCategoryLabel,
    type ServiceCategory,
} from '@/constants/serviceCategories';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    Archive,
    ArrowDown,
    ArrowUp,
    ArrowUpDown,
    Package,
    Pencil,
    Plus,
    RefreshCcw,
    RotateCcw,
    Scissors,
    Search,
    ToggleLeft,
    ToggleRight,
    Trash2,
    Weight,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import Vue3EasyDataTable, { type Header } from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

interface ShopService {
    id: number;
    service_name: string;
    category: ServiceCategory;
    description: string | null;
    is_active: boolean;
    pricing_model: 'per_kg' | 'per_bundle';
    price_per_kg: string | null;
    bundle_weight_kg: string | null;
    bundle_price: string | null;
    estimated_hours: number | null;
}

type ServiceTableItem = ShopService & {
    category_label: string;
    price_sort: number;
    actions: string;
};

type ServiceSortColumn =
    | 'service_name'
    | 'category_label'
    | 'pricing_model'
    | 'price_sort'
    | 'estimated_hours'
    | 'is_active';

const EasyDataTable = Vue3EasyDataTable;
const props = defineProps<{
    services: ShopService[];
    showArchived: boolean;
}>();

const page = usePage<AppPageProps>();
const isOwner = computed(() => page.props.auth.user.role === 'owner');
const base = computed(() =>
    isOwner.value ? '/shop/operations/services' : '/staff/operations/services',
);

const search = ref('');
const categoryFilter = ref('');
const pricingFilter = ref('');
const statusFilter = ref('');
const tableLoading = ref(false);
const sortBy = ref<ServiceSortColumn>('service_name');
const sortType = ref<'asc' | 'desc'>('asc');
const serviceToDelete = ref<ShopService | null>(null);
const showDeleteDialog = ref(false);

const headers = computed<Header[]>(() => [
    { text: 'Service', value: 'service_name', width: 230 },
    { text: 'Category', value: 'category_label', width: 240 },
    { text: 'Pricing Model', value: 'pricing_model', width: 150 },
    { text: 'Price', value: 'price_sort', width: 160 },
    { text: 'Turnaround', value: 'estimated_hours', width: 135 },
    ...(!props.showArchived
        ? [{ text: 'Status', value: 'is_active', width: 115 }]
        : []),
    { text: 'Actions', value: 'actions', width: 145 },
]);

const totalServices = computed(() => props.services.length);
const activeServices = computed(
    () => props.services.filter((service) => service.is_active).length,
);
const perKgServices = computed(
    () =>
        props.services.filter((service) => service.pricing_model === 'per_kg')
            .length,
);
const perBundleServices = computed(
    () =>
        props.services.filter(
            (service) => service.pricing_model === 'per_bundle',
        ).length,
);
const statCards = computed(() => [
    { label: 'Total Services', value: totalServices.value },
    { label: 'Active Services', value: activeServices.value },
    { label: 'Per KG', value: perKgServices.value },
    { label: 'Per Bundle', value: perBundleServices.value },
]);

const tableItems = computed<ServiceTableItem[]>(() => {
    const query = search.value.trim().toLowerCase();
    const items = props.services
        .map((service) => ({
            ...service,
            category_label: serviceCategoryLabel(service.category),
            price_sort: Number(
                service.pricing_model === 'per_kg'
                    ? service.price_per_kg
                    : service.bundle_price,
            ),
            actions: '',
        }))
        .filter((service) => {
            const matchesSearch =
                !query ||
                service.service_name.toLowerCase().includes(query) ||
                service.category_label.toLowerCase().includes(query) ||
                service.description?.toLowerCase().includes(query);
            const matchesCategory =
                !categoryFilter.value ||
                service.category === categoryFilter.value;
            const matchesPricing =
                !pricingFilter.value ||
                service.pricing_model === pricingFilter.value;
            const matchesStatus =
                props.showArchived ||
                !statusFilter.value ||
                (statusFilter.value === 'active'
                    ? service.is_active
                    : !service.is_active);

            return (
                matchesSearch &&
                matchesCategory &&
                matchesPricing &&
                matchesStatus
            );
        });

    return items.sort((left, right) => {
        const leftValue = left[sortBy.value] ?? '';
        const rightValue = right[sortBy.value] ?? '';
        const result =
            typeof leftValue === 'number' && typeof rightValue === 'number'
                ? leftValue - rightValue
                : String(leftValue).localeCompare(String(rightValue));

        return sortType.value === 'asc' ? result : -result;
    });
});

watch(
    () => page.props.toast as { type: string; message: string } | undefined,
    (message) => {
        if (!message) return;
        if (message.type === 'success') toast.success(message.message);
        else if (message.type === 'error') toast.error(message.message);
        else if (message.type === 'warning') toast.warning(message.message);
        else toast.info(message.message);
    },
    { immediate: true },
);

function formatCurrency(value: string | number | null) {
    return value !== null
        ? new Intl.NumberFormat('en-PH', {
              style: 'currency',
              currency: 'PHP',
          }).format(Number(value))
        : '—';
}

function formatPrice(service: ShopService) {
    return service.pricing_model === 'per_kg'
        ? `${formatCurrency(service.price_per_kg)}/kg`
        : `${formatCurrency(service.bundle_price)} / ${service.bundle_weight_kg}kg bundle`;
}

function toggleSort(column: ServiceSortColumn) {
    if (sortBy.value === column) {
        sortType.value = sortType.value === 'asc' ? 'desc' : 'asc';
        return;
    }

    sortBy.value = column;
    sortType.value = 'asc';
}

function sortIcon(column: ServiceSortColumn) {
    if (sortBy.value !== column) return ArrowUpDown;
    return sortType.value === 'asc' ? ArrowUp : ArrowDown;
}

function resetFilters() {
    search.value = '';
    categoryFilter.value = '';
    pricingFilter.value = '';
    statusFilter.value = '';
    sortBy.value = 'service_name';
    sortType.value = 'asc';
}

function toggleArchived() {
    router.get(
        base.value,
        { archived: props.showArchived ? undefined : true },
        {
            preserveState: false,
            replace: true,
            onStart: () => (tableLoading.value = true),
            onFinish: () => (tableLoading.value = false),
        },
    );
}

function confirmDelete(service: ShopService) {
    serviceToDelete.value = service;
    showDeleteDialog.value = true;
}

function handleDelete() {
    if (!serviceToDelete.value) return;

    router.delete(`${base.value}/${serviceToDelete.value.id}`, {
        preserveScroll: true,
        onStart: () => (tableLoading.value = true),
        onSuccess: () => {
            showDeleteDialog.value = false;
            serviceToDelete.value = null;
        },
        onFinish: () => (tableLoading.value = false),
    });
}

function restoreService(service: ShopService) {
    router.patch(
        `${base.value}/${service.id}/restore`,
        {},
        {
            preserveScroll: true,
            onStart: () => (tableLoading.value = true),
            onFinish: () => (tableLoading.value = false),
        },
    );
}

function restoreAll() {
    router.post(
        `${base.value}/restore-all`,
        {},
        {
            preserveScroll: true,
            onStart: () => (tableLoading.value = true),
            onFinish: () => (tableLoading.value = false),
        },
    );
}

function toggleActive(service: ShopService) {
    router.patch(
        `${base.value}/${service.id}/toggle`,
        {},
        {
            preserveScroll: true,
            onStart: () => (tableLoading.value = true),
            onFinish: () => (tableLoading.value = false),
        },
    );
}
</script>

<template>
    <Head :title="showArchived ? 'Archived Services' : 'Services & Pricing'" />

    <ShopLayout
        :title="showArchived ? 'Archived Services' : 'Services & Pricing'"
    >
        <div class="space-y-6 px-6">
            <div
                v-if="!showArchived"
                class="grid grid-cols-2 gap-3 md:grid-cols-4"
            >
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
            </div>

            <div
                v-if="showArchived"
                class="flex flex-wrap items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3"
            >
                <Archive class="h-5 w-5 shrink-0 text-amber-600" />
                <div class="min-w-52 flex-1">
                    <p class="text-sm font-semibold text-amber-800">
                        Viewing Archived Services
                    </p>
                    <p class="text-xs text-amber-600">
                        Restore individual services or restore them all at once.
                    </p>
                </div>
                <Button
                    v-if="services.length"
                    size="sm"
                    variant="outline"
                    class="border-amber-300 text-amber-700 hover:bg-amber-100"
                    @click="restoreAll"
                >
                    <RotateCcw class="mr-1.5 h-4 w-4" /> Restore All
                </Button>
            </div>

            <CardContent class="space-y-4">
                <div class="flex flex-wrap items-center gap-2 pt-6">
                    <div class="relative min-w-52 flex-1">
                        <Search
                            class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                        />
                        <Input
                            v-model="search"
                            class="pl-8"
                            :placeholder="
                                showArchived
                                    ? 'Search archived services or categories...'
                                    : 'Search services or categories...'
                            "
                        />
                    </div>

                    <select
                        v-model="categoryFilter"
                        class="h-9 rounded-md border bg-background px-3 text-sm"
                        aria-label="Filter services by category"
                    >
                        <option value="">All categories</option>
                        <option
                            v-for="category in serviceCategories"
                            :key="category.value"
                            :value="category.value"
                        >
                            {{ category.label }}
                        </option>
                    </select>

                    <select
                        v-model="pricingFilter"
                        class="h-9 rounded-md border bg-background px-3 text-sm"
                        aria-label="Filter services by pricing model"
                    >
                        <option value="">All pricing</option>
                        <option value="per_kg">Per KG</option>
                        <option value="per_bundle">Per Bundle</option>
                    </select>

                    <select
                        v-if="!showArchived"
                        v-model="statusFilter"
                        class="h-9 rounded-md border bg-background px-3 text-sm"
                        aria-label="Filter services by status"
                    >
                        <option value="">All statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>

                    <Button size="sm" variant="outline" @click="resetFilters">
                        <RefreshCcw class="mr-1.5 h-4 w-4" /> Reset
                    </Button>
                    <Button
                        size="sm"
                        variant="outline"
                        :class="
                            showArchived
                                ? 'border-blue-300 bg-blue-50 text-blue-700'
                                : 'border-amber-300 bg-amber-50 text-amber-700'
                        "
                        @click="toggleArchived"
                    >
                        <Archive class="mr-1.5 h-4 w-4" />
                        {{ showArchived ? 'Active Services' : 'Archive' }}
                    </Button>
                    <Button
                        v-if="!showArchived"
                        size="sm"
                        @click="router.visit(`${base}/create`)"
                    >
                        <Plus class="mr-1.5 h-4 w-4" /> New Service
                    </Button>
                </div>

                <div class="overflow-hidden rounded-lg border">
                    <EasyDataTable
                        class="shop-data-table"
                        :headers="headers"
                        :items="tableItems"
                        :loading="tableLoading"
                        :rows-items="[5, 10, 15, 20, 50]"
                        :rows-per-page="10"
                        rows-of-page-separator-message="out of"
                        rows-per-page-message="Rows per page:"
                        buttons-pagination
                        alternating
                        hide-footer-if-one-page
                    >
                        <template #header-service_name="{ text }">
                            <button
                                type="button"
                                class="sortable-column"
                                @click="toggleSort('service_name')"
                            >
                                <span>{{ text }}</span>
                                <component
                                    :is="sortIcon('service_name')"
                                    class="h-3.5 w-3.5"
                                    :class="{
                                        'opacity-60': sortBy !== 'service_name',
                                    }"
                                />
                            </button>
                        </template>
                        <template #header-category_label="{ text }">
                            <button
                                type="button"
                                class="sortable-column"
                                @click="toggleSort('category_label')"
                            >
                                <span>{{ text }}</span>
                                <component
                                    :is="sortIcon('category_label')"
                                    class="h-3.5 w-3.5"
                                    :class="{
                                        'opacity-60':
                                            sortBy !== 'category_label',
                                    }"
                                />
                            </button>
                        </template>
                        <template #header-pricing_model="{ text }">
                            <button
                                type="button"
                                class="sortable-column"
                                @click="toggleSort('pricing_model')"
                            >
                                <span>{{ text }}</span>
                                <component
                                    :is="sortIcon('pricing_model')"
                                    class="h-3.5 w-3.5"
                                    :class="{
                                        'opacity-60':
                                            sortBy !== 'pricing_model',
                                    }"
                                />
                            </button>
                        </template>
                        <template #header-price_sort="{ text }">
                            <button
                                type="button"
                                class="sortable-column"
                                @click="toggleSort('price_sort')"
                            >
                                <span>{{ text }}</span>
                                <component
                                    :is="sortIcon('price_sort')"
                                    class="h-3.5 w-3.5"
                                    :class="{
                                        'opacity-60': sortBy !== 'price_sort',
                                    }"
                                />
                            </button>
                        </template>
                        <template #header-estimated_hours="{ text }">
                            <button
                                type="button"
                                class="sortable-column"
                                @click="toggleSort('estimated_hours')"
                            >
                                <span>{{ text }}</span>
                                <component
                                    :is="sortIcon('estimated_hours')"
                                    class="h-3.5 w-3.5"
                                    :class="{
                                        'opacity-60':
                                            sortBy !== 'estimated_hours',
                                    }"
                                />
                            </button>
                        </template>
                        <template #header-is_active="{ text }">
                            <button
                                type="button"
                                class="sortable-column"
                                @click="toggleSort('is_active')"
                            >
                                <span>{{ text }}</span>
                                <component
                                    :is="sortIcon('is_active')"
                                    class="h-3.5 w-3.5"
                                    :class="{
                                        'opacity-60': sortBy !== 'is_active',
                                    }"
                                />
                            </button>
                        </template>

                        <template #item-service_name="service">
                            <div class="py-1">
                                <p class="font-medium text-foreground">
                                    {{ service.service_name }}
                                </p>
                                <p
                                    v-if="service.description"
                                    class="mt-0.5 max-w-56 truncate text-xs text-muted-foreground"
                                >
                                    {{ service.description }}
                                </p>
                            </div>
                        </template>
                        <template #item-category_label="service">
                            <span
                                class="inline-flex rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700 ring-1 ring-sky-200"
                                >{{ service.category_label }}</span
                            >
                        </template>
                        <template #item-pricing_model="service">
                            <span
                                class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold ring-1"
                                :class="
                                    service.pricing_model === 'per_kg'
                                        ? 'bg-blue-50 text-blue-700 ring-blue-200'
                                        : 'bg-violet-50 text-violet-700 ring-violet-200'
                                "
                            >
                                <Weight
                                    v-if="service.pricing_model === 'per_kg'"
                                    class="h-3.5 w-3.5"
                                />
                                <Package v-else class="h-3.5 w-3.5" />
                                {{
                                    service.pricing_model === 'per_kg'
                                        ? 'Per KG'
                                        : 'Per Bundle'
                                }}
                            </span>
                        </template>
                        <template #item-price_sort="service">
                            <span class="font-medium text-foreground">{{
                                formatPrice(service)
                            }}</span>
                        </template>
                        <template #item-estimated_hours="service">
                            <span class="text-muted-foreground">{{
                                service.estimated_hours
                                    ? `${service.estimated_hours}h`
                                    : '—'
                            }}</span>
                        </template>
                        <template #item-is_active="service">
                            <span
                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1"
                                :class="
                                    service.is_active
                                        ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                        : 'bg-slate-100 text-slate-600 ring-slate-200'
                                "
                            >
                                {{ service.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </template>
                        <template #item-actions="service">
                            <div class="flex items-center justify-center gap-1">
                                <template v-if="!showArchived">
                                    <Button
                                        size="icon"
                                        variant="ghost"
                                        :title="
                                            service.is_active
                                                ? 'Deactivate'
                                                : 'Activate'
                                        "
                                        @click="toggleActive(service)"
                                    >
                                        <ToggleRight
                                            v-if="service.is_active"
                                            class="h-4 w-4 text-emerald-600"
                                        />
                                        <ToggleLeft
                                            v-else
                                            class="h-4 w-4 text-slate-500"
                                        />
                                    </Button>
                                    <Button
                                        size="icon"
                                        variant="ghost"
                                        title="Edit service"
                                        @click="
                                            router.visit(
                                                `${base}/${service.id}/edit`,
                                            )
                                        "
                                    >
                                        <Pencil class="h-4 w-4 text-blue-600" />
                                    </Button>
                                    <Button
                                        size="icon"
                                        variant="ghost"
                                        title="Archive service"
                                        @click="confirmDelete(service)"
                                    >
                                        <Trash2 class="h-4 w-4 text-red-600" />
                                    </Button>
                                </template>
                                <Button
                                    v-else
                                    size="sm"
                                    variant="outline"
                                    class="border-emerald-300 text-emerald-700 hover:bg-emerald-50"
                                    @click="restoreService(service)"
                                >
                                    <RotateCcw class="mr-1.5 h-4 w-4" /> Restore
                                </Button>
                            </div>
                        </template>
                        <template #empty-message>
                            <div
                                class="py-10 text-center text-muted-foreground"
                            >
                                <component
                                    :is="showArchived ? Archive : Scissors"
                                    class="mx-auto mb-2 h-10 w-10 opacity-30"
                                />
                                <p class="font-medium">
                                    {{
                                        showArchived
                                            ? 'No archived services found'
                                            : 'No services found'
                                    }}
                                </p>
                                <p class="mt-1 text-xs">
                                    Try adjusting the search or filters.
                                </p>
                            </div>
                        </template>
                    </EasyDataTable>
                </div>
            </CardContent>
        </div>

        <AlertDialog
            :open="showDeleteDialog"
            @update:open="showDeleteDialog = $event"
        >
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Archive Service</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to archive
                        <span class="font-semibold text-foreground">{{
                            serviceToDelete?.service_name
                        }}</span
                        >? It can be restored later.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="showDeleteDialog = false"
                        >Cancel</AlertDialogCancel
                    >
                    <AlertDialogAction
                        class="bg-red-600 text-white hover:bg-red-700"
                        @click="handleDelete"
                        >Archive Service</AlertDialogAction
                    >
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </ShopLayout>
</template>

<style scoped>
.shop-data-table {
    --easy-table-border: 0;
    --easy-table-row-border: 1px solid var(--border);
    --easy-table-header-background-color: var(--muted);
    --easy-table-header-font-color: var(--muted-foreground);
    --easy-table-header-font-size: 12px;
    --easy-table-header-height: 52px;
    --easy-table-header-item-padding: 10px 12px;
    --easy-table-body-row-background-color: var(--background);
    --easy-table-body-even-row-background-color: color-mix(
        in srgb,
        var(--muted) 35%,
        transparent
    );
    --easy-table-body-row-font-color: var(--foreground);
    --easy-table-body-even-row-font-color: var(--foreground);
    --easy-table-body-row-hover-background-color: color-mix(
        in srgb,
        var(--muted) 65%,
        transparent
    );
    --easy-table-body-row-hover-font-color: var(--foreground);
    --easy-table-body-row-height: 64px;
    --easy-table-body-item-padding: 10px 12px;
    --easy-table-message-font-color: var(--muted-foreground);
    --easy-table-footer-background-color: var(--background);
    --easy-table-footer-font-color: var(--muted-foreground);
    --easy-table-footer-font-size: 12px;
    --easy-table-footer-height: 56px;
    --easy-table-footer-padding: 0 16px;
    --easy-table-buttons-pagination-border: 1px solid var(--border);
    width: 100%;
}

.sortable-column {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: inherit;
    font: inherit;
}

.sortable-column:hover {
    color: var(--foreground);
}

:deep(.vue3-easy-data-table__main) {
    background: var(--background);
}

:deep(.vue3-easy-data-table__main table) {
    min-width: 1080px;
}
</style>
