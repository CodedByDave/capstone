<script setup lang="ts">
import { usePermissions } from '@/composables/usePermissions';
import { useUndoToast } from '@/composables/useUndoToast';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import {
    BulkActionButton,
    DataTable,
    DataTableToolbar,
    KpiTiles,
    RowActions,
    StatusPill,
    type DataTableHeader,
    type StatusTone,
} from '@/components/data-table';
import DispatchMapPanel from '@/components/logistics/DispatchMapPanel.vue';
import ActionsMenu from '@/components/shop/ActionsMenu.vue';
import PageHeader from '@/components/shop/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { usePaginatorTable } from '@/composables/useDataTable';
import {
    Archive,
    Camera,
    CameraOff,
    CheckCircle2,
    Clock,
    Download,
    Loader2,
    MapPin,
    Package,
    Phone,
    Plus,
    RotateCcw,
    Trash2,
    Truck,
    User,
    Weight,
    X,
    XCircle,
} from 'lucide-vue-next';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Rider {
    id: number;
    name: string;
    vehicle_type: string | null;
    available: boolean;
    /** Why the rider cannot take a run now: On delivery, No shift today or Offline. */
    unavailable_reason: string | null;
}

interface DeliveryPhoto {
    public_id: string;
    created_at: string;
    /** "pickup", "delivery" and "cash" are the rider's required photos; "proof" is added by the shop. */
    kind?: string;
}

const PHOTO_LABELS: Record<string, string> = {
    pickup: 'Pickup',
    delivery: 'Delivery',
    cash: 'Cash',
};

interface Delivery {
    id: number;
    public_id: string;
    shop_order_id: number | null;
    type: 'pickup' | 'delivery';
    rider_id: number | null;
    /** Set when the order's weight is outside the shop's rider load limit, so no rider is auto-assigned. */
    load_warning?: string | null;
    photos: DeliveryPhoto[];
    customer_name: string;
    customer_phone: string | null;
    delivery_address: string | null;
    status: string;
    notes: string | null;
    assigned_at: string | null;
    picked_up_at: string | null;
    delivered_at: string | null;
    created_at: string;
    rider: Rider | null;
    order: { id: number; order_number: string } | null;
}

interface Stats {
    pending: number;
    assigned: number;
    picked_up: number;
    delivered: number;
    failed: number;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    deliveries: {
        data: Delivery[];
        links: any[];
        current_page: number;
        last_page: number;
        per_page?: number;
        total: number;
    };
    riders: Rider[];
    stats: Stats;
    map: {
        shop: {
            public_id: string;
            name: string | null;
            lat: number | null;
            lng: number | null;
        } | null;
        stops: {
            public_id: string;
            customer_name: string;
            address: string | null;
            status: string;
            type: 'pickup' | 'delivery';
            lat: number;
            lng: number;
        }[];
    };
    filters: { status?: string };
    showArchived: boolean;
}>();

// ─── Flash toast ──────────────────────────────────────────────────────────────

const page = usePage();
const { can, isOwner } = usePermissions();
const base = isOwner.value ? '/shop/logistics' : '/staff/logistics';

// An archived run is read-only: it can be viewed and restored, nothing else.
const selected = ref<Array<string | number>>([]);

const canChange = (action: string) =>
    !props.showArchived && can('Logistics', action);
onMounted(() => {
    const flash = (page.props as any).toast as
        | { type: string; message: string }
        | undefined;
    if (!flash) return;
    if (flash.type === 'success') toast.success(flash.message);
    else toast.error(flash.message);
});

// ─── Status config ────────────────────────────────────────────────────────────

const STATUS = {
    pending: {
        label: 'Pending',
        dot: 'bg-amber-400',
        badge: 'bg-amber-100 text-amber-700',
        icon: Clock,
    },
    assigned: {
        label: 'Assigned',
        dot: 'bg-blue-400',
        badge: 'bg-blue-100 text-blue-700',
        icon: User,
    },
    picked_up: {
        label: 'Picked Up',
        dot: 'bg-violet-400',
        badge: 'bg-violet-100 text-violet-700',
        icon: Package,
    },
    delivered: {
        label: 'Delivered',
        dot: 'bg-green-500',
        badge: 'bg-green-100 text-green-700',
        icon: CheckCircle2,
    },
    failed: {
        label: 'Failed',
        dot: 'bg-red-400',
        badge: 'bg-red-100 text-red-700',
        icon: XCircle,
    },
} as Record<string, any>;

// A pickup run collects the laundry from the customer and brings it to the shop,
// so the same statuses read differently from a delivery back to the customer.
const PICKUP_STATUS_LABEL: Record<string, string> = {
    picked_up: 'Collected',
    delivered: 'At shop',
};
function statusLabel(d: Delivery, status = d.status): string {
    if (d.type === 'pickup' && PICKUP_STATUS_LABEL[status]) {
        return PICKUP_STATUS_LABEL[status];
    }
    return STATUS[status]?.label ?? status;
}

// ─── Filter ───────────────────────────────────────────────────────────────────

const activeFilter = ref(props.filters.status ?? 'all');

function setFilter(f: string) {
    activeFilter.value = f;
    const archived = props.showArchived ? { archived: 1 } : {};
    router.get(base, f === 'all' ? archived : { ...archived, status: f }, {
        preserveState: true,
        preserveScroll: true,
    });
}

// The shop can move a run on the board without a photo (e.g. a rider's phone died), so the board
// says when one is missing. Adding one from "Proof photos" clears it; a courier takes no photos.
const missingProofPhoto = (d: Delivery) => {
    // Received at the shop without a rider ever collecting it (the customer dropped it off).
    if (d.type === 'pickup' && d.status === 'delivered' && !d.picked_up_at) {
        return false;
    }

    const reachedProofStep =
        d.status === 'delivered' ||
        (d.type === 'pickup' && d.status === 'picked_up');

    return (
        reachedProofStep && !d.photos?.some((photo) => photo.kind !== 'cash')
    );
};

// ─── Proof photos dialog ──────────────────────────────────────────────────────

const photosModal = ref<{ open: boolean; delivery: Delivery | null }>({
    open: false,
    delivery: null,
});

function openPhotos(delivery: Delivery) {
    photosModal.value = { open: true, delivery };
}

// The open delivery, kept fresh as uploads reload the page props.
const modalDelivery = computed(
    () =>
        props.deliveries.data.find(
            (d) => d.id === photosModal.value.delivery?.id,
        ) ?? photosModal.value.delivery,
);

// ─── Proof photos ─────────────────────────────────────────────────────────────

const uploadingPhotos = ref(false);
const removingPhoto = ref<string | null>(null);

function uploadPhotos(event: Event) {
    const input = event.target as HTMLInputElement;
    const files = Array.from(input.files ?? []);
    const delivery = modalDelivery.value;
    if (!files.length || !delivery) return;

    uploadingPhotos.value = true;
    router.post(
        `${base}/${delivery.public_id}/photos`,
        { photos: files },
        {
            forceFormData: true,
            preserveScroll: true,
            preserveState: true,
            onError: (errors) => {
                toast.error(Object.values(errors)[0] ?? 'Upload failed.');
            },
            onFinish: () => {
                uploadingPhotos.value = false;
                input.value = '';
            },
        },
    );
}

function removePhoto(photo: DeliveryPhoto) {
    if (!confirm('Remove this proof photo?')) return;
    removingPhoto.value = photo.public_id;
    router.delete(`${base}/photos/${photo.public_id}`, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            removingPhoto.value = null;
        },
    });
}

// ─── Delete ───────────────────────────────────────────────────────────────────

const deleting = ref<string | null>(null);

// A pickup no rider has taken: the customer may have been told to bring the laundry in themselves.
const canMarkDroppedOff = (d: Delivery) =>
    canChange('update') &&
    d.type === 'pickup' &&
    d.status === 'pending' &&
    d.rider_id === null;

function markDroppedOff(d: Delivery) {
    if (
        !confirm(
            `Mark ${d.customer_name}'s laundry as received at the shop? No rider will be sent for it.`,
        )
    ) {
        return;
    }
    router.post(
        `${base}/${d.public_id}/dropped-off`,
        {},
        {
            preserveScroll: true,
            onSuccess: () =>
                toast.success('Laundry marked as received at the shop.'),
            onError: (errors) =>
                toast.error(Object.values(errors)[0] ?? 'Could not update.'),
        },
    );
}

const { undoable } = useUndoToast();

// Archiving can be undone, so it happens at once and a toast offers Undo for a few seconds.
function archiveDelivery(d: Delivery) {
    deleting.value = d.public_id;
    router.delete(`${base}/${d.public_id}`, {
        preserveScroll: true,
        onSuccess: () =>
            undoable(
                `${d.type === 'pickup' ? 'Pickup' : 'Delivery'} for ${d.customer_name} archived.`,
                () =>
                    router.post(
                        `${base}/${d.public_id}/restore`,
                        {},
                        { preserveScroll: true },
                    ),
            ),
        onError: (errors) =>
            toast.error(Object.values(errors)[0] ?? 'Could not archive.'),
        onFinish: () => {
            deleting.value = null;
        },
    });
}

function restoreDelivery(d: Delivery) {
    router.post(
        `${base}/${d.public_id}/restore`,
        {},
        {
            preserveScroll: true,
            onError: (errors) =>
                toast.error(Object.values(errors)[0] ?? 'Could not restore.'),
        },
    );
}

function toggleArchived() {
    router.get(base, props.showArchived ? {} : { archived: 1 }, {
        preserveState: false,
        replace: true,
    });
}

// ─── Table ────────────────────────────────────────────────────────────────────

const { options, loading } = usePaginatorTable(
    () => props.deliveries,
    ['deliveries'],
);

const STATUS_TONE: Record<string, StatusTone> = {
    pending: 'warning',
    assigned: 'info',
    picked_up: 'info',
    delivered: 'success',
    failed: 'danger',
};

const statusFilterOptions = computed(() => [
    {
        value: 'all',
        label: 'All',
        count: Object.values(props.stats).reduce(
            (sum, count) => sum + Number(count ?? 0),
            0,
        ),
    },
    ...Object.keys(STATUS).map((key) => ({
        value: key,
        label: STATUS[key].label,
        count: props.stats[key as keyof Stats],
    })),
]);

const headers: DataTableHeader[] = [
    { text: 'Customer', value: 'customer_name', sortable: true, width: 260 },
    { text: 'Order', value: 'order', sortable: true },
    { text: 'Rider / courier', value: 'rider', sortable: true },
    { text: 'Status', value: 'status', sortable: true },
    { text: 'Date', value: 'created_at', sortable: true },
    { text: 'Actions', value: 'actions', align: 'center', width: 230 },
];

// ─── Helpers ─────────────────────────────────────────────────────────────────

function fmtDate(d: string) {
    return new Date(d).toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
function fmtTime(d: string) {
    return new Date(d).toLocaleTimeString('en-PH', {
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head :title="showArchived ? 'Archived Deliveries' : 'Logistics'" />

    <ShopLayout :title="showArchived ? 'Archived Deliveries' : 'Logistics'">
        <div class="space-y-6 px-6">
            <!-- ── Header ── -->
            <PageHeader
                :title="showArchived ? 'Archived Deliveries' : 'Deliveries'"
                description="Track and manage laundry deliveries. Drivers are employees whose position is Driver."
            >
                <Button
                    v-if="showArchived && can('Logistics', 'archive')"
                    variant="back"
                    class="h-11 gap-1.5"
                    @click="toggleArchived"
                >
                    <Archive class="h-4 w-4" />
                    Back to Active
                </Button>
                <ActionsMenu
                    v-if="!showArchived"
                    :items="[
                        {
                            label: 'Export CSV',
                            icon: Download,
                            href: `${isOwner ? '/shop' : '/staff'}/export/logistics`,
                        },
                        {
                            label: 'Archived deliveries',
                            icon: Archive,
                            onSelect: toggleArchived,
                            separator: true,
                            hidden: !can('Logistics', 'archive'),
                        },
                    ]"
                />
            </PageHeader>

            <!-- ── Archived Banner ── -->
            <div
                v-if="showArchived"
                class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3"
            >
                <Archive class="h-5 w-5 shrink-0 text-amber-600" />
                <div>
                    <p class="text-sm font-semibold text-amber-800">
                        Viewing Archived Deliveries
                    </p>
                    <p class="text-xs text-amber-700">
                        Archived runs keep their details and proof photos.
                        Restore one to put it back on the board.
                    </p>
                </div>
            </div>

            <!-- ── Stats ── -->
            <KpiTiles
                v-if="!showArchived"
                :tiles="
                    Object.entries(STATUS).map(([key, cfg]) => ({
                        label: cfg.label,
                        value: props.stats[key as keyof Stats],
                        active: activeFilter === key,
                        onSelect: () => setFilter(key),
                    }))
                "
            />

            <!-- ── Dispatch map ── -->
            <DispatchMapPanel
                v-if="!showArchived"
                :shop="props.map.shop"
                :stops="props.map.stops"
            />

            <!-- ── Deliveries Table ── -->
            <DataTable
                v-model:options="options"
                v-model:selected="selected"
                item-key="public_id"
                :headers="headers"
                :items="props.deliveries.data"
                :total="props.deliveries.total"
                :loading="loading"
                noun="deliveries"
                :empty-icon="showArchived ? Archive : Truck"
                :empty-message="
                    showArchived
                        ? 'No archived deliveries.'
                        : 'No deliveries found.'
                "
                :resettable="activeFilter !== 'all'"
                :min-width="960"
                hide-rows-per-page
                @reset="setFilter('all')"
            >
                <template #bulk-actions>
                    <BulkActionButton
                        v-if="can('Logistics', 'archive')"
                        resource="delivery"
                        :action="showArchived ? 'restore' : 'archive'"
                        :ids="selected"
                        noun="deliveries"
                        @done="selected = []"
                    />
                </template>

                <template #toolbar>
                    <DataTableToolbar
                        :resettable="activeFilter !== 'all'"
                        @reset="setFilter('all')"
                        :status="activeFilter"
                        :statuses="statusFilterOptions"
                        status-label="Filter by delivery status"
                        hide-search
                        @update:status="setFilter"
                    />
                </template>

                <!-- Customer -->
                <template #item-customer_name="d">
                    <p class="text-sm font-medium">{{ d.customer_name }}</p>
                    <span
                        class="mt-0.5 inline-flex items-center gap-1 rounded-full px-1.5 py-0.5 text-[10px] font-medium"
                        :class="
                            d.type === 'pickup'
                                ? 'bg-sky-100 text-sky-700'
                                : 'bg-emerald-100 text-emerald-700'
                        "
                    >
                        <Package class="h-2.5 w-2.5" />
                        {{
                            d.type === 'pickup'
                                ? 'Pickup from customer'
                                : 'Deliver to customer'
                        }}
                    </span>
                    <p
                        v-if="d.customer_phone"
                        class="mt-0.5 flex items-center gap-1 text-xs text-muted-foreground"
                    >
                        <Phone class="h-3 w-3" />
                        {{ d.customer_phone }}
                    </p>
                    <p
                        v-if="d.delivery_address"
                        class="mt-0.5 flex items-start gap-1 text-xs text-muted-foreground"
                    >
                        <MapPin class="mt-0.5 h-3 w-3 shrink-0" />
                        <span class="line-clamp-1">{{
                            d.delivery_address
                        }}</span>
                    </p>
                </template>

                <!-- Order -->
                <template #item-order="d">
                    <span class="font-mono text-xs text-muted-foreground">
                        {{ d.order?.order_number ?? '—' }}
                    </span>
                </template>

                <!-- Rider -->
                <template #item-rider="d">
                    <div class="text-xs">
                        <span v-if="d.rider" class="font-medium">{{
                            d.rider.name
                        }}</span>
                        <span v-else class="text-muted-foreground italic"
                            >Unassigned</span
                        >
                        <p
                            v-if="d.photos?.length"
                            class="mt-1 flex items-center gap-1 text-muted-foreground"
                        >
                            <Camera class="h-3 w-3" />
                            {{ d.photos.length }}
                            proof
                            {{ d.photos.length === 1 ? 'photo' : 'photos' }}
                        </p>
                    </div>
                </template>

                <!-- Status -->
                <template #item-status="d">
                    <StatusPill :tone="STATUS_TONE[d.status] ?? 'neutral'">
                        {{ statusLabel(d) }}
                    </StatusPill>
                    <div v-if="missingProofPhoto(d)" class="mt-1">
                        <StatusPill tone="warning" :icon="CameraOff">
                            No photo
                        </StatusPill>
                    </div>
                    <div v-if="d.load_warning" class="mt-1">
                        <StatusPill tone="warning" :icon="Weight">
                            {{ d.load_warning }}
                        </StatusPill>
                    </div>
                    <div class="mt-1 space-y-0.5 text-xs text-muted-foreground">
                        <p v-if="d.picked_up_at">
                            Picked up {{ fmtTime(d.picked_up_at) }}
                        </p>
                        <p v-if="d.delivered_at">
                            Delivered {{ fmtTime(d.delivered_at) }}
                        </p>
                    </div>
                </template>

                <!-- Date -->
                <template #item-created_at="d">
                    <span
                        class="text-xs whitespace-nowrap text-muted-foreground"
                    >
                        {{ fmtDate(d.created_at) }}
                    </span>
                </template>

                <!-- Actions -->
                <template #item-actions="d">
                    <RowActions
                        :label="`delivery for ${d.customer_name}`"
                        :actions="[
                            {
                                label: 'Customer dropped off',
                                icon: Package,
                                show: canMarkDroppedOff(d),
                                onSelect: () => markDroppedOff(d),
                            },
                            {
                                label: 'Proof photos',
                                icon: Camera,
                                show:
                                    canChange('update') ||
                                    (d.photos?.length ?? 0) > 0,
                                onSelect: () => openPhotos(d),
                            },
                            {
                                label: 'Archive delivery',
                                icon:
                                    deleting === d.public_id
                                        ? Loader2
                                        : Archive,
                                destructive: true,
                                disabled: deleting === d.public_id,
                                show: canChange('archive'),
                                onSelect: () => archiveDelivery(d),
                            },
                            {
                                label: 'Restore delivery',
                                icon: RotateCcw,
                                show:
                                    showArchived && can('Logistics', 'archive'),
                                onSelect: () => restoreDelivery(d),
                            },
                        ]"
                    >
                    </RowActions>
                </template>
            </DataTable>
        </div>
    </ShopLayout>

    <!-- ── Proof photos ──────────────────────────────────────────────────────── -->
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="photosModal.open"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm"
                @click.self="photosModal.open = false"
            >
                <div
                    class="flex max-h-[90vh] w-full max-w-md flex-col overflow-hidden rounded-2xl border bg-background shadow-xl"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="proof-photos-title"
                >
                    <div
                        class="flex items-center justify-between border-b px-5 py-4"
                    >
                        <div>
                            <p
                                id="proof-photos-title"
                                class="text-sm font-semibold"
                            >
                                Proof photos
                            </p>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                {{ photosModal.delivery?.customer_name }}
                            </p>
                        </div>
                        <button
                            type="button"
                            class="flex h-9 w-9 items-center justify-center rounded-lg hover:bg-muted"
                            aria-label="Close"
                            @click="photosModal.open = false"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="space-y-3 overflow-y-auto px-5 py-4">
                        <!-- Proof photos -->
                        <div class="space-y-2 border-t pt-3">
                            <div class="flex items-center justify-between">
                                <label
                                    class="flex items-center gap-1.5 text-xs font-medium"
                                >
                                    <Camera class="h-3.5 w-3.5" />
                                    Proof photos
                                    <span
                                        class="font-normal text-muted-foreground"
                                        >(the customer can see these)</span
                                    >
                                </label>
                                <label
                                    v-if="canChange('update')"
                                    class="inline-flex cursor-pointer items-center gap-1 rounded-lg border px-2.5 py-1 text-xs font-medium transition-colors hover:bg-muted"
                                    :class="
                                        uploadingPhotos
                                            ? 'pointer-events-none opacity-60'
                                            : ''
                                    "
                                >
                                    <Loader2
                                        v-if="uploadingPhotos"
                                        class="h-3 w-3 animate-spin"
                                    />
                                    <Plus v-else class="h-3 w-3" />
                                    {{
                                        uploadingPhotos
                                            ? 'Uploading…'
                                            : 'Add photos'
                                    }}
                                    <input
                                        type="file"
                                        class="sr-only"
                                        accept="image/jpeg,image/png,image/webp"
                                        multiple
                                        :disabled="uploadingPhotos"
                                        @change="uploadPhotos"
                                    />
                                </label>
                            </div>
                            <div
                                v-if="modalDelivery?.photos?.length"
                                class="grid grid-cols-3 gap-2"
                            >
                                <div
                                    v-for="photo in modalDelivery.photos"
                                    :key="photo.public_id"
                                    class="group relative aspect-square overflow-hidden rounded-lg border bg-muted"
                                >
                                    <a
                                        :href="`${base}/photos/${photo.public_id}`"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        <img
                                            :src="`${base}/photos/${photo.public_id}`"
                                            :alt="
                                                photo.kind === 'cash'
                                                    ? 'Cash collected'
                                                    : photo.kind === 'pickup'
                                                      ? 'Pickup proof'
                                                      : 'Delivery proof'
                                            "
                                            class="h-full w-full object-cover"
                                            loading="lazy"
                                        />
                                    </a>
                                    <span
                                        v-if="
                                            photo.kind &&
                                            PHOTO_LABELS[photo.kind]
                                        "
                                        class="absolute bottom-1 left-1 rounded bg-emerald-600 px-1.5 py-0.5 text-[10px] font-semibold text-white"
                                        >{{ PHOTO_LABELS[photo.kind] }}</span
                                    >
                                    <button
                                        v-if="canChange('archive')"
                                        type="button"
                                        class="absolute top-1 right-1 flex h-6 w-6 items-center justify-center rounded-full bg-black/60 text-white opacity-0 transition-opacity group-hover:opacity-100 focus:opacity-100"
                                        title="Remove photo"
                                        :disabled="
                                            removingPhoto === photo.public_id
                                        "
                                        @click="removePhoto(photo)"
                                    >
                                        <Trash2 class="h-3 w-3" />
                                    </button>
                                </div>
                            </div>
                            <p v-else class="text-xs text-muted-foreground">
                                No proof photos yet. JPG, PNG or WebP, up to 5
                                MB each (max 8).
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
