<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// shadcn components
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

// icons
import { ArrowLeft } from 'lucide-vue-next';

// ─── Types ────────────────────────────────────────────────────────────────────

interface AuditUser {
    id: number;
    name: string;
}

interface Employee {
    id: number;
    employee_id: string;
    first_name: string;
    last_name: string;
    phone: string | null;
    address: string | null;
    position: string;
    employment_type: string | null;
    pay_rate: string | null;
    pay_basis: string;
    branch_name: string | null;
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
        href: `${baseRoute.value}/employee/${employee.id}`,
    },
];

// ─── Helpers ──────────────────────────────────────────────────────────────────

const formatPayRate = (val: string | null) =>
    val
        ? `₱${parseFloat(val).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
        : '—';

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
    probationary: 'Probationary',
    seasonal: 'Seasonal',
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
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
</script>

<template>
    <Head :title="`${employee.first_name} ${employee.last_name}`" />

    <ShopLayout
        :breadcrumbs="breadcrumbs"
        :title="`${employee.first_name} ${employee.last_name}`"
    >
        <div class="mx-auto max-w-7xl space-y-5 px-4 pb-8 sm:px-6">
            <div class="flex items-center">
                <Button
                    type="button"
                    variant="outline"
                    @click="router.visit(`${baseRoute}/employee`)"
                >
                    <ArrowLeft class="mr-2 h-4 w-4" /> Back to Employees
                </Button>
            </div>

            <Card class="overflow-hidden">
                <CardContent class="p-0">
                    <div
                        class="flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:p-6"
                    >
                        <div
                            class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-primary/10"
                        >
                            <span class="text-2xl font-bold text-primary">
                                {{ employee.first_name[0]
                                }}{{ employee.last_name[0] }}
                            </span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-3">
                                <h2
                                    class="text-xl font-semibold tracking-tight"
                                >
                                    {{ employee.first_name }}
                                    {{ employee.last_name }}
                                </h2>
                                <span
                                    class="rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                    :class="{
                                        'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300':
                                            employee.status === 'Active',
                                        'border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300':
                                            employee.status === 'Inactive',
                                    }"
                                >
                                    {{ employee.status }}
                                </span>
                            </div>
                            <p class="mt-0.5 text-sm text-muted-foreground">
                                {{ employee.position }}
                            </p>
                        </div>
                        <div
                            class="border-t pt-4 sm:border-t-0 sm:border-l sm:pt-0 sm:pl-6"
                        >
                            <p
                                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                Employee ID
                            </p>
                            <p class="mt-1 font-mono text-sm font-semibold">
                                {{ employee.employee_id }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="grid gap-5 xl:grid-cols-3">
                <div class="space-y-5 xl:col-span-2">
                    <Card>
                        <CardHeader class="border-b pb-4">
                            <CardTitle class="text-base"
                                >Personal information</CardTitle
                            >
                            <p class="text-sm text-muted-foreground">
                                Contact and identifying details for this
                                employee.
                            </p>
                        </CardHeader>
                        <CardContent class="p-0">
                            <dl class="grid sm:grid-cols-2">
                                <div class="border-b px-5 py-4 sm:border-r">
                                    <dt
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Full name
                                    </dt>
                                    <dd class="mt-1 text-sm font-medium">
                                        {{ employee.first_name }}
                                        {{ employee.last_name }}
                                    </dd>
                                </div>
                                <div class="border-b px-5 py-4">
                                    <dt
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Phone number
                                    </dt>
                                    <dd class="mt-1 text-sm font-medium">
                                        {{ employee.phone ?? '—' }}
                                    </dd>
                                </div>
                                <div class="px-5 py-4 sm:col-span-2">
                                    <dt
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Address
                                    </dt>
                                    <dd
                                        class="mt-1 text-sm leading-6 font-medium"
                                    >
                                        {{ employee.address ?? '—' }}
                                    </dd>
                                </div>
                            </dl>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="border-b pb-4">
                            <CardTitle class="text-base"
                                >Employment details</CardTitle
                            >
                            <p class="text-sm text-muted-foreground">
                                Role, assignment, and compensation information.
                            </p>
                        </CardHeader>
                        <CardContent class="p-0">
                            <dl class="grid sm:grid-cols-2">
                                <div class="border-b px-5 py-4 sm:border-r">
                                    <dt
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Position
                                    </dt>
                                    <dd class="mt-1 text-sm font-medium">
                                        {{ employee.position }}
                                    </dd>
                                </div>
                                <div class="border-b px-5 py-4">
                                    <dt
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Branch
                                    </dt>
                                    <dd class="mt-1 text-sm font-medium">
                                        {{ employee.branch_name ?? '—' }}
                                    </dd>
                                </div>
                                <div class="border-b px-5 py-4 sm:border-r">
                                    <dt
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Employment type
                                    </dt>
                                    <dd class="mt-1 text-sm font-medium">
                                        {{
                                            formatEmploymentType(
                                                employee.employment_type,
                                            )
                                        }}
                                    </dd>
                                </div>
                                <div class="border-b px-5 py-4">
                                    <dt
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Hire date
                                    </dt>
                                    <dd class="mt-1 text-sm font-medium">
                                        {{ formatDate(employee.hire_date) }}
                                    </dd>
                                </div>
                                <div class="px-5 py-4 sm:border-r">
                                    <dt
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Pay rate
                                    </dt>
                                    <dd class="mt-1 text-sm font-semibold">
                                        {{ formatPayRate(employee.pay_rate) }}
                                    </dd>
                                </div>
                                <div class="border-t px-5 py-4 sm:border-t-0">
                                    <dt
                                        class="text-xs font-medium text-muted-foreground"
                                    >
                                        Pay basis
                                    </dt>
                                    <dd class="mt-1 text-sm font-medium">
                                        {{
                                            payBasisLabels[
                                                employee.pay_basis
                                            ] ?? employee.pay_basis
                                        }}
                                    </dd>
                                </div>
                            </dl>
                        </CardContent>
                    </Card>
                </div>

                <Card class="h-fit">
                    <CardHeader class="border-b pb-4">
                        <CardTitle class="text-base">Weekly schedule</CardTitle>
                        <p class="text-sm text-muted-foreground">
                            Regular working hours for each day.
                        </p>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div
                            v-if="!schedules || schedules.length === 0"
                            class="px-5 py-10 text-center text-sm text-muted-foreground"
                        >
                            No schedule assigned yet.
                        </div>
                        <div v-else class="divide-y">
                            <div
                                v-for="day in weekdays"
                                :key="day"
                                class="flex items-center justify-between gap-4 px-5 py-3.5"
                            >
                                <span
                                    class="text-sm font-medium"
                                    :class="{
                                        'text-muted-foreground':
                                            !getScheduleForDay(day),
                                    }"
                                >
                                    {{ day }}
                                </span>
                                <span
                                    v-if="getScheduleForDay(day)"
                                    class="text-right text-sm tabular-nums"
                                >
                                    {{
                                        fmtTime(
                                            getScheduleForDay(day)!.start_time,
                                        )
                                    }}–{{
                                        fmtTime(
                                            getScheduleForDay(day)!.end_time,
                                        )
                                    }}
                                </span>
                                <span
                                    v-else
                                    class="text-sm text-muted-foreground"
                                    >Day off</span
                                >
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader class="border-b pb-4">
                    <CardTitle class="text-base">Record information</CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <dl class="grid sm:grid-cols-2">
                        <div
                            class="border-b px-5 py-4 sm:border-r sm:border-b-0"
                        >
                            <dt
                                class="text-xs font-medium text-muted-foreground"
                            >
                                Added by
                            </dt>
                            <dd class="mt-1 text-sm font-medium">
                                {{ employee.creator?.name ?? '—' }}
                            </dd>
                            <dd class="mt-1 text-xs text-muted-foreground">
                                {{ formatDateTime(employee.created_at) }}
                            </dd>
                        </div>
                        <div class="px-5 py-4">
                            <dt
                                class="text-xs font-medium text-muted-foreground"
                            >
                                Last modified by
                            </dt>
                            <dd class="mt-1 text-sm font-medium">
                                {{ employee.updater?.name ?? '—' }}
                            </dd>
                            <dd class="mt-1 text-xs text-muted-foreground">
                                {{ formatDateTime(employee.updated_at) }}
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>
        </div>
    </ShopLayout>
</template>
