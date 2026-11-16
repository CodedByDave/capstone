<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { computed, useAttrs, watch } from 'vue';

defineOptions({ inheritAttrs: false });

const props = defineProps<{
    modelValue?: string | null;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const attrs = useAttrs();

function formatPartial(value: string): string {
    let digits = value.replace(/\D/g, '');

    if (digits.startsWith('63')) digits = digits.slice(2);
    if (digits.startsWith('0')) digits = digits.slice(1);
    digits = digits.slice(0, 10);

    if (!digits) return '+63-';

    let formatted = `+63-${digits.slice(0, 3)}`;
    if (digits.length > 3) formatted += `-${digits.slice(3, 7)}`;
    if (digits.length > 7) formatted += `-${digits.slice(7, 10)}`;

    return formatted;
}

const displayValue = computed(() => formatPartial(props.modelValue ?? ''));

// A number filled in from elsewhere (a saved address, for example) can arrive as 0929…; the box
// already shows it formatted, so the model is brought to the same value the form validates.
watch(
    () => props.modelValue,
    (value) => {
        if (!value) return;

        const formatted = formatPartial(value);

        if (formatted !== value) {
            emit('update:modelValue', formatted === '+63-' ? '' : formatted);
        }
    },
    { immediate: true },
);

function update(value: string | number) {
    const formatted = formatPartial(String(value));

    emit('update:modelValue', formatted === '+63-' ? '' : formatted);
}
</script>

<template>
    <Input
        v-bind="attrs"
        type="tel"
        inputmode="tel"
        autocomplete="tel"
        maxlength="16"
        :model-value="displayValue"
        @update:model-value="update"
    />
</template>
