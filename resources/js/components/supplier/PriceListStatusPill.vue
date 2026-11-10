<script setup lang="ts">
import { StatusPill, type StatusTone } from '@/components/data-table';
import { computed } from 'vue';

export type PriceListStatus =
    | 'pending'
    | 'verified'
    | 'mismatch'
    | 'unreadable';

/** How the OCR check of a supplier's latest price-list photo came out, as a dot-and-label pill. */
const props = defineProps<{ status: PriceListStatus | null | undefined }>();

const config = computed<{ tone: StatusTone; label: string }>(() => {
    switch (props.status) {
        case 'verified':
            return { tone: 'success', label: 'Verified' };
        case 'mismatch':
            return { tone: 'warning', label: 'Mismatch' };
        case 'unreadable':
            return { tone: 'danger', label: 'Unreadable' };
        case 'pending':
            return { tone: 'info', label: 'Checking' };
        default:
            return { tone: 'neutral', label: 'Not uploaded' };
    }
});
</script>

<template>
    <StatusPill :tone="config.tone">{{ config.label }}</StatusPill>
</template>
