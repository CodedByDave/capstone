<script setup lang="ts">
import type { DriverAttendance } from '@/types/driver';
import { router } from '@inertiajs/vue3';
import {
    ArrowDownLeft,
    CalendarOff,
    Loader2,
    MapPinOff,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue3-toastify';

const props = defineProps<{ attendance: DriverAttendance }>();

const busy = ref(false);
const locating = ref(false);
const locationBlocked = ref(false);

// Find out early whether the browser already refuses location, so the driver is told before
// pressing the button.
onMounted(async () => {
    try {
        const status = await navigator.permissions?.query({
            name: 'geolocation' as PermissionName,
        });
        if (status) {
            locationBlocked.value = status.state === 'denied';
            status.onchange = () => {
                locationBlocked.value = status.state === 'denied';
            };
        }
    } catch {
        // Not every browser can report the permission; the button still asks on press.
    }
});

const clockedIn = computed(() => props.attendance.has_clocked_in);
const clockedOut = computed(() => props.attendance.has_clocked_out);
const canClockIn = computed(() => props.attendance.blocked_reason === null);

function time12(value: string | null): string {
    if (!value) return '—';
    const [h, m] = value.split(':');
    const hour = parseInt(h, 10);
    return `${hour % 12 || 12}:${m} ${hour >= 12 ? 'PM' : 'AM'}`;
}

const compact = (value: string | null): string => {
    if (!value) return '';
    const [h, m] = value.split(':');
    const hour = parseInt(h, 10);
    return `${hour % 12 || 12}${m === '00' ? '' : `:${m}`}${hour >= 12 ? 'PM' : 'AM'}`;
};

const shiftLabel = computed(() =>
    props.attendance.shift
        ? `${compact(props.attendance.shift.start)}-${compact(props.attendance.shift.end)}`
        : null,
);

const dayName = new Date().toLocaleDateString('en-US', { weekday: 'long' });

/** Asks for the phone's position. Nothing is sent to the server unless this succeeds. */
function requestLocation(
    required: boolean,
): Promise<{ lat: number; lng: number } | null> {
    return new Promise((resolve) => {
        if (!('geolocation' in navigator)) {
            if (required) toast.error('This device cannot share its location.');
            resolve(null);
            return;
        }

        locating.value = true;
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                locating.value = false;
                locationBlocked.value = false;
                resolve({
                    lat: pos.coords.latitude,
                    lng: pos.coords.longitude,
                });
            },
            () => {
                locating.value = false;
                if (required) {
                    locationBlocked.value = true;
                    toast.error('Turn on your location to clock in.');
                }
                resolve(null);
            },
            { enableHighAccuracy: true, timeout: 10_000 },
        );
    });
}

async function clockIn() {
    if (!canClockIn.value) return;

    const position = await requestLocation(true);
    if (!position) return;

    busy.value = true;
    router.post(
        '/driver/attendance/clock-in',
        { latitude: position.lat, longitude: position.lng },
        { preserveScroll: true, onFinish: () => (busy.value = false) },
    );
}

async function clockOut() {
    // Location is optional when clocking out, but sent when the phone shares it.
    const position = await requestLocation(false);

    busy.value = true;
    router.post(
        '/driver/attendance/clock-out',
        { latitude: position?.lat ?? null, longitude: position?.lng ?? null },
        { preserveScroll: true, onFinish: () => (busy.value = false) },
    );
}
</script>

<template>
    <section class="mt-3 rounded-2xl bg-[#f3f3f3] p-4">
        <div class="flex items-center gap-3">
            <div class="min-w-0">
                <p class="text-sm font-semibold">{{ dayName }}</p>
                <p class="text-[10px] text-slate-500">
                    {{ shiftLabel ?? 'No shift' }}
                </p>
            </div>

            <button
                type="button"
                class="rounded-full bg-[#5b6cff] px-3 py-1 text-[11px] font-semibold text-white disabled:opacity-50"
                :disabled="clockedIn || !canClockIn || busy || locating"
                @click="clockIn"
            >
                <Loader2
                    v-if="(busy || locating) && !clockedIn"
                    class="mr-1 inline h-3 w-3 animate-spin"
                />
                Clock-in
            </button>
            <button
                type="button"
                class="rounded-full bg-[#ff5b5b] px-3 py-1 text-[11px] font-semibold text-white disabled:opacity-50"
                :disabled="!clockedIn || clockedOut || busy || locating"
                @click="clockOut"
            >
                <Loader2
                    v-if="(busy || locating) && clockedIn"
                    class="mr-1 inline h-3 w-3 animate-spin"
                />
                Clock-out
            </button>
            <ArrowDownLeft
                class="ml-auto h-4 w-4 shrink-0 text-slate-500"
                aria-hidden="true"
            />
        </div>

        <p
            v-if="clockedIn || attendance.record"
            class="mt-2 text-[11px] text-slate-600"
        >
            In {{ time12(attendance.record?.time_in ?? null) }} · Out
            {{ time12(attendance.record?.time_out ?? null) }}
        </p>

        <p
            v-if="!canClockIn && !clockedIn"
            class="mt-2 flex items-start gap-2 text-[11px] text-slate-600"
        >
            <CalendarOff
                class="mt-0.5 h-3.5 w-3.5 shrink-0"
                aria-hidden="true"
            />
            {{ attendance.blocked_reason }}
        </p>
        <p
            v-if="attendance.record?.remarks"
            class="mt-2 rounded-lg bg-red-50 px-3 py-2 text-[11px] text-red-700"
        >
            {{ attendance.record.remarks }}
        </p>
        <p
            v-if="locationBlocked && !clockedOut"
            class="mt-2 flex items-start gap-2 rounded-lg bg-amber-50 p-2 text-[11px] text-amber-800"
            role="alert"
        >
            <MapPinOff class="mt-0.5 h-3.5 w-3.5 shrink-0" aria-hidden="true" />
            Location is off. Turn it on for this site, then try again. You
            cannot clock in without it.
        </p>
    </section>
</template>
