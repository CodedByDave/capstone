<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    CheckCircle2,
    Copy,
    Loader2,
    MessageSquareText,
    Send,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue3-toastify';

export interface SmsNotice {
    /** Texts can go out automatically (platform sender or the shop's own phone). */
    enabled: boolean;
    /** The customer number in +63 form, or null when it is not a mobile number. */
    phone: string | null;
    message: string;
    last: {
        status: 'sent' | 'failed';
        error: string | null;
        sent_at: string | null;
        sent_by: string | null;
    } | null;
}

const props = defineProps<{
    notice: SmsNotice;
    customerName: string;
    url: string;
    canManage: boolean;
    /** Just marked Completed with no automatic text: ask staff to send one now. */
    prompt?: boolean;
    /** The laundry goes out with a rider instead of being collected at the shop. */
    delivery?: boolean;
}>();

const sending = ref(false);
const promptOpen = ref(props.prompt === true && props.notice.phone !== null);

const lastSent = computed(() => props.notice.last?.status === 'sent');
const lastFailed = computed(() => props.notice.last?.status === 'failed');
const canSend = computed(
    () =>
        props.canManage && props.notice.enabled && props.notice.phone !== null,
);

// Opens the phone's own messaging app with the text filled in: free, no gateway needed.
const smsLink = computed(() =>
    props.notice.phone
        ? `sms:${props.notice.phone}?body=${encodeURIComponent(props.notice.message)}`
        : null,
);

const when = computed(() => {
    const value = props.notice.last?.sent_at;
    if (!value) return '';

    return new Date(value).toLocaleString('en-PH', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
});

function send() {
    sending.value = true;
    router.post(
        props.url,
        {},
        {
            preserveScroll: true,
            onError: (errors) => {
                toast.error(
                    errors.sms ?? 'The text could not be sent. Try again.',
                );
            },
            onFinish: () => (sending.value = false),
        },
    );
}

function openedSmsApp() {
    promptOpen.value = false;
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
                    {{ delivery ? 'Laundry done' : 'Ready for pickup' }} — let
                    {{ customerName }} know
                </p>
                <p class="text-sm text-emerald-800 dark:text-emerald-300">
                    “{{ notice.message }}”
                </p>

                <p
                    v-if="lastSent"
                    class="flex items-center gap-1.5 text-xs text-emerald-800 dark:text-emerald-300"
                >
                    <CheckCircle2 class="h-3.5 w-3.5" aria-hidden="true" />
                    Texted {{ when
                    }}{{
                        notice.last?.sent_by
                            ? ` by ${notice.last.sent_by}`
                            : ' automatically'
                    }}.
                </p>
                <p
                    v-else-if="lastFailed"
                    class="flex items-start gap-1.5 text-xs text-red-700 dark:text-red-400"
                    role="alert"
                >
                    <AlertTriangle
                        class="mt-0.5 h-3.5 w-3.5 shrink-0"
                        aria-hidden="true"
                    />
                    Text not sent {{ when }}: {{ notice.last?.error }}
                </p>
                <p
                    v-else-if="!notice.phone"
                    class="text-xs text-muted-foreground"
                >
                    No mobile number on this order. Add one on the Edit page to
                    text the customer.
                </p>
                <p
                    v-else-if="!notice.enabled"
                    class="text-xs text-muted-foreground"
                >
                    Automatic texts are not available. Send it from your phone
                    with "Open SMS app".
                </p>
            </div>
        </div>

        <div class="flex shrink-0 flex-wrap gap-2">
            <Button
                v-if="canSend"
                type="button"
                variant="success"
                :disabled="sending"
                @click="send"
            >
                <Loader2 v-if="sending" class="h-4 w-4 animate-spin" />
                <Send v-else class="h-4 w-4" />
                {{ lastSent ? 'Text again' : 'Text customer' }}
            </Button>
            <template v-else-if="notice.phone">
                <Button as-child variant="success-outline">
                    <a :href="smsLink ?? undefined">
                        <Send class="h-4 w-4" />
                        Open SMS app
                    </a>
                </Button>
            </template>
            <Button type="button" variant="outline" @click="copyMessage">
                <Copy class="h-4 w-4" />
                Copy message
            </Button>
        </div>
    </section>

    <!-- One tap after marking the order Completed, when no text went out on its own -->
    <Dialog v-model:open="promptOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle
                    >Let {{ customerName }} know it is ready</DialogTitle
                >
                <DialogDescription>
                    Tap the button to open your messaging app with the text
                    filled in, then press Send.
                </DialogDescription>
            </DialogHeader>
            <p
                class="rounded-lg border bg-muted/40 p-3 text-sm"
                aria-label="Message"
            >
                {{ notice.message }}
            </p>
            <DialogFooter class="flex-col gap-2 sm:flex-col">
                <Button
                    as-child
                    variant="success"
                    class="min-h-12 w-full text-base"
                >
                    <a :href="smsLink ?? undefined" @click="openedSmsApp">
                        <Send class="h-5 w-5" />
                        Text {{ customerName }} now
                    </a>
                </Button>
                <div class="grid grid-cols-2 gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        class="min-h-11"
                        @click="copyMessage"
                    >
                        <Copy class="h-4 w-4" />
                        Copy message
                    </Button>
                    <Button
                        type="button"
                        variant="ghost"
                        class="min-h-11"
                        @click="promptOpen = false"
                    >
                        Later
                    </Button>
                </div>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
