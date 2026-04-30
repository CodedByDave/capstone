<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
    CreditCard, CheckCircle2, AlertCircle, ExternalLink,
    Eye, EyeOff, Loader2, PlugZap, Unplug, Plug, Copy,
} from 'lucide-vue-next'

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    paymongo_configured: boolean
    paymongo_public_key: string | null
}>()

// ─── Flash ────────────────────────────────────────────────────────────────────

const page = usePage()
onMounted(() => {
    const flash = (page.props as any).toast as { type: string; message: string } | undefined
    if (!flash) return
    if (flash.type === 'success') toast.success(flash.message)
    else if (flash.type === 'error') toast.error(flash.message)
    else toast(flash.message)
})

// ─── State ────────────────────────────────────────────────────────────────────

const showForm       = ref(!props.paymongo_configured)
const showCode       = ref(false)
const saving         = ref(false)
const accountCode    = ref('')   // secret key — called "Account Code" for owners
const accountId      = ref(props.paymongo_public_key ?? '')  // public key — called "Account ID"

const maskedAccountId = computed(() => {
    const k = props.paymongo_public_key ?? ''
    return k.length > 8 ? k.slice(0, 8) + '••••••••' + k.slice(-4) : k
})

// ─── Actions ──────────────────────────────────────────────────────────────────

function connect() {
    if (!accountCode.value.trim() || !accountId.value.trim()) {
        toast.error('Please fill in both your Account Code and Account ID.')
        return
    }
    saving.value = true
    router.post('/shop/settings/paymongo', {
        paymongo_secret_key: accountCode.value.trim(),
        paymongo_public_key: accountId.value.trim(),
    }, {
        preserveScroll: true,
        onSuccess: () => {
            accountCode.value = ''
            showForm.value    = false
        },
        onFinish: () => { saving.value = false },
    })
}

function disconnect() {
    saving.value = true
    router.post('/shop/settings/paymongo', {
        paymongo_secret_key: null,
        paymongo_public_key: null,
    }, {
        preserveScroll: true,
        onFinish: () => { saving.value = false },
    })
}
</script>

<template>
    <Head title="Online Payments" />
    <ShopLayout>
        <div class="max-w-xl mx-auto px-4 py-8 space-y-5">

            <!-- Header -->
            <div>
                <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <CreditCard class="h-5 w-5 text-blue-600" />
                    Online Payments
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Let your customers pay online using GCash, Maya, credit card, and more.
                    Money goes directly into your account.
                </p>
            </div>

            <!-- ══ CONNECTED ══ -->
            <template v-if="paymongo_configured && !showForm">

                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 flex items-start gap-4">
                    <div class="h-11 w-11 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                        <PlugZap class="h-5 w-5 text-emerald-600" />
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-emerald-800">Account Connected</p>
                        <p class="text-sm text-emerald-700 mt-0.5">
                            Your customers can now pay online. Payments go straight to your PayMongo account.
                        </p>
                        <p v-if="maskedAccountId" class="text-xs text-emerald-600 font-mono mt-2 tracking-wide">
                            Account ID: {{ maskedAccountId }}
                        </p>
                    </div>
                </div>

                <!-- Accepted methods -->
                <div class="rounded-2xl border border-gray-100 bg-white p-5 space-y-3">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Accepted Payment Methods</p>
                    <div class="grid grid-cols-2 gap-2">
                        <div v-for="m in ['GCash', 'Maya', 'Credit / Debit Card', 'GrabPay', 'Online Banking']"
                            :key="m"
                            class="flex items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 px-3 py-2">
                            <CheckCircle2 class="h-3.5 w-3.5 text-emerald-500 shrink-0" />
                            <span class="text-xs font-medium text-gray-700">{{ m }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <Button variant="outline" size="sm" @click="showForm = true">
                        Update Connection
                    </Button>
                    <Button variant="outline" size="sm"
                        class="text-red-600 border-red-200 hover:bg-red-50"
                        :disabled="saving"
                        @click="disconnect">
                        <Loader2 v-if="saving" class="h-3.5 w-3.5 mr-1.5 animate-spin" />
                        <Unplug v-else class="h-3.5 w-3.5 mr-1.5" />
                        {{ saving ? 'Disconnecting…' : 'Disconnect Account' }}
                    </Button>
                </div>

            </template>

            <!-- ══ SETUP WIZARD ══ -->
            <template v-else>

                <!-- Not connected banner -->
                <div v-if="!paymongo_configured"
                    class="rounded-2xl border border-amber-200 bg-amber-50 p-4 flex items-start gap-3">
                    <AlertCircle class="h-5 w-5 text-amber-500 shrink-0 mt-0.5" />
                    <p class="text-sm text-amber-700">
                        Your account is not connected yet. Follow the 3 steps below to start accepting online payments.
                    </p>
                </div>

                <!-- Step 1 -->
                <div class="rounded-2xl border border-gray-100 bg-white p-5 space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="h-7 w-7 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold shrink-0">
                            1
                        </div>
                        <p class="font-semibold text-gray-800 text-sm">Open your PayMongo account</p>
                    </div>
                    <p class="text-sm text-gray-500 ml-10">
                        Don't have one yet? Sign up for free — it only takes a few minutes.
                        Your laundry payments will be deposited directly into this account.
                    </p>
                    <div class="ml-10">
                        <a href="https://dashboard.paymongo.com" target="_blank">
                            <Button variant="outline" size="sm" class="gap-1.5">
                                Open PayMongo <ExternalLink class="h-3.5 w-3.5" />
                            </Button>
                        </a>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="rounded-2xl border border-gray-100 bg-white p-5 space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="h-7 w-7 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold shrink-0">
                            2
                        </div>
                        <p class="font-semibold text-gray-800 text-sm">Find your Account Code and Account ID</p>
                    </div>
                    <div class="ml-10 space-y-2 text-sm text-gray-500">
                        <p>Inside your PayMongo dashboard:</p>
                        <ol class="space-y-1.5 ml-4">
                            <li class="flex items-start gap-2">
                                <span class="font-semibold text-gray-700 shrink-0">①</span>
                                Click <span class="font-medium text-gray-700">Developers</span> on the left menu
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="font-semibold text-gray-700 shrink-0">②</span>
                                Click <span class="font-medium text-gray-700">API Keys</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="font-semibold text-gray-700 shrink-0">③</span>
                                You will see two codes — copy both and paste them in Step 3
                            </li>
                        </ol>
                    </div>
                    <div class="ml-10">
                        <a href="https://dashboard.paymongo.com/developers" target="_blank">
                            <Button variant="outline" size="sm" class="gap-1.5">
                                Go to Developers section <ExternalLink class="h-3.5 w-3.5" />
                            </Button>
                        </a>
                    </div>
                </div>

                <!-- Step 3 — Input -->
                <div class="rounded-2xl border border-blue-200 bg-blue-50/50 p-5 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="h-7 w-7 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold shrink-0">
                            3
                        </div>
                        <p class="font-semibold text-gray-800 text-sm">Paste your codes below</p>
                    </div>

                    <div class="ml-10 space-y-4">

                        <!-- Account Code (secret key) -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-gray-700 flex items-center gap-1.5">
                                <Copy class="h-3.5 w-3.5 text-gray-400" />
                                Account Code
                                <span class="text-gray-400 font-normal">(the longer code starting with <span class="font-mono">sk_</span>)</span>
                            </label>
                            <div class="relative">
                                <Input
                                    v-model="accountCode"
                                    :type="showCode ? 'text' : 'password'"
                                    placeholder="Paste your Account Code here…"
                                    class="h-10 text-sm pr-10"
                                />
                                <button type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                    @click="showCode = !showCode">
                                    <EyeOff v-if="showCode" class="h-4 w-4" />
                                    <Eye v-else class="h-4 w-4" />
                                </button>
                            </div>
                            <p class="text-xs text-gray-400">This code is private — keep it safe like a password.</p>
                        </div>

                        <!-- Account ID (public key) -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-gray-700 flex items-center gap-1.5">
                                <Copy class="h-3.5 w-3.5 text-gray-400" />
                                Account ID
                                <span class="text-gray-400 font-normal">(the other code starting with <span class="font-mono">pk_</span>)</span>
                            </label>
                            <Input
                                v-model="accountId"
                                placeholder="Paste your Account ID here…"
                                class="h-10 text-sm"
                            />
                        </div>

                        <div class="flex gap-3 pt-1">
                            <Button :disabled="saving" class="gap-2 bg-blue-600 hover:bg-blue-700 text-white" @click="connect">
                                <Loader2 v-if="saving" class="h-4 w-4 animate-spin" />
                                <Plug v-else class="h-4 w-4" />
                                {{ saving ? 'Connecting your account…' : 'Connect My Account' }}
                            </Button>
                            <Button v-if="paymongo_configured" variant="outline" @click="showForm = false">
                                Cancel
                            </Button>
                        </div>

                        <p class="text-xs text-gray-400">
                            We'll verify your account automatically. If there's an issue, we'll let you know right away.
                        </p>
                    </div>
                </div>

            </template>

        </div>
    </ShopLayout>
</template>
