<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Users, Package, ClipboardList, Banknote, BarChart3, Check, Minus } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { router } from '@inertiajs/vue3'

const plans = [
    {
        name: 'Basic',
        price: 1999,
        description: 'Essential tools for small shops just getting started with digital operations.',
        featured: false,
        savings: null,
        modules: [
            { name: 'Human Resource Managament', icon: Users, included: true },
            { name: 'Operations Management', icon: ClipboardList, included: true },
            { name: 'Inventory Management', icon: Package, included: false },
            { name: 'Finance Management', icon: Banknote, included: false },
            { name: 'Reports & Analytics', icon: BarChart3, included: false },
        ],
    },
    {
        name: 'Standard',
        price: 3999,
        description: 'A complete operational stack for growing shops that need full visibility.',
        featured: true,
        savings: 1900,
        modules: [
            { name: 'Human Resource Managament', icon: Users, included: true },
            { name: 'Operations Management', icon: ClipboardList, included: true },
            { name: 'Inventory Management', icon: Package, included: true },
            { name: 'Finance Management', icon: Banknote, included: true },
            { name: 'Reports & Analytics', icon: BarChart3, included: false },
        ],
    },
    {
        name: 'Premium',
        price: 5999,
        description: 'The full suite for multi-branch shops that need deep analytics and financial control.',
        featured: false,
        savings: 3500,
        modules: [
            { name: 'Human Resource Managament', icon: Users, included: true },
            { name: 'Operations Management', icon: ClipboardList, included: true },
            { name: 'Inventory Management', icon: Package, included: true },
            { name: 'Finance Management', icon: Banknote, included: true },
            { name: 'Reports & Analytics', icon: BarChart3, included: true },
        ],
    },
]

function goToCheckout(plan: any) {
    router.visit(`/shop/checkout/${plan.name}`)
}

function formatPHP(amount: number) {
    return amount.toLocaleString('en-PH')
}

const visibleItems = ref<Record<number, boolean>>({})
const cardRefs = ref<HTMLElement[]>([])

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const index = Number((entry.target as HTMLElement).dataset.index)
                    visibleItems.value[index] = true
                }
            })
        },
        { threshold: 0.15 }
    )
    cardRefs.value.forEach(el => { if (el) observer.observe(el) })
})
</script>

<template>
    <section id="modules" class="py-24 lg:py-32 bg-background">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">

            <!-- Header -->
            <div class="mx-auto max-w-2xl text-center mb-14">
                <p class="text-sm font-semibold uppercase tracking-widest text-primary">Pricing</p>
                <h2 class="mt-3 font-serif text-4xl font-bold tracking-tight text-foreground sm:text-5xl text-balance">
                    Plans built for laundry businesses
                </h2>
                <p class="mt-4 text-lg leading-relaxed text-muted-foreground">
                    Start with what you need, upgrade as you grow.
                </p>
            </div>

            <!-- Plan cards -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <div v-for="(plan, index) in plans" :key="plan.name"
                    :ref="el => { if (el) { (el as HTMLElement).dataset.index = String(index); cardRefs[index] = el as HTMLElement } }"
                    :class="[
                        'relative flex flex-col rounded-2xl bg-card p-8 transition-all duration-700',
                        plan.featured
                            ? 'border-2 border-primary shadow-lg'
                            : 'border border-border hover:border-primary/30 hover:shadow-md',
                        visibleItems[index] ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10',
                    ]">
                    <!-- Most popular badge -->
                    <div v-if="plan.featured" class="mb-4">
                        <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                            Most popular
                        </span>
                    </div>

                    <!-- Plan name & price -->
                    <p class="text-sm font-medium text-muted-foreground">{{ plan.name }}</p>
                    <div class="mt-1 flex items-baseline gap-1">
                        <span class="text-3xl font-bold tracking-tight text-foreground">
                            ₱{{ formatPHP(plan.price) }}
                        </span>
                        <span class="text-sm text-muted-foreground">/mo</span>
                    </div>
                    <p class="mt-3 text-sm leading-relaxed text-muted-foreground">{{ plan.description }}</p>

                    <!-- Divider -->
                    <hr class="my-6 border-border" />

                    <!-- Module list -->
                    <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-muted-foreground">
                        {{ plan.name === 'Premium' ? 'Everything in Standard, plus' : 'Includes' }}
                    </p>
                    <ul class="space-y-3 flex-1">
                        <li v-for="mod in plan.modules" :key="mod.name" class="flex items-center gap-3">
                            <span :class="[
                                'flex h-5 w-5 shrink-0 items-center justify-center rounded-full',
                                mod.included
                                    ? 'bg-emerald-500/10 text-emerald-600'
                                    : 'bg-muted text-muted-foreground',
                            ]">
                                <Check v-if="mod.included" class="h-3 w-3" />
                                <Minus v-else class="h-3 w-3" />
                            </span>
                            <span :class="[
                                'flex items-center gap-2 text-sm',
                                mod.included ? 'text-foreground' : 'text-muted-foreground line-through',
                            ]">
                                <component :is="mod.icon" class="h-3.5 w-3.5 shrink-0 opacity-60" />
                                {{ mod.name }}
                            </span>
                        </li>
                    </ul>

                    <!-- CTA -->
                    <div class="mt-8">
                        <Button :variant="plan.featured ? 'default' : 'outline'" class="w-full"
                            @click="goToCheckout(plan)">
                            Get started
                        </Button>

                        <p v-if="plan.savings" class="mt-2 text-center text-xs text-emerald-600 font-medium">
                            Save ₱{{ formatPHP(plan.savings) }} vs individual modules
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer note -->
            <p class="mt-10 text-center text-sm text-muted-foreground">
                Need a custom setup?
                <a href="#contact" class="font-medium text-primary underline-offset-4 hover:underline">
                    Contact us
                </a>
                and we'll build a plan for your shop.
            </p>

        </div>
    </section>
</template>
