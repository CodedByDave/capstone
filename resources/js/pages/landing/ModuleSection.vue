<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Users, Package, ClipboardList, Tags, BarChart3, ArrowRight } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'

const modules = [
    { icon: Users, name: "Employee Management", description: "Manage employee profiles, roles, attendance, and payroll tracking.", price: 1800, color: "bg-blue-500/10 text-blue-600 group-hover:bg-blue-500 group-hover:text-white" },
    { icon: Package, name: "Inventory Management", description: "Track supplies, stock levels, stock movements, and suppliers.", price: 2000, color: "bg-emerald-500/10 text-emerald-600 group-hover:bg-emerald-500 group-hover:text-white" },
    { icon: ClipboardList, name: "Order Management", description: "Create, track, and manage customer orders from processing to completion.", price: 2500, color: "bg-orange-500/10 text-orange-600 group-hover:bg-orange-500 group-hover:text-white" },
    { icon: Tags, name: "Services & Pricing", description: "Configure laundry services, pricing rules, discounts, and promos.", price: 1500, color: "bg-violet-500/10 text-violet-600 group-hover:bg-violet-500 group-hover:text-white" },
    { icon: BarChart3, name: "Reports & Analytics", description: "Generate sales reports, performance insights, and business analytics.", price: 2200, color: "bg-rose-500/10 text-rose-600 group-hover:bg-rose-500 group-hover:text-white" },
]

function formatPHP(amount: number) {
    return `P${amount.toLocaleString()}`
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
        { threshold: 0.2 }
    )
    cardRefs.value.forEach(el => { if (el) observer.observe(el) })
})
</script>

<template>
    <section id="modules" class="py-24 lg:py-32 bg-background">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center mb-12">
                <p class="text-sm font-semibold uppercase tracking-widest text-primary">Modules</p>
                <h2 class="mt-3 font-serif text-4xl font-bold tracking-tight text-foreground sm:text-5xl text-balance">
                    Pick the tools your business needs
                </h2>
                <p class="mt-4 text-lg leading-relaxed text-muted-foreground">
                    Every laundry shop is different. Choose only the modules that match your operations and scale as you grow.
                </p>
            </div>

            <!-- Top row -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="(mod, index) in modules.slice(0, 3)"
                    :key="mod.name"
                    :ref="el => { if (el) { (el as HTMLElement).dataset.index = String(index); cardRefs[index] = el as HTMLElement } }"
                    :class="['group relative rounded-2xl border border-border bg-card p-8 transition-all duration-700 hover:border-primary/30 hover:shadow-lg', visibleItems[index] ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10']"
                >
                    <div class="flex items-start justify-between">
                        <div :class="`flex h-12 w-12 items-center justify-center rounded-xl transition-all duration-300 ${mod.color}`">
                            <component :is="mod.icon" class="h-6 w-6" />
                        </div>
                        <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">{{ formatPHP(mod.price) }}/mo</span>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-foreground">{{ mod.name }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted-foreground">{{ mod.description }}</p>
                </div>
            </div>

            <!-- Bottom row -->
            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:max-w-[66%] lg:mx-auto">
                <div
                    v-for="(mod, index) in modules.slice(3)"
                    :key="mod.name"
                    :ref="el => { const i = index + 3; if (el) { (el as HTMLElement).dataset.index = String(i); cardRefs[i] = el as HTMLElement } }"
                    :class="['group relative rounded-2xl border border-border bg-card p-8 transition-all duration-700 hover:border-primary/30 hover:shadow-lg', visibleItems[index + 3] ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10']"
                >
                    <div class="flex items-start justify-between">
                        <div :class="`flex h-12 w-12 items-center justify-center rounded-xl transition-all duration-300 ${mod.color}`">
                            <component :is="mod.icon" class="h-6 w-6" />
                        </div>
                        <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">{{ formatPHP(mod.price) }}/mo</span>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-foreground">{{ mod.name }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted-foreground">{{ mod.description }}</p>
                </div>
            </div>

            <div class="mt-12 text-center">
                <Button variant="outline" size="lg" class="border-border text-foreground hover:bg-secondary group">
                    See Full Module Details
                    <ArrowRight class="ml-2 h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" />
                </Button>
            </div>
        </div>
    </section>
</template>
