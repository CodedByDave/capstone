<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Eye, EyeOff, CheckCircle2, XCircle } from 'lucide-vue-next';

interface GoogleUser {
    google_id: string
    name: string
    email: string
    avatar: string | null
}

const props = defineProps<{ googleUser?: GoogleUser | null }>()

const form = useForm({
    name: props.googleUser?.name ?? '',
    email: props.googleUser?.email ?? '',
    password: '',
    password_confirmation: '',
    google_id: props.googleUser?.google_id ?? '',
})

const showPassword = ref(false)
const showPasswordConfirmation = ref(false)
const isGoogleUser = computed(() => !!form.google_id)

const rules = {
    length: (v: string) => v.length >= 8,
    alphaNumeric: (v: string) => /[A-Za-z]/.test(v) && /\d/.test(v),
    special: (v: string) => /[^A-Za-z0-9]/.test(v),
}

const passwordRules = computed(() => [
    { label: 'At least 8 characters', pass: rules.length(form.password) },
    { label: 'Contains a letter and number', pass: rules.alphaNumeric(form.password) },
    { label: 'Contains a special character', pass: rules.special(form.password) },
])

const passwordStrength = computed(() => {
    const passed = passwordRules.value.filter(r => r.pass).length
    if (passed <= 1) return { label: 'Weak', color: 'bg-red-500', width: 'w-1/3' }
    if (passed === 2) return { label: 'Good', color: 'bg-yellow-400', width: 'w-2/3' }
    return { label: 'Strong', color: 'bg-green-500', width: 'w-full' }
})

const passwordsMatch = computed(() =>
    form.password === form.password_confirmation &&
    form.password_confirmation.length > 0
)

function submit() {
    form.post('/register')
}

function goToGoogle() {
    window.location.href = '/auth/google?register_as=user'
}
</script>

<template>
    <AuthBase title="Create an account" description="Enter your details below to create your account">

        <Head title="Create Account" />

        <!-- Google pre-fill banner -->
        <div v-if="isGoogleUser"
            class="mb-5 flex items-center gap-3 rounded-lg border border-blue-100 bg-blue-50 px-4 py-3">
            <img v-if="googleUser?.avatar" :src="googleUser.avatar" class="h-8 w-8 rounded-full" />
            <div class="min-w-0">
                <p class="text-sm font-medium text-blue-800 truncate">{{ googleUser?.name }}</p>
                <p class="text-xs text-blue-600 truncate">Signed in with Google</p>
            </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-5">

            <!-- Name -->
            <div class="space-y-1.5">
                <Label for="name">Name</Label>
                <Input id="name" v-model="form.name" type="text" required autocomplete="name" placeholder="Full name"
                    :class="form.errors.name ? 'border-red-400' : ''" />
                <InputError :message="form.errors.name" />
            </div>

            <!-- Email -->
            <div class="space-y-1.5">
                <Label for="email">Email address</Label>
                <Input id="email" v-model="form.email" type="email" required autocomplete="email"
                    placeholder="email@example.com" :readonly="isGoogleUser"
                    :class="[form.errors.email ? 'border-red-400' : '', isGoogleUser ? 'bg-gray-50 cursor-not-allowed' : '']" />
                <p v-if="isGoogleUser" class="text-xs text-gray-400">Email is set by your Google account</p>
                <InputError :message="form.errors.email" />
            </div>

            <!-- Password fields — hidden for Google users -->
            <template v-if="!isGoogleUser">

                <!-- Password -->
                <div class="space-y-1.5">
                    <Label for="password">Password</Label>
                    <div class="relative">
                        <Input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'" required
                            autocomplete="new-password" placeholder="Password" class="pr-10"
                            :class="form.errors.password ? 'border-red-400' : ''" />
                        <button type="button"
                            class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600"
                            @click="showPassword = !showPassword">
                            <EyeOff v-if="showPassword" class="h-4 w-4" />
                            <Eye v-else class="h-4 w-4" />
                        </button>
                    </div>

                    <!-- Strength bar -->
                    <div v-if="form.password.length > 0" class="mt-2">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs text-gray-400">Password strength</span>
                            <span class="text-xs font-medium" :class="{
                                'text-red-500': passwordStrength.label === 'Weak',
                                'text-yellow-500': passwordStrength.label === 'Good',
                                'text-green-500': passwordStrength.label === 'Strong',
                            }">{{ passwordStrength.label }}</span>
                        </div>
                        <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                            <div
                                :class="['h-full rounded-full transition-all duration-300', passwordStrength.color, passwordStrength.width]" />
                        </div>
                    </div>

                    <!-- Requirements checklist -->
                    <ul v-if="form.password.length > 0" class="mt-2 space-y-1">
                        <li v-for="rule in passwordRules" :key="rule.label" class="flex items-center gap-1.5 text-xs"
                            :class="rule.pass ? 'text-green-600' : 'text-gray-400'">
                            <CheckCircle2 v-if="rule.pass" class="h-3.5 w-3.5 shrink-0 text-green-500" />
                            <XCircle v-else class="h-3.5 w-3.5 shrink-0 text-gray-300" />
                            {{ rule.label }}
                        </li>
                    </ul>

                    <InputError :message="form.errors.password" />
                </div>

                <!-- Confirm Password -->
                <div class="space-y-1.5">
                    <Label for="password_confirmation">Confirm password</Label>
                    <div class="relative">
                        <Input id="password_confirmation" v-model="form.password_confirmation"
                            :type="showPasswordConfirmation ? 'text' : 'password'" required autocomplete="new-password"
                            placeholder="Confirm password" class="pr-10" />
                        <button type="button"
                            class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600"
                            @click="showPasswordConfirmation = !showPasswordConfirmation">
                            <EyeOff v-if="showPasswordConfirmation" class="h-4 w-4" />
                            <Eye v-else class="h-4 w-4" />
                        </button>
                    </div>
                    <p v-if="form.password_confirmation.length > 0" class="text-xs flex items-center gap-1"
                        :class="passwordsMatch ? 'text-green-600' : 'text-red-500'">
                        <CheckCircle2 v-if="passwordsMatch" class="h-3.5 w-3.5" />
                        <XCircle v-else class="h-3.5 w-3.5" />
                        {{ passwordsMatch ? 'Passwords match' : 'Passwords do not match' }}
                    </p>
                    <InputError :message="form.errors.password_confirmation" />
                </div>

            </template>

            <!-- Submit -->
            <Button type="submit" class="w-full" :disabled="form.processing">
                <span v-if="form.processing"
                    class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                {{ form.processing ? 'Creating account...' : 'Create account' }}
            </Button>

            <!-- Google OAuth button — only show if NOT already from Google -->
            <template v-if="!isGoogleUser">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <span class="w-full border-t border-border" />
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="bg-background px-2 text-muted-foreground">OR</span>
                    </div>
                </div>

                <button type="button"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-md border border-border bg-background px-4 py-2 text-sm font-medium text-foreground shadow-sm transition-colors hover:bg-muted"
                    @click="goToGoogle">
                    <svg class="h-4 w-4" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" />
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                    </svg>
                    Continue with Google
                </button>
            </template>


            <!-- Hidden google_id -->
            <input type="hidden" v-model="form.google_id" />

            <!-- Footer -->
            <p class="text-center text-sm text-gray-500">
                Already have an account?
                <a class="text-gray-900 font-medium hover:underline cursor-pointer"
                    @click.prevent="router.visit('/login')">Log
                    in</a>
            </p>
            <p class="text-center text-xs text-gray-400">
                Own a laundry shop?
                <a class="text-blue-600 hover:underline cursor-pointer"
                    @click.prevent="router.visit('/register/shop')">Register
                    your shop</a>
            </p>

        </form>
    </AuthBase>
</template>
