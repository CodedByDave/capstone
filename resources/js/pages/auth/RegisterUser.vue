<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Eye, EyeOff, CheckCircle2, XCircle } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const rules = {
    length:      (v: string) => v.length >= 8,
    alphaNumeric:(v: string) => /[A-Za-z]/.test(v) && /\d/.test(v),
    special:     (v: string) => /[^A-Za-z0-9]/.test(v),
};

const isPasswordValid = computed(() =>
    rules.length(form.password) &&
    rules.alphaNumeric(form.password) &&
    rules.special(form.password)
);

const passwordRules = computed(() => [
    { label: 'At least 8 characters',       pass: rules.length(form.password) },
    { label: 'Contains a letter and number', pass: rules.alphaNumeric(form.password) },
    { label: 'Contains a special character', pass: rules.special(form.password) },
]);

const passwordStrength = computed(() => {
    const passed = passwordRules.value.filter(r => r.pass).length;
    if (passed <= 1) return { label: 'Weak',   color: 'bg-red-500',    width: 'w-1/3' };
    if (passed === 2) return { label: 'Good',   color: 'bg-yellow-400', width: 'w-2/3' };
    return               { label: 'Strong', color: 'bg-green-500',  width: 'w-full' };
});

const passwordsMatch = computed(() =>
    form.password === form.password_confirmation &&
    form.password_confirmation.length > 0
);

function submit() {
    form.post('/register');
}
</script>

<template>
    <Head title="Create Account" />

    <div class="min-h-screen bg-white flex items-center justify-center p-4">
        <div class="w-full max-w-sm">

            <!-- Logo -->
            <div class="flex flex-col items-center mb-8">
                <div class="h-14 w-14 mb-3">
                    <img src="/laundryhub.png" alt="LaundryHub" class="h-14 w-14 object-contain" />
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Create an account</h1>
                <p class="text-sm text-gray-500 mt-1">Enter your details below to create your account</p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-5">

                <!-- Name -->
                <div class="space-y-1.5">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        autocomplete="name"
                        placeholder="Full name"
                        :class="form.errors.name ? 'border-red-400' : ''"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <!-- Email -->
                <div class="space-y-1.5">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="email"
                        placeholder="email@example.com"
                        :class="form.errors.email ? 'border-red-400' : ''"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <!-- Password -->
                <div class="space-y-1.5">
                    <Label for="password">Password</Label>
                    <div class="relative">
                        <Input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            autocomplete="new-password"
                            placeholder="Password"
                            class="pr-10"
                            :class="form.errors.password ? 'border-red-400' : ''"
                        />
                        <button
                            type="button"
                            class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600"
                            @click="showPassword = !showPassword"
                        >
                            <EyeOff v-if="showPassword" class="h-4 w-4" />
                            <Eye v-else class="h-4 w-4" />
                        </button>
                    </div>

                    <!-- Strength bar -->
                    <div v-if="form.password.length > 0" class="mt-2">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs text-gray-400">Password strength</span>
                            <span class="text-xs font-medium" :class="{
                                'text-red-500':    passwordStrength.label === 'Weak',
                                'text-yellow-500': passwordStrength.label === 'Good',
                                'text-green-500':  passwordStrength.label === 'Strong',
                            }">{{ passwordStrength.label }}</span>
                        </div>
                        <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                            <div :class="['h-full rounded-full transition-all duration-300', passwordStrength.color, passwordStrength.width]" />
                        </div>
                    </div>

                    <!-- Requirements checklist -->
                    <ul v-if="form.password.length > 0" class="mt-2 space-y-1">
                        <li
                            v-for="rule in passwordRules"
                            :key="rule.label"
                            class="flex items-center gap-1.5 text-xs"
                            :class="rule.pass ? 'text-green-600' : 'text-gray-400'"
                        >
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
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :type="showPasswordConfirmation ? 'text' : 'password'"
                            required
                            autocomplete="new-password"
                            placeholder="Confirm password"
                            class="pr-10"
                        />
                        <button
                            type="button"
                            class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600"
                            @click="showPasswordConfirmation = !showPasswordConfirmation"
                        >
                            <EyeOff v-if="showPasswordConfirmation" class="h-4 w-4" />
                            <Eye v-else class="h-4 w-4" />
                        </button>
                    </div>
                    <p
                        v-if="form.password_confirmation.length > 0"
                        class="text-xs flex items-center gap-1"
                        :class="passwordsMatch ? 'text-green-600' : 'text-red-500'"
                    >
                        <CheckCircle2 v-if="passwordsMatch" class="h-3.5 w-3.5" />
                        <XCircle v-else class="h-3.5 w-3.5" />
                        {{ passwordsMatch ? 'Passwords match' : 'Passwords do not match' }}
                    </p>
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <!-- Submit -->
                <Button
                    type="submit"
                    class="w-full h-11 text-base"
                    :disabled="form.processing || !isPasswordValid"
                >
                    <span v-if="form.processing" class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                    {{ form.processing ? 'Creating account...' : 'Create account' }}
                </Button>

                <!-- Footer -->
                <p class="text-center text-sm text-gray-500">
                    Already have an account?
                    <a class="text-gray-900 font-medium hover:underline cursor-pointer"
                        @click.prevent="router.visit('/login')">Log in</a>
                </p>
                <p class="text-center text-xs text-gray-400">
                    Own a laundry shop?
                    <a class="text-blue-600 hover:underline cursor-pointer"
                        @click.prevent="router.visit('/register/shop')">Register your shop</a>
                </p>

            </form>
        </div>
    </div>
</template>
