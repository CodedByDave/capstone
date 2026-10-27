<script setup lang="ts">
import { getInitials } from '@/composables/useInitials';
import { computed } from 'vue';

/** A person or entity cell: tinted initials avatar, bold name, and a muted secondary line. */
const props = defineProps<{
    name: string | null | undefined;
    secondary?: string | null;
    avatarUrl?: string | null;
}>();

const AVATAR_TINTS = [
    'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
    'bg-orange-50 text-orange-700 dark:bg-orange-500/15 dark:text-orange-300',
    'bg-purple-50 text-purple-700 dark:bg-purple-500/15 dark:text-purple-300',
    'bg-teal-50 text-teal-700 dark:bg-teal-500/15 dark:text-teal-300',
    'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
    'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
];

const initials = computed(() => getInitials(props.name ?? '') || '?');

// The same name always gets the same tint.
const tint = computed(() => {
    const value = props.name ?? '';
    let hash = 0;
    for (let index = 0; index < value.length; index++) {
        hash = (hash * 31 + value.charCodeAt(index)) >>> 0;
    }

    return AVATAR_TINTS[hash % AVATAR_TINTS.length];
});
</script>

<template>
    <div class="flex min-w-0 items-center gap-3">
        <img
            v-if="avatarUrl"
            :src="avatarUrl"
            alt=""
            class="h-9 w-9 shrink-0 rounded-full object-cover"
        />
        <span
            v-else
            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-semibold"
            :class="tint"
            aria-hidden="true"
        >
            {{ initials }}
        </span>
        <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-foreground">
                {{ name || 'Unknown' }}
            </p>
            <p v-if="secondary" class="truncate text-xs text-muted-foreground">
                {{ secondary }}
            </p>
            <slot />
        </div>
    </div>
</template>
