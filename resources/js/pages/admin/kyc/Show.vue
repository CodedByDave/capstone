<script setup lang="ts">
import { StatusPill, statusLabel, statusTone } from '@/components/data-table';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
    Check,
    FileText,
    ShieldCheck,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

interface KycReviewRow {
    key: string;
    label: string;
    ocr: string | null;
    typed: string | null;
    confirmed: string | null;
    status: 'match' | 'mismatch' | 'unread';
}

const props = defineProps<{
    verification: {
        public_id: string;
        status: string;
        shop_name: string;
        owner_name: string | null;
        email: string | null;
        block_street: string | null;
        barangay: string;
        municipality: string;
        postal_code: string;
        documents: Record<string, string>;
        rejection_reason: string | null;
        flags: string[];
        reviewed_by: string | null;
        reviewed_at: string | null;
        created_at: string;
    };
    kycReview: KycReviewRow[];
    kycFlagOptions: { key: string; label: string }[];
}>();

const page = usePage();
onMounted(() => {
    const flash = page.props.toast as
        | { type: string; message: string }
        | undefined;
    if (flash)
        (flash.type === 'error' ? toast.error : toast.success)(flash.message);
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'KYC Verifications', href: '/admin/kyc-verifications' },
    { title: props.verification.shop_name, href: '#' },
];

const statusMeta = {
    match: { text: 'Matches', cls: 'bg-emerald-100 text-emerald-700' },
    mismatch: { text: 'Mismatch', cls: 'bg-red-100 text-red-600' },
    unread: { text: 'Not read', cls: 'bg-stone-100 text-stone-600' },
} as const;
const docLabels: Record<string, string> = {
    bir: 'BIR',
    dti: 'DTI',
    mayors: "Mayor's permit",
    sanitary: 'Sanitary permit',
};

const isPending = computed(() => props.verification.status === 'pending');
const mismatchedKeys = computed(() =>
    props.kycReview.filter((r) => r.status === 'mismatch').map((r) => r.key),
);

const showReject = ref(false);
const reason = ref('');
const flags = ref<string[]>([]);
const busy = ref(false);

function openReject() {
    reason.value = '';
    // Start with the fields OCR found mismatched; the admin can adjust.
    flags.value = [...mismatchedKeys.value];
    showReject.value = true;
}

function approve() {
    busy.value = true;
    router.post(
        `/admin/kyc-verifications/${props.verification.public_id}/approve`,
        {},
        { preserveScroll: true, onFinish: () => (busy.value = false) },
    );
}

function reject() {
    if (!reason.value.trim()) return;
    busy.value = true;
    router.post(
        `/admin/kyc-verifications/${props.verification.public_id}/reject`,
        { rejection_reason: reason.value, flags: flags.value },
        {
            preserveScroll: true,
            onSuccess: () => (showReject.value = false),
            onError: () => router.reload(),
            onFinish: () => (busy.value = false),
        },
    );
}

const address = computed(() =>
    [
        props.verification.block_street,
        props.verification.barangay,
        props.verification.municipality,
        props.verification.postal_code,
    ]
        .filter(Boolean)
        .join(', '),
);
</script>

<template>
    <Head :title="`KYC — ${verification.shop_name}`" />

    <AdminLayout :breadcrumbs="breadcrumbs" title="KYC Verifications">
        <div class="space-y-4 px-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <Button
                        variant="outline"
                        size="icon"
                        aria-label="Back to KYC verifications"
                        @click="router.visit('/admin/kyc-verifications')"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <div>
                        <h1 class="text-xl font-semibold">
                            {{ verification.shop_name }}
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            {{ verification.owner_name }} ·
                            {{ verification.email }} · submitted
                            {{ verification.created_at }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <StatusPill :tone="statusTone(verification.status)">
                        {{ statusLabel(verification.status) }}
                    </StatusPill>
                    <template v-if="isPending">
                        <Button
                            variant="outline"
                            size="sm"
                            class="border-red-300 text-red-600 hover:bg-red-50"
                            :disabled="busy"
                            @click="openReject"
                        >
                            <X class="mr-1.5 h-4 w-4" /> Reject
                        </Button>
                        <Button size="sm" :disabled="busy" @click="approve">
                            <Check class="mr-1.5 h-4 w-4" /> Approve
                        </Button>
                    </template>
                </div>
            </div>

            <div
                v-if="verification.status === 'rejected'"
                class="flex gap-3 rounded-xl border border-red-200 bg-red-50 p-4"
            >
                <AlertTriangle class="mt-0.5 h-5 w-5 shrink-0 text-red-500" />
                <div class="text-sm">
                    <p class="font-semibold text-red-700">Rejection reason</p>
                    <p class="text-red-600">
                        {{ verification.rejection_reason }}
                    </p>
                    <p
                        v-if="verification.flags.length"
                        class="mt-1 text-xs text-red-500"
                    >
                        Flagged:
                        {{
                            kycFlagOptions
                                .filter((o) =>
                                    verification.flags.includes(o.key),
                                )
                                .map((o) => o.label)
                                .join(', ')
                        }}
                    </p>
                </div>
            </div>

            <section class="rounded-xl border bg-background">
                <div
                    class="flex flex-wrap items-center justify-between gap-2 border-b px-4 py-3"
                >
                    <h2 class="flex items-center gap-2 text-sm font-semibold">
                        <ShieldCheck class="h-4 w-4" /> OCR result vs.
                        registration
                    </h2>
                    <div class="flex flex-wrap gap-3 text-xs">
                        <a
                            v-for="(path, doc) in verification.documents"
                            :key="doc"
                            :href="`/admin/kyc-file?path=${encodeURIComponent(path)}`"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center gap-1 font-medium text-primary hover:underline"
                        >
                            <FileText class="h-3.5 w-3.5" />
                            {{ docLabels[doc] ?? doc }}
                        </a>
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
                                        :class="statusMeta[row.status].cls"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full bg-current"
                                        />
                                        {{ statusMeta[row.status].text }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="border-t px-4 py-2 text-xs text-muted-foreground">
                    Registered address: {{ address }}
                </p>
            </section>
        </div>

        <Teleport to="body">
            <div
                v-if="showReject"
                class="fixed inset-0 z-50 flex items-center justify-center"
            >
                <div
                    class="absolute inset-0 bg-black/50"
                    @click="showReject = false"
                />
                <div
                    class="relative z-10 mx-4 w-full max-w-md rounded-xl bg-background p-6 shadow-xl"
                    role="dialog"
                    aria-label="Reject verification"
                >
                    <div class="mb-4 flex items-start gap-3">
                        <AlertTriangle
                            class="mt-0.5 h-5 w-5 shrink-0 text-red-500"
                        />
                        <div>
                            <h3 class="text-base font-semibold">
                                Reject {{ verification.shop_name }}
                            </h3>
                            <p class="mt-0.5 text-sm text-muted-foreground">
                                Tell the owner what to correct. They are
                                notified by email.
                            </p>
                        </div>
                    </div>
                    <fieldset class="mb-3 rounded-lg border p-3">
                        <legend class="px-1 text-xs font-medium">
                            What needs to be corrected? (optional)
                        </legend>
                        <div
                            class="grid max-h-40 gap-1.5 overflow-y-auto sm:grid-cols-2"
                        >
                            <label
                                v-for="opt in kycFlagOptions"
                                :key="opt.key"
                                class="flex cursor-pointer items-center gap-2 text-sm"
                            >
                                <input
                                    v-model="flags"
                                    type="checkbox"
                                    :value="opt.key"
                                    class="h-4 w-4 rounded border"
                                />
                                {{ opt.label }}
                            </label>
                        </div>
                        <p class="mt-2 text-xs text-muted-foreground">
                            Anything unchecked is kept as the owner submitted
                            it.
                        </p>
                    </fieldset>
                    <textarea
                        v-model="reason"
                        rows="4"
                        aria-label="Rejection reason"
                        placeholder="e.g. BIR expiry date does not match the document."
                        class="w-full resize-none rounded-lg border bg-muted/30 px-3 py-2 text-sm focus:ring-2 focus:ring-red-400 focus:outline-none"
                    />
                    <p v-if="!reason.trim()" class="mt-1 text-xs text-red-400">
                        Reason is required.
                    </p>
                    <div class="mt-4 flex justify-end gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            @click="showReject = false"
                            >Cancel</Button
                        >
                        <Button
                            size="sm"
                            variant="destructive"
                            :disabled="!reason.trim() || busy"
                            @click="reject"
                        >
                            <X class="mr-1.5 h-4 w-4" /> Confirm Reject
                        </Button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>
