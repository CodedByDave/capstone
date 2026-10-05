<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { ImageUp, PenLine, RotateCcw } from 'lucide-vue-next';
import { onBeforeUnmount, ref } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue: File | null;
        disabled?: boolean;
        error?: string;
        label?: string;
        description?: string;
        fileName?: string;
    }>(),
    {
        label: 'Owner signature',
        description:
            'Draw inside the signature line or upload a clear signature image.',
        fileName: 'owner-signature.png',
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: File | null];
    'update:method': [value: 'drawn' | 'uploaded'];
}>();

const canvas = ref<HTMLCanvasElement | null>(null);
const mode = ref<'drawn' | 'uploaded'>('drawn');
const drawing = ref(false);
const hasDrawing = ref(false);
const uploadedPreview = ref<string | null>(null);

function point(event: PointerEvent) {
    const target = canvas.value!;
    const rect = target.getBoundingClientRect();

    return {
        x: ((event.clientX - rect.left) / rect.width) * target.width,
        y: ((event.clientY - rect.top) / rect.height) * target.height,
    };
}

function startDrawing(event: PointerEvent) {
    if (!canvas.value) return;
    drawing.value = true;
    canvas.value.setPointerCapture(event.pointerId);
    const context = canvas.value.getContext('2d')!;
    const start = point(event);
    context.beginPath();
    context.moveTo(start.x, start.y);
}

function continueDrawing(event: PointerEvent) {
    if (!drawing.value || !canvas.value) return;
    const context = canvas.value.getContext('2d')!;
    const next = point(event);
    context.lineWidth = 3;
    context.lineCap = 'round';
    context.lineJoin = 'round';
    context.strokeStyle = '#111827';
    context.lineTo(next.x, next.y);
    context.stroke();
    hasDrawing.value = true;
}

function finishDrawing() {
    if (!drawing.value) return;
    drawing.value = false;
    if (!hasDrawing.value || !canvas.value) return;

    canvas.value.toBlob((blob) => {
        if (!blob) return;
        emit(
            'update:modelValue',
            new File([blob], props.fileName, { type: 'image/png' }),
        );
        emit('update:method', 'drawn');
    }, 'image/png');
}

function clearDrawing() {
    if (!canvas.value) return;
    canvas.value
        .getContext('2d')
        ?.clearRect(0, 0, canvas.value.width, canvas.value.height);
    hasDrawing.value = false;
    emit('update:modelValue', null);
}

function setMode(value: 'drawn' | 'uploaded') {
    mode.value = value;
    emit('update:modelValue', null);
    emit('update:method', value);
}

function uploadSignature(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;

    if (uploadedPreview.value) URL.revokeObjectURL(uploadedPreview.value);
    uploadedPreview.value = URL.createObjectURL(file);
    emit('update:modelValue', file);
    emit('update:method', 'uploaded');
}

onBeforeUnmount(() => {
    if (uploadedPreview.value) URL.revokeObjectURL(uploadedPreview.value);
});
</script>

<template>
    <div class="border-y border-stone-300 py-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-stone-900">
                    {{ label }}
                </p>
                <p class="text-xs text-stone-500">
                    {{ description }}
                </p>
            </div>
            <div class="flex gap-2">
                <Button
                    type="button"
                    size="sm"
                    :variant="mode === 'drawn' ? 'default' : 'outline'"
                    :disabled="disabled"
                    @click="setMode('drawn')"
                >
                    <PenLine class="mr-1.5 h-4 w-4" /> Draw
                </Button>
                <Button
                    type="button"
                    size="sm"
                    :variant="mode === 'uploaded' ? 'default' : 'outline'"
                    :disabled="disabled"
                    @click="setMode('uploaded')"
                >
                    <ImageUp class="mr-1.5 h-4 w-4" /> Upload
                </Button>
            </div>
        </div>

        <div v-if="mode === 'drawn'" class="mt-4">
            <canvas
                ref="canvas"
                width="800"
                height="220"
                class="h-44 w-full touch-none border-b-2 border-stone-700 bg-white"
                @pointerdown="startDrawing"
                @pointermove="continueDrawing"
                @pointerup="finishDrawing"
                @pointercancel="finishDrawing"
            />
            <div class="mt-2 flex items-center justify-between">
                <span class="text-xs text-stone-500">Sign above the line</span>
                <button
                    type="button"
                    class="inline-flex items-center gap-1 text-xs text-stone-600 hover:text-stone-900"
                    @click="clearDrawing"
                >
                    <RotateCcw class="h-3.5 w-3.5" /> Clear signature
                </button>
            </div>
        </div>

        <label
            v-else
            class="mt-4 flex min-h-44 cursor-pointer flex-col items-center justify-center border border-dashed border-stone-400 bg-white p-5 text-center"
        >
            <img
                v-if="uploadedPreview"
                :src="uploadedPreview"
                alt="Uploaded owner signature"
                class="mb-3 max-h-24 max-w-full object-contain"
            />
            <ImageUp v-else class="mb-2 h-7 w-7 text-stone-400" />
            <span class="text-sm font-medium text-stone-800"
                >Choose signature image</span
            >
            <span class="mt-1 text-xs text-stone-500"
                >PNG or JPG, maximum 2MB</span
            >
            <input
                type="file"
                accept="image/png,image/jpeg"
                class="sr-only"
                :disabled="disabled"
                @change="uploadSignature"
            />
        </label>

        <p v-if="error" class="mt-2 text-xs text-red-600">{{ error }}</p>
    </div>
</template>
