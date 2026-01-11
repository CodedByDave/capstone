<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login, register} from '@/routes';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { watch, ref, computed } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';
import { store } from '@/routes/register';

const page = usePage();

/* ---------------- PASSWORD STATE ---------------- */
const password = ref('');
const passwordConfirmation = ref('');
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

/* ---------------- PASSWORD VALIDATION ---------------- */
const rules = {
    length: (v: string) => v.length >= 8,
    alphaNumeric: (v: string) => /[A-Za-z]/.test(v) && /\d/.test(v),
    special: (v: string) => /[^A-Za-z0-9]/.test(v),
};

const isPasswordValid = computed(() =>
    rules.length(password.value) &&
    rules.alphaNumeric(password.value) &&
    rules.special(password.value)
);

const passwordError = computed(() => {
    if (!password.value) return null;
    if (!rules.length(password.value))
        return 'At least 8 characters required.';
    if (!rules.alphaNumeric(password.value))
        return 'Must include letters and numbers.';
    if (!rules.special(password.value))
        return 'Must include a special character.';
    return null;
});
</script>

<template>
    <AuthBase
        title="Create an account"
        description="Enter your details below to create your account"
    >
        <Head title="Register" />

        <Form
            v-bind="store.form()"
            :reset-on-success="true"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <!-- Name -->
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        name="name"
                        type="text"
                        required
                        autocomplete="name"
                        placeholder="Full name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <!-- Email -->
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        name="email"
                        type="email"
                        required
                        autocomplete="email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <!-- Password -->
                <div class="grid gap-2">
                    <Label for="password">Password</Label>

                    <div class="relative">
                        <Input
                            id="password"
                            name="password"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            autocomplete="new-password"
                            placeholder="Password"
                            class="pr-10"
                            v-model="password"
                        />

                        <button
                            type="button"
                            class="absolute inset-y-0 right-3 flex items-center text-muted-foreground"
                            @click="showPassword = !showPassword"
                        >
                            <Eye v-if="!showPassword" class="h-4 w-4" />
                            <EyeOff v-else class="h-4 w-4" />
                        </button>
                    </div>

                    <p v-if="passwordError" class="text-sm text-destructive">
                        {{ passwordError }}
                    </p>

                    <p v-else-if="password" class="text-sm text-green-600">
                        Strong password
                    </p>

                    <InputError :message="errors.password" />
                </div>

                <!-- Confirm Password -->
                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>

                    <div class="relative">
                        <Input
                            id="password_confirmation"
                            name="password_confirmation"
                            :type="showPasswordConfirmation ? 'text' : 'password'"
                            required
                            autocomplete="new-password"
                            placeholder="Confirm password"
                            class="pr-10"
                            v-model="passwordConfirmation"
                        />

                        <button
                            type="button"
                            class="absolute inset-y-0 right-3 flex items-center text-muted-foreground"
                            @click="showPasswordConfirmation = !showPasswordConfirmation"
                        >
                            <Eye v-if="!showPasswordConfirmation" class="h-4 w-4" />
                            <EyeOff v-else class="h-4 w-4" />
                        </button>
                    </div>

                    <InputError :message="errors.password_confirmation" />
                </div>

                <!-- Submit -->
                <Button
                    type="submit"
                    class="mt-2 w-full"
                    :disabled="processing || !isPasswordValid"
                >
                    <Spinner v-if="processing" />
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                >
                    Log in
                </TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
