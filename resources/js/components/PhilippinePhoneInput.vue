<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { computed, useAttrs } from 'vue';

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
