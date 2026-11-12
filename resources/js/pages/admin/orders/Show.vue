<script setup lang="ts">
import {
    DataTable,
    StatusPill,
    type DataTableHeader,
    type StatusTone,
} from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
    CreditCard,
    Download,
    FileSignature,
    FileText,
    ShieldCheck,
} from 'lucide-vue-next';
import { ref } from 'vue';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Module {
    id: number;
    name: string;
}

interface Payment {
    id: number;
    payment_method: string;
    amount: string;
    status: string;
    transaction_id: string | null;
    paymongo_payment_id: string | null;
    paid_at: string | null;
    created_at: string;
}

interface Order {
    kyc_bir?: string | null;
    kyc_dti?: string | null;
    kyc_mayors?: string | null;
    kyc_sanitary?: string | null;
    kyc_flags?: string[] | null;
    public_id: string;
    transaction_reference: string;
    shop_name: string;
    owner_name: string;
    email: string;
    phone: string;
    block_street: string;
    municipality: string;
    barangay: string;
    postal_code: string;
    plan_name: string | null;
    total_price: string;
    status: string;
    rejection_reason: string | null;
    expires_at: string | null;
    created_at: string;
    modules: Module[];
    payments: Payment[];
}

interface AgreementAcceptance {
    public_id: string;
    business_name: string;
    signer_name: string;
    signer_role: string;
    accepted_at: string;
    content_hash: string;
    agreement: {
        title: string;
        version: string;
        content: string;
        effective_at: string;
    };
}

// ─── Props ────────────────────────────────────────────────────────────────────

interface KycReviewRow {
    key: string;
    label: string;
    ocr: string | null;
    typed: string | null;
    confirmed: string | null;
    status: 'match' | 'mismatch' | 'unread';
}

const { order, agreementAcceptance, kycReview, kycFlagOptions } = defineProps<{
    order: Order;
    agreementAcceptance: AgreementAcceptance | null;
    kycReview: KycReviewRow[];
    kycFlagOptions: { key: string; label: string }[];
}>();

const kycStatusMeta = {
    match: { text: 'Matches', cls: 'bg-emerald-100 text-emerald-700' },
    mismatch: { text: 'Mismatch', cls: 'bg-red-100 text-red-600' },
    unread: { text: 'Not read', cls: 'bg-stone-100 text-stone-600' },
} as const;
const kycDocLinks = [
    { key: 'kyc_bir', label: 'BIR' },
    { key: 'kyc_dti', label: 'DTI' },
    { key: 'kyc_mayors', label: "Mayor's permit" },
    { key: 'kyc_sanitary', label: 'Sanitary permit' },
] as const;

const downloadingAgreement = ref(false);

async function downloadAgreement() {
    if (!agreementAcceptance || downloadingAgreement.value) return;

    downloadingAgreement.value = true;

    try {
        window.open(
            `/business-agreement-acceptances/${agreementAcceptance.public_id}/pdf`,
            '_blank',
            'noopener',
        );
    } finally {
        downloadingAgreement.value = false;
    }
}

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Order Management', href: '/admin/orders' },
    {
        title: order.transaction_reference,
        href: `/admin/orders/${order.public_id}`,
    },
];

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatDate(d: string | null) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function formatPrice(p: string | number) {
    return `₱${Number(p).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
}

function moduleBadgeClass(name: string) {
    const moduleName = name.toLowerCase();

    if (moduleName.includes('hrm')) {
        return 'bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:hover:bg-blue-900/70';
    }
    if (moduleName.includes('operations')) {
        return 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:hover:bg-emerald-900/70';
    }
    if (moduleName.includes('inventory')) {
        return 'bg-amber-100 text-amber-700 hover:bg-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:hover:bg-amber-900/70';
    }
    if (moduleName.includes('finance')) {
        return 'bg-violet-100 text-violet-700 hover:bg-violet-200 dark:bg-violet-950/60 dark:text-violet-300 dark:hover:bg-violet-900/70';
    }

    return 'bg-rose-100 text-rose-700 hover:bg-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:hover:bg-rose-900/70';
}

function isExpired(expiresAt: string | null) {
    if (!expiresAt) return false;
    return new Date(expiresAt) < new Date();
}

const planStyles: Record<string, { label: string; cls: string }> = {
    Basic: { label: 'Basic', cls: 'bg-blue-100 text-blue-700' },
    Standard: { label: 'Standard', cls: 'bg-violet-100 text-violet-700' },
    Premium: { label: 'Premium', cls: 'bg-amber-100 text-amber-700' },
};

function getPlanBadge(p: string | null) {
    if (!p) return { label: 'None', cls: 'bg-gray-100 text-gray-400' };
    return planStyles[p] ?? { label: p, cls: 'bg-gray-100 text-gray-500' };
}

const statusBadge: Record<string, string> = {
    paid: 'bg-green-100 text-green-700',
    approved: 'bg-emerald-100 text-emerald-700',
    rejected: 'bg-red-100 text-red-600',
    pending: 'bg-amber-100 text-amber-700',
    failed: 'bg-red-100 text-red-600',
};

const paymentStatusTone: Record<string, StatusTone> = {
    paid: 'success',
    pending: 'warning',
    failed: 'danger',
    refund_pending: 'warning',
    refunded: 'neutral',
};

const paymentHeaders: DataTableHeader[] = [
    { text: 'Method', value: 'payment_method' },
    { text: 'Amount', value: 'amount' },
    { text: 'Status', value: 'status' },
    { text: 'Transaction ID', value: 'transaction_id' },
    { text: 'Paid at', value: 'paid_at' },
];

const paymentStatusLabel: Record<string, string> = {
    paid: 'Paid',
    pending: 'Pending',
    failed: 'Failed',
    refund_pending: 'Refund Pending',
    refunded: 'Refunded',
};
</script>

<template>
    <Head :title="order.transaction_reference" />
    <AdminLayout
        :breadcrumbs="breadcrumbs"
        :title="order.transaction_reference"
    >
        <div class="space-y-6 px-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="flex items-center gap-2 text-lg font-semibold">
                        {{ order.transaction_reference }}
                    </h2>
                    <p class="mt-0.5 text-sm text-muted-foreground">
                        Placed on {{ formatDate(order.created_at) }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span
                        class="rounded-full px-3 py-1 text-sm font-medium capitalize"
                        :class="
                            statusBadge[order.status] ??
                            'bg-gray-100 text-gray-500'
                        "
                    >
                        {{ order.status }}
                    </span>
                    <Button
                        variant="back"
                        size="sm"
                        @click="router.visit('/admin/orders')"
                    >
                        <ArrowLeft class="mr-1.5 h-4 w-4" /> Back
                    </Button>
                </div>
            </div>

            <!-- KYC verification: OCR vs. what the shop submitted -->
            <section
                v-if="kycReview.length"
                class="rounded-xl border bg-background"
                aria-labelledby="kyc-verification-title"
            >
                <div
                    class="flex flex-wrap items-center justify-between gap-2 border-b px-4 py-3"
                >
                    <h2
                        id="kyc-verification-title"
                        class="flex items-center gap-2 text-sm font-semibold"
                    >
                        <ShieldCheck class="h-4 w-4" /> KYC verification
                    </h2>
                    <div class="flex flex-wrap gap-3 text-xs">
                        <template v-for="doc in kycDocLinks" :key="doc.key">
                            <a
                                v-if="order[doc.key]"
                                :href="`/admin/kyc-file?path=${encodeURIComponent(order[doc.key] as string)}`"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex items-center gap-1 font-medium text-primary hover:underline"
                            >
                                <FileText class="h-3.5 w-3.5" />
                                {{ doc.label }}
                            </a>
                        </template>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="bg-muted/40 text-xs tracking-wide text-muted-foreground uppercase"
                        >
                            <tr>
                                <th class="px-4 py-2 font-medium">Field</th>
                                <th class="px-4 py-2 font-medium">
                                    Read by OCR
                                </th>
                                <th class="px-4 py-2 font-medium">
                                    Registered
                                </th>
                                <th class="px-4 py-2 font-medium">
                                    Confirmed by shop
                                </th>
                                <th class="px-4 py-2 font-medium">Result</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="row in kycReview"
                                :key="row.key"
                                :class="
                                    row.status === 'mismatch'
                                        ? 'bg-red-50/50'
                                        : ''
                                "
                            >
                                <td class="px-4 py-2.5 font-medium">
                                    {{ row.label }}
                                </td>
                                <td class="px-4 py-2.5">
                                    {{ row.ocr ?? '—' }}
                                </td>
                                <td class="px-4 py-2.5">
                                    {{ row.typed ?? '—' }}
                                </td>
                                <td class="px-4 py-2.5">
                                    {{ row.confirmed ?? '—' }}
                                </td>
                                <td class="px-4 py-2.5">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="kycStatusMeta[row.status].cls"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full bg-current"
                                        />
                                        {{ kycStatusMeta[row.status].text }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p
                    v-if="order.kyc_flags?.length"
                    class="border-t px-4 py-2 text-xs text-muted-foreground"
                >
                    Flagged on rejection:
                    {{
                        kycFlagOptions
                            .filter((o) => order.kyc_flags?.includes(o.key))
                            .map((o) => o.label)
                            .join(', ')
                    }}
                </p>
            </section>

            <!-- Rejection reason banner -->
            <div
                v-if="order.status === 'rejected' && order.rejection_reason"
                class="flex gap-3 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-950/30"
            >
                <AlertTriangle class="mt-0.5 h-5 w-5 shrink-0 text-red-500" />
                <div class="flex-1">
                    <p
                        class="mb-0.5 text-sm font-semibold text-red-700 dark:text-red-400"
                    >
                        Rejection Reason
                    </p>
                    <p class="text-sm text-red-600 dark:text-red-300">
                        {{ order.rejection_reason }}
                    </p>
                    <p class="mt-1.5 text-xs text-red-400">
                        <template
                            v-if="
                                order.payments.some(
                                    (p) => p.status === 'refunded',
                                )
                            "
                        >
                            A PayMongo refund was automatically issued. The
                            owner has been notified by email.
                        </template>
                        <template
                            v-else-if="
                                order.payments.some(
                                    (p) => p.status === 'refund_pending',
                                )
                            "
                        >
                            Refund is pending manual processing. See payment
                            history below.
                        </template>
                        <template v-else>
                            The shop owner has been notified by email.
                        </template>
                    </p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Left col -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Shop & Owner info -->
                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle
                                class="flex items-center gap-2 text-sm font-semibold"
                            >
                                Shop Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="mb-1 text-xs text-muted-foreground">
                                    Shop Name
                                </p>
                                <p class="font-medium">{{ order.shop_name }}</p>
                            </div>
                            <div>
                                <p class="mb-1 text-xs text-muted-foreground">
                                    Owner
                                </p>
                                <p
                                    class="flex items-center gap-1.5 font-medium"
                                >
                                    {{ order.owner_name }}
                                </p>
                            </div>
                            <div>
                                <p class="mb-1 text-xs text-muted-foreground">
                                    Email
                                </p>
                                <p
                                    class="flex items-center gap-1.5 font-medium"
                                >
                                    {{ order.email }}
                                </p>
                            </div>
                            <div>
                                <p class="mb-1 text-xs text-muted-foreground">
                                    Phone
                                </p>
                                <p
                                    class="flex items-center gap-1.5 font-medium"
                                >
                                    {{ order.phone }}
                                </p>
                            </div>
                            <div class="col-span-2">
                                <p class="mb-1 text-xs text-muted-foreground">
                                    Address
                                </p>
                                <p
                                    class="flex items-center gap-1.5 font-medium"
                                >
                                    {{
                                        [
                                            order.block_street,
                                            order.barangay,
                                            order.municipality,
                                        ]
                                            .filter(Boolean)
                                            .join(', ')
                                    }}
                                    <span
                                        v-if="order.postal_code"
                                        class="text-muted-foreground"
                                        >{{ order.postal_code }}</span
                                    >
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Payment history -->
                    <DataTable
                        :headers="paymentHeaders"
                        :items="order.payments"
                        noun="payments"
                        :empty-icon="CreditCard"
                        empty-message="No payment records found."
                        :resettable="false"
                        :min-width="640"
                        compact
                        hide-footer
                    >
                        <template #toolbar>
                            <h2 class="text-sm font-semibold">
                                Payment History
                            </h2>
                        </template>

                        <template #item-payment_method="payment">
                            <span class="capitalize">{{
                                payment.payment_method
                            }}</span>
                        </template>

                        <template #item-amount="payment">
                            <span class="font-medium whitespace-nowrap">{{
                                formatPrice(payment.amount)
                            }}</span>
                        </template>

                        <template #item-status="payment">
                            <StatusPill
                                :tone="
                                    paymentStatusTone[payment.status] ??
                                    'neutral'
                                "
                            >
                                {{
                                    paymentStatusLabel[payment.status] ??
                                    payment.status
                                }}
                            </StatusPill>
                        </template>

                        <template #item-transaction_id="payment">
                            <span
                                class="font-mono text-xs text-muted-foreground"
                            >
                                {{
                                    payment.transaction_id ??
                                    payment.paymongo_payment_id ??
                                    '—'
                                }}
                            </span>
                        </template>

                        <template #item-paid_at="payment">
                            <span
                                class="text-xs whitespace-nowrap text-muted-foreground"
                            >
                                {{ formatDate(payment.paid_at) }}
                            </span>
                        </template>
                    </DataTable>
                </div>

                <!-- Right col — subscription summary -->
                <div class="space-y-6">
                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle class="text-sm font-semibold"
                                >Subscription Summary</CardTitle
                            >
                        </CardHeader>
                        <CardContent class="space-y-4 text-sm">
                            <div class="flex items-center justify-between">
                                <p class="text-muted-foreground">Plan</p>
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="getPlanBadge(order.plan_name).cls"
                                >
                                    {{ getPlanBadge(order.plan_name).label }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-muted-foreground">
                                    Order Status
                                </p>
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                                    :class="
                                        statusBadge[order.status] ??
                                        'bg-gray-100 text-gray-500'
                                    "
                                >
                                    {{ order.status }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-muted-foreground">
                                    Total Amount
                                </p>
                                <p class="font-semibold text-green-600">
                                    {{ formatPrice(order.total_price) }}
                                </p>
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-muted-foreground">Modules</p>
                                <p class="font-medium">
                                    {{ order.modules.length }}
                                </p>
                            </div>
                            <div class="border-t pt-4">
                                <p class="mb-1 text-xs text-muted-foreground">
                                    Subscription Expiry
                                </p>
                                <p
                                    class="text-sm font-medium"
                                    :class="
                                        isExpired(order.expires_at)
                                            ? 'text-red-500'
                                            : 'text-foreground'
                                    "
                                >
                                    {{ formatDate(order.expires_at) }}
                                    <span
                                        v-if="isExpired(order.expires_at)"
                                        class="mt-0.5 block text-xs text-red-400"
                                    >
                                        This subscription has expired
                                    </span>
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Business agreement -->
                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle
                                class="flex items-center gap-2 text-sm font-semibold"
                            >
                                <FileSignature class="h-4 w-4" /> Business
                                Agreement
                            </CardTitle>
                        </CardHeader>
                        <CardContent
                            v-if="agreementAcceptance"
                            class="space-y-3 text-sm"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-muted-foreground"
                                    >Version</span
                                >
                                <span class="font-medium">{{
                                    agreementAcceptance.agreement.version
                                }}</span>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Accepted by
                                </p>
                                <p class="font-medium">
                                    {{ agreementAcceptance.signer_name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ agreementAcceptance.signer_role }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Accepted
                                </p>
                                <p class="font-medium">
                                    {{
                                        formatDate(
                                            agreementAcceptance.accepted_at,
                                        )
                                    }}
                                </p>
                            </div>
                            <Button
                                size="sm"
                                class="w-full gap-2"
                                :disabled="downloadingAgreement"
                                @click="downloadAgreement"
                            >
                                <Download class="h-4 w-4" /> Download agreement
                            </Button>
                        </CardContent>
                        <CardContent
                            v-else
                            class="text-sm text-muted-foreground"
                        >
                            No agreement acceptance is linked to this order.
                        </CardContent>
                    </Card>

                    <!-- Modules -->
                    <Card>
                        <CardHeader class="px-4 pt-4 pb-2">
                            <CardTitle
                                class="flex items-center gap-2 text-sm font-semibold"
                            >
                                Subscribed Modules
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="px-4 pb-4">
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="mod in order.modules"
                                    :key="mod.id"
                                    class="rounded-md px-2.5 py-1.5 text-xs font-medium transition-colors"
                                    :class="moduleBadgeClass(mod.name)"
                                >
                                    {{ mod.name }}
                                </span>
                                <span
                                    v-if="order.modules.length === 0"
                                    class="text-xs text-muted-foreground"
                                >
                                    No subscribed modules
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
