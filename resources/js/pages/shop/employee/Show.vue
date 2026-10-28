<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { toast } from 'vue-sonner';

// shadcn components
import { Button } from '@/components/ui/button';

// icons
import { ArrowLeft, CalendarDays, Copy, Pencil } from 'lucide-vue-next';

// ─── Types ────────────────────────────────────────────────────────────────────

interface AuditUser {
    id: number;
    name: string;
}

interface Employee {
    id: number;
    public_id: string;
    employee_id: string;
    first_name: string;
    last_name: string;
    email: string | null;
    phone: string | null;
    address: string | null;
    position: string;
    employment_type: string | null;
    pay_rate: string | null;
    pay_basis: string;
    hire_date: string;
    status: 'Active' | 'Inactive';
    created_at: string;
    updated_at: string;
    creator: AuditUser | null;
    updater: AuditUser | null;
}

interface Schedule {
    id: number;
    day: string;
    start_time: string;
    end_time: string;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const { employee, schedules } = defineProps<{
    employee: Employee;
    schedules: Schedule[];
}>();

const page = usePage();
const isOwner = computed(() => page.props.auth.user.role === 'owner');
const baseRoute = computed(() => (isOwner.value ? '/shop' : '/staff'));

const weekdays = [
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
    'Sunday',
];

const getScheduleForDay = (day: string) =>
    schedules?.find((s) => s.day === day);

function fmtTime(time: string): string {
    const [h, m] = time.slice(0, 5).split(':').map(Number);
    const period = h >= 12 ? 'PM' : 'AM';
    const hour = h % 12 || 12;
    return `${hour}:${m.toString().padStart(2, '0')} ${period}`;
}

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Employee Management', href: `${baseRoute.value}/employee` },
    {
        title: `${employee.first_name} ${employee.last_name}`,
        href: `${baseRoute.value}/employee/${employee.public_id}`,
    },
];

// ─── Helpers ──────────────────────────────────────────────────────────────────

const formatPayRate = (val: string | null) =>
    val
        ? `₱${parseFloat(val).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
        : '—';

const payBasisUnits: Record<string, string> = {
    monthly: 'month',
    daily: 'day',
    hourly: 'hr',
    per_shift: 'shift',
    fixed_contract: 'contract',
};

const payUnit = computed(() => payBasisUnits[employee.pay_basis] ?? '');

const hiredAgo = computed(() => {
    const days = Math.floor(
        (Date.now() - new Date(employee.hire_date).getTime()) / 86_400_000,
    );

    if (days < 0) return 'Starts soon';
    if (days === 0) return 'Today';
    if (days < 60) return `${days} day${days === 1 ? '' : 's'} ago`;
    if (days < 730) return `${Math.floor(days / 30)} months ago`;

    return `${Math.floor(days / 365)} years ago`;
});

const formatShortDate = (val: string) =>
    new Date(val).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });

const initials = computed(
    () => `${employee.first_name[0] ?? ''}${employee.last_name[0] ?? ''}`,
);

const editHref = computed(
    () => `${baseRoute.value}/employee/${employee.public_id}/edit`,
);

const statusClass = computed(() =>
    employee.status === 'Active'
        ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300'
        : 'border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300',
);

// The address is stored as one line: "street, barangay, city, province".
const addressFields = computed(() => {
    const parts = (employee.address ?? '')
        .split(',')
        .map((part) => part.trim())
        .filter(Boolean);
    const structured = parts.length >= 4;

    return [
        {
            label: 'Province',
            value: structured ? parts[parts.length - 1] : '',
            span: '',
        },
        {
            label: 'City / Municipality',
            value: structured ? parts[parts.length - 2] : '',
            span: '',
        },
        {
            label: 'Barangay',
            value: structured ? parts[parts.length - 3] : '',
            span: '',
        },
        {
            label: 'Street / Block No.',
            value: structured
                ? parts.slice(0, -3).join(', ')
                : parts.join(', '),
            span: 'sm:col-span-3',
        },
    ];
});

const workDays = computed(() => schedules?.length ?? 0);

async function copyEmployeeId() {
    try {
        await navigator.clipboard.writeText(employee.employee_id);
        toast.success('Employee ID copied.');
    } catch {
        toast.error('Could not copy the employee ID.');
    }
}

const formatDate = (val: string) =>
    new Date(val).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });

const employmentTypeLabels: Record<string, string> = {
    full_time: 'Full-time',
    part_time: 'Part-time',
    contractual: 'Contractual',
};

const payBasisLabels: Record<string, string> = {
    monthly: 'Monthly',
    daily: 'Daily',
    hourly: 'Hourly',
    per_shift: 'Per Shift',
    fixed_contract: 'Fixed Contract',
};

const formatEmploymentType = (value: string | null) =>
    value ? (employmentTypeLabels[value] ?? value) : 'Not set';

const formatDateTime = (val: string) =>
    new Date(val).toLocaleString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
</script>

<template>
    <Head :title="`${employee.first_name} ${employee.last_name}`" />

    <ShopLayout
        :breadcrumbs="breadcrumbs"
        :title="`${employee.first_name} ${employee.last_name}`"
    >
        <div class="mx-auto max-w-6xl space-y-8 px-4 pb-10 sm:px-6">
            <!-- Header -->
            <header class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xl font-bold text-primary uppercase"
                        aria-hidden="true"
                    >
                        {{ initials }}
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <h2 class="text-2xl font-bold tracking-tight">
                                {{ employee.first_name }}
                                {{ employee.last_name }}
                            </h2>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                :class="statusClass"
                            >
                                <span
                                    class="h-1.5 w-1.5 rounded-full bg-current"
                                    aria-hidden="true"
                                />
                                {{ employee.status }}
                            </span>
                        </div>
                        <div
                            class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-muted-foreground"
                        >
                            <span class="capitalize">{{
                                employee.position
                            }}</span>
                            <span aria-hidden="true">·</span>
                            <span>{{
                                formatEmploymentType(employee.employment_type)
                            }}</span>
                            <span aria-hidden="true">·</span>
                            <span
                                >ID
                                <span
                                    class="font-mono font-semibold text-foreground"
                                    >{{ employee.employee_id }}</span
                                ></span
                            >
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                class="ml-1 h-7 gap-1.5 px-2 text-xs"
                                @click="copyEmployeeId"
                            >
                                <Copy class="h-3 w-3" /> Copy
                            </Button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="router.visit(`${baseRoute}/employee`)"
                    >
                        <ArrowLeft class="mr-2 h-4 w-4" /> Back to employees
                    </Button>
                    <Button as-child>
                        <Link :href="editHref">
                            <Pencil class="mr-2 h-4 w-4" /> Edit employee
                        </Link>
                    </Button>
                </div>
            </header>

            <!-- Key figures -->
            <dl class="grid grid-cols-2 divide-x border-y py-5 lg:grid-cols-4">
                <div class="px-4 first:pl-0">
                    <dt
                        class="text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                    >
                        Pay rate
                    </dt>
                    <dd class="mt-1.5 text-2xl font-bold tabular-nums">
                        {{ formatPayRate(employee.pay_rate) }}
                        <span
                            v-if="payUnit"
                            class="text-sm font-normal text-muted-foreground"
                            >/ {{ payUnit }}</span
                        >
                    </dd>
                </div>
                <div class="px-4">
                    <dt
                        class="text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                    >
                        Hired
                    </dt>
                    <dd class="mt-1.5 text-2xl font-bold">
                        {{ formatShortDate(employee.hire_date) }}
                    </dd>
                    <dd class="mt-0.5 text-xs text-muted-foreground">
                        {{ hiredAgo }}
                    </dd>
                </div>
                <div class="px-4 max-lg:border-t max-lg:pt-5 max-lg:pl-0">
                    <dt
                        class="text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                    >
                        Employment
                    </dt>
                    <dd class="mt-1.5 text-2xl font-bold">
                        {{ formatEmploymentType(employee.employment_type) }}
                    </dd>
                </div>
                <div class="px-4 max-lg:border-t max-lg:pt-5">
                    <dt
                        class="text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                    >
                        Schedule
                    </dt>
                    <dd v-if="workDays" class="mt-1.5 text-2xl font-bold">
                        {{ workDays }} day{{ workDays === 1 ? '' : 's' }} / week
                    </dd>
                    <dd v-else class="mt-1.5 text-2xl font-bold text-amber-500">
                        Not set
                    </dd>
                </div>
            </dl>

            <div
                class="grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_320px]"
            >
                <!-- Left column -->
                <div class="space-y-8">
                    <section>
                        <h3 class="text-base font-semibold">
                            Personal information
                        </h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            Contact and identifying details for this employee.
                        </p>
                        <dl class="mt-5 grid gap-x-6 gap-y-5 sm:grid-cols-3">
                            <div>
                                <dt class="field-label">Employee ID</dt>
                                <dd class="mt-1 font-mono text-sm font-medium">
                                    {{ employee.employee_id }}
                                </dd>
                            </div>
                            <div>
                                <dt class="field-label">First name</dt>
                                <dd class="mt-1 text-sm font-medium">
                                    {{ employee.first_name }}
                                </dd>
                            </div>
                            <div>
                                <dt class="field-label">Last name</dt>
                                <dd class="mt-1 text-sm font-medium">
                                    {{ employee.last_name }}
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="field-label">Email address</dt>
                                <dd class="mt-1 text-sm font-medium">
                                    <template v-if="employee.email">{{
                                        employee.email
                                    }}</template>
                                    <span
                                        v-else
                                        class="font-normal text-muted-foreground"
                                        >Not provided</span
                                    >
                                </dd>
                            </div>
                            <div>
                                <dt class="field-label">Phone number</dt>
                                <dd class="mt-1 text-sm font-medium">
                                    <a
                                        v-if="employee.phone"
                                        :href="`tel:${employee.phone}`"
                                        class="underline decoration-dashed underline-offset-4"
                                        >{{ employee.phone }}</a
                                    >
                                    <span
                                        v-else
                                        class="font-normal text-muted-foreground"
                                        >Not provided</span
                                    >
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">Address</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            Where the employee lives.
                        </p>
                        <dl class="mt-5 grid gap-x-6 gap-y-5 sm:grid-cols-3">
                            <div
                                v-for="item in addressFields"
                                :key="item.label"
                                :class="item.span"
                            >
                                <dt class="field-label">{{ item.label }}</dt>
                                <dd class="mt-1 text-sm font-medium">
                                    <template v-if="item.value">{{
                                        item.value
                                    }}</template>
                                    <span
                                        v-else
                                        class="font-normal text-muted-foreground"
                                        >Not provided</span
                                    >
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">
                            Employment details
                        </h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            Role, assignment, and compensation.
                        </p>
                        <dl class="mt-5 grid gap-x-6 gap-y-5 sm:grid-cols-3">
                            <div>
                                <dt class="field-label">Position</dt>
                                <dd class="mt-1 text-sm font-medium capitalize">
                                    {{ employee.position }}
                                </dd>
                            </div>
                            <div>
                                <dt class="field-label">Employment type</dt>
                                <dd class="mt-1 text-sm font-medium">
                                    {{
                                        formatEmploymentType(
                                            employee.employment_type,
                                        )
                                    }}
                                </dd>
                            </div>
                            <div>
                                <dt class="field-label">Hire date</dt>
                                <dd class="mt-1 text-sm font-medium">
                                    {{ formatDate(employee.hire_date) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="field-label">Status</dt>
                                <dd class="mt-1">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                        :class="statusClass"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full bg-current"
                                            aria-hidden="true"
                                        />
                                        {{ employee.status }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="field-label">Pay rate</dt>
                                <dd class="mt-1 text-sm font-semibold">
                                    {{ formatPayRate(employee.pay_rate) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="field-label">Pay basis</dt>
                                <dd class="mt-1 text-sm font-medium">
                                    {{
                                        payBasisLabels[employee.pay_basis] ??
                                        employee.pay_basis
                                    }}
                                </dd>
                            </div>
                        </dl>
                    </section>
                </div>

                <!-- Right column -->
                <aside
                    class="rounded-xl border bg-card p-5 shadow-sm lg:sticky lg:top-4"
                >
                    <h3 class="text-base font-semibold">Weekly schedule</h3>
                    <p class="mt-0.5 text-sm text-muted-foreground">
                        Regular working hours for each day.
                    </p>

                    <div
                        v-if="!schedules || schedules.length === 0"
                        class="flex flex-col items-center px-2 py-8 text-center"
                    >
                        <CalendarDays
                            class="h-8 w-8 text-muted-foreground"
                            aria-hidden="true"
                        />
                        <p class="mt-3 text-sm font-semibold">
                            No schedule assigned yet
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Set the days and hours
                            {{ employee.first_name }} works so attendance and
                            payroll stay accurate.
                        </p>
                    </div>
                    <div v-else class="mt-4 divide-y border-y">
                        <div
                            v-for="day in weekdays"
                            :key="day"
                            class="flex items-center justify-between gap-4 py-2.5"
                        >
                            <span
                                class="text-sm font-medium"
                                :class="{
                                    'text-muted-foreground':
                                        !getScheduleForDay(day),
                                }"
                                >{{ day }}</span
                            >
                            <span
                                v-if="getScheduleForDay(day)"
                                class="text-right text-sm tabular-nums"
                            >
                                {{
                                    fmtTime(getScheduleForDay(day)!.start_time)
                                }}–{{
                                    fmtTime(getScheduleForDay(day)!.end_time)
                                }}
                            </span>
                            <span v-else class="text-sm text-muted-foreground"
                                >Day off</span
                            >
                        </div>
                    </div>

                    <div class="mt-5 border-t pt-5">
                        <p class="field-label">Record information</p>
                        <dl class="mt-3 space-y-3">
                            <div>
                                <dt class="text-xs text-muted-foreground">
                                    Added by
                                </dt>
                                <dd class="text-sm font-medium">
                                    {{ employee.creator?.name ?? '—' }}
                                </dd>
                                <dd class="text-xs text-muted-foreground">
                                    {{ formatDateTime(employee.created_at) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-muted-foreground">
                                    Last modified by
                                </dt>
                                <dd class="text-sm font-medium">
                                    {{ employee.updater?.name ?? '—' }}
                                </dd>
                                <dd class="text-xs text-muted-foreground">
                                    {{ formatDateTime(employee.updated_at) }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </aside>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.field-label {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--muted-foreground);
}
</style>
