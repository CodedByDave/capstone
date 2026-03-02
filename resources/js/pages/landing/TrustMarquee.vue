<script setup lang="ts">
const trustSignals = [
    "500+ Registered Shops",
    "Employee Management",
    "Inventory Tracking",
    "Order Processing",
    "Custom Pricing Rules",
    "Sales Analytics",
    "Real-Time Reports",
    "Multi-Branch Support",
]

const doubled = [...trustSignals, ...trustSignals]
</script>

<template>
    <section class="overflow-hidden border-y border-border bg-secondary/50 py-8">
        <p class="text-center text-xs font-semibold uppercase tracking-widest text-muted-foreground mb-6">
            Everything your laundry business needs in one platform
        </p>

        <div class="relative">
            <!-- Fade edges -->
            <div class="pointer-events-none absolute left-0 top-0 z-10 h-full w-24 bg-gradient-to-r from-secondary/50 to-transparent" />
            <div class="pointer-events-none absolute right-0 top-0 z-10 h-full w-24 bg-gradient-to-l from-secondary/50 to-transparent" />

            <!-- Marquee track -->
            <div class="marquee-track flex gap-12">
                <span
                    v-for="(item, i) in doubled"
                    :key="item + '-' + i"
                    class="shrink-0 whitespace-nowrap text-lg font-semibold text-muted-foreground/60"
                >
                    ✦ {{ item }}
                </span>
            </div>
        </div>
    </section>
</template>

<style scoped>
.marquee-track {
    display: flex;
    width: max-content;
    animation: marquee 25s linear infinite;
}

@keyframes marquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
</style>
