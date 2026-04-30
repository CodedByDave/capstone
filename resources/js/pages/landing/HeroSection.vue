<script setup lang="ts">
import { ref, onMounted } from "vue"
import { Button } from "@/components/ui/button"
import { ArrowRight, Blocks } from "lucide-vue-next"
import { router } from "@inertiajs/vue3"

const isLoaded = ref(false)

onMounted(() => {
    isLoaded.value = true
})

const stats = [
    { value: "500+", label: "Shops Registered" },
    { value: "5", label: "Powerful Modules" },
    { value: "99.9%", label: "Uptime" },
    { value: "24/7", label: "Support" },
]
</script>

<template>
    <section class="relative min-h-screen flex items-center overflow-hidden">

        <!-- Background -->
        <div class="absolute inset-0 z-0">
            <img src="/images/hero-platform.jpg" alt="" class="w-full h-full object-cover" />
            <div class="absolute inset-0 bg-background/88 backdrop-blur-sm"></div>
            <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-primary/5 animate-pulse"></div>
            <div class="absolute -bottom-32 -left-32 h-[500px] w-[500px] rounded-full bg-primary/5 animate-pulse"
                style="animation-delay:1s"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 pt-28 pb-16 lg:px-8 lg:pt-40 lg:pb-32 w-full">
            <div class="flex flex-col items-center text-center">

                <!-- Badge -->
                <div :class="[
                    'inline-flex items-center gap-2 rounded-full border border-border bg-card px-4 py-1.5 text-sm text-muted-foreground mb-8 transition-all duration-700',
                    isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'
                ]">
                    <Blocks class="h-3.5 w-3.5 text-primary" />
                    <span>Modular platform for laundry businesses</span>
                </div>

                <!-- Heading -->
                <h1 :class="[
                    'font-serif text-4xl font-bold tracking-tight sm:text-6xl lg:text-8xl text-balance leading-[1.1] transition-all duration-700 delay-150',
                    'text-gray-900',
                    isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'
                ]">
                    Run your laundry
                    <span class="text-blue-600">
                        shop
                    </span>,
                    <br />
                    your way
                </h1>


                <!-- Subtitle -->
                <p :class="[
                    'mt-6 max-w-2xl text-base sm:text-lg leading-relaxed text-muted-foreground lg:text-xl transition-all duration-700 delay-300 px-2',
                    isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'
                ]">
                    Register your shop, pick only the modules you need, and manage
                    employees, inventory, orders, pricing, and analytics -- all from one platform.
                </p>

                <!-- Buttons -->
                <div :class="[
                    'mt-10 flex flex-col items-center gap-3 sm:flex-row transition-all duration-700 delay-[450ms]',
                    isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'
                ]">
                    <Button @click="router.visit('/trial')" size="lg" variant="default" class="w-full sm:w-auto
                        !bg-blue-600 !text-white
                        hover:!bg-blue-500
                        hover:-translate-y-1
                        hover:shadow-lg
                        transition-all duration-300 ease-in-out
                        px-8 py-6 text-base
                        !shadow-none
                        group">
                        Start Free Trial
                        <ArrowRight class="ml-2 h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" />
                    </Button>

                    <Button @click="router.visit('/plans')" variant="outline" size="lg"
                        class="w-full sm:w-auto px-8 py-6 text-base border-border text-foreground hover:bg-secondary">
                        See Pricing
                    </Button>
                </div>

                <!-- Stats -->
                <div :class="[
                    'mt-16 grid grid-cols-2 gap-6 sm:grid-cols-4 sm:gap-8 transition-all duration-700 delay-[600ms]',
                    isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'
                ]">
                    <div v-for="stat in stats" :key="stat.label" class="flex flex-col items-center">
                        <span class="text-2xl sm:text-3xl font-bold text-foreground font-serif lg:text-4xl">
                            {{ stat.value }}
                        </span>
                        <span class="mt-1 text-xs sm:text-sm text-muted-foreground text-center">
                            {{ stat.label }}
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </section>
</template>
