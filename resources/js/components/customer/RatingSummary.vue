<script setup lang="ts">
import { Star } from 'lucide-vue-next';

/** A shop's average star rating and how many ratings it is based on. */
defineProps<{ average: number | null; count: number }>();
</script>

<template>
    <span
        v-if="count > 0 && average !== null"
        class="inline-flex items-center gap-1 text-xs font-medium text-gray-700 dark:text-gray-200"
    >
        <Star
            class="h-3.5 w-3.5 fill-amber-400 text-amber-400"
            aria-hidden="true"
        />
        <span aria-hidden="true">{{ average.toFixed(1) }}</span>
        <span class="font-normal text-gray-400" aria-hidden="true"
            >({{ count }})</span
        >
        <span class="sr-only"
            >Rated {{ average.toFixed(1) }} out of 5 from {{ count }}
            {{ count === 1 ? 'rating' : 'ratings' }}</span
        >
    </span>
    <span v-else class="text-xs text-gray-400">No ratings yet</span>
</template>
