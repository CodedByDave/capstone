<script setup lang="ts">
import AgreementSignatureInput from '@/components/business/AgreementSignatureInput.vue';
import PhilippinePhoneInput from '@/components/PhilippinePhoneInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    Check,
    Clock,
    ExternalLink,
    FileText,
    Loader2,
    Lock,
    MapPin,
    ShieldCheck,
    Upload,
} from 'lucide-vue-next';
import {
    computed,
    onBeforeUnmount,
    onMounted,
    reactive,
    ref,
    watch,
} from 'vue';

interface Plan {
    name: string;
    price: number;
    moduleNames: string[];
}
interface Period {
    months: number;
    shortLabel: string;
    fullLabel: string;
    discountPct: number;
}
interface UploadedFile {
    file: File;
    preview?: string;
    name: string;
    size: string;
}
interface Agreement {
    public_id: string;
    title: string;
    version: string;
    content: string;
    effective_at: string;
    accepted: boolean;
    acceptance?: {
        business_name: string;
        signer_name: string;
        signer_role: string;
        accepted_at: string;
        signature_url?: string | null;
    } | null;
}

const props = defineProps<{
    planName: string;
    vatPct: number;
    billingMonths: number;
    user: {
        name: string;
        email: string;
    };
    shop?: {
        phone?: string;
        shop_name?: string;
        block_street?: string;
        municipality?: string;
        barangay?: string;
        postal_code?: string;
    };
    agreement: Agreement;
}>();

const page = usePage();
const signatureImageError = computed(
    () => page.props.errors?.signature_image as string | undefined,
);
watch(
    () => (page.props.flash as any)?.checkout_url,
    (url) => {
        if (url) window.location.href = url;
    },
    { immediate: true },
);

const modLabel = (mod: string, plan: string) =>
    mod === 'Reports & Analytics'
        ? plan === 'Premium'
            ? 'Advanced Reports & Analytics'
            : 'Basic Reports'
        : mod;

// ── Plans ──────────────────────────────────────────────────────────────────
const planList: Plan[] = [
    {
        name: 'Basic',
        price: 999,
        moduleNames: ['HRM', 'Operations', 'Reports & Analytics'],
    },
    {
        name: 'Standard',
        price: 1499,
        moduleNames: [
            'HRM',
            'Operations',
            'Inventory Management',
            'Finance Management',
            'Reports & Analytics',
        ],
    },
    {
        name: 'Premium',
        price: 1999,
        moduleNames: [
            'HRM',
            'Operations',
            'Inventory Management',
            'Finance Management',
            'Reports & Analytics',
        ],
    },
];
const selectedPlanName = ref(props.planName);
const plan = computed(
    () =>
        planList.find((p) => p.name === selectedPlanName.value) ?? planList[1],
);
function selectPlan(p: Plan) {
    selectedPlanName.value = p.name;
}

// ── Periods ────────────────────────────────────────────────────────────────
const PERIODS: Period[] = [
    { months: 1, shortLabel: '1 mo', fullLabel: '1 month', discountPct: 0 },
    {
        months: 12,
        shortLabel: '12 mo',
        fullLabel: '12 months',
        discountPct: 10,
    },
    {
        months: 24,
        shortLabel: '24 mo',
        fullLabel: '24 months',
        discountPct: 20,
    },
    {
        months: 48,
        shortLabel: '48 mo',
        fullLabel: '48 months',
        discountPct: 30,
    },
];
const selectedPeriod = ref<Period>(
    PERIODS.find((p) => p.months === Number(props.billingMonths)) ?? PERIODS[0],
);

// ── Pricing ────────────────────────────────────────────────────────────────
const discountedMonthly = computed(
    () => plan.value.price * (1 - selectedPeriod.value.discountPct / 100),
);
const subtotal = computed(
    () => discountedMonthly.value * selectedPeriod.value.months,
);
const vatAmount = computed(
    () => subtotal.value * ((Number(props.vatPct) || 0) / 100),
);
const totalDue = computed(() => subtotal.value + vatAmount.value);
const savedAmount = computed(
    () => plan.value.price * selectedPeriod.value.months - subtotal.value,
);
const fmt = (n: number) => '₱' + Math.round(n).toLocaleString('en-PH');

// ── Location Data ──────────────────────────────────────────────────────────
const barangaysByMunicipality: Record<
    string,
    Array<{ name: string; postal: string }>
> = {
    'City of Cavite': [
        { name: 'Barangay 1 (Hen. M. Alvarez)', postal: '4100' },
        { name: 'Barangay 2 (Hen. C. Tirona)', postal: '4100' },
        { name: 'Barangay 3 (M. Paterno)', postal: '4100' },
        { name: 'Barangay 4 (M. Roxas)', postal: '4100' },
    ],
    'City of Dasmariñas': [
        { name: 'Burol', postal: '4114' },
        { name: 'Burol II', postal: '4114' },
        { name: 'Dangcalan', postal: '4114' },
        { name: 'Langkaan I', postal: '4114' },
        { name: 'Langkaan II', postal: '4114' },
        { name: 'Langkaan III', postal: '4114' },
        { name: 'Langkaan IV', postal: '4114' },
        { name: 'Malagasang I', postal: '4114' },
        { name: 'Malagasang II', postal: '4114' },
        { name: 'Paliparan I', postal: '4114' },
        { name: 'Paliparan II', postal: '4114' },
        { name: 'Paliparan III', postal: '4114' },
        { name: 'Real', postal: '4114' },
        { name: 'Salawag', postal: '4114' },
        { name: 'San Agustin', postal: '4114' },
        { name: 'San Jose', postal: '4114' },
        { name: 'San Miguel', postal: '4114' },
        { name: 'Sampaloc', postal: '4114' },
        { name: 'Santo Niño', postal: '4114' },
        { name: 'Santo Tomas', postal: '4114' },
        { name: 'Sapang Malapit', postal: '4114' },
    ],
    'City of Bacoor': [
        { name: 'Aniban', postal: '4102' },
        { name: 'Bayanan', postal: '4102' },
        { name: 'Burgos', postal: '4102' },
        { name: 'Zapote', postal: '4102' },
        { name: 'Niog', postal: '4102' },
        { name: 'Molino I', postal: '4102' },
        { name: 'Molino II', postal: '4102' },
        { name: 'Molino III', postal: '4102' },
        { name: 'Molino IV', postal: '4102' },
    ],
    'City of Imus': [
        { name: 'Buhay na Tubig', postal: '4103' },
        { name: 'Anabu I', postal: '4103' },
        { name: 'Anabu II', postal: '4103' },
        { name: 'Barangka', postal: '4103' },
        { name: 'Burgos', postal: '4103' },
        { name: 'Dela Paz', postal: '4103' },
    ],
    'City of Trece Martires': [
        { name: 'Centro I', postal: '4109' },
        { name: 'Centro II', postal: '4109' },
        { name: 'Buenavista', postal: '4109' },
        { name: 'Palico', postal: '4109' },
    ],
    'City of General Trias': [
        { name: 'Pasong Camachile I', postal: '4107' },
        { name: 'Pasong Camachile II', postal: '4107' },
        { name: 'Navarro', postal: '4107' },
        { name: 'Manggahan', postal: '4107' },
    ],
};

const municipalities = Object.keys(barangaysByMunicipality);

// ── Normalize municipality from DB (handles encoding issues like ├▒ → ñ) ──
function normalizeMunicipality(raw: string | undefined): string {
    if (!raw) return '';
    // Try exact match first
    if (municipalities.includes(raw)) return raw;

    const normalize = (s: string) =>
        s
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    const normalized = normalize(raw);
    return municipalities.find((m) => normalize(m) === normalized) ?? '';
}

// Shop form
const shopForm = ref({
    shop_name: props.shop?.shop_name ?? '',
    email: props.user.email,
    phone: props.shop?.phone ?? '',
    block_street: props.shop?.block_street ?? '',
    municipality: normalizeMunicipality(props.shop?.municipality),
    barangay: props.shop?.barangay ?? '',
    postal_code: props.shop?.postal_code ?? '',
});

// ── Derived location state ─────────────────────────────────────────────────
const selectedBarangays = computed(
    () => barangaysByMunicipality[shopForm.value.municipality] ?? [],
);

// Auto-fill postal from barangay selection
watch(
    () => shopForm.value.barangay,
    (barangayName) => {
        const found = selectedBarangays.value.find(
            (b) => b.name === barangayName,
        );
        if (found) shopForm.value.postal_code = found.postal;
    },
);

// Reset barangay and postal when municipality changes (skip on initial load)
const isInitialLoad = ref(true);
watch(
    () => shopForm.value.municipality,
    () => {
        if (isInitialLoad.value) return;
        shopForm.value.barangay = '';
        shopForm.value.postal_code = '';
    },
);

onMounted(() => {
    setTimeout(() => {
        isInitialLoad.value = false;
    }, 300);
});

// ── KYC ───────────────────────────────────────────────────────────────────
const kycDocs = ref<Record<string, UploadedFile | null>>({
    bir: null,
    dti: null,
    mayors: null,
    sanitary: null,
});
const expiry = reactive<Record<string, string>>({
    bir: '',
    dti: '',
    mayors: '',
    sanitary: '',
});
const kycMeta: Record<
    string,
    { label: string; description: string; required: boolean }
> = {
    bir: {
        label: 'BIR permit',
        description: 'Form 2303 — Bureau of Internal Revenue',
        required: true,
    },
    dti: {
        label: 'DTI permit',
        description: 'Business name certificate from DTI',
        required: true,
    },
    mayors: {
        label: "Mayor's permit",
        description: 'Valid business permit from your LGU',
        required: true,
    },
    sanitary: {
        label: 'Sanitary permit',
        description:
            'Optional · health / sanitary certificate · PDF, JPG or PNG',
        required: false,
    },
};
const requiredKeys = Object.keys(kycMeta).filter((k) => kycMeta[k].required);
const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];
const requiredKycComplete = computed(() =>
    Object.entries(kycMeta)
        .filter(([, v]) => v.required)
        .every(([k]) => kycDocs.value[k] !== null),
);

const signerName = ref(
    props.agreement.acceptance?.signer_name ?? props.user.name,
);
const signerRole = ref(props.agreement.acceptance?.signer_role ?? 'Owner');
const signerAuthorityConfirmed = ref(props.agreement.accepted);
const agreementAccepted = ref(props.agreement.accepted);
const signatureImage = ref<File | null>(null);
const signatureMethod = ref<'drawn' | 'uploaded'>('uploaded');
const showAgreementPdf = ref(false);
function formatBytes(b: number) {
    if (b < 1024) return b + ' B';
    if (b < 1024 * 1024) return (b / 1024).toFixed(1) + ' KB';
    return (b / (1024 * 1024)).toFixed(1) + ' MB';
}
function handleFileUpload(e: Event, key: string) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    kycDocs.value[key] = {
        file,
        name: file.name,
        size: formatBytes(file.size),
        preview: file.type.startsWith('image/')
            ? URL.createObjectURL(file)
            : undefined,
    };
    (e.target as HTMLInputElement).value = '';
}
function removeFile(key: string) {
    if (kycDocs.value[key]?.preview)
        URL.revokeObjectURL(kycDocs.value[key]!.preview!);
    kycDocs.value[key] = null;
    expiry[key] = '';
}

// ── Agreement PDF preview ──────────────────────────────────────────────────
// The agreement is shown as a PDF. Uploading the signature image re-renders
// the PDF with the signature placed on the owner's signature line.
const agreementPdfUrl = ref<string | null>(null);
const agreementPdfLoading = ref(false);
const agreementPdfError = ref('');
let previewRequest = 0;
let previewTimer: ReturnType<typeof setTimeout> | undefined;

async function loadAgreementPdf() {
    const requestId = ++previewRequest;
    agreementPdfLoading.value = true;
    agreementPdfError.value = '';

    const body = new FormData();
    body.append('business_name', shopForm.value.shop_name);
    body.append('signer_name', signerName.value);
    body.append('signer_role', signerRole.value);
    if (signatureImage.value)
        body.append('signature_image', signatureImage.value);

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

function scheduleAgreementPdf(delay = 600) {
    clearTimeout(previewTimer);
    previewTimer = setTimeout(loadAgreementPdf, delay);
}

watch(signatureImage, () => scheduleAgreementPdf(0));
watch([() => shopForm.value.shop_name, signerName, signerRole], () =>
    scheduleAgreementPdf(),
);
onMounted(loadAgreementPdf);
onBeforeUnmount(() => {
    clearTimeout(previewTimer);
    if (agreementPdfUrl.value) URL.revokeObjectURL(agreementPdfUrl.value);
});

// ── Validation ─────────────────────────────────────────────────────────────
const uploadedRequiredCount = computed(
    () => requiredKeys.filter((k) => kycDocs.value[k] !== null).length,
);
const shopInfoComplete = computed(
    () =>
        !!shopForm.value.shop_name &&
        !!shopForm.value.phone &&
        !!shopForm.value.municipality &&
        !!shopForm.value.barangay &&
        !!shopForm.value.postal_code,
);
const kycComplete = computed(
    () => requiredKycComplete.value && requiredKeys.every((k) => !!expiry[k]),
);
const agreementComplete = computed(
    () =>
        signerName.value.trim().length >= 2 &&
        signerAuthorityConfirmed.value &&
        agreementAccepted.value &&
        (props.agreement.accepted || signatureImage.value !== null),
);
const canProceed = computed(
    () =>
        shopInfoComplete.value && kycComplete.value && agreementComplete.value,
);

const checklist = computed(() => [
    { label: 'Shop information', done: shopInfoComplete.value },
    { label: 'Business verification', done: kycComplete.value },
    { label: 'Agreement signed', done: agreementComplete.value },
]);
const proceedHint = computed(() => {
    if (!shopInfoComplete.value) return 'Complete your shop information.';
    if (!kycComplete.value)
        return 'Upload your permits and enter their expiry dates.';
    if (!agreementComplete.value)
        return 'Upload your signature and accept the agreement.';
    return '';
});

// ── Submit ─────────────────────────────────────────────────────────────────
const isSubmitting = ref(false);
function proceedToPayment() {
    if (!canProceed.value) return;
    isSubmitting.value = true;
    const formData = new FormData();
    formData.append('plan_name', selectedPlanName.value);
    formData.append('billing_months', String(selectedPeriod.value.months));
    formData.append('shop_name', shopForm.value.shop_name);
    formData.append('email', shopForm.value.email);
    formData.append('phone', shopForm.value.phone);
    formData.append('block_street', shopForm.value.block_street);
    formData.append('municipality', shopForm.value.municipality);
    formData.append('barangay', shopForm.value.barangay);
    formData.append('postal_code', shopForm.value.postal_code);
    Object.entries(kycDocs.value).forEach(([key, fileObj]) => {
        if (fileObj?.file) formData.append(`kyc_${key}`, fileObj.file);
    });
    formData.append('bir_expiry_date', expiry.bir);
    formData.append('mayors_expiry_date', expiry.mayors);
    formData.append('dti_expiry_date', expiry.dti);
    if (expiry.sanitary)
        formData.append('sanitary_expiry_date', expiry.sanitary);
    formData.append('agreement_public_id', props.agreement.public_id);
    formData.append('signer_name', signerName.value);
    formData.append('signer_role', signerRole.value);
    formData.append(
        'signer_authority_confirmed',
        signerAuthorityConfirmed.value ? '1' : '0',
    );
    formData.append('agreement_accepted', agreementAccepted.value ? '1' : '0');
    if (signatureImage.value) {
        formData.append('signature_image', signatureImage.value);
        formData.append('signature_method', signatureMethod.value);
    }
    router.post('/checkout/process', formData, {
        forceFormData: true,
        onError: () => {
            isSubmitting.value = false;
        },
        onSuccess: () => {
            isSubmitting.value = false;
        },
    });
}
</script>

<template>
    <Head :title="`Confirm Order — ${plan.name} Plan`" />

    <div class="min-h-screen bg-background text-foreground">
        <!-- Top bar -->
        <header class="border-b border-border">
            <div
                class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6"
            >
                <span class="text-base font-semibold tracking-tight">
                    Laundry<span class="text-primary">Hub</span>
                </span>
                <ol class="flex items-center gap-3 text-xs">
                    <li class="flex items-center gap-2">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-primary text-primary-foreground"
                        >
                            <Check class="h-3 w-3" />
                        </span>
                        <span class="hidden text-muted-foreground sm:inline"
                            >Account</span
                        >
                    </li>
                    <li class="h-px w-6 bg-border" aria-hidden="true" />
                    <li class="flex items-center gap-2" aria-current="step">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-primary text-[11px] font-semibold text-primary-foreground"
                            >2</span
                        >
                        <span class="hidden font-medium sm:inline"
                            >Confirm order</span
                        >
                    </li>
                    <li class="h-px w-6 bg-border" aria-hidden="true" />
                    <li class="flex items-center gap-2">
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-muted text-[11px] font-semibold text-muted-foreground"
                            >3</span
                        >
                        <span class="hidden text-muted-foreground sm:inline"
                            >Activate</span
                        >
                    </li>
                </ol>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
            <h1 class="text-3xl font-bold tracking-tight">
                Confirm your order
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Logged in as {{ props.user.email }}
            </p>

            <div
                class="mt-8 grid grid-cols-1 gap-10 lg:grid-cols-[minmax(0,1fr)_340px]"
            >
                <!-- Left column: numbered, divider-separated sections -->
                <div class="min-w-0">
                    <!-- 1 · Choose your plan -->
                    <section>
                        <h2
                            class="flex items-center gap-3 text-lg font-semibold"
                        >
                            <span
                                class="flex h-6 w-6 items-center justify-center rounded-full bg-primary/15 text-xs font-semibold text-primary"
                                >1</span
                            >
                            Choose your plan
                        </h2>

                        <div
                            class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3"
                            role="radiogroup"
                            aria-label="Plan"
                        >
                            <button
                                v-for="p in planList"
                                :key="p.name"
                                type="button"
                                role="radio"
                                :aria-checked="selectedPlanName === p.name"
                                class="flex min-h-11 flex-col rounded-xl border p-4 text-left transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="
                                    selectedPlanName === p.name
                                        ? 'border-primary bg-primary/5 ring-1 ring-primary'
                                        : 'border-border bg-card hover:border-primary/40'
                                "
                                @click="selectPlan(p)"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold">{{
                                        p.name
                                    }}</span>
                                    <span
                                        class="flex h-4 w-4 items-center justify-center rounded-full border-2"
                                        :class="
                                            selectedPlanName === p.name
                                                ? 'border-primary'
                                                : 'border-muted-foreground/40'
                                        "
                                    >
                                        <span
                                            v-if="selectedPlanName === p.name"
                                            class="h-2 w-2 rounded-full bg-primary"
                                        />
                                    </span>
                                </div>
                                <p class="mt-3 text-2xl font-bold">
                                    {{ fmt(p.price)
                                    }}<span
                                        class="ml-1 text-xs font-normal text-muted-foreground"
                                        >/mo</span
                                    >
                                </p>
                                <ul class="mt-3 space-y-1.5">
                                    <li
                                        v-for="mod in p.moduleNames"
                                        :key="mod"
                                        class="flex items-start gap-1.5 text-xs text-muted-foreground"
                                    >
                                        <Check
                                            class="mt-0.5 h-3 w-3 shrink-0 text-emerald-500"
                                        />
                                        {{ modLabel(mod, p.name) }}
                                    </li>
                                </ul>
                            </button>
                        </div>
                        <p class="mt-3 text-xs text-muted-foreground">
                            Modules in your plan switch on automatically once
                            your order is confirmed.
                        </p>

                        <p
                            class="mt-6 text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                        >
                            Billing period
                        </p>
                        <div
                            class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-4"
                            role="radiogroup"
                            aria-label="Billing period"
                        >
                            <button
                                v-for="period in PERIODS"
                                :key="period.months"
                                type="button"
                                role="radio"
                                :aria-checked="
                                    selectedPeriod.months === period.months
                                "
                                class="relative flex min-h-11 flex-col items-center rounded-xl border px-2 py-3 text-center transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="
                                    selectedPeriod.months === period.months
                                        ? 'border-primary bg-primary/5 ring-1 ring-primary'
                                        : 'border-border bg-card hover:border-primary/40'
                                "
                                @click="selectedPeriod = period"
                            >
                                <span
                                    v-if="period.discountPct > 0"
                                    class="absolute -top-2.5 right-2 rounded-full bg-emerald-500 px-2 py-0.5 text-[10px] font-semibold whitespace-nowrap text-white"
                                >
                                    Save {{ period.discountPct }}%
                                </span>
                                <span class="text-sm font-semibold">{{
                                    period.shortLabel
                                }}</span>
                                <span
                                    class="mt-0.5 text-xs text-muted-foreground"
                                >
                                    {{
                                        fmt(
                                            plan.price *
                                                (1 - period.discountPct / 100),
                                        )
                                    }}/mo
                                </span>
                            </button>
                        </div>
                    </section>

                    <!-- 2 · Shop information -->
                    <section class="mt-10 border-t border-border pt-8">
                        <h2
                            class="flex items-center gap-3 text-lg font-semibold"
                        >
                            <span
                                class="flex h-6 w-6 items-center justify-center rounded-full bg-primary/15 text-xs font-semibold text-primary"
                                >2</span
                            >
                            Shop information
                        </h2>

                        <div
                            class="mt-4 flex items-center gap-2 rounded-lg border border-primary/20 bg-primary/5 px-3 py-2 text-sm"
                        >
                            <MapPin class="h-4 w-4 text-primary" />
                            <span class="text-muted-foreground"
                                >Service area:</span
                            >
                            <span class="font-semibold"
                                >Cavite Province only</span
                            >
                        </div>

                        <div class="mt-5 grid gap-4">
                            <div>
                                <Label for="shop-name" class="mb-1.5"
                                    >Business name
                                    <span class="text-red-500">*</span></Label
                                >
                                <Input
                                    id="shop-name"
                                    v-model="shopForm.shop_name"
                                    placeholder="Laundry Express"
                                />
                            </div>

                            <div>
                                <Label class="mb-1.5">Email address</Label>
                                <div
                                    class="flex h-9 w-full cursor-not-allowed items-center rounded-md border border-border bg-muted px-3 text-sm text-muted-foreground"
                                >
                                    {{ props.user.email }}
                                </div>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Filled in from your account.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <Label class="mb-1.5"
                                        >Phone number
                                        <span class="text-red-500"
                                            >*</span
                                        ></Label
                                    >
                                    <PhilippinePhoneInput
                                        v-model="shopForm.phone"
                                    />
                                </div>
                                <div>
                                    <Label for="block-street" class="mb-1.5"
                                        >Block / street
                                        <span class="text-red-500"
                                            >*</span
                                        ></Label
                                    >
                                    <Input
                                        id="block-street"
                                        v-model="shopForm.block_street"
                                        placeholder="e.g. Block 5, St. 12"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <Label for="municipality" class="mb-1.5"
                                        >City / municipality
                                        <span class="text-red-500"
                                            >*</span
                                        ></Label
                                    >
                                    <select
                                        id="municipality"
                                        v-model="shopForm.municipality"
                                        class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                    >
                                        <option value="">
                                            Select city / municipality
                                        </option>
                                        <option
                                            v-for="mun in municipalities"
                                            :key="mun"
                                            :value="mun"
                                        >
                                            {{ mun }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <Label for="barangay" class="mb-1.5"
                                        >Barangay
                                        <span class="text-red-500"
                                            >*</span
                                        ></Label
                                    >
                                    <select
                                        id="barangay"
                                        v-model="shopForm.barangay"
                                        :disabled="!shopForm.municipality"
                                        class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <option value="">
                                            {{
                                                shopForm.municipality
                                                    ? 'Select barangay'
                                                    : 'Select a city / municipality first'
                                            }}
                                        </option>
                                        <option
                                            v-for="b in selectedBarangays"
                                            :key="b.name"
                                            :value="b.name"
                                        >
                                            {{ b.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="sm:w-1/3">
                                <Label for="postal-code" class="mb-1.5"
                                    >Postal code
                                    <span class="text-red-500">*</span></Label
                                >
                                <Input
                                    id="postal-code"
                                    v-model="shopForm.postal_code"
                                    placeholder="4102"
                                />
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Auto-filled, editable if needed.
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- 3 · Business verification -->
                    <section class="mt-10 border-t border-border pt-8">
                        <div
                            class="flex flex-wrap items-center justify-between gap-2"
                        >
                            <h2
                                class="flex items-center gap-3 text-lg font-semibold"
                            >
                                <span
                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-primary/15 text-xs font-semibold text-primary"
                                    >3</span
                                >
                                Business verification
                            </h2>
                            <span
                                class="rounded-full px-2.5 py-1 text-xs font-medium"
                                :class="
                                    requiredKycComplete
                                        ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400'
                                        : 'bg-amber-500/15 text-amber-600 dark:text-amber-400'
                                "
                            >
                                {{ uploadedRequiredCount }} of
                                {{ requiredKeys.length }} uploaded
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Upload each permit and enter the expiry date printed
                            on it. Fields marked
                            <span class="text-red-500">*</span> are required.
                        </p>

                        <ul
                            class="mt-4 divide-y divide-border border-y border-border"
                        >
                            <li
                                v-for="(meta, key) in kycMeta"
                                :key="key"
                                class="flex flex-wrap items-center gap-x-4 gap-y-3 py-4"
                            >
                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border"
                                    :class="
                                        kycDocs[key]
                                            ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-500'
                                            : 'border-border bg-muted text-muted-foreground'
                                    "
                                >
                                    <FileText
                                        v-if="kycDocs[key] || meta.required"
                                        class="h-5 w-5"
                                    />
                                    <ShieldCheck v-else class="h-5 w-5" />
                                </span>

                                <div class="min-w-0 flex-1 basis-48">
                                    <p class="text-sm font-semibold">
                                        {{ meta.label
                                        }}<span
                                            v-if="meta.required"
                                            class="ml-0.5 text-red-500"
                                            >*</span
                                        >
                                    </p>
                                    <p
                                        class="truncate text-xs text-muted-foreground"
                                    >
                                        {{
                                            kycDocs[key]
                                                ? `${kycDocs[key]?.name} · ${kycDocs[key]?.size}`
                                                : meta.required
                                                  ? 'PDF, JPG or PNG'
                                                  : meta.description
                                        }}
                                    </p>
                                </div>

                                <template v-if="kycDocs[key]">
                                    <div>
                                        <label
                                            :for="`expiry-${key}`"
                                            class="mb-1 block text-xs text-muted-foreground"
                                            >Expiry date{{
                                                meta.required
                                                    ? ''
                                                    : ' (optional)'
                                            }}</label
                                        >
                                        <input
                                            :id="`expiry-${key}`"
                                            v-model="expiry[key]"
                                            type="date"
                                            :min="tomorrow"
                                            class="h-9 rounded-md border border-input bg-background px-3 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                        />
                                    </div>
                                    <div
                                        class="flex items-center gap-3 self-end"
                                    >
                                        <label
                                            :for="`kyc-${key}`"
                                            class="inline-flex h-9 cursor-pointer items-center rounded-md border border-input bg-background px-3 text-sm font-medium hover:bg-accent"
                                            >Replace</label
                                        >
                                        <button
                                            type="button"
                                            class="text-sm font-medium text-red-500 hover:text-red-400 focus-visible:underline focus-visible:outline-none"
                                            :aria-label="`Remove ${meta.label}`"
                                            @click="removeFile(key)"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </template>
                                <label
                                    v-else
                                    :for="`kyc-${key}`"
                                    class="inline-flex h-9 cursor-pointer items-center gap-1.5 rounded-md border border-input bg-background px-3 text-sm font-medium hover:bg-accent"
                                >
                                    <Upload class="h-4 w-4" /> Upload
                                </label>

                                <input
                                    :id="`kyc-${key}`"
                                    type="file"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    class="sr-only"
                                    @change="handleFileUpload($event, key)"
                                />
                            </li>
                        </ul>

                        <p
                            class="mt-3 flex items-center gap-2 text-xs text-muted-foreground"
                        >
                            <Lock class="h-3.5 w-3.5 shrink-0" />
                            Documents are encrypted and used only for business
                            verification. They are never shared with third
                            parties.
                        </p>
                    </section>

                    <!-- 4 · Sign the agreement -->
                    <section class="mt-10 border-t border-border pt-8">
                        <h2
                            class="flex items-center gap-3 text-lg font-semibold"
                        >
                            <span
                                class="flex h-6 w-6 items-center justify-center rounded-full bg-primary/15 text-xs font-semibold text-primary"
                                >4</span
                            >
                            Sign the agreement
                        </h2>

                        <div
                            class="mt-4 flex flex-wrap items-center gap-3 rounded-xl border border-border bg-card p-4"
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
                                    @click="
                                        showAgreementPdf = !showAgreementPdf
                                    "
                                >
                                    {{
                                        showAgreementPdf ? 'Hide' : 'Read here'
                                    }}
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
                            class="relative mt-3 h-[70vh] min-h-96 overflow-hidden rounded-lg border border-border bg-muted"
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

                        <div
                            v-if="agreement.accepted"
                            class="mt-5 flex items-center gap-2 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600 dark:text-emerald-400"
                        >
                            <Check class="h-4 w-4 shrink-0" />
                            This agreement version has already been signed for
                            your business.
                        </div>

                        <div v-else class="mt-6">
                            <p
                                class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                            >
                                Execute this agreement
                            </p>
                            <p class="mt-1 text-sm">
                                Your printed name and signature are attached to
                                the agreement record (Version
                                {{ agreement.version }}).
                            </p>

                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <div>
                                    <Label for="signer-name" class="mb-1.5"
                                        >Printed full name
                                        <span class="text-red-500"
                                            >*</span
                                        ></Label
                                    >
                                    <Input
                                        id="signer-name"
                                        v-model="signerName"
                                        placeholder="Full legal name"
                                    />
                                </div>
                                <div>
                                    <Label for="signer-role" class="mb-1.5"
                                        >Signing capacity</Label
                                    >
                                    <select
                                        id="signer-role"
                                        v-model="signerRole"
                                        class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                    >
                                        <option>Owner</option>
                                        <option>
                                            Authorized Representative
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4">
                                <AgreementSignatureInput
                                    v-model="signatureImage"
                                    upload-only
                                    label="Owner signature *"
                                    description=""
                                    :error="signatureImageError"
                                    @update:method="signatureMethod = $event"
                                />
                            </div>

                            <div class="mt-5 space-y-3">
                                <label
                                    class="flex cursor-pointer items-start gap-2.5 text-sm"
                                >
                                    <input
                                        v-model="signerAuthorityConfirmed"
                                        type="checkbox"
                                        class="mt-0.5 h-4 w-4 accent-primary"
                                    />
                                    I confirm that I am authorized to bind this
                                    business to this agreement.
                                </label>
                                <label
                                    class="flex cursor-pointer items-start gap-2.5 text-sm"
                                >
                                    <input
                                        v-model="agreementAccepted"
                                        type="checkbox"
                                        class="mt-0.5 h-4 w-4 accent-primary"
                                    />
                                    I have read, signed, and accept the
                                    Platform–Business Services Agreement.
                                </label>
                            </div>
                            <p
                                v-if="page.props.errors?.agreement_accepted"
                                class="mt-2 text-xs text-red-500"
                            >
                                {{ page.props.errors.agreement_accepted }}
                            </p>
                        </div>
                    </section>
                </div>

                <!-- Right column: order summary -->
                <aside>
                    <div
                        class="rounded-2xl border border-border bg-card p-5 lg:sticky lg:top-6"
                    >
                        <p
                            class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                        >
                            Order summary
                        </p>
                        <div class="mt-3 flex items-baseline justify-between">
                            <span class="text-base font-semibold"
                                >{{ plan.name }} plan</span
                            >
                            <span class="text-xs text-muted-foreground">{{
                                selectedPeriod.months === 1
                                    ? '1 month'
                                    : selectedPeriod.fullLabel
                            }}</span>
                        </div>

                        <dl class="mt-4 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">
                                    Price per month
                                </dt>
                                <dd>{{ fmt(discountedMonthly) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Duration</dt>
                                <dd>× {{ selectedPeriod.months }} mo</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Subtotal</dt>
                                <dd>{{ fmt(subtotal) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">
                                    VAT ({{ props.vatPct }}%)
                                </dt>
                                <dd>{{ fmt(vatAmount) }}</dd>
                            </div>
                            <div
                                v-if="savedAmount > 0"
                                class="flex justify-between font-medium text-emerald-500"
                            >
                                <dt>You save</dt>
                                <dd>− {{ fmt(savedAmount) }}</dd>
                            </div>
                        </dl>

                        <div
                            class="mt-4 flex items-end justify-between border-t border-border pt-4"
                        >
                            <div>
                                <p class="text-sm font-semibold">
                                    Total due today
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{
                                        selectedPeriod.months === 1
                                            ? 'Billed monthly'
                                            : `Billed once for ${selectedPeriod.months} months`
                                    }}
                                </p>
                            </div>
                            <span class="text-3xl font-bold text-primary">{{
                                fmt(totalDue)
                            }}</span>
                        </div>

                        <div class="mt-5 border-t border-border pt-4">
                            <p
                                class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                            >
                                Before you proceed
                            </p>
                            <ul class="mt-3 space-y-2.5">
                                <li
                                    v-for="item in checklist"
                                    :key="item.label"
                                    class="flex items-center gap-2.5 text-sm"
                                    :class="
                                        item.done ? '' : 'text-muted-foreground'
                                    "
                                >
                                    <span
                                        class="flex h-5 w-5 items-center justify-center rounded-full border"
                                        :class="
                                            item.done
                                                ? 'border-primary bg-primary text-primary-foreground'
                                                : 'border-muted-foreground/40'
                                        "
                                    >
                                        <Check
                                            v-if="item.done"
                                            class="h-3 w-3"
                                        />
                                    </span>
                                    {{ item.label }}
                                    <span class="sr-only">{{
                                        item.done ? '(done)' : '(not done)'
                                    }}</span>
                                </li>
                            </ul>
                        </div>

                        <Button
                            class="mt-5 h-11 w-full"
                            type="button"
                            :disabled="!canProceed || isSubmitting"
                            @click="proceedToPayment"
                        >
                            <Loader2
                                v-if="isSubmitting"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            {{
                                isSubmitting
                                    ? 'Processing...'
                                    : 'Proceed to order'
                            }}
                        </Button>
                        <p
                            v-if="proceedHint"
                            class="mt-3 text-center text-xs text-muted-foreground"
                        >
                            {{ proceedHint }}
                        </p>

                        <div
                            class="mt-4 flex items-center justify-center gap-5 text-xs text-muted-foreground"
                        >
                            <span class="flex items-center gap-1.5">
                                <ShieldCheck class="h-3.5 w-3.5" /> Secure
                            </span>
                            <span class="flex items-center gap-1.5">
                                <Clock class="h-3.5 w-3.5" /> Instant activation
                            </span>
                        </div>
                    </div>
                </aside>
            </div>
        </main>
    </div>
</template>
