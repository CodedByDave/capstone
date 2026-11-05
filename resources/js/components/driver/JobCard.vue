<script setup lang="ts">
import type { DriverJob } from '@/types/driver';
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{ job: DriverJob }>();

const initials = computed(() =>
    props.job.customer_name
        .split(' ')
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase(),
);

const status = computed(
    () =>
        ({
            assigned: { label: 'Not done', cls: 'bg-[#ffe9a8] text-[#8a6100]' },
            picked_up: {
                label: 'Picked up',
                cls: 'bg-[#c9f5d6] text-[#17803d]',
            },
            delivered: { label: 'Done', cls: 'bg-[#c9f5d6] text-[#17803d]' },
            failed: { label: 'Failed', cls: 'bg-red-100 text-red-600' },
        })[props.job.status],
);
</script>

<template>
    <Link
        :href="`/driver/jobs/${job.public_id}`"
        class="flex items-center gap-3 border-b border-slate-300 py-3 last:border-b-0"
    >
        <span
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-300 text-xs font-semibold text-slate-600"
            aria-hidden="true"
        >
            {{ initials }}
        </span>

        <span class="min-w-0 flex-1">
            <span class="flex items-center gap-2">
                <span class="truncate text-sm font-semibold text-slate-700">{{
                    job.customer_name
                }}</span>
                <span
                    :class="[
                        'rounded-full px-2 py-0.5 text-[10px] font-semibold',
                        status.cls,
                    ]"
                >
                    {{ status.label }}
                </span>
            </span>
            <span class="block truncate text-[11px] text-slate-500">
                {{ job.address ?? 'No address' }}
            </span>
            <span
                v-if="job.order_number"
                class="block text-[11px] font-semibold text-slate-600"
            >
                {{ job.order_number }}
            </span>
        </span>

        <ArrowRight
            class="h-4 w-4 shrink-0 text-slate-500"
            aria-hidden="true"
        />
    </Link>
</template>
