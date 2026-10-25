<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';
import { ArrowRight, Blocks } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

function scrollToPricing() {
    document.getElementById('modules')?.scrollIntoView({ behavior: 'smooth' });
}

const isLoaded = ref(false);

onMounted(() => {
    isLoaded.value = true;
});

const props = defineProps<{ shopCount?: number }>();

const stats = computed(() => [
    {
        value: (props.shopCount ?? 0).toLocaleString('en-PH'),
        label: 'Shops Registered',
    },
    { value: '5', label: 'Powerful Modules' },
    { value: '99.9%', label: 'Uptime' },
    { value: '24/7', label: 'Support' },
]);
</script>

<template>
    <section class="relative flex min-h-screen items-center overflow-hidden">
        <!-- Background -->
        <div class="absolute inset-0 z-0">
            <img
                src="/images/hero-platform.jpg"
                alt=""
                class="h-full w-full object-cover"
            />
            <div
                class="absolute inset-0 bg-background/88 backdrop-blur-sm"
            ></div>
            <div
                class="absolute -top-24 -right-24 h-96 w-96 animate-pulse rounded-full bg-primary/5"
            ></div>
            <div
                class="absolute -bottom-32 -left-32 h-[500px] w-[500px] animate-pulse rounded-full bg-primary/5"
                style="animation-delay: 1s"
            ></div>
        </div>

        <div
            class="relative z-10 mx-auto w-full max-w-7xl px-4 pt-28 pb-16 sm:px-6 lg:px-8 lg:pt-40 lg:pb-32"
        >
            <div class="flex flex-col items-center text-center">
                <!-- Badge -->
                <div
                    :class="[
                        'mb-8 inline-flex items-center gap-2 rounded-full border border-border bg-card px-4 py-1.5 text-sm text-muted-foreground transition-all duration-700',
                        isLoaded
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-4 opacity-0',
                    ]"
                >
                    <Blocks class="h-3.5 w-3.5 text-primary" />
                    <span>Modular platform for laundry businesses</span>
                </div>

                <!-- Heading -->
                <h1
                    :class="[
                        'font-serif text-4xl leading-[1.1] font-bold tracking-tight text-balance transition-all delay-150 duration-700 sm:text-6xl lg:text-8xl',
                        'text-gray-900',
                        isLoaded
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-6 opacity-0',
                    ]"
                >
                    Run your laundry
                    <span class="text-blue-600"> shop </span>,
                    <br />
                    your way
                </h1>

                <!-- Subtitle -->
                <p
                    :class="[
                        'mt-6 max-w-2xl px-2 text-base leading-relaxed text-muted-foreground transition-all delay-300 duration-700 sm:text-lg lg:text-xl',
                        isLoaded
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-6 opacity-0',
                    ]"
                >
                    Register your shop, pick only the modules you need, and
                    manage employees, inventory, orders, pricing, and analytics
                    -- all from one platform.
                </p>

                <!-- Buttons -->
                <div
                    :class="[
                        'mt-10 flex flex-col items-center gap-3 transition-all delay-[450ms] duration-700 sm:flex-row',
                        isLoaded
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-6 opacity-0',
                    ]"
                >
                    <Button
                        @click="router.visit('/register/shop')"
                        size="lg"
                        variant="default"
                        class="group w-full !bg-blue-600 px-8 py-6 text-base !text-white !shadow-none transition-all duration-300 ease-in-out hover:-translate-y-1 hover:!bg-blue-500 hover:shadow-lg sm:w-auto"
                    >
                        Start Free Trial
                        <ArrowRight
                            class="ml-2 h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                        />
                    </Button>

                    <Button
                        @click="scrollToPricing"
                        variant="outline"
                        size="lg"
                        class="w-full border-border px-8 py-6 text-base text-foreground hover:bg-secondary sm:w-auto"
                    >
                        See Pricing
                    </Button>
                </div>

                <!-- Stats -->
                <div
                    :class="[
                        'mt-16 grid grid-cols-2 gap-6 transition-all delay-[600ms] duration-700 sm:grid-cols-4 sm:gap-8',
                        isLoaded
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-6 opacity-0',
                    ]"
                >
                    <div
                        v-for="stat in stats"
                        :key="stat.label"
                        class="flex flex-col items-center"
                    >
                        <span
                            class="font-serif text-2xl font-bold text-foreground sm:text-3xl lg:text-4xl"
                        >
                            {{ stat.value }}
                        </span>
                        <span
                            class="mt-1 text-center text-xs text-muted-foreground sm:text-sm"
                        >
                            {{ stat.label }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
