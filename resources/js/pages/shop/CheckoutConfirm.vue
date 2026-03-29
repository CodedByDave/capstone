<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    ChevronLeft, ShieldCheck, Clock, Upload, X,
    FileText, CheckCircle2, AlertCircle,
    Phone, MapPin, Tag, Loader2, CreditCard
} from 'lucide-vue-next'

// -------------------- TYPES --------------------
interface Plan { name: string; price: number; moduleNames: string[] }
interface Period { months: number; shortLabel: string; fullLabel: string; discountPct: number }
interface UploadedFile { file: File; preview?: string; name: string; size: string }
interface PsgcItem { code: string; name: string }
interface PaymentMethod { key: string; label: string; description: string; icon: string; tag?: string }

// -------------------- PROPS --------------------
const props = defineProps<{
    planName: string
    vatPct: number
    user: { name: string; email: string; phone?: string }
}>()

// -------------------- PAYMONGO REDIRECT --------------------
const page = usePage()

watch(() => (page.props.flash as any)?.checkout_url, (url) => {
    if (url) {
        window.location.href = url
    }
}, { immediate: true })

// -------------------- PLAN --------------------
const planList: Plan[] = [
    { name: 'Basic', price: 3800, moduleNames: ['HRM', 'Operations'] },
    { name: 'Standard', price: 6300, moduleNames: ['HRM', 'Operations', 'Inventory Management', 'Finance Management'] },
    { name: 'Premium', price: 8000, moduleNames: ['HRM', 'Operations', 'Inventory Management', 'Finance Management', 'Reports & Analytics'] },
]

const selectedPlanName = ref(props.planName)
const plan = computed(() => planList.find(p => p.name === selectedPlanName.value) ?? planList[1])
const showPlanModal = ref(false)

function selectPlan(p: Plan) {
    selectedPlanName.value = p.name
    showPlanModal.value = false
}

// -------------------- PERIODS --------------------
const PERIODS: Period[] = [
    { months: 1,  shortLabel: '1 mo',  fullLabel: '1 month',   discountPct: 0  },
    { months: 12, shortLabel: '12 mo', fullLabel: '12 months', discountPct: 10 },
    { months: 24, shortLabel: '24 mo', fullLabel: '24 months', discountPct: 20 },
    { months: 48, shortLabel: '48 mo', fullLabel: '48 months', discountPct: 30 },
]
const selectedPeriod = ref<Period>(PERIODS[1])

// -------------------- PRICING --------------------
const discountedMonthly = computed(() => plan.value.price * (1 - selectedPeriod.value.discountPct / 100))
const subtotal          = computed(() => discountedMonthly.value * selectedPeriod.value.months)
const vatAmount         = computed(() => subtotal.value * ((Number(props.vatPct) || 0) / 100))
const totalDue          = computed(() => subtotal.value + vatAmount.value)
const savedAmount       = computed(() => plan.value.price * selectedPeriod.value.months - subtotal.value)
const fmt = (n: number) => '₱' + Math.round(n).toLocaleString('en-PH')

// -------------------- PAYMENT METHODS --------------------
const PAYMENT_METHODS: PaymentMethod[] = [
    { key: 'gcash',    label: 'GCash',               description: 'Pay via GCash mobile wallet',      icon: 'https://upload.wikimedia.org/wikipedia/commons/5/52/GCash_logo.svg',                                          tag: 'Most Popular' },
    { key: 'maya',     label: 'Maya',                description: 'Pay via Maya (PayMaya) wallet',    icon: 'https://upload.wikimedia.org/wikipedia/commons/e/e6/Maya_logo.svg' },
    { key: 'card',     label: 'Credit / Debit Card', description: 'Visa, Mastercard, JCB',            icon: 'https://upload.wikimedia.org/wikipedia/commons/9/98/Visa_Inc._logo_%282005%E2%80%932014%29.svg' },
    { key: 'grab_pay', label: 'GrabPay',             description: 'Pay via GrabPay wallet',           icon: 'https://upload.wikimedia.org/wikipedia/commons/f/f6/Grab_Logo.svg' },
    { key: 'dob',      label: 'Online Banking',      description: 'BDO, BPI, UnionBank, Metrobank',   icon: 'https://upload.wikimedia.org/wikipedia/commons/4/49/BDO_Unibank_%28logo%29.svg' },
    { key: 'billease', label: 'BillEase',            description: 'Buy now, pay later',               icon: 'https://logobase.net/wp-content/uploads/2025/08/BillEase-Logo-1.webp',                                       tag: 'Installment' },
]

const selectedPayment = ref('')
const selectedPaymentMethod = computed(() =>
    PAYMENT_METHODS.find(m => m.key === selectedPayment.value) ?? null
)

// -------------------- PSGC --------------------
const BASE        = 'https://psgc.cloud/api'
const CAVITE_CODE = '0402100000'
const cityMunis         = ref<PsgcItem[]>([])
const barangays         = ref<PsgcItem[]>([])
const loadingCityMunis  = ref(false)
const loadingBarangays  = ref(false)
const apiError          = ref('')
const selectedCityMuni  = ref('')
const selectedBarangay  = ref('')

async function fetchJson(url: string) {
    const res = await fetch(url)
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
    return res.json()
}

onMounted(async () => {
    loadingCityMunis.value = true
    apiError.value = ''
    try {
        const data = await fetchJson(`${BASE}/provinces/${CAVITE_CODE}/cities-municipalities`)
        cityMunis.value = (Array.isArray(data) ? data : [])
            .map((m: any) => ({ code: m.code, name: m.name }))
            .sort((a, b) => a.name.localeCompare(b.name))
    } catch (e) {
        apiError.value = 'Failed to load cities. Please refresh the page.'
    } finally {
        loadingCityMunis.value = false
    }
})

// -------------------- CAVITE POSTAL CODES --------------------
const CAVITE_POSTAL: Record<string, string> = {
    'Alfonso': '4123', 'Amadeo': '4119', 'Carmona': '4116',
    'City of Bacoor': '4102', 'City of Cavite': '4100', 'City of Dasmariñas': '4114',
    'City of General Trias': '4107', 'City of Imus': '4103', 'City of Tagaytay': '4120',
    'City of Trece Martires': '4109', 'Gen. Mariano Alvarez': '4117',
    'General Emilio Aguinaldo': '4123', 'Indang': '4122', 'Kawit': '4104',
    'Magallanes': '4113', 'Maragondon': '4112', 'Mendez': '4121',
    'Naic': '4110', 'Noveleta': '4105', 'Rosario': '4106',
    'Silang': '4118', 'Tanza': '4108', 'Ternate': '4111',
}

watch(selectedCityMuni, async (code) => {
    selectedBarangay.value = ''
    barangays.value        = []
    shopForm.value.barangay = ''
    if (!code) return
    loadingBarangays.value = true
    try {
        const data = await fetchJson(`${BASE}/cities-municipalities/${code}/barangays`)
        barangays.value = (Array.isArray(data) ? data : [])
            .map((b: any) => ({ code: b.code, name: b.name }))
            .sort((a: PsgcItem, b: PsgcItem) => a.name.localeCompare(b.name))
    } catch (e) {
        console.error('Failed to load barangays:', e)
    } finally {
        loadingBarangays.value = false
    }
})

watch(selectedCityMuni, (code) => {
    const name = cityMunis.value.find(m => m.code === code)?.name ?? ''
    shopForm.value.municipality = name
    shopForm.value.postal_code  = CAVITE_POSTAL[name] ?? ''
})

watch(selectedBarangay, (code) => {
    shopForm.value.barangay = barangays.value.find(b => b.code === code)?.name ?? ''
})

// -------------------- SHOP FORM --------------------
const shopForm = ref({
    shop_name:    '',
    email:        props.user.email,
    phone:        props.user.phone ?? '',
    block_street: '',
    municipality: '',
    barangay:     '',
    postal_code:  '',
})

// -------------------- KYC --------------------
const kycDocs = ref<Record<string, UploadedFile | null>>({
    bir:      null,
    dti:      null,
    mayors:   null,
    sanitary: null,
})

const kycMeta: Record<string, { label: string; description: string; required: boolean }> = {
    bir:      { label: 'BIR Certificate of Registration', description: 'Form 2303 — Bureau of Internal Revenue', required: true  },
    dti:      { label: 'DTI Business Name Registration',  description: 'Business name certificate from DTI',      required: true  },
    mayors:   { label: "Mayor's Business Permit",         description: 'Valid business permit from your LGU',     required: true  },
    sanitary: { label: 'Sanitary Permit',                 description: 'Health / sanitary certificate',           required: false },
}

const requiredKycComplete = computed(() =>
    Object.entries(kycMeta)
        .filter(([, v]) => v.required)
        .every(([k]) => kycDocs.value[k] !== null)
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
        file,
        name:    file.name,
        size:    formatBytes(file.size),
        preview: file.type.startsWith('image/') ? URL.createObjectURL(file) : undefined,
    }
    ;(e.target as HTMLInputElement).value = ''
}

function removeFile(key: string) {
    if (kycDocs.value[key]?.preview) URL.revokeObjectURL(kycDocs.value[key]!.preview!)
    kycDocs.value[key] = null
}

// -------------------- VALIDATION --------------------
const canProceed = computed(() =>
    requiredKycComplete.value &&
    !!selectedPayment.value &&
    !!shopForm.value.shop_name &&
    !!shopForm.value.phone &&
    !!shopForm.value.block_street &&
    !!shopForm.value.municipality &&
    !!shopForm.value.barangay &&
    !!shopForm.value.postal_code
)

// -------------------- SUBMIT --------------------
const isSubmitting = ref(false)

function proceedToPayment() {
    if (!canProceed.value) return
    isSubmitting.value = true

    const formData = new FormData()

    formData.append('plan_name',      selectedPlanName.value)
    formData.append('billing_months', String(selectedPeriod.value.months))
    formData.append('shop_name',      shopForm.value.shop_name)
    formData.append('email',          shopForm.value.email)
    formData.append('phone',          shopForm.value.phone)
    formData.append('block_street',   shopForm.value.block_street)
    formData.append('municipality',   shopForm.value.municipality)
    formData.append('barangay',       shopForm.value.barangay)
    formData.append('postal_code',    shopForm.value.postal_code)
    formData.append('payment_method', selectedPayment.value)

    Object.entries(kycDocs.value).forEach(([key, fileObj]) => {
        if (fileObj?.file) {
            formData.append(`kyc_${key}`, fileObj.file)
        }
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

    <div class="min-h-screen bg-[#f8f7f4]">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6">

            <!-- Top bar -->
            <div class="mb-8 flex items-center justify-between border-b border-stone-200 pb-5">
                <span class="text-base font-semibold tracking-tight text-stone-800">
                    Laundry<span class="text-blue-600">Hub</span>
                </span>
                <div class="flex items-center gap-2 text-xs">
                    <div
                        class="flex h-6 w-6 items-center justify-center rounded-full bg-stone-200 text-stone-500 font-medium">
                        1</div>
                    <div class="h-px w-6 bg-stone-200" />
                    <div
                        class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-white font-medium">
                        2</div>
                    <div class="h-px w-6 bg-stone-200" />
                    <div
                        class="flex h-6 w-6 items-center justify-center rounded-full bg-stone-200 text-stone-500 font-medium">
                        3</div>
                </div>
            </div>

            <!-- Heading -->
            <div class="mb-8">
                <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Confirm your order</h1>
                <p class="mt-1 text-sm text-stone-500">
                    Logged in as <span class="font-medium text-stone-700">{{ props.user.email }}</span>
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_320px]">

                <!-- ── Left ── -->
                <div class="space-y-5">

                    <!-- Selected plan -->
                    <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                        <p class="mb-3 text-[11px] font-semibold uppercase tracking-widest text-stone-400">Selected Plan
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-stone-800">
                                {{ plan.name }} — {{ selectedPeriod.fullLabel }}
                            </span>
                            <button type="button"
                                class="text-xs font-medium text-blue-600 underline underline-offset-2 hover:text-blue-700"
                                @click="showPlanModal = true">Change</button>
                        </div>
                    </div>

                    <!-- Shop Information -->
                    <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                        <p class="mb-4 text-[11px] font-semibold uppercase tracking-widest text-stone-400">Shop
                            Information</p>

                        <div
                            class="mb-4 flex items-center gap-2 rounded-lg border border-blue-100 bg-blue-50 px-3 py-2">
                            <MapPin class="h-3.5 w-3.5 shrink-0 text-blue-500" />
                            <span class="text-xs text-blue-700">
                                Service area: <span class="font-semibold">Cavite Province</span> only
                            </span>
                        </div>

                        <div v-if="apiError"
                            class="mb-4 flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                            <AlertCircle class="h-3.5 w-3.5 shrink-0" />
                            {{ apiError }}
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

                            <!-- Email (auto-filled, read-only) -->
                            <div>
                                <Label class="mb-1.5 flex items-center gap-1.5 text-xs font-medium text-stone-600">
                                    <ShieldCheck class="h-3.5 w-3.5" /> Email Address
                                </Label>
                                <div
                                    class="flex h-9 w-full rounded-md border border-stone-200 bg-stone-100 px-3 py-2 text-sm text-stone-500 cursor-not-allowed">
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
                                        <MapPin class="h-3.5 w-3.5" /> Block / Street <span
                                            class="text-red-400">*</span>
                                    </Label>
                                    <Input v-model="shopForm.block_street" placeholder="e.g. Block 5, St. 12"
                                        class="border-stone-200 bg-stone-50 focus:bg-white" />
                                </div>
                            </div>

                            <!-- City/Municipality + Barangay -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <Label class="mb-1.5 text-xs font-medium text-stone-600">
                                        City / Municipality <span class="text-red-400">*</span>
                                    </Label>
                                    <div class="relative">
                                        <select v-model="selectedCityMuni"
                                            class="w-full appearance-none rounded-md border border-stone-200 bg-stone-50 px-3 py-2 pr-8 text-sm text-stone-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-50"
                                            :disabled="loadingCityMunis || !!apiError">
                                            <option value="">
                                                {{ loadingCityMunis ? 'Loading cities...' : '— Select City / Municipality —' }}
                                            </option>
                                            <option v-for="m in cityMunis" :key="m.code" :value="m.code">{{ m.name }}
                                            </option>
                                        </select>
                                        <Loader2 v-if="loadingCityMunis"
                                            class="pointer-events-none absolute right-2.5 top-2.5 h-4 w-4 animate-spin text-stone-400" />
                                        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            class="pointer-events-none absolute right-2.5 top-2.5 h-4 w-4 text-stone-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <Label class="mb-1.5 text-xs font-medium text-stone-600">
                                        Barangay <span class="text-red-400">*</span>
                                    </Label>
                                    <div class="relative">
                                        <select v-model="selectedBarangay"
                                            class="w-full appearance-none rounded-md border border-stone-200 bg-stone-50 px-3 py-2 pr-8 text-sm text-stone-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-50"
                                            :disabled="!selectedCityMuni || loadingBarangays">
                                            <option value="">
                                                <template v-if="loadingBarangays">Loading barangays...</template>
                                                <template v-else-if="!selectedCityMuni">Select a city/municipality
                                                    first</template>
                                                <template v-else>— Select Barangay —</template>
                                            </option>
                                            <option v-for="b in barangays" :key="b.code" :value="b.code">{{ b.name }}
                                            </option>
                                        </select>
                                        <Loader2 v-if="loadingBarangays"
                                            class="pointer-events-none absolute right-2.5 top-2.5 h-4 w-4 animate-spin text-stone-400" />
                                        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            class="pointer-events-none absolute right-2.5 top-2.5 h-4 w-4 text-stone-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
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
                            <p class="text-[11px] font-semibold uppercase tracking-widest text-stone-400">Payment Method
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <button v-for="method in PAYMENT_METHODS" :key="method.key" type="button"
                                @click="selectedPayment = method.key"
                                class="relative flex items-center gap-3 rounded-xl border px-4 py-3 text-left transition-all"
                                :class="selectedPayment === method.key
                                    ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500'
                                    : 'border-stone-200 bg-stone-50 hover:border-stone-300 hover:bg-white'">

                                <!-- Selected indicator -->
                                <div class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2 transition-colors"
                                    :class="selectedPayment === method.key
                                        ? 'border-blue-500 bg-blue-500'
                                        : 'border-stone-300'">
                                    <div v-if="selectedPayment === method.key"
                                        class="h-1.5 w-1.5 rounded-full bg-white" />
                                </div>

                                <img :src="method.icon" alt="" class="h-6 w-6 object-contain shrink-0" />

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-stone-800">{{ method.label }}</p>
                                    <p class="text-xs text-stone-400">{{ method.description }}</p>
                                </div>

                                <!-- Tag badge -->
                                <span v-if="method.tag"
                                    class="absolute right-2 top-2 rounded-full bg-blue-100 px-1.5 py-0.5 text-[10px] font-semibold text-blue-600">
                                    {{ method.tag }}
                                </span>
                            </button>
                        </div>

                        <p v-if="!selectedPayment" class="mt-3 flex items-center gap-1.5 text-xs text-amber-600">
                            <AlertCircle class="h-3.5 w-3.5 shrink-0" />
                            Please select a payment method to proceed.
                        </p>
                    </div>

                    <!-- KYC Documents -->
                    <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-start justify-between">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-widest text-stone-400">
                                    Business Verification (KYC)
                                </p>
                                <p class="mt-1 text-xs text-stone-500">
                                    Upload your business documents. Required fields are marked
                                    <span class="font-medium text-red-400">*</span>
                                </p>
                            </div>
                            <span class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
                                :class="requiredKycComplete ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'">
                                <CheckCircle2 v-if="requiredKycComplete" class="h-3.5 w-3.5" />
                                <AlertCircle v-else class="h-3.5 w-3.5" />
                                {{ requiredKycComplete ? 'Complete' : 'Incomplete' }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div v-for="(meta, key) in kycMeta" :key="key" class="rounded-xl border transition-colors"
                                :class="kycDocs[key] ? 'border-emerald-200 bg-emerald-50/40' : 'border-stone-200 bg-stone-50'">

                                <!-- Uploaded -->
                                <div v-if="kycDocs[key]" class="flex items-center gap-3 p-3.5">
                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-emerald-200 bg-white">
                                        <img v-if="kycDocs[key]?.preview" :src="kycDocs[key]?.preview"
                                            class="h-full w-full object-cover" />
                                        <FileText v-else class="h-5 w-5 text-emerald-600" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-stone-800">{{ kycDocs[key]?.name }}
                                        </p>
                                        <p class="text-xs text-stone-400">{{ kycDocs[key]?.size }}</p>
                                    </div>
                                    <CheckCircle2 class="h-4 w-4 shrink-0 text-emerald-500" />
                                    <button type="button"
                                        class="flex h-6 w-6 items-center justify-center rounded-full text-stone-400 transition-colors hover:bg-stone-200 hover:text-stone-600"
                                        @click="removeFile(key)">
                                        <X class="h-3.5 w-3.5" />
                                    </button>
                                </div>

                                <!-- Empty -->
                                <label v-else :for="`kyc-${key}`" class="flex cursor-pointer items-center gap-3 p-3.5">
                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-dashed border-stone-300 bg-white">
                                        <Upload class="h-4 w-4 text-stone-400" />
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-stone-700">
                                            {{ meta.label }}
                                            <span v-if="meta.required" class="ml-0.5 text-red-400">*</span>
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
                            <p class="text-xs text-blue-700">
                                Documents are encrypted and stored securely. Used only for business verification and
                                never shared with third
                                parties.
                            </p>
                        </div>
                    </div>

                </div>

                <!-- ── Right: Summary ── -->
                <div>
                    <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm lg:sticky lg:top-6">
                        <p class="mb-4 text-[11px] font-semibold uppercase tracking-widest text-stone-400">Order Summary
                        </p>

                        <div class="mb-4 flex items-start justify-between">
                            <span class="text-sm font-semibold text-stone-800">{{ plan.name }} plan</span>
                            <span class="text-xs text-stone-400">{{ selectedPeriod.fullLabel }}</span>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between text-stone-600">
                                <span>Monthly price</span>
                                <span class="font-medium text-stone-800">{{ fmt(discountedMonthly) }}</span>
                            </div>
                            <div class="flex justify-between text-stone-600">
                                <span>Duration</span>
                                <span class="font-medium text-stone-800">× {{ selectedPeriod.months }} mo</span>
                            </div>
                            <div class="flex justify-between text-stone-600">
                                <span>Subtotal</span>
                                <span class="font-medium text-stone-800">{{ fmt(subtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-stone-600">
                                <span>VAT ({{ props.vatPct }}%)</span>
                                <span class="font-medium text-stone-800">{{ fmt(vatAmount) }}</span>
                            </div>
                            <div v-if="savedAmount > 0" class="flex justify-between font-medium text-emerald-600">
                                <span>You save</span>
                                <span>− {{ fmt(savedAmount) }}</span>
                            </div>
                        </div>

                        <hr class="my-4 border-stone-100" />

                        <!-- Selected payment method preview -->
                        <div v-if="selectedPayment"
                            class="mb-4 flex items-center gap-2 rounded-lg border border-stone-100 bg-stone-50 px-3 py-2">
                            <span class="text-base">
                                <img v-if="selectedPaymentMethod?.icon" :src="selectedPaymentMethod.icon"
                                    class="h-5 w-auto" alt="payment icon" />
                            </span>

                            <span class="text-xs font-medium text-stone-700">
                                {{ selectedPaymentMethod?.label || 'Select payment' }}
                            </span>
                            <span class="ml-auto text-[10px] text-stone-400">via PayMongo</span>
                        </div>

                        <div class="flex items-baseline justify-between">
                            <span class="text-sm font-medium text-stone-700">Total due today</span>
                            <span class="text-xl font-bold text-stone-900">{{ fmt(totalDue) }}</span>
                        </div>
                        <p class="mt-0.5 text-right text-xs text-stone-400">
                            {{ selectedPeriod.months === 1 ? 'Billed monthly' : `Billed once for
                            ${selectedPeriod.months} months` }}
                        </p>

                        <div v-if="!canProceed"
                            class="mt-4 flex items-center gap-2 rounded-lg bg-amber-50 p-2.5 text-xs text-amber-700">
                            <AlertCircle class="h-3.5 w-3.5 shrink-0" />
                            <span v-if="!requiredKycComplete">Upload all required KYC documents.</span>
                            <span v-else-if="!selectedPayment">Select a payment method.</span>
                            <span v-else>Please fill in all required fields.</span>
                        </div>

                        <Button class="mt-4 w-full" type="button" :disabled="!canProceed || isSubmitting"
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
        <!-- Plan Change Modal -->
        <Teleport to="body">
            <div v-if="showPlanModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm"
                @click.self="showPlanModal = false">
                <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl">

                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="text-base font-semibold text-stone-900">Change Plan</h2>
                        <button type="button"
                            class="flex h-7 w-7 items-center justify-center rounded-full text-stone-400 hover:bg-stone-100"
                            @click="showPlanModal = false">
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <button v-for="p in planList" :key="p.name" type="button" @click="selectPlan(p)"
                            class="relative flex flex-col rounded-xl border p-4 text-left transition-all" :class="selectedPlanName === p.name
                                ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500'
                                : 'border-stone-200 bg-stone-50 hover:border-stone-300 hover:bg-white'">

                            <div class="mb-3 flex items-center justify-between">
                                <span class="text-sm font-semibold text-stone-800">{{ p.name }}</span>
                                <div class="flex h-4 w-4 items-center justify-center rounded-full border-2 transition-colors"
                                    :class="selectedPlanName === p.name
                                        ? 'border-blue-500 bg-blue-500'
                                        : 'border-stone-300'">
                                    <div v-if="selectedPlanName === p.name" class="h-1.5 w-1.5 rounded-full bg-white" />
                                </div>
                            </div>

                            <p class="mb-3 text-lg font-bold text-stone-900">
                                {{ fmt(p.price) }}
                                <span class="text-xs font-normal text-stone-400">/mo</span>
                            </p>

                            <ul class="space-y-1">
                                <li v-for="mod in p.moduleNames" :key="mod"
                                    class="flex items-center gap-1.5 text-xs text-stone-600">
                                    <CheckCircle2 class="h-3 w-3 shrink-0 text-emerald-500" />
                                    {{ mod }}
                                </li>
                            </ul>
                        </button>
                    </div>

                    <p class="mt-4 text-center text-xs text-stone-400">
                        Click a plan to select it. Pricing updates automatically.
                    </p>
                </div>
            </div>
        </Teleport>
    </div>
</template>
