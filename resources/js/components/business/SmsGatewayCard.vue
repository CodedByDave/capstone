<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    patchSettingsForm,
    useSettingsSection,
} from '@/composables/useSettingsSaveBar';
import { useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    CheckCircle2,
    ChevronDown,
    Cloud,
    Loader2,
    MessageSquareText,
    Send,
    Smartphone,
    Wifi,
} from 'lucide-vue-next';
import { computed } from 'vue';

export interface SmsGatewaySettings {
    /** Texts can go out: the platform sender is set up, or the shop uses its own phone. */
    available: boolean;
    platform_available: boolean;
    notify_ready: boolean;
    own_phone: {
        enabled: boolean;
        mode: 'cloud' | 'local';
        url: string | null;
        username: string | null;
        has_password: boolean;
    };
}

const props = defineProps<{ settings: SmsGatewaySettings }>();

const form = useForm({
    notify_ready: props.settings.notify_ready,
    enabled: props.settings.own_phone.enabled,
    mode: props.settings.own_phone.mode,
    url: props.settings.own_phone.url ?? '',
    username: props.settings.own_phone.username ?? '',
    // Blank keeps the saved password; it is never sent back to the browser.
    password: '',
});

const testForm = useForm({ phone: '' });

// Open the advanced part straight away only for shops already using their own phone.
const advancedOpen = computed(() => props.settings.own_phone.enabled);

const modes = [
    {
        value: 'cloud' as const,
        label: 'Cloud',
        hint: 'Works anywhere the phone has internet.',
        icon: Cloud,
    },
    {
        value: 'local' as const,
        label: 'Local network',
        hint: 'The phone and this server must be on the same Wi-Fi.',
        icon: Wifi,
    },
];

useSettingsSection({
    key: 'sms',
    label: 'SMS notifications',
    isDirty: () => form.isDirty,
    save: async () => {
        const saved = await patchSettingsForm(form, '/shop/settings/sms');
        if (saved) form.password = '';
        return saved;
    },
    discard: () => {
        form.reset();
        form.clearErrors();
    },
});

function sendTest() {
    testForm.post('/shop/settings/sms/test', {
        preserveScroll: true,
        onSuccess: () => testForm.reset(),
    });
}
</script>

<template>
    <section class="space-y-6">
        <div>
            <div class="flex items-center gap-2">
                <MessageSquareText class="h-4 w-4 text-muted-foreground" />
                <h3 class="text-base font-semibold">SMS Notifications</h3>
            </div>
            <p class="mt-1 text-sm text-muted-foreground">
                Customers get a text when their laundry is done: ready for
                pickup, or on its way for delivery orders, including walk-in
                customers without an account.
            </p>
        </div>

        <!-- Status -->
        <p
            v-if="settings.available"
            class="flex max-w-xl items-start gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-800 dark:bg-transparent dark:text-emerald-300"
        >
            <CheckCircle2 class="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true" />
            <span>
                Texting is ready.
                {{
                    settings.own_phone.enabled
                        ? 'Texts are sent from your own phone.'
                        : 'LaundryHub sends the texts for you. There is nothing to install.'
                }}
            </span>
        </p>
        <p
            v-else
            class="flex max-w-xl items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-800 dark:bg-transparent dark:text-amber-300"
        >
            <AlertTriangle class="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true" />
            <span>
                Automatic texts are not available yet. When an order is
                completed, staff are asked to send the text from their own phone
                in one tap.
            </span>
        </p>

        <!-- The one setting most owners need -->
        <label
            class="flex max-w-xl cursor-pointer items-center justify-between gap-4 rounded-lg border p-4 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-ring"
        >
            <span>
                <span class="block text-sm font-medium"
                    >Text customers when their laundry is ready</span
                >
                <span class="block text-xs text-muted-foreground">
                    Sent once, when an order is marked Completed. Walk-in and
                    delivery orders both get it.
                </span>
            </span>
            <input
                v-model="form.notify_ready"
                type="checkbox"
                role="switch"
                :aria-checked="form.notify_ready"
                class="peer sr-only"
            />
            <span
                class="relative h-6 w-11 shrink-0 rounded-full transition"
                :class="
                    form.notify_ready ? 'bg-primary' : 'bg-muted-foreground/30'
                "
                aria-hidden="true"
            >
                <span
                    class="absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow transition"
                    :class="form.notify_ready ? 'translate-x-5' : ''"
                />
            </span>
        </label>

        <!-- Test -->
        <div v-if="settings.available" class="max-w-xl space-y-2">
            <label
                for="sms-test-phone"
                class="flex items-center gap-2 text-sm font-medium"
            >
                <Smartphone class="h-4 w-4 text-muted-foreground" />
                Send a test text to yourself
            </label>
            <div class="flex flex-wrap gap-2">
                <Input
                    id="sms-test-phone"
                    v-model="testForm.phone"
                    class="min-w-48 flex-1"
                    inputmode="tel"
                    placeholder="0917 123 4567"
                    @keydown.enter.prevent="sendTest"
                />
                <Button
                    type="button"
                    variant="primary-outline"
                    class="min-h-11"
                    :disabled="testForm.processing || !testForm.phone"
                    @click="sendTest"
                >
                    <Loader2
                        v-if="testForm.processing"
                        class="h-4 w-4 animate-spin"
                    />
                    <Send v-else class="h-4 w-4" />
                    Send test
                </Button>
            </div>
            <InputError :message="testForm.errors.phone" />
        </div>

        <!-- Advanced: the shop's own phone -->
        <details class="group max-w-xl rounded-lg border" :open="advancedOpen">
            <summary
                class="flex min-h-11 cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-sm font-medium focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
            >
                <span>
                    Advanced: send from my own phone
                    <span
                        class="block text-xs font-normal text-muted-foreground"
                        >Optional. Texts then come from your shop's
                        number.</span
                    >
                </span>
                <ChevronDown
                    class="h-4 w-4 shrink-0 text-muted-foreground transition group-open:rotate-180"
                    aria-hidden="true"
                />
            </summary>

            <div class="space-y-5 border-t px-4 py-4">
                <ol
                    class="list-decimal space-y-1 pl-5 text-sm text-muted-foreground"
                >
                    <li>
                        On an Android phone with a SIM, install
                        <a
                            href="https://sms-gate.app/"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="font-medium text-primary underline-offset-4 hover:underline"
                            >SMS Gateway for Android</a
                        >
                        and allow it to send SMS.
                    </li>
                    <li>
                        Turn on <strong>Cloud server</strong> in the app and
                        copy the username and password it shows.
                    </li>
                    <li>Enter them below and save.</li>
                </ol>

                <label class="flex items-start gap-3">
                    <input
                        v-model="form.enabled"
                        type="checkbox"
                        class="mt-0.5 h-5 w-5 rounded border-gray-300 accent-blue-600"
                    />
                    <span class="text-sm font-medium"
                        >Send texts from my own phone</span
                    >
                </label>

                <template v-if="form.enabled">
                    <div
                        class="grid gap-2 sm:grid-cols-2"
                        role="radiogroup"
                        aria-label="Connection"
                    >
                        <label
                            v-for="mode in modes"
                            :key="mode.value"
                            class="flex cursor-pointer items-start gap-3 rounded-lg border p-3 transition has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-ring"
                            :class="
                                form.mode === mode.value
                                    ? 'border-primary bg-primary/5'
                                    : 'hover:border-muted-foreground/40'
                            "
                        >
                            <input
                                v-model="form.mode"
                                type="radio"
                                name="sms_mode"
                                :value="mode.value"
                                class="sr-only"
                            />
                            <component
                                :is="mode.icon"
                                class="mt-0.5 h-4 w-4 shrink-0"
                                :class="
                                    form.mode === mode.value
                                        ? 'text-primary'
                                        : 'text-muted-foreground'
                                "
                                aria-hidden="true"
                            />
                            <span>
                                <span class="block text-sm font-medium">{{
                                    mode.label
                                }}</span>
                                <span
                                    class="block text-xs text-muted-foreground"
                                    >{{ mode.hint }}</span
                                >
                            </span>
                        </label>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div
                            v-if="form.mode === 'local'"
                            class="space-y-2 sm:col-span-2"
                        >
                            <label for="sms-url" class="text-sm font-medium"
                                >Phone address</label
                            >
                            <Input
                                id="sms-url"
                                v-model="form.url"
                                inputmode="url"
                                placeholder="http://192.168.1.20:8080"
                            />
                            <InputError :message="form.errors.url" />
                        </div>
                        <div class="space-y-2">
                            <label
                                for="sms-username"
                                class="text-sm font-medium"
                                >Username</label
                            >
                            <Input
                                id="sms-username"
                                v-model="form.username"
                                autocomplete="off"
                            />
                            <InputError :message="form.errors.username" />
                        </div>
                        <div class="space-y-2">
                            <label
                                for="sms-password"
                                class="text-sm font-medium"
                                >Password</label
                            >
                            <Input
                                id="sms-password"
                                v-model="form.password"
                                type="password"
                                autocomplete="new-password"
                                :placeholder="
                                    settings.own_phone.has_password
                                        ? 'Saved — leave blank to keep'
                                        : ''
                                "
                            />
                            <InputError :message="form.errors.password" />
                        </div>
                    </div>
                </template>
            </div>
        </details>
    </section>
</template>
