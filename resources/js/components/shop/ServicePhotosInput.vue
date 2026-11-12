<script setup lang="ts">
import { ImagePlus, Trash2 } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

/**
 * Up to `max` photos for a service. On edit, the saved photos are shown first and can be taken
 * off; new photos are added after them. The first photo shown is the cover customers see.
 */
const props = withDefaults(
    defineProps<{
        /** URLs of the photos already saved on the service, the cover first. */
        currentUrls?: string[];
        error?: string;
        max?: number;
    }>(),
    { currentUrls: () => [], error: undefined, max: 3 },
);

const files = defineModel<File[]>({ default: () => [] });
/** Positions in `currentUrls` that are taken off the service. */
const removed = defineModel<number[]>('removed', { default: () => [] });

const input = ref<HTMLInputElement | null>(null);
const localError = ref<string | null>(null);
const previews = ref<string[]>([]);

const MAX_BYTES = 5 * 1024 * 1024;
const ACCEPTED = ['image/jpeg', 'image/png', 'image/webp'];

watch(
    files,
    (list) => {
        previews.value.forEach((url) => URL.revokeObjectURL(url));
        previews.value = list.map((file) => URL.createObjectURL(file));
    },
    { immediate: true },
);

onBeforeUnmount(() =>
    previews.value.forEach((url) => URL.revokeObjectURL(url)),
);

type Tile =
    | { kind: 'saved'; url: string; index: number }
    | { kind: 'new'; url: string; index: number };

const tiles = computed<Tile[]>(() => [
    ...props.currentUrls
        .map((url, index) => ({ kind: 'saved' as const, url, index }))
        .filter((tile) => !removed.value.includes(tile.index)),
    ...previews.value.map((url, index) => ({
        kind: 'new' as const,
        url,
        index,
    })),
]);

const remaining = computed(() => props.max - tiles.value.length);

function onPick(event: Event) {
    const picked = Array.from((event.target as HTMLInputElement).files ?? []);
    localError.value = null;

    const valid = picked.filter((file) => {
        if (!ACCEPTED.includes(file.type)) {
            localError.value = 'Photos must be JPG, PNG or WebP images.';
            return false;
        }
        if (file.size > MAX_BYTES) {
            localError.value = 'Each photo must be 5 MB or smaller.';
            return false;
        }
        return true;
    });

    if (valid.length > remaining.value) {
        localError.value = `Only ${props.max} photos are allowed, so the extra ones were left out.`;
    }

    files.value = [...files.value, ...valid.slice(0, remaining.value)];
    if (input.value) input.value.value = '';
}

function remove(tile: Tile) {
    localError.value = null;
    if (tile.kind === 'saved') {
        removed.value = [...removed.value, tile.index];
    } else {
        files.value = files.value.filter((_, i) => i !== tile.index);
    }
}
</script>

<template>
    <div class="space-y-1.5">
        <div class="flex items-baseline justify-between gap-2">
            <span class="text-sm font-medium"
                >Photos
                <span class="font-normal text-muted-foreground"
                    >(optional, up to {{ max }})</span
                ></span
            >
            <span class="text-xs text-muted-foreground tabular-nums"
                >{{ tiles.length }} / {{ max }}</span
            >
        </div>

        <ul class="grid grid-cols-3 gap-3">
            <li
                v-for="(tile, position) in tiles"
                :key="`${tile.kind}-${tile.index}`"
                class="relative aspect-[4/3] overflow-hidden rounded-lg border bg-muted"
            >
                <img
                    :src="tile.url"
                    :alt="`Service photo ${position + 1}`"
                    class="h-full w-full object-cover"
                />
                <span
                    v-if="position === 0"
                    class="absolute bottom-1.5 left-1.5 rounded bg-background/90 px-1.5 py-0.5 text-[11px] font-semibold shadow"
                    >Cover</span
                >
                <button
                    type="button"
                    class="absolute top-1.5 right-1.5 inline-flex h-8 w-8 items-center justify-center rounded-md bg-background/90 text-red-600 shadow hover:bg-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    :aria-label="`Remove photo ${position + 1}`"
                    @click="remove(tile)"
                >
                    <Trash2 class="h-4 w-4" aria-hidden="true" />
                </button>
            </li>

            <li v-if="remaining > 0">
                <button
                    type="button"
                    class="flex aspect-[4/3] w-full flex-col items-center justify-center rounded-lg border border-dashed bg-muted/30 text-center transition hover:border-primary/50 hover:bg-muted/50 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    :class="
                        error || localError ? 'border-red-500' : 'border-input'
                    "
                    @click="input?.click()"
                >
                    <ImagePlus
                        class="mb-1.5 h-5 w-5 text-muted-foreground"
                        aria-hidden="true"
                    />
                    <span class="text-xs font-semibold">Add photo</span>
                    <span class="mt-0.5 text-[11px] text-muted-foreground"
                        >JPG, PNG, WebP · 5 MB</span
                    >
                </button>
            </li>
        </ul>

        <p v-if="localError || error" class="text-xs text-red-500">
            {{ localError || error }}
        </p>
        <p v-else class="text-xs text-muted-foreground">
            The first photo is the cover customers see first.
        </p>

        <input
            ref="input"
            type="file"
            multiple
            accept="image/jpeg,image/png,image/webp"
            class="hidden"
            @change="onPick"
        />
    </div>
</template>
