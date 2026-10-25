<script setup lang="ts">
import DriverLayout from '@/components/driver/DriverLayout.vue';
import JobCard from '@/components/driver/JobCard.vue';
import type { DriverInfo, DriverJob } from '@/types/driver';

defineProps<{ driver: DriverInfo; open: DriverJob[]; done: DriverJob[] }>();
</script>

<template>
    <DriverLayout title="Jobs">
        <h1 class="text-xl font-bold">Jobs</h1>

        <section class="mt-4">
            <h2 class="text-sm font-semibold text-slate-500">
                Active · {{ open.length }}
            </h2>
            <div class="mt-2 space-y-2">
                <JobCard v-for="job in open" :key="job.public_id" :job="job" />
                <p
                    v-if="open.length === 0"
                    class="rounded-2xl bg-white p-6 text-center text-sm text-slate-500"
                >
                    No active jobs. Stay online to receive orders.
                </p>
            </div>
        </section>

        <section class="mt-6">
            <h2 class="text-sm font-semibold text-slate-500">
                Finished today · {{ done.length }}
            </h2>
            <div class="mt-2 space-y-2">
                <JobCard v-for="job in done" :key="job.public_id" :job="job" />
                <p
                    v-if="done.length === 0"
                    class="rounded-2xl bg-white p-6 text-center text-sm text-slate-500"
                >
                    Nothing finished yet today.
                </p>
            </div>
        </section>
    </DriverLayout>
</template>
