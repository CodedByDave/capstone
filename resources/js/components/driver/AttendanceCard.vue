<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { DriverAttendance } from '@/types/driver';
import { router } from '@inertiajs/vue3';
import {
    CalendarOff,
    Clock,
    Loader2,
    LogIn,
    LogOut,
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

const shiftLabel = computed(() =>
    props.attendance.shift
        ? `${time12(props.attendance.shift.start)} – ${time12(props.attendance.shift.end)}`
        : null,
);

const statusChip = computed(() => {
    switch (props.attendance.record?.status) {
        case 'present':
            return { label: 'Present', cls: 'bg-emerald-100 text-emerald-700' };
        case 'late':
            return { label: 'Late', cls: 'bg-amber-100 text-amber-700' };
        case 'absent':
            return { label: 'Absent', cls: 'bg-red-100 text-red-600' };
        default:
            return null;
    }
});

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
    <section class="mt-4 rounded-2xl bg-white p-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="flex items-center gap-2 text-base font-bold">
                    <Clock class="h-4 w-4" aria-hidden="true" />
                    Attendance
                </h2>
                <p v-if="shiftLabel" class="mt-0.5 text-xs text-slate-500">
                    Scheduled shift today: {{ shiftLabel }}
                </p>
            </div>
            <span
                v-if="statusChip"
                :class="[
                    'rounded-full px-2.5 py-1 text-xs font-semibold',
                    statusChip.cls,
                ]"
            >
                {{ statusChip.label }}
            </span>
        </div>

        <!-- Not scheduled -->
        <div
            v-if="!canClockIn && !clockedIn"
            class="mt-3 flex items-start gap-2 rounded-xl bg-slate-100 p-3 text-xs text-slate-600"
        >
            <CalendarOff class="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true" />
            <span>{{ attendance.blocked_reason }}</span>
        </div>

        <!-- Times -->
        <div
            v-if="clockedIn || attendance.record"
            class="mt-3 grid grid-cols-2 gap-2 text-sm"
        >
            <div class="rounded-xl bg-slate-50 p-3">
                <p class="text-[11px] text-slate-500">Clocked in</p>
                <p class="font-semibold">
                    {{ time12(attendance.record?.time_in ?? null) }}
                </p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
                <p class="text-[11px] text-slate-500">Clocked out</p>
                <p class="font-semibold">
                    {{ time12(attendance.record?.time_out ?? null) }}
                </p>
            </div>
        </div>
        <p
            v-if="attendance.record?.remarks"
            class="mt-2 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700"
        >
            {{ attendance.record.remarks }}
        </p>

        <!-- Location is required -->
        <p
            v-if="locationBlocked && !clockedOut"
            class="mt-3 flex items-start gap-2 rounded-xl bg-amber-50 p-3 text-xs text-amber-800"
            role="alert"
        >
            <MapPinOff class="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true" />
            <span>
                Location is off. Turn it on for this site in your browser or
                phone settings, then try again. You cannot clock in without it.
            </span>
        </p>

        <!-- Actions -->
        <div class="mt-3">
            <Button
                v-if="!clockedIn"
                class="h-12 w-full rounded-xl bg-blue-600 font-semibold hover:bg-blue-700"
                :disabled="!canClockIn || busy || locating"
                @click="clockIn"
            >
                <Loader2
                    v-if="busy || locating"
                    class="mr-2 h-4 w-4 animate-spin"
                />
                <LogIn v-else class="mr-2 h-4 w-4" />
                {{ locating ? 'Getting your location…' : 'Clock in' }}
            </Button>

            <Button
                v-else-if="!clockedOut"
                variant="outline"
                class="h-12 w-full rounded-xl font-semibold"
                :disabled="busy || locating"
                @click="clockOut"
            >
                <Loader2
                    v-if="busy || locating"
                    class="mr-2 h-4 w-4 animate-spin"
                />
                <LogOut v-else class="mr-2 h-4 w-4" />
                Clock out
            </Button>

            <p
                v-else
                class="py-2 text-center text-sm font-semibold text-slate-500"
            >
                Done for today.
            </p>
        </div>
    </section>
</template>
