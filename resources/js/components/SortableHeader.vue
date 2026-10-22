<script setup lang="ts">
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-vue-next';
import { computed } from 'vue';

/**
 * A table column header that always shows a sort arrow: neutral when the column is not sorted,
 * up or down when it is. Matches the sortable headers on the admin Shop Management table.
 */
const props = defineProps<{
    label: string;
    column: string;
    sortBy?: string;
    sortType?: 'asc' | 'desc';
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
            class="h-3.5 w-3.5"
            :class="{ 'opacity-60': !active }"
        />
    </button>
</template>

<style scoped>
.sortable-column {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: inherit;
    font: inherit;
}

.sortable-column:hover {
    color: var(--foreground);
}
</style>
