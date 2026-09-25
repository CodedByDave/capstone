<script setup lang="ts">
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    label: string;
    active?: boolean;
    direction?: 'asc' | 'desc';
}>();

defineEmits<{ sort: [] }>();

const icon = computed(() => {
    if (!props.active) return ArrowUpDown;
    return props.direction === 'asc' ? ArrowUp : ArrowDown;
});
</script>

<template>
    <div class="flex min-w-0 flex-col gap-1.5 text-left">
        <button
            type="button"
            class="inline-flex w-fit items-center gap-1.5 font-medium text-inherit hover:text-foreground"
            @click="$emit('sort')"
        >
            <span>{{ label }}</span>
            <component
                :is="icon"
                class="h-3.5 w-3.5 shrink-0"
                :class="{ 'opacity-60': !active }"
            />
        </button>
        <slot />
    </div>
</template>
