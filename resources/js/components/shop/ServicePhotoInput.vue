<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { ImagePlus, Trash2, Upload } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps<{
    modelValue: File | null;
    /** Photo already saved on the service, shown until it is replaced or removed. */
    currentUrl?: string | null;
    removed?: boolean;
    error?: string;
    /** A single dropzone-style box, used beside the other details on the new service form. */
    compact?: boolean;
}>();

const emit = defineEmits<{
    'update:modelValue': [file: File | null];
    'update:removed': [removed: boolean];
}>();

const input = ref<HTMLInputElement | null>(null);
const previewUrl = ref<string | null>(null);

const revoke = () => {
    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = null;
};

watch(
    () => props.modelValue,
    (file) => {
        revoke();
        if (file) previewUrl.value = URL.createObjectURL(file);
    },
);

onBeforeUnmount(revoke);

const shownUrl = computed(
    () =>
        previewUrl.value ?? (props.removed ? null : (props.currentUrl ?? null)),
);

const onPick = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    emit('update:modelValue', file);
    if (file) emit('update:removed', false);
};

const clear = () => {
    emit('update:modelValue', null);
    if (input.value) input.value.value = '';
    if (props.currentUrl) emit('update:removed', true);
};
</script>

<template>
    <div v-if="compact" class="space-y-1.5">
        <label class="text-sm font-medium"
            >Photo
            <span class="font-normal text-muted-foreground"
                >(optional)</span
            ></label
        >
        <div class="relative">
            <button
                type="button"
                class="flex h-28 w-full flex-col items-center justify-center overflow-hidden rounded-lg border border-dashed bg-muted/30 text-center transition hover:border-primary/50 hover:bg-muted/50 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                :class="error ? 'border-red-500' : 'border-input'"
                @click="input?.click()"
            >
                <img
                    v-if="shownUrl"
                    :src="shownUrl"
                    alt="Service photo preview"
                    class="h-full w-full object-cover"
                />
                <template v-else>
                    <Upload class="mb-1.5 h-4 w-4 text-muted-foreground" />
                    <span class="text-xs font-semibold">Upload photo</span>
                    <span class="mt-0.5 text-[11px] text-muted-foreground"
                        >JPG, PNG, WebP · 5 MB</span
                    >
                </template>
            </button>
            <button
                v-if="shownUrl"
                type="button"
                class="absolute top-1.5 right-1.5 rounded-md bg-background/90 p-1 text-red-600 shadow hover:bg-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                aria-label="Remove photo"
                @click="clear"
            >
                <Trash2 class="h-3.5 w-3.5" />
            </button>
        </div>
        <p v-if="error" class="text-xs text-red-500">{{ error }}</p>
        <input
            ref="input"
            type="file"
            accept="image/jpeg,image/png,image/webp"
            class="hidden"
            @change="onPick"
        />
    </div>
    <div v-else class="space-y-2">
        <label class="text-sm font-medium">Service Photo</label>
        <div class="flex flex-wrap items-center gap-4">
            <div
                class="flex h-28 w-28 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-dashed border-gray-300 bg-gray-50 text-gray-400"
            >
                <img
                    v-if="shownUrl"
                    :src="shownUrl"
                    alt="Service photo preview"
                    class="h-full w-full object-cover"
                />
                <ImagePlus v-else class="h-8 w-8" />
            </div>
            <div class="space-y-2">
                <div class="flex flex-wrap gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="input?.click()"
                    >
                        <Upload class="mr-2 h-4 w-4" />
                        {{ shownUrl ? 'Change photo' : 'Upload photo' }}
                    </Button>
                    <Button
                        v-if="shownUrl"
                        type="button"
                        variant="outline"
                        size="sm"
                        class="text-red-600"
                        @click="clear"
                    >
                        <Trash2 class="mr-2 h-4 w-4" />
                        Remove
                    </Button>
                </div>
                <p class="text-xs text-muted-foreground">
                    Optional. JPG, PNG or WebP, up to 5 MB.
                </p>
            </div>
            <input
                ref="input"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                class="hidden"
                @change="onPick"
            />
        </div>
        <p v-if="error" class="text-xs text-red-500">{{ error }}</p>
    </div>
</template>
