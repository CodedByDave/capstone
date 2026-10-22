<script setup lang="ts">
import { Star } from 'lucide-vue-next';

/** Read-only row of five stars showing a whole-number rating. */
withDefaults(defineProps<{ value: number; size?: 'sm' | 'md' }>(), {
    size: 'sm',
});
</script>

<template>
    <span
        class="inline-flex items-center gap-0.5"
        role="img"
        :aria-label="`${value} out of 5 stars`"
    >
        <Star
            v-for="n in 5"
            :key="n"
            :class="[
                size === 'md' ? 'h-5 w-5' : 'h-4 w-4',
                n <= value
                    ? 'fill-amber-400 text-amber-400'
                    : 'text-gray-300 dark:text-gray-600',
            ]"
            aria-hidden="true"
        />
    </span>
</template>
