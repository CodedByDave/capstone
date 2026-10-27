<script setup lang="ts">
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-vue-next';
import { computed } from 'vue';

/**
 * A table column header that always shows a sort arrow: a faint neutral arrow when the column is
 * not sorted, and an accent-colored up or down arrow when it is. Matches the sortable headers on
 * the admin Shop Management table.
 */
const props = defineProps<{
    label: string;
    column: string;
    sortBy?: string | string[];
    sortType?: 'asc' | 'desc' | Array<'asc' | 'desc'>;
}>();

defineEmits<{ toggle: [] }>();

const active = computed(() => props.sortBy === props.column);
const icon = computed(() => {
    if (!active.value) return ArrowUpDown;

    return props.sortType === 'asc' ? ArrowUp : ArrowDown;
});
</script>

<template>
    <button
        type="button"
        class="sortable-column"
        :aria-label="`Sort by ${label} ${active && sortType === 'asc' ? 'descending' : 'ascending'}`"
        @click="$emit('toggle')"
    >
        <span>{{ label }}</span>
        <component
            :is="icon"
            class="h-3.5 w-3.5 shrink-0"
            :class="active ? 'text-primary' : 'opacity-40'"
            aria-hidden="true"
        />
    </button>
</template>

<style scoped>
.sortable-column {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: 32px;
    border-radius: 4px;
    color: inherit;
    font: inherit;
    letter-spacing: inherit;
    text-transform: inherit;
    outline: none;
}

.sortable-column:hover {
    color: var(--foreground);
}

.sortable-column:focus-visible {
    box-shadow: 0 0 0 2px color-mix(in srgb, var(--ring) 50%, transparent);
}
</style>
