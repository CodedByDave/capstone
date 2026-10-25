<script setup lang="ts">
import AttendanceCard from '@/components/driver/AttendanceCard.vue';
import DriverLayout from '@/components/driver/DriverLayout.vue';
import JobCard from '@/components/driver/JobCard.vue';
import type {
    DriverAttendance,
    DriverInfo,
    DriverJob,
    EarningsSummary,
} from '@/types/driver';
import { Link, router } from '@inertiajs/vue3';
import { MessageSquare, Navigation, Phone } from 'lucide-vue-next';
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

const tabs = computed(() => [
    {
        key: 'pickups' as const,
        label: `Pickups · ${props.queue.pickups.length}`,
    },
    {
        key: 'deliveries' as const,
        label: `Deliveries · ${props.queue.deliveries.length}`,
    },
    { key: 'done' as const, label: `Done · ${props.queue.done.length}` },
]);

const todayLabel = new Date().toLocaleDateString('en-PH', {
    weekday: 'long',
    month: 'short',
    day: 'numeric',
});

const initials = computed(() =>
    props.driver.name
        .split(' ')
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase(),
);

const progress = computed(() =>
    props.today.jobs_total === 0
        ? 0
        : Math.round(
              (props.today.jobs_completed / props.today.jobs_total) * 100,
          ),
);

const peso = (amount: number | null) =>
    `₱${(amount ?? 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

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
        <header class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span
                    class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-200 text-sm font-semibold text-slate-700"
                    aria-hidden="true"
                >
                    {{ initials }}
                </span>
                <div>
                    <p class="text-xs text-slate-500">{{ todayLabel }}</p>
                    <p class="text-lg leading-tight font-bold">
                        Hi, {{ driver.name.split(' ')[0] }}
                    </p>
                </div>
            </div>

            <button
                type="button"
                role="switch"
                :aria-checked="driver.is_online"
                :disabled="toggling"
                class="flex items-center gap-2 rounded-full border bg-white py-1.5 pr-1.5 pl-4 text-sm font-semibold disabled:opacity-60"
                @click="toggleOnline"
            >
                <span
                    :class="
                        driver.is_online ? 'text-emerald-800' : 'text-slate-500'
                    "
                >
                    {{ driver.is_online ? 'Online' : 'Offline' }}
                </span>
                <span
                    :class="[
                        'relative h-7 w-12 rounded-full transition-colors',
                        driver.is_online ? 'bg-blue-600' : 'bg-slate-300',
                    ]"
                >
                    <span
                        :class="[
                            'absolute top-0.5 h-6 w-6 rounded-full bg-white shadow transition-all',
                            driver.is_online ? 'left-[22px]' : 'left-0.5',
                        ]"
                    />
                </span>
            </button>
        </header>

        <p
            v-if="!driver.is_online"
            class="mt-3 rounded-xl bg-amber-50 px-3 py-2 text-xs text-amber-800"
        >
            You are offline, so new orders are not assigned to you. Switch to
            online to receive orders.
        </p>

        <AttendanceCard :attendance="attendance" />

        <!-- Today -->
        <section class="mt-4 rounded-3xl bg-[#12359b] p-4 text-white">
            <template v-if="today.earnings.enabled">
                <p class="text-xs text-blue-100">Today's earnings</p>
                <p class="mt-1 text-4xl font-bold tracking-tight">
                    {{ peso(today.earnings.amount) }}
                </p>
            </template>
            <template v-else>
                <p class="text-xs text-blue-100">Today's jobs</p>
                <p class="mt-1 text-4xl font-bold tracking-tight">
                    {{ today.jobs_completed }}
                </p>
            </template>

            <div
                class="mt-4 flex items-center justify-between text-xs text-blue-100"
            >
                <span>Jobs completed</span>
                <span class="font-bold text-white">
                    {{ today.jobs_completed }} of {{ today.jobs_total }}
                </span>
            </div>
            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-white/20">
                <div
                    class="h-full rounded-full bg-emerald-300"
                    :style="{ width: `${progress}%` }"
                />
            </div>

            <div class="mt-4 grid grid-cols-3 gap-2">
                <div class="rounded-xl bg-blue-600 p-3">
                    <p class="text-lg font-bold">{{ today.distance_km }} km</p>
                    <p class="text-[11px] text-blue-100">Distance</p>
                </div>
                <div class="rounded-xl bg-blue-600 p-3">
                    <p class="text-lg font-bold">{{ today.orders }}</p>
                    <p class="text-[11px] text-blue-100">Orders handled</p>
                </div>
                <div class="rounded-xl bg-blue-600 p-3">
                    <p class="text-lg font-bold">{{ today.rating ?? '—' }}</p>
                    <p class="text-[11px] text-blue-100">Rating</p>
                </div>
            </div>
        </section>

        <!-- Next stop -->
        <section class="mt-5">
            <h2 class="text-base font-bold">Next stop</h2>

            <div
                v-if="!next_stop"
                class="mt-2 rounded-2xl border border-dashed bg-white p-6 text-center text-sm text-slate-500"
            >
                No orders assigned right now.
            </div>

            <div
                v-else
                class="mt-2 rounded-2xl border-2 border-orange-200 bg-white p-4"
            >
                <div class="flex items-center justify-between">
                    <span
                        :class="[
                            'rounded-md px-2.5 py-1 text-[11px] font-bold tracking-wide uppercase',
                            next_stop.type === 'pickup'
                                ? 'bg-orange-100 text-orange-700'
                                : 'bg-blue-100 text-blue-700',
                        ]"
                    >
                        {{ next_stop.type }}
                    </span>
                    <span
                        v-if="next_stop.distance_km"
                        class="text-sm text-slate-500"
                    >
                        {{ next_stop.distance_km }} km
                    </span>
                </div>

                <p class="mt-3 text-lg font-bold">
                    {{ next_stop.target.label }}
                </p>
                <p class="text-sm text-slate-500">
                    {{ next_stop.target.address }}
                </p>

                <div class="mt-3 flex flex-wrap gap-2 text-xs text-slate-600">
                    <span
                        v-if="next_stop.service"
                        class="rounded-lg bg-slate-100 px-2.5 py-1"
                    >
                        {{ next_stop.service }}
                    </span>
                    <span
                        v-if="next_stop.order_number"
                        class="rounded-lg bg-slate-100 px-2.5 py-1"
                    >
                        Order #{{ next_stop.order_number }}
                    </span>
                </div>

                <div class="mt-4 flex gap-2">
                    <Link
                        :href="`/driver/jobs/${next_stop.public_id}`"
                        class="flex h-12 flex-1 items-center justify-center gap-2 rounded-xl bg-blue-600 font-semibold text-white"
                    >
                        <Navigation class="h-4 w-4" aria-hidden="true" />
                        {{
                            next_stop.type === 'pickup'
                                ? 'Start pickup'
                                : 'Start delivery'
                        }}
                    </Link>
                    <a
                        v-if="next_stop.customer_phone"
                        :href="`tel:${next_stop.customer_phone}`"
                        class="flex h-12 w-12 items-center justify-center rounded-xl border bg-white"
                        aria-label="Call customer"
                    >
                        <Phone class="h-5 w-5" />
                    </a>
                    <a
                        v-if="next_stop.customer_phone"
                        :href="`sms:${next_stop.customer_phone}`"
                        class="flex h-12 w-12 items-center justify-center rounded-xl border bg-white"
                        aria-label="Message customer"
                    >
                        <MessageSquare class="h-5 w-5" />
                    </a>
                </div>
            </div>
        </section>

        <!-- Queue -->
        <section class="mt-5">
            <h2 class="text-base font-bold">Today's queue</h2>

            <div
                class="mt-2 grid grid-cols-3 gap-1 rounded-2xl bg-slate-200 p-1"
            >
                <button
                    v-for="item in tabs"
                    :key="item.key"
                    type="button"
                    :class="[
                        'rounded-xl py-2 text-xs font-semibold',
                        tab === item.key
                            ? 'bg-white shadow-sm'
                            : 'text-slate-600',
                    ]"
                    @click="tab = item.key"
                >
                    {{ item.label }}
                </button>
            </div>

            <div class="mt-3 space-y-2">
                <JobCard
                    v-for="job in queue[tab]"
                    :key="job.public_id"
                    :job="job"
                />
                <p
                    v-if="queue[tab].length === 0"
                    class="rounded-2xl bg-white p-6 text-center text-sm text-slate-500"
                >
                    Nothing here yet.
                </p>
            </div>
        </section>
    </DriverLayout>
</template>
