<script setup lang="ts">
import type { Component } from 'vue';
import { computed } from 'vue';

/**
 * The KPI row above a table: one separate card per metric, 20px apart. Every tile has the same
 * layout and height: a small uppercase label, a large neutral number, and one muted detail line.
 * Numbers use the default text colour on purpose; a state (low stock, overdue) belongs in the detail.
 */
export interface KpiTile {
    label: string;
    value: string | number;
    /** One muted line under the number, such as "needs reordering" or "this month". */
    detail?: string;
    /** Decorative icon at the top right; the label already names the metric. */
    icon?: Component;
    /** Makes the tile a button that filters the table (clicking again clears it). */
    onSelect?: () => void;
    /** True while that filter is applied. */
    active?: boolean;
}

const props = defineProps<{ tiles: readonly KpiTile[] }>();

// Counts read with thousands separators; text (currency, percentages) is shown as given.
const show = (value: string | number) =>
    typeof value === 'number' ? value.toLocaleString('en-PH') : value;

// Wide screens fit the count in one row where it divides well; narrow ones always use two columns.
const columns = computed(
    () =>
        ({ 3: 'lg:grid-cols-3', 5: 'lg:grid-cols-5', 6: 'lg:grid-cols-3' })[
            props.tiles.length
        ] ?? 'lg:grid-cols-4',
);
</script>

<template>
    <div
        v-if="tiles.length"
        class="grid grid-cols-2 gap-5"
        :class="columns"
        role="list"
    >
        <component
            :is="tile.onSelect ? 'button' : 'div'"
            v-for="tile in tiles"
            :key="tile.label"
            :type="tile.onSelect ? 'button' : undefined"
            :aria-pressed="tile.onSelect ? !!tile.active : undefined"
            role="listitem"
            class="flex h-full flex-col rounded-xl border bg-card p-5 text-left text-card-foreground"
            :class="
                tile.onSelect
                    ? [
                          'cursor-pointer transition outline-none hover:border-primary/40 focus-visible:ring-2 focus-visible:ring-ring',
                          tile.active
                              ? 'border-primary ring-1 ring-primary'
                              : '',
                      ]
                    : ''
            "
            @click="tile.onSelect?.()"
        >
            <div class="flex items-center justify-between gap-2">
                <p
                    class="text-[11px] font-medium tracking-widest text-muted-foreground uppercase"
                >
                    {{ tile.label }}
                </p>
                <component
                    :is="tile.icon"
                    v-if="tile.icon"
                    class="h-4 w-4 shrink-0 text-muted-foreground"
                    aria-hidden="true"
                />
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight tabular-nums">
                {{ show(tile.value) }}
            </p>
            <p
                v-if="tile.detail"
                class="mt-1 text-xs text-muted-foreground tabular-nums"
            >
                {{ tile.detail }}
            </p>
        </component>
    </div>
</template>
