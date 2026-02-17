<script setup lang="ts">
import InputError from '@/components/InputError.vue'
import TextLink from '@/components/TextLink.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Spinner } from '@/components/ui/spinner'
import AuthBase from '@/layouts/AuthLayout.vue'
import { login } from '@/routes'
import { Head, useForm } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import { Eye, EyeOff } from 'lucide-vue-next'

/* ---------------- STEP ---------------- */
const step = ref(1)

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
    postal_code: '',   // ← kept in sync by watchers below

    valid_id: null as File | null,
    agree: false,

    password: '',
    password_confirmation: '',
})

/* ---------------- PASSWORD ---------------- */
const showPassword = ref(false)
const showConfirmPassword = ref(false)

const rules = {
    length:       (v: string) => v.length >= 8,
    alphaNumeric: (v: string) => /[A-Za-z]/.test(v) && /\d/.test(v),
    special:      (v: string) => /[^A-Za-z0-9]/.test(v),
}

const isPasswordValid = computed(() =>
    rules.length(form.password) &&
    rules.alphaNumeric(form.password) &&
    rules.special(form.password)
)

const passwordsMatch = computed(() =>
    form.password === form.password_confirmation &&
    form.password_confirmation.length > 0
)

/* ---------------- MUNICIPALITIES / BARANGAYS ---------------- */
const barangaysByMunicipality: Record<string, Array<{ name: string; postal: string }>> = {
    'Cavite City': [
        { name: 'Barangay 1 (Hen. M. Alvarez)', postal: '4100' },
        { name: 'Barangay 2 (Hen. C. Tirona)',  postal: '4100' },
        { name: 'Barangay 3 (M. Paterno)',       postal: '4100' },
        { name: 'Barangay 4 (M. Roxas)',         postal: '4100' },
    ],
    'Dasmariñas': [
        { name: 'Burol',          postal: '4114' },
        { name: 'Burol II',       postal: '4114' },
        { name: 'Dangcalan',      postal: '4114' },
        { name: 'Langkaan I',     postal: '4114' },
        { name: 'Langkaan II',    postal: '4114' },
        { name: 'Langkaan III',   postal: '4114' },
        { name: 'Langkaan IV',    postal: '4114' },
        { name: 'Malagasang I',   postal: '4114' },
        { name: 'Malagasang II',  postal: '4114' },
        { name: 'Paliparan I',    postal: '4114' },
        { name: 'Paliparan II',   postal: '4114' },
        { name: 'Paliparan III',  postal: '4114' },
        { name: 'Real',           postal: '4114' },
        { name: 'Salawag',        postal: '4114' },
        { name: 'San Agustin',    postal: '4114' },
        { name: 'San Jose',       postal: '4114' },
        { name: 'San Miguel',     postal: '4114' },
        { name: 'Sampaloc',       postal: '4114' },
        { name: 'Santo Niño',     postal: '4114' },
        { name: 'Santo Tomas',    postal: '4114' },
        { name: 'Sapang Malapit', postal: '4114' },
    ],
    'Bacoor': [
        { name: 'Aniban',     postal: '4102' },
        { name: 'Bayanan',    postal: '4102' },
        { name: 'Burgos',     postal: '4102' },
        { name: 'Zapote',     postal: '4102' },
        { name: 'Niog',       postal: '4102' },
        { name: 'Molino I',   postal: '4102' },
        { name: 'Molino II',  postal: '4102' },
        { name: 'Molino III', postal: '4102' },
        { name: 'Molino IV',  postal: '4102' },
    ],
    'Imus': [
        { name: 'Buhay na Tubig', postal: '4103' },
        { name: 'Anabu I',        postal: '4103' },
        { name: 'Anabu II',       postal: '4103' },
        { name: 'Barangka',       postal: '4103' },
        { name: 'Burgos',         postal: '4103' },
        { name: 'Dela Paz',       postal: '4103' },
    ],
    'Trece Martires': [
        { name: 'Centro I',   postal: '4109' },
        { name: 'Centro II',  postal: '4109' },
        { name: 'Buenavista', postal: '4109' },
        { name: 'Palico',     postal: '4109' },
    ],
    'General Trias': [
        { name: 'Pasong Camachile I',  postal: '4107' },
        { name: 'Pasong Camachile II', postal: '4107' },
        { name: 'Navarro',             postal: '4107' },
        { name: 'Manggahan',           postal: '4107' },
    ],
}

const municipalities = Object.keys(barangaysByMunicipality)

const selectedBarangays = computed(() =>
    barangaysByMunicipality[form.municipality] ?? []
)

// ← Must be declared BEFORE the watchers that use it
const autoPostalCode = computed(() => {
    const found = selectedBarangays.value.find(b => b.name === form.barangay)
    return found ? found.postal : ''
})

// Sync computed postal code into form so it actually gets submitted
watch(autoPostalCode, (val) => {
    form.postal_code = val
})

// Reset dependent fields when municipality changes
watch(() => form.municipality, () => {
    form.barangay    = ''
    form.postal_code = ''
})

/* ---------------- VALIDATIONS ---------------- */
const step1Valid = computed(() =>
    form.name.trim().length > 2 &&
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email) &&
    form.phone.length >= 10
)

const step2Valid = computed(() =>
    form.shop_name.trim().length > 2 &&
    form.block_street.trim().length > 5 &&
    form.municipality.trim().length > 0 &&
    form.barangay.trim().length > 0 &&
    autoPostalCode.value.length >= 4
)

const step3Valid = computed(() =>
    form.valid_id !== null
)

const step4Valid = computed(() =>
    isPasswordValid.value &&
    passwordsMatch.value &&
    form.agree === true
)

/* ---------------- NAV ---------------- */
const nextStep = () => step.value++
const prevStep = () => step.value--

/* ---------------- SUBMIT ---------------- */
const submit = () => {
    if (!step4Valid.value) return
    form.post('/register/shop')
}
</script>

<template>
    <AuthBase title="Register Laundry Shop" description="Create your shop account">

        <Head title="Register" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">

            <!-- STEP 1: Owner Info -->
            <div v-if="step === 1" class="grid gap-4">
                <h2 class="font-semibold text-center">Owner Info</h2>

                <div>
                    <Label class="mb-2">Owner Name</Label>
                    <Input v-model="form.name" placeholder="John Doe" />
                </div>

                <div>
                    <Label class="mb-2">Email</Label>
                    <Input v-model="form.email" type="email" placeholder="shop@email.com" />
                </div>

                <div>
                    <Label class="mb-2">Phone</Label>
                    <Input v-model="form.phone" placeholder="09XX-XXX-XXXX" />
                </div>

                <Button type="button" @click="nextStep" :disabled="!step1Valid">Next</Button>
            </div>

            <!-- STEP 2: Shop Info -->
            <div v-if="step === 2" class="grid gap-4">
                <h2 class="font-semibold text-center">Shop Info</h2>

                <div>
                    <Label class="mb-2">Shop Name</Label>
                    <Input v-model="form.shop_name" placeholder="Laundry Hub" />
                </div>

                <div>
                    <Label class="mb-2">Branch Name (optional)</Label>
                    <Input v-model="form.branch_name" placeholder="N/A" />
                </div>

                <div>
                    <Label class="mb-2">Block / Street</Label>
                    <Input v-model="form.block_street" placeholder="e.g. Block 5, St. 12" />
                </div>

                <div>
                    <Label class="mb-2">Municipality</Label>
                    <select v-model="form.municipality" class="border p-2 rounded w-full">
                        <option value="">Select municipality</option>
                        <option v-for="mun in municipalities" :key="mun" :value="mun">{{ mun }}</option>
                    </select>
                </div>

                <div>
                    <Label class="mb-2">Barangay</Label>
                    <select
                        v-model="form.barangay"
                        class="border p-2 rounded w-full"
                        :disabled="!form.municipality"
                    >
                        <option value="">Select barangay</option>
                        <option v-for="b in selectedBarangays" :key="b.name" :value="b.name">
                            {{ b.name }}
                        </option>
                    </select>
                </div>

                <!-- Shows autoPostalCode visually; form.postal_code is synced by watcher -->
                <div>
                    <Label class="mb-2">Postal Code</Label>
                    <Input
                        :value="autoPostalCode"
                        placeholder="Auto-filled on barangay select"
                        readonly
                        class="bg-muted/50 cursor-not-allowed"
                    />
                </div>

                <div class="flex gap-2">
                    <Button type="button" @click="nextStep" :disabled="!step2Valid" class="w-full">Next</Button>
                </div>

                <div class="flex gap-2">
                    <Button type="button" variant="outline" @click="prevStep" class="w-full">Back</Button>
                </div>
            </div>

            <!-- STEP 3: Verification -->
            <div v-if="step === 3" class="grid gap-4">
                <h2 class="font-semibold text-center">Verification</h2>

                <div>
                    <Label class="mb-2">Upload Valid ID</Label>
                    <Input
                        type="file"
                        @change="(e: Event) => form.valid_id = (e.target as HTMLInputElement).files?.[0] ?? null"
                    />
                </div>

                <div class="flex gap-2">
                    <Button type="button" @click="nextStep" :disabled="!step3Valid" class="w-full">Next</Button>
                </div>

                <div class="flex gap-2">
                    <Button type="button" variant="outline" @click="prevStep" class="w-full">Back</Button>
                </div>
            </div>

            <!-- STEP 4: Password -->
            <div v-if="step === 4" class="grid gap-4">
                <h2 class="font-semibold text-center">Set Password</h2>

                <div>
                    <Label class="mb-2">Password</Label>
                    <div class="relative">
                        <Input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            class="pr-10"
                        />
                        <button
                            type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                            @click="showPassword = !showPassword"
                        >
                            <Eye v-if="!showPassword" class="h-4 w-4" />
                            <EyeOff v-else class="h-4 w-4" />
                        </button>
                    </div>
                    <InputError :message="form.errors.password" />
                </div>

                <div>
                    <Label class="mb-2">Confirm Password</Label>
                    <div class="relative">
                        <Input
                            v-model="form.password_confirmation"
                            :type="showConfirmPassword ? 'text' : 'password'"
                            class="pr-10"
                        />
                        <button
                            type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                            @click="showConfirmPassword = !showConfirmPassword"
                        >
                            <Eye v-if="!showConfirmPassword" class="h-4 w-4" />
                            <EyeOff v-else class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <div class="flex gap-2">
                    <Button type="submit" :disabled="!step4Valid || form.processing" class="w-full">
                        <Spinner v-if="form.processing" class="mr-2" />
                        Register
                    </Button>
                </div>

                <div class="flex gap-2">
                    <Button type="button" variant="outline" @click="prevStep" class="w-full">Back</Button>
                </div>
            </div>

            <!-- TERMS (always visible) -->
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" v-model="form.agree" />
                I agree to Terms &amp; Conditions
            </label>
            <p v-if="!form.agree" class="text-red-500 text-sm">You must agree before registering</p>

            <!-- LOGIN -->
            <div class="text-center text-sm">
                Already have an account?
                <TextLink :href="login()" class="underline">Login</TextLink>
            </div>

        </form>
    </AuthBase>
</template>
