<script setup lang="ts">
import { onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/login';
import { Form, Head, router } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const page = usePage();

onMounted(() => {
    const toastData = page.props.toast as { type: string; message: string } | undefined;

    if (toastData) {
        switch (toastData.type) {
            case 'success': toast.success(toastData.message); break;
            case 'error': toast.error(toastData.message); break;
            case 'warning': toast.warning(toastData.message); break;
            case 'info': toast.info(toastData.message); break;
            default: toast(toastData.message);
        }
    }
});

function goToGoogle() {
    window.location.href = '/auth/google'
}
</script>

<template>
    <AuthBase title="Log in to your account" description="Enter your email and password below to log in">

        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <Form v-bind="store.form()" :reset-on-success="['password']" v-slot="{ errors, processing }"
            class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input id="email" type="email" name="email" required autofocus :tabindex="1" autocomplete="email"
                        placeholder="email@example.com" />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                        <a v-if="canResetPassword" @click.prevent="router.visit('/forgot-password')"
                            class="underline text-primary hover:text-primary/80 cursor-pointer text-sm">
                            Forgot password?
                        </a>
                    </div>
                    <Input id="password" type="password" name="password" required :tabindex="2"
                        autocomplete="current-password" placeholder="Password" />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>Remember me</span>
                    </Label>
                </div>

                <Button type="submit" class="mt-4 w-full" :tabindex="4" :disabled="processing" data-test="login-button">
                    <Spinner v-if="processing" />
                    Log in
                </Button>
            </div>

            <!-- Divider -->
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <span class="w-full border-t border-border" />
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                    <span class="bg-card px-2 text-muted-foreground">Or continue with</span>
                </div>
            </div>

            <Button type="button" variant="outline" class="w-full gap-2" @click="goToGoogle">
                <svg class="h-4 w-4" viewBox="0 0 24 24">
                    <path
                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                        fill="#4285F4" />
                    <path
                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                        fill="#34A853" />
                    <path
                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"
                        fill="#FBBC05" />
                    <path
                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                        fill="#EA4335" />
                </svg>
                Continue with Google
            </Button>

            <div class="text-center text-sm text-muted-foreground">
                Don't have an account?
                <a @click.prevent="router.visit('/register/shop')"
                    class="underline text-primary hover:text-primary/80 cursor-pointer">
                    Sign Up
                </a>
            </div>
        </Form>
    </AuthBase>
</template>
