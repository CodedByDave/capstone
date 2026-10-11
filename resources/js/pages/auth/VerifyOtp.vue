<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Head, useForm } from '@inertiajs/vue3';

const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute('content');

const form = useForm({
    otp: '',
});

const submit = () => {
    form.post('/verify-otp');
};
</script>

<template>
    <Head title="Verify OTP" />

    <div
        class="flex min-h-screen items-center justify-center bg-slate-50 dark:bg-[#0f0f0f]"
    >
        <div
            class="w-80 space-y-6 rounded-lg bg-white p-8 shadow-md dark:bg-[#1f1f1f]"
        >
            <h2
                class="text-center text-2xl font-bold text-gray-800 dark:text-white"
            >
                Verify Your Email
            </h2>
            <p class="text-center text-sm text-gray-600 dark:text-gray-300">
                Please enter the 6-digit OTP sent to your Gmail account.
            </p>

            <div>
                <Input
                    v-model="form.otp"
                    placeholder="Enter OTP"
                    maxlength="6"
                    class="text-center text-lg tracking-widest"
                />
                <p v-if="form.errors.otp" class="mt-1 text-sm text-red-500">
                    {{ form.errors.otp }}
                </p>
            </div>

            <Button class="w-full" @click="submit" :disabled="form.processing">
                Verify
            </Button>

            <form action="/otp/resend" method="POST">
                <input type="hidden" name="_token" :value="csrfToken" />
                <button
                    type="submit"
                    class="mt-2 w-full bg-transparent p-0 text-center text-sm text-blue-600 hover:underline"
                >
                    Resend OTP
                </button>
            </form>
        </div>
    </div>
</template>
