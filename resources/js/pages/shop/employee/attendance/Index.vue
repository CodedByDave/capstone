<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    AlertCircle,
    CalendarClock,
    ChevronLeft,
    ChevronRight,
    Clock,
    Loader2,
    MapPin,
    Pencil,
    Search,
    UserCheck,
    Users,
    UserX,
    X,
} from 'lucide-vue-next';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Employee {
    id: number;
    employee_id: string;
    first_name: string;
    last_name: string;
    position: string;
    branch_name: string | null;
}

interface Attendance {
    id: number;
    employee_id: number;
    date: string;
    status: 'present' | 'absent' | 'late' | 'half_day';
    time_in: string | null;
    time_out: string | null;
    remarks: string | null;
    check_in_lat: number | null;
    check_in_lng: number | null;
    check_out_lat: number | null;
    check_out_lng: number | null;
    marker?: { id: number; name: string } | null;
}

interface Schedule {
    employee_id: number;
    day: string;
    start_time: string;
    end_time: string;
}

interface Stats {
    present: number;
    absent: number;
    late: number;
    half_day: number;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    employees: Employee[];
    attendances: Attendance[];
    stats: Stats;
    date: string;
    branches: string[];
    filters: Record<string, string>;
    schedules: Schedule[];
    can_edit: boolean;
}>();

// ─── Page setup ───────────────────────────────────────────────────────────────

const page = usePage<AppPageProps>();
const selectedBranch = ref(props.filters?.branch ?? 'all');
const isOwner = computed(() => page.props.auth.user.role === 'owner');
const hasMultipleLocations = computed(() =>
    Boolean((page.props as any).shopCapabilities?.multiple_locations),
);
const baseRoute = computed(() => (isOwner.value ? '/shop' : '/staff'));

onMounted(() => {
    const flashToast = usePage<AppPageProps>().props.toast as
        | { type: string; message: string }
        | undefined;
    if (!flashToast) return;
    switch (flashToast.type) {
        case 'success':
            toast.success(flashToast.message);
            break;
        case 'error':
            toast.error(flashToast.message);
            break;
        case 'warning':
            toast.warning(flashToast.message);
            break;
        default:
            toast(flashToast.message);
    }
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Employee Management', href: `${baseRoute.value}/employee` },
    { title: 'Attendance', href: `${baseRoute.value}/attendance` },
];

// ─── Date Navigation ──────────────────────────────────────────────────────────

const selectedDate = ref(props.date);
const search = ref(props.filters?.search ?? '');

function navigateDate(offset: number) {
    const d = new Date(selectedDate.value);
    d.setDate(d.getDate() + offset);
    selectedDate.value = d.toISOString().split('T')[0];
    loadDate();
}

function goToday() {
    selectedDate.value = new Date().toISOString().split('T')[0];
    loadDate();
}

function loadDate() {
    router.get(
        `${baseRoute.value}/attendance`,
        {
            date: selectedDate.value,
            search: search.value,
            branch:
                selectedBranch.value === 'all'
                    ? undefined
                    : selectedBranch.value,
        },
        { preserveState: true, preserveScroll: true },
    );
}

watch(selectedDate, () => loadDate());
watch(selectedBranch, (val) => {
    router.get(
        `${baseRoute.value}/attendance`,
        {
            date: selectedDate.value,
            search: search.value,
            branch: val === 'all' ? undefined : val,
        },
        { preserveState: true, preserveScroll: true },
    );
});

const isToday = computed(
    () => selectedDate.value === new Date().toISOString().split('T')[0],
);

function formatDateDisplay(dateStr: string): string {
    return new Date(dateStr + 'T00:00:00').toLocaleDateString('en-PH', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

// ─── Schedule helpers ─────────────────────────────────────────────────────────

const DAYS = [
    'Sunday',
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
];

const selectedDayName = computed(
    () => DAYS[new Date(selectedDate.value + 'T00:00:00').getDay()],
);

function getSchedule(employeeId: number): Schedule | null {
    const empSchedules = props.schedules.filter(
        (s) => s.employee_id === employeeId,
    );
    if (empSchedules.length === 0) return null;
    return empSchedules.find((s) => s.day === selectedDayName.value) ?? null;
}

function isScheduledDay(employeeId: number): boolean {
    const empSchedules = props.schedules.filter(
        (s) => s.employee_id === employeeId,
    );
    if (empSchedules.length === 0) return false; // no schedule configured = no work days
    return empSchedules.some((s) => s.day === selectedDayName.value);
}

// ─── Search / filter ──────────────────────────────────────────────────────────

const filteredEmployees = computed(() => {
    if (!search.value.trim()) return props.employees;
    const q = search.value.toLowerCase();
    return props.employees.filter(
        (e) =>
            e.first_name.toLowerCase().includes(q) ||
            e.last_name.toLowerCase().includes(q) ||
            e.employee_id.toLowerCase().includes(q) ||
            e.position?.toLowerCase().includes(q),
    );
});

// ─── Data lookup helpers ──────────────────────────────────────────────────────

function getAttendance(employeeId: number): Attendance | null {
    return props.attendances.find((a) => a.employee_id === employeeId) ?? null;
}

// ─── Status / time helpers ────────────────────────────────────────────────────

const statusOptions = [
    {
        value: 'present',
        label: 'Present',
        dot: 'bg-green-500',
        badge: 'bg-green-100 text-green-700',
    },
    {
        value: 'absent',
        label: 'Absent',
        dot: 'bg-red-500',
        badge: 'bg-red-100 text-red-700',
    },
    {
        value: 'late',
        label: 'Late',
        dot: 'bg-amber-500',
        badge: 'bg-amber-100 text-amber-700',
    },
    {
        value: 'half_day',
        label: 'Half Day',
        dot: 'bg-blue-500',
        badge: 'bg-blue-100 text-blue-700',
    },
];

function statusCfg(status: string) {
    return statusOptions.find((s) => s.value === status);
}

function fmtTime(time: string | null): string {
    if (!time) return '—';
    const [h, m] = time.split(':').map(Number);
    const period = h >= 12 ? 'PM' : 'AM';
    return `${h % 12 || 12}:${m.toString().padStart(2, '0')} ${period}`;
}

const timeOptions = computed(() => {
    const opts: string[] = [];
    for (let h = 0; h < 24; h++) {
        for (const m of ['00', '30']) {
            opts.push(`${h.toString().padStart(2, '0')}:${m}`);
        }
    }
    return opts;
});

// ─── Location helpers ─────────────────────────────────────────────────────────

function mapsUrl(lat: number, lng: number): string {
    return `https://www.google.com/maps?q=${lat},${lng}`;
}

// ─── Mark Attendance Modal (owner only) ───────────────────────────────────────

interface ModalForm {
    employee_id: number;
    status: string;
    time_in: string;
    time_out: string;
    remarks: string;
}

const modal = ref<{
    open: boolean;
    employee: Employee | null;
    form: ModalForm;
}>({
    open: false,
    employee: null,
    form: {
        employee_id: 0,
        status: '',
        time_in: '',
        time_out: '',
        remarks: '',
    },
});

const isSaving = ref(false);

function openModal(emp: Employee) {
    const existing = getAttendance(emp.id);
    const schedule = getSchedule(emp.id);

    modal.value = {
        open: true,
        employee: emp,
        form: {
            employee_id: emp.id,
            status: existing?.status ?? '',
            time_in:
                existing?.time_in?.slice(0, 5) ??
                schedule?.start_time?.slice(0, 5) ??
                '',
            time_out:
                existing?.time_out?.slice(0, 5) ??
                schedule?.end_time?.slice(0, 5) ??
                '',
            remarks: existing?.remarks ?? '',
        },
    };
}

function closeModal() {
    modal.value.open = false;
}

function saveModal() {
    if (!modal.value.form.status) {
        toast.error('Please select a status.');
        return;
    }
    isSaving.value = true;
    router.post(
        `${baseRoute.value}/attendance`,
        {
            date: selectedDate.value,
            entries: [modal.value.form],
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Attendance saved.');
                closeModal();
            },
            onError: () => toast.error('Failed to save attendance.'),
            onFinish: () => {
                isSaving.value = false;
            },
        },
    );
}
</script>

<template>
    <Head title="Attendance" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Attendance">
        <div class="space-y-6 px-6">
            <!-- ── Header ──────────────────────────────────────────── -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2 class="text-lg font-semibold">Daily Attendance</h2>
                    <p class="text-sm text-muted-foreground">
                        <template v-if="props.can_edit">
                            Click the edit icon on any row to mark or update
                            attendance.
                        </template>
                        <template v-else>
                            View clock-in and clock-out records for all
                            employees.
                        </template>
                    </p>
                </div>
                <span
                    v-if="!props.can_edit"
                    class="flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700"
                >
                    <AlertCircle class="h-3.5 w-3.5" />
                    View only — editing is restricted to the owner
                </span>
            </div>

            <!-- ── Stats Cards ─────────────────────────────────────── -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <Card>
                    <CardContent class="flex items-center gap-3 pt-4 pb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100"
                        >
                            <UserCheck class="h-5 w-5 text-green-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">
                                {{ stats.present }}
                            </p>
                            <p class="text-xs text-muted-foreground">Present</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 pt-4 pb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100"
                        >
                            <UserX class="h-5 w-5 text-red-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.absent }}</p>
                            <p class="text-xs text-muted-foreground">Absent</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 pt-4 pb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100"
                        >
                            <Clock class="h-5 w-5 text-amber-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.late }}</p>
                            <p class="text-xs text-muted-foreground">Late</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 pt-4 pb-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100"
                        >
                            <AlertCircle class="h-5 w-5 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">
                                {{ stats.half_day }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Half Day
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- ── Toolbar ─────────────────────────────────────────── -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center gap-2">
                    <Button
                        size="sm"
                        variant="outline"
                        @click="navigateDate(-1)"
                    >
                        <ChevronLeft class="h-4 w-4" />
                    </Button>
                    <Input
                        v-model="selectedDate"
                        type="date"
                        class="w-40 text-sm"
                        :max="new Date().toISOString().slice(0, 10)"
                    />
                    <Button
                        v-if="!isToday"
                        size="sm"
                        variant="outline"
                        @click="goToday"
                        >Today</Button
                    >
                    <Button
                        size="sm"
                        variant="outline"
                        @click="navigateDate(1)"
                        :disabled="isToday"
                    >
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                </div>

                <div class="flex items-center gap-2">
                    <Select
                        v-if="hasMultipleLocations"
                        v-model="selectedBranch"
                    >
                        <SelectTrigger class="h-9 w-44 text-sm">
                            <SelectValue placeholder="All Branches" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Branches</SelectItem>
                            <SelectItem value="__none__">No Branch</SelectItem>
                            <SelectItem
                                v-for="b in branches"
                                :key="b"
                                :value="b"
                                >{{ b }}</SelectItem
                            >
                        </SelectContent>
                    </Select>
                    <div class="relative">
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search employee..."
                            class="w-56 pl-9"
                        />
                    </div>
                </div>
            </div>

            <p class="text-sm font-medium text-muted-foreground">
                <CalendarClock class="-mt-0.5 mr-1 inline h-4 w-4" />
                {{ formatDateDisplay(selectedDate) }}
                <span class="ml-2 text-xs"
                    >({{ filteredEmployees.length }} employees)</span
                >
            </p>

            <!-- ── Attendance Table ────────────────────────────────── -->
            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b bg-muted/40 text-xs text-muted-foreground"
                        >
                            <th class="w-[28%] px-4 py-3 text-left font-medium">
                                Employee
                            </th>
                            <th class="px-4 py-3 text-left font-medium">
                                Status
                            </th>
                            <th class="px-4 py-3 text-left font-medium">
                                Clock In
                            </th>
                            <th class="px-4 py-3 text-left font-medium">
                                Clock Out
                            </th>
                            <th class="px-4 py-3 text-left font-medium">
                                Location
                            </th>
                            <th class="px-4 py-3 text-left font-medium">
                                Remarks
                            </th>
                            <th v-if="props.can_edit" class="w-12 px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="emp in filteredEmployees"
                            :key="emp.id"
                            class="border-b transition-colors last:border-0"
                            :class="
                                isScheduledDay(emp.id)
                                    ? 'hover:bg-muted/20'
                                    : 'bg-muted/10 opacity-60'
                            "
                        >
                            <!-- Employee -->
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary/10"
                                    >
                                        <span
                                            class="text-xs font-bold text-primary"
                                        >
                                            {{ emp.first_name[0]
                                            }}{{ emp.last_name[0] }}
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium">
                                            {{ emp.first_name }}
                                            {{ emp.last_name }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ emp.position }}
                                            <span
                                                v-if="
                                                    hasMultipleLocations &&
                                                    emp.branch_name
                                                "
                                                class="ml-1 text-muted-foreground/60"
                                            >
                                                · {{ emp.branch_name }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3">
                                <span
                                    v-if="!isScheduledDay(emp.id)"
                                    class="rounded bg-muted px-2 py-0.5 text-xs text-muted-foreground italic"
                                >
                                    Day Off
                                </span>
                                <template
                                    v-else-if="getAttendance(emp.id)?.status"
                                >
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="
                                            statusCfg(
                                                getAttendance(emp.id)!.status,
                                            )?.badge
                                        "
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full"
                                            :class="
                                                statusCfg(
                                                    getAttendance(emp.id)!
                                                        .status,
                                                )?.dot
                                            "
                                        />
                                        {{
                                            statusCfg(
                                                getAttendance(emp.id)!.status,
                                            )?.label
                                        }}
                                    </span>
                                </template>
                                <span
                                    v-else
                                    class="text-xs text-muted-foreground italic"
                                    >Not marked</span
                                >
                            </td>

                            <!-- Clock In -->
                            <td class="px-4 py-3 font-mono text-xs">
                                <span
                                    v-if="!isScheduledDay(emp.id)"
                                    class="text-muted-foreground"
                                    >—</span
                                >
                                <span v-else>{{
                                    fmtTime(
                                        getAttendance(emp.id)?.time_in ?? null,
                                    )
                                }}</span>
                            </td>

                            <!-- Clock Out -->
                            <td class="px-4 py-3 font-mono text-xs">
                                <span
                                    v-if="!isScheduledDay(emp.id)"
                                    class="text-muted-foreground"
                                    >—</span
                                >
                                <span v-else>{{
                                    fmtTime(
                                        getAttendance(emp.id)?.time_out ?? null,
                                    )
                                }}</span>
                            </td>

                            <!-- Location -->
                            <td class="px-4 py-3">
                                <template v-if="getAttendance(emp.id)">
                                    <div class="flex flex-col gap-1">
                                        <a
                                            v-if="
                                                getAttendance(emp.id)!
                                                    .check_in_lat
                                            "
                                            :href="
                                                mapsUrl(
                                                    getAttendance(emp.id)!
                                                        .check_in_lat!,
                                                    getAttendance(emp.id)!
                                                        .check_in_lng!,
                                                )
                                            "
                                            target="_blank"
                                            class="flex items-center gap-1 text-xs font-medium text-emerald-600 hover:underline"
                                        >
                                            <MapPin class="h-3 w-3 shrink-0" />
                                            Clock In
                                        </a>
                                        <a
                                            v-if="
                                                getAttendance(emp.id)!
                                                    .check_out_lat
                                            "
                                            :href="
                                                mapsUrl(
                                                    getAttendance(emp.id)!
                                                        .check_out_lat!,
                                                    getAttendance(emp.id)!
                                                        .check_out_lng!,
                                                )
                                            "
                                            target="_blank"
                                            class="flex items-center gap-1 text-xs font-medium text-blue-600 hover:underline"
                                        >
                                            <MapPin class="h-3 w-3 shrink-0" />
                                            Clock Out
                                        </a>
                                        <span
                                            v-if="
                                                !getAttendance(emp.id)!
                                                    .check_in_lat &&
                                                !getAttendance(emp.id)!
                                                    .check_out_lat
                                            "
                                            class="text-xs text-muted-foreground"
                                            >—</span
                                        >
                                    </div>
                                </template>
                                <span
                                    v-else
                                    class="text-xs text-muted-foreground"
                                    >—</span
                                >
                            </td>

                            <!-- Remarks -->
                            <td class="px-4 py-3 text-xs text-muted-foreground">
                                <span v-if="!isScheduledDay(emp.id)">—</span>
                                <span v-else>{{
                                    getAttendance(emp.id)?.remarks || '—'
                                }}</span>
                            </td>

                            <!-- Edit button (owner only) -->
                            <td
                                v-if="props.can_edit"
                                class="px-3 py-3 text-right"
                            >
                                <button
                                    v-if="isScheduledDay(emp.id)"
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-lg border transition-colors hover:bg-muted"
                                    title="Mark attendance"
                                    @click="openModal(emp)"
                                >
                                    <Pencil
                                        class="h-3.5 w-3.5 text-muted-foreground"
                                    />
                                </button>
                            </td>
                        </tr>

                        <tr v-if="filteredEmployees.length === 0">
                            <td
                                :colspan="props.can_edit ? 7 : 6"
                                class="px-4 py-12 text-center text-sm text-muted-foreground"
                            >
                                <Users
                                    class="mx-auto mb-2 h-8 w-8 opacity-30"
                                />
                                <p>No active employees found.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </ShopLayout>

    <!-- ── Mark Attendance Modal ──────────────────────────────────────────────── -->
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="modal.open"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm"
                @click.self="closeModal"
            >
                <div
                    class="w-full max-w-sm overflow-hidden rounded-2xl border bg-background shadow-xl"
                >
                    <!-- Modal header -->
                    <div
                        class="flex items-center justify-between border-b px-5 py-4"
                    >
                        <div>
                            <p class="text-sm font-semibold">Mark Attendance</p>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                {{ modal.employee?.first_name }}
                                {{ modal.employee?.last_name }} ·
                                {{ formatDateDisplay(selectedDate) }}
                            </p>
                        </div>
                        <button
                            @click="closeModal"
                            class="flex h-7 w-7 items-center justify-center rounded-lg transition-colors hover:bg-muted"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <!-- Modal body -->
                    <div class="space-y-4 px-5 py-4">
                        <!-- Status -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium"
                                >Status
                                <span class="text-red-500">*</span></label
                            >
                            <Select v-model="modal.form.status">
                                <SelectTrigger class="h-9 text-sm">
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="opt in statusOptions"
                                        :key="opt.value"
                                        :value="opt.value"
                                    >
                                        <span class="flex items-center gap-2">
                                            <span
                                                class="h-2 w-2 rounded-full"
                                                :class="opt.dot"
                                            />
                                            {{ opt.label }}
                                        </span>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Time In / Time Out -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1.5">
                                <label class="text-xs font-medium"
                                    >Clock In</label
                                >
                                <Select
                                    v-model="modal.form.time_in"
                                    :disabled="modal.form.status === 'absent'"
                                >
                                    <SelectTrigger class="h-9 text-sm">
                                        <SelectValue>
                                            {{
                                                modal.form.time_in
                                                    ? fmtTime(
                                                          modal.form.time_in,
                                                      )
                                                    : '—'
                                            }}
                                        </SelectValue>
                                    </SelectTrigger>
                                    <SelectContent class="max-h-60">
                                        <SelectItem value=""
                                            >— None —</SelectItem
                                        >
                                        <SelectItem
                                            v-for="t in timeOptions"
                                            :key="'mod-in-' + t"
                                            :value="t"
                                        >
                                            {{ fmtTime(t) }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-medium"
                                    >Clock Out</label
                                >
                                <Select
                                    v-model="modal.form.time_out"
                                    :disabled="modal.form.status === 'absent'"
                                >
                                    <SelectTrigger class="h-9 text-sm">
                                        <SelectValue>
                                            {{
                                                modal.form.time_out
                                                    ? fmtTime(
                                                          modal.form.time_out,
                                                      )
                                                    : '—'
                                            }}
                                        </SelectValue>
                                    </SelectTrigger>
                                    <SelectContent class="max-h-60">
                                        <SelectItem value=""
                                            >— None —</SelectItem
                                        >
                                        <SelectItem
                                            v-for="t in timeOptions"
                                            :key="'mod-out-' + t"
                                            :value="t"
                                        >
                                            {{ fmtTime(t) }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium">Remarks</label>
                            <Input
                                v-model="modal.form.remarks"
                                placeholder="Optional note..."
                                class="h-9 text-sm"
                            />
                        </div>
                    </div>

                    <!-- Modal footer -->
                    <div
                        class="flex justify-end gap-2 border-t bg-muted/20 px-5 py-4"
                    >
                        <Button variant="outline" size="sm" @click="closeModal"
                            >Cancel</Button
                        >
                        <Button
                            size="sm"
                            :disabled="isSaving"
                            @click="saveModal"
                        >
                            <Loader2
                                v-if="isSaving"
                                class="mr-1.5 h-3.5 w-3.5 animate-spin"
                            />
                            {{ isSaving ? 'Saving…' : 'Save' }}
                        </Button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
