<script setup lang="ts">
import AgreementSignatureInput from '@/components/business/AgreementSignatureInput.vue';
import BusinessAgreementDocument from '@/components/business/BusinessAgreementDocument.vue';
import PhilippinePhoneInput from '@/components/PhilippinePhoneInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    Clock,
    Loader2,
    ShieldCheck,
} from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    trialDays: number;
    user: { name: string; email: string };
    shop: {
        shop_name: string;
        phone: string;
        block_street: string;
        municipality: string;
        barangay: string;
        postal_code: string;
    };
    agreement: {
        public_id: string;
        title: string;
        version: string;
        content: string;
        accepted: boolean;
        effective_at?: string;
        acceptance?: {
            business_name: string;
            signer_name: string;
            signer_role: string;
            accepted_at: string;
            signature_url?: string | null;
        } | null;
    };
}>();

const form = useForm({
    owner_name: props.user.name,
    email: props.user.email,
    shop_name: props.shop.shop_name,
    phone: props.shop.phone,
    block_street: props.shop.block_street,
    municipality: props.shop.municipality,
    barangay: props.shop.barangay,
    postal_code: props.shop.postal_code,
    agreement_public_id: props.agreement.public_id,
    signer_name: props.agreement.acceptance?.signer_name ?? props.user.name,
    signer_role: props.agreement.acceptance?.signer_role ?? 'Owner',
    signer_authority_confirmed: props.agreement.accepted,
    agreement_accepted: props.agreement.accepted,
    signature_image: null as File | null,
    signature_method: 'drawn' as 'drawn' | 'uploaded',
});

const TRIAL_MODULES = [
    'HRM',
    'Operations',
    'Inventory Management',
    'Finance Management',
];

const canSubmit = computed(
    () =>
        form.signer_name.trim().length >= 2 &&
        form.signer_authority_confirmed &&
        form.agreement_accepted &&
        (props.agreement.accepted || form.signature_image !== null),
);

const submit = () => {
    if (canSubmit.value) form.post('/trial');
};
</script>

<template>
    <Head title="Start Free Trial" />

    <div
        class="flex min-h-screen items-start justify-center bg-gray-50 p-4 py-12"
    >
        <div class="w-full max-w-4xl">
            <!-- Header -->
            <div class="mb-8 text-center">
                <div
                    class="mb-4 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-1.5 text-sm font-medium text-emerald-700"
                >
                    <Clock class="h-3.5 w-3.5" />
                    {{ trialDays }}-Day Free Trial
                </div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Set up your shop
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    No credit card required. Cancel anytime.
                </p>
            </div>

            <!-- What's included -->
            <div
                class="mb-6 rounded-xl border border-emerald-100 bg-emerald-50 px-5 py-4"
            >
                <p
                    class="mb-3 text-xs font-semibold tracking-widest text-emerald-700 uppercase"
                >
                    What's included in your trial
                </p>
                <ul class="space-y-1.5">
                    <li
                        v-for="mod in TRIAL_MODULES"
                        :key="mod"
                        class="flex items-center gap-2 text-sm text-emerald-800"
                    >
                        <CheckCircle2
                            class="h-4 w-4 shrink-0 text-emerald-500"
                        />
                        {{ mod }}
                    </li>
                </ul>
            </div>

            <div>
                <form @submit.prevent="submit" class="space-y-5">
                    <section
                        class="space-y-5 border border-gray-200 bg-white p-6 shadow-sm"
                    >
                        <!-- Account info (read-only) -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <Label>Owner Name</Label>
                                <Input
                                    v-model="form.owner_name"
                                    readonly
                                    class="cursor-not-allowed bg-gray-50"
                                />
                            </div>
                            <div class="space-y-1.5">
                                <Label>Email</Label>
                                <Input
                                    v-model="form.email"
                                    readonly
                                    class="cursor-not-allowed bg-gray-50"
                                />
                            </div>
                        </div>

                        <div class="border-t" />

                        <!-- Shop name -->
                        <div class="space-y-1.5">
                            <Label for="shop_name"
                                >Shop Name
                                <span class="text-red-500">*</span></Label
                            >
                            <Input
                                id="shop_name"
                                v-model="form.shop_name"
                                placeholder="e.g. QuickWash Laundry"
                                :class="{
                                    'border-red-400': form.errors.shop_name,
                                }"
                            />
                            <p
                                v-if="form.errors.shop_name"
                                class="text-xs text-red-500"
                            >
                                {{ form.errors.shop_name }}
                            </p>
                        </div>

                        <!-- Phone -->
                        <div class="space-y-1.5">
                            <Label for="phone"
                                >Phone Number
                                <span class="text-red-500">*</span></Label
                            >
                            <PhilippinePhoneInput
                                id="phone"
                                v-model="form.phone"
                                :class="{ 'border-red-400': form.errors.phone }"
                            />
                            <p
                                v-if="form.errors.phone"
                                class="text-xs text-red-500"
                            >
                                {{ form.errors.phone }}
                            </p>
                        </div>

                        <!-- Address -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <Label for="municipality"
                                    >Municipality / City
                                    <span class="text-red-500">*</span></Label
                                >
                                <Input
                                    id="municipality"
                                    v-model="form.municipality"
                                    placeholder="e.g. Dasmariñas"
                                    :class="{
                                        'border-red-400':
                                            form.errors.municipality,
                                    }"
                                />
                                <p
                                    v-if="form.errors.municipality"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.municipality }}
                                </p>
                            </div>
                            <div class="space-y-1.5">
                                <Label for="barangay"
                                    >Barangay
                                    <span class="text-red-500">*</span></Label
                                >
                                <Input
                                    id="barangay"
                                    v-model="form.barangay"
                                    placeholder="e.g. Salawag"
                                    :class="{
                                        'border-red-400': form.errors.barangay,
                                    }"
                                />
                                <p
                                    v-if="form.errors.barangay"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.barangay }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <Label for="block_street"
                                    >Block / Street
                                    <span class="text-xs text-muted-foreground"
                                        >(optional)</span
                                    ></Label
                                >
                                <Input
                                    id="block_street"
                                    v-model="form.block_street"
                                    placeholder="e.g. Block 5 Lot 2"
                                />
                            </div>
                            <div class="space-y-1.5">
                                <Label for="postal_code"
                                    >Postal Code
                                    <span class="text-red-500">*</span></Label
                                >
                                <Input
                                    id="postal_code"
                                    v-model="form.postal_code"
                                    placeholder="e.g. 4114"
                                    :class="{
                                        'border-red-400':
                                            form.errors.postal_code,
                                    }"
                                />
                                <p
                                    v-if="form.errors.postal_code"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.postal_code }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Platform–Business Agreement -->
                    <div class="space-y-5">
                        <BusinessAgreementDocument
                            :agreement="agreement"
                            :business-name="form.shop_name"
                            :acceptance="agreement.acceptance"
                        />

                        <section
                            v-if="!agreement.accepted"
                            class="border-y border-stone-300 bg-white px-6 py-6"
                        >
                            <h2 class="text-base font-semibold text-stone-950">
                                Execute this agreement
                            </h2>
                            <p class="mt-1 text-sm text-stone-500">
                                The printed name and owner signature will become
                                part of the agreement record.
                            </p>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="space-y-1.5">
                                    <Label>Printed full name</Label>
                                    <Input v-model="form.signer_name" />
                                    <p
                                        v-if="form.errors.signer_name"
                                        class="text-xs text-red-500"
                                    >
                                        {{ form.errors.signer_name }}
                                    </p>
                                </div>
                                <div class="space-y-1.5">
                                    <Label>Signing capacity</Label>
                                    <select
                                        v-model="form.signer_role"
                                        class="w-full rounded-md border bg-white px-3 py-2 text-sm"
                                    >
                                        <option>Owner</option>
                                        <option>
                                            Authorized Representative
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <AgreementSignatureInput
                                v-model="form.signature_image"
                                :error="form.errors.signature_image"
                                @update:method="form.signature_method = $event"
                            />

                            <div class="space-y-3">
                                <label
                                    class="flex cursor-pointer items-start gap-2 text-sm text-gray-700"
                                >
                                    <input
                                        v-model="
                                            form.signer_authority_confirmed
                                        "
                                        type="checkbox"
                                        class="mt-1 accent-blue-600"
                                    />
                                    <span
                                        >I am authorized to bind this business
                                        to the agreement.</span
                                    >
                                </label>
                                <label
                                    class="flex cursor-pointer items-start gap-2 text-sm text-gray-700"
                                >
                                    <input
                                        v-model="form.agreement_accepted"
                                        type="checkbox"
                                        class="mt-1 accent-blue-600"
                                    />
                                    <span
                                        >I have read, signed, and accept the
                                        Platform–Business Services
                                        Agreement.</span
                                    >
                                </label>
                            </div>
                            <p
                                v-if="form.errors.agreement_accepted"
                                class="text-xs text-red-500"
                            >
                                {{ form.errors.agreement_accepted }}
                            </p>
                            <div
                                class="flex items-start gap-2 text-xs text-gray-500"
                            >
                                <ShieldCheck
                                    class="mt-0.5 h-3.5 w-3.5 shrink-0"
                                />
                                Acceptance records the agreement version,
                                timestamp, IP address, and browser information.
                            </div>
                        </section>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-2">
                        <Button
                            type="button"
                            variant="outline"
                            class="flex-1"
                            :disabled="form.processing"
                            @click="router.visit('/shop/dashboard')"
                        >
                            <ArrowLeft class="mr-1 h-4 w-4" /> Back
                        </Button>
                        <Button
                            type="submit"
                            class="flex-2 flex-grow"
                            :disabled="form.processing || !canSubmit"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            {{
                                form.processing
                                    ? 'Starting trial...'
                                    : `Start ${trialDays}-Day Free Trial`
                            }}
                        </Button>
                    </div>
                </form>
            </div>

            <p class="mt-4 text-center text-xs text-gray-400">
                Already have an account?
                <a
                    class="cursor-pointer text-blue-600 hover:underline"
                    @click.prevent="router.visit('/login')"
                    >Log in</a
                >
            </p>
        </div>
    </div>
</template>
