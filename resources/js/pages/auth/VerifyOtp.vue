<script setup lang="ts">
import AuthPanelShell from '@/components/auth/AuthPanelShell.vue';
import {
    primaryButtonClass,
    textLinkClass,
} from '@/components/auth/authStyles';
import InputError from '@/components/InputError.vue';
import { Spinner } from '@/components/ui/spinner';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, ref } from 'vue';

const LENGTH = 6;

const form = useForm({
    otp: '',
});

const resendForm = useForm({ error: '' });

const digits = ref<string[]>(Array(LENGTH).fill(''));
const boxes = ref<HTMLInputElement[]>([]);

const isComplete = computed(() => digits.value.every((d) => d !== ''));

function sync() {
    form.otp = digits.value.join('');
}

function focusBox(index: number) {
    const box = boxes.value[Math.min(Math.max(index, 0), LENGTH - 1)];
    box?.focus();
    box?.select();
}

/** Spreads digits across the boxes starting at `start`; returns how many were placed. */
function fill(start: number, value: string): number {
    const chars = value.replace(/\D/g, '').slice(0, LENGTH - start);
    chars.split('').forEach((c, i) => (digits.value[start + i] = c));
    sync();
    return chars.length;
}

function onInput(index: number, event: Event) {
    const input = event.target as HTMLInputElement;
    const raw = input.value;

    if (raw === '') {
        digits.value[index] = '';
        sync();
        return;
    }

    const placed = fill(index, raw);
    // Keep the box in step with state even when the typed character was rejected.
    input.value = digits.value[index];

    if (placed > 0) focusBox(index + placed);
}

function onKeydown(index: number, event: KeyboardEvent) {
    if (event.key === 'Backspace' && !digits.value[index]) {
        event.preventDefault();
        if (index > 0) {
            digits.value[index - 1] = '';
            sync();
            focusBox(index - 1);
        }
    } else if (event.key === 'ArrowLeft') {
        event.preventDefault();
        focusBox(index - 1);
    } else if (event.key === 'ArrowRight') {
        event.preventDefault();
        focusBox(index + 1);
    }
}

function onPaste(index: number, event: ClipboardEvent) {
    event.preventDefault();
    const placed = fill(index, event.clipboardData?.getData('text') ?? '');
    if (placed > 0) focusBox(index + placed);
}

function resetDigits() {
    digits.value = Array(LENGTH).fill('');
    form.otp = '';
    nextTick(() => focusBox(0));
}

const submit = () => {
    if (!isComplete.value) return;
    form.post('/verify-otp', {
        onError: resetDigits,
    });
};

const resend = () => {
    resendForm.post('/resend-otp', {
        preserveScroll: true,
        onSuccess: resetDigits,
    });
};

onMounted(() => focusBox(0));
</script>

<template>
    <Head title="Verify email" />

    <AuthPanelShell
        screen="otp"
        title="Verify Your Email"
        description="Please enter the 6-digit code sent to your email address."
    >
        <form class="mt-6 space-y-5" @submit.prevent="submit">
            <div
                class="flex justify-center gap-2 sm:gap-2.5"
                role="group"
                aria-label="6-digit verification code"
            >
                <input
                    v-for="(_, i) in digits"
                    :key="i"
                    :ref="(el) => (boxes[i] = el as HTMLInputElement)"
                    :value="digits[i]"
                    type="text"
                    inputmode="numeric"
                    :autocomplete="i === 0 ? 'one-time-code' : 'off'"
                    maxlength="6"
                    :aria-label="`Digit ${i + 1} of ${LENGTH}`"
                    :aria-invalid="!!form.errors.otp"
                    class="h-[60px] w-11 rounded-xl border bg-white text-center text-xl font-semibold text-slate-900 transition-colors focus-visible:border-blue-600 focus-visible:ring-2 focus-visible:ring-blue-600/25 focus-visible:outline-none sm:w-14"
                    :class="
                        form.errors.otp ? 'border-red-500' : 'border-slate-300'
                    "
                    @input="onInput(i, $event)"
                    @keydown="onKeydown(i, $event)"
                    @paste="onPaste(i, $event)"
                    @focus="($event.target as HTMLInputElement).select()"
                />
            </div>

            <InputError class="text-center" :message="form.errors.otp" />
            <InputError
                class="text-center"
                :message="resendForm.errors.error"
            />

            <button
                type="submit"
                :class="primaryButtonClass"
                :disabled="form.processing || !isComplete"
            >
                <Spinner v-if="form.processing" />
                Verify
            </button>

            <p class="text-center text-sm text-slate-500">
                Didn't get the code?
                <button
                    type="button"
                    :class="[textLinkClass, 'disabled:opacity-50']"
                    :disabled="resendForm.processing"
                    @click="resend"
                >
                    Resend OTP
                </button>
            </p>
        </form>

        <template #panel>
            <h2 class="font-display text-3xl font-bold tracking-tight">
                Check your inbox
            </h2>
            <p class="mt-3 max-w-[310px] text-[15px] leading-relaxed">
                We sent a code to your email. Enter it to finish creating your
                account.
            </p>
        </template>
    </AuthPanelShell>
</template>
