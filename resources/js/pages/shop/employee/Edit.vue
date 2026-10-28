<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import PhilippinePhoneInput from '@/components/PhilippinePhoneInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import axios from 'axios';
import { ArrowLeft, Loader2, X } from 'lucide-vue-next';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Employee {
    id: number;
    public_id: string;
    employee_id: string;
    user_id: number | null;
    first_name: string;
    last_name: string;
    phone: string | null;
    address: string | null;
    position: string;
    employment_type: string | null;
    pay_rate: string | null;
    pay_basis: string;
    hire_date: string;
    status: 'Active' | 'Inactive';
}

interface Schedule {
    id: number;
    day: string;
    start_time: string;
    end_time: string;
}

interface PsgcItem {
    code: string;
    name: string;
}

interface EmploymentTypeOption {
    value: string;
    label: string;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const { employee, schedules: initialSchedules } = defineProps<{
    employee: Employee;
    schedules: Schedule[];
    roles: string[];
    employment_types: EmploymentTypeOption[];
    pay_bases: EmploymentTypeOption[];
}>();

// ─── RBAC ─────────────────────────────────────────────────────────────────────

const page = usePage<AppPageProps>();
const user = computed(() => page.props.auth.user);
const isOwner = computed(() => user.value.role === 'owner');
const isStaff = computed(() => !isOwner.value);
const baseRoute = computed(() => (isOwner.value ? '/shop' : '/staff'));

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Employee Management', href: `${baseRoute.value}/employee` },
    {
        title: `${employee.first_name} ${employee.last_name}`,
        href: `${baseRoute.value}/employee/${employee.public_id}`,
    },
    {
        title: 'Edit',
        href: `${baseRoute.value}/employee/${employee.public_id}/edit`,
    },
]);

const errors = computed(() => page.props.errors as Record<string, string>);

// ─── PSGC Address ─────────────────────────────────────────────────────────────

const BASE = 'https://psgc.cloud/api';

const provinces = ref<PsgcItem[]>([]);
const cities = ref<PsgcItem[]>([]);
const barangays = ref<PsgcItem[]>([]);

const loadingProvinces = ref(false);
const loadingCities = ref(false);
const loadingBarangays = ref(false);

const selectedProvince = ref('');
const selectedCity = ref('');
const selectedBarangay = ref('');
const streetInput = ref('');

function parseExistingAddress(address: string | null) {
    if (!address) return;
    streetInput.value = address.split(',')[0]?.trim() ?? '';
}

onMounted(async () => {
    parseExistingAddress(employee.address);
    loadingProvinces.value = true;
    try {
        const res = await fetch(`${BASE}/provinces`);
        const data = await res.json();
        provinces.value = data
            .map((p: any) => ({ code: p.code, name: p.name }))
            .sort((a: PsgcItem, b: PsgcItem) => a.name.localeCompare(b.name));
    } finally {
        loadingProvinces.value = false;
    }
});

watch(selectedProvince, async (code) => {
    selectedCity.value = '';
    selectedBarangay.value = '';
    cities.value = [];
    barangays.value = [];
    if (!code) return;
    loadingCities.value = true;
    try {
        const [citRes, munRes] = await Promise.all([
            fetch(`${BASE}/provinces/${code}/cities`),
            fetch(`${BASE}/provinces/${code}/municipalities`),
        ]);
        cities.value = [
            ...((await citRes.json()) || []),
            ...((await munRes.json()) || []),
        ]
            .map((c: any) => ({ code: c.code, name: c.name }))
            .sort((a: PsgcItem, b: PsgcItem) => a.name.localeCompare(b.name));
    } finally {
        loadingCities.value = false;
    }
});

watch(selectedCity, async (code) => {
    selectedBarangay.value = '';
    barangays.value = [];
    if (!code) return;
    loadingBarangays.value = true;
    try {
        const res = await fetch(
            `${BASE}/cities-municipalities/${code}/barangays`,
        );
        barangays.value = ((await res.json()) || [])
            .map((b: any) => ({ code: b.code, name: b.name }))
            .sort((a: PsgcItem, b: PsgcItem) => a.name.localeCompare(b.name));
    } finally {
        loadingBarangays.value = false;
    }
});

const fullAddress = computed(() => {
    const hasNewSelection =
        selectedBarangay.value || selectedCity.value || selectedProvince.value;
    if (!hasNewSelection) {
        if (!employee.address) return streetInput.value.trim();
        const parts = employee.address.split(',').map((s) => s.trim());
        parts[0] = streetInput.value.trim();
        return parts.filter(Boolean).join(', ');
    }
    return [
        streetInput.value.trim(),
        barangays.value.find((b) => b.code === selectedBarangay.value)?.name ??
            '',
        cities.value.find((c) => c.code === selectedCity.value)?.name ?? '',
        provinces.value.find((p) => p.code === selectedProvince.value)?.name ??
            '',
    ]
        .filter(Boolean)
        .join(', ');
});

// ─── Employee Form ────────────────────────────────────────────────────────────

const hasAccount = employee.user_id !== null && employee.user_id !== undefined;

// ← Separate ref so shadcn Checkbox reactivity is isolated and reliable
const createAccount = ref(false);

const form = ref({
    employee_id: employee.employee_id,
    first_name: employee.first_name,
    last_name: employee.last_name,
    phone: employee.phone ?? '',
    address: employee.address ?? '',
    position: employee.position,
    employment_type: employee.employment_type ?? '',
    pay_rate: employee.pay_rate ?? '',
    pay_basis: employee.pay_basis,
    hire_date: employee.hire_date,
    status: employee.status,
});

const isSubmitting = ref(false);

// ─── Change tracking (drives the Save button and status text) ────────────────

const snapshot = () =>
    JSON.stringify({
        form: { ...form.value, address: '' },
        createAccount: createAccount.value,
        street: streetInput.value,
        province: selectedProvince.value,
        city: selectedCity.value,
        barangay: selectedBarangay.value,
    });

const baseline = ref<string | null>(null);
const justSaved = ref(false);

onMounted(() => {
    baseline.value = snapshot();
});

const isDirty = computed(
    () => baseline.value !== null && snapshot() !== baseline.value,
);

const saveStatus = computed(() => {
    if (isDirty.value) return 'You have unsaved changes';
    return justSaved.value ? 'Changes saved' : 'No changes yet';
});

const initials = computed(
    () =>
        `${form.value.first_name.charAt(0)}${form.value.last_name.charAt(0)}`.toUpperCase() ||
        '?',
);

function submit() {
    form.value.address = fullAddress.value;
    isSubmitting.value = true;
    router.put(
        `${baseRoute.value}/employee/${employee.public_id}`,
        {
            ...form.value,
            create_account: createAccount.value ? 1 : 0,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                baseline.value = snapshot();
                justSaved.value = true;
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
}

// ─── Schedule ─────────────────────────────────────────────────────────────────

const weekdays = [
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
    'Sunday',
] as const;

interface DaySchedule {
    id?: number;
    day: string;
    start_time: string;
    end_time: string;
    enabled: boolean;
}

const scheduleMap = ref<DaySchedule[]>(
    weekdays.map((day) => {
        const existing = initialSchedules?.find((s) => s.day === day);
        return {
            id: existing?.id,
            day,
            start_time: existing?.start_time?.slice(0, 5) ?? '08:00',
            end_time: existing?.end_time?.slice(0, 5) ?? '17:00',
            enabled: !!existing,
        };
    }),
);

const savingSchedule = ref(false);
const scheduleError = ref<string | null>(null);

async function saveSchedule() {
    const active = scheduleMap.value.filter((d) => d.enabled);
    const invalid = active.find((d) => d.start_time >= d.end_time);
    if (invalid) {
        scheduleError.value = `${invalid.day}: end time must be after start time.`;
        return;
    }

    savingSchedule.value = true;
    scheduleError.value = null;
    try {
        await axios.put(`/shop/employee/${employee.public_id}/schedule`, {
            schedules: active.map((d) => ({
                day: d.day,
                start_time: d.start_time,
                end_time: d.end_time,
            })),
        });
        toast.success(
            `Schedule successfully added to ${employee.first_name} ${employee.last_name}.`,
        );
    } catch (err: any) {
        scheduleError.value =
            err.response?.data?.message ?? 'Failed to save schedule.';
        toast.error(scheduleError.value!);
    } finally {
        savingSchedule.value = false;
    }
}
</script>

<template>
    <Head :title="`Edit — ${employee.first_name} ${employee.last_name}`" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Edit Employee">
        <div class="mx-auto max-w-6xl space-y-6 px-6 pb-10">
            <!-- Header -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">
                        Edit employee
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Update the details for
                        <span class="font-medium text-foreground"
                            >{{ employee.first_name }}
                            {{ employee.last_name }}</span
                        >.
                    </p>
                </div>
                <Button
                    type="button"
                    variant="outline"
                    class="shrink-0"
                    @click="router.visit(`${baseRoute}/employee`)"
                >
                    <ArrowLeft class="mr-2 h-4 w-4" /> Back to employees
                </Button>
            </div>

            <form
                class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_300px]"
                @submit.prevent="submit"
            >
                <!-- Left column -->
                <div class="space-y-8">
                    <!-- Identity -->
                    <section>
                        <h3 class="text-base font-semibold">Identity</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            Who this employee is and how to reach them.
                        </p>
                        <div
                            class="mt-5 grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2"
                        >
                            <div class="space-y-1.5">
                                <label
                                    class="text-sm font-medium"
                                    for="employee-id"
                                    >Employee ID</label
                                >
                                <Input
                                    id="employee-id"
                                    v-model="form.employee_id"
                                    class="font-mono"
                                    disabled
                                />
                            </div>
                            <div class="space-y-1.5">
                                <label
                                    class="text-sm font-medium"
                                    for="first-name"
                                    >First Name
                                    <span class="text-red-500">*</span></label
                                >
                                <Input
                                    id="first-name"
                                    v-model="form.first_name"
                                    :class="{
                                        'border-red-500': errors.first_name,
                                    }"
                                />
                                <p
                                    v-if="errors.first_name"
                                    class="text-xs text-red-500"
                                >
                                    {{ errors.first_name }}
                                </p>
                            </div>
                            <div class="space-y-1.5">
                                <label
                                    class="text-sm font-medium"
                                    for="last-name"
                                    >Last Name
                                    <span class="text-red-500">*</span></label
                                >
                                <Input
                                    id="last-name"
                                    v-model="form.last_name"
                                    :class="{
                                        'border-red-500': errors.last_name,
                                    }"
                                />
                                <p
                                    v-if="errors.last_name"
                                    class="text-xs text-red-500"
                                >
                                    {{ errors.last_name }}
                                </p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium">Phone</label>
                                <PhilippinePhoneInput
                                    v-model="form.phone"
                                    :class="{ 'border-red-500': errors.phone }"
                                />
                                <p
                                    v-if="errors.phone"
                                    class="text-xs text-red-500"
                                >
                                    {{ errors.phone }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Employment -->
                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">Employment</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            Role, pay and employment status.
                        </p>
                        <div
                            class="mt-5 grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2"
                        >
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium"
                                    >Position
                                    <span class="text-red-500">*</span></label
                                >
                                <Select v-model="form.position">
                                    <SelectTrigger
                                        class="w-full"
                                        :class="{
                                            'border-red-500': errors.position,
                                        }"
                                    >
                                        <SelectValue
                                            placeholder="Select position"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="role in roles"
                                            :key="role"
                                            :value="role"
                                            class="capitalize"
                                        >
                                            {{ role }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p
                                    v-if="errors.position"
                                    class="text-xs text-red-500"
                                >
                                    {{ errors.position }}
                                </p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium"
                                    >Employment Type
                                    <span class="text-red-500">*</span></label
                                >
                                <Select v-model="form.employment_type">
                                    <SelectTrigger
                                        class="w-full"
                                        :class="{
                                            'border-red-500':
                                                errors.employment_type,
                                        }"
                                    >
                                        <SelectValue
                                            placeholder="Select employment type"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="type in employment_types"
                                            :key="type.value"
                                            :value="type.value"
                                        >
                                            {{ type.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p
                                    v-if="errors.employment_type"
                                    class="text-xs text-red-500"
                                >
                                    {{ errors.employment_type }}
                                </p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium">
                                    Hire Date
                                    <span class="text-red-500">*</span>
                                    <span
                                        v-if="isStaff"
                                        class="ml-1 text-xs text-amber-500"
                                        >(view only)</span
                                    >
                                </label>
                                <div
                                    v-if="isStaff"
                                    class="flex h-9 w-full cursor-not-allowed rounded-md border border-input bg-muted/50 px-3 py-2 text-sm text-muted-foreground select-none"
                                >
                                    {{ form.hire_date }}
                                </div>
                                <Input
                                    v-else
                                    v-model="form.hire_date"
                                    type="date"
                                    :class="{
                                        'border-red-500': errors.hire_date,
                                    }"
                                />
                                <p
                                    v-if="errors.hire_date"
                                    class="text-xs text-red-500"
                                >
                                    {{ errors.hire_date }}
                                </p>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-sm font-medium">
                                    Pay Rate (₱)
                                    <span
                                        v-if="isStaff"
                                        class="ml-1 text-xs text-amber-500"
                                        >(view only)</span
                                    >
                                </label>
                                <div
                                    v-if="isStaff"
                                    class="flex h-9 w-full cursor-not-allowed rounded-md border border-input bg-muted/50 px-3 py-2 text-sm text-muted-foreground select-none"
                                >
                                    {{
                                        form.pay_rate
                                            ? `₱${Number(
                                                  form.pay_rate,
                                              ).toLocaleString('en-PH', {
                                                  minimumFractionDigits: 2,
                                              })}`
                                            : '—'
                                    }}
                                </div>
                                <Input
                                    v-else
                                    v-model="form.pay_rate"
                                    type="number"
                                    min="0.01"
                                    step="0.01"
                                    :class="{
                                        'border-red-500': errors.pay_rate,
                                    }"
                                />
                                <p
                                    v-if="errors.pay_rate"
                                    class="text-xs text-red-500"
                                >
                                    {{ errors.pay_rate }}
                                </p>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-sm font-medium">
                                    Pay Basis
                                    <span class="text-red-500">*</span>
                                    <span
                                        v-if="isStaff"
                                        class="ml-1 text-xs text-amber-500"
                                        >(view only)</span
                                    >
                                </label>
                                <div
                                    v-if="isStaff"
                                    class="flex h-9 w-full cursor-not-allowed rounded-md border border-input bg-muted/50 px-3 py-2 text-sm text-muted-foreground select-none"
                                >
                                    {{
                                        pay_bases.find(
                                            (basis) =>
                                                basis.value === form.pay_basis,
                                        )?.label ?? form.pay_basis
                                    }}
                                </div>
                                <Select v-else v-model="form.pay_basis">
                                    <SelectTrigger
                                        :class="{
                                            'border-red-500': errors.pay_basis,
                                        }"
                                    >
                                        <SelectValue
                                            placeholder="Select pay basis"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="basis in pay_bases"
                                            :key="basis.value"
                                            :value="basis.value"
                                        >
                                            {{ basis.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p
                                    v-if="errors.pay_basis"
                                    class="text-xs text-red-500"
                                >
                                    {{ errors.pay_basis }}
                                </p>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-sm font-medium">
                                    Status <span class="text-red-500">*</span>
                                    <span
                                        v-if="isStaff"
                                        class="ml-1 text-xs text-amber-500"
                                        >(view only)</span
                                    >
                                </label>
                                <div
                                    v-if="isStaff"
                                    class="flex h-9 w-full cursor-not-allowed items-center rounded-md border border-input bg-muted/50 px-3 py-2 text-sm select-none"
                                >
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs font-semibold text-white"
                                        :class="
                                            form.status === 'Active'
                                                ? 'bg-green-500'
                                                : 'bg-red-500'
                                        "
                                    >
                                        {{ form.status }}
                                    </span>
                                </div>
                                <Select v-else v-model="form.status">
                                    <SelectTrigger
                                        :class="{
                                            'border-red-500': errors.status,
                                        }"
                                    >
                                        <SelectValue
                                            placeholder="Select status"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Active"
                                            >Active</SelectItem
                                        >
                                        <SelectItem value="Inactive"
                                            >Inactive</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                                <p
                                    v-if="errors.status"
                                    class="text-xs text-red-500"
                                >
                                    {{ errors.status }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Address -->
                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">Address</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            Where the employee lives.
                        </p>
                        <div
                            v-if="employee.address"
                            class="mt-5 rounded-lg bg-muted/40 px-4 py-3 text-sm"
                        >
                            <p class="mb-1 text-xs text-muted-foreground">
                                Current address
                            </p>
                            <p class="font-medium">{{ employee.address }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                Select a new province below to update, or just
                                edit the street field.
                            </p>
                        </div>
                        <div
                            class="mt-5 grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2"
                        >
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium"
                                    >Province</label
                                >
                                <Select
                                    v-model="selectedProvince"
                                    :disabled="loadingProvinces"
                                >
                                    <SelectTrigger class="w-full">
                                        <SelectValue>
                                            <span
                                                v-if="loadingProvinces"
                                                class="flex items-center gap-1.5 text-muted-foreground"
                                            >
                                                <Loader2
                                                    class="h-3 w-3 animate-spin"
                                                />
                                                Loading...
                                            </span>
                                            <span
                                                v-else-if="!selectedProvince"
                                                class="text-muted-foreground"
                                                >Select province</span
                                            >
                                            <span v-else>{{
                                                provinces.find(
                                                    (p) =>
                                                        p.code ===
                                                        selectedProvince,
                                                )?.name
                                            }}</span>
                                        </SelectValue>
                                    </SelectTrigger>
                                    <SelectContent class="max-h-60">
                                        <SelectItem
                                            v-for="p in provinces"
                                            :key="p.code"
                                            :value="p.code"
                                            >{{ p.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium"
                                    >City / Municipality</label
                                >
                                <Select
                                    v-model="selectedCity"
                                    :disabled="
                                        !selectedProvince || loadingCities
                                    "
                                >
                                    <SelectTrigger class="w-full">
                                        <SelectValue>
                                            <span
                                                v-if="loadingCities"
                                                class="flex items-center gap-1.5 text-muted-foreground"
                                            >
                                                <Loader2
                                                    class="h-3 w-3 animate-spin"
                                                />
                                                Loading...
                                            </span>
                                            <span
                                                v-else-if="!selectedCity"
                                                class="text-muted-foreground"
                                                >Select city/municipality</span
                                            >
                                            <span v-else>{{
                                                cities.find(
                                                    (c) =>
                                                        c.code === selectedCity,
                                                )?.name
                                            }}</span>
                                        </SelectValue>
                                    </SelectTrigger>
                                    <SelectContent class="max-h-60">
                                        <SelectItem
                                            v-for="c in cities"
                                            :key="c.code"
                                            :value="c.code"
                                            >{{ c.name }}</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium"
                                    >Barangay</label
                                >
                                <Select
                                    v-model="selectedBarangay"
                                    :disabled="
                                        !selectedCity || loadingBarangays
                                    "
                                >
                                    <SelectTrigger class="w-full">
                                        <SelectValue>
                                            <span
                                                v-if="loadingBarangays"
                                                class="flex items-center gap-1.5 text-muted-foreground"
                                            >
                                                <Loader2
                                                    class="h-3 w-3 animate-spin"
                                                />
                                                Loading...
                                            </span>
                                            <span
                                                v-else-if="!selectedBarangay"
                                                class="text-muted-foreground"
                                                >Select barangay</span
                                            >
                                            <span v-else>{{
                                                barangays.find(
                                                    (b) =>
                                                        b.code ===
                                                        selectedBarangay,
                                                )?.name
                                            }}</span>
                                        </SelectValue>
                                    </SelectTrigger>
                                    <SelectContent class="max-h-60">
                                        <SelectItem
                                            v-for="b in barangays"
                                            :key="b.code"
                                            :value="b.code"
                                            >{{ b.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium" for="street"
                                    >Street / Block No.</label
                                >
                                <Input
                                    id="street"
                                    v-model="streetInput"
                                    placeholder="e.g. 123 Rizal St."
                                />
                            </div>
                        </div>
                        <p
                            v-if="fullAddress"
                            class="mt-3 text-xs text-muted-foreground"
                        >
                            <span class="font-medium">Full address:</span>
                            {{ fullAddress }}
                        </p>
                        <p
                            v-if="errors.address"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ errors.address }}
                        </p>
                    </section>

                    <!-- System access -->
                    <section class="space-y-4 border-t pt-8">
                        <div>
                            <h3 class="text-base font-semibold">
                                System access
                            </h3>
                            <p class="mt-0.5 text-sm text-muted-foreground">
                                Whether this employee can sign in.
                            </p>
                        </div>

                        <div
                            v-if="hasAccount"
                            class="flex items-center gap-3 rounded-lg bg-green-50 px-4 py-3 dark:bg-green-900/20"
                        >
                            <div
                                class="h-2 w-2 flex-shrink-0 rounded-full bg-green-500"
                            />
                            <p
                                class="text-sm text-green-800 dark:text-green-200"
                            >
                                This employee already has a system account.
                            </p>
                        </div>

                        <template v-else>
                            <div class="flex items-start gap-3">
                                <input
                                    type="checkbox"
                                    id="create_account"
                                    :checked="createAccount"
                                    @change="
                                        (e) => {
                                            createAccount = e.target.checked;
                                        }
                                    "
                                    class="mt-1 h-4 w-4 cursor-pointer rounded border-gray-300 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                />
                                <div class="space-y-1">
                                    <label
                                        for="create_account"
                                        class="cursor-pointer text-sm font-medium"
                                    >
                                        Create a system account for this
                                        employee
                                    </label>
                                    <p class="text-xs text-muted-foreground">
                                        A login account will be created using
                                        the employee's email. The default
                                        password will be their last name. Only
                                        enable this for employees who need
                                        system access.
                                    </p>
                                </div>
                            </div>
                            <p
                                v-if="errors.create_account"
                                class="text-xs text-red-500"
                            >
                                {{ errors.create_account }}
                            </p>
                        </template>
                    </section>

                    <!-- Schedule (owner only) -->
                    <section v-if="isOwner" class="border-t pt-8">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-base font-semibold">
                                    Weekly schedule
                                </h3>
                                <p class="mt-0.5 text-sm text-muted-foreground">
                                    Toggle workdays and set shift times. The
                                    schedule is saved separately.
                                </p>
                            </div>
                            <Button
                                type="button"
                                size="sm"
                                variant="outline"
                                class="shrink-0"
                                :disabled="savingSchedule"
                                @click="saveSchedule"
                            >
                                <Loader2
                                    v-if="savingSchedule"
                                    class="mr-1.5 h-3.5 w-3.5 animate-spin"
                                />
                                {{
                                    savingSchedule
                                        ? 'Saving...'
                                        : 'Save Schedule'
                                }}
                            </Button>
                        </div>

                        <div
                            v-if="scheduleError"
                            class="mt-4 flex items-center justify-between rounded-lg bg-red-50 px-4 py-2 text-sm text-red-700"
                        >
                            <span>{{ scheduleError }}</span>
                            <button
                                type="button"
                                aria-label="Dismiss schedule error"
                                @click="scheduleError = null"
                            >
                                <X
                                    class="ml-3 h-3.5 w-3.5 text-red-400 hover:text-red-600"
                                />
                            </button>
                        </div>

                        <div class="mt-5 divide-y border-y">
                            <div
                                v-for="d in scheduleMap"
                                :key="d.day"
                                class="flex flex-wrap items-center gap-x-4 gap-y-2 px-2 py-3 transition-colors"
                                :class="d.enabled ? 'bg-primary/[0.08]' : ''"
                            >
                                <label
                                    class="flex w-32 cursor-pointer items-center gap-3 select-none"
                                >
                                    <input
                                        type="checkbox"
                                        v-model="d.enabled"
                                        class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                    />
                                    <span
                                        class="text-sm font-medium"
                                        :class="{
                                            'text-muted-foreground': !d.enabled,
                                        }"
                                    >
                                        {{ d.day }}
                                    </span>
                                </label>
                                <template v-if="d.enabled">
                                    <div class="flex items-center gap-2">
                                        <Input
                                            v-model="d.start_time"
                                            type="time"
                                            :aria-label="`${d.day} start time`"
                                            class="h-8 w-32 text-sm"
                                        />
                                        <span
                                            class="text-xs text-muted-foreground"
                                            >to</span
                                        >
                                        <Input
                                            v-model="d.end_time"
                                            type="time"
                                            :aria-label="`${d.day} end time`"
                                            class="h-8 w-32 text-sm"
                                        />
                                    </div>
                                </template>
                                <span
                                    v-else
                                    class="text-sm text-muted-foreground"
                                    >Day off</span
                                >
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Right column -->
                <aside
                    class="rounded-xl border bg-card p-4 shadow-sm lg:sticky lg:top-4"
                >
                    <p
                        class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                    >
                        Employee summary
                    </p>
                    <div class="mt-3 flex items-center gap-3">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary"
                            aria-hidden="true"
                        >
                            {{ initials }}
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold">
                                {{ form.first_name }} {{ form.last_name }}
                            </p>
                            <p
                                class="truncate font-mono text-xs text-muted-foreground"
                            >
                                {{ form.employee_id }}
                            </p>
                        </div>
                    </div>
                    <dl class="mt-3 space-y-1.5 text-sm">
                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-muted-foreground">Position</dt>
                            <dd class="font-medium capitalize">
                                {{ form.position || '—' }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-muted-foreground">Status</dt>
                            <dd>
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs font-semibold text-white"
                                    :class="
                                        form.status === 'Active'
                                            ? 'bg-green-500'
                                            : 'bg-red-500'
                                    "
                                    >{{ form.status }}</span
                                >
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-4 border-t pt-4">
                        <p
                            class="text-xs text-muted-foreground"
                            role="status"
                            aria-live="polite"
                        >
                            {{ saveStatus }}
                        </p>

                        <Button
                            type="submit"
                            class="mt-3 w-full"
                            :disabled="!isDirty || isSubmitting"
                        >
                            <Loader2
                                v-if="isSubmitting"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            class="mt-2 w-full"
                            :disabled="isSubmitting"
                            @click="router.visit(`${baseRoute}/employee`)"
                        >
                            Cancel
                        </Button>
                    </div>
                </aside>
            </form>
        </div>
    </ShopLayout>
</template>
