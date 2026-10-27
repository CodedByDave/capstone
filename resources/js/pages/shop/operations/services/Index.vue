<script setup lang="ts">
import {
    DataTable,
    DataTableToolbar,
    RowActions,
    StatusPill,
    type DataTableHeader,
} from '@/components/data-table';
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
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useDataTable } from '@/composables/useDataTable';
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
    CheckCircle2,
    Download,
    FileWarning,
    Loader2,
    Package,
    Pencil,
    Plus,
    RefreshCcw,
    RotateCcw,
    Scissors,
    Shirt,
    ToggleLeft,
    ToggleRight,
    Trash2,
    Upload,
    Weight,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

interface ShopService {
    id: number;
    service_name: string;
    category: ServiceCategory | string;
    supplies?: { id: number; name: string }[];
    description: string | null;
    is_active: boolean;
    pricing_model: 'per_kg' | 'per_bundle' | 'per_piece';
    price_per_piece: string | null;
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

const props = defineProps<{
    services: ShopService[];
    showArchived: boolean;
    customCategories: { value: string; label: string }[];
}>();

const page = usePage<AppPageProps>();
const isOwner = computed(() => page.props.auth.user.role === 'owner');
const base = computed(() =>
    isOwner.value ? '/shop/operations/services' : '/staff/operations/services',
);

const categoryFilter = ref('');
const pricingFilter = ref('');
const tableLoading = ref(false);
const serviceToDelete = ref<ShopService | null>(null);
const showDeleteDialog = ref(false);

const headers = computed<DataTableHeader[]>(() => [
    { text: 'Service', value: 'service_name', width: 230, sortable: true },
    { text: 'Category', value: 'category_label', width: 240, sortable: true },
    {
        text: 'Pricing model',
        value: 'pricing_model',
        width: 150,
        sortable: true,
    },
    { text: 'Price', value: 'price_sort', width: 160, sortable: true },
    {
        text: 'Turnaround',
        value: 'estimated_hours',
        width: 135,
        sortable: true,
    },
    ...(!props.showArchived
        ? [
              {
                  text: 'Status',
                  value: 'is_active',
                  width: 115,
                  sortable: true,
              },
          ]
        : []),
    { text: 'Actions', value: 'actions', width: 145, align: 'center' as const },
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
const perPieceServices = computed(
    () =>
        props.services.filter(
            (service) => service.pricing_model === 'per_piece',
        ).length,
);
const statCards = computed(() => [
    { label: 'Total Services', value: totalServices.value },
    { label: 'Active Services', value: activeServices.value },
    { label: 'Per KG', value: perKgServices.value },
    { label: 'Per Bundle', value: perBundleServices.value },
    { label: 'Per Piece', value: perPieceServices.value },
]);

const serviceRows = computed<ServiceTableItem[]>(() =>
    props.services.map((service) => ({
        ...service,
        category_label:
            props.customCategories.find((c) => c.value === service.category)
                ?.label ??
            serviceCategoryLabel(service.category as ServiceCategory),
        price_sort: Number(
            service.pricing_model === 'per_kg'
                ? service.price_per_kg
                : service.pricing_model === 'per_piece'
                  ? service.price_per_piece
                  : service.bundle_price,
        ),
        actions: '',
    })),
);

const {
    search,
    status: statusFilter,
    options,
    pageItems,
    total,
    statusCounts,
    reset,
} = useDataTable(serviceRows, {
    searchFields: (service) => [
        service.service_name,
        service.category_label,
        service.description,
    ],
    status: (service) =>
        props.showArchived ? '' : service.is_active ? 'active' : 'inactive',
    filter: (service) =>
        (!categoryFilter.value || service.category === categoryFilter.value) &&
        (!pricingFilter.value || service.pricing_model === pricingFilter.value),
    sortValue: (service, column) => {
        const value = service[column as keyof ServiceTableItem];

        return typeof value === 'boolean' ? Number(value) : value;
    },
    sortBy: 'service_name',
});

const statusOptions = computed(() => [
    {
        value: '',
        label: 'All',
        count:
            (statusCounts.value.active ?? 0) +
            (statusCounts.value.inactive ?? 0),
    },
    { value: 'active', label: 'Active', count: statusCounts.value.active ?? 0 },
    {
        value: 'inactive',
        label: 'Inactive',
        count: statusCounts.value.inactive ?? 0,
    },
]);

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
    if (service.pricing_model === 'per_kg') {
        return `${formatCurrency(service.price_per_kg)}/kg`;
    }

    if (service.pricing_model === 'per_piece') {
        return `${formatCurrency(service.price_per_piece)}/piece`;
    }

    return `${formatCurrency(service.bundle_price)} / ${service.bundle_weight_kg}kg bundle`;
}

function resetFilters() {
    categoryFilter.value = '';
    pricingFilter.value = '';
    reset();
}

// ─── CSV Import ───────────────────────────────────────────────────────────────

const isImportOpen = ref(false);
const importFile = ref<File | null>(null);
const importErrors = ref<string[]>([]);
const importing = ref(false);
const isDragging = ref(false);
const fileInputRef = ref<HTMLInputElement | null>(null);

function openImport() {
    importFile.value = null;
    importErrors.value = [];
    isImportOpen.value = true;
}

function acceptFile(file: File | undefined) {
    if (file?.name.toLowerCase().endsWith('.csv')) {
        importFile.value = file;
        importErrors.value = [];
    } else {
        importFile.value = null;
        importErrors.value = ['Please choose a valid .csv file.'];
    }
}

function clearFile() {
    importFile.value = null;
    importErrors.value = [];
    if (fileInputRef.value) fileInputRef.value.value = '';
}

async function submitImport() {
    if (!importFile.value || importing.value) return;

    importing.value = true;
    importErrors.value = [];

    const formData = new FormData();
    formData.append('csv_file', importFile.value);

    try {
        const response = await fetch(`${base.value}/import`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN':
                    document
                        .querySelector<HTMLMetaElement>(
                            'meta[name="csrf-token"]',
                        )
                        ?.getAttribute('content') ?? '',
                Accept: 'application/json',
            },
            body: formData,
        });
        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const validation = data.errors?.csv_file;
            importErrors.value = Array.isArray(data.errors)
                ? data.errors
                : Array.isArray(validation)
                  ? validation
                  : [data.message ?? 'The import failed. Please try again.'];
            toast.error('Import failed. Please fix the errors and try again.');
            return;
        }

        toast.success(data.message ?? 'Services imported successfully.');
        isImportOpen.value = false;
        router.reload({ only: ['services'] });
    } catch {
        importErrors.value = [
            'Failed to connect to the server. Please try again.',
        ];
    } finally {
        importing.value = false;
    }
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
            <div class="flex flex-wrap items-center justify-end gap-2">
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
                <template v-if="!showArchived">
                    <Button size="sm" variant="outline" as-child>
                        <a :href="`${base}/export`">
                            <Download class="mr-1.5 h-4 w-4" /> Export CSV
                        </a>
                    </Button>
                    <Button size="sm" variant="outline" @click="openImport">
                        <Upload class="mr-1.5 h-4 w-4" /> Import CSV
                    </Button>
                    <Button size="sm" @click="router.visit(`${base}/create`)">
                        <Plus class="mr-1.5 h-4 w-4" /> New Service
                    </Button>
                </template>
            </div>

            <div
                v-if="!showArchived"
                class="grid grid-cols-2 gap-3 md:grid-cols-5"
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

            <DataTable
                v-model:options="options"
                :headers="headers"
                :items="pageItems"
                :total="total"
                :loading="tableLoading"
                noun="services"
                :empty-icon="showArchived ? Archive : Scissors"
                :empty-message="
                    showArchived
                        ? 'No archived services found. Try adjusting the search or filters.'
                        : 'No services found. Try adjusting the search or filters.'
                "
                :min-width="1080"
                @reset="resetFilters"
            >
                <template #toolbar>
                    <DataTableToolbar
                        v-model:search="search"
                        v-model:status="statusFilter"
                        :statuses="showArchived ? [] : statusOptions"
                        :search-placeholder="
                            showArchived
                                ? 'Search archived services or categories...'
                                : 'Search services or categories...'
                        "
                        search-label="Search services"
                        status-label="Filter services by status"
                    >
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
                            <option
                                v-for="category in customCategories"
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
                            <option value="per_piece">Per Piece</option>
                        </select>

                        <Button
                            size="sm"
                            variant="outline"
                            @click="resetFilters"
                        >
                            <RefreshCcw class="mr-1.5 h-4 w-4" /> Reset
                        </Button>
                    </DataTableToolbar>
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
                    <p
                        v-if="service.supplies?.length"
                        class="mt-1 flex items-center gap-1 text-xs text-muted-foreground"
                    >
                        <Package class="h-3 w-3" />
                        Uses {{ service.supplies.length }}
                        {{
                            service.supplies.length === 1
                                ? 'supply'
                                : 'supplies'
                        }}
                    </p>
                </template>
                <template #item-pricing_model="service">
                    <span
                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold whitespace-nowrap ring-1"
                        :class="
                            service.pricing_model === 'per_kg'
                                ? 'bg-blue-50 text-blue-700 ring-blue-200'
                                : service.pricing_model === 'per_piece'
                                  ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                  : 'bg-violet-50 text-violet-700 ring-violet-200'
                        "
                    >
                        <Weight
                            v-if="service.pricing_model === 'per_kg'"
                            class="h-3.5 w-3.5"
                        />
                        <Shirt
                            v-else-if="service.pricing_model === 'per_piece'"
                            class="h-3.5 w-3.5"
                        />
                        <Package v-else class="h-3.5 w-3.5" />
                        {{
                            service.pricing_model === 'per_kg'
                                ? 'Per KG'
                                : service.pricing_model === 'per_piece'
                                  ? 'Per Piece'
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
                    <StatusPill
                        :tone="service.is_active ? 'success' : 'neutral'"
                    >
                        {{ service.is_active ? 'Active' : 'Inactive' }}
                    </StatusPill>
                </template>
                <template #item-actions="service">
                    <RowActions
                        :label="service.service_name"
                        :actions="[
                            {
                                label: service.is_active
                                    ? 'Deactivate'
                                    : 'Activate',
                                icon: service.is_active
                                    ? ToggleRight
                                    : ToggleLeft,
                                show: !showArchived,
                                onSelect: () => toggleActive(service),
                            },
                            {
                                label: 'Edit',
                                icon: Pencil,
                                show: !showArchived,
                                onSelect: () =>
                                    router.visit(`${base}/${service.id}/edit`),
                            },
                            {
                                label: 'Archive',
                                icon: Trash2,
                                destructive: true,
                                show: !showArchived,
                                onSelect: () => confirmDelete(service),
                            },
                            {
                                label: 'Restore',
                                icon: RotateCcw,
                                show: showArchived,
                                onSelect: () => restoreService(service),
                            },
                        ]"
                    />
                </template>
            </DataTable>
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

        <!-- Import Dialog -->
        <Dialog v-model:open="isImportOpen">
            <DialogContent class="max-w-lg">
                <DialogHeader>
                    <DialogTitle>Import Services via CSV</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 text-sm">
                    <div
                        class="rounded-lg border bg-muted/40 px-4 py-3 text-xs leading-relaxed text-muted-foreground"
                    >
                        <p class="mb-1 font-semibold text-foreground">
                            Required CSV columns:
                        </p>
                        <code class="block font-mono break-all">
                            service_name, category, description, pricing_model,
                            price_per_kg, bundle_price, bundle_weight_kg,
                            estimated_hours, status, price_per_piece
                        </code>
                        <p class="mt-2">
                            <span class="font-medium">category</span>:
                            <code>self_service</code>,
                            <code>full_service</code>,
                            <code>dry_cleaning</code>,
                            <code>ironing_pressing</code>,
                            <code>pickup_delivery</code>, or
                            <code>specialty_bulk</code>
                        </p>
                        <p class="mt-1">
                            <span class="font-medium">pricing_model</span>:
                            <code>per_kg</code> (needs price_per_kg) or
                            <code>per_bundle</code> (needs bundle_price and
                            bundle_weight_kg), or <code>per_piece</code> (needs
                            price_per_piece) &nbsp;|&nbsp;
                            <span class="font-medium">status</span>:
                            <code>Active</code> or <code>Inactive</code>
                        </p>
                        <a
                            :href="`${base}/import-template`"
                            class="mt-2 inline-flex items-center gap-1 font-medium text-primary hover:underline"
                        >
                            <Download class="h-3 w-3" /> Download blank template
                        </a>
                    </div>

                    <div
                        class="relative cursor-pointer rounded-xl border-2 border-dashed transition-colors"
                        :class="
                            isDragging
                                ? 'border-primary bg-primary/5'
                                : 'border-muted-foreground/30 hover:border-primary/50'
                        "
                        @dragover.prevent="isDragging = true"
                        @dragleave="isDragging = false"
                        @drop.prevent="
                            isDragging = false;
                            acceptFile($event.dataTransfer?.files?.[0]);
                        "
                        @click="fileInputRef?.click()"
                    >
                        <input
                            ref="fileInputRef"
                            type="file"
                            accept=".csv"
                            class="hidden"
                            @change="
                                acceptFile(
                                    ($event.target as HTMLInputElement)
                                        .files?.[0],
                                )
                            "
                        />
                        <div
                            class="flex flex-col items-center justify-center px-4 py-8 text-center select-none"
                        >
                            <template v-if="!importFile">
                                <Upload
                                    class="mb-2 h-8 w-8 text-muted-foreground"
                                />
                                <p class="font-medium text-foreground">
                                    Click to browse or drag & drop
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Only .csv files are accepted
                                </p>
                            </template>
                            <template v-else>
                                <div
                                    class="flex items-center gap-2 text-green-600"
                                >
                                    <CheckCircle2 class="h-5 w-5" />
                                    <span class="font-medium">{{
                                        importFile.name
                                    }}</span>
                                </div>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    {{ (importFile.size / 1024).toFixed(1) }} KB
                                </p>
                                <button
                                    type="button"
                                    class="mt-2 flex items-center gap-1 text-xs text-red-500 hover:underline"
                                    @click.stop="clearFile"
                                >
                                    <X class="h-3 w-3" /> Remove file
                                </button>
                            </template>
                        </div>
                    </div>

                    <div
                        v-if="importErrors.length"
                        class="max-h-48 overflow-y-auto rounded-lg border border-red-200 bg-red-50 px-4 py-3"
                    >
                        <div
                            class="mb-2 flex items-center gap-2 font-semibold text-red-700"
                        >
                            <FileWarning class="h-4 w-4 flex-shrink-0" />
                            Import failed. Nothing was imported; please fix:
                        </div>
                        <ul class="space-y-1">
                            <li
                                v-for="(error, i) in importErrors"
                                :key="i"
                                class="flex items-start gap-1.5 text-xs text-red-600"
                            >
                                <span class="mt-0.5 flex-shrink-0">•</span
                                >{{ error }}
                            </li>
                        </ul>
                    </div>
                </div>

                <DialogFooter class="mt-2">
                    <Button variant="outline" @click="isImportOpen = false"
                        >Cancel</Button
                    >
                    <Button
                        :disabled="!importFile || importing"
                        @click="submitImport"
                    >
                        <Loader2
                            v-if="importing"
                            class="mr-1.5 h-4 w-4 animate-spin"
                        />
                        <Upload v-else class="mr-1.5 h-4 w-4" />
                        {{ importing ? 'Importing...' : 'Import' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </ShopLayout>
</template>
