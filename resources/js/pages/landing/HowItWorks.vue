<script setup lang="ts">
import { ref, onMounted } from "vue"
import { UserPlus, Store, Blocks, Rocket } from "lucide-vue-next"

const steps = [
    {
        icon: UserPlus,
        number: "01",
        title: "Create Your Account",
        description: "Sign up in minutes with your email. No credit card required to get started.",
    },
    {
        icon: Store,
        number: "02",
        title: "Register Your Shop",
        description: "Add your shop details -- name, location, operating hours, and service areas.",
    },
    {
        icon: Blocks,
        number: "03",
        title: "Choose Your Modules",
        description: "Pick only the tools you need: employee management, inventory, orders, pricing, or analytics.",
    },
    {
        icon: Rocket,
        number: "04",
        title: "Go Live",
        description: "Start managing your business and serving customers from your personalized dashboard.",
    },
]

const visibleItems = ref<Record<string, boolean>>({})
const headerRef = ref<HTMLElement | null>(null)
const imageRef = ref<HTMLElement | null>(null)
const stepRefs = ref<HTMLElement[]>([])

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const key = (entry.target as HTMLElement).dataset.key
                    if (key) visibleItems.value[key] = true
                }
            })
        },
        { threshold: 0.15 }
    )

    if (headerRef.value) {
        headerRef.value.dataset.key = 'header'
        observer.observe(headerRef.value)
    }
    if (imageRef.value) {
        imageRef.value.dataset.key = 'image'
        observer.observe(imageRef.value)
    }
    stepRefs.value.forEach((el, i) => {
        if (el) {
            el.dataset.key = `step-${i}`
            observer.observe(el)
        }
    })
})
</script>

<template>
    <section id="how-it-works" class="py-24 lg:py-32 bg-background">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-16 lg:grid-cols-2 lg:gap-20 items-center">

                <!-- LEFT IMAGE -->
                <div ref="imageRef" :class="[
                    'relative transition-all duration-1000 order-2 lg:order-1',
                    visibleItems['image'] ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-12'
                ]">
                    <div class="relative aspect-[4/5] overflow-hidden rounded-3xl">
                        <img src="/assets/images/shop-setup.jpg" alt="LaundryHub shop setup"
                            class="w-full h-full object-cover" />
                    </div>
                    <div class="absolute -bottom-6 -right-2 sm:-right-4 lg:-right-6 rounded-2xl border border-border bg-card p-4 sm:p-5 shadow-xl animate-float">
                        <p class="text-2xl sm:text-3xl font-bold text-primary font-serif">5 min</p>
                        <p class="text-xs sm:text-sm text-muted-foreground">Setup time</p>
                    </div>
                </div>

                <!-- RIGHT SIDE -->
                <div class="order-1 lg:order-2">
                    <div ref="headerRef" :class="[
                        'transition-all duration-700',
                        visibleItems['header'] ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
                    ]">
                        <p class="text-sm font-semibold uppercase tracking-widest text-primary">How It Works</p>
                        <h2 class="mt-3 font-serif text-3xl sm:text-4xl font-bold tracking-tight text-foreground sm:text-5xl text-balance">
                            Get your shop online in 4 steps
                        </h2>
                    </div>

                    <div class="mt-10 sm:mt-12 flex flex-col gap-6 sm:gap-8">
                        <div
                            v-for="(step, index) in steps"
                            :key="step.number"
                            :ref="el => { if (el) stepRefs[index] = el as HTMLElement }"
                            :class="[
                                'flex items-start gap-4 sm:gap-5 transition-all duration-700',
                                visibleItems[`step-${index}`] ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-8'
                            ]"
                            :style="{ transitionDelay: index * 120 + 'ms' }"
                        >
                            <div class="flex h-11 w-11 sm:h-12 sm:w-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <component :is="step.icon" class="h-5 w-5" />
                            </div>
                            <div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-bold tracking-wider text-primary">{{ step.number }}</span>
                                    <h3 class="text-base sm:text-lg font-semibold text-foreground">{{ step.title }}</h3>
                                </div>
                                <p class="mt-1.5 text-sm leading-relaxed text-muted-foreground">{{ step.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</template>
