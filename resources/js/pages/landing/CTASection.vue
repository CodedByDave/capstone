<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

function scrollToPricing() {
    document.getElementById('modules')?.scrollIntoView({ behavior: 'smooth' });
}

const sectionRef = ref<HTMLElement | null>(null);
const isVisible = ref(false);

onMounted(() => {
    const observer = new IntersectionObserver(
        ([entry]) => {
            if (entry.isIntersecting) isVisible.value = true;
        },
        { threshold: 0.2 },
    );
    if (sectionRef.value) observer.observe(sectionRef.value);
});
</script>

<template>
    <section ref="sectionRef" class="bg-primary py-24 lg:py-32">
        <div
            :class="[
                'mx-auto max-w-4xl px-4 text-center transition-all duration-1000 sm:px-6',
                isVisible
                    ? 'translate-y-0 scale-100 opacity-100'
                    : 'translate-y-10 scale-95 opacity-0',
            ]"
        >
            <h2
                class="font-serif text-3xl font-bold tracking-tight text-balance text-primary-foreground sm:text-4xl sm:text-5xl lg:text-6xl"
            >
                Ready to digitize your laundry business?
            </h2>
            <p
                class="mx-auto mt-6 max-w-2xl text-base leading-relaxed text-primary-foreground/80 sm:text-lg"
            >
                Join over 500 shop owners who are already managing their
                business smarter with LaundryHub. Get started in under 5
                minutes.
            </p>
            <div
                class="mt-10 flex flex-col items-center gap-3 sm:flex-row sm:justify-center"
            >
                <Button
                    @click="router.visit('/trial')"
                    size="lg"
                    class="group w-full bg-white px-8 py-6 text-base text-blue-600 shadow-sm hover:bg-blue-50 sm:w-auto"
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
                    class="w-full border-white bg-transparent px-8 py-6 text-base text-white hover:bg-white/10 sm:w-auto"
                >
                    See Pricing
                </Button>
            </div>
        </div>
    </section>
</template>
