<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted, watch } from 'vue'
import { toast } from 'vue-sonner'
import {
    UserCircle, Package, ClipboardList, Tags, BarChart3,
    ShieldAlert, ChevronRight, LogIn, LogOut, MapPin,
    MapPinOff, Clock, CheckCircle2, XCircle, Loader2,
    CalendarClock,
} from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface TodayAttendance {
    status: string
    time_in: string | null
    time_out: string | null
    remarks: string | null
}

interface Schedule {
    day: string
    start_time: string | null
    end_time: string | null
}

interface OwnAttendance {
    date: string
    status: string
    time_in: string | null
    time_out: string | null
    remarks: string | null
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    employee: {
        id: number
        first_name: string
        last_name: string
        position: string
        branch_name: string | null
    }
    permissions: Record<string, string[]>
    today_attendance: TodayAttendance | null
    shop_has_geo: boolean
    schedules: Schedule[]
    own_attendances: OwnAttendance[]
}>()

// ─── Flash toast ──────────────────────────────────────────────────────────────

const page = usePage()
onMounted(() => {
    const flash = page.props.toast as { type: string; message: string } | undefined
    if (!flash) return
    if (flash.type === 'success') toast.success(flash.message)
    else if (flash.type === 'error') toast.error(flash.message)
    else toast(flash.message)
})

// ─── Clock-in / Clock-out ─────────────────────────────────────────────────────

const attendance = ref<TodayAttendance | null>(props.today_attendance)
const locating   = ref(false)
const clocking   = ref(false)

const hasClockedIn  = computed(() => !!attendance.value?.time_in)
const hasClockedOut = computed(() => !!attendance.value?.time_out)

// ─── Schedule check ───────────────────────────────────────────────────────────

const DAY_NAMES = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
const todayName = DAY_NAMES[new Date().getDay()]

const todaySchedule = computed<Schedule | null>(
    () => props.schedules.find(s => s.day === todayName) ?? null
)

// True only when today matches a scheduled work day.
// If no schedule is configured at all, clock-in is disabled — the owner must
// set up the employee's weekly schedule first.
const isWorkDay = computed(() => {
    if (props.schedules.length === 0) return false  // no schedule set → no work days
    return todaySchedule.value !== null
})

// Human-readable shift string, e.g. "8:00 AM – 5:00 PM"
const shiftLabel = computed(() => {
    const s = todaySchedule.value
    if (!s) return null
    return `${fmt12(s.start_time)} – ${fmt12(s.end_time)}`
})

function fmt12(t: string | null) {
    if (!t) return '—'
    const [h, m] = t.split(':')
    const hour = parseInt(h)
    const ampm = hour >= 12 ? 'PM' : 'AM'
    return `${hour % 12 || 12}:${m} ${ampm}`
}

const attendanceStatusCfg = computed(() => {
    const s = attendance.value?.status
    if (s === 'present') return { label: 'Present', cls: 'bg-emerald-100 text-emerald-700' }
    if (s === 'absent')  return { label: 'Absent',  cls: 'bg-red-100 text-red-600' }
    if (s === 'late')    return { label: 'Late',     cls: 'bg-amber-100 text-amber-700' }
    return null
})

function fmt(t: string | null) {
    if (!t) return '—'
    const [h, m] = t.split(':')
    const hour = parseInt(h)
    const ampm = hour >= 12 ? 'PM' : 'AM'
    const h12  = hour % 12 || 12
    return `${h12}:${m} ${ampm}`
}

function doClockAction(action: 'clock-in' | 'clock-out') {
    const submit = (lat: number | null, lng: number | null) => {
        clocking.value = true
        router.post(
            `/staff/attendance/${action}`,
            { latitude: lat, longitude: lng },
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Reload page props to get fresh attendance
                    router.reload({ only: ['today_attendance'] })
                },
                onFinish: () => { clocking.value = false },
            }
        )
    }

    if (!props.shop_has_geo) {
        // Shop has no coordinates configured — skip geo check
        submit(null, null)
        return
    }

    locating.value = true
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            locating.value = false
            submit(pos.coords.latitude, pos.coords.longitude)
        },
        () => {
            locating.value = false
            toast.error('Could not get your location. Please enable location access and try again.')
        },
        { enableHighAccuracy: true, timeout: 10_000 }
    )
}

// Re-sync attendance when Inertia reloads props after clock-in/out
watch(
    () => (page.props as any).today_attendance as TodayAttendance | null,
    (val) => { attendance.value = val },
)

// ─── Module cards ─────────────────────────────────────────────────────────────

const moduleIconMap: Record<string, { icon: any; href: string; description: string; color: string }> = {
    'HRM':                  { icon: UserCircle,    href: '/staff/employee',  description: 'View and manage employees',    color: 'bg-blue-100 text-blue-600'    },
    'Employee Management':  { icon: UserCircle,    href: '/staff/employee',  description: 'View and manage employees',    color: 'bg-blue-100 text-blue-600'    },
    'Inventory Management': { icon: Package,       href: '/staff/inventory', description: 'Track stock and supplies',     color: 'bg-teal-100 text-teal-600'    },
    'Order Management':     { icon: ClipboardList, href: '/staff/orders',    description: 'Process customer orders',      color: 'bg-orange-100 text-orange-600' },
    'Services & Pricing':   { icon: Tags,          href: '/staff/services',  description: 'Manage services and pricing',  color: 'bg-yellow-100 text-yellow-600' },
    'Reports & Analytics':  { icon: BarChart3,     href: '/staff/reports',   description: 'View reports and analytics',   color: 'bg-green-100 text-green-600'  },
}

const actionColorMap: Record<string, string> = {
    view:    'bg-blue-50 text-blue-700 border border-blue-200',
    create:  'bg-green-50 text-green-700 border border-green-200',
    update:  'bg-yellow-50 text-yellow-700 border border-yellow-200',
    archive: 'bg-red-50 text-red-700 border border-red-200',
    delete:  'bg-red-50 text-red-700 border border-red-200',
    export:  'bg-purple-50 text-purple-700 border border-purple-200',
    import:  'bg-indigo-50 text-indigo-700 border border-indigo-200',
    manage:  'bg-orange-50 text-orange-700 border border-orange-200',
}

const accessibleModules = computed(() =>
    Object.entries(props.permissions)
        .filter(([, actions]) => actions.length > 0)
        .map(([module, actions]) => ({
            module,
            actions,
            ...(moduleIconMap[module] ?? { icon: Package, href: '#', description: '', color: 'bg-gray-100 text-gray-600' }),
        }))
)

// ─── Attendance history helpers ───────────────────────────────────────────────

const statusCfg: Record<string, { label: string; dot: string; text: string }> = {
    present:  { label: 'Present',  dot: 'bg-emerald-500', text: 'text-emerald-700' },
    absent:   { label: 'Absent',   dot: 'bg-red-500',     text: 'text-red-600'    },
    late:     { label: 'Late',     dot: 'bg-amber-500',   text: 'text-amber-700'  },
    half_day: { label: 'Half Day', dot: 'bg-blue-500',    text: 'text-blue-700'   },
}

function fmtDate(d: string): string {
    return new Date(d + 'T00:00:00').toLocaleDateString('en-PH', {
        weekday: 'short', month: 'short', day: 'numeric',
    })
}
</script>

<template>
    <Head title="Dashboard" />

    <ShopLayout title="Dashboard">
        <div class="p-4 sm:p-6 space-y-6">

            <!-- ── Welcome ── -->
            <div>
                <h2 class="text-lg font-semibold">
                    Welcome, {{ props.employee.first_name }}!
                </h2>
                <p class="text-sm text-muted-foreground capitalize">
                    {{ props.employee.position }}
                    <span v-if="props.employee.branch_name"> · {{ props.employee.branch_name }}</span>
                </p>
            </div>

            <!-- ── Time In / Time Out card ── -->
            <div class="rounded-2xl border bg-card shadow-sm p-5 space-y-4">

                <!-- Card header -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <Clock class="h-4 w-4 text-muted-foreground" />
                        <span class="text-sm font-semibold">Today's Attendance</span>
                        <span class="text-xs text-muted-foreground">— {{ todayName }}</span>
                    </div>
                    <span
                        v-if="attendanceStatusCfg"
                        class="text-xs px-2.5 py-0.5 rounded-full font-medium"
                        :class="attendanceStatusCfg.cls"
                    >
                        {{ attendanceStatusCfg.label }}
                    </span>
                    <span v-else class="text-xs text-muted-foreground">Not recorded yet</span>
                </div>

                <!-- Shift info -->
                <div v-if="todaySchedule" class="flex items-center gap-1.5 text-xs text-muted-foreground">
                    <Clock class="h-3.5 w-3.5 shrink-0" />
                    Scheduled shift: <span class="font-medium text-foreground ml-0.5">{{ shiftLabel }}</span>
                </div>

                <!-- Not a work day notice -->
                <div
                    v-if="!isWorkDay"
                    class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 dark:bg-amber-950/30 dark:border-amber-800 px-3 py-2.5"
                >
                    <XCircle class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" />
                    <div>
                        <p class="text-xs font-semibold text-amber-700 dark:text-amber-400">
                            {{ props.schedules.length === 0 ? 'No schedule configured' : 'Not a scheduled work day' }}
                        </p>
                        <p class="text-xs text-amber-600 dark:text-amber-500 mt-0.5">
                            <template v-if="props.schedules.length === 0">
                                Your work schedule hasn't been set up yet. Please contact your manager.
                            </template>
                            <template v-else>
                                {{ todayName }} is not in your work schedule. Clock-in is disabled.
                            </template>
                        </p>
                    </div>
                </div>

                <!-- Time display boxes -->
                <div class="grid grid-cols-2 gap-3 text-center">
                    <div class="rounded-xl bg-muted/40 p-3">
                        <p class="text-xs text-muted-foreground mb-1">Clock In</p>
                        <p class="text-lg font-bold font-mono">{{ fmt(attendance?.time_in ?? null) }}</p>
                    </div>
                    <div class="rounded-xl bg-muted/40 p-3">
                        <p class="text-xs text-muted-foreground mb-1">Clock Out</p>
                        <p class="text-lg font-bold font-mono">{{ fmt(attendance?.time_out ?? null) }}</p>
                    </div>
                </div>

                <!-- Auto-absent remark -->
                <div
                    v-if="attendance?.status === 'absent'"
                    class="flex items-start gap-2 rounded-lg bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 px-3 py-2"
                >
                    <MapPinOff class="h-4 w-4 text-red-500 shrink-0 mt-0.5" />
                    <p class="text-xs text-red-600 dark:text-red-400">{{ attendance.remarks }}</p>
                </div>

                <!-- Geo note -->
                <div
                    v-if="props.shop_has_geo && isWorkDay && !hasClockedIn"
                    class="flex items-center gap-2 text-xs text-muted-foreground"
                >
                    <MapPin class="h-3.5 w-3.5 shrink-0" />
                    Your location will be verified against the shop's address when you clock in.
                </div>

                <!-- Action buttons -->
                <div class="flex gap-2">

                    <!-- Clock In button -->
                    <button
                        v-if="!hasClockedIn"
                        class="flex-1 flex items-center justify-center gap-2 rounded-xl py-2.5 text-sm font-semibold
                               transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                        :class="isWorkDay
                            ? 'bg-emerald-600 hover:bg-emerald-700 text-white'
                            : 'bg-muted text-muted-foreground border'"
                        :disabled="!isWorkDay || clocking || locating"
                        :title="!isWorkDay ? `${todayName} is not in your work schedule` : undefined"
                        @click="isWorkDay && doClockAction('clock-in')"
                    >
                        <Loader2 v-if="clocking || locating" class="h-4 w-4 animate-spin" />
                        <LogIn v-else class="h-4 w-4" />
                        {{ locating ? 'Getting location…' : clocking ? 'Recording…' : 'Clock In' }}
                    </button>

                    <!-- Already clocked in -->
                    <template v-else>
                        <div class="flex items-center gap-1.5 flex-1 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 px-3 py-2">
                            <CheckCircle2 class="h-4 w-4 text-emerald-600 shrink-0" />
                            <span class="text-xs text-emerald-700 dark:text-emerald-400 font-medium">
                                Clocked in at {{ fmt(attendance?.time_in ?? null) }}
                            </span>
                        </div>

                        <!-- Clock Out button -->
                        <button
                            v-if="!hasClockedOut"
                            class="flex items-center justify-center gap-2 px-4 rounded-xl py-2.5 text-sm font-semibold
                                   bg-red-600 hover:bg-red-700 text-white transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                            :disabled="clocking || locating"
                            @click="doClockAction('clock-out')"
                        >
                            <Loader2 v-if="clocking || locating" class="h-4 w-4 animate-spin" />
                            <LogOut v-else class="h-4 w-4" />
                            {{ locating ? 'Getting location…' : clocking ? 'Recording…' : 'Clock Out' }}
                        </button>

                        <!-- Already clocked out -->
                        <div
                            v-else
                            class="flex items-center gap-1.5 px-3 rounded-xl bg-muted/40 border py-2"
                        >
                            <XCircle class="h-4 w-4 text-muted-foreground shrink-0" />
                            <span class="text-xs text-muted-foreground font-medium">
                                Out at {{ fmt(attendance?.time_out ?? null) }}
                            </span>
                        </div>
                    </template>

                </div>
            </div>

            <!-- ── Module access ── -->
            <div>
                <h3 class="text-sm font-semibold mb-3">Your Access</h3>

                <!-- No permissions -->
                <div
                    v-if="accessibleModules.length === 0"
                    class="flex flex-col items-center justify-center py-10 text-center px-4 rounded-2xl border bg-muted/20"
                >
                    <ShieldAlert class="h-10 w-10 text-muted-foreground mb-3" />
                    <h3 class="font-semibold">No module access assigned</h3>
                    <p class="text-sm text-muted-foreground mt-1 max-w-xs">
                        Your shop owner hasn't assigned any module permissions to your role yet.
                    </p>
                </div>

                <!-- Module cards -->
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                    <button
                        v-for="item in accessibleModules"
                        :key="item.module"
                        class="group relative flex flex-col gap-4 rounded-2xl border bg-card p-5 text-left
                               shadow-sm hover:shadow-md hover:border-primary/40 transition-all duration-200
                               active:scale-[0.98] w-full min-w-0"
                        @click="router.visit(item.href)"
                    >
                        <div class="flex items-start justify-between gap-3 min-w-0">
                            <div class="flex items-center gap-3 min-w-0">
                                <div :class="['rounded-xl p-2.5 shrink-0', item.color]">
                                    <component :is="item.icon" class="h-5 w-5" />
                                </div>
                                <span class="font-semibold text-sm leading-tight break-words min-w-0">
                                    {{ item.module }}
                                </span>
                            </div>
                            <ChevronRight class="h-4 w-4 text-muted-foreground shrink-0 mt-0.5
                                                group-hover:translate-x-0.5 transition-transform" />
                        </div>

                        <p class="text-xs text-muted-foreground leading-relaxed">
                            {{ item.description }}
                        </p>

                        <div class="flex flex-wrap gap-1.5">
                            <span
                                v-for="action in item.actions"
                                :key="action"
                                class="text-xs px-2 py-0.5 rounded-full capitalize font-medium"
                                :class="actionColorMap[action] ?? 'bg-muted text-muted-foreground'"
                            >
                                {{ action }}
                            </span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- ── Personal Attendance History ── -->
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <CalendarClock class="h-4 w-4 text-muted-foreground" />
                    <h3 class="text-sm font-semibold">My Attendance — Last 30 Days</h3>
                </div>

                <div class="rounded-xl border overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                                <th class="text-left px-4 py-3 font-medium">Date</th>
                                <th class="text-left px-4 py-3 font-medium">Status</th>
                                <th class="text-left px-4 py-3 font-medium">Clock In</th>
                                <th class="text-left px-4 py-3 font-medium">Clock Out</th>
                                <th class="text-left px-4 py-3 font-medium">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="rec in props.own_attendances"
                                :key="rec.date"
                                class="border-b last:border-0 hover:bg-muted/20 transition-colors"
                            >
                                <td class="px-4 py-3 text-xs font-medium">{{ fmtDate(rec.date) }}</td>
                                <td class="px-4 py-3">
                                    <span v-if="statusCfg[rec.status]" class="flex items-center gap-1.5">
                                        <span class="h-2 w-2 rounded-full shrink-0" :class="statusCfg[rec.status].dot" />
                                        <span class="text-xs font-medium" :class="statusCfg[rec.status].text">
                                            {{ statusCfg[rec.status].label }}
                                        </span>
                                    </span>
                                    <span v-else class="text-xs text-muted-foreground italic">—</span>
                                </td>
                                <td class="px-4 py-3 text-xs font-mono">{{ rec.time_in ? fmt(rec.time_in) : '—' }}</td>
                                <td class="px-4 py-3 text-xs font-mono">{{ rec.time_out ? fmt(rec.time_out) : '—' }}</td>
                                <td class="px-4 py-3 text-xs text-muted-foreground">{{ rec.remarks || '—' }}</td>
                            </tr>

                            <tr v-if="props.own_attendances.length === 0">
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-muted-foreground">
                                    <CalendarClock class="h-7 w-7 mx-auto mb-2 opacity-30" />
                                    No attendance records in the last 30 days.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </ShopLayout>
</template>
