<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    Check,
    CheckCircle2,
    Copy,
    CreditCard,
    Eye,
    EyeOff,
    FlaskConical,
    Loader2,
    PlugZap,
    ShieldCheck,
    Unplug,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

type DemoCredentials = {
    secret_key: string;
    public_key: string;
};

const props = defineProps<{
    paymongo_configured: boolean;
    paymongo_public_key: string | null;
    payment_mode: 'demo' | 'paymongo' | null;
    demo_enabled: boolean;
    demo_credentials: DemoCredentials | null;
}>();

const page = usePage();
const showForm = ref(!props.paymongo_configured);
const showSecret = ref(false);
const saving = ref(false);
const secretKey = ref('');
const publicKey = ref(props.paymongo_public_key ?? '');
const copied = ref<string | null>(null);

const maskedPublicKey = computed(() => {
    const key = props.paymongo_public_key ?? '';
    return key.length > 12
        ? `${key.slice(0, 10)}••••••••${key.slice(-4)}`
        : key;
});

onMounted(() => {
    const flash = (page.props as any).toast as
        | { type: string; message: string }
        | undefined;
    if (!flash) return;
    if (flash.type === 'success') toast.success(flash.message);
    else if (flash.type === 'error') toast.error(flash.message);
    else toast(flash.message);
});

function useDemoKeys() {
    if (!props.demo_credentials) return;
    secretKey.value = props.demo_credentials.secret_key;
    publicKey.value = props.demo_credentials.public_key;
    showSecret.value = true;
    showForm.value = true;
    toast.info('Demo keys filled in. Click Connect Demo Gateway.');
}

async function copyValue(value: string, field: string) {
    await navigator.clipboard.writeText(value);
    copied.value = field;
    setTimeout(() => (copied.value = null), 1500);
}

function connect() {
    if (!secretKey.value.trim() || !publicKey.value.trim()) {
        toast.error('Enter both the secret key and public key.');
        return;
    }

    saving.value = true;
    router.post(
        '/shop/settings/paymongo',
        {
            paymongo_secret_key: secretKey.value.trim(),
            paymongo_public_key: publicKey.value.trim(),
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                secretKey.value = '';
                showForm.value = false;
            },
            onError: (errors) => {
                toast.error(
                    String(
                        errors.paymongo_secret_key ??
                            errors.paymongo_public_key ??
                            'Could not connect the payment gateway.',
                    ),
                );
            },
            onFinish: () => (saving.value = false),
        },
    );
}

function disconnect() {
    saving.value = true;
    router.post(
        '/shop/settings/paymongo',
        { paymongo_secret_key: null, paymongo_public_key: null },
        {
            preserveScroll: true,
            onFinish: () => (saving.value = false),
        },
    );
}
</script>

<template>
    <Head title="Payment Settings" />
    <ShopLayout>
        <div class="mx-auto max-w-2xl space-y-5 px-4 py-8">
            <div>
                <div class="flex items-center gap-2">
                    <CreditCard class="h-5 w-5 text-blue-600" />
                    <h1 class="text-xl font-bold text-gray-900">
                        Payment Settings
                    </h1>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    Configure online payment for this shop. Thesis demo mode
                    opens PayMongo's hosted test checkout using LaundryHub's
                    test account, so no real money is charged.
                </p>
            </div>

            <div
                v-if="
                    demo_enabled &&
                    demo_credentials &&
                    paymongo_configured &&
                    !showForm
                "
                class="rounded-2xl border border-violet-200 bg-violet-50 p-5"
            >
                <div class="flex items-start gap-3">
                    <FlaskConical
                        class="mt-0.5 h-5 w-5 shrink-0 text-violet-600"
                    />
                    <div>
                        <p class="font-semibold text-violet-950">
                            How to set up PayMongo test payments
                        </p>
                        <ol
                            class="mt-2 list-decimal space-y-1 pl-5 text-sm text-violet-800"
                        >
                            <li>
                                Copy the simulation secret and public keys
                                below.
                            </li>
                            <li>
                                Click Use simulation keys to open and fill the
                                credentials form.
                            </li>
                            <li>
                                Click Connect Demo Gateway to save the shop's
                                test-payment setup.
                            </li>
                            <li>
                                After weighing, customers can select GCash,
                                Maya, Card, or GrabPay at PayMongo's test
                                checkout.
                            </li>
                        </ol>
                    </div>
                </div>

                <div class="mt-4 space-y-3">
                    <div
                        class="rounded-xl border border-violet-200 bg-white p-3"
                    >
                        <p class="text-xs font-medium text-gray-500">
                            Simulation secret key (SK)
                        </p>
                        <div class="mt-1 flex items-center gap-2">
                            <code
                                class="min-w-0 flex-1 text-sm break-all text-gray-800"
                                >{{ demo_credentials.secret_key }}</code
                            >
                            <button
                                class="rounded-lg p-2 text-gray-500 hover:bg-gray-100"
                                type="button"
                                aria-label="Copy simulation secret key"
                                @click="
                                    copyValue(
                                        demo_credentials.secret_key,
                                        'secret',
                                    )
                                "
                            >
                                <Check
                                    v-if="copied === 'secret'"
                                    class="h-4 w-4 text-emerald-600"
                                />
                                <Copy v-else class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                    <div
                        class="rounded-xl border border-violet-200 bg-white p-3"
                    >
                        <p class="text-xs font-medium text-gray-500">
                            Simulation public key (PK)
                        </p>
                        <div class="mt-1 flex items-center gap-2">
                            <code
                                class="min-w-0 flex-1 text-sm break-all text-gray-800"
                                >{{ demo_credentials.public_key }}</code
                            >
                            <button
                                class="rounded-lg p-2 text-gray-500 hover:bg-gray-100"
                                type="button"
                                aria-label="Copy simulation public key"
                                @click="
                                    copyValue(
                                        demo_credentials.public_key,
                                        'public',
                                    )
                                "
                            >
                                <Check
                                    v-if="copied === 'public'"
                                    class="h-4 w-4 text-emerald-600"
                                />
                                <Copy v-else class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                    <Button
                        class="w-full bg-violet-600 text-white hover:bg-violet-700"
                        @click="useDemoKeys"
                    >
                        <FlaskConical class="mr-2 h-4 w-4" />
                        Use simulation keys
                    </Button>
                </div>
            </div>

            <div v-if="paymongo_configured && !showForm" class="space-y-4">
                <div
                    :class="[
                        'flex items-start gap-4 rounded-2xl border p-5',
                        payment_mode === 'demo'
                            ? 'border-violet-200 bg-violet-50'
                            : 'border-emerald-200 bg-emerald-50',
                    ]"
                >
                    <div
                        :class="[
                            'flex h-11 w-11 shrink-0 items-center justify-center rounded-full',
                            payment_mode === 'demo'
                                ? 'bg-violet-100'
                                : 'bg-emerald-100',
                        ]"
                    >
                        <FlaskConical
                            v-if="payment_mode === 'demo'"
                            class="h-5 w-5 text-violet-600"
                        />
                        <PlugZap v-else class="h-5 w-5 text-emerald-600" />
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p
                                :class="[
                                    'font-semibold',
                                    payment_mode === 'demo'
                                        ? 'text-violet-900'
                                        : 'text-emerald-900',
                                ]"
                            >
                                {{
                                    payment_mode === 'demo'
                                        ? 'Demo Gateway Connected'
                                        : 'PayMongo Connected'
                                }}
                            </p>
                            <span
                                v-if="payment_mode === 'demo'"
                                class="rounded-full bg-violet-200 px-2 py-0.5 text-[10px] font-bold tracking-wide text-violet-800 uppercase"
                            >
                                PayMongo test mode
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-600">
                            {{
                                payment_mode === 'demo'
                                    ? "Customers are redirected to PayMongo's hosted test checkout. Transactions use LaundryHub's test account and no real money is charged."
                                    : 'Customers can pay through the connected PayMongo account.'
                            }}
                        </p>
                        <p class="mt-2 font-mono text-xs text-gray-500">
                            Public key: {{ maskedPublicKey }}
                        </p>
                    </div>
                </div>

                <div
                    class="grid grid-cols-2 gap-2 rounded-2xl border border-gray-100 bg-white p-5 sm:grid-cols-4"
                >
                    <div
                        v-for="method in ['GCash', 'Maya', 'Card', 'GrabPay']"
                        :key="method"
                        class="flex items-center gap-2 rounded-xl bg-gray-50 px-3 py-2.5"
                    >
                        <CheckCircle2 class="h-4 w-4 text-emerald-500" />
                        <span class="text-xs font-medium text-gray-700">{{
                            method
                        }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <Button variant="outline" @click="showForm = true">
                        Change keys
                    </Button>
                    <Button
                        variant="outline"
                        class="border-red-200 text-red-600 hover:bg-red-50"
                        :disabled="saving"
                        @click="disconnect"
                    >
                        <Loader2
                            v-if="saving"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        <Unplug v-else class="mr-2 h-4 w-4" />
                        Disconnect
                    </Button>
                </div>
            </div>

            <div v-else class="space-y-5">
                <div
                    v-if="demo_enabled && demo_credentials"
                    class="rounded-2xl border border-violet-200 bg-violet-50 p-5"
                >
                    <div class="flex items-start gap-3">
                        <FlaskConical
                            class="mt-0.5 h-5 w-5 shrink-0 text-violet-600"
                        />
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-semibold text-violet-950">
                                    Thesis demonstration keys
                                </p>
                                <span
                                    class="rounded-full bg-violet-200 px-2 py-0.5 text-[10px] font-bold text-violet-800 uppercase"
                                >
                                    No real payment
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-violet-800">
                                Use this same pair for any registered demo shop.
                                It opens PayMongo's hosted test checkout through
                                LaundryHub's test account.
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div
                            class="rounded-xl border border-violet-200 bg-white p-3"
                        >
                            <p class="text-xs font-medium text-gray-500">
                                Demo secret key (SK)
                            </p>
                            <div class="mt-1 flex items-center gap-2">
                                <code
                                    class="min-w-0 flex-1 text-sm break-all text-gray-800"
                                    >{{ demo_credentials.secret_key }}</code
                                >
                                <button
                                    class="rounded-lg p-2 text-gray-500 hover:bg-gray-100"
                                    type="button"
                                    aria-label="Copy demo secret key"
                                    @click="
                                        copyValue(
                                            demo_credentials.secret_key,
                                            'secret',
                                        )
                                    "
                                >
                                    <Check
                                        v-if="copied === 'secret'"
                                        class="h-4 w-4 text-emerald-600"
                                    />
                                    <Copy v-else class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        <div
                            class="rounded-xl border border-violet-200 bg-white p-3"
                        >
                            <p class="text-xs font-medium text-gray-500">
                                Demo public key (PK)
                            </p>
                            <div class="mt-1 flex items-center gap-2">
                                <code
                                    class="min-w-0 flex-1 text-sm break-all text-gray-800"
                                    >{{ demo_credentials.public_key }}</code
                                >
                                <button
                                    class="rounded-lg p-2 text-gray-500 hover:bg-gray-100"
                                    type="button"
                                    aria-label="Copy demo public key"
                                    @click="
                                        copyValue(
                                            demo_credentials.public_key,
                                            'public',
                                        )
                                    "
                                >
                                    <Check
                                        v-if="copied === 'public'"
                                        class="h-4 w-4 text-emerald-600"
                                    />
                                    <Copy v-else class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        <Button
                            class="w-full bg-violet-600 text-white hover:bg-violet-700"
                            @click="useDemoKeys"
                        >
                            <FlaskConical class="mr-2 h-4 w-4" />
                            Use demo keys
                        </Button>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5">
                    <div class="flex items-center gap-2">
                        <ShieldCheck class="h-5 w-5 text-blue-600" />
                        <h2 class="font-semibold text-gray-900">
                            Connect payment credentials
                        </h2>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        For the defense demo, click “Use demo keys” above. Real
                        PayMongo test/live credentials are also supported.
                    </p>

                    <div class="mt-4 space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-gray-700">
                                Secret key (SK)
                            </label>
                            <div class="relative">
                                <Input
                                    v-model="secretKey"
                                    :type="showSecret ? 'text' : 'password'"
                                    placeholder="sk_test_..."
                                    class="pr-10 font-mono text-sm"
                                />
                                <button
                                    type="button"
                                    class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                    @click="showSecret = !showSecret"
                                >
                                    <EyeOff v-if="showSecret" class="h-4 w-4" />
                                    <Eye v-else class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-gray-700">
                                Public key (PK)
                            </label>
                            <Input
                                v-model="publicKey"
                                placeholder="pk_test_..."
                                class="font-mono text-sm"
                            />
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <Button :disabled="saving" @click="connect">
                                <Loader2
                                    v-if="saving"
                                    class="mr-2 h-4 w-4 animate-spin"
                                />
                                <PlugZap v-else class="mr-2 h-4 w-4" />
                                {{
                                    saving
                                        ? 'Connecting…'
                                        : secretKey ===
                                            demo_credentials?.secret_key
                                          ? 'Connect Demo Gateway'
                                          : 'Connect Payment Gateway'
                                }}
                            </Button>
                            <Button
                                v-if="paymongo_configured"
                                variant="outline"
                                @click="showForm = false"
                            >
                                Cancel
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>
