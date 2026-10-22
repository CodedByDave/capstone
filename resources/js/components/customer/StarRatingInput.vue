<script setup lang="ts">
import { Star } from 'lucide-vue-next';
import { nextTick, ref } from 'vue';

/** Five tappable stars, usable with a keyboard (arrows, Home, End) as a radio group. */
const model = defineModel<number>({ default: 0 });

withDefaults(defineProps<{ label?: string; size?: 'md' | 'lg' }>(), {
    label: 'Star rating',
    size: 'lg',
});

const words = ['Poor', 'Fair', 'Good', 'Very good', 'Excellent'];
const hover = ref(0);
const buttons = ref<HTMLButtonElement[]>([]);

function choose(value: number) {
    model.value = Math.min(5, Math.max(1, value));
    nextTick(() => buttons.value[model.value - 1]?.focus());
}

function onKeydown(event: KeyboardEvent) {
    const current = model.value || 0;

    if (event.key === 'ArrowRight' || event.key === 'ArrowUp') {
        event.preventDefault();
        choose(current + 1);
    } else if (event.key === 'ArrowLeft' || event.key === 'ArrowDown') {
        event.preventDefault();
        choose(current - 1);
    } else if (event.key === 'Home') {
        event.preventDefault();
        choose(1);
    } else if (event.key === 'End') {
        event.preventDefault();
        choose(5);
    }
}
</script>

<template>
    <div class="flex flex-col items-center gap-2">
        <div
            role="radiogroup"
            :aria-label="label"
            class="flex items-center gap-1"
            @keydown="onKeydown"
            @mouseleave="hover = 0"
        >
            <button
                v-for="n in 5"
                :key="n"
                :ref="(el) => (buttons[n - 1] = el as HTMLButtonElement)"
                type="button"
                role="radio"
                :aria-checked="model === n"
                :aria-label="`${n} ${n === 1 ? 'star' : 'stars'}, ${words[n - 1]}`"
                :tabindex="model === n || (model === 0 && n === 1) ? 0 : -1"
                class="rounded-md p-1 transition-transform hover:scale-110 focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:outline-none"
                @click="choose(n)"
                @mouseenter="hover = n"
            >
                <Star
                    :class="[
                        size === 'lg' ? 'h-9 w-9' : 'h-6 w-6',
                        (hover || model) >= n
                            ? 'fill-amber-400 text-amber-400'
                            : 'text-gray-300 dark:text-gray-600',
                    ]"
                    aria-hidden="true"
                />
            </button>
        </div>
        <p
            class="h-5 text-sm font-medium text-gray-600 dark:text-gray-300"
            aria-live="polite"
        >
            {{ words[(hover || model) - 1] ?? '' }}
        </p>
    </div>
</template>
