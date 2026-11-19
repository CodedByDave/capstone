<script setup lang="ts">
import { textLinkClass } from '@/components/auth/authStyles';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, WashingMachine } from 'lucide-vue-next';

export interface LegalSection {
    heading: string;
    body: string[];
}

defineProps<{
    title: string;
    updated: string;
    intro: string;
    sections: LegalSection[];
}>();
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen bg-slate-50 text-slate-700">
        <header class="border-b border-slate-200 bg-white">
            <div
                class="mx-auto flex max-w-3xl items-center justify-between px-4 py-4 sm:px-6"
            >
                <Link
                    href="/"
                    class="flex items-center gap-2 rounded font-bold text-slate-900 focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:outline-none"
                >
                    <WashingMachine class="size-5 text-blue-600" />
                    LaundryHub
                </Link>
                <Link href="/login" :class="[textLinkClass, 'text-sm']">
                    <ArrowLeft class="mr-1 inline size-4" aria-hidden="true" />
                    Back to sign in
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
            <article
                class="rounded-[14px] border border-slate-200 bg-white p-6 sm:p-10"
            >
                <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">
                    {{ title }}
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Last updated {{ updated }}
                </p>
                <p class="mt-6 text-[15px] leading-relaxed">{{ intro }}</p>

                <section
                    v-for="(section, i) in sections"
                    :key="section.heading"
                    class="mt-8"
                >
                    <h2 class="text-lg font-bold text-slate-900">
                        {{ i + 1 }}. {{ section.heading }}
                    </h2>
                    <p
                        v-for="paragraph in section.body"
                        :key="paragraph"
                        class="mt-2 text-[15px] leading-relaxed"
                    >
                        {{ paragraph }}
                    </p>
                </section>
            </article>
        </main>
    </div>
</template>
