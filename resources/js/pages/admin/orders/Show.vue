<script setup lang="ts">
import AgreementSignatureInput from '@/components/business/AgreementSignatureInput.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { downloadBusinessAgreementPdf } from '@/lib/businessAgreementPdf';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
    Check,
    CreditCard,
    Download,
    FileSignature,
    X,
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
    signature_method: 'drawn' | 'uploaded' | null;
    signature_url: string | null;
    execution_status: 'awaiting_platform_signature' | 'fully_executed';
    platform_signature: {
        public_id: string;
        signer_name: string;
        signer_role: string;
        signed_at: string;
        signature_method: 'drawn' | 'uploaded';
        signature_url: string;
    } | null;
    agreement: {
        title: string;
        version: string;
        content: string;
        effective_at: string;
    };
}

// ─── Props ────────────────────────────────────────────────────────────────────

const { order, agreementAcceptance, platformSigner } = defineProps<{
    order: Order;
    agreementAcceptance: AgreementAcceptance | null;
    platformSigner: { name: string; role: string };
}>();

const downloadingAgreement = ref(false);

async function downloadAgreement() {
    if (!agreementAcceptance || downloadingAgreement.value) return;

    downloadingAgreement.value = true;

    try {
        await downloadBusinessAgreementPdf(agreementAcceptance);
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

const paymentStatusBadge: Record<string, string> = {
    paid: 'bg-green-100 text-green-700',
    pending: 'bg-amber-100 text-amber-700',
    failed: 'bg-red-100 text-red-600',
    refund_pending: 'bg-orange-100 text-orange-700',
    refunded: 'bg-gray-100 text-gray-500',
};

const paymentStatusLabel: Record<string, string> = {
    paid: 'Paid',
    pending: 'Pending',
    failed: 'Failed',
    refund_pending: 'Refund Pending',
    refunded: 'Refunded',
};

// ─── Approve / Reject ─────────────────────────────────────────────────────────

const showRejectDialog = ref(false);
const showApprovalDialog = ref(false);
const rejectionReason = ref('');
const submitting = ref(false);
const approvalForm = useForm({
    platform_signer_name: platformSigner.name,
    platform_signer_role: platformSigner.role,
    platform_signature_method: 'drawn' as 'drawn' | 'uploaded',
    platform_signature_image: null as File | null,
    platform_signer_authority_confirmed: false,
});

function approveOrder() {
    if (!agreementAcceptance) return;
    approvalForm.clearErrors();
    showApprovalDialog.value = true;
}

function submitApproval() {
    approvalForm.post(`/admin/orders/${order.public_id}/approve`, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            showApprovalDialog.value = false;
            approvalForm.reset(
                'platform_signature_image',
                'platform_signer_authority_confirmed',
            );
        },
    });
}

function openRejectDialog() {
    rejectionReason.value = '';
    showRejectDialog.value = true;
}

function submitReject() {
    if (!rejectionReason.value.trim()) return;
    submitting.value = true;
    router.post(
        `/admin/orders/${order.public_id}/reject`,
        { rejection_reason: rejectionReason.value },
        {
            onSuccess: () => {
                showRejectDialog.value = false;
            },
            onFinish: () => {
                submitting.value = false;
            },
        },
    );
}
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
                    <template v-if="order.status === 'pending'">
                        <Button
                            size="sm"
                            class="bg-emerald-600 text-white hover:bg-emerald-700"
                            :disabled="submitting || !agreementAcceptance"
                            @click="approveOrder"
                        >
                            Approve and sign
                        </Button>
                        <Button
                            size="sm"
                            variant="destructive"
                            :disabled="submitting"
                            @click="openRejectDialog"
                        >
                            Reject
                        </Button>
                    </template>
                    <Button
                        variant="outline"
                        size="sm"
                        @click="router.visit('/admin/orders')"
                    >
                        <ArrowLeft class="mr-1.5 h-4 w-4" /> Back
                    </Button>
                </div>
            </div>

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
                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle
                                class="flex items-center gap-2 text-sm font-semibold"
                            >
                                Payment History
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div
                                v-if="order.payments.length > 0"
                                class="overflow-hidden rounded-lg border"
                            >
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr
                                            class="border-b bg-muted/40 text-xs text-muted-foreground"
                                        >
                                            <th
                                                class="px-4 py-2.5 text-left font-medium"
                                            >
                                                Method
                                            </th>
                                            <th
                                                class="px-4 py-2.5 text-left font-medium"
                                            >
                                                Amount
                                            </th>
                                            <th
                                                class="px-4 py-2.5 text-left font-medium"
                                            >
                                                Status
                                            </th>
                                            <th
                                                class="px-4 py-2.5 text-left font-medium"
                                            >
                                                Transaction ID
                                            </th>
                                            <th
                                                class="px-4 py-2.5 text-left font-medium"
                                            >
                                                Paid At
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="payment in order.payments"
                                            :key="payment.id"
                                            class="border-b last:border-0"
                                        >
                                            <td class="px-4 py-2.5 capitalize">
                                                {{ payment.payment_method }}
                                            </td>
                                            <td class="px-4 py-2.5 font-medium">
                                                {{
                                                    formatPrice(payment.amount)
                                                }}
                                            </td>
                                            <td class="px-4 py-2.5">
                                                <span
                                                    class="rounded-full px-2 py-0.5 text-xs font-medium"
                                                    :class="
                                                        paymentStatusBadge[
                                                            payment.status
                                                        ] ??
                                                        'bg-gray-100 text-gray-500'
                                                    "
                                                >
                                                    {{
                                                        paymentStatusLabel[
                                                            payment.status
                                                        ] ?? payment.status
                                                    }}
                                                </span>
                                            </td>
                                            <td
                                                class="px-4 py-2.5 font-mono text-xs text-muted-foreground"
                                            >
                                                {{
                                                    payment.transaction_id ??
                                                    payment.paymongo_payment_id ??
                                                    '—'
                                                }}
                                            </td>
                                            <td
                                                class="px-4 py-2.5 text-xs whitespace-nowrap text-muted-foreground"
                                            >
                                                {{
                                                    formatDate(payment.paid_at)
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div
                                v-else
                                class="py-8 text-center text-sm text-muted-foreground"
                            >
                                <CreditCard
                                    class="mx-auto mb-2 h-8 w-8 opacity-20"
                                />
                                No payment records found.
                            </div>
                        </CardContent>
                    </Card>
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
                            <div class="flex items-center justify-between">
                                <span class="text-muted-foreground"
                                    >Status</span
                                >
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                    :class="
                                        agreementAcceptance.execution_status ===
                                        'fully_executed'
                                            ? 'bg-emerald-100 text-emerald-700'
                                            : 'bg-amber-100 text-amber-700'
                                    "
                                >
                                    {{
                                        agreementAcceptance.execution_status ===
                                        'fully_executed'
                                            ? 'Fully Executed'
                                            : 'Awaiting Platform Signature'
                                    }}
                                </span>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Signed by
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
                            <div v-if="agreementAcceptance.platform_signature">
                                <p class="text-xs text-muted-foreground">
                                    Platform representative
                                </p>
                                <p class="font-medium">
                                    {{
                                        agreementAcceptance.platform_signature
                                            .signer_name
                                    }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{
                                        agreementAcceptance.platform_signature
                                            .signer_role
                                    }}
                                    ·
                                    {{
                                        formatDate(
                                            agreementAcceptance
                                                .platform_signature.signed_at,
                                        )
                                    }}
                                </p>
                            </div>
                            <Button
                                v-if="
                                    agreementAcceptance.execution_status ===
                                    'fully_executed'
                                "
                                variant="outline"
                                size="sm"
                                class="w-full gap-2 bg-blue-600 text-white hover:bg-blue-700 hover:text-white"
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

        <!-- Approval and platform signature dialog -->
        <Teleport to="body">
            <div
                v-if="showApprovalDialog"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
            >
                <div
                    class="fixed inset-0 bg-black/50"
                    @click="showApprovalDialog = false"
                />
                <form
                    class="relative z-10 my-8 w-full max-w-2xl space-y-5 bg-background p-6 shadow-xl"
                    @submit.prevent="submitApproval"
                >
                    <div>
                        <h3 class="text-lg font-semibold">
                            Approve and countersign agreement
                        </h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Approval records your signature for LaundryHub and
                            makes the agreement fully executed.
                        </p>
                    </div>

                    <div
                        v-if="
                            approvalForm.errors.agreement ||
                            approvalForm.errors.order
                        "
                        class="border-y border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700"
                    >
                        {{
                            approvalForm.errors.agreement ||
                            approvalForm.errors.order
                        }}
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <Label class="mb-1.5">Printed full name</Label>
                            <Input
                                v-model="approvalForm.platform_signer_name"
                            />
                            <p
                                v-if="approvalForm.errors.platform_signer_name"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ approvalForm.errors.platform_signer_name }}
                            </p>
                        </div>
                        <div>
                            <Label class="mb-1.5"
                                >Position / signing capacity</Label
                            >
                            <Input
                                v-model="approvalForm.platform_signer_role"
                            />
                            <p
                                v-if="approvalForm.errors.platform_signer_role"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ approvalForm.errors.platform_signer_role }}
                            </p>
                        </div>
                    </div>

                    <AgreementSignatureInput
                        v-model="approvalForm.platform_signature_image"
                        label="LaundryHub representative signature"
                        description="Draw your authorized platform signature or upload a clear signature image."
                        file-name="platform-signature.png"
                        :disabled="approvalForm.processing"
                        :error="approvalForm.errors.platform_signature_image"
                        @update:method="
                            approvalForm.platform_signature_method = $event
                        "
                    />

                    <label
                        class="flex cursor-pointer items-start gap-2 text-sm"
                    >
                        <input
                            v-model="
                                approvalForm.platform_signer_authority_confirmed
                            "
                            type="checkbox"
                            class="mt-1 accent-emerald-600"
                        />
                        <span>
                            I confirm that I am authorized to approve this order
                            and bind LaundryHub to this agreement.
                        </span>
                    </label>
                    <p
                        v-if="
                            approvalForm.errors
                                .platform_signer_authority_confirmed
                        "
                        class="text-xs text-red-600"
                    >
                        {{
                            approvalForm.errors
                                .platform_signer_authority_confirmed
                        }}
                    </p>

                    <div class="flex justify-end gap-2 border-t pt-4">
                        <Button
                            type="button"
                            variant="outline"
                            :disabled="approvalForm.processing"
                            @click="showApprovalDialog = false"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            class="bg-emerald-600 text-white hover:bg-emerald-700"
                            :disabled="
                                approvalForm.processing ||
                                !approvalForm.platform_signature_image ||
                                !approvalForm.platform_signer_authority_confirmed
                            "
                        >
                            Approve and sign
                        </Button>
                    </div>
                </form>
            </div>
        </Teleport>

        <!-- Reject dialog -->
        <Teleport to="body">
            <div
                v-if="showRejectDialog"
                class="fixed inset-0 z-50 flex items-center justify-center"
            >
                <div
                    class="absolute inset-0 bg-black/50"
                    @click="showRejectDialog = false"
                />
                <div
                    class="relative z-10 mx-4 w-full max-w-md rounded-xl bg-background p-6 shadow-xl"
                >
                    <div class="mb-4 flex items-start gap-3">
                        <AlertTriangle
                            class="mt-0.5 h-5 w-5 shrink-0 text-red-500"
                        />
                        <div>
                            <h3 class="text-base font-semibold">
                                Reject {{ order.transaction_reference }}
                            </h3>
                            <p class="mt-0.5 text-sm text-muted-foreground">
                                Provide a reason. The shop owner will be
                                notified by email. No payment will be collected.
                            </p>
                        </div>
                    </div>
                    <textarea
                        v-model="rejectionReason"
                        rows="4"
                        placeholder="e.g. Incomplete or invalid KYC documents submitted."
                        class="w-full resize-none rounded-lg border bg-muted/30 px-3 py-2 text-sm focus:ring-2 focus:ring-red-400 focus:outline-none"
                    />
                    <p
                        v-if="!rejectionReason.trim()"
                        class="mt-1 text-xs text-red-400"
                    >
                        Reason is required.
                    </p>
                    <div class="mt-4 flex justify-end gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            @click="showRejectDialog = false"
                            >Cancel</Button
                        >
                        <Button
                            size="sm"
                            variant="destructive"
                            :disabled="!rejectionReason.trim() || submitting"
                            @click="submitReject"
                        >
                            <X class="mr-1.5 h-4 w-4" /> Confirm Reject
                        </Button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>
