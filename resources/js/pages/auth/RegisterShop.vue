<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { Head, useForm } from '@inertiajs/vue3';
import { Check, Eye, EyeOff } from 'lucide-vue-next';

/* ---------------- STATE ---------------- */
const currentStep = ref(1);
const totalSteps = 3;

const showPassword = ref(false);
const showConfirmPassword = ref(false);

/* ---------------- FORM ---------------- */
const form = useForm({
    shop_name: '',
    phone: '',
    address: '',
    business_license: '',
    owner_name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

/* ---------------- CLIENT VALIDATION ---------------- */
const clientErrors = ref({
    phone: '',
    email: '',
});

const phoneRegex = /^[0-9+ ]+$/;
const gmailRegex = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;

/* Phone validation */
watch(() => form.phone, (value) => {
    if (!value) {
        clientErrors.value.phone = '';
        return;
    }

    if (!phoneRegex.test(value)) {
        clientErrors.value.phone = 'Phone number must contain numbers only';
    } else {
        clientErrors.value.phone = '';
    }
});

/* Email validation */
watch(() => form.email, (value) => {
    if (!value) {
        clientErrors.value.email = '';
        return;
    }

    if (!gmailRegex.test(value)) {
        clientErrors.value.email = 'Email must be a valid @gmail.com address';
    } else {
        clientErrors.value.email = '';
    }
});

/* ---------------- STEPS ---------------- */
const steps = [
    { number: 1, title: 'Shop Information' },
    { number: 2, title: 'Owner Information' },
    { number: 3, title: 'Account Security' },
];

/* ---------------- STEP CONTROL ---------------- */
const canProceed = computed(() => {
    if (currentStep.value === 1) {
        return (
            form.shop_name &&
            form.phone &&
            !clientErrors.value.phone &&
            form.address &&
            form.business_license
        );
    }

    if (currentStep.value === 2) {
        return (
            form.owner_name &&
            form.email &&
            !clientErrors.value.email
        );
    }

    if (currentStep.value === 3) {
        return form.password && form.password_confirmation;
    }

    return false;
});

const nextStep = () => {
    if (currentStep.value < totalSteps && canProceed.value) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const submit = () => {
    form.post('/register/shop', {
        onSuccess: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};

/* ---------------- PASSWORD STRENGTH ---------------- */
const passwordStrength = ref('');

watch(() => form.password, (password) => {
    passwordStrength.value = getPasswordStrength(password);
});

function getPasswordStrength(password: string) {
    if (!password) return '';
    const strong = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
    const medium = /^(?=.*[a-zA-Z])(?=.*\d).{6,}$/;

    if (strong.test(password)) return 'Strong';
    if (medium.test(password)) return 'Medium';
    return 'Weak';
}
</script>

<template>
    <Head title="Register Shop" />

    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12 bg-slate-50 dark:bg-[#0f0f0f]">

        <!-- Header -->
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold">Register Your Shop</h2>
            <p class="text-sm text-muted-foreground">
                Join LaundryHub as a laundry shop partner
            </p>
        </div>

        <!-- Step Indicators -->
        <div class="flex gap-4 mb-8">
            <div
                v-for="step in steps"
                :key="step.number"
                class="w-8 h-8 rounded-full flex items-center justify-center border-2"
                :class="[
                    currentStep > step.number
                        ? 'bg-green-500 border-green-500 text-white'
                        : currentStep === step.number
                            ? 'border-primary text-primary'
                            : 'border-muted text-muted-foreground'
                ]"
            >
                <Check v-if="currentStep > step.number" class="w-4 h-4" />
                <span v-else class="text-sm">{{ step.number }}</span>
            </div>
        </div>

        <!-- Form -->
        <div class="w-full max-w-xl space-y-6">

            <!-- STEP 1 -->
            <div v-show="currentStep === 1" class="space-y-4">
                <div class="grid gap-2 w-80 mx-auto">
                    <Label>Shop Name</Label>
                    <Input v-model="form.shop_name" placeholder="Laundry Express"/>
                    <InputError :message="form.errors.shop_name" />
                </div>

                <div class="grid gap-2 w-80 mx-auto">
                    <Label>Phone Number</Label>
                    <Input
                        v-model="form.phone"
                        placeholder="+63 912 345 6789"
                        @input="form.phone = form.phone.replace(/[^0-9+ ]/g, '')"
                    />
                    <InputError :message="clientErrors.phone || form.errors.phone" />
                </div>

                <div class="grid gap-2 w-80 mx-auto">
                    <Label>Shop Address</Label>
                    <Input v-model="form.address" placeholder="123 main street"/>
                    <InputError :message="form.errors.address" />
                </div>

                <div class="grid gap-2 w-80 mx-auto">
                    <Label>Business License</Label>
                    <Input v-model="form.business_license" placeholder="BL-123-456" />
                    <InputError :message="form.errors.business_license" />
                </div>
            </div>

            <!-- STEP 2 -->
            <div v-show="currentStep === 2" class="space-y-4">
                <div class="grid gap-2 w-80 mx-auto">
                    <Label>Owner Name</Label>
                    <Input v-model="form.owner_name" placeholder="John Doe"/>
                    <InputError :message="form.errors.owner_name" />
                </div>

                <div class="grid gap-2 w-80 mx-auto">
                    <Label>Email (Gmail only)</Label>
                    <Input
                        v-model="form.email"
                        placeholder="shop@gmail.com"
                    />
                    <InputError :message="clientErrors.email || form.errors.email" />
                </div>
            </div>

            <!-- STEP 3 -->
            <div v-show="currentStep === 3" class="space-y-4 w-80 mx-auto">

                <div class="relative">
                    <Input
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Password"
                        class="pr-10"
                    />
                    <button
                        type="button"
                        class="absolute inset-y-0 right-3 flex items-center"
                        @click="showPassword = !showPassword"
                    >
                        <Eye v-if="!showPassword" class="w-4 h-4" />
                        <EyeOff v-else class="w-4 h-4" />
                    </button>
                </div>

                <InputError :message="form.errors.password" />

                <p v-if="passwordStrength" class="text-sm"
                    :class="{
                        'text-red-500': passwordStrength === 'Weak',
                        'text-yellow-500': passwordStrength === 'Medium',
                        'text-green-500': passwordStrength === 'Strong'
                    }"
                >
                    Password strength: {{ passwordStrength }}
                </p>

                <div class="relative">
                    <Input
                        v-model="form.password_confirmation"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        placeholder="Confirm password"
                        class="pr-10"
                    />
                    <button
                        type="button"
                        class="absolute inset-y-0 right-3 flex items-center"
                        @click="showConfirmPassword = !showConfirmPassword"
                    >
                        <Eye v-if="!showConfirmPassword" class="w-4 h-4" />
                        <EyeOff v-else class="w-4 h-4" />
                    </button>
                </div>

                <InputError :message="form.errors.password_confirmation" />
            </div>
        </div>

        <!-- Navigation -->
        <div class="flex justify-between mt-6 w-80">
            <Button v-if="currentStep > 1" variant="outline" @click="prevStep">
                Previous
            </Button>
            <Button
                v-if="currentStep < totalSteps"
                @click="nextStep"
                :disabled="!canProceed"
            >
                Next
            </Button>
            <Button
                v-else
                @click="submit"
                :disabled="form.processing"
            >
                <Spinner v-if="form.processing" />
                Register Shop
            </Button>
        </div>

        <div class="text-sm text-muted-foreground mt-4">
            Already have an account?
            <TextLink :href="login()" class="underline">Log in</TextLink>
        </div>
    </div>
</template>
