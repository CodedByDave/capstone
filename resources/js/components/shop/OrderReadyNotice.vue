<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    CheckCircle2,
    Copy,
    Mail,
    MessageSquareText,
    Phone,
    Send,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { toast } from 'vue3-toastify';

export interface ReadyNotice {
    /** The customer's email, when they gave one: they are emailed automatically. */
    email: string | null;
    email_sent_at: string | null;
    phone: string | null;
    /** A ready-made message staff can send from their own phone. */
    message: string;
}

const props = defineProps<{
    notice: ReadyNotice;
    customerName: string;
    /** The laundry goes out with a rider instead of being collected at the shop. */
    delivery?: boolean;
}>();

const hasEmail = computed(() => !!props.notice.email);

// Opens the phone's own messaging app with the text filled in, sent from the staff's own number.
const smsLink = computed(() =>
    props.notice.phone
        ? `sms:${props.notice.phone}?body=${encodeURIComponent(props.notice.message)}`
        : null,
);
const telLink = computed(() =>
    props.notice.phone ? `tel:${props.notice.phone}` : null,
);

const sentAt = computed(() => {
    const value = props.notice.email_sent_at;
    if (!value) return '';

    return new Date(value).toLocaleString('en-PH', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
});

async function copyNumber() {
    try {
        await navigator.clipboard.writeText(props.notice.phone ?? '');
        toast.success('Number copied.');
    } catch {
        toast.error('Could not copy. Select the number and copy it.');
    }
}

async function copyMessage() {
    try {
        await navigator.clipboard.writeText(props.notice.message);
        toast.success('Message copied. Paste it in Viber, Messenger or SMS.');
    } catch {
        toast.error('Could not copy. Select the message and copy it.');
    }
}
</script>

<template>
    <section
        class="flex flex-col gap-4 rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 sm:flex-row sm:items-start sm:justify-between dark:border-emerald-800 dark:bg-transparent"
        aria-labelledby="ready-notice-title"
    >
        <div class="flex min-w-0 items-start gap-3">
            <MessageSquareText
                class="mt-0.5 h-5 w-5 shrink-0 text-emerald-700 dark:text-emerald-400"
                aria-hidden="true"
            />
            <div class="min-w-0 space-y-1">
                <p
                    id="ready-notice-title"
                    class="text-sm font-semibold text-emerald-900 dark:text-emerald-200"
                >
                    {{ delivery ? 'Laundry done' : 'Ready for pickup' }}
                </p>

                <p
                    v-if="hasEmail"
                    class="flex items-start gap-1.5 text-sm text-emerald-800 dark:text-emerald-300"
                >
                    <component
                        :is="notice.email_sent_at ? CheckCircle2 : Mail"
                        class="mt-0.5 h-4 w-4 shrink-0"
                        aria-hidden="true"
                    />
                    <span v-if="notice.email_sent_at">
                        {{ customerName }} was emailed at {{ notice.email }} on
                        {{ sentAt }}.
                    </span>
                    <span v-else>
                        {{ customerName }} is being emailed at
                        {{ notice.email }}.
                    </span>
                </p>

                <template v-else>
                    <p class="text-sm text-emerald-800 dark:text-emerald-300">
                        {{ customerName }} did not give an email, so they are
                        not notified automatically. Let them know yourself.
                    </p>
                    <template v-if="notice.phone">
                        <p
                            class="text-sm font-medium text-emerald-900 dark:text-emerald-200"
                        >
                            Mobile number: {{ notice.phone }}
                        </p>
                        <p
                            class="text-sm text-emerald-800 dark:text-emerald-300"
                        >
                            “{{ notice.message }}”
                        </p>
                        <p class="text-xs text-muted-foreground">
                            On a computer, copy the number and message, then
                            send it from your phone (SMS, Viber or Messenger).
                        </p>
                    </template>
                    <p v-else class="text-xs text-muted-foreground">
                        There is no mobile number on this order either.
                    </p>
                </template>
            </div>
        </div>

        <div v-if="!hasEmail" class="flex shrink-0 flex-wrap gap-2">
            <template v-if="notice.phone">
                <Button as-child variant="success">
                    <a :href="telLink ?? undefined">
                        <Phone class="h-4 w-4" />
                        Call
                    </a>
                </Button>
                <Button as-child variant="success-outline">
                    <a :href="smsLink ?? undefined">
                        <Send class="h-4 w-4" />
                        Text from my phone
                    </a>
                </Button>
            </template>
            <Button
                v-if="notice.phone"
                type="button"
                variant="outline"
                @click="copyNumber"
            >
                <Copy class="h-4 w-4" />
                Copy number
            </Button>
            <Button type="button" variant="outline" @click="copyMessage">
                <Copy class="h-4 w-4" />
                Copy message
            </Button>
        </div>
    </section>
</template>
