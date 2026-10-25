<script setup lang="ts">
import DriverLayout from '@/components/driver/DriverLayout.vue';
import type { DriverInfo, EarningsSummary } from '@/types/driver';
import { EyeOff } from 'lucide-vue-next';

defineProps<{
    driver: DriverInfo;
    today: EarningsSummary;
    earnings: {
        mode: 'none' | 'delivery_fee' | 'daily_rate';
        enabled: boolean;
        total: number | null;
        days: { date: string; jobs: number; amount: number | null }[];
    };
}>();

const peso = (amount: number | null) =>
    `₱${(amount ?? 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const dayLabel = (date: string) =>
    new Date(`${date}T00:00:00`).toLocaleDateString('en-PH', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
    });
</script>

<template>
    <DriverLayout title="Earnings">
        <h1 class="text-xl font-bold">Earnings</h1>

        <div
            v-if="!earnings.enabled"
            class="mt-4 flex items-start gap-3 rounded-2xl bg-white p-5 text-sm text-slate-600"
        >
            <EyeOff class="mt-0.5 h-5 w-5 shrink-0" aria-hidden="true" />
            Your shop does not show earnings in the app.
        </div>

        <template v-else>
            <section class="mt-4 rounded-3xl bg-[#12359b] p-4 text-white">
                <p class="text-xs text-blue-100">Today</p>
                <p class="mt-1 text-4xl font-bold tracking-tight">
                    {{ peso(today.amount) }}
                </p>
                <p class="mt-3 text-xs text-blue-100">
                    Last 14 days · {{ peso(earnings.total) }}
                </p>
            </section>

            <section class="mt-5">
                <h2 class="text-base font-bold">By day</h2>
                <ul class="mt-2 space-y-2">
                    <li
                        v-for="day in earnings.days"
                        :key="day.date"
                        class="flex items-center justify-between rounded-2xl bg-white p-4"
                    >
                        <div>
                            <p class="font-semibold">
                                {{ dayLabel(day.date) }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ day.jobs }}
                                {{ day.jobs === 1 ? 'job' : 'jobs' }}
                            </p>
                        </div>
                        <p class="font-bold">{{ peso(day.amount) }}</p>
                    </li>
                    <li
                        v-if="earnings.days.length === 0"
                        class="rounded-2xl bg-white p-6 text-center text-sm text-slate-500"
                    >
                        No completed jobs in the last 14 days.
                    </li>
                </ul>
            </section>
        </template>
    </DriverLayout>
</template>
