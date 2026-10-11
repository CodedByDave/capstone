<script setup lang="ts">
import { Blocks, Rocket, Store, UserPlus } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const steps = [
    {
        icon: UserPlus,
        number: '01',
        title: 'Create Your Account',
        description:
            'Sign up in minutes with your email. No credit card required to get started.',
    },
    {
        icon: Store,
        number: '02',
        title: 'Register Your Shop',
        description:
            'Add your shop details -- name, location, operating hours, and service areas.',
    },
    {
        icon: Blocks,
        number: '03',
        title: 'Choose Your Modules',
        description:
            'Pick only the tools you need: employee management, inventory, orders, pricing, or analytics.',
    },
    {
        icon: Rocket,
        number: '04',
        title: 'Go Live',
        description:
            'Start managing your business and serving customers from your personalized dashboard.',
    },
];

const visibleItems = ref<Record<string, boolean>>({});
const headerRef = ref<HTMLElement | null>(null);
const imageRef = ref<HTMLElement | null>(null);
const stepRefs = ref<HTMLElement[]>([]);

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const key = (entry.target as HTMLElement).dataset.key;
                    if (key) visibleItems.value[key] = true;
                }
            });
        },
        { threshold: 0.15 },
    );

    if (headerRef.value) {
        headerRef.value.dataset.key = 'header';
        observer.observe(headerRef.value);
    }
    if (imageRef.value) {
        imageRef.value.dataset.key = 'image';
        observer.observe(imageRef.value);
    }
    stepRefs.value.forEach((el, i) => {
        if (el) {
            el.dataset.key = `step-${i}`;
            observer.observe(el);
        }
    });
});
</script>

<template>
    <section id="how-it-works" class="bg-background py-24 lg:py-32">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-16 lg:grid-cols-2 lg:gap-20">
                <!-- LEFT IMAGE -->
                <div
                    ref="imageRef"
                    :class="[
                        'relative order-2 transition-all duration-1000 lg:order-1',
                        visibleItems['image']
                            ? 'translate-x-0 opacity-100'
                            : '-translate-x-12 opacity-0',
                    ]"
                >
                    <div
                        class="relative aspect-[4/5] overflow-hidden rounded-3xl"
                    >
                        <img
                            src="/assets/images/shop-setup.jpg"
                            alt="LaundryHub shop setup"
                            class="h-full w-full object-cover"
                        />
                    </div>
                    <div
                        class="animate-float absolute -right-2 -bottom-6 rounded-2xl border border-border bg-card p-4 shadow-xl sm:-right-4 sm:p-5 lg:-right-6"
                    >
                        <p
                            class="font-serif text-2xl font-bold text-primary sm:text-3xl"
                        >
                            5 min
                        </p>
                        <p class="text-xs text-muted-foreground sm:text-sm">
                            Setup time
                        </p>
                    </div>
                </div>

                <!-- RIGHT SIDE -->
                <div class="order-1 lg:order-2">
                    <div
                        ref="headerRef"
                        :class="[
                            'transition-all duration-700',
                            visibleItems['header']
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0',
                        ]"
                    >
                        <p
                            class="text-sm font-semibold tracking-widest text-primary uppercase"
                        >
                            How It Works
                        </p>
                        <h2
                            class="mt-3 font-serif text-3xl font-bold tracking-tight text-balance text-foreground sm:text-4xl sm:text-5xl"
                        >
                            Get your shop online in 4 steps
                        </h2>
                    </div>

                    <div class="mt-10 flex flex-col gap-6 sm:mt-12 sm:gap-8">
                        <div
                            v-for="(step, index) in steps"
                            :key="step.number"
                            :ref="
                                (el) => {
                                    if (el) stepRefs[index] = el as HTMLElement;
                                }
                            "
                            :class="[
                                'flex items-start gap-4 transition-all duration-700 sm:gap-5',
                                visibleItems[`step-${index}`]
                                    ? 'translate-x-0 opacity-100'
                                    : 'translate-x-8 opacity-0',
                            ]"
                            :style="{ transitionDelay: index * 120 + 'ms' }"
                        >
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary sm:h-12 sm:w-12"
                            >
                                <component :is="step.icon" class="h-5 w-5" />
                            </div>
                            <div>
                                <div class="flex items-center gap-3">
                                    <span
                                        class="text-xs font-bold tracking-wider text-primary"
                                        >{{ step.number }}</span
                                    >
                                    <h3
                                        class="text-base font-semibold text-foreground sm:text-lg"
                                    >
                                        {{ step.title }}
                                    </h3>
                                </div>
                                <p
                                    class="mt-1.5 text-sm leading-relaxed text-muted-foreground"
                                >
                                    {{ step.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
