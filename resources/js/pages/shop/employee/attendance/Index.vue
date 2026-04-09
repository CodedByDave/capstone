<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { type BreadcrumbItem, type AppPageProps } from '@/types'
import { ref, computed, watch, onMounted } from 'vue'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent } from '@/components/ui/card'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import {
    CalendarClock, Users, UserCheck, UserX, Clock, AlertCircle,
    ChevronLeft, ChevronRight, Save, Loader2, Search,
} from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface Employee {
    id: number
    employee_id: string
    first_name: string
    last_name: string
    position: string
    branch_name: string | null
}

interface Attendance {
    id: number
    employee_id: number
    date: string
    status: 'present' | 'absent' | 'late' | 'half_day'
    time_in: string | null
    time_out: string | null
    remarks: string | null
    marker?: { id: number; name: string } | null
}

interface Schedule {
    employee_id: number
    day: string
    start_time: string
    end_time: string
}

interface Stats {
    present: number
    absent: number
    late: number
    half_day: number
}

interface EntryForm {
    employee_id: number
    status: 'present' | 'absent' | 'late' | 'half_day' | ''
    time_in: string
    time_out: string
    remarks: string
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    employees: Employee[]
    attendances: Attendance[]
    stats: Stats
    date: string
    branches: string[]
    filters: Record<string, string>
    schedules: Schedule[]
}>()

// ─── RBAC ─────────────────────────────────────────────────────────────────────

const page = usePage<AppPageProps>()
const selectedBranch = ref(props.filters?.branch ?? 'all')
const isOwner = computed(() => page.props.auth.user.role === 'owner')
const baseRoute = computed(() => isOwner.value ? '/shop' : '/staff')

onMounted(() => {
    const flashToast = usePage<AppPageProps>().props.toast as { type: string; message: string } | undefined
    if (!flashToast) return
    switch (flashToast.type) {
        case 'success': toast.success(flashToast.message); break
        case 'error':   toast.error(flashToast.message);   break
        case 'warning': toast.warning(flashToast.message); break
        default:        toast(flashToast.message)
    }
})

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Employee Management', href: '/shop/employee' },
    { title: 'Attendance', href: '/shop/attendance' },
]

// ─── Date Navigation ──────────────────────────────────────────────────────────

const selectedDate = ref(props.date)
const search = ref(props.filters?.search ?? '')

function navigateDate(offset: number) {
    const d = new Date(selectedDate.value)
    d.setDate(d.getDate() + offset)
    selectedDate.value = d.toISOString().split('T')[0]
    loadDate()
}

function goToday() {
    selectedDate.value = new Date().toISOString().split('T')[0]
    loadDate()
}

function loadDate() {
    router.get(`${baseRoute.value}/attendance`, {
        date:   selectedDate.value,
        search: search.value,
        branch: selectedBranch.value === 'all' ? undefined : selectedBranch.value,
    }, {
        preserveState:  true,
        preserveScroll: true,
    })
}

watch(selectedDate, () => loadDate())

const isToday = computed(() => selectedDate.value === new Date().toISOString().split('T')[0])

function formatDateDisplay(dateStr: string): string {
    return new Date(dateStr + 'T00:00:00').toLocaleDateString('en-PH', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
    })
}

// ─── Schedule / Day-Off Logic ─────────────────────────────────────────────────

const DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']

const selectedDayName = computed(() =>
    DAYS[new Date(selectedDate.value + 'T00:00:00').getDay()]
)

function getSchedule(employeeId: number): Schedule | null {
    const empSchedules = props.schedules.filter(s => s.employee_id === employeeId)
    if (empSchedules.length === 0) return null
    return empSchedules.find(s => s.day === selectedDayName.value) ?? null
}

function isScheduledDay(employeeId: number): boolean {
    const empSchedules = props.schedules.filter(s => s.employee_id === employeeId)
    if (empSchedules.length === 0) return true // no schedule set = always works
    return empSchedules.some(s => s.day === selectedDayName.value)
}

// ─── Attendance Entries ───────────────────────────────────────────────────────

function buildEntries(): EntryForm[] {
    return props.employees.map(emp => {
        const existing = props.attendances.find(a => a.employee_id === emp.id)
        const schedule = getSchedule(emp.id)
        return {
            employee_id: emp.id,
            status:      existing?.status ?? '',
            time_in:     existing?.time_in?.slice(0, 5)  ?? schedule?.start_time?.slice(0, 5) ?? '',
            time_out:    existing?.time_out?.slice(0, 5) ?? schedule?.end_time?.slice(0, 5)   ?? '',
            remarks:     existing?.remarks ?? '',
        }
    })
}

const entries = ref<EntryForm[]>(buildEntries())

watch(selectedBranch, (val) => {
    router.get(`${baseRoute.value}/attendance`, {
        date:   selectedDate.value,
        search: search.value,
        branch: val === 'all' ? undefined : val,
    }, {
        preserveState:  true,
        preserveScroll: true,
    })
})

// Rebuild entries when props change (e.g. after date navigation)
watch(() => [props.employees, props.attendances, props.schedules], () => {
    entries.value = buildEntries()
}, { deep: true })

// ─── Search / Filter ──────────────────────────────────────────────────────────

const filteredEmployees = computed(() => {
    if (!search.value.trim()) return props.employees
    const q = search.value.toLowerCase()
    return props.employees.filter(e =>
        e.first_name.toLowerCase().includes(q) ||
        e.last_name.toLowerCase().includes(q) ||
        e.employee_id.toLowerCase().includes(q) ||
        e.position?.toLowerCase().includes(q)
    )
})

function getEntry(employeeId: number): EntryForm {
    return entries.value.find(e => e.employee_id === employeeId)!
}

// ─── Time Options ─────────────────────────────────────────────────────────────

const timeOptions = computed(() => {
    const options: string[] = []
    for (let h = 0; h < 24; h++) {
        for (const m of ['00', '30']) {
            options.push(`${h.toString().padStart(2, '0')}:${m}`)
        }
    }
    return options
})

function fmtTime(time: string): string {
    if (!time) return ''
    const [h, m] = time.split(':').map(Number)
    const period = h >= 12 ? 'PM' : 'AM'
    const hour   = h % 12 || 12
    return `${hour}:${m.toString().padStart(2, '0')} ${period}`
}

// ─── Submit ───────────────────────────────────────────────────────────────────

const isSaving = ref(false)

function saveAttendance() {
    isSaving.value = true
    router.post(`${baseRoute.value}/attendance`, {
        date:    selectedDate.value,
        entries: entries.value.filter(e => isScheduledDay(e.employee_id)),
    }, {
        preserveScroll: true,
        onSuccess: () => toast.success('Attendance saved successfully'),
        onError:   () => toast.error('Failed to save attendance'),
        onFinish:  () => { isSaving.value = false },
    })
}

// ─── Status Helpers ───────────────────────────────────────────────────────────

const statusOptions = [
    { value: 'present',  label: 'Present',  color: 'bg-green-500' },
    { value: 'absent',   label: 'Absent',   color: 'bg-red-500'   },
    { value: 'late',     label: 'Late',     color: 'bg-amber-500' },
    { value: 'half_day', label: 'Half Day', color: 'bg-blue-500'  },
]

function statusColor(status: string): string {
    return statusOptions.find(s => s.value === status)?.color ?? 'bg-gray-400'
}

function statusLabel(status: string): string {
    return statusOptions.find(s => s.value === status)?.label ?? status
}
</script>

<template>

    <Head title="Attendance" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Attendance">
        <div class="px-6 space-y-6">

            <!-- ── Header ──────────────────────────────────────────── -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold">Daily Attendance</h2>
                    <p class="text-sm text-muted-foreground">
                        Mark attendance for all active employees.
                    </p>
                </div>
                <Button :disabled="isSaving" @click="saveAttendance">
                    <Loader2 v-if="isSaving" class="h-4 w-4 mr-2 animate-spin" />
                    <Save v-else class="h-4 w-4 mr-2" />
                    {{ isSaving ? 'Saving...' : 'Save Attendance' }}
                </Button>
            </div>

            <!-- ── Stats Cards ─────────────────────────────────────── -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <Card>
                    <CardContent class="pt-4 pb-4 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-green-100 flex items-center justify-center">
                            <UserCheck class="h-5 w-5 text-green-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.present }}</p>
                            <p class="text-xs text-muted-foreground">Present</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-4 pb-4 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-red-100 flex items-center justify-center">
                            <UserX class="h-5 w-5 text-red-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.absent }}</p>
                            <p class="text-xs text-muted-foreground">Absent</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-4 pb-4 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-amber-100 flex items-center justify-center">
                            <Clock class="h-5 w-5 text-amber-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.late }}</p>
                            <p class="text-xs text-muted-foreground">Late</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-4 pb-4 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                            <AlertCircle class="h-5 w-5 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.half_day }}</p>
                            <p class="text-xs text-muted-foreground">Half Day</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- ── Toolbar ─────────────────────────────────────────── -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <!-- Left: Date Navigation -->
                <div class="flex items-center gap-2">
                    <Button size="sm" variant="outline" @click="navigateDate(-1)">
                        <ChevronLeft class="h-4 w-4" />
                    </Button>
                    <Input v-model="selectedDate" type="date" class="w-40 text-sm"
                        :max="new Date().toISOString().slice(0, 10)" />
                    <Button v-if="!isToday" size="sm" variant="outline" @click="goToday">
                        Today
                    </Button>
                    <Button size="sm" variant="outline" @click="navigateDate(1)" :disabled="isToday">
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                </div>

                <!-- Right: Branch + Search -->
                <div class="flex items-center gap-2">
                    <Select v-model="selectedBranch">
                        <SelectTrigger class="h-9 w-44 text-sm">
                            <SelectValue placeholder="All Branches" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Branches</SelectItem>
                            <SelectItem value="__none__">No Branch</SelectItem>
                            <SelectItem v-for="b in branches" :key="b" :value="b">{{ b }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <Input v-model="search" placeholder="Search employee..." class="pl-9 w-56" />
                    </div>
                </div>
            </div>

            <p class="text-sm font-medium text-muted-foreground">
                <CalendarClock class="inline h-4 w-4 mr-1 -mt-0.5" />
                {{ formatDateDisplay(selectedDate) }}
                <span class="ml-2 text-xs">({{ filteredEmployees.length }} employees)</span>
            </p>

            <!-- ── Attendance Table ────────────────────────────────── -->
            <div class="rounded-xl border overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                            <th class="text-left px-4 py-3 font-medium w-[30%]">Employee</th>
                            <th class="text-left px-4 py-3 font-medium">Status</th>
                            <th class="text-left px-4 py-3 font-medium">Time In</th>
                            <th class="text-left px-4 py-3 font-medium">Time Out</th>
                            <th class="text-left px-4 py-3 font-medium">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="emp in filteredEmployees" :key="emp.id"
                            class="border-b last:border-0 transition-colors"
                            :class="isScheduledDay(emp.id) ? 'hover:bg-muted/20' : 'bg-muted/10 opacity-60'">

                            <!-- Employee Info -->
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                                        <span class="text-xs font-bold text-primary">
                                            {{ emp.first_name[0] }}{{ emp.last_name[0] }}
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium truncate">{{ emp.first_name }} {{ emp.last_name }}</p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ emp.position }}
                                            <span v-if="emp.branch_name" class="ml-1 text-muted-foreground/60">
                                                · {{ emp.branch_name }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3">
                                <span v-if="!isScheduledDay(emp.id)"
                                    class="text-xs text-muted-foreground italic px-2 py-1 rounded bg-muted">
                                    Day Off
                                </span>
                                <Select v-else v-model="getEntry(emp.id).status">
                                    <SelectTrigger class="h-8 w-28 text-xs">
                                        <SelectValue>
                                            <span v-if="!getEntry(emp.id).status" class="text-muted-foreground">Select</span>
                                            <span v-else class="flex items-center gap-1.5">
                                                <span class="h-2 w-2 rounded-full" :class="statusColor(getEntry(emp.id).status)" />
                                                {{ statusLabel(getEntry(emp.id).status) }}
                                            </span>
                                        </SelectValue>
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                                            <span class="flex items-center gap-1.5">
                                                <span class="h-2 w-2 rounded-full" :class="opt.color" />
                                                {{ opt.label }}
                                            </span>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </td>

                            <!-- Time In -->
                            <td class="px-4 py-3">
                                <span v-if="!isScheduledDay(emp.id)" class="text-xs text-muted-foreground">—</span>
                                <Select v-else v-model="getEntry(emp.id).time_in"
                                    :disabled="getEntry(emp.id).status === 'absent'">
                                    <SelectTrigger class="h-8 w-32 text-xs">
                                        <SelectValue>
                                            {{ getEntry(emp.id).time_in ? fmtTime(getEntry(emp.id).time_in) : 'Select' }}
                                        </SelectValue>
                                    </SelectTrigger>
                                    <SelectContent class="max-h-60">
                                        <SelectItem value="__clear__">— None —</SelectItem>
                                        <SelectItem v-for="t in timeOptions" :key="'in-' + emp.id + t" :value="t">
                                            {{ fmtTime(t) }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </td>

                            <!-- Time Out -->
                            <td class="px-4 py-3">
                                <span v-if="!isScheduledDay(emp.id)" class="text-xs text-muted-foreground">—</span>
                                <Select v-else v-model="getEntry(emp.id).time_out"
                                    :disabled="getEntry(emp.id).status === 'absent'">
                                    <SelectTrigger class="h-8 w-32 text-xs">
                                        <SelectValue>
                                            {{ getEntry(emp.id).time_out ? fmtTime(getEntry(emp.id).time_out) : 'Select' }}
                                        </SelectValue>
                                    </SelectTrigger>
                                    <SelectContent class="max-h-60">
                                        <SelectItem value="__clear__">— None —</SelectItem>
                                        <SelectItem v-for="t in timeOptions" :key="'out-' + emp.id + t" :value="t">
                                            {{ fmtTime(t) }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </td>

                            <!-- Remarks -->
                            <td class="px-4 py-3">
                                <span v-if="!isScheduledDay(emp.id)" class="text-xs text-muted-foreground">—</span>
                                <Input v-else v-model="getEntry(emp.id).remarks" placeholder="Optional"
                                    class="h-8 text-xs w-full min-w-[120px]" />
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <tr v-if="filteredEmployees.length === 0">
                            <td colspan="5" class="px-4 py-12 text-center text-sm text-muted-foreground">
                                <Users class="h-8 w-8 mx-auto mb-2 opacity-30" />
                                <p>No active employees found.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </ShopLayout>
</template>
