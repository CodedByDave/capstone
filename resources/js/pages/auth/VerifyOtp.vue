<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const form = useForm({
    otp: '',
});

const resendForm = useForm({});

const submit = () => {
    form.post('/verify-otp');
};

const resendOtp = () => {
    resendForm.post('/resend-otp');
};
</script>

<template>

    <Head title="Verify OTP" />

    <div class="min-h-screen flex items-center justify-center bg-slate-50 dark:bg-[#0f0f0f]">
        <div class="bg-white dark:bg-[#1f1f1f] p-8 rounded-lg shadow-md w-80 space-y-6">

            <h2 class="text-2xl font-bold text-center text-gray-800 dark:text-white">Verify Your Email</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300 text-center">
                Please enter the 6-digit OTP sent to your Gmail account.
            </p>

            <div>
                <Input v-model="form.otp" placeholder="Enter OTP" maxlength="6"
                    class="text-center tracking-widest text-lg" />
                <p v-if="form.errors.otp" class="text-red-500 text-sm mt-1">{{ form.errors.otp }}</p>
            </div>

            <Button class="w-full" @click="submit" :disabled="form.processing">
                Verify
            </Button>

            <form action="/otp/resend" method="POST">
                <input type="hidden" name="_token" :value="csrfToken">
                    <button type="submit"
                        class="text-sm text-blue-600 hover:underline w-full text-center mt-2 bg-transparent p-0">
                        Resend OTP
                    </button>
            </form>


        </div>
    </div>
</template>
