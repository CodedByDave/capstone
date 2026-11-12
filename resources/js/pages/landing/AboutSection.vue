<script setup lang="ts">
import { Bike, ShieldCheck, ShoppingBag, Store } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const audiences = [
    {
        icon: Store,
        title: 'For shop owners',
        description:
            'Run orders, staff, inventory, and finances from one dashboard, and see what needs your attention today.',
    },
    {
        icon: ShoppingBag,
        title: 'For customers',
        description:
            'Find a nearby shop, book a pickup or drop-off, approve the weight, and pay online or in cash.',
    },
    {
        icon: Bike,
        title: 'For riders',
        description:
            'Get pickup and delivery runs on your phone, with turn-by-turn directions to each stop.',
    },
];

const visible = ref<Record<string, boolean>>({});
const sectionRef = ref<HTMLElement | null>(null);

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                const key = (entry.target as HTMLElement).dataset.key;
                if (entry.isIntersecting && key) visible.value[key] = true;
            });
        },
        { threshold: 0.15 },
    );

    sectionRef.value
        ?.querySelectorAll<HTMLElement>('[data-key]')
        .forEach((el) => observer.observe(el));
});
</script>

<template>
    <section
        id="about"
        ref="sectionRef"
        class="scroll-mt-20 bg-background py-24 lg:py-32"
        aria-labelledby="about-heading"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-2 lg:gap-20">
                <div
                    data-key="intro"
                    :class="[
                        'transition-all duration-700',
                        visible.intro
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-8 opacity-0',
                    ]"
                >
                    <p
                        class="text-sm font-semibold tracking-widest text-primary uppercase"
                    >
                        About LaundryHub
                    </p>
                    <h2
                        id="about-heading"
                        class="mt-3 font-serif text-3xl font-bold tracking-tight text-balance text-foreground sm:text-4xl sm:text-5xl"
                    >
                        One platform for the shop, the customer, and the rider
                    </h2>
                    <p
                        class="mt-6 text-base leading-relaxed text-muted-foreground"
                    >
                        LaundryHub is a web-based, modular platform built for
                        laundry shops in Cavite. It replaces notebooks, chat
                        threads, and spreadsheets with one connected system, so
                        an order placed by a customer reaches the shop, the
                        rider, and the books without being written down twice.
                    </p>
                    <p
                        class="mt-4 text-base leading-relaxed text-muted-foreground"
                    >
                        Shops pick only the modules they need and can upgrade as
                        they grow. It runs in any browser and can be installed
                        on a phone like an app.
                    </p>

                    <div
                        class="mt-8 flex items-start gap-3 rounded-2xl border border-border bg-card p-4"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"
                        >
                            <ShieldCheck class="h-5 w-5" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">
                                Every shop is verified
                            </p>
                            <p
                                class="mt-1 text-sm leading-relaxed text-muted-foreground"
                            >
                                Owners submit their BIR, DTI, and Mayor's
                                permit. Our team checks them before a shop can
                                take orders, and the documents are stored
                                encrypted.
                            </p>
                        </div>
                    </div>
                </div>

                <ul class="flex flex-col gap-4 sm:gap-5 lg:justify-center">
                    <li
                        v-for="(item, index) in audiences"
                        :key="item.title"
                        :data-key="`audience-${index}`"
                        :class="[
                            'flex items-start gap-4 rounded-2xl border border-border bg-card p-5 transition-all duration-700 sm:p-6',
                            visible[`audience-${index}`]
                                ? 'translate-x-0 opacity-100'
                                : 'translate-x-8 opacity-0',
                        ]"
                        :style="{ transitionDelay: index * 120 + 'ms' }"
                    >
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary sm:h-12 sm:w-12"
                        >
                            <component
                                :is="item.icon"
                                class="h-5 w-5"
                                aria-hidden="true"
                            />
                        </div>
                        <div>
                            <h3
                                class="text-base font-semibold text-foreground sm:text-lg"
                            >
                                {{ item.title }}
                            </h3>
                            <p
                                class="mt-1.5 text-sm leading-relaxed text-muted-foreground"
                            >
                                {{ item.description }}
                            </p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </section>
</template>
