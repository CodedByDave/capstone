<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{ shopCount?: number }>();

const trustSignals = computed(() => [
    `${(props.shopCount ?? 0).toLocaleString('en-PH')} Registered Shops`,
    'Employee Management',
    'Inventory Tracking',
    'Order Processing',
    'Custom Pricing Rules',
    'Sales Analytics',
    'Real-Time Reports',
]);

const doubled = computed(() => [
    ...trustSignals.value,
    ...trustSignals.value,
]);
</script>

<template>
    <section
        class="overflow-hidden border-y border-border bg-secondary/50 py-8"
    >
        <p
            class="mb-6 text-center text-xs font-semibold tracking-widest text-muted-foreground uppercase"
        >
            Everything your laundry business needs in one platform
        </p>

        <div class="relative">
            <!-- Fade edges -->
            <div
                class="pointer-events-none absolute top-0 left-0 z-10 h-full w-24 bg-gradient-to-r from-secondary/50 to-transparent"
            />
            <div
                class="pointer-events-none absolute top-0 right-0 z-10 h-full w-24 bg-gradient-to-l from-secondary/50 to-transparent"
            />

            <!-- Marquee track -->
            <div class="marquee-track flex gap-12">
                <span
                    v-for="(item, i) in doubled"
                    :key="item + '-' + i"
                    class="shrink-0 text-lg font-semibold whitespace-nowrap text-muted-foreground/60"
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
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}
</style>
