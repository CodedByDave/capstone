<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

// shadcn components
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

// icons
import { ArrowLeft, Loader2, Mail } from 'lucide-vue-next';

interface Shop {
    id: number;
    shop_name: string;
}

interface PsgcItem {
    code: string;
    name: string;
}

interface EmploymentTypeOption {
    value: string;
    label: string;
}

const { shop, roles, employment_types, pay_bases } = defineProps<{
    roles: string[];
    shop: Shop;
    employment_types: EmploymentTypeOption[];
    pay_bases: EmploymentTypeOption[];
}>();

const page = usePage<AppPageProps>();
const isOwner = computed(() => page.props.auth.user.role === 'owner');
const baseRoute = computed(() => (isOwner.value ? '/shop' : '/staff'));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Employee Management', href: `${baseRoute.value}/employee` },
    { title: 'Add Employee', href: `${baseRoute.value}/employee/create` },
];
const errors = computed(() => page.props.errors as Record<string, string>);

function generateEmployeeId(): string {
    const year = new Date().getFullYear();
    const digits = String(Math.floor(10000 + Math.random() * 90000));
    return `${year}-${digits}`;
}

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

onMounted(async () => {
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
    form.value.employee_id = generateEmployeeId();
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
        const citData = await citRes.json();
        const munData = await munRes.json();
        cities.value = [...(citData || []), ...(munData || [])]
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
        const data = await res.json();
        barangays.value = (data || [])
            .map((b: any) => ({ code: b.code, name: b.name }))
            .sort((a: PsgcItem, b: PsgcItem) => a.name.localeCompare(b.name));
    } finally {
        loadingBarangays.value = false;
    }
});

const fullAddress = computed(() => {
    const parts = [
        streetInput.value.trim(),
        barangays.value.find((b) => b.code === selectedBarangay.value)?.name ??
            '',
        cities.value.find((c) => c.code === selectedCity.value)?.name ?? '',
        provinces.value.find((p) => p.code === selectedProvince.value)?.name ??
            '',
    ].filter(Boolean);
    return parts.join(', ');
});

const form = ref({
    employee_id: generateEmployeeId(),
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    address: '',
    position: '',
    employment_type: '',
    hire_date: '',
    pay_rate: '',
    pay_basis: 'monthly',
    status: 'Active' as 'Active' | 'Inactive',
});

const payBasisHelp = computed(() => {
    const messages: Record<string, string> = {
        monthly: 'Payroll converts this monthly rate using 26 working days.',
        daily: 'The rate is paid for each present day; half-days receive half the rate.',
        hourly: 'Attendance currently uses an 8-hour workday for hourly payroll.',
        per_shift: 'Each present attendance day is treated as one paid shift.',
        fixed_contract:
            'The full rate is paid once per payroll period and is not reduced by attendance.',
    };

    return messages[form.value.pay_basis] ?? '';
});

const isSubmitting = ref(false);

const isDirty = computed(
    () =>
        form.value.first_name !== '' ||
        form.value.last_name !== '' ||
        form.value.email !== '' ||
        form.value.phone !== '' ||
        form.value.position !== '' ||
        form.value.employment_type !== '' ||
        form.value.hire_date !== '' ||
        form.value.pay_rate !== '' ||
        form.value.pay_basis !== 'monthly' ||
        form.value.status !== 'Active' ||
        selectedProvince.value !== '' ||
        streetInput.value !== '' ||
        scheduleMap.value.some((d) => d.enabled),
);

const fullName = computed(() =>
    `${form.value.first_name} ${form.value.last_name}`.trim(),
);

const initials = computed(
    () =>
        `${form.value.first_name.charAt(0)}${form.value.last_name.charAt(0)}`.toUpperCase() ||
        '?',
);

const employmentTypeLabel = computed(
    () =>
        employment_types.find((t) => t.value === form.value.employment_type)
            ?.label ?? '',
);

const payBasisLabel = computed(
    () => pay_bases.find((b) => b.value === form.value.pay_basis)?.label ?? '',
);

const payRatePreview = computed(() =>
    form.value.pay_rate !== '' && Number(form.value.pay_rate) > 0
        ? `₱${Number(form.value.pay_rate).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
        : '₱ —',
);

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

const scheduleMap = ref(
    weekdays.map((day) => ({
        day: day as string,
        start_time: '08:00',
        end_time: '17:00',
        enabled: false,
    })),
);

const scheduleError = computed(() => {
    const invalid = scheduleMap.value.find(
        (d) => d.enabled && d.start_time >= d.end_time,
    );

    return invalid
        ? `${invalid.day}: end time must be after start time.`
        : null;
});

const scheduleServerError = computed(() => {
    const key = Object.keys(errors.value).find((k) =>
        k.startsWith('schedules'),
    );

    return key ? errors.value[key] : null;
});

function cancel() {
    router.visit(`${baseRoute.value}/employee`);
}

function submit() {
    if (scheduleError.value) {
        toast.error(scheduleError.value);
        return;
    }

    form.value.address = fullAddress.value;
    isSubmitting.value = true;
    const schedules = isOwner.value
        ? scheduleMap.value
              .filter((d) => d.enabled)
              .map(({ day, start_time, end_time }) => ({
                  day,
                  start_time,
                  end_time,
              }))
        : [];
    router.post(
        `${baseRoute.value}/employee`,
        { ...form.value, schedules },
        {
            preserveScroll: true,
            onError: () => {
                toast.error('Failed to add employee', {
                    description:
                        'Please check the form for errors and try again.',
                });
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
}
</script>

<template>
    <Head title="Add New Employee" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Add New Employee">
        <div class="mx-auto max-w-6xl space-y-6 px-6 pb-10">
            <!-- Header -->
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">
                        New employee
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Register a new employee under
                        <span class="font-medium text-foreground">{{
                            shop.shop_name
                        }}</span
                        >.
                    </p>
                </div>
                <Button
                    type="button"
                    variant="outline"
                    class="shrink-0"
                    @click="cancel"
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
                            Who the employee is and how to reach them.
                        </p>
                        <div class="mt-5 grid gap-x-6 gap-y-5 sm:grid-cols-3">
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium"
                                    >Employee ID
                                    <span class="text-red-500">*</span></label
                                >
                                <Input
                                    v-model="form.employee_id"
                                    class="cursor-not-allowed bg-muted font-mono"
                                    :class="{
                                        'border-red-500': errors.employee_id,
                                    }"
                                    disabled
                                />
                                <p
                                    v-if="errors.employee_id"
                                    class="text-xs text-red-500"
                                >
                                    {{ errors.employee_id }}
                                </p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium"
                                    >First Name
                                    <span class="text-red-500">*</span></label
                                >
                                <Input
                                    v-model="form.first_name"
                                    placeholder="Prince Juan"
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
                                <label class="text-sm font-medium"
                                    >Last Name
                                    <span class="text-red-500">*</span></label
                                >
                                <Input
                                    v-model="form.last_name"
                                    placeholder="Delacruz"
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
                            <div class="space-y-1.5 sm:col-span-2">
                                <label class="text-sm font-medium"
                                    >Email Address
                                    <span class="text-red-500">*</span></label
                                >
                                <Input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="employee@example.com"
                                    :class="{ 'border-red-500': errors.email }"
                                />
                                <p
                                    v-if="errors.email"
                                    class="text-xs text-red-500"
                                >
                                    {{ errors.email }}
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
                            Role, employment type and pay.
                        </p>
                        <div class="mt-5 grid gap-x-6 gap-y-5 sm:grid-cols-2">
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium"
                                    >Position
                                    <span class="text-red-500">*</span></label
                                >
                                <Select v-model="form.position"
                                    ><SelectTrigger
                                        class="w-full"
                                        :class="{
                                            'border-red-500': errors.position,
                                        }"
                                        ><SelectValue
                                            placeholder="Select position" /></SelectTrigger
                                    ><SelectContent
                                        ><SelectItem
                                            v-for="role in roles"
                                            :key="role"
                                            :value="role"
                                            class="capitalize"
                                            >{{ role }}</SelectItem
                                        ></SelectContent
                                    ></Select
                                >
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
                                <Select v-model="form.employment_type"
                                    ><SelectTrigger
                                        class="w-full"
                                        :class="{
                                            'border-red-500':
                                                errors.employment_type,
                                        }"
                                        ><SelectValue
                                            placeholder="Select employment type" /></SelectTrigger
                                    ><SelectContent
                                        ><SelectItem
                                            v-for="type in employment_types"
                                            :key="type.value"
                                            :value="type.value"
                                            >{{ type.label }}</SelectItem
                                        ></SelectContent
                                    ></Select
                                >
                                <p
                                    v-if="errors.employment_type"
                                    class="text-xs text-red-500"
                                >
                                    {{ errors.employment_type }}
                                </p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium"
                                    >Hire Date
                                    <span class="text-red-500">*</span></label
                                >
                                <Input
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
                                <label class="text-sm font-medium"
                                    >Status
                                    <span class="text-red-500">*</span></label
                                >
                                <Select v-model="form.status"
                                    ><SelectTrigger
                                        class="w-full"
                                        :class="{
                                            'border-red-500': errors.status,
                                        }"
                                        ><SelectValue
                                            placeholder="Select status" /></SelectTrigger
                                    ><SelectContent
                                        ><SelectItem value="Active"
                                            >Active</SelectItem
                                        ><SelectItem value="Inactive"
                                            >Inactive</SelectItem
                                        ></SelectContent
                                    ></Select
                                >
                                <p
                                    v-if="errors.status"
                                    class="text-xs text-red-500"
                                >
                                    {{ errors.status }}
                                </p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium"
                                    >Pay Rate
                                    <span class="text-red-500">*</span></label
                                >
                                <Input
                                    v-model.number="form.pay_rate"
                                    type="number"
                                    min="0.01"
                                    step="0.01"
                                    placeholder="Enter amount"
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
                                <label class="text-sm font-medium"
                                    >Pay Basis
                                    <span class="text-red-500">*</span></label
                                >
                                <Select v-model="form.pay_basis"
                                    ><SelectTrigger
                                        class="w-full"
                                        :class="{
                                            'border-red-500': errors.pay_basis,
                                        }"
                                        ><SelectValue
                                            placeholder="Select pay basis" /></SelectTrigger
                                    ><SelectContent
                                        ><SelectItem
                                            v-for="basis in pay_bases"
                                            :key="basis.value"
                                            :value="basis.value"
                                            >{{ basis.label }}</SelectItem
                                        ></SelectContent
                                    ></Select
                                >
                                <p
                                    v-if="errors.pay_basis"
                                    class="text-xs text-red-500"
                                >
                                    {{ errors.pay_basis }}
                                </p>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-muted-foreground">
                            {{ payBasisHelp }}
                        </p>
                    </section>

                    <!-- Address -->
                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">Address</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            Where the employee lives.
                        </p>
                        <div class="mt-5 grid gap-x-6 gap-y-5 sm:grid-cols-3">
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium"
                                    >Province
                                    <span class="text-red-500">*</span></label
                                >
                                <Select
                                    v-model="selectedProvince"
                                    :disabled="loadingProvinces"
                                    ><SelectTrigger class="w-full"
                                        ><SelectValue
                                            ><span
                                                v-if="loadingProvinces"
                                                class="flex items-center gap-1.5 text-muted-foreground"
                                                ><Loader2
                                                    class="h-3 w-3 animate-spin"
                                                />
                                                Loading...</span
                                            ><span
                                                v-else-if="!selectedProvince"
                                                class="text-muted-foreground"
                                                >Select province</span
                                            ><span v-else>{{
                                                provinces.find(
                                                    (x) =>
                                                        x.code ===
                                                        selectedProvince,
                                                )?.name
                                            }}</span></SelectValue
                                        ></SelectTrigger
                                    ><SelectContent class="max-h-60"
                                        ><SelectItem
                                            v-for="x in provinces"
                                            :key="x.code"
                                            :value="x.code"
                                            >{{ x.name }}</SelectItem
                                        ></SelectContent
                                    ></Select
                                >
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium"
                                    >City / Municipality
                                    <span class="text-red-500">*</span></label
                                >
                                <Select
                                    v-model="selectedCity"
                                    :disabled="
                                        !selectedProvince || loadingCities
                                    "
                                    ><SelectTrigger class="w-full"
                                        ><SelectValue
                                            ><span
                                                v-if="loadingCities"
                                                class="flex items-center gap-1.5 text-muted-foreground"
                                                ><Loader2
                                                    class="h-3 w-3 animate-spin"
                                                />
                                                Loading...</span
                                            ><span
                                                v-else-if="!selectedCity"
                                                class="text-muted-foreground"
                                                >Select city/municipality</span
                                            ><span v-else>{{
                                                cities.find(
                                                    (x) =>
                                                        x.code === selectedCity,
                                                )?.name
                                            }}</span></SelectValue
                                        ></SelectTrigger
                                    ><SelectContent class="max-h-60"
                                        ><SelectItem
                                            v-for="x in cities"
                                            :key="x.code"
                                            :value="x.code"
                                            >{{ x.name }}</SelectItem
                                        ></SelectContent
                                    ></Select
                                >
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium"
                                    >Barangay
                                    <span class="text-red-500">*</span></label
                                >
                                <Select
                                    v-model="selectedBarangay"
                                    :disabled="
                                        !selectedCity || loadingBarangays
                                    "
                                    ><SelectTrigger class="w-full"
                                        ><SelectValue
                                            ><span
                                                v-if="loadingBarangays"
                                                class="flex items-center gap-1.5 text-muted-foreground"
                                                ><Loader2
                                                    class="h-3 w-3 animate-spin"
                                                />
                                                Loading...</span
                                            ><span
                                                v-else-if="!selectedBarangay"
                                                class="text-muted-foreground"
                                                >Select barangay</span
                                            ><span v-else>{{
                                                barangays.find(
                                                    (x) =>
                                                        x.code ===
                                                        selectedBarangay,
                                                )?.name
                                            }}</span></SelectValue
                                        ></SelectTrigger
                                    ><SelectContent class="max-h-60"
                                        ><SelectItem
                                            v-for="x in barangays"
                                            :key="x.code"
                                            :value="x.code"
                                            >{{ x.name }}</SelectItem
                                        ></SelectContent
                                    ></Select
                                >
                            </div>
                            <div class="space-y-1.5 sm:col-span-3">
                                <label class="text-sm font-medium"
                                    >Street / Block No.</label
                                >
                                <Input
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

                    <!-- Weekly schedule (owner only) -->
                    <section v-if="isOwner" class="border-t pt-8">
                        <h3 class="text-base font-semibold">Weekly schedule</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            Toggle workdays and set shift times. Saved together
                            with the employee.
                        </p>

                        <p
                            v-if="scheduleError || scheduleServerError"
                            class="mt-4 rounded-lg bg-red-50 px-4 py-2 text-sm text-red-700"
                            role="alert"
                        >
                            {{ scheduleError || scheduleServerError }}
                        </p>

                        <div class="mt-5 divide-y border-y">
                            <div
                                v-for="d in scheduleMap"
                                :key="d.day"
                                class="flex flex-wrap items-center gap-x-4 gap-y-2 px-2 py-3 transition-colors"
                                :class="d.enabled ? 'bg-primary/10' : ''"
                            >
                                <label
                                    class="flex w-32 cursor-pointer items-center gap-3 select-none"
                                >
                                    <input
                                        v-model="d.enabled"
                                        type="checkbox"
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
                                <div
                                    v-if="d.enabled"
                                    class="flex items-center gap-2"
                                >
                                    <Input
                                        v-model="d.start_time"
                                        type="time"
                                        :aria-label="`${d.day} start time`"
                                        class="h-8 w-32 text-sm"
                                    />
                                    <span class="text-xs text-muted-foreground"
                                        >to</span
                                    >
                                    <Input
                                        v-model="d.end_time"
                                        type="time"
                                        :aria-label="`${d.day} end time`"
                                        class="h-8 w-32 text-sm"
                                    />
                                </div>
                                <span
                                    v-else
                                    class="text-sm text-muted-foreground"
                                    >Day off</span
                                >
                            </div>
                        </div>
                    </section>

                    <!-- System access -->
                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">System access</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            How the employee signs in.
                        </p>
                        <div
                            class="mt-5 flex items-start gap-3 rounded-lg bg-blue-50 p-4 dark:bg-blue-950/30"
                        >
                            <Mail
                                class="mt-0.5 h-4 w-4 shrink-0 text-blue-600 dark:text-blue-400"
                                aria-hidden="true"
                            />
                            <div class="space-y-1">
                                <p
                                    class="text-sm font-medium text-blue-700 dark:text-blue-300"
                                >
                                    A login account will be created
                                    automatically
                                </p>
                                <p
                                    class="text-xs text-blue-600 dark:text-blue-400"
                                >
                                    The employee will receive their login
                                    credentials (email + temporary password) at
                                    the email address provided above. Their
                                    temporary password is their last name. They
                                    can change it after logging in.
                                </p>
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
                        Employee preview
                    </p>
                    <div class="mt-3 flex items-center gap-3">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-700 dark:bg-blue-900/50 dark:text-blue-300"
                        >
                            {{ initials }}
                        </div>
                        <div class="min-w-0">
                            <p
                                class="truncate text-base font-bold"
                                :class="
                                    fullName ? '' : 'text-muted-foreground/60'
                                "
                            >
                                {{ fullName || 'Employee name' }}
                            </p>
                            <p class="truncate text-xs text-muted-foreground">
                                {{ form.email || 'email@example.com' }}
                            </p>
                        </div>
                    </div>
                    <dl class="mt-3 space-y-1.5 text-xs">
                        <div class="flex justify-between gap-2">
                            <dt class="text-muted-foreground">Position</dt>
                            <dd class="font-medium capitalize">
                                {{ form.position || '—' }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-muted-foreground">Type</dt>
                            <dd class="font-medium">
                                {{ employmentTypeLabel || '—' }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-muted-foreground">Pay</dt>
                            <dd class="font-bold">
                                {{ payRatePreview }}
                                <span
                                    class="font-normal text-muted-foreground"
                                    >{{ payBasisLabel }}</span
                                >
                            </dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-muted-foreground">Status</dt>
                            <dd
                                class="font-medium"
                                :class="
                                    form.status === 'Active'
                                        ? 'text-blue-600'
                                        : 'text-muted-foreground'
                                "
                            >
                                {{ form.status }}
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-4 border-t pt-4">
                        <p class="text-xs text-muted-foreground" role="status">
                            {{
                                isDirty
                                    ? 'You have unsaved changes'
                                    : 'No changes yet'
                            }}
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
                            {{ isSubmitting ? 'Saving...' : 'Add Employee' }}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            class="mt-2 w-full"
                            :disabled="isSubmitting"
                            @click="cancel"
                        >
                            Cancel
                        </Button>
                    </div>
                </aside>
            </form>
        </div>
    </ShopLayout>
</template>
