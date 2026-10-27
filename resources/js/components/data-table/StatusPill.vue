<script setup lang="ts">
import type { Component } from 'vue';
import { computed } from 'vue';
import { statusToneClasses, type StatusTone } from './status';

/**
 * A status badge with a colored dot (or the status's own icon) and a text label, so status is
 * never conveyed by color alone. Matches the status pills on the admin Shop Management table.
 */
const props = withDefaults(
    defineProps<{
        tone?: StatusTone;
        icon?: Component;
    }>(),
    { tone: 'neutral', icon: undefined },
);

const classes = computed(() => statusToneClasses[props.tone]);
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium whitespace-nowrap"
        :class="classes.pill"
    >
        <component
            :is="icon"
            v-if="icon"
            class="h-3 w-3 shrink-0"
            aria-hidden="true"
        />
        <span
            v-else
            class="h-1.5 w-1.5 shrink-0 rounded-full"
            :class="classes.dot"
            aria-hidden="true"
        />
        <slot />
    </span>
</template>
