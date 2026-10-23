<script setup lang="ts">
import { CheckCircle2, CircleDot, Search, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

/** The status pill for a customer complaint, shared by the customer and shop pages. */
const props = defineProps<{ status: string }>();

const config = computed(() => {
    switch (props.status) {
        case 'under_review':
            return {
                label: 'Under review',
                icon: Search,
                classes:
                    'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300',
            };
        case 'resolved':
            return {
                label: 'Resolved',
                icon: CheckCircle2,
                classes:
                    'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300',
            };
        case 'rejected':
            return {
                label: 'Closed',
                icon: XCircle,
                classes:
                    'border-slate-200 bg-slate-100 text-slate-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300',
            };
        default:
            return {
                label: 'Open',
                icon: CircleDot,
                classes:
                    'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300',
            };
    }
});
</script>

<template>
    <span
        class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs font-medium"
        :class="config.classes"
    >
        <component :is="config.icon" class="h-3 w-3" aria-hidden="true" />
        {{ config.label }}
    </span>
</template>
