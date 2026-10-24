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
    <div class="space-y-2">
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
