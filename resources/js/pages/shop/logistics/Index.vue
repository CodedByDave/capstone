<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import QRCode from 'qrcode';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import PhilippinePhoneInput from '@/components/PhilippinePhoneInput.vue';
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
import {
    ArrowRight,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    Clock,
    Loader2,
    MapPin,
    Package,
    Pencil,
    Phone,
    Plus,
    QrCode,
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
}

interface Delivery {
    id: number;
    shop_order_id: number | null;
    rider_id: number | null;
    customer_name: string;
    customer_phone: string | null;
    delivery_address: string | null;
    status: string;
    notes: string | null;
    driver_token: string | null;
    assigned_at: string | null;
    picked_up_at: string | null;
    delivered_at: string | null;
    created_at: string;
    rider: Rider | null;
    order: { id: number; order_number: string } | null;
}

interface PendingOrder {
    id: number;
    order_number: string;
    customer_name: string;
    customer_phone: string | null;
    customer_email: string | null;
    delivery_address: string | null;
    pickup_type: string;
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
    };
    riders: Rider[];
    pending_orders: PendingOrder[];
    stats: Stats;
    filters: { status?: string };
    rider_base_url: string;
}>();

// ─── Flash toast ──────────────────────────────────────────────────────────────

const page = usePage();
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

// ─── Filter ───────────────────────────────────────────────────────────────────

const activeFilter = ref(props.filters.status ?? 'all');

function setFilter(f: string) {
    activeFilter.value = f;
    router.get('/shop/logistics', f === 'all' ? {} : { status: f }, {
        preserveState: true,
        preserveScroll: true,
    });
}

// ─── Create Delivery Modal ────────────────────────────────────────────────────

const showCreate = ref(false);
const creating = ref(false);

const createForm = ref({
    shop_order_id: '',
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    delivery_address: '',
    rider_id: '',
    notes: '',
});

function prefillFromOrder(orderId: string) {
    if (orderId === '__none__') {
        createForm.value.customer_name = '';
        createForm.value.customer_phone = '';
        createForm.value.customer_email = '';
        createForm.value.delivery_address = '';
        return;
    }
    const order = props.pending_orders.find((o) => o.id === Number(orderId));
    if (!order) return;
    createForm.value.customer_name = order.customer_name;
    createForm.value.customer_phone = order.customer_phone ?? '';
    createForm.value.delivery_address = order.delivery_address ?? '';
    // Auto-fill email only when the order type is pickup
    createForm.value.customer_email =
        order.pickup_type === 'pickup' ? (order.customer_email ?? '') : '';
}

function openCreate() {
    createForm.value = {
        shop_order_id: '',
        customer_name: '',
        customer_phone: '',
        customer_email: '',
        delivery_address: '',
        rider_id: '',
        notes: '',
    };
    showCreate.value = true;
}

function submitCreate() {
    creating.value = true;
    const orderId = createForm.value.shop_order_id;
    const riderId = createForm.value.rider_id;
    router.post(
        '/shop/logistics',
        {
            shop_order_id: orderId && orderId !== '__none__' ? orderId : null,
            customer_name: createForm.value.customer_name,
            customer_phone: createForm.value.customer_phone || null,
            customer_email: createForm.value.customer_email || null,
            delivery_address: createForm.value.delivery_address || null,
            rider_id: riderId && riderId !== '__none__' ? riderId : null,
            notes: createForm.value.notes || null,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showCreate.value = false;
            },
            onFinish: () => {
                creating.value = false;
            },
        },
    );
}

// ─── Update Status Modal ──────────────────────────────────────────────────────

const statusModal = ref<{
    open: boolean;
    delivery: Delivery | null;
    form: any;
}>({
    open: false,
    delivery: null,
    form: { status: '', rider_id: '', notes: '' },
});
const updatingStatus = ref(false);

function openStatusModal(delivery: Delivery, nextStatus?: string) {
    statusModal.value = {
        open: true,
        delivery,
        form: {
            status: nextStatus ?? delivery.status,
            rider_id: delivery.rider_id?.toString() ?? '',
            notes: delivery.notes ?? '',
        },
    };
}

function submitStatus() {
    if (!statusModal.value.delivery) return;
    updatingStatus.value = true;
    const riderId = statusModal.value.form.rider_id;
    router.patch(
        `/shop/logistics/${statusModal.value.delivery.id}/status`,
        {
            status: statusModal.value.form.status,
            rider_id: riderId && riderId !== '__none__' ? riderId : null,
            notes: statusModal.value.form.notes || null,
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

// ─── Delete ───────────────────────────────────────────────────────────────────

const deleting = ref<number | null>(null);

function deleteDelivery(id: number) {
    if (!confirm('Remove this delivery record?')) return;
    deleting.value = id;
    router.delete(`/shop/logistics/${id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = null;
        },
    });
}

// ─── QR Code Modal ───────────────────────────────────────────────────────────

const qrModal = ref<{
    open: boolean;
    delivery: Delivery | null;
    dataUrl: string;
    riderUrl: string;
}>({
    open: false,
    delivery: null,
    dataUrl: '',
    riderUrl: '',
});

const isLocalhost = computed(() =>
    /localhost|127\.0\.0\.1/.test(props.rider_base_url),
);

async function openQr(delivery: Delivery) {
    if (!delivery.driver_token) return;
    const riderUrl = `${props.rider_base_url}/driver/${delivery.driver_token}`;
    const dataUrl = await QRCode.toDataURL(riderUrl, {
        width: 280,
        margin: 2,
        color: { dark: '#111827', light: '#ffffff' },
    });
    qrModal.value = { open: true, delivery, dataUrl, riderUrl };
}

// ─── Pagination ───────────────────────────────────────────────────────────────

function goPage(url: string | null) {
    if (!url) return;
    router.get(url, {}, { preserveState: true, preserveScroll: true });
}

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
                        Track and manage laundry deliveries.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="router.visit('/shop/logistics/riders')"
                    >
                        <User class="mr-1.5 h-4 w-4" />
                        Manage Riders
                    </Button>
                    <Button size="sm" @click="openCreate">
                        <Plus class="mr-1.5 h-4 w-4" />
                        New Delivery
                    </Button>
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

            <!-- ── Filter pills ── -->
            <div class="flex flex-wrap items-center gap-2">
                <button
                    v-for="f in [
                        'all',
                        'pending',
                        'assigned',
                        'picked_up',
                        'delivered',
                        'failed',
                    ]"
                    :key="f"
                    class="rounded-full border px-3 py-1 text-xs transition-colors"
                    :class="
                        activeFilter === f
                            ? 'border-primary bg-primary text-primary-foreground'
                            : 'border-muted-foreground/20 text-muted-foreground hover:bg-muted'
                    "
                    @click="setFilter(f)"
                >
                    {{ f === 'all' ? 'All' : (STATUS[f]?.label ?? f) }}
                </button>
            </div>

            <!-- ── Deliveries Table ── -->
            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b bg-muted/40 text-xs text-muted-foreground"
                        >
                            <th class="px-4 py-3 text-left font-medium">
                                Customer
                            </th>
                            <th class="px-4 py-3 text-left font-medium">
                                Order
                            </th>
                            <th class="px-4 py-3 text-left font-medium">
                                Rider
                            </th>
                            <th class="px-4 py-3 text-left font-medium">
                                Status
                            </th>
                            <th class="px-4 py-3 text-left font-medium">
                                Date
                            </th>
                            <th class="w-32 px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="d in props.deliveries.data"
                            :key="d.id"
                            class="border-b transition-colors last:border-0 hover:bg-muted/20"
                        >
                            <!-- Customer -->
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium">
                                    {{ d.customer_name }}
                                </p>
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
                            </td>

                            <!-- Order -->
                            <td
                                class="px-4 py-3 font-mono text-xs text-muted-foreground"
                            >
                                {{ d.order?.order_number ?? '—' }}
                            </td>

                            <!-- Rider -->
                            <td class="px-4 py-3 text-xs">
                                <span v-if="d.rider" class="font-medium">{{
                                    d.rider.name
                                }}</span>
                                <span
                                    v-else
                                    class="text-muted-foreground italic"
                                    >Unassigned</span
                                >
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="STATUS[d.status]?.badge"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full"
                                        :class="STATUS[d.status]?.dot"
                                    />
                                    {{ STATUS[d.status]?.label }}
                                </span>
                                <div
                                    class="mt-1 space-y-0.5 text-xs text-muted-foreground"
                                >
                                    <p v-if="d.picked_up_at">
                                        Picked up {{ fmtTime(d.picked_up_at) }}
                                    </p>
                                    <p v-if="d.delivered_at">
                                        Delivered {{ fmtTime(d.delivered_at) }}
                                    </p>
                                </div>
                            </td>

                            <!-- Date -->
                            <td class="px-4 py-3 text-xs text-muted-foreground">
                                {{ fmtDate(d.created_at) }}
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-3">
                                <div
                                    class="flex items-center justify-end gap-1.5"
                                >
                                    <!-- Quick advance -->
                                    <button
                                        v-if="NEXT_STATUS[d.status]"
                                        class="flex items-center gap-1 rounded-lg bg-primary px-2.5 py-1 text-xs text-primary-foreground transition-colors hover:bg-primary/90"
                                        @click="
                                            openStatusModal(
                                                d,
                                                NEXT_STATUS[d.status]!,
                                            )
                                        "
                                    >
                                        <ArrowRight class="h-3 w-3" />
                                        {{ NEXT_LABEL[d.status] }}
                                    </button>
                                    <!-- QR code for rider -->
                                    <button
                                        class="flex h-7 w-7 items-center justify-center rounded-lg border transition-colors hover:bg-muted"
                                        title="Show rider QR code"
                                        @click="openQr(d)"
                                    >
                                        <QrCode
                                            class="h-3.5 w-3.5 text-muted-foreground"
                                        />
                                    </button>
                                    <!-- Edit -->
                                    <button
                                        class="flex h-7 w-7 items-center justify-center rounded-lg border transition-colors hover:bg-muted"
                                        @click="openStatusModal(d)"
                                    >
                                        <Pencil
                                            class="h-3.5 w-3.5 text-muted-foreground"
                                        />
                                    </button>
                                    <!-- Delete -->
                                    <button
                                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-red-200 transition-colors hover:bg-red-50"
                                        :disabled="deleting === d.id"
                                        @click="deleteDelivery(d.id)"
                                    >
                                        <Loader2
                                            v-if="deleting === d.id"
                                            class="h-3.5 w-3.5 animate-spin text-muted-foreground"
                                        />
                                        <Trash2
                                            v-else
                                            class="h-3.5 w-3.5 text-red-500"
                                        />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="props.deliveries.data.length === 0">
                            <td
                                colspan="6"
                                class="px-4 py-12 text-center text-sm text-muted-foreground"
                            >
                                <Truck
                                    class="mx-auto mb-2 h-8 w-8 opacity-30"
                                />
                                <p>No deliveries found.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ── Pagination ── -->
            <div
                v-if="props.deliveries.last_page > 1"
                class="flex items-center justify-center gap-2"
            >
                <Button
                    size="sm"
                    variant="outline"
                    :disabled="props.deliveries.current_page === 1"
                    @click="goPage(props.deliveries.links[0]?.url)"
                >
                    <ChevronLeft class="h-4 w-4" />
                </Button>
                <span class="text-sm text-muted-foreground">
                    Page {{ props.deliveries.current_page }} of
                    {{ props.deliveries.last_page }}
                </span>
                <Button
                    size="sm"
                    variant="outline"
                    :disabled="
                        props.deliveries.current_page ===
                        props.deliveries.last_page
                    "
                    @click="
                        goPage(
                            props.deliveries.links[
                                props.deliveries.links.length - 1
                            ]?.url,
                        )
                    "
                >
                    <ChevronRight class="h-4 w-4" />
                </Button>
            </div>
        </div>
    </ShopLayout>

    <!-- ── Create Delivery Modal ─────────────────────────────────────────────── -->
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="showCreate"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm"
                @click.self="showCreate = false"
            >
                <div
                    class="w-full max-w-md overflow-hidden rounded-2xl border bg-background shadow-xl"
                >
                    <div
                        class="flex items-center justify-between border-b px-5 py-4"
                    >
                        <p class="text-sm font-semibold">New Delivery</p>
                        <button
                            @click="showCreate = false"
                            class="flex h-7 w-7 items-center justify-center rounded-lg hover:bg-muted"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <div
                        class="max-h-[70vh] space-y-3 overflow-y-auto px-5 py-4"
                    >
                        <!-- Link to order -->
                        <div
                            class="space-y-1.5"
                            v-if="props.pending_orders.length > 0"
                        >
                            <label class="text-xs font-medium"
                                >Link to Order
                                <span class="text-muted-foreground"
                                    >(optional)</span
                                ></label
                            >
                            <Select
                                v-model="createForm.shop_order_id"
                                @update:model-value="prefillFromOrder"
                            >
                                <SelectTrigger class="h-9 text-sm">
                                    <SelectValue
                                        placeholder="Select completed order…"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="__none__"
                                        >— None —</SelectItem
                                    >
                                    <SelectItem
                                        v-for="o in props.pending_orders"
                                        :key="o.id"
                                        :value="String(o.id)"
                                    >
                                        {{ o.order_number }} —
                                        {{ o.customer_name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-medium"
                                >Customer Name
                                <span class="text-red-500">*</span></label
                            >
                            <Input
                                v-model="createForm.customer_name"
                                placeholder="Juan dela Cruz"
                                class="h-9 text-sm"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium">Phone</label>
                            <PhilippinePhoneInput
                                v-model="createForm.customer_phone"
                                class="h-9 text-sm"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium">
                                Email
                                <span
                                    v-if="createForm.customer_email"
                                    class="ml-1 font-normal text-blue-500"
                                    >(auto-filled)</span
                                >
                            </label>
                            <Input
                                v-model="createForm.customer_email"
                                type="email"
                                placeholder="customer@email.com"
                                class="h-9 text-sm"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium"
                                >Delivery Address</label
                            >
                            <Input
                                v-model="createForm.delivery_address"
                                placeholder="Street, Barangay, City"
                                class="h-9 text-sm"
                            />
                        </div>

                        <!-- Assign rider -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium"
                                >Assign Rider
                                <span class="text-muted-foreground"
                                    >(optional)</span
                                ></label
                            >
                            <Select v-model="createForm.rider_id">
                                <SelectTrigger class="h-9 text-sm">
                                    <SelectValue placeholder="Assign later…" />
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
                                        {{ r.name
                                        }}<span
                                            v-if="r.vehicle_type"
                                            class="text-muted-foreground"
                                        >
                                            · {{ r.vehicle_type }}</span
                                        >
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-medium">Notes</label>
                            <Input
                                v-model="createForm.notes"
                                placeholder="Optional instructions…"
                                class="h-9 text-sm"
                            />
                        </div>
                    </div>

                    <div
                        class="flex justify-end gap-2 border-t bg-muted/20 px-5 py-4"
                    >
                        <Button
                            variant="outline"
                            size="sm"
                            @click="showCreate = false"
                            >Cancel</Button
                        >
                        <Button
                            size="sm"
                            :disabled="creating || !createForm.customer_name"
                            @click="submitCreate"
                        >
                            <Loader2
                                v-if="creating"
                                class="mr-1.5 h-3.5 w-3.5 animate-spin"
                            />
                            {{ creating ? 'Creating…' : 'Create Delivery' }}
                        </Button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>

    <!-- ── Update Status Modal ───────────────────────────────────────────────── -->
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="statusModal.open"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm"
                @click.self="statusModal.open = false"
            >
                <div
                    class="w-full max-w-sm overflow-hidden rounded-2xl border bg-background shadow-xl"
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

                    <div class="space-y-3 px-5 py-4">
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

    <!-- ── QR Code Modal ────────────────────────────────────────────────────── -->
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="qrModal.open"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm"
                @click.self="qrModal.open = false"
            >
                <div
                    class="w-full max-w-xs overflow-hidden rounded-2xl border bg-background shadow-xl"
                >
                    <div
                        class="flex items-center justify-between border-b px-5 py-4"
                    >
                        <div>
                            <p class="text-sm font-semibold">Rider QR Code</p>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                {{ qrModal.delivery?.customer_name }}
                                <span v-if="qrModal.delivery?.order">
                                    ·
                                    {{
                                        qrModal.delivery.order.order_number
                                    }}</span
                                >
                            </p>
                        </div>
                        <button
                            @click="qrModal.open = false"
                            class="flex h-7 w-7 items-center justify-center rounded-lg hover:bg-muted"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="flex flex-col items-center gap-4 px-5 py-6">
                        <!-- Localhost warning -->
                        <div
                            v-if="isLocalhost"
                            class="flex w-full items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs text-amber-700"
                        >
                            <span class="mt-0.5 shrink-0 font-bold">⚠</span>
                            <span>
                                Your
                                <code
                                    class="rounded bg-amber-100 px-1 font-mono"
                                    >RIDER_BASE_URL</code
                                >
                                is set to <strong>localhost</strong> — riders
                                can't reach it. Set it to your LAN IP in
                                <code
                                    class="rounded bg-amber-100 px-1 font-mono"
                                    >.env</code
                                >, e.g.
                                <code
                                    class="rounded bg-amber-100 px-1 font-mono"
                                    >RIDER_BASE_URL=http://192.168.x.x:8000</code
                                >
                            </span>
                        </div>

                        <!-- QR Image -->
                        <div class="rounded-xl border bg-white p-2 shadow-sm">
                            <img
                                :src="qrModal.dataUrl"
                                alt="Rider QR Code"
                                class="h-56 w-56"
                            />
                        </div>

                        <!-- URL chip -->
                        <p
                            class="px-2 text-center font-mono text-[11px] break-all text-muted-foreground"
                        >
                            {{ qrModal.riderUrl }}
                        </p>

                        <!-- Instruction -->
                        <div class="space-y-1 text-center">
                            <p class="text-xs font-medium text-gray-700">
                                Share this QR with the rider
                            </p>
                            <p class="text-xs text-muted-foreground">
                                The rider scans it to open their delivery page
                                and update the status directly.
                            </p>
                        </div>

                        <!-- Rider info pill -->
                        <div
                            v-if="qrModal.delivery?.rider"
                            class="flex items-center gap-2 rounded-full bg-muted px-3 py-1.5 text-xs"
                        >
                            <User class="h-3.5 w-3.5 text-muted-foreground" />
                            <span class="font-medium">{{
                                qrModal.delivery.rider.name
                            }}</span>
                            <span
                                v-if="qrModal.delivery.rider.vehicle_type"
                                class="text-muted-foreground"
                                >·
                                {{ qrModal.delivery.rider.vehicle_type }}</span
                            >
                        </div>
                        <p v-else class="text-xs font-medium text-amber-600">
                            No rider assigned yet
                        </p>
                    </div>

                    <div class="px-5 pb-5">
                        <Button
                            class="w-full"
                            size="sm"
                            variant="outline"
                            @click="qrModal.open = false"
                            >Close</Button
                        >
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
