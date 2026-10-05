<script setup lang="ts">
import { computed } from 'vue';

interface Agreement {
    title: string;
    version: string;
    content: string;
    effective_at?: string;
}

interface Acceptance {
    business_name?: string;
    signer_name: string;
    signer_role: string;
    accepted_at?: string;
    signature_url?: string | null;
    execution_status?: 'awaiting_platform_signature' | 'fully_executed';
    platform_signature?: {
        signer_name: string;
        signer_role: string;
        signed_at: string;
        signature_url: string;
    } | null;
}

const props = defineProps<{
    agreement: Agreement;
    businessName?: string;
    acceptance?: Acceptance | null;
}>();

const clauses = computed(() => {
    return props.agreement.content
        .split(/\n\s*\n/)
        .map((paragraph) => paragraph.trim())
        .filter(Boolean)
        .filter((paragraph) => paragraph !== props.agreement.title)
        .map((paragraph) => {
            const match = paragraph.match(
                /^(\d+\.\s+[A-Z][A-Z &/()-]+)\n([\s\S]+)$/,
            );

            return match
                ? { heading: match[1], body: match[2] }
                : { heading: null, body: paragraph };
        });
});

function formatDate(value?: string) {
    if (!value) return 'Not specified';

    return new Date(value).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}
</script>

<template>
    <article
        class="mx-auto w-full max-w-4xl border border-stone-300 bg-white px-6 py-8 text-stone-900 shadow-sm sm:px-10 sm:py-12"
    >
        <header class="border-b-2 border-stone-900 pb-6 text-center">
            <p
                class="text-xs font-semibold tracking-[0.24em] text-stone-500 uppercase"
            >
                LaundryHub
            </p>
            <h2 class="mt-3 text-xl font-bold tracking-tight sm:text-2xl">
                {{ agreement.title }}
            </h2>
            <div
                class="mt-3 flex flex-wrap justify-center gap-x-5 gap-y-1 text-xs text-stone-500"
            >
                <span>Agreement version {{ agreement.version }}</span>
                <span>Effective {{ formatDate(agreement.effective_at) }}</span>
            </div>
        </header>

        <section class="mt-7 border-y border-stone-300 py-4 text-sm leading-6">
            <p><strong>Platform:</strong> LaundryHub</p>
            <p>
                <strong>Business:</strong>
                {{
                    businessName || 'The business identified in the application'
                }}
            </p>
        </section>

        <section class="mt-7 space-y-6 text-sm leading-7 text-stone-800">
            <div v-for="(clause, index) in clauses" :key="index">
                <h3
                    v-if="clause.heading"
                    class="mb-1 font-bold tracking-wide text-stone-950"
                >
                    {{ clause.heading }}
                </h3>
                <p class="text-justify whitespace-pre-line">
                    {{ clause.body }}
                </p>
            </div>
        </section>

        <section
            v-if="acceptance"
            class="mt-10 border-t-2 border-stone-900 pt-6"
        >
            <p
                class="text-xs font-semibold tracking-widest text-stone-500 uppercase"
            >
                Signatures
            </p>
            <div class="mt-5 grid gap-10 sm:grid-cols-2">
                <div>
                    <div
                        class="flex h-24 items-end border-b border-stone-800 pb-2"
                    >
                        <img
                            v-if="acceptance.signature_url"
                            :src="acceptance.signature_url"
                            alt="Owner signature"
                            class="max-h-20 max-w-full object-contain"
                        />
                    </div>
                    <p class="mt-2 text-sm font-semibold">
                        {{ acceptance.signer_name }}
                    </p>
                    <p class="text-xs text-stone-500">
                        {{ acceptance.signer_role }}
                    </p>
                    <p class="mt-1 text-xs text-stone-500">
                        For
                        {{
                            acceptance.business_name ||
                            businessName ||
                            'Business'
                        }}
                    </p>
                </div>
                <div>
                    <div
                        class="flex h-24 items-end border-b border-stone-800 pb-2"
                    >
                        <img
                            v-if="acceptance.platform_signature?.signature_url"
                            :src="acceptance.platform_signature.signature_url"
                            alt="LaundryHub representative signature"
                            class="max-h-20 max-w-full object-contain"
                        />
                        <span v-else class="pb-2 text-xs text-stone-400">
                            Awaiting platform signature
                        </span>
                    </div>
                    <template v-if="acceptance.platform_signature">
                        <p class="mt-2 text-sm font-semibold">
                            {{ acceptance.platform_signature.signer_name }}
                        </p>
                        <p class="text-xs text-stone-500">
                            {{ acceptance.platform_signature.signer_role }}
                        </p>
                        <p class="mt-1 text-xs text-stone-500">
                            For LaundryHub
                        </p>
                    </template>
                </div>
            </div>
            <div
                class="mt-6 grid gap-2 border-t border-stone-300 pt-4 text-xs text-stone-500 sm:grid-cols-2"
            >
                <p>Business signed: {{ formatDate(acceptance.accepted_at) }}</p>
                <p v-if="acceptance.platform_signature">
                    Platform signed:
                    {{ formatDate(acceptance.platform_signature.signed_at) }}
                </p>
            </div>
        </section>
    </article>
</template>
