<script setup lang="ts">
import AgreementSignatureInput from '@/components/business/AgreementSignatureInput.vue';
import BusinessAgreementDocument from '@/components/business/BusinessAgreementDocument.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { downloadBusinessAgreementPdf } from '@/lib/businessAgreementPdf';
import { Head, useForm } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Clock3,
    Download,
    Loader2,
    ShieldCheck,
} from 'lucide-vue-next';
import { ref } from 'vue';

interface Acceptance {
    public_id: string;
    business_name: string;
    signer_name: string;
    signer_role: string;
    accepted_at: string;
    content_hash: string;
    signature_method: 'drawn' | 'uploaded' | null;
    signature_url: string | null;
    execution_status: 'awaiting_platform_signature' | 'fully_executed';
    platform_signature: {
        public_id: string;
        signer_name: string;
        signer_role: string;
        signed_at: string;
        signature_method: 'drawn' | 'uploaded';
        signature_url: string;
    } | null;
    agreement: {
        title: string;
        version: string;
        content: string;
        effective_at: string;
    };
}

interface CurrentAgreement {
    public_id: string;
    title: string;
    version: string;
    content: string;
    content_hash: string;
    effective_at: string;
    accepted: boolean;
    acceptance: Acceptance | null;
}

const props = defineProps<{
    currentAgreement: CurrentAgreement;
    history: Acceptance[];
}>();

const form = useForm({
    agreement_public_id: props.currentAgreement.public_id,
    signer_name: '',
    signer_role: 'Owner',
    signature_image: null as File | null,
    signature_method: 'drawn' as 'drawn' | 'uploaded',
    signer_authority_confirmed: false,
    agreement_accepted: false,
});

const downloadingId = ref<string | null>(null);

function formatDate(value: string) {
    return new Date(value).toLocaleString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function acceptAgreement() {
    form.post('/shop/agreement', {
        preserveScroll: true,
        forceFormData: true,
    });
}

async function downloadAgreement(acceptance: Acceptance) {
    if (downloadingId.value) return;

    downloadingId.value = acceptance.public_id;

    try {
        await downloadBusinessAgreementPdf(acceptance);
    } finally {
        downloadingId.value = null;
    }
}
</script>

<template>
    <Head title="Legal Documents" />

    <ShopLayout title="Legal Documents">
        <div class="mx-auto max-w-5xl space-y-6 p-4 sm:p-6">
            <header>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Legal Documents
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Review, execute, and download the agreement between
                    LaundryHub and your business.
                </p>
            </header>

            <BusinessAgreementDocument
                :agreement="currentAgreement"
                :business-name="currentAgreement.acceptance?.business_name"
                :acceptance="currentAgreement.acceptance"
            />

            <section
                v-if="currentAgreement.acceptance"
                class="border-y px-5 py-5"
                :class="
                    currentAgreement.acceptance.execution_status ===
                    'fully_executed'
                        ? 'border-emerald-300 bg-emerald-50/70'
                        : 'border-amber-300 bg-amber-50/70'
                "
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-start gap-3">
                        <CheckCircle2
                            v-if="
                                currentAgreement.acceptance.execution_status ===
                                'fully_executed'
                            "
                            class="mt-0.5 h-5 w-5 shrink-0 text-emerald-700"
                        />
                        <Clock3
                            v-else
                            class="mt-0.5 h-5 w-5 shrink-0 text-amber-700"
                        />
                        <div>
                            <p
                                class="font-semibold"
                                :class="
                                    currentAgreement.acceptance
                                        .execution_status === 'fully_executed'
                                        ? 'text-emerald-950'
                                        : 'text-amber-950'
                                "
                            >
                                {{
                                    currentAgreement.acceptance
                                        .execution_status === 'fully_executed'
                                        ? 'Agreement fully executed'
                                        : 'Awaiting platform signature'
                                }}
                            </p>
                            <p
                                class="mt-1 text-sm"
                                :class="
                                    currentAgreement.acceptance
                                        .execution_status === 'fully_executed'
                                        ? 'text-emerald-800'
                                        : 'text-amber-800'
                                "
                            >
                                <template
                                    v-if="
                                        currentAgreement.acceptance
                                            .platform_signature
                                    "
                                >
                                    LaundryHub countersigned through
                                    {{
                                        currentAgreement.acceptance
                                            .platform_signature.signer_name
                                    }}
                                    on
                                    {{
                                        formatDate(
                                            currentAgreement.acceptance
                                                .platform_signature.signed_at,
                                        )
                                    }}.
                                </template>
                                <template v-else>
                                    Your signature was recorded. LaundryHub will
                                    countersign when the order is approved.
                                </template>
                            </p>
                        </div>
                    </div>
                    <Button
                        v-if="
                            currentAgreement.acceptance.execution_status ===
                            'fully_executed'
                        "
                        variant="outline"
                        class="gap-2"
                        :disabled="downloadingId !== null"
                        @click="downloadAgreement(currentAgreement.acceptance)"
                    >
                        <Loader2
                            v-if="
                                downloadingId ===
                                currentAgreement.acceptance.public_id
                            "
                            class="h-4 w-4 animate-spin"
                        />
                        <Download v-else class="h-4 w-4" />
                        Download signed PDF
                    </Button>
                </div>
            </section>

            <form
                v-else
                class="space-y-5 border-y border-stone-300 bg-white px-5 py-6"
                @submit.prevent="acceptAgreement"
            >
                <div>
                    <h2 class="text-base font-semibold text-stone-950">
                        Execute this agreement
                    </h2>
                    <p class="mt-1 text-sm text-stone-500">
                        The printed name and owner signature become part of the
                        permanent agreement record.
                    </p>
                </div>

                <div class="flex items-start gap-2 text-sm text-blue-800">
                    <ShieldCheck class="mt-0.5 h-4 w-4 shrink-0" />
                    Only the business owner or an authorized representative may
                    sign this agreement.
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <Label class="mb-1.5">Printed full name</Label>
                        <Input
                            v-model="form.signer_name"
                            placeholder="Full legal name"
                        />
                        <p
                            v-if="form.errors.signer_name"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.signer_name }}
                        </p>
                    </div>
                    <div>
                        <Label class="mb-1.5">Signing capacity</Label>
                        <select
                            v-model="form.signer_role"
                            class="w-full rounded-md border bg-background px-3 py-2 text-sm"
                        >
                            <option>Owner</option>
                            <option>Authorized Representative</option>
                        </select>
                    </div>
                </div>

                <AgreementSignatureInput
                    v-model="form.signature_image"
                    :disabled="form.processing"
                    :error="form.errors.signature_image"
                    @update:method="form.signature_method = $event"
                />

                <div class="space-y-3">
                    <label
                        class="flex cursor-pointer items-start gap-2 text-sm"
                    >
                        <input
                            v-model="form.signer_authority_confirmed"
                            type="checkbox"
                            class="mt-1 accent-blue-600"
                        />
                        <span
                            >I confirm that I am authorized to bind this
                            business.</span
                        >
                    </label>
                    <label
                        class="flex cursor-pointer items-start gap-2 text-sm"
                    >
                        <input
                            v-model="form.agreement_accepted"
                            type="checkbox"
                            class="mt-1 accent-blue-600"
                        />
                        <span
                            >I have read, signed, and accept this
                            Platform–Business Services Agreement.</span
                        >
                    </label>
                </div>
                <p
                    v-if="form.errors.agreement_accepted"
                    class="text-xs text-red-600"
                >
                    {{ form.errors.agreement_accepted }}
                </p>

                <Button
                    type="submit"
                    :disabled="
                        form.processing ||
                        !form.signature_image ||
                        !form.signer_authority_confirmed ||
                        !form.agreement_accepted ||
                        form.signer_name.trim().length < 2
                    "
                >
                    <Loader2
                        v-if="form.processing"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    Sign and accept agreement
                </Button>
            </form>

            <Card v-if="history.length">
                <CardHeader>
                    <CardTitle>Acceptance History</CardTitle>
                </CardHeader>
                <CardContent class="divide-y">
                    <div
                        v-for="acceptance in history"
                        :key="acceptance.public_id"
                        class="flex flex-col gap-3 py-4 first:pt-0 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p class="font-medium">
                                Version {{ acceptance.agreement.version }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                {{ acceptance.signer_name }} ·
                                {{ acceptance.signer_role }} ·
                                {{ formatDate(acceptance.accepted_at) }}
                            </p>
                        </div>
                        <Button
                            v-if="
                                acceptance.execution_status === 'fully_executed'
                            "
                            variant="outline"
                            size="sm"
                            class="gap-2"
                            :disabled="downloadingId !== null"
                            @click="downloadAgreement(acceptance)"
                        >
                            <Loader2
                                v-if="downloadingId === acceptance.public_id"
                                class="h-4 w-4 animate-spin"
                            />
                            <Download v-else class="h-4 w-4" /> PDF
                        </Button>
                        <span
                            v-else
                            class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700"
                        >
                            <Clock3 class="h-4 w-4" /> Awaiting platform
                            signature
                        </span>
                    </div>
                </CardContent>
            </Card>
        </div>
    </ShopLayout>
</template>
