<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Star } from 'lucide-vue-next'

const testimonials = [
    {
        name: "Maria Santos",
        role: "Owner, QuickWash Laundry",
        content: "LaundryHub transformed how I run my shop. The Order Management module alone saved us hours every day. We went from tracking orders on paper to a fully digital system in one afternoon.",
        rating: 5,
    },
    {
        name: "Carlos Reyes",
        role: "Manager, FreshPress Laundromat",
        content: "The modular approach is perfect. We started with just Employee Management and Inventory, then added Reports & Analytics as we grew. No wasted money on features we didn't need.",
        rating: 5,
    },
    {
        name: "Ana De La Cruz",
        role: "Owner, Sparkle Clean Co.",
        content: "I run three branches and LaundryHub's Reports & Analytics module gives me a clear picture of all locations in real time. The insights helped us increase revenue by 30% in the first quarter.",
        rating: 5,
    },
    {
        name: "Ramon Villanueva",
        role: "Owner, EcoWash Manila",
        content: "Setting up was incredibly easy -- I had my shop live in under five minutes. The Services & Pricing module lets me configure promos and discounts that keep customers coming back.",
        rating: 5,
    },
]

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
    <section id="testimonials" class="py-24 lg:py-32 bg-background">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center mb-12">
                <p class="text-sm font-semibold uppercase tracking-widest text-primary">Testimonials</p>
                <h2 class="mt-3 font-serif text-4xl font-bold tracking-tight text-foreground sm:text-5xl text-balance">
                    Trusted by shop owners
                </h2>
                <p class="mt-4 text-lg leading-relaxed text-muted-foreground">
                    See how laundry businesses across the Philippines are growing with LaundryHub.
                </p>
            </div>

            <div class="mt-16 grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div
                    v-for="(testimonial, index) in testimonials"
                    :key="testimonial.name"
                    :ref="el => { if (el) { (el as HTMLElement).dataset.index = String(index); cardRefs[index] = el as HTMLElement } }"
                    :class="[
                        'rounded-2xl border border-border bg-card p-8 transition-all duration-700 hover:shadow-lg',
                        visibleItems[index] ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 translate-y-8 scale-95'
                    ]"
                    :style="{ transitionDelay: index * 120 + 'ms' }"
                >
                    <div class="flex gap-1">
                        <component v-for="i in testimonial.rating" :key="i" :is="Star"
                            class="h-4 w-4 fill-primary text-primary" />
                    </div>
                    <p class="mt-5 text-base leading-relaxed text-foreground">
                        &ldquo;{{ testimonial.content }}&rdquo;
                    </p>
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary">
                            {{ testimonial.name.split(' ').map(n => n[0]).join('') }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">{{ testimonial.name }}</p>
                            <p class="text-xs text-muted-foreground">{{ testimonial.role }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
