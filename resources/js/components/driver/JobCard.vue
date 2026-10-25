<script setup lang="ts">
import type { DriverJob } from '@/types/driver';
import { Link } from '@inertiajs/vue3';
import {
    ArrowDownToLine,
    ArrowUpFromLine,
    ChevronRight,
} from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{ job: DriverJob }>();

const isPickup = computed(() => props.job.type === 'pickup');

const time = computed(() => {
    const at = props.job.finished_at ?? props.job.assigned_at;
    return at
        ? new Date(at).toLocaleTimeString('en-PH', {
              hour: '2-digit',
              minute: '2-digit',
          })
        : '';
});

const statusLabel = computed(
    () =>
        ({
            assigned: 'Assigned',
            picked_up: 'On the way',
            delivered: 'Done',
            failed: 'Failed',
        })[props.job.status],
);
</script>

<template>
    <Link
        :href="`/driver/jobs/${job.public_id}`"
        class="flex items-center gap-3 rounded-2xl bg-white p-3 shadow-sm"
    >
        <span
            :class="[
                'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl',
                isPickup
                    ? 'bg-orange-100 text-orange-700'
                    : 'bg-blue-100 text-blue-700',
            ]"
        >
            <ArrowUpFromLine
                v-if="isPickup"
                class="h-5 w-5"
                aria-hidden="true"
            />
            <ArrowDownToLine v-else class="h-5 w-5" aria-hidden="true" />
        </span>

        <span class="min-w-0 flex-1">
            <span class="flex items-baseline justify-between gap-2">
                <span class="truncate font-semibold">{{
                    job.customer_name
                }}</span>
                <span class="shrink-0 text-sm font-medium">{{ time }}</span>
            </span>
            <span class="block truncate text-xs text-slate-500">
                {{ job.address ?? 'No address' }}
            </span>
            <span
                class="mt-1 flex items-center gap-2 text-[11px] text-slate-500"
            >
                <span v-if="job.order_number">#{{ job.order_number }}</span>
                <span
                    class="rounded-full bg-slate-100 px-2 py-0.5 font-medium text-slate-600"
                >
                    {{ statusLabel }}
                </span>
            </span>
        </span>

        <ChevronRight
            class="h-4 w-4 shrink-0 text-slate-400"
            aria-hidden="true"
        />
    </Link>
</template>
