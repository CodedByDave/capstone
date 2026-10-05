<script setup lang="ts">
defineProps<{
    agreement: { title: string; version: string; content: string; effective_at?: string; accepted_at?: string; customer_name?: string };
    modelValue?: boolean;
    confirmationText?: string;
    readonly?: boolean;
}>();

defineEmits<{ 'update:modelValue': [value: boolean] }>();
</script>

<template>
    <div class="rounded-xl border border-blue-200 bg-blue-50/60 p-4 text-sm">
        <div class="flex items-start justify-between gap-3">
            <div><p class="font-semibold text-gray-900">{{ agreement.title }}</p><p class="text-xs text-gray-500">Version {{ agreement.version }}</p></div>
            <span v-if="readonly" class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Accepted</span>
        </div>
        <details class="mt-3 rounded-lg border border-blue-100 bg-white">
            <summary class="cursor-pointer px-3 py-2 font-medium text-blue-700">Read the complete agreement</summary>
            <div class="max-h-72 overflow-y-auto whitespace-pre-wrap border-t border-blue-100 px-3 py-3 text-xs leading-5 text-gray-600">{{ agreement.content }}</div>
        </details>
        <p v-if="readonly && agreement.accepted_at" class="mt-3 text-xs text-gray-500">Accepted by {{ agreement.customer_name || 'customer' }} on {{ new Date(agreement.accepted_at).toLocaleString('en-PH') }}.</p>
        <label v-if="!readonly" class="mt-3 flex cursor-pointer items-start gap-2 text-xs leading-5 text-gray-700">
            <input type="checkbox" class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600" :checked="modelValue" @change="$emit('update:modelValue', ($event.target as HTMLInputElement).checked)" />
            <span>{{ confirmationText || 'I have read and accept this laundry service agreement, including its garment damage, refund, compensation, and claim terms.' }}</span>
        </label>
    </div>
</template>
