<script setup lang="ts">
import DriverLayout from '@/components/driver/DriverLayout.vue';
import JobCard from '@/components/driver/JobCard.vue';
import type { DriverInfo, DriverJob } from '@/types/driver';
import { computed, ref } from 'vue';

const props = defineProps<{
    driver: DriverInfo;
    open: DriverJob[];
    done: DriverJob[];
}>();

const tab = ref<'pickup' | 'delivered' | 'done'>('pickup');

const tabs = [
    { key: 'pickup' as const, label: 'Pickup' },
    { key: 'delivered' as const, label: 'Delivered' },
    { key: 'done' as const, label: 'Done' },
];

const titles = {
    pickup: 'Pickup List',
    delivered: 'Delivery List',
    done: 'Finished Today',
};

const list = computed(() => {
    if (tab.value === 'pickup') {
        return props.open.filter((job) => job.type === 'pickup');
    }
    if (tab.value === 'delivered') {
        return props.open.filter((job) => job.type === 'delivery');
    }
    return props.done;
});
</script>

<template>
    <DriverLayout title="Dispatch">
        <h1 class="mt-6 text-[28px] leading-tight font-bold">Dispatch</h1>

        <div
            class="mt-4 grid grid-cols-3 rounded-2xl bg-[#f3f3f3] p-1.5"
            role="tablist"
        >
            <button
                v-for="item in tabs"
                :key="item.key"
                type="button"
                role="tab"
                :aria-selected="tab === item.key"
                :class="[
                    'rounded-xl py-2 text-xs font-semibold',
                    tab === item.key
                        ? 'bg-slate-300 text-slate-800'
                        : 'text-slate-600',
                ]"
                @click="tab = item.key"
            >
                {{ item.label }}
            </button>
        </div>

        <h2 class="mt-6 text-[11px] font-semibold text-slate-700">
            {{ titles[tab] }}
        </h2>

        <div class="mt-2 min-h-[24rem] rounded-2xl bg-[#f3f3f3] px-3 py-2">
            <JobCard v-for="job in list" :key="job.public_id" :job="job" />
            <p
                v-if="list.length === 0"
                class="py-10 text-center text-sm text-slate-500"
            >
                Nothing here yet.
            </p>
        </div>
    </DriverLayout>
</template>
