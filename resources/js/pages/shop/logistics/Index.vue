<script setup lang="ts">
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import PhilippinePhoneInput from '@/components/PhilippinePhoneInput.vue';
import {
    DataTable,
    DataTableToolbar,
    RowActions,
    StatusPill,
    type DataTableHeader,
    type StatusTone,
} from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { usePaginatorTable } from '@/composables/useDataTable';
import {
    ArrowRight,
    Camera,
    CheckCircle2,
    Clock,
    Loader2,
    MapPin,
    Package,
    Pencil,
    Phone,
    Plus,
    Trash2,
    Truck,
    User,
    X,
    XCircle,
} from 'lucide-vue-next';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Rider {
    id: number;
    name: string;
    vehicle_type: string | null;
    available: boolean;
}

interface DeliveryPhoto {
    public_id: string;
    created_at: string;
    /** "cash" is the rider's photo of the cash collected at the door; anything else is delivery proof. */
    kind?: string;
}

interface Delivery {
    id: number;
    public_id: string;
    shop_order_id: number | null;
    type: 'pickup' | 'delivery';
    rider_id: number | null;
    courier: 'own_rider' | 'lalamove';
    courier_reference: string | null;
    courier_tracking_url: string | null;
    courier_driver_name: string | null;
    courier_driver_phone: string | null;
    courier_vehicle_plate: string | null;
    courier_cost: string | null;
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
    filters: { status?: string };
}>();

// ─── Flash toast ──────────────────────────────────────────────────────────────

const page = usePage();
const { can, isOwner } = usePermissions();
const base = isOwner.value ? '/shop/logistics' : '/staff/logistics';
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

// Next status transitions
const NEXT_STATUS: Record<string, string | null> = {
    pending: 'assigned',
    assigned: 'picked_up',
    picked_up: 'delivered',
    delivered: null,
    failed: null,
};
const NEXT_LABEL: Record<string, string> = {
    pending: 'Assign Rider',
    assigned: 'Mark Picked Up',
    picked_up: 'Mark Delivered',
};

// A pickup run collects the laundry from the customer and brings it to the shop,
// so the same statuses read differently from a delivery back to the customer.
const PICKUP_STATUS_LABEL: Record<string, string> = {
    picked_up: 'Collected',
    delivered: 'At shop',
};
const PICKUP_NEXT_LABEL: Record<string, string> = {
    pending: 'Assign Rider',
    assigned: 'Mark Collected',
    picked_up: 'Mark At Shop',
};

function statusLabel(d: Delivery, status = d.status): string {
    if (d.type === 'pickup' && PICKUP_STATUS_LABEL[status]) {
        return PICKUP_STATUS_LABEL[status];
    }
    return STATUS[status]?.label ?? status;
}

function nextLabel(d: Delivery): string {
    return (d.type === 'pickup' ? PICKUP_NEXT_LABEL : NEXT_LABEL)[d.status];
}

// ─── Courier ──────────────────────────────────────────────────────────────────

const COURIERS = [
    { value: 'own_rider', label: 'Own rider' },
    { value: 'lalamove', label: 'Lalamove' },
] as const;

function formatCost(value: string | number) {
    return `₱${Number(value).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

// ─── Filter ───────────────────────────────────────────────────────────────────

const activeFilter = ref(props.filters.status ?? 'all');

function setFilter(f: string) {
    activeFilter.value = f;
    router.get(base, f === 'all' ? {} : { status: f }, {
        preserveState: true,
        preserveScroll: true,
    });
}

// ─── Update Status Modal ──────────────────────────────────────────────────────

const statusModal = ref<{
    open: boolean;
    delivery: Delivery | null;
    form: any;
}>({
    open: false,
    delivery: null,
    form: emptyStatusForm(),
});
const updatingStatus = ref(false);

function emptyStatusForm() {
    return {
        status: '',
        courier: 'own_rider',
        rider_id: '',
        notes: '',
        courier_reference: '',
        courier_tracking_url: '',
        courier_driver_name: '',
        courier_driver_phone: '',
        courier_vehicle_plate: '',
        courier_cost: '',
    };
}

// The open delivery, kept fresh as uploads reload the page props.
const modalDelivery = computed(
    () =>
        props.deliveries.data.find(
            (d) => d.id === statusModal.value.delivery?.id,
        ) ?? statusModal.value.delivery,
);

function openStatusModal(delivery: Delivery, nextStatus?: string) {
    statusModal.value = {
        open: true,
        delivery,
        form: {
            status: nextStatus ?? delivery.status,
            courier: delivery.courier ?? 'own_rider',
            rider_id: delivery.rider_id?.toString() ?? '',
            notes: delivery.notes ?? '',
            courier_reference: delivery.courier_reference ?? '',
            courier_tracking_url: delivery.courier_tracking_url ?? '',
            courier_driver_name: delivery.courier_driver_name ?? '',
            courier_driver_phone: delivery.courier_driver_phone ?? '',
            courier_vehicle_plate: delivery.courier_vehicle_plate ?? '',
            courier_cost: delivery.courier_cost ?? '',
        },
    };
}

function submitStatus() {
    if (!statusModal.value.delivery) return;
    updatingStatus.value = true;
    const form = statusModal.value.form;
    const riderId = form.rider_id;
    router.patch(
        `${base}/${statusModal.value.delivery.public_id}/status`,
        {
            status: statusModal.value.form.status,
            courier: form.courier,
            rider_id:
                form.courier === 'own_rider' &&
                riderId &&
                riderId !== '__none__'
                    ? riderId
                    : null,
            notes: form.notes || null,
            courier_reference: form.courier_reference || null,
            courier_tracking_url: form.courier_tracking_url || null,
            courier_driver_name: form.courier_driver_name || null,
            courier_driver_phone: form.courier_driver_phone || null,
            courier_vehicle_plate: form.courier_vehicle_plate || null,
            courier_cost: form.courier_cost === '' ? null : form.courier_cost,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                statusModal.value.open = false;
            },
            onFinish: () => {
                updatingStatus.value = false;
            },
        },
    );
}

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

const deleting = ref<number | null>(null);

function deleteDelivery(id: number) {
    if (!confirm('Remove this delivery record?')) return;
    deleting.value = id;
    router.delete(`${base}/${id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = null;
        },
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
    { text: 'Customer', value: 'customer_name', width: 260 },
    { text: 'Order', value: 'order' },
    { text: 'Rider / courier', value: 'rider' },
    { text: 'Status', value: 'status' },
    { text: 'Date', value: 'created_at' },
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
    <Head title="Logistics" />

    <ShopLayout title="Logistics">
        <div class="space-y-6 px-6">
            <!-- ── Header ── -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Deliveries</h2>
                    <p class="text-sm text-muted-foreground">
                        Track and manage laundry deliveries. Drivers are
                        employees whose position is Driver.
                    </p>
                </div>
            </div>

            <!-- ── Stats ── -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                <Card
                    v-for="(cfg, key) in STATUS"
                    :key="key"
                    class="cursor-pointer transition-all"
                    :class="activeFilter === key ? 'ring-2 ring-primary' : ''"
                    @click="setFilter(key)"
                >
                    <CardContent class="flex items-center gap-2.5 pt-3 pb-3">
                        <span
                            class="h-2.5 w-2.5 shrink-0 rounded-full"
                            :class="cfg.dot"
                        />
                        <div>
                            <p class="text-xl leading-none font-bold">
                                {{ props.stats[key as keyof Stats] }}
                            </p>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                {{ cfg.label }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- ── Deliveries Table ── -->
            <DataTable
                v-model:options="options"
                :headers="headers"
                :items="props.deliveries.data"
                :total="props.deliveries.total"
                :loading="loading"
                noun="deliveries"
                :empty-icon="Truck"
                empty-message="No deliveries found."
                :resettable="activeFilter !== 'all'"
                :min-width="960"
                hide-rows-per-page
                @reset="setFilter('all')"
            >
                <template #toolbar>
                    <DataTableToolbar
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
                        <template v-if="d.courier === 'lalamove'">
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-orange-100 px-2 py-0.5 font-medium text-orange-700"
                            >
                                <Truck class="h-3 w-3" />
                                Lalamove
                            </span>
                            <p
                                v-if="d.courier_driver_name"
                                class="mt-1 font-medium"
                            >
                                {{ d.courier_driver_name }}
                            </p>
                            <p
                                v-if="d.courier_reference"
                                class="font-mono text-muted-foreground"
                            >
                                {{ d.courier_reference }}
                            </p>
                            <p
                                v-if="d.courier_cost"
                                class="text-muted-foreground"
                            >
                                Cost {{ formatCost(d.courier_cost) }}
                            </p>
                        </template>
                        <template v-else>
                            <span v-if="d.rider" class="font-medium">{{
                                d.rider.name
                            }}</span>
                            <span v-else class="text-muted-foreground italic"
                                >Unassigned</span
                            >
                        </template>
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
                                label: 'Edit delivery',
                                icon: Pencil,
                                show: can('Logistics', 'update'),
                                onSelect: () => openStatusModal(d),
                            },
                            {
                                label: 'Remove delivery',
                                icon: deleting === d.id ? Loader2 : Trash2,
                                destructive: true,
                                disabled: deleting === d.id,
                                show: can('Logistics', 'archive'),
                                onSelect: () => deleteDelivery(d.id),
                            },
                        ]"
                    >
                        <!-- Quick advance stays visible as the row's primary step -->
                        <button
                            v-if="
                                NEXT_STATUS[d.status] &&
                                can('Logistics', 'update')
                            "
                            type="button"
                            class="flex h-9 items-center gap-1 rounded-lg bg-primary px-2.5 text-xs whitespace-nowrap text-primary-foreground transition-colors hover:bg-primary/90"
                            @click="openStatusModal(d, NEXT_STATUS[d.status]!)"
                        >
                            <ArrowRight class="h-3 w-3" />
                            {{ nextLabel(d) }}
                        </button>
                    </RowActions>
                </template>
            </DataTable>
        </div>
    </ShopLayout>

    <!-- ── Update Status Modal ───────────────────────────────────────────────── -->
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="statusModal.open"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm"
                @click.self="statusModal.open = false"
            >
                <div
                    class="flex max-h-[90vh] w-full max-w-md flex-col overflow-hidden rounded-2xl border bg-background shadow-xl"
                >
                    <div
                        class="flex items-center justify-between border-b px-5 py-4"
                    >
                        <div>
                            <p class="text-sm font-semibold">Update Delivery</p>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                {{ statusModal.delivery?.customer_name }}
                            </p>
                        </div>
                        <button
                            @click="statusModal.open = false"
                            class="flex h-7 w-7 items-center justify-center rounded-lg hover:bg-muted"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="space-y-3 overflow-y-auto px-5 py-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium">Status</label>
                            <Select v-model="statusModal.form.status">
                                <SelectTrigger class="h-9 text-sm">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="s in [
                                            'pending',
                                            'assigned',
                                            'picked_up',
                                            'delivered',
                                            'failed',
                                        ]"
                                        :key="s"
                                        :value="s"
                                    >
                                        {{ STATUS[s]?.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-medium">Courier</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    v-for="c in COURIERS"
                                    :key="c.value"
                                    type="button"
                                    class="rounded-lg border px-3 py-2 text-xs font-medium transition-colors"
                                    :class="
                                        statusModal.form.courier === c.value
                                            ? 'border-primary bg-primary/5 text-primary'
                                            : 'text-muted-foreground hover:bg-muted'
                                    "
                                    @click="statusModal.form.courier = c.value"
                                >
                                    {{ c.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Lalamove booking details -->
                        <div
                            v-if="statusModal.form.courier === 'lalamove'"
                            class="space-y-3 rounded-xl border border-orange-200 bg-orange-50/50 p-3"
                        >
                            <p class="text-xs text-muted-foreground">
                                Book the run in the Lalamove app, then record it
                                here. The customer sees the driver and tracking
                                link, never the cost.
                            </p>
                            <div class="space-y-1.5">
                                <label class="text-xs font-medium"
                                    >Booking / order number</label
                                >
                                <Input
                                    v-model="statusModal.form.courier_reference"
                                    placeholder="Lalamove order number"
                                    class="h-9 text-sm"
                                />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-medium"
                                    >Tracking link</label
                                >
                                <Input
                                    v-model="
                                        statusModal.form.courier_tracking_url
                                    "
                                    placeholder="https://…"
                                    class="h-9 text-sm"
                                />
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-medium"
                                        >Driver name</label
                                    >
                                    <Input
                                        v-model="
                                            statusModal.form.courier_driver_name
                                        "
                                        class="h-9 text-sm"
                                    />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-medium"
                                        >Plate number</label
                                    >
                                    <Input
                                        v-model="
                                            statusModal.form
                                                .courier_vehicle_plate
                                        "
                                        class="h-9 text-sm"
                                    />
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-medium"
                                    >Driver phone</label
                                >
                                <PhilippinePhoneInput
                                    v-model="
                                        statusModal.form.courier_driver_phone
                                    "
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
                                />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-medium"
                                    >Lalamove cost (₱)</label
                                >
                                <Input
                                    v-model="statusModal.form.courier_cost"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="h-9 text-sm"
                                />
                            </div>
                        </div>

                        <div v-else class="space-y-1.5">
                            <label class="text-xs font-medium">Rider</label>
                            <Select v-model="statusModal.form.rider_id">
                                <SelectTrigger class="h-9 text-sm">
                                    <SelectValue placeholder="Unassigned" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="__none__"
                                        >— Unassigned —</SelectItem
                                    >
                                    <SelectItem
                                        v-for="r in props.riders"
                                        :key="r.id"
                                        :value="String(r.id)"
                                    >
                                        {{ r.name }}
                                        <span
                                            v-if="
                                                !r.available &&
                                                r.id !==
                                                    statusModal.delivery
                                                        ?.rider_id
                                            "
                                            class="text-amber-600"
                                        >
                                            · On delivery</span
                                        >
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-medium">Notes</label>
                            <Input
                                v-model="statusModal.form.notes"
                                placeholder="Optional…"
                                class="h-9 text-sm"
                            />
                        </div>

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
                                    v-if="can('Logistics', 'update')"
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
                                                    : 'Delivery proof'
                                            "
                                            class="h-full w-full object-cover"
                                            loading="lazy"
                                        />
                                    </a>
                                    <span
                                        v-if="photo.kind === 'cash'"
                                        class="absolute bottom-1 left-1 rounded bg-emerald-600 px-1.5 py-0.5 text-[10px] font-semibold text-white"
                                        >Cash</span
                                    >
                                    <button
                                        v-if="can('Logistics', 'archive')"
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

                    <div
                        class="flex justify-end gap-2 border-t bg-muted/20 px-5 py-4"
                    >
                        <Button
                            variant="outline"
                            size="sm"
                            @click="statusModal.open = false"
                            >Cancel</Button
                        >
                        <Button
                            size="sm"
                            :disabled="updatingStatus"
                            @click="submitStatus"
                        >
                            <Loader2
                                v-if="updatingStatus"
                                class="mr-1.5 h-3.5 w-3.5 animate-spin"
                            />
                            {{ updatingStatus ? 'Saving…' : 'Save' }}
                        </Button>
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
