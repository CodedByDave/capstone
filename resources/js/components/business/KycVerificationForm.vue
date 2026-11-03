<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useKycExpiryScan } from '@/composables/useKycExpiryScan';
import { useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ExternalLink,
    FileText,
    Loader2,
    Lock,
    ShieldCheck,
    Upload,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';

interface PreviousSubmission {
    reason: string | null;
    flags: string[];
    reusable_docs: string[];
    expiry: Record<string, string>;
    confirmed: Record<string, string>;
}

const props = defineProps<{
    user: { name: string; email: string };
    kyc: {
        previous: PreviousSubmission | null;
        registration: {
            shop_name: string;
            block_street: string;
            barangay: string;
            municipality: string;
            postal_code: string;
        };
    };
    agreement: {
        public_id: string;
        title: string;
        version: string;
        content: string;
        accepted: boolean;
        effective_at?: string;
        acceptance?: {
            business_name: string;
            signer_name: string;
            signer_role: string;
            accepted_at: string;
            signature_url?: string | null;
        } | null;
    };
}>();

const DOCS = [
    {
        key: 'bir',
        label: 'BIR Certificate of Registration',
        hint: 'Form 2303 — Bureau of Internal Revenue',
        required: true,
    },
    {
        key: 'dti',
        label: 'DTI Business Name Registration',
        hint: 'Business name certificate from DTI',
        required: true,
    },
    {
        key: 'mayors',
        label: "Mayor's Business Permit",
        hint: 'Valid business permit from your LGU',
        required: true,
    },
    {
        key: 'sanitary',
        label: 'Sanitary Permit',
        hint: 'Optional · health / sanitary certificate',
        required: false,
    },
] as const;

const flagLabels: Record<string, string> = {
    tin: 'TIN',
    tax_type: 'Tax type (VAT / Non-VAT)',
    registered_name: 'Registered name',
    registered_address: 'Registered address',
    bir_expiry_date: 'BIR expiry date',
    dti_expiry_date: 'DTI expiry date',
    mayors_expiry_date: "Mayor's permit expiry date",
    sanitary_expiry_date: 'Sanitary permit expiry date',
    kyc_bir: 'BIR document',
    kyc_dti: 'DTI document',
    kyc_mayors: "Mayor's permit document",
    kyc_sanitary: 'Sanitary permit document',
};

const previous = props.kyc.previous;
const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];

const { scanState, scanResults, scanDocument, resetScan } = useKycExpiryScan();

// Documents kept from a rejected verification, shown as already uploaded.
const reused = reactive<Record<string, boolean>>({});
const fileNames = reactive<Record<string, string>>({});
const confirmedEdited = reactive<Record<string, boolean>>({});

const form = useForm({
    kyc_bir: null as File | null,
    kyc_dti: null as File | null,
    kyc_mayors: null as File | null,
    kyc_sanitary: null as File | null,
    bir_expiry_date: previous?.expiry.bir ?? '',
    dti_expiry_date: previous?.expiry.dti ?? '',
    mayors_expiry_date: previous?.expiry.mayors ?? '',
    sanitary_expiry_date: previous?.expiry.sanitary ?? '',
    kyc_confirmed: {
        tin: previous?.confirmed.tin ?? '',
        tax_type: previous?.confirmed.tax_type ?? '',
        registered_name: previous?.confirmed.registered_name ?? '',
        registered_address: previous?.confirmed.registered_address ?? '',
    } as Record<string, string>,
    agreement_public_id: props.agreement.public_id,
    agreement_accepted: props.agreement.accepted,
});

previous?.reusable_docs.forEach((doc) => {
    reused[doc] = true;
    fileNames[doc] = 'Previously uploaded document (kept)';
});
Object.keys(previous?.confirmed ?? {}).forEach(
    (field) => (confirmedEdited[field] = true),
);

type DocKey = (typeof DOCS)[number]['key'];
const fileField = (key: DocKey) => `kyc_${key}` as const;
const expiryField = (key: DocKey) => `${key}_expiry_date` as const;
const hasDoc = (key: DocKey) => !!form[fileField(key)] || reused[key];

function formatBytes(b: number) {
    if (b < 1024) return `${b} B`;
    if (b < 1048576) return `${(b / 1024).toFixed(1)} KB`;
    return `${(b / 1048576).toFixed(1)} MB`;
}

function onFile(e: Event, key: DocKey) {
    const input = e.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;
    input.value = '';

    form[fileField(key)] = file;
    form[expiryField(key)] = '';
    reused[key] = false;
    fileNames[key] = `${file.name} · ${formatBytes(file.size)}`;

    scanDocument(key, file).then((result) => {
        // Ignore the result if the file was replaced or removed meanwhile.
        if (!result || form[fileField(key)] !== file) return;
        if (result.expiry_date) form[expiryField(key)] = result.expiry_date;
        fillConfirmedFromScan();
    });
}

function removeDoc(key: DocKey) {
    form[fileField(key)] = null;
    form[expiryField(key)] = '';
    reused[key] = false;
    delete fileNames[key];
    resetScan(key);
    fillConfirmedFromScan();
}

// Pre-fill the review form from BIR first, then DTI, without overwriting edits.
function fillConfirmedFromScan() {
    for (const field of [
        'tin',
        'tax_type',
        'registered_name',
        'registered_address',
    ]) {
        if (confirmedEdited[field]) continue;
        const value =
            (form.kyc_bir && scanResults.bir?.[field as 'tin']) ||
            (form.kyc_dti && scanResults.dti?.[field as 'tin']) ||
            '';
        if (value) form.kyc_confirmed[field] = value;
    }
}

// ── Review: what OCR read vs. what was registered ──────────────────────────
const registeredAddress = computed(() =>
    [
        props.kyc.registration.block_street,
        props.kyc.registration.barangay,
        props.kyc.registration.municipality,
        props.kyc.registration.postal_code,
    ]
        .filter(Boolean)
        .join(', '),
);
const reviewVisible = computed(() => hasDoc('bir') || hasDoc('dti'));
const scanning = computed(
    () => scanState.bir === 'scanning' || scanState.dti === 'scanning',
);
const norm = (s: string) =>
    s
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, ' ')
        .trim();
function looksLike(a: string, b: string) {
    const x = norm(a);
    const y = norm(b);
    if (!x || !y) return null;
    if (x.includes(y) || y.includes(x)) return true;
    const tokens = y.split(' ').filter((t) => t.length > 2);
    if (!tokens.length) return null;
    return tokens.filter((t) => x.includes(t)).length / tokens.length >= 0.6;
}
const nameMatch = computed(() =>
    looksLike(
        form.kyc_confirmed.registered_name,
        props.kyc.registration.shop_name,
    ),
);
const addressMatch = computed(() =>
    looksLike(form.kyc_confirmed.registered_address, registeredAddress.value),
);

const docsComplete = computed(() =>
    DOCS.filter((d) => d.required).every(
        (d) => hasDoc(d.key) && !!form[expiryField(d.key)],
    ),
);
const confirmedComplete = computed(
    () =>
        form.kyc_confirmed.tin.trim().length >= 9 &&
        ['vat', 'non_vat'].includes(form.kyc_confirmed.tax_type) &&
        form.kyc_confirmed.registered_name.trim().length >= 2 &&
        form.kyc_confirmed.registered_address.trim().length >= 5,
);
const agreementComplete = computed(() => form.agreement_accepted);
const canSubmit = computed(
    () =>
        docsComplete.value &&
        confirmedComplete.value &&
        agreementComplete.value,
);
const hint = computed(() => {
    if (!docsComplete.value)
        return 'Upload your permits and check their expiry dates.';
    if (!confirmedComplete.value)
        return 'Confirm the TIN, registered name and address.';
    if (!agreementComplete.value) return 'Read and accept the agreement.';
    return '';
});

// ── Agreement PDF ──────────────────────────────────────────────────────────
// The agreement is long, so it is shown as a PDF instead of a scrolling page.
const agreementPdfUrl = ref<string | null>(null);
const agreementPdfLoading = ref(false);
const agreementPdfError = ref('');
const showAgreementPdf = ref(true);
let previewRequest = 0;

async function loadAgreementPdf() {
    const requestId = ++previewRequest;
    agreementPdfLoading.value = true;
    agreementPdfError.value = '';

    const body = new FormData();
    body.append('business_name', props.kyc.registration.shop_name);

    try {
        const response = await fetch('/business-agreement/preview', {
            method: 'POST',
            body,
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN':
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') ?? '',
                Accept: 'application/pdf',
            },
        });
        if (!response.ok) throw new Error('Preview failed');
        const blob = await response.blob();
        if (requestId !== previewRequest) return;
        if (agreementPdfUrl.value) URL.revokeObjectURL(agreementPdfUrl.value);
        agreementPdfUrl.value = URL.createObjectURL(blob);
    } catch {
        if (requestId === previewRequest)
            agreementPdfError.value =
                'The agreement PDF could not be loaded. Please try again.';
    } finally {
        if (requestId === previewRequest) agreementPdfLoading.value = false;
    }
}

onMounted(loadAgreementPdf);
onBeforeUnmount(() => {
    if (agreementPdfUrl.value) URL.revokeObjectURL(agreementPdfUrl.value);
});

function submit() {
    if (!canSubmit.value) return;
    form.transform((data) => ({
        ...data,
        reuse_kyc: Object.keys(reused).filter((k) => reused[k]),
    })).post('/kyc-verification', {
        forceFormData: true,
        preserveScroll: true,
    });
}

const scanNote = (key: DocKey) => {
    const state = scanState[key];
    if (!state || state === 'idle') return null;
    return {
        scanning: 'Reading document…',
        found: 'Detected from your document. Please verify.',
        expired:
            'Detected date has already passed. Check or upload a valid document.',
        not_found: 'Could not read the date. Please enter it.',
    }[state];
};
</script>

<template>
    <div class="mx-auto w-full max-w-3xl">
        <div class="mb-8 text-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Welcome, {{ user.name }}
            </h2>
            <p class="mt-2 text-sm text-muted-foreground">
                First, verify your business. Upload your permits, check what we
                read from them, then submit for review. Your 7-day free trial
                starts automatically once an admin approves.
            </p>
        </div>

        <!-- Rejection feedback -->
        <div
            v-if="previous?.reason"
            class="mb-6 flex gap-3 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-950/30"
            role="alert"
        >
            <AlertTriangle class="mt-0.5 h-5 w-5 shrink-0 text-red-500" />
            <div class="text-sm">
                <p class="font-semibold text-red-700 dark:text-red-400">
                    Your last submission needs corrections
                </p>
                <p class="mt-1 text-red-600 dark:text-red-300">
                    {{ previous.reason }}
                </p>
                <p
                    v-if="previous.flags.length"
                    class="mt-2 text-xs text-red-500"
                >
                    Please correct:
                    <span class="font-medium">{{
                        previous.flags.map((f) => flagLabels[f] ?? f).join(', ')
                    }}</span
                    >. Everything else was kept as you submitted it.
                </p>
            </div>
        </div>

        <form class="space-y-8" @submit.prevent="submit">
            <!-- 1 · Documents -->
            <section>
                <h3 class="flex items-center gap-2 text-base font-semibold">
                    <FileText class="h-4 w-4" /> 1 · Upload your documents
                </h3>
                <p class="mt-1 text-xs text-muted-foreground">
                    PDF, JPG or PNG, up to 5 MB each. We read the expiry date
                    for you.
                </p>

                <ul class="mt-3 divide-y divide-border border-y border-border">
                    <li
                        v-for="doc in DOCS"
                        :key="doc.key"
                        class="flex flex-wrap items-center gap-x-4 gap-y-3 py-4"
                    >
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border"
                            :class="
                                hasDoc(doc.key)
                                    ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-500'
                                    : 'border-border bg-muted text-muted-foreground'
                            "
                        >
                            <FileText class="h-5 w-5" />
                        </span>

                        <div class="min-w-0 flex-1 basis-48">
                            <p class="text-sm font-semibold">
                                {{ doc.label
                                }}<span
                                    v-if="doc.required"
                                    class="ml-0.5 text-red-500"
                                    >*</span
                                >
                            </p>
                            <p class="truncate text-xs text-muted-foreground">
                                {{ fileNames[doc.key] ?? doc.hint }}
                            </p>
                            <p
                                v-if="form.errors[fileField(doc.key)]"
                                class="text-xs text-red-500"
                            >
                                {{ form.errors[fileField(doc.key)] }}
                            </p>
                        </div>

                        <template v-if="hasDoc(doc.key)">
                            <div>
                                <label
                                    :for="`expiry-${doc.key}`"
                                    class="mb-1 block text-xs text-muted-foreground"
                                    >Expiry date{{
                                        doc.required ? '' : ' (optional)'
                                    }}</label
                                >
                                <input
                                    :id="`expiry-${doc.key}`"
                                    v-model="form[expiryField(doc.key)]"
                                    type="date"
                                    :min="tomorrow"
                                    class="h-9 rounded-md border border-input bg-background px-3 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                />
                                <p
                                    v-if="scanNote(doc.key)"
                                    class="mt-1 flex items-center gap-1 text-xs"
                                    :class="
                                        scanState[doc.key] === 'found'
                                            ? 'text-emerald-600'
                                            : scanState[doc.key] === 'scanning'
                                              ? 'text-muted-foreground'
                                              : 'text-amber-600'
                                    "
                                    role="status"
                                >
                                    <Loader2
                                        v-if="scanState[doc.key] === 'scanning'"
                                        class="h-3 w-3 animate-spin"
                                    />
                                    {{ scanNote(doc.key) }}
                                </p>
                                <p
                                    v-if="form.errors[expiryField(doc.key)]"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ form.errors[expiryField(doc.key)] }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3 self-end">
                                <label
                                    :for="`kyc-${doc.key}`"
                                    class="inline-flex h-9 cursor-pointer items-center rounded-md border border-input bg-background px-3 text-sm font-medium hover:bg-accent"
                                    >Replace</label
                                >
                                <button
                                    type="button"
                                    class="text-sm font-medium text-red-500 hover:text-red-400 focus-visible:underline focus-visible:outline-none"
                                    :aria-label="`Remove ${doc.label}`"
                                    @click="removeDoc(doc.key)"
                                >
                                    Remove
                                </button>
                            </div>
                        </template>
                        <label
                            v-else
                            :for="`kyc-${doc.key}`"
                            class="inline-flex h-9 cursor-pointer items-center gap-1.5 rounded-md border border-input bg-background px-3 text-sm font-medium hover:bg-accent"
                        >
                            <Upload class="h-4 w-4" /> Upload
                        </label>

                        <input
                            :id="`kyc-${doc.key}`"
                            type="file"
                            accept=".pdf,.jpg,.jpeg,.png"
                            class="sr-only"
                            @change="onFile($event, doc.key)"
                        />
                    </li>
                </ul>
                <p
                    class="mt-3 flex items-center gap-2 text-xs text-muted-foreground"
                >
                    <Lock class="h-3.5 w-3.5 shrink-0" />
                    Documents are encrypted and used only for business
                    verification. Uploaded files are sent to OCR.space only to
                    read them, and are not shared with anyone else.
                </p>
            </section>

            <!-- 2 · Review -->
            <section v-if="reviewVisible" aria-labelledby="kyc-review-title">
                <h3
                    id="kyc-review-title"
                    class="flex items-center gap-2 text-base font-semibold"
                >
                    <ShieldCheck class="h-4 w-4" /> 2 · Review your business
                    details
                </h3>
                <p class="mt-1 text-xs text-muted-foreground">
                    We read these from your document. Correct anything that is
                    wrong, and compare it with what you registered.
                </p>
                <div class="mt-3 grid gap-4 md:grid-cols-2">
                    <div class="rounded-lg border border-border p-4">
                        <p
                            class="flex items-center gap-2 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            <Loader2
                                v-if="scanning"
                                class="h-3.5 w-3.5 animate-spin"
                            />
                            Read from your document
                        </p>
                        <div class="mt-3 space-y-3">
                            <div>
                                <Label for="kyc-tin">TIN</Label>
                                <Input
                                    id="kyc-tin"
                                    v-model="form.kyc_confirmed.tin"
                                    placeholder="123-456-789-000"
                                    @input="confirmedEdited.tin = true"
                                />
                                <p
                                    v-if="form.errors['kyc_confirmed.tin']"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ form.errors['kyc_confirmed.tin'] }}
                                </p>
                            </div>
                            <div>
                                <Label for="kyc-tax-type"
                                    >Tax type (from BIR certificate)</Label
                                >
                                <select
                                    id="kyc-tax-type"
                                    v-model="form.kyc_confirmed.tax_type"
                                    class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                    @change="confirmedEdited.tax_type = true"
                                >
                                    <option value="" disabled>
                                        Select tax type
                                    </option>
                                    <option value="vat">
                                        VAT-registered (Value Added Tax)
                                    </option>
                                    <option value="non_vat">
                                        Non-VAT (Percentage Tax)
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors['kyc_confirmed.tax_type']"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ form.errors['kyc_confirmed.tax_type'] }}
                                </p>
                            </div>
                            <div>
                                <Label for="kyc-reg-name"
                                    >Registered name</Label
                                >
                                <Input
                                    id="kyc-reg-name"
                                    v-model="form.kyc_confirmed.registered_name"
                                    @input="
                                        confirmedEdited.registered_name = true
                                    "
                                />
                            </div>
                            <div>
                                <Label for="kyc-reg-address"
                                    >Registered address</Label
                                >
                                <Input
                                    id="kyc-reg-address"
                                    v-model="
                                        form.kyc_confirmed.registered_address
                                    "
                                    @input="
                                        confirmedEdited.registered_address = true
                                    "
                                />
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-lg border border-border bg-muted/30 p-4"
                    >
                        <p
                            class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            What you registered
                        </p>
                        <dl class="mt-3 space-y-3 text-sm">
                            <div>
                                <dt class="text-xs text-muted-foreground">
                                    Business name
                                </dt>
                                <dd class="font-medium">
                                    {{ kyc.registration.shop_name || '—' }}
                                </dd>
                                <p
                                    v-if="nameMatch !== null"
                                    class="mt-1 text-xs"
                                    :class="
                                        nameMatch
                                            ? 'text-emerald-600'
                                            : 'text-amber-600'
                                    "
                                >
                                    {{
                                        nameMatch
                                            ? 'Matches the document'
                                            : 'Does not match the document'
                                    }}
                                </p>
                            </div>
                            <div>
                                <dt class="text-xs text-muted-foreground">
                                    Business address
                                </dt>
                                <dd class="font-medium">
                                    {{ registeredAddress || '—' }}
                                </dd>
                                <p
                                    v-if="addressMatch !== null"
                                    class="mt-1 text-xs"
                                    :class="
                                        addressMatch
                                            ? 'text-emerald-600'
                                            : 'text-amber-600'
                                    "
                                >
                                    {{
                                        addressMatch
                                            ? 'Matches the document'
                                            : 'Does not match the document'
                                    }}
                                </p>
                            </div>
                        </dl>
                    </div>
                </div>
            </section>

            <!-- 3 · Agreement -->
            <section>
                <h3 class="flex items-center gap-2 text-base font-semibold">
                    <ShieldCheck class="h-4 w-4" /> 3 · Read and accept the
                    agreement
                </h3>
                <div class="mt-3 space-y-4">
                    <div
                        class="flex flex-wrap items-center gap-x-4 gap-y-3 rounded-lg border border-border p-4"
                    >
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"
                        >
                            <FileText class="h-5 w-5" />
                        </span>
                        <div class="min-w-0 flex-1 basis-56">
                            <p class="text-sm font-semibold">
                                {{ agreement.title }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Version {{ agreement.version }} · PDF
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                :aria-expanded="showAgreementPdf"
                                @click="showAgreementPdf = !showAgreementPdf"
                            >
                                {{ showAgreementPdf ? 'Hide' : 'Read here' }}
                            </Button>
                            <Button
                                as="a"
                                variant="outline"
                                size="sm"
                                :href="agreementPdfUrl ?? undefined"
                                target="_blank"
                                rel="noopener"
                                :class="
                                    agreementPdfUrl
                                        ? ''
                                        : 'pointer-events-none opacity-50'
                                "
                            >
                                Open PDF
                                <ExternalLink class="ml-1.5 h-3.5 w-3.5" />
                            </Button>
                        </div>
                    </div>

                    <div
                        v-if="showAgreementPdf"
                        class="relative h-[70vh] min-h-96 overflow-hidden rounded-lg border border-border bg-muted"
                    >
                        <iframe
                            v-if="agreementPdfUrl"
                            :src="agreementPdfUrl"
                            title="Platform–Business Services Agreement"
                            class="h-full w-full"
                        />
                        <div
                            v-if="agreementPdfLoading"
                            class="absolute inset-0 flex items-center justify-center bg-background/70"
                        >
                            <Loader2
                                class="h-6 w-6 animate-spin text-primary"
                            />
                        </div>
                        <div
                            v-else-if="agreementPdfError"
                            class="flex h-full flex-col items-center justify-center gap-2 text-sm text-red-500"
                        >
                            {{ agreementPdfError }}
                            <Button
                                type="button"
                                size="sm"
                                variant="outline"
                                @click="loadAgreementPdf"
                            >
                                Retry
                            </Button>
                        </div>
                    </div>

                    <div v-if="!agreement.accepted" class="space-y-4">
                        <label
                            class="flex cursor-pointer items-start gap-2 text-sm"
                        >
                            <input
                                v-model="form.agreement_accepted"
                                type="checkbox"
                                class="mt-1 accent-blue-600"
                            />
                            <span
                                >I have read and accept the Platform–Business
                                Services Agreement.</span
                            >
                        </label>
                        <p
                            v-if="form.errors.agreement_accepted"
                            class="text-xs text-red-500"
                        >
                            {{ form.errors.agreement_accepted }}
                        </p>
                    </div>
                </div>
            </section>

            <div>
                <Button
                    type="submit"
                    class="w-full"
                    :disabled="!canSubmit || form.processing"
                >
                    <Loader2
                        v-if="form.processing"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    {{
                        form.processing
                            ? 'Submitting...'
                            : 'Confirm & Submit Verification'
                    }}
                </Button>
                <p
                    v-if="hint"
                    class="mt-3 text-center text-xs text-muted-foreground"
                >
                    {{ hint }}
                </p>
            </div>
        </form>
    </div>
</template>
