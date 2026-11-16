<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { ArrowUpDown, ChevronDown } from 'lucide-vue-next';
import type { Component } from 'vue';

export interface ActionsMenuItem {
    label: string;
    icon?: Component;
    /** Called when the item is chosen. */
    onSelect?: () => void;
    /** Link target for downloads or navigation; used instead of `onSelect`. */
    href?: string;
    /** Draw a divider above this item. */
    separator?: boolean;
    /** Hide the item (for permission checks). */
    hidden?: boolean;
    disabled?: boolean;
}

/**
 * The page header's grouped secondary actions (export, import, archives) as one dropdown button,
 * matching the Employees page.
 */
withDefaults(
    defineProps<{ items: ActionsMenuItem[]; label?: string }>(),
    { label: 'Import / export' },
);
</script>

<template>
    <DropdownMenu v-if="items.some((item) => !item.hidden)">
        <DropdownMenuTrigger as-child>
            <Button variant="success-outline" class="h-11">
                <ArrowUpDown class="mr-2 h-4 w-4" aria-hidden="true" />
                {{ label }}
                <ChevronDown class="ml-2 h-4 w-4" aria-hidden="true" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <template v-for="(item, index) in items" :key="item.label">
                <template v-if="!item.hidden">
                    <DropdownMenuSeparator
                        v-if="item.separator && index > 0"
                    />
                    <DropdownMenuItem
                        v-if="item.href"
                        as-child
                        :disabled="item.disabled"
                    >
                        <a :href="item.href" download>
                            <component
                                :is="item.icon"
                                v-if="item.icon"
                                class="mr-2 h-4 w-4"
                            />
                            {{ item.label }}
                        </a>
                    </DropdownMenuItem>
                    <DropdownMenuItem
                        v-else
                        :disabled="item.disabled"
                        @select="item.onSelect?.()"
                    >
                        <component
                            :is="item.icon"
                            v-if="item.icon"
                            class="mr-2 h-4 w-4"
                        />
                        {{ item.label }}
                    </DropdownMenuItem>
                </template>
            </template>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
