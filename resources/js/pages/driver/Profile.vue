<script setup lang="ts">
import AttendanceCard from '@/components/driver/AttendanceCard.vue';
import DriverLayout from '@/components/driver/DriverLayout.vue';
import type {
    AttendanceHistoryItem,
    DriverAttendance,
    DriverInfo,
} from '@/types/driver';
import { router } from '@inertiajs/vue3';
import { LogOut } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue3-toastify';

const props = defineProps<{
    driver: DriverInfo;
    rating: number | null;
    attendance: DriverAttendance;
    attendance_history: AttendanceHistoryItem[];
}>();

const initials = computed(() =>
    props.driver.name
        .split(' ')
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase(),
);

const dayLabel = (date: string) =>
    new Date(`${date}T00:00:00`).toLocaleDateString('en-PH', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
    });

function time12(value: string | null): string {
    if (!value) return '—';
    const [h, m] = value.split(':');
    const hour = parseInt(h, 10);
    return `${hour % 12 || 12}:${m} ${hour >= 12 ? 'PM' : 'AM'}`;
}

const statusClass = (status: string) =>
    ({
        present: 'bg-[#c9f5d6] text-[#17803d]',
        late: 'bg-[#ffe9a8] text-[#8a6100]',
        absent: 'bg-red-100 text-red-600',
    })[status] ?? 'bg-slate-100 text-slate-600';

const photoInput = ref<HTMLInputElement | null>(null);
const uploading = ref(false);

function onPhotoPicked(event: Event) {
    const input = event.target as HTMLInputElement;
    const photo = input.files?.[0];
    if (!photo) return;

    uploading.value = true;
    router.post(
        '/profile/photo',
        { photo },
        {
            forceFormData: true,
            preserveScroll: true,
            onError: (errors) => toast.error(errors.photo ?? 'Upload failed.'),
            onFinish: () => {
                uploading.value = false;
                input.value = '';
            },
        },
    );
}

function logout() {
    router.post('/logout');
}
</script>

<template>
    <DriverLayout title="Profile">
        <h1 class="mt-4 text-2xl font-bold">Profile</h1>

        <section
            class="mt-3 flex items-center gap-4 rounded-2xl bg-[#f3f3f3] p-4"
        >
            <img
                v-if="driver.avatar"
                :src="driver.avatar"
                :alt="`${driver.name} profile photo`"
                class="h-20 w-20 shrink-0 rounded-full object-cover"
            />
            <span
                v-else
                class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-slate-300 text-2xl font-bold text-slate-500"
                aria-hidden="true"
            >
                {{ initials }}
            </span>
            <div class="min-w-0 text-[11px]">
                <p class="flex items-center gap-2">
                    <span class="truncate text-sm font-semibold">{{
                        driver.name
                    }}</span>
                    <span
                        class="rounded-md bg-[#5b6cff] px-2 py-0.5 text-[10px] font-semibold text-white"
                        >Driver</span
                    >
                </p>
                <p v-if="driver.email" class="mt-0.5 truncate underline">
                    {{ driver.email }}
                </p>
                <p v-if="driver.phone" class="mt-0.5">{{ driver.phone }}</p>
                <input
                    ref="photoInput"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    class="hidden"
                    @change="onPhotoPicked"
                />
                <button
                    type="button"
                    :disabled="uploading"
                    class="mt-2 rounded-md bg-slate-300 px-2 py-1 text-[10px] font-medium text-slate-700 disabled:opacity-60"
                    @click="photoInput?.click()"
                >
                    {{ uploading ? 'Uploading…' : 'Upload Profile' }}
                </button>
            </div>
        </section>

        <h2 class="mt-6 text-2xl font-bold">Schedule</h2>
        <AttendanceCard :attendance="attendance" />

        <section v-if="attendance_history.length" class="mt-6">
            <h2 class="text-base font-bold">Attendance · last 14 days</h2>
            <ul class="mt-2 divide-y divide-slate-300 rounded-2xl bg-[#f3f3f3]">
                <li
                    v-for="item in attendance_history"
                    :key="item.date"
                    class="flex items-center justify-between px-4 py-3 text-sm"
                >
                    <div>
                        <p class="font-semibold">{{ dayLabel(item.date) }}</p>
                        <p class="text-[11px] text-slate-500">
                            {{ time12(item.time_in) }} –
                            {{ time12(item.time_out) }}
                        </p>
                    </div>
                    <span
                        :class="[
                            'rounded-full px-2.5 py-1 text-[10px] font-semibold capitalize',
                            statusClass(item.status),
                        ]"
                    >
                        {{ item.status }}
                    </span>
                </li>
            </ul>
        </section>

        <button
            type="button"
            class="mt-6 flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-slate-200 text-sm font-semibold text-slate-700"
            @click="logout"
        >
            <LogOut class="h-4 w-4" aria-hidden="true" />
            Log out
        </button>
    </DriverLayout>
</template>
