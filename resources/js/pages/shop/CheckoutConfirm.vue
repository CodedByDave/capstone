<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    ShieldCheck, Clock, Upload, X,
    FileText, CheckCircle2, AlertCircle,
    Phone, MapPin, Tag, Loader2, CreditCard
} from 'lucide-vue-next'

interface Plan { name: string; price: number; moduleNames: string[] }
interface Period { months: number; shortLabel: string; fullLabel: string; discountPct: number }
interface UploadedFile { file: File; preview?: string; name: string; size: string }
interface PaymentMethod { key: string; label: string; description: string; icon: string; tag?: string }

const props = defineProps<{
    planName: string
    vatPct: number
    billingMonths: number
    user: {
        name: string
        email: string
    }
    shop?: {
        phone?: string
        shop_name?: string
        block_street?: string
        municipality?: string
        barangay?: string
        postal_code?: string
    }
}>()

const page = usePage()
watch(() => (page.props.flash as any)?.checkout_url, (url) => {
    if (url) window.location.href = url
}, { immediate: true })

// ── Plans ──────────────────────────────────────────────────────────────────
const planList: Plan[] = [
    { name: 'Basic', price: 3800, moduleNames: ['HRM', 'Operations'] },
    { name: 'Standard', price: 6300, moduleNames: ['HRM', 'Operations', 'Inventory Management', 'Finance Management'] },
    { name: 'Premium', price: 8000, moduleNames: ['HRM', 'Operations', 'Inventory Management', 'Finance Management', 'Reports & Analytics'] },
]
const selectedPlanName = ref(props.planName)
const plan = computed(() => planList.find(p => p.name === selectedPlanName.value) ?? planList[1])
function selectPlan(p: Plan) { selectedPlanName.value = p.name }

// ── Periods ────────────────────────────────────────────────────────────────
const PERIODS: Period[] = [
    { months: 1, shortLabel: '1 mo', fullLabel: '1 month', discountPct: 0 },
    { months: 12, shortLabel: '12 mo', fullLabel: '12 months', discountPct: 10 },
    { months: 24, shortLabel: '24 mo', fullLabel: '24 months', discountPct: 20 },
    { months: 48, shortLabel: '48 mo', fullLabel: '48 months', discountPct: 30 },
]
const selectedPeriod = ref<Period>(
    PERIODS.find(p => p.months === Number(props.billingMonths)) ?? PERIODS[0]
)

// ── Pricing ────────────────────────────────────────────────────────────────
const discountedMonthly = computed(() => plan.value.price * (1 - selectedPeriod.value.discountPct / 100))
const subtotal = computed(() => discountedMonthly.value * selectedPeriod.value.months)
const vatAmount = computed(() => subtotal.value * ((Number(props.vatPct) || 0) / 100))
const totalDue = computed(() => subtotal.value + vatAmount.value)
const savedAmount = computed(() => plan.value.price * selectedPeriod.value.months - subtotal.value)
const fmt = (n: number) => '₱' + Math.round(n).toLocaleString('en-PH')

// ── Payment methods ────────────────────────────────────────────────────────
const PAYMENT_METHODS: PaymentMethod[] = [
    { key: 'gcash', label: 'GCash', description: 'Pay via GCash mobile wallet', icon: 'https://upload.wikimedia.org/wikipedia/commons/5/52/GCash_logo.svg', tag: 'Most Popular' },
    { key: 'maya', label: 'Maya', description: 'Pay via Maya (PayMaya) wallet', icon: 'https://upload.wikimedia.org/wikipedia/commons/e/e6/Maya_logo.svg' },
    { key: 'card', label: 'Credit / Debit Card', description: 'Visa, Mastercard, JCB', icon: 'https://upload.wikimedia.org/wikipedia/commons/9/98/Visa_Inc._logo_%282005%E2%80%932014%29.svg' },
    { key: 'grab_pay', label: 'GrabPay', description: 'Pay via GrabPay wallet', icon: 'https://upload.wikimedia.org/wikipedia/commons/f/f6/Grab_Logo.svg' },
    { key: 'dob', label: 'Online Banking', description: 'BDO, BPI, UnionBank, Metrobank', icon: 'https://upload.wikimedia.org/wikipedia/commons/4/49/BDO_Unibank_%28logo%29.svg' },
    { key: 'billease', label: 'BillEase', description: 'Buy now, pay later', icon: 'https://logobase.net/wp-content/uploads/2025/08/BillEase-Logo-1.webp', tag: 'Installment' },
]
const selectedPayment = ref('')
const selectedPaymentMethod = computed(() => PAYMENT_METHODS.find(m => m.key === selectedPayment.value) ?? null)

// ── Location Data ──────────────────────────────────────────────────────────
const barangaysByMunicipality: Record<string, Array<{ name: string; postal: string }>> = {
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
}

const municipalities = Object.keys(barangaysByMunicipality)

// ── Normalize municipality from DB (handles encoding issues like ├▒ → ñ) ──
function normalizeMunicipality(raw: string | undefined): string {
    if (!raw) return ''
    // Try exact match first
    if (municipalities.includes(raw)) return raw

    const normalize = (s: string) =>
        s.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim()
    const normalized = normalize(raw)
    return municipalities.find(m => normalize(m) === normalized) ?? ''
}

// Shop form
const shopForm = ref({
    shop_name:    props.shop?.shop_name    ?? '',
    email:        props.user.email,
    phone:        props.shop?.phone        ?? '',
    block_street: props.shop?.block_street ?? '',
    municipality: normalizeMunicipality(props.shop?.municipality),
    barangay:     props.shop?.barangay     ?? '',
    postal_code:  props.shop?.postal_code  ?? '',
})

// ── Derived location state ─────────────────────────────────────────────────
const selectedBarangays = computed(() =>
    barangaysByMunicipality[shopForm.value.municipality] ?? []
)

// Auto-fill postal from barangay selection
watch(() => shopForm.value.barangay, (barangayName) => {
    const found = selectedBarangays.value.find(b => b.name === barangayName)
    if (found) shopForm.value.postal_code = found.postal
})

// Reset barangay and postal when municipality changes (skip on initial load)
const isInitialLoad = ref(true)
watch(() => shopForm.value.municipality, () => {
    if (isInitialLoad.value) return
    shopForm.value.barangay = ''
    shopForm.value.postal_code = ''
})

onMounted(() => {
    setTimeout(() => { isInitialLoad.value = false }, 300)
})

// ── KYC ───────────────────────────────────────────────────────────────────
const kycDocs = ref<Record<string, UploadedFile | null>>({
    bir: null, dti: null, mayors: null, sanitary: null,
})
const kycMeta: Record<string, { label: string; description: string; required: boolean }> = {
    bir: { label: 'BIR Certificate of Registration', description: 'Form 2303 — Bureau of Internal Revenue', required: true },
    dti: { label: 'DTI Business Name Registration', description: 'Business name certificate from DTI', required: true },
    mayors: { label: "Mayor's Business Permit", description: 'Valid business permit from your LGU', required: true },
    sanitary: { label: 'Sanitary Permit', description: 'Health / sanitary certificate', required: false },
}
const requiredKycComplete = computed(() =>
    Object.entries(kycMeta).filter(([, v]) => v.required).every(([k]) => kycDocs.value[k] !== null)
)
function formatBytes(b: number) {
    if (b < 1024) return b + ' B'
    if (b < 1024 * 1024) return (b / 1024).toFixed(1) + ' KB'
    return (b / (1024 * 1024)).toFixed(1) + ' MB'
}
function handleFileUpload(e: Event, key: string) {
    const file = (e.target as HTMLInputElement).files?.[0]
    if (!file) return
    kycDocs.value[key] = {
        file, name: file.name, size: formatBytes(file.size),
        preview: file.type.startsWith('image/') ? URL.createObjectURL(file) : undefined,
    }
    ;(e.target as HTMLInputElement).value = ''
}
function removeFile(key: string) {
    if (kycDocs.value[key]?.preview) URL.revokeObjectURL(kycDocs.value[key]!.preview!)
    kycDocs.value[key] = null
}

// ── Validation ─────────────────────────────────────────────────────────────
const canProceed = computed(() =>
    requiredKycComplete.value &&
    !!selectedPayment.value &&
    !!shopForm.value.shop_name &&
    !!shopForm.value.phone &&
    !!shopForm.value.municipality &&
    !!shopForm.value.barangay &&
    !!shopForm.value.postal_code
)

// ── Submit ─────────────────────────────────────────────────────────────────
const isSubmitting = ref(false)
function proceedToPayment() {
    if (!canProceed.value) return
    isSubmitting.value = true
    const formData = new FormData()
    formData.append('plan_name', selectedPlanName.value)
    formData.append('billing_months', String(selectedPeriod.value.months))
    formData.append('shop_name', shopForm.value.shop_name)
    formData.append('email', shopForm.value.email)
    formData.append('phone', shopForm.value.phone)
    formData.append('block_street', shopForm.value.block_street)
    formData.append('municipality', shopForm.value.municipality)
    formData.append('barangay', shopForm.value.barangay)
    formData.append('postal_code', shopForm.value.postal_code)
    formData.append('payment_method', selectedPayment.value)
    Object.entries(kycDocs.value).forEach(([key, fileObj]) => {
        if (fileObj?.file) formData.append(`kyc_${key}`, fileObj.file)
    })
    router.post('/checkout/process', formData, {
        forceFormData: true,
        onError: () => { isSubmitting.value = false },
        onSuccess: () => { isSubmitting.value = false },
    })
}
</script>

<template>
    <Head :title="`Confirm Order — ${plan.name} Plan`" />

    <div class="min-h-screen">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6">

            <!-- Header -->
            <div class="mb-8 flex items-center justify-between border-b border-stone-200 pb-5">
                <span class="text-base font-semibold tracking-tight text-stone-800">
                    Laundry<span class="text-blue-600">Hub</span>
                </span>
                <div class="flex items-center gap-2 text-xs">
                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-stone-200 text-stone-500 font-medium">1</div>
                    <div class="h-px w-6 bg-stone-200" />
                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-white font-medium">2</div>
                    <div class="h-px w-6 bg-stone-200" />
                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-stone-200 text-stone-500 font-medium">3</div>
                </div>
            </div>

            <div class="mb-8">
                <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Confirm your order</h1>
                <p class="mt-1 text-sm text-stone-500">
                    Logged in as <span class="font-medium text-stone-700">{{ props.user.email }}</span>
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_320px]">

                <div class="space-y-5">

                    <!-- Plan + Period selector -->
                    <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                        <p class="mb-3 text-[11px] font-semibold uppercase tracking-widest text-stone-400">Select Plan</p>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 mb-5">
                            <button v-for="p in planList" :key="p.name" type="button" @click="selectPlan(p)"
                                class="relative flex flex-col rounded-xl border p-4 text-left transition-all"
                                :class="selectedPlanName === p.name ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500' : 'border-stone-200 bg-stone-50 hover:border-stone-300 hover:bg-white'">
                                <div class="mb-3 flex items-center justify-between">
                                    <span class="text-sm font-semibold text-stone-800">{{ p.name }}</span>
                                    <div class="flex h-4 w-4 items-center justify-center rounded-full border-2 transition-colors"
                                        :class="selectedPlanName === p.name ? 'border-blue-500 bg-blue-500' : 'border-stone-300'">
                                        <div v-if="selectedPlanName === p.name" class="h-1.5 w-1.5 rounded-full bg-white" />
                                    </div>
                                </div>
                                <p class="mb-3 text-lg font-bold text-stone-900">
                                    {{ fmt(p.price) }}<span class="text-xs font-normal text-stone-400">/mo</span>
                                </p>
                                <ul class="space-y-1">
                                    <li v-for="mod in p.moduleNames" :key="mod"
                                        class="flex items-center gap-1.5 text-xs text-stone-600">
                                        <CheckCircle2 class="h-3 w-3 shrink-0 text-emerald-500" />{{ mod }}
                                    </li>
                                </ul>
                            </button>
                        </div>

                        <p class="mb-3 text-[11px] font-semibold uppercase tracking-widest text-stone-400">Billing Period</p>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <button v-for="period in PERIODS" :key="period.months" type="button"
                                class="relative flex flex-col items-center rounded-xl border py-4 px-2 text-center transition-all"
                                :class="selectedPeriod.months === period.months ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500' : 'border-stone-200 bg-stone-50 hover:border-stone-300 hover:bg-white'"
                                @click="selectedPeriod = period">
                                <span v-if="period.discountPct > 0"
                                    class="absolute -top-2.5 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-emerald-500 px-2 py-0.5 text-[10px] font-semibold text-white">
                                    Save {{ period.discountPct }}%
                                </span>
                                <span class="text-sm font-semibold"
                                    :class="selectedPeriod.months === period.months ? 'text-blue-600' : 'text-stone-800'">
                                    {{ period.shortLabel }}
                                </span>
                                <span class="mt-1 text-xs text-stone-400">
                                    {{ fmt(Math.round(plan.price * (1 - period.discountPct / 100))) }}/mo
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Shop Information -->
                    <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                        <p class="mb-4 text-[11px] font-semibold uppercase tracking-widest text-stone-400">Shop Information</p>

                        <div class="mb-4 flex items-center gap-2 rounded-lg border border-blue-100 bg-blue-50 px-3 py-2">
                            <MapPin class="h-3.5 w-3.5 shrink-0 text-blue-500" />
                            <span class="text-xs text-blue-700">Service area: <span class="font-semibold">Cavite Province</span> only</span>
                        </div>

                        <div class="grid gap-4">
                            <!-- Business Name -->
                            <div>
                                <Label class="mb-1.5 flex items-center gap-1.5 text-xs font-medium text-stone-600">
                                    <Tag class="h-3.5 w-3.5" /> Business Name <span class="text-red-400">*</span>
                                </Label>
                                <Input v-model="shopForm.shop_name" placeholder="Laundry Express"
                                    class="border-stone-200 bg-stone-50 focus:bg-white" />
                            </div>

                            <!-- Email (read-only) -->
                            <div>
                                <Label class="mb-1.5 flex items-center gap-1.5 text-xs font-medium text-stone-600">
                                    <ShieldCheck class="h-3.5 w-3.5" /> Email Address
                                </Label>
                                <div class="flex h-9 w-full rounded-md border border-stone-200 bg-stone-100 px-3 py-2 text-sm text-stone-500 cursor-not-allowed">
                                    {{ props.user.email }}
                                </div>
                                <p class="mt-1 text-[11px] text-stone-400">Auto-filled from your account</p>
                            </div>

                            <!-- Phone + Block/Street -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <Label class="mb-1.5 flex items-center gap-1.5 text-xs font-medium text-stone-600">
                                        <Phone class="h-3.5 w-3.5" /> Phone Number <span class="text-red-400">*</span>
                                    </Label>
                                    <Input v-model="shopForm.phone" placeholder="09XX-XXX-XXXX"
                                        class="border-stone-200 bg-stone-50 focus:bg-white" />
                                </div>
                                <div>
                                    <Label class="mb-1.5 flex items-center gap-1.5 text-xs font-medium text-stone-600">
                                        <MapPin class="h-3.5 w-3.5" /> Block / Street <span class="text-red-400">*</span>
                                    </Label>
                                    <!-- ✅ FIXED: removed stray {{ props.user.block_street }} text node -->
                                    <Input v-model="shopForm.block_street" placeholder="e.g. Block 5, St. 12"
                                        class="border-stone-200 bg-stone-50 focus:bg-white" />
                                </div>
                            </div>

                            <!-- Municipality + Barangay -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <Label class="mb-1.5 text-xs font-medium text-stone-600">
                                        City / Municipality <span class="text-red-400">*</span>
                                    </Label>
                                    <div class="relative">
                                        <select v-model="shopForm.municipality"
                                            class="w-full appearance-none rounded-md border border-stone-200 bg-stone-50 px-3 py-2 pr-8 text-sm text-stone-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">— Select City / Municipality —</option>
                                            <option v-for="mun in municipalities" :key="mun" :value="mun">{{ mun }}</option>
                                        </select>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            class="pointer-events-none absolute right-2.5 top-2.5 h-4 w-4 text-stone-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <Label class="mb-1.5 text-xs font-medium text-stone-600">
                                        Barangay <span class="text-red-400">*</span>
                                    </Label>
                                    <div class="relative">
                                        <select v-model="shopForm.barangay"
                                            class="w-full appearance-none rounded-md border border-stone-200 bg-stone-50 px-3 py-2 pr-8 text-sm text-stone-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-50"
                                            :disabled="!shopForm.municipality">
                                            <option value="">
                                                {{ !shopForm.municipality ? 'Select a city/municipality first' : '— Select Barangay —' }}
                                            </option>
                                            <option v-for="b in selectedBarangays" :key="b.name" :value="b.name">{{ b.name }}</option>
                                        </select>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            class="pointer-events-none absolute right-2.5 top-2.5 h-4 w-4 text-stone-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Postal Code -->
                            <div class="w-1/3">
                                <Label class="mb-1.5 text-xs font-medium text-stone-600">
                                    Postal Code <span class="text-red-400">*</span>
                                </Label>
                                <Input v-model="shopForm.postal_code" placeholder="4102"
                                    class="border-stone-200 bg-stone-50 focus:bg-white" />
                                <p class="mt-1 text-[11px] text-stone-400">Auto-filled · editable if needed</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-center gap-2">
                            <CreditCard class="h-4 w-4 text-stone-400" />
                            <p class="text-[11px] font-semibold uppercase tracking-widest text-stone-400">Payment Method</p>
                        </div>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <button v-for="method in PAYMENT_METHODS" :key="method.key" type="button"
                                @click="selectedPayment = method.key"
                                class="relative flex items-center gap-3 rounded-xl border px-4 py-3 text-left transition-all"
                                :class="selectedPayment === method.key ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500' : 'border-stone-200 bg-stone-50 hover:border-stone-300 hover:bg-white'">
                                <div class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2 transition-colors"
                                    :class="selectedPayment === method.key ? 'border-blue-500 bg-blue-500' : 'border-stone-300'">
                                    <div v-if="selectedPayment === method.key" class="h-1.5 w-1.5 rounded-full bg-white" />
                                </div>
                                <img :src="method.icon" alt="" class="h-6 w-6 object-contain shrink-0" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-stone-800">{{ method.label }}</p>
                                    <p class="text-xs text-stone-400">{{ method.description }}</p>
                                </div>
                                <span v-if="method.tag"
                                    class="absolute right-2 top-2 rounded-full bg-blue-100 px-1.5 py-0.5 text-[10px] font-semibold text-blue-600">
                                    {{ method.tag }}
                                </span>
                            </button>
                        </div>
                        <p v-if="!selectedPayment" class="mt-3 flex items-center gap-1.5 text-xs text-amber-600">
                            <AlertCircle class="h-3.5 w-3.5 shrink-0" />Please select a payment method to proceed.
                        </p>
                    </div>

                    <!-- KYC Documents -->
                    <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-start justify-between">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-widest text-stone-400">Business Verification (KYC)</p>
                                <p class="mt-1 text-xs text-stone-500">Upload your business documents. Required fields are marked <span class="font-medium text-red-400">*</span></p>
                            </div>
                            <span class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
                                :class="requiredKycComplete ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'">
                                <CheckCircle2 v-if="requiredKycComplete" class="h-3.5 w-3.5" />
                                <AlertCircle v-else class="h-3.5 w-3.5" />
                                {{ requiredKycComplete ? 'Complete' : 'Incomplete' }}
                            </span>
                        </div>
                        <div class="space-y-3">
                            <div v-for="(meta, key) in kycMeta" :key="key"
                                class="rounded-xl border transition-colors"
                                :class="kycDocs[key] ? 'border-emerald-200 bg-emerald-50/40' : 'border-stone-200 bg-stone-50'">
                                <div v-if="kycDocs[key]" class="flex items-center gap-3 p-3.5">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-emerald-200 bg-white">
                                        <img v-if="kycDocs[key]?.preview" :src="kycDocs[key]?.preview" class="h-full w-full object-cover" />
                                        <FileText v-else class="h-5 w-5 text-emerald-600" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-stone-800">{{ kycDocs[key]?.name }}</p>
                                        <p class="text-xs text-stone-400">{{ kycDocs[key]?.size }}</p>
                                    </div>
                                    <CheckCircle2 class="h-4 w-4 shrink-0 text-emerald-500" />
                                    <button type="button"
                                        class="flex h-6 w-6 items-center justify-center rounded-full text-stone-400 transition-colors hover:bg-stone-200 hover:text-stone-600"
                                        @click="removeFile(key)">
                                        <X class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                                <label v-else :for="`kyc-${key}`" class="flex cursor-pointer items-center gap-3 p-3.5">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-dashed border-stone-300 bg-white">
                                        <Upload class="h-4 w-4 text-stone-400" />
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-stone-700">
                                            {{ meta.label }}<span v-if="meta.required" class="ml-0.5 text-red-400">*</span>
                                        </p>
                                        <p class="text-xs text-stone-400">{{ meta.description }}</p>
                                        <p class="mt-0.5 text-xs text-blue-500">Click to upload · PDF, JPG, PNG</p>
                                    </div>
                                    <input :id="`kyc-${key}`" type="file" accept=".pdf,.jpg,.jpeg,.png" class="sr-only"
                                        @change="handleFileUpload($event, key)" />
                                </label>
                            </div>
                        </div>
                        <div class="mt-4 flex items-start gap-2 rounded-lg bg-blue-50 p-3">
                            <ShieldCheck class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" />
                            <p class="text-xs text-blue-700">Documents are encrypted and stored securely. Used only for business verification and never shared with third parties.</p>
                        </div>
                    </div>
                </div>

                <!-- Right: Order Summary -->
                <div>
                    <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm lg:sticky lg:top-6">
                        <p class="mb-4 text-[11px] font-semibold uppercase tracking-widest text-stone-400">Order Summary</p>
                        <div class="mb-4 flex items-start justify-between">
                            <span class="text-sm font-semibold text-stone-800">{{ plan.name }} plan</span>
                            <span class="text-xs text-stone-400">{{ selectedPeriod.fullLabel }}</span>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between text-stone-600">
                                <span>Monthly price</span><span class="font-medium text-stone-800">{{ fmt(discountedMonthly) }}</span>
                            </div>
                            <div class="flex justify-between text-stone-600">
                                <span>Duration</span><span class="font-medium text-stone-800">× {{ selectedPeriod.months }} mo</span>
                            </div>
                            <div class="flex justify-between text-stone-600">
                                <span>Subtotal</span><span class="font-medium text-stone-800">{{ fmt(subtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-stone-600">
                                <span>VAT ({{ props.vatPct }}%)</span><span class="font-medium text-stone-800">{{ fmt(vatAmount) }}</span>
                            </div>
                            <div v-if="savedAmount > 0" class="flex justify-between font-medium text-emerald-600">
                                <span>You save</span><span>− {{ fmt(savedAmount) }}</span>
                            </div>
                        </div>
                        <hr class="my-4 border-stone-100" />
                        <div v-if="selectedPayment"
                            class="mb-4 flex items-center gap-2 rounded-lg border border-stone-100 bg-stone-50 px-3 py-2">
                            <img v-if="selectedPaymentMethod?.icon" :src="selectedPaymentMethod.icon" class="h-5 w-auto" alt="" />
                            <span class="text-xs font-medium text-stone-700">{{ selectedPaymentMethod?.label }}</span>
                            <span class="ml-auto text-[10px] text-stone-400">via PayMongo</span>
                        </div>
                        <div class="flex items-baseline justify-between">
                            <span class="text-sm font-medium text-stone-700">Total due today</span>
                            <span class="text-xl font-bold text-stone-900">{{ fmt(totalDue) }}</span>
                        </div>
                        <p class="mt-0.5 text-right text-xs text-stone-400">
                            {{ selectedPeriod.months === 1 ? 'Billed monthly' : `Billed once for ${selectedPeriod.months} months` }}
                        </p>
                        <div v-if="!canProceed"
                            class="mt-4 flex items-center gap-2 rounded-lg bg-amber-50 p-2.5 text-xs text-amber-700">
                            <AlertCircle class="h-3.5 w-3.5 shrink-0" />
                            <span v-if="!requiredKycComplete">Upload all required KYC documents.</span>
                            <span v-else-if="!selectedPayment">Select a payment method.</span>
                            <span v-else>Please fill in all required fields.</span>
                        </div>
                        <Button class="mt-4 w-full bg-blue-500" type="button" :disabled="!canProceed || isSubmitting"
                            @click="proceedToPayment">
                            <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
                            {{ isSubmitting ? 'Processing...' : 'Proceed to Payment' }}
                        </Button>
                        <div class="mt-4 flex items-center justify-center gap-5">
                            <span class="flex items-center gap-1.5 text-xs text-stone-400">
                                <ShieldCheck class="h-3.5 w-3.5" /> Secure
                            </span>
                            <span class="flex items-center gap-1.5 text-xs text-stone-400">
                                <Clock class="h-3.5 w-3.5" /> Instant activation
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
