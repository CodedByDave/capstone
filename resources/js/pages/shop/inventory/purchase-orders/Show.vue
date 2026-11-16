<script setup lang="ts">
import {
    DataTable,
    StatusPill,
    type DataTableHeader,
} from '@/components/data-table';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { usePermissions } from '@/composables/usePermissions';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import {
    PO_STATUS,
    peso,
    qty,
    shortDate,
    type PurchaseOrderStatus,
} from '@/lib/purchaseOrders';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Ban,
    Camera,
    CheckCircle2,
    Circle,
    ExternalLink,
    MessageSquareText,
    PackageCheck,
    PackagePlus,
    Send,
    Truck,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface OrderItem {
    id: number;
    name: string;
    unit: string | null;
    quantity: string;
    unit_price: string;
    line_total: string;
    stock_per_unit: number;
    received_quantity: string | null;
    inventory: { id: number; name: string; unit: string } | null;
    /** Position on the order, used in links so no database ids are exposed. */
    line: number;
    /** Received, but not in the product list yet. */
    can_list: boolean;
}

interface Order {
    public_id: string;
    po_number: string;
    status: PurchaseOrderStatus;
    total: string;
    notes: string | null;
    expected_date: string | null;
    supplier_note: string | null;
    created_at: string;
    sent_at: string | null;
    confirmed_at: string | null;
    declined_at: string | null;
    received_at: string | null;
    cancelled_at: string | null;
    supplier_delivered_at: string | null;
    has_supplier_proof: boolean;
    has_received_proof: boolean;
    supplier: {
        public_id: string;
        name: string;
        contact_person: string | null;
        phone: string | null;
        email: string | null;
        user_id: number | null;
    } | null;
    creator: { name: string } | null;
    receiver: { name: string } | null;
    items: OrderItem[];
}

// Not named `order`: the shop layout reads the shop's subscription from a page prop of that name,
// so reusing it would empty the sidebar.
const props = defineProps<{ purchaseOrder: Order; base: string }>();
const order = computed(() => props.purchaseOrder);

const { isOwner, can } = usePermissions();
const canSend = computed(
    () => isOwner.value || can('Inventory Management', 'create'),
);
const canUpdate = computed(
    () => isOwner.value || can('Inventory Management', 'update'),
);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Inventory',
        href: isOwner.value ? '/shop/inventory' : '/staff/inventory',
    },
    { title: 'Purchase Orders', href: props.base },
    {
        title: props.purchaseOrder.po_number,
        href: `${props.base}/${props.purchaseOrder.public_id}`,
    },
];

const status = computed(() => PO_STATUS[props.purchaseOrder.status]);
const url = (action: string) =>
    `${props.base}/${props.purchaseOrder.public_id}/${action}`;

// The supplier hands over with a photo first; only then can the shop receive.
const canReceive = computed(
    () =>
        canUpdate.value &&
        props.purchaseOrder.status === 'confirmed' &&
        props.purchaseOrder.has_supplier_proof,
);
const awaitingSupplierPhoto = computed(
    () =>
        props.purchaseOrder.status === 'confirmed' &&
        !props.purchaseOrder.has_supplier_proof,
);
const canAddToList = computed(
    () => isOwner.value || can('Inventory Management', 'create'),
);
const inventoryBase = computed(() =>
    isOwner.value ? '/shop/inventory' : '/staff/inventory',
);
const addToList = (item: OrderItem) =>
    router.visit(
        `${inventoryBase.value}/create?purchase_order=${props.purchaseOrder.public_id}&line=${item.line}`,
    );
const canCancel = computed(
    () =>
        canUpdate.value &&
        ['draft', 'sent', 'confirmed'].includes(props.purchaseOrder.status),
);

// ─── Timeline ────────────────────────────────────────────────────────────────

const when = (value: string | null) =>
    value
        ? new Date(value).toLocaleString('en-PH', {
              month: 'short',
              day: 'numeric',
              hour: 'numeric',
              minute: '2-digit',
          })
        : null;

const timeline = computed(() => {
    const o = props.purchaseOrder;
    const steps = [
        { label: 'Created', at: when(o.created_at), by: o.creator?.name },
        { label: 'Sent to supplier', at: when(o.sent_at) },
    ];
    if (o.declined_at) {
        steps.push({ label: 'Declined by supplier', at: when(o.declined_at) });
    } else {
        steps.push({
            label: 'Confirmed by supplier',
            at: when(o.confirmed_at),
        });
        steps.push({
            label: 'Delivered by supplier',
            at: when(o.supplier_delivered_at),
        });
        steps.push({
            label: 'Received',
            at: when(o.received_at),
            by: o.receiver?.name,
        });
    }
    if (o.cancelled_at) {
        steps.push({ label: 'Cancelled', at: when(o.cancelled_at) });
    }
    return steps;
});

// ─── Items ───────────────────────────────────────────────────────────────────

const headers: DataTableHeader[] = [
    { text: 'Supply', value: 'name' },
    { text: 'Quantity', value: 'quantity' },
    { text: 'Unit price', value: 'unit_price' },
    { text: 'Amount', value: 'line_total' },
    { text: 'Goes to stock', value: 'inventory' },
];

// ─── Actions ─────────────────────────────────────────────────────────────────

const busy = ref(false);
const post = (action: string) => {
    busy.value = true;
    router.post(
        url(action),
        {},
        {
            preserveScroll: true,
            onFinish: () => (busy.value = false),
        },
    );
};

const cancelOpen = ref(false);
function confirmCancel() {
    cancelOpen.value = false;
    post('cancel');
}

const receiveOpen = ref(false);
const receiveForm = useForm({
    received: Object.fromEntries(
        props.purchaseOrder.items.map((item) => [
            item.id,
            Number(item.quantity),
        ]),
    ) as Record<number, number>,
    proof: null as File | null,
});
function onProof(event: Event) {
    receiveForm.proof = (event.target as HTMLInputElement).files?.[0] ?? null;
    receiveForm.clearErrors('proof');
}
const receivedTotal = computed(() =>
    props.purchaseOrder.items.reduce(
        (sum, item) =>
            sum +
            (Number(receiveForm.received[item.id]) || 0) *
                Number(item.unit_price),
        0,
    ),
);
function submitReceive() {
    receiveForm.post(url('receive'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => (receiveOpen.value = false),
    });
}
const receiveError = (id: number) =>
    (receiveForm.errors as Record<string, string | undefined>)[
        `received.${id}`
    ];
</script>

<template>
    <Head :title="order.po_number" />
    <ShopLayout :breadcrumbs="breadcrumbs" :title="order.po_number">
        <div class="mx-auto max-w-6xl space-y-6 px-6 pb-10">
            <!-- Header -->
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl font-bold">
                            {{ order.po_number }}
                        </h1>
                        <StatusPill :tone="status.tone">{{
                            status.label
                        }}</StatusPill>
                    </div>
                    <p class="mt-1 text-sm text-muted-foreground">
                        To {{ order.supplier?.name ?? 'supplier' }} ·
                        {{ peso(order.total) }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button variant="back" @click="router.visit(base)">
                        <ArrowLeft class="h-4 w-4" />
                        Back to purchase orders
                    </Button>
                    <Button
                        v-if="canCancel"
                        variant="warning-outline"
                        :disabled="busy"
                        @click="cancelOpen = true"
                    >
                        <Ban class="h-4 w-4" /> Cancel order
                    </Button>
                    <Button
                        v-if="order.status === 'draft' && canSend"
                        :disabled="busy"
                        @click="post('send')"
                    >
                        <Send class="h-4 w-4" /> Send to supplier
                    </Button>
                    <Button
                        v-if="canReceive"
                        variant="success"
                        :disabled="busy"
                        @click="receiveOpen = true"
                    >
                        <PackageCheck class="h-4 w-4" /> Mark received
                    </Button>
                </div>
            </div>

            <!-- Supplier's answer -->
            <div
                v-if="order.status === 'confirmed'"
                class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900 dark:border-emerald-800 dark:bg-transparent dark:text-emerald-300"
            >
                <Truck class="mt-0.5 h-5 w-5 shrink-0" aria-hidden="true" />
                <div>
                    <p class="font-semibold">
                        Confirmed — delivery on
                        {{ shortDate(order.expected_date) }}
                    </p>
                    <p v-if="order.supplier_note">
                        “{{ order.supplier_note }}”
                    </p>
                    <p v-if="awaitingSupplierPhoto" class="mt-1 text-xs">
                        Waiting for the supplier to mark it delivered with a
                        photo. You can mark it received after that.
                    </p>
                    <p v-else class="mt-1 text-xs">
                        The supplier delivered it
                        {{ shortDate(order.supplier_delivered_at) }}. Check
                        their photo below, then press Mark received and add your
                        own photo. The stock is added then.
                    </p>
                </div>
            </div>
            <div
                v-else-if="order.status === 'declined'"
                class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-900 dark:border-red-800 dark:bg-transparent dark:text-red-300"
            >
                <Ban class="mt-0.5 h-5 w-5 shrink-0" aria-hidden="true" />
                <div>
                    <p class="font-semibold">
                        The supplier declined this order
                    </p>
                    <p v-if="order.supplier_note">
                        “{{ order.supplier_note }}”
                    </p>
                </div>
            </div>
            <div
                v-else-if="order.status === 'sent' && !order.supplier?.user_id"
                class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-800 dark:bg-transparent dark:text-amber-300"
            >
                <MessageSquareText
                    class="mt-0.5 h-5 w-5 shrink-0"
                    aria-hidden="true"
                />
                <p>
                    {{ order.supplier?.name }} has no supplier portal login, so
                    they cannot see this order online. An order can only be
                    received after the supplier confirms it and marks it
                    delivered with a photo in their portal, so send them a login
                    invite from the supplier page.
                </p>
            </div>

            <div
                class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_300px]"
            >
                <div class="min-w-0 space-y-6">
                    <DataTable
                        :headers="headers"
                        :items="order.items"
                        item-key="id"
                        :selectable="false"
                        hide-footer
                        noun="supplies"
                        :min-width="640"
                    >
                        <template #item-name="item">
                            <span class="font-medium">{{ item.name }}</span>
                        </template>
                        <template #item-quantity="item">
                            <span class="tabular-nums">
                                {{ qty(item.quantity) }} {{ item.unit ?? '' }}
                            </span>
                            <span
                                v-if="
                                    item.received_quantity !== null &&
                                    Number(item.received_quantity) !==
                                        Number(item.quantity)
                                "
                                class="block text-xs text-amber-700 dark:text-amber-400"
                            >
                                {{ qty(item.received_quantity) }} received
                            </span>
                        </template>
                        <template #item-unit_price="item">
                            <span class="tabular-nums">{{
                                peso(item.unit_price)
                            }}</span>
                        </template>
                        <template #item-line_total="item">
                            <span class="font-medium tabular-nums">{{
                                peso(item.line_total)
                            }}</span>
                        </template>
                        <template #item-inventory="item">
                            <span
                                v-if="item.inventory"
                                class="text-sm text-muted-foreground"
                            >
                                {{ item.inventory.name }} (+{{
                                    qty(
                                        Number(item.quantity) *
                                            item.stock_per_unit,
                                    )
                                }}
                                {{ item.inventory.unit }})
                            </span>
                            <div v-else-if="item.can_list" class="space-y-1">
                                <p
                                    class="text-xs font-medium text-amber-700 dark:text-amber-400"
                                >
                                    Not in your product list
                                </p>
                                <Button
                                    v-if="canAddToList"
                                    type="button"
                                    size="sm"
                                    variant="primary-outline"
                                    @click="addToList(item)"
                                >
                                    <PackagePlus class="h-4 w-4" />
                                    Add to product list
                                </Button>
                            </div>
                            <span v-else class="text-sm text-muted-foreground"
                                >Not tracked</span
                            >
                        </template>
                    </DataTable>

                    <section
                        v-if="
                            order.has_supplier_proof || order.has_received_proof
                        "
                        aria-labelledby="po-proof-heading"
                        class="rounded-[14px] border bg-card p-4"
                    >
                        <h2
                            id="po-proof-heading"
                            class="flex items-center gap-2 text-sm font-semibold"
                        >
                            <Camera class="h-4 w-4" aria-hidden="true" />
                            Delivery photos
                        </h2>
                        <div class="mt-3 grid gap-4 sm:grid-cols-2">
                            <figure v-if="order.has_supplier_proof">
                                <a
                                    :href="url('proof/supplier')"
                                    target="_blank"
                                    rel="noopener"
                                    class="block overflow-hidden rounded-lg border focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    <img
                                        :src="url('proof/supplier')"
                                        alt="Photo of the delivery taken by the supplier"
                                        class="h-44 w-full object-cover"
                                    />
                                </a>
                                <figcaption
                                    class="mt-1.5 flex items-center justify-between text-xs text-muted-foreground"
                                >
                                    <span>Supplier delivery photo</span>
                                    <ExternalLink
                                        class="h-3.5 w-3.5"
                                        aria-hidden="true"
                                    />
                                </figcaption>
                            </figure>
                            <figure v-if="order.has_received_proof">
                                <a
                                    :href="url('proof/received')"
                                    target="_blank"
                                    rel="noopener"
                                    class="block overflow-hidden rounded-lg border focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    <img
                                        :src="url('proof/received')"
                                        alt="Photo of the delivery taken by the shop when it arrived"
                                        class="h-44 w-full object-cover"
                                    />
                                </a>
                                <figcaption
                                    class="mt-1.5 flex items-center justify-between text-xs text-muted-foreground"
                                >
                                    <span>Shop receipt photo</span>
                                    <ExternalLink
                                        class="h-3.5 w-3.5"
                                        aria-hidden="true"
                                    />
                                </figcaption>
                            </figure>
                        </div>
                    </section>

                    <div
                        v-if="order.notes"
                        class="rounded-[14px] border bg-card p-4 text-sm"
                    >
                        <p class="text-xs font-medium text-muted-foreground">
                            Note to supplier
                        </p>
                        <p class="mt-1">{{ order.notes }}</p>
                    </div>
                </div>

                <!-- Timeline -->
                <aside class="rounded-[14px] border bg-card p-5">
                    <p class="text-sm font-semibold">Progress</p>
                    <ol class="mt-4 space-y-4">
                        <li
                            v-for="step in timeline"
                            :key="step.label"
                            class="flex gap-3"
                        >
                            <CheckCircle2
                                v-if="step.at"
                                class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600"
                                aria-hidden="true"
                            />
                            <Circle
                                v-else
                                class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground/50"
                                aria-hidden="true"
                            />
                            <div class="text-sm">
                                <p
                                    :class="
                                        step.at
                                            ? 'font-medium'
                                            : 'text-muted-foreground'
                                    "
                                >
                                    {{ step.label }}
                                </p>
                                <p
                                    v-if="step.at"
                                    class="text-xs text-muted-foreground"
                                >
                                    {{ step.at
                                    }}{{ step.by ? ` · ${step.by}` : '' }}
                                </p>
                            </div>
                        </li>
                    </ol>
                    <div
                        v-if="order.supplier"
                        class="mt-5 space-y-1 border-t pt-4 text-sm"
                    >
                        <p class="text-xs font-medium text-muted-foreground">
                            Supplier contact
                        </p>
                        <p class="font-medium">{{ order.supplier.name }}</p>
                        <p
                            v-if="order.supplier.contact_person"
                            class="text-muted-foreground"
                        >
                            {{ order.supplier.contact_person }}
                        </p>
                        <a
                            v-if="order.supplier.phone"
                            :href="`tel:${order.supplier.phone}`"
                            class="block text-primary hover:underline"
                            >{{ order.supplier.phone }}</a
                        >
                    </div>
                </aside>
            </div>
        </div>

        <!-- Cancel -->
        <Dialog v-model:open="cancelOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Cancel {{ order.po_number }}?</DialogTitle>
                    <DialogDescription>
                        {{
                            order.status === 'draft'
                                ? 'The draft is kept in the list as cancelled.'
                                : 'The supplier is told the order is cancelled.'
                        }}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <Button variant="outline" @click="cancelOpen = false"
                        >Keep order</Button
                    >
                    <Button variant="warning" @click="confirmCancel"
                        >Cancel order</Button
                    >
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Receive -->
        <Dialog v-model:open="receiveOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>What arrived?</DialogTitle>
                    <DialogDescription>
                        Change a quantity if the delivery was short and add a
                        photo of what you received. Supplies already in your
                        product list are added to your stock, and the cost is
                        saved as an expense.
                    </DialogDescription>
                </DialogHeader>
                <form class="space-y-3" @submit.prevent="submitReceive">
                    <div
                        v-for="item in order.items"
                        :key="item.id"
                        class="grid grid-cols-[minmax(0,1fr)_110px] items-center gap-3"
                    >
                        <label :for="`recv-${item.id}`" class="min-w-0 text-sm">
                            <span class="block truncate font-medium">{{
                                item.name
                            }}</span>
                            <span class="text-xs text-muted-foreground"
                                >Ordered {{ qty(item.quantity) }}
                                {{ item.unit ?? '' }}</span
                            >
                        </label>
                        <div>
                            <input
                                :id="`recv-${item.id}`"
                                v-model.number="receiveForm.received[item.id]"
                                type="number"
                                min="0"
                                :max="Number(item.quantity)"
                                step="0.01"
                                class="h-11 w-full rounded-md border border-input bg-background px-3 text-sm tabular-nums focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                            />
                            <InputError :message="receiveError(item.id)" />
                        </div>
                    </div>
                    <div class="space-y-1.5 border-t pt-3">
                        <label
                            for="recv-photo"
                            class="flex items-center gap-2 text-sm font-medium"
                        >
                            <Camera class="h-4 w-4" aria-hidden="true" />
                            Photo of the delivery
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="recv-photo"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            capture="environment"
                            required
                            class="block w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-primary/10 file:px-3 file:py-2 file:text-sm file:font-medium file:text-primary"
                            @change="onProof"
                        />
                        <p class="text-xs text-muted-foreground">
                            JPG, PNG or WEBP, up to 5 MB.
                        </p>
                        <InputError :message="receiveForm.errors.proof" />
                    </div>
                    <InputError
                        :message="
                            (
                                receiveForm.errors as Record<
                                    string,
                                    string | undefined
                                >
                            ).status
                        "
                    />
                    <div
                        class="flex items-baseline justify-between border-t pt-3 text-sm"
                    >
                        <span class="font-semibold">Cost of what arrived</span>
                        <span class="text-lg font-bold tabular-nums">{{
                            peso(receivedTotal)
                        }}</span>
                    </div>
                    <DialogFooter class="gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            @click="receiveOpen = false"
                            >Not yet</Button
                        >
                        <Button
                            type="submit"
                            variant="success"
                            :disabled="
                                receiveForm.processing || !receiveForm.proof
                            "
                        >
                            <PackageCheck class="h-4 w-4" /> Mark received
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </ShopLayout>
</template>
