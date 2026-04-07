<script setup lang="ts">
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Spinner } from '@/components/ui/spinner'
import AuthBase from '@/layouts/AuthLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref, computed, watch, nextTick, onMounted } from 'vue'
import { Eye, EyeOff, CheckCircle2, XCircle } from 'lucide-vue-next'

/* ---------------- PROPS (from GoogleAuthController) ---------------- */
const props = defineProps<{
    googleUser?: {
        google_id: string
        name: string
        email: string
        avatar: string
    } | null
}>()

/* ---------------- STEP ---------------- */
const step = ref(1)
const TOTAL_STEPS = 3

/* ---------------- FORM ---------------- */
const form = useForm({
    name: '',
    email: '',
    phone: '',

    shop_name: '',
    branch_name: '',
    block_street: '',
    municipality: '',
    barangay: '',
    postal_code: '',

    agree: false,

    password: '',
    password_confirmation: '',

    google_id: '',
})

/* ---------------- PRE-FILL FROM GOOGLE ---------------- */
const isFromGoogle = computed(() => !!props.googleUser)

onMounted(() => {
    if (props.googleUser) {
        form.name = props.googleUser.name
        form.email = props.googleUser.email
        form.google_id = props.googleUser.google_id
    }
})

/* ---------------- FIELD ERRORS (client-side) ---------------- */
const errors = ref<Record<string, string>>({})

/* ---------------- PASSWORD ---------------- */
const showPassword = ref(false)
const showConfirmPassword = ref(false)

const passwordRules = computed(() => [
    { label: 'At least 8 characters', pass: form.password.length >= 8 },
    { label: 'Contains a letter', pass: /[A-Za-z]/.test(form.password) },
    { label: 'Contains a number', pass: /\d/.test(form.password) },
    { label: 'Contains a special character', pass: /[^A-Za-z0-9]/.test(form.password) },
])

const isPasswordValid = computed(() => passwordRules.value.every(r => r.pass))

const passwordsMatch = computed(() =>
    form.password === form.password_confirmation &&
    form.password_confirmation.length > 0
)

const passwordStrength = computed(() => {
    const passed = passwordRules.value.filter(r => r.pass).length
    if (passed <= 1) return { label: 'Weak', color: 'bg-red-500', width: 'w-1/4' }
    if (passed === 2) return { label: 'Fair', color: 'bg-orange-400', width: 'w-2/4' }
    if (passed === 3) return { label: 'Good', color: 'bg-yellow-400', width: 'w-3/4' }
    return { label: 'Strong', color: 'bg-green-500', width: 'w-full' }
})

/* ---------------- MUNICIPALITIES / BARANGAYS ---------------- */
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

const selectedBarangays = computed(() =>
    barangaysByMunicipality[form.municipality] ?? []
)

const autoPostalCode = computed(() => {
    const found = selectedBarangays.value.find(b => b.name === form.barangay)
    return found ? found.postal : ''
})

watch(autoPostalCode, (val) => { form.postal_code = val })

watch(() => form.municipality, () => {
    form.barangay = ''
    form.postal_code = ''
})

/* ---------------- VALIDATION per step ---------------- */
const validateStep1 = (): boolean => {
    const errs: Record<string, string> = {}
    if (form.name.trim().length <= 2)
        errs.name = 'Owner name must be more than 2 characters.'
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email))
        errs.email = 'Please enter a valid email address.'
    if (!/^09\d{9}$/.test(form.phone))
        errs.phone = 'Please enter a valid PH mobile number (e.g. 09XXXXXXXXX).'
    errors.value = errs
    return Object.keys(errs).length === 0
}

const validateStep2 = (): boolean => {
    const errs: Record<string, string> = {}
    if (form.shop_name.trim().length <= 2)
        errs.shop_name = 'Shop name must be more than 2 characters.'
    if (!form.municipality)
        errs.municipality = 'Please select a municipality.'
    if (!form.barangay)
        errs.barangay = 'Please select a barangay.'
    errors.value = errs
    return Object.keys(errs).length === 0
}

const validateStep3 = (): boolean => {
    const errs: Record<string, string> = {}
    if (!isPasswordValid.value)
        errs.password = 'Password does not meet all requirements.'
    if (!passwordsMatch.value)
        errs.password_confirmation = 'Passwords do not match.'
    if (!form.agree)
        errs.agree = 'You must agree to the Terms & Conditions before registering.'
    errors.value = errs
    return Object.keys(errs).length === 0
}

/* ---------------- NAV ---------------- */
const focusFirstError = () => {
    nextTick(() => {
        const el = document.querySelector('[data-error]') as HTMLElement | null
        el?.focus()
        el?.scrollIntoView({ behavior: 'smooth', block: 'center' })
    })
}

const nextStep = () => {
    let valid = false
    if (step.value === 1) valid = validateStep1()
    if (step.value === 2) valid = validateStep2()
    if (valid) {
        errors.value = {}
        step.value++
    } else {
        focusFirstError()
    }
}

const prevStep = () => {
    errors.value = {}
    step.value--
}


/* ---------------- SERVER ERROR → STEP REDIRECT ---------------- */
watch(() => form.errors, (serverErrors) => {
    if (!serverErrors || Object.keys(serverErrors).length === 0) return

    if (serverErrors.name || serverErrors.email || serverErrors.phone) {
        step.value = 1
        focusFirstError()
    } else if (serverErrors.shop_name || serverErrors.municipality || serverErrors.barangay) {
        step.value = 2
        focusFirstError()
    }
}, { deep: true })

/* ---------------- SUBMIT ---------------- */
const submit = () => {
    console.log('agree:', form.agree)
    console.log('isPasswordValid:', isPasswordValid.value)
    console.log('passwordsMatch:', passwordsMatch.value)

    if (!validateStep3()) {
        focusFirstError()
        return
    }

    console.log('About to post, form data:', form.data()) // ← add this
    form.post('/register/shop')
    console.log('Post called') // ← and this
}

/* ---------------- GOOGLE OAuth ---------------- */
const goToGoogle = () => {
    window.location.href = '/auth/google'
}
</script>

<template>
    <AuthBase title="Register Laundry Shop" description="Create your shop account">

        <Head title="Register" />

        <!-- Step progress dots -->
        <div class="flex items-center justify-center gap-2 mb-4">
            <template v-for="s in TOTAL_STEPS" :key="s">
                <div :class="[
                    'h-2 rounded-full transition-all duration-300',
                    s === step ? 'w-8 bg-primary' : s < step ? 'w-4 bg-primary/50' : 'w-4 bg-muted'
                ]" />
            </template>
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">

            <!-- STEP 1: Owner Info -->
            <div v-if="step === 1" class="grid gap-4">
                <h2 class="font-semibold text-center">Step 1 — Owner Info</h2>

                <!-- Google banner if pre-filled -->
                <div v-if="isFromGoogle"
                    class="flex items-center gap-3 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2.5 text-sm text-blue-700">
                    <img :src="props.googleUser!.avatar" class="h-7 w-7 rounded-full object-cover shrink-0" />
                    <span>
                        Signed in with Google as
                        <span class="font-medium">{{ props.googleUser!.email }}</span>.
                        Complete your shop details below.
                    </span>
                </div>

                <div>
                    <Label class="mb-2">Owner Name</Label>
                    <Input v-model="form.name" placeholder="John Doe"
                        :class="errors.name ? 'border-red-500 focus-visible:ring-red-500' : ''"
                        :data-error="errors.name ? '' : undefined" />
                    <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</p>
                </div>

                <div>
                    <Label class="mb-2">Email</Label>
                    <Input v-model="form.email" type="email" placeholder="shop@email.com" :readonly="isFromGoogle"
                        :class="[
                            errors.email || form.errors.email ? 'border-red-500 focus-visible:ring-red-500' : '',
                            isFromGoogle ? 'bg-muted/50 cursor-not-allowed' : ''
                        ]" />
                    <p v-if="isFromGoogle" class="text-xs text-muted-foreground mt-1">
                        Email is locked to your Google account.
                    </p>
                    <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</p>
                    <InputError :message="form.errors.email" />
                </div>

                <div>
                    <Label class="mb-2">Phone</Label>
                    <Input v-model="form.phone" placeholder="09XXXXXXXXX" maxlength="11"
                        @input="form.phone = form.phone.replace(/\D/g, '')"
                        :class="errors.phone ? 'border-red-500 focus-visible:ring-red-500' : ''"
                        :data-error="errors.phone ? '' : undefined" />
                    <p v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone }}</p>
                </div>

                <Button type="button" @click="nextStep" class="w-full">Next</Button>

                <!-- Google OAuth button — only show if NOT already from Google -->
                <template v-if="!isFromGoogle">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <span class="w-full border-t border-border" />
                        </div>
                        <div class="relative flex justify-center text-xs">
                            <span class="bg-background px-2 text-muted-foreground">or continue with</span>
                        </div>
                    </div>

                    <button type="button"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-md border border-border bg-background px-4 py-2 text-sm font-medium text-foreground shadow-sm transition-colors hover:bg-muted"
                        @click="goToGoogle">
                        <svg class="h-4 w-4" viewBox="0 0 24 24">
                            <path fill="#4285F4"
                                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                            <path fill="#34A853"
                                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                            <path fill="#FBBC05"
                                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" />
                            <path fill="#EA4335"
                                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                        </svg>
                        Continue with Google
                    </button>
                </template>
            </div>

            <!-- STEP 2: Shop Info -->
            <div v-if="step === 2" class="grid gap-4">
                <h2 class="font-semibold text-center">Step 2 — Shop Info</h2>

                <div>
                    <Label class="mb-2">Business Name</Label>
                    <Input v-model="form.shop_name" placeholder="Laundry Hub"
                        :class="errors.shop_name ? 'border-red-500 focus-visible:ring-red-500' : ''"
                        :data-error="errors.shop_name ? '' : undefined" />
                    <p v-if="errors.shop_name" class="text-red-500 text-xs mt-1">{{ errors.shop_name }}</p>
                </div>

                <div>
                    <Label class="mb-2">Block / Street</Label>
                    <Input v-model="form.block_street" placeholder="e.g. Block 5, St. 12"
                        :class="errors.block_street ? 'border-red-500 focus-visible:ring-red-500' : ''"
                        :data-error="errors.block_street ? '' : undefined" />
                    <p v-if="errors.block_street" class="text-red-500 text-xs mt-1">{{ errors.block_street }}</p>
                </div>

                <div>
                    <Label class="mb-2">Municipality</Label>
                    <select v-model="form.municipality" class="border p-2 rounded w-full text-sm"
                        :class="errors.municipality ? 'border-red-500' : ''"
                        :data-error="errors.municipality ? '' : undefined">
                        <option value="">Select municipality</option>
                        <option v-for="mun in municipalities" :key="mun" :value="mun">{{ mun }}</option>
                    </select>
                    <p v-if="errors.municipality" class="text-red-500 text-xs mt-1">{{ errors.municipality }}</p>
                </div>

                <div>
                    <Label class="mb-2">Barangay</Label>
                    <select v-model="form.barangay" class="border p-2 rounded w-full text-sm"
                        :class="errors.barangay ? 'border-red-500' : ''" :disabled="!form.municipality"
                        :data-error="errors.barangay ? '' : undefined">
                        <option value="">Select barangay</option>
                        <option v-for="b in selectedBarangays" :key="b.name" :value="b.name">{{ b.name }}</option>
                    </select>
                    <p v-if="errors.barangay" class="text-red-500 text-xs mt-1">{{ errors.barangay }}</p>
                </div>

                <div>
                    <Label class="mb-2">Postal Code</Label>
                    <Input :value="autoPostalCode" placeholder="Auto-filled on barangay select" readonly
                        class="bg-muted/50 cursor-not-allowed" />
                </div>

                <Button type="button" @click="nextStep" class="w-full">Next</Button>
                <Button type="button" variant="outline" @click="prevStep" class="w-full">Back</Button>
            </div>

            <!-- STEP 3: Password + Terms -->
            <div v-if="step === 3" class="grid gap-4">
                <h2 class="font-semibold text-center">Step 3 — Set Password</h2>

                <!-- Password -->
                <div>
                    <Label class="mb-2">Password</Label>
                    <div class="relative">
                        <Input v-model="form.password" :type="showPassword ? 'text' : 'password'" class="pr-10"
                            :class="errors.password ? 'border-red-500 focus-visible:ring-red-500' : ''"
                            :data-error="errors.password ? '' : undefined" placeholder="Create a strong password" />
                        <button type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                            @click="showPassword = !showPassword">
                            <Eye v-if="!showPassword" class="h-4 w-4" />
                            <EyeOff v-else class="h-4 w-4" />
                        </button>
                    </div>

                    <!-- Strength bar -->
                    <div v-if="form.password.length > 0" class="mt-2">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs text-muted-foreground">Password strength</span>
                            <span class="text-xs font-medium" :class="{
                                'text-red-500': passwordStrength.label === 'Weak',
                                'text-orange-400': passwordStrength.label === 'Fair',
                                'text-yellow-500': passwordStrength.label === 'Good',
                                'text-green-500': passwordStrength.label === 'Strong',
                            }">{{ passwordStrength.label }}</span>
                        </div>
                        <div class="h-1.5 w-full bg-muted rounded-full overflow-hidden">
                            <div
                                :class="['h-full rounded-full transition-all duration-300', passwordStrength.color, passwordStrength.width]" />
                        </div>
                    </div>

                    <!-- Requirements checklist -->
                    <ul class="mt-2 space-y-1">
                        <li v-for="rule in passwordRules" :key="rule.label" class="flex items-center gap-1.5 text-xs"
                            :class="rule.pass ? 'text-green-600' : 'text-muted-foreground'">
                            <CheckCircle2 v-if="rule.pass" class="h-3.5 w-3.5 shrink-0 text-green-500" />
                            <XCircle v-else class="h-3.5 w-3.5 shrink-0 text-muted-foreground/50" />
                            {{ rule.label }}
                        </li>
                    </ul>

                    <p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password }}</p>
                    <InputError :message="form.errors.password" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <Label class="mb-2">Confirm Password</Label>
                    <div class="relative">
                        <Input v-model="form.password_confirmation" :type="showConfirmPassword ? 'text' : 'password'"
                            class="pr-10"
                            :class="errors.password_confirmation ? 'border-red-500 focus-visible:ring-red-500' : ''"
                            :data-error="errors.password_confirmation ? '' : undefined"
                            placeholder="Re-enter your password" />
                        <button type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                            @click="showConfirmPassword = !showConfirmPassword">
                            <Eye v-if="!showConfirmPassword" class="h-4 w-4" />
                            <EyeOff v-else class="h-4 w-4" />
                        </button>
                    </div>

                    <!-- Match indicator -->
                    <p v-if="form.password_confirmation.length > 0" class="text-xs mt-1 flex items-center gap-1"
                        :class="passwordsMatch ? 'text-green-600' : 'text-red-500'">
                        <CheckCircle2 v-if="passwordsMatch" class="h-3.5 w-3.5" />
                        <XCircle v-else class="h-3.5 w-3.5" />
                        {{ passwordsMatch ? 'Passwords match' : 'Passwords do not match' }}
                    </p>
                    <p v-if="errors.password_confirmation" class="text-red-500 text-xs mt-1">{{
                        errors.password_confirmation }}
                    </p>
                </div>

                <!-- Terms & Conditions -->
                <div class="rounded-lg border p-3 transition-colors"
                    :class="errors.agree ? 'border-red-400 bg-red-50' : 'border-muted'"
                    :data-error="errors.agree ? '' : undefined">
                    <label class="flex items-start gap-2 text-sm cursor-pointer">
                        <input type="checkbox" :checked="form.agree"
                            @change="form.agree = ($event.target as HTMLInputElement).checked"
                            class="mt-0.5 accent-primary" />
                        <span>
                            I have read and agree to the
                            <a href="#" class="underline text-primary hover:text-primary/80 font-medium">Terms &amp;
                                Conditions</a>
                            and
                            <a href="#" class="underline text-primary hover:text-primary/80 font-medium">Privacy
                                Policy</a>.
                        </span>
                    </label>
                    <p v-if="errors.agree" class="text-red-500 text-xs mt-1.5 ml-5">{{ errors.agree }}</p>
                </div>

                <Button type="submit" :disabled="form.processing" class="w-full">
                    <Spinner v-if="form.processing" class="mr-2" />
                    Create Account
                </Button>

                <Button type="button" variant="outline" @click="prevStep" class="w-full">Back</Button>
            </div>

            <!-- Login link -->
            <div class="text-center text-sm">
                Already have an account?
                <button type="button" @click="router.visit('/login')"
                    class="underline text-primary hover:text-primary/80">
                    Login
                </button>
            </div>

        </form>
    </AuthBase>
</template>
