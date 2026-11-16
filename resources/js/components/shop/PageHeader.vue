<script setup lang="ts">
/** Page title and description on the left, the page's actions on the right (above the KPIs). */
defineProps<{ title: string; description?: string }>();
</script>

<template>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">{{ title }}</h1>
            <p v-if="description" class="text-sm text-muted-foreground">
                {{ description }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <slot />
        </div>
    </div>
</template>
