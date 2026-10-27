<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { MoreVertical } from 'lucide-vue-next';
import { computed, type Component } from 'vue';

export interface RowAction {
    label: string;
    icon?: Component;
    iconClass?: string;
    onSelect: () => void;
    /** Hide the action when false (permissions, row state). */
    show?: boolean;
    disabled?: boolean;
    destructive?: boolean;
}

/**
 * The kebab "more actions" menu for a table row. The trigger's accessible name includes the row,
 * and every action keeps its icon next to a text label.
 */
const props = defineProps<{
    label: string;
    actions: RowAction[];
}>();

const visibleActions = computed(() =>
    props.actions.filter((action) => action.show !== false),
);
</script>

<template>
    <div class="flex items-center justify-center gap-1">
        <slot />
        <DropdownMenu v-if="visibleActions.length">
            <DropdownMenuTrigger as-child>
                <Button
                    variant="ghost"
                    size="icon"
                    class="h-9 w-9 text-muted-foreground hover:text-foreground"
                    :aria-label="`More actions for ${label}`"
                >
                    <MoreVertical class="h-4 w-4" aria-hidden="true" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="min-w-44">
                <DropdownMenuItem
                    v-for="action in visibleActions"
                    :key="action.label"
                    :disabled="action.disabled"
                    :variant="action.destructive ? 'destructive' : 'default'"
                    class="min-h-9 cursor-pointer"
                    @select="action.onSelect()"
                >
                    <component
                        :is="action.icon"
                        v-if="action.icon"
                        class="h-4 w-4"
                        :class="action.iconClass"
                        aria-hidden="true"
                    />
                    {{ action.label }}
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    </div>
</template>
