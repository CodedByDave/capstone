<script setup lang="ts">
import DriverLayout from '@/components/driver/DriverLayout.vue';
import JobCard from '@/components/driver/JobCard.vue';
import type {
    DriverAttendance,
    DriverInfo,
    DriverJob,
    EarningsSummary,
} from '@/types/driver';
import { Link, router } from '@inertiajs/vue3';
import {
    Bell,
    MapPin,
    MessageSquare,
    Phone,
    Send,
    User,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    driver: DriverInfo;
    attendance: DriverAttendance;
    today: {
        earnings: EarningsSummary;
        jobs_completed: number;
        jobs_total: number;
        distance_km: number;
        orders: number;
        rating: number | null;
    };
    next_stop: DriverJob | null;
    queue: { pickups: DriverJob[]; deliveries: DriverJob[]; done: DriverJob[] };
}>();

const tab = ref<'pickups' | 'deliveries' | 'done'>('pickups');

const tabs = [
    { key: 'pickups' as const, label: 'Pickup' },
    { key: 'deliveries' as const, label: 'Delivered' },
    { key: 'done' as const, label: 'Done' },
];

const todayLabel = new Date().toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
});

/** One bar per job today: green once done, white while still to do. */
const bars = computed(() =>
    Array.from({ length: Math.min(props.today.jobs_total, 8) }, (_, index) =>
        index < props.today.jobs_completed ? 'bg-[#22e06b]' : 'bg-white',
    ),
);

const toggling = ref(false);

function toggleOnline() {
    toggling.value = true;
    router.patch(
        '/driver/availability',
        { online: !props.driver.is_online },
        { preserveScroll: true, onFinish: () => (toggling.value = false) },
    );
}
</script>

<template>
    <DriverLayout title="Driver">
        <!-- Header -->
        <header class="mt-8 flex items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl leading-tight font-bold">
                    Hi, {{ driver.name }}
                </h1>
                <p class="mt-0.5 flex items-center gap-1 text-[10px]">
                    Status :
                    <button
                        type="button"
                        role="switch"
                        :aria-checked="driver.is_online"
                        :disabled="toggling"
                        :class="[
                            'rounded-full px-2 py-0.5 text-[10px] font-medium disabled:opacity-60',
                            driver.is_online
                                ? 'bg-[#c9f5d6] text-[#17803d]'
                                : 'bg-slate-200 text-slate-600',
                        ]"
                        @click="toggleOnline"
                    >
                        {{ driver.is_online ? 'Online' : 'Offline' }}
                    </button>
                </p>
            </div>
            <button
                type="button"
                class="p-1 text-[#0a2dff]"
                aria-label="Notifications"
            >
                <Bell class="h-5 w-5" />
            </button>
        </header>

        <!-- Jobs today -->
        <section
            class="mt-4 overflow-hidden rounded-2xl bg-[#0a2dff] text-white"
        >
            <div class="px-4 pt-4 pb-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-semibold">Jobs Today</span>
                    <span class="font-semibold">{{ todayLabel }}</span>
                </div>
                <p class="mt-1 text-[34px] leading-none font-bold">
                    {{ today.jobs_completed }}
                    <span class="text-xs font-normal"
                        >of {{ today.jobs_total }} done</span
                    >
                </p>
                <div class="mt-2 flex gap-1.5" aria-hidden="true">
                    <span
                        v-for="(color, index) in bars"
                        :key="index"
                        :class="['h-[3px] flex-1 rounded-full', color]"
                    />
                </div>
            </div>

            <div
                class="grid grid-cols-[1fr_1fr_1.4fr] border-t border-white/60 text-[10px]"
            >
                <div class="px-3 py-2.5">
                    <p>Distance</p>
                    <p class="text-lg leading-tight font-bold">
                        {{ today.distance_km }}
                        <span class="text-[10px] font-normal">km</span>
                    </p>
                </div>
                <div class="border-x border-white/60 px-3 py-2.5">
                    <p>Bags Handled</p>
                    <p class="text-lg leading-tight font-bold">
                        {{ today.orders }}
                    </p>
                </div>
                <div class="px-3 py-2.5">
                    <p>Ratings</p>
                    <p class="text-xs leading-tight font-semibold">
                        {{ today.rating ?? 'No ratings yet' }}
                    </p>
                </div>
            </div>
        </section>

        <p
            v-if="!driver.is_online"
            class="mt-3 rounded-xl bg-amber-50 px-3 py-2 text-xs text-amber-800"
        >
            You are offline, so new orders are not assigned to you.
        </p>

        <!-- New task -->
        <section class="mt-6">
            <h2 class="text-xl font-bold">New Task</h2>

            <div
                v-if="!next_stop"
                class="mt-2 rounded-2xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500"
            >
                No orders assigned right now.
            </div>

            <div
                v-else
                class="mt-2 rounded-2xl border border-[#f6c89a] bg-[#fffaf5] p-4"
            >
                <span
                    class="rounded-md bg-[#ffe9a8] px-2 py-0.5 text-[10px] font-semibold text-[#8a6100] capitalize"
                >
                    {{ next_stop.type }}
                </span>

                <p class="mt-3 flex items-center gap-2 text-base font-semibold">
                    <User class="h-4 w-4" aria-hidden="true" />
                    {{ next_stop.target.label }}
                </p>
                <p
                    class="mt-1.5 flex items-start gap-2 text-[11px] text-slate-600"
                >
                    <MapPin
                        class="mt-0.5 h-3.5 w-3.5 shrink-0"
                        aria-hidden="true"
                    />
                    {{ next_stop.target.address }}
                </p>

                <div class="mt-3 flex flex-wrap gap-2 text-[10px] font-medium">
                    <span
                        v-if="next_stop.service"
                        class="rounded-md bg-[#ffe9a8] px-2 py-0.5 text-[#8a6100]"
                    >
                        {{ next_stop.service }}
                    </span>
                    <span
                        v-if="next_stop.order_number"
                        class="rounded-md bg-[#ffe9a8] px-2 py-0.5 text-[#8a6100]"
                    >
                        {{ next_stop.order_number }}
                    </span>
                </div>

                <div class="mt-4 flex gap-2">
                    <Link
                        :href="`/driver/jobs/${next_stop.public_id}`"
                        class="flex h-11 flex-1 items-center justify-center gap-2 rounded-lg bg-[#0a2dff] text-sm font-semibold text-white"
                    >
                        <Send class="h-4 w-4" aria-hidden="true" />
                        {{
                            next_stop.type === 'pickup'
                                ? 'Start pickup'
                                : 'Start delivery'
                        }}
                    </Link>
                    <a
                        v-if="next_stop.customer_phone"
                        :href="`tel:${next_stop.customer_phone}`"
                        class="flex h-11 w-11 items-center justify-center rounded-lg bg-slate-200 text-slate-700"
                        aria-label="Call customer"
                    >
                        <Phone class="h-4 w-4" />
                    </a>
                    <a
                        v-if="next_stop.customer_phone"
                        :href="`sms:${next_stop.customer_phone}`"
                        class="flex h-11 w-11 items-center justify-center rounded-lg bg-slate-200 text-slate-700"
                        aria-label="Message customer"
                    >
                        <MessageSquare class="h-4 w-4" />
                    </a>
                </div>
            </div>
        </section>

        <!-- Today's queue -->
        <section class="mt-6">
            <h2 class="text-xl font-bold">Today’s Queue</h2>

            <div
                class="mt-2 grid grid-cols-3 rounded-2xl bg-[#f3f3f3] p-1.5"
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

            <div
                v-if="queue[tab].length"
                class="mt-3 rounded-2xl bg-[#f3f3f3] px-3 py-1"
            >
                <JobCard
                    v-for="job in queue[tab]"
                    :key="job.public_id"
                    :job="job"
                />
            </div>
        </section>
    </DriverLayout>
</template>
