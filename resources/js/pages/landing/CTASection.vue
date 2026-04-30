<script setup lang="ts">
import { ref, onMounted } from "vue"
import { Button } from "@/components/ui/button"
import { ArrowRight } from "lucide-vue-next"
import { Form, Head, router } from '@inertiajs/vue3';

const sectionRef = ref<HTMLElement | null>(null)
const isVisible = ref(false)

onMounted(() => {
    const observer = new IntersectionObserver(
        ([entry]) => { if (entry.isIntersecting) isVisible.value = true },
        { threshold: 0.2 }
    )
    if (sectionRef.value) observer.observe(sectionRef.value)
})
</script>

<template>
    <section ref="sectionRef" class="py-24 lg:py-32 bg-primary">
        <div :class="[
            'mx-auto max-w-4xl px-4 sm:px-6 text-center transition-all duration-1000',
            isVisible ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 translate-y-10 scale-95'
        ]">
            <h2
                class="font-serif text-3xl sm:text-4xl font-bold tracking-tight text-primary-foreground sm:text-5xl lg:text-6xl text-balance">
                Ready to digitize your laundry business?
            </h2>
            <p class="mx-auto mt-6 max-w-2xl text-base sm:text-lg leading-relaxed text-primary-foreground/80">
                Join over 500 shop owners who are already managing their business
                smarter with LaundryHub. Get started in under 5 minutes.
            </p>
            <div class="mt-10 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">

                <Button @click="router.visit('/trial')" size="lg"
                    class="w-full sm:w-auto bg-white text-blue-600 hover:bg-blue-50 px-8 py-6 text-base shadow-sm group">
                    Start Free Trial
                    <ArrowRight class="ml-2 h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" />
                </Button>

                <Button @click="router.visit('/plans')" variant="outline" size="lg"
                    class="w-full sm:w-auto border-white text-white hover:bg-white/10 px-8 py-6 text-base bg-transparent">
                    See Pricing
                </Button>

            </div>

        </div>
    </section>
</template>
