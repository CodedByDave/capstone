<script setup lang="ts">
import {
    DataTable,
    RowActions,
    StatusPill,
    type DataTableHeader,
    type StatusTone,
} from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useDataTable } from '@/composables/useDataTable';
import { usePermissions } from '@/composables/usePermissions';
import { router } from '@inertiajs/vue3';
import {
    Ban,
    CreditCard,
    Loader2,
    Plus,
    RefreshCw,
    Wallet,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

export interface OrderPayment {
    id: string;
    amount: number;
    method: string;
    reference: string | null;
    note: string | null;
    status: 'pending' | 'confirmed' | 'voided' | 'cancelled';
    source: 'shop' | 'online';
    recorded_by: string | null;
    paid_at: string | null;
    created_at: string | null;
    voided_at: string | null;
    voided_by: string | null;
    void_reason: string | null;
}

const props = defineProps<{
    order: {
        payment_public_id: string;
        status: string;
        payment_status: string;
        total_amount: string;
        amount_paid: string;
    };
    payments: OrderPayment[];
    base: string;
}>();

const { can } = usePermissions();
const canManage = computed(() => can('Operations', 'manage'));

const total = computed(() => Number(props.order.total_amount));
const paid = computed(() => Number(props.order.amount_paid));
const balance = computed(() => Math.max(0, round(total.value - paid.value)));
const isCancelled = computed(() => props.order.status === 'cancelled');
const hasPending = computed(() =>
    props.payments.some((payment) => payment.status === 'pending'),
);

function round(value: number): number {
    return Math.round(value * 100) / 100;
}

const money = (value: number | string | null) =>
    value === null
        ? '—'
        : `₱${Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;

const formatDate = (value: string | null) =>
    value
        ? new Date(value).toLocaleString('en-PH', {
              month: 'short',
              day: 'numeric',
              year: 'numeric',
              hour: '2-digit',
              minute: '2-digit',
          })
        : '—';

const methodLabel = (method: string) =>
    ({ gcash: 'GCash', grabpay: 'GrabPay' })[method] ??
    method.charAt(0).toUpperCase() + method.slice(1);

const statusStyle = (status: string) =>
    ({
        unpaid: 'bg-red-50 text-red-700 ring-1 ring-red-200',
        partial: 'bg-orange-50 text-orange-700 ring-1 ring-orange-200',
        paid: 'bg-green-50 text-green-700 ring-1 ring-green-200',
    })[status] ?? 'bg-gray-50 text-gray-700 ring-1 ring-gray-200';

const paymentBadge = (status: string): { label: string; tone: StatusTone } =>
    ({
        confirmed: { label: 'Received', tone: 'success' as StatusTone },
        pending: {
            label: 'Waiting for online payment',
            tone: 'warning' as StatusTone,
        },
        voided: { label: 'Voided', tone: 'neutral' as StatusTone },
    })[status] ?? { label: status, tone: 'neutral' };

// ─── History table ──────────────────────────────────

const headers: DataTableHeader[] = [
    { text: 'Date', value: 'date', width: 160 },
    { text: 'Method', value: 'method', width: 110 },
    { text: 'Reference', value: 'reference', width: 150 },
    { text: 'Amount', value: 'amount', width: 120 },
    { text: 'Status', value: 'status', width: 190 },
    { text: 'Recorded by', value: 'recorder', width: 140 },
    { text: 'Actions', value: 'actions', width: 90, align: 'center' },
];

const rows = computed(() =>
    [...props.payments].reverse().map((payment) => ({
        ...payment,
        date: '',
        recorder: '',
        actions: '',
    })),
);

// Newest first, as recorded; paged five at a time.
const {
    options,
    pageItems,
    total: tableTotal,
} = useDataTable(rows, { rowsPerPage: 5 });

// ─── Record a payment ───────────────────────────────

const methods = ['cash', 'gcash', 'maya', 'card', 'grabpay'];
const recordOpen = ref(false);
const saving = ref(false);
const form = ref({ amount: '', method: 'cash', reference: '', note: '' });
const errors = ref<Record<string, string>>({});

function openRecord() {
    form.value = {
        amount: balance.value ? balance.value.toFixed(2) : '',
        method: 'cash',
        reference: '',
        note: '',
    };
    errors.value = {};
    recordOpen.value = true;
}

function payFullBalance() {
    form.value.amount = balance.value.toFixed(2);
}

const remainingAfter = computed(() => {
    const entered = Number(form.value.amount);
    return Number.isFinite(entered) && entered > 0
        ? Math.max(0, round(balance.value - entered))
        : balance.value;
});

function submitPayment() {
    saving.value = true;
    errors.value = {};
    router.post(
        `${props.base}/${props.order.payment_public_id}/payments`,
        {
            amount: form.value.amount,
            method: form.value.method,
            reference: form.value.reference || null,
            note: form.value.note || null,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                recordOpen.value = false;
            },
            onError: (e) => {
                errors.value = e as Record<string, string>;
            },
            onFinish: () => {
                saving.value = false;
            },
        },
    );
}

// ─── Void a payment ─────────────────────────────────

const voidTarget = ref<OrderPayment | null>(null);
const voidReason = ref('');
const voiding = ref(false);
const voidError = ref('');

function openVoid(payment: OrderPayment) {
    voidTarget.value = payment;
    voidReason.value = '';
    voidError.value = '';
}

function submitVoid() {
    if (!voidTarget.value) return;
    voiding.value = true;
    voidError.value = '';
    router.post(
        `${props.base}/${props.order.payment_public_id}/payments/${voidTarget.value.id}/void`,
        { reason: voidReason.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                voidTarget.value = null;
            },
            onError: (e) => {
                voidError.value = e.reason ?? 'Could not void this payment.';
            },
            onFinish: () => {
                voiding.value = false;
            },
        },
    );
}

// ─── Check online payment ───────────────────────────

const checking = ref(false);

function checkStatus() {
    checking.value = true;
    router.post(
        `${props.base}/${props.order.payment_public_id}/payments/check`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                checking.value = false;
            },
        },
    );
}
</script>

<template>
    <Card>
        <CardHeader class="flex flex-row items-center justify-between gap-3">
            <CardTitle class="flex items-center gap-2">
                <Wallet class="h-4 w-4" /> Payments
            </CardTitle>
            <span
                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold capitalize"
                :class="statusStyle(order.payment_status)"
            >
                <CreditCard class="h-3.5 w-3.5" />
                {{ order.payment_status }}
            </span>
        </CardHeader>
        <CardContent class="space-y-4">
            <div class="grid grid-cols-3 gap-3 text-sm">
                <div class="rounded-lg border p-3">
                    <p class="text-xs text-muted-foreground">Total</p>
                    <p class="font-semibold">{{ money(total) }}</p>
                </div>
                <div class="rounded-lg border p-3">
                    <p class="text-xs text-muted-foreground">Paid</p>
                    <p class="font-semibold text-emerald-600">
                        {{ money(paid) }}
                    </p>
                </div>
                <div class="rounded-lg border p-3">
                    <p class="text-xs text-muted-foreground">Balance</p>
                    <p
                        class="font-semibold"
                        :class="balance > 0 ? 'text-orange-600' : ''"
                    >
                        {{ money(balance) }}
                    </p>
                </div>
            </div>

            <div v-if="canManage" class="flex flex-wrap gap-2">
                <Button
                    v-if="balance > 0 && !isCancelled"
                    size="sm"
                    @click="openRecord"
                >
                    <Plus class="mr-1.5 h-4 w-4" /> Record payment
                </Button>
                <Button
                    v-if="hasPending"
                    size="sm"
                    variant="outline"
                    :disabled="checking"
                    @click="checkStatus"
                >
                    <Loader2
                        v-if="checking"
                        class="mr-1.5 h-4 w-4 animate-spin"
                    />
                    <RefreshCw v-else class="mr-1.5 h-4 w-4" />
                    Check payment status
                </Button>
            </div>

            <p
                v-if="hasPending"
                class="rounded-lg bg-amber-50 p-3 text-xs text-amber-700"
            >
                The customer started an online payment that is not confirmed
                yet. It is counted automatically once PayMongo reports it as
                paid.
            </p>

            <DataTable
                v-model:options="options"
                :headers="headers"
                :items="pageItems"
                :total="tableTotal"
                noun="payments"
                :empty-icon="Wallet"
                empty-message="No payments recorded yet."
                :resettable="false"
                :rows-items="[5, 10, 20]"
                :hide-footer="rows.length <= 5"
                :min-width="760"
                compact
            >
                <template #item-date="{ paid_at, created_at }">
                    <span class="whitespace-nowrap">
                        {{ formatDate(paid_at ?? created_at) }}
                    </span>
                </template>
                <template #item-method="{ method }">
                    {{ methodLabel(method) }}
                </template>
                <template #item-reference="{ reference, note }">
                    <span class="block">{{ reference ?? '—' }}</span>
                    <span
                        v-if="note"
                        class="block text-xs text-muted-foreground"
                        >{{ note }}</span
                    >
                </template>
                <template #item-amount="{ amount, status }">
                    <span
                        class="font-medium whitespace-nowrap"
                        :class="
                            status === 'voided'
                                ? 'text-muted-foreground line-through'
                                : ''
                        "
                        >{{ money(amount) }}</span
                    >
                </template>
                <template
                    #item-status="{ status, void_reason, voided_by, voided_at }"
                >
                    <StatusPill :tone="paymentBadge(status).tone">
                        {{ paymentBadge(status).label }}
                    </StatusPill>
                    <span
                        v-if="status === 'voided'"
                        class="mt-1 block text-xs text-muted-foreground"
                    >
                        {{ void_reason }}
                        <template v-if="voided_by">
                            — {{ voided_by }},
                            {{ formatDate(voided_at) }}</template
                        >
                    </span>
                </template>
                <template #item-recorder="{ recorded_by, source }">
                    {{
                        source === 'online'
                            ? 'Customer (online)'
                            : (recorded_by ?? '—')
                    }}
                </template>
                <template #item-actions="item">
                    <RowActions
                        :label="`${money(item.amount)} ${methodLabel(item.method)} payment`"
                        :actions="[
                            {
                                label: 'Void payment',
                                icon: Ban,
                                destructive: true,
                                show: canManage && item.status === 'confirmed',
                                onSelect: () => openVoid(item as OrderPayment),
                            },
                        ]"
                    />
                </template>
            </DataTable>
        </CardContent>
    </Card>

    <!-- Record payment -->
    <div
        v-if="recordOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm"
        @click.self="recordOpen = false"
    >
        <form
            class="w-full max-w-md space-y-4 rounded-2xl border bg-background p-5 shadow-xl"
            @submit.prevent="submitPayment"
        >
            <div>
                <p class="text-sm font-semibold">Record payment</p>
                <p class="mt-0.5 text-xs text-muted-foreground">
                    Balance due {{ money(balance) }}
                </p>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-medium" for="payment-amount"
                    >Amount received</label
                >
                <div class="flex gap-2">
                    <Input
                        id="payment-amount"
                        v-model="form.amount"
                        type="number"
                        step="0.01"
                        min="0.01"
                        inputmode="decimal"
                        required
                    />
                    <Button
                        type="button"
                        variant="outline"
                        @click="payFullBalance"
                        >Full balance</Button
                    >
                </div>
                <p v-if="errors.amount" class="text-xs text-red-500">
                    {{ errors.amount }}
                </p>
                <p v-else class="text-xs text-muted-foreground">
                    Balance after this payment: {{ money(remainingAfter) }}
                </p>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-medium" for="payment-method"
                    >Paid with</label
                >
                <select
                    id="payment-method"
                    v-model="form.method"
                    class="h-9 w-full rounded-md border bg-background px-3 text-sm"
                >
                    <option v-for="m in methods" :key="m" :value="m">
                        {{ methodLabel(m) }}
                    </option>
                </select>
                <p v-if="errors.method" class="text-xs text-red-500">
                    {{ errors.method }}
                </p>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-medium" for="payment-reference"
                    >Reference number (optional)</label
                >
                <Input
                    id="payment-reference"
                    v-model="form.reference"
                    maxlength="100"
                    placeholder="e.g. GCash reference"
                />
                <p v-if="errors.reference" class="text-xs text-red-500">
                    {{ errors.reference }}
                </p>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-medium" for="payment-note"
                    >Note (optional)</label
                >
                <Input id="payment-note" v-model="form.note" maxlength="255" />
                <p v-if="errors.note" class="text-xs text-red-500">
                    {{ errors.note }}
                </p>
            </div>

            <div class="flex justify-end gap-2">
                <Button
                    type="button"
                    variant="outline"
                    :disabled="saving"
                    @click="recordOpen = false"
                    >Cancel</Button
                >
                <Button type="submit" :disabled="saving">
                    <Loader2 v-if="saving" class="mr-2 h-4 w-4 animate-spin" />
                    Save payment
                </Button>
            </div>
        </form>
    </div>

    <!-- Void payment -->
    <div
        v-if="voidTarget"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm"
        @click.self="voidTarget = null"
    >
        <form
            class="w-full max-w-md space-y-4 rounded-2xl border bg-background p-5 shadow-xl"
            @submit.prevent="submitVoid"
        >
            <div>
                <p class="text-sm font-semibold">Void this payment?</p>
                <p class="mt-0.5 text-xs text-muted-foreground">
                    {{ money(voidTarget.amount) }} via
                    {{ methodLabel(voidTarget.method) }}. It stays in the
                    history and the balance goes back up.
                </p>
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-medium" for="void-reason"
                    >Reason</label
                >
                <Input
                    id="void-reason"
                    v-model="voidReason"
                    maxlength="255"
                    required
                    placeholder="e.g. Typed the wrong amount"
                />
                <p v-if="voidError" class="text-xs text-red-500">
                    {{ voidError }}
                </p>
            </div>
            <div class="flex justify-end gap-2">
                <Button
                    type="button"
                    variant="outline"
                    :disabled="voiding"
                    @click="voidTarget = null"
                    >Keep payment</Button
                >
                <Button type="submit" variant="destructive" :disabled="voiding">
                    <Loader2 v-if="voiding" class="mr-2 h-4 w-4 animate-spin" />
                    Void payment
                </Button>
            </div>
        </form>
    </div>
</template>
