<script setup lang="ts">
import type { StatusOption } from '@/composables/useDataTable';
import { Search } from 'lucide-vue-next';

/**
 * The data table toolbar: a search box, segmented status chips with counts, and slots for extra
 * filters and actions. It wraps on small screens.
 */
withDefaults(
    defineProps<{
        searchPlaceholder?: string;
        searchLabel?: string;
        statuses?: StatusOption[];
        statusLabel?: string;
        hideSearch?: boolean;
    }>(),
    {
        searchPlaceholder: 'Search...',
        searchLabel: 'Search table',
        statuses: () => [],
        statusLabel: 'Filter by status',
        hideSearch: false,
    },
);

const search = defineModel<string>('search', { default: '' });
const status = defineModel<string>('status', { default: '' });

const emit = defineEmits<{ submit: [] }>();

function selectStatus(value: string) {
    status.value = value;
}
</script>

<template>
    <div class="flex flex-wrap items-center gap-3">
        <div
            v-if="statuses.length"
            role="group"
            :aria-label="statusLabel"
            class="inline-flex max-w-full flex-wrap items-center gap-1 rounded-lg bg-muted p-1"
        >
            <button
                v-for="option in statuses"
                :key="option.value"
                type="button"
                class="inline-flex h-9 items-center gap-1.5 rounded-md px-3 text-sm font-medium transition-colors outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
                :class="
                    status === option.value
                        ? 'bg-background text-foreground shadow-sm'
                        : 'text-foreground/80 hover:text-foreground'
                "
                :aria-pressed="status === option.value"
                @click="selectStatus(option.value)"
            >
                {{ option.label }}
                <span
                    v-if="option.count !== undefined"
                    class="text-xs font-normal text-muted-foreground tabular-nums"
                >
                    {{ option.count }}
                </span>
            </button>
        </div>

        <slot />

        <div
            v-if="!hideSearch || $slots.actions"
            class="flex w-full flex-wrap items-center gap-2 sm:ml-auto sm:w-auto"
        >
            <label v-if="!hideSearch" class="relative w-full sm:w-80">
                <span class="sr-only">{{ searchLabel }}</span>
                <Search
                    class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    aria-hidden="true"
                />
                <input
                    v-model="search"
                    type="search"
                    :placeholder="searchPlaceholder"
                    class="h-11 w-full rounded-lg border border-input bg-background pr-3 pl-9 text-sm outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                    @keyup.enter="emit('submit')"
                />
            </label>
            <slot name="actions" />
        </div>
    </div>
</template>
